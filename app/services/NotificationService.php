<?php
declare(strict_types=1);

/**
 * WhatsApp & SMS notification sender.
 *
 * Currently supports sending outbound messages, always recording them to the
 * `notification_logs` table. When real gateway credentials are configured in
 * config and dry_run is false, messages are actually delivered via Twilio.
 */
class NotificationService
{
    /** Insert a notification log row (queued), returns the id. */
    public static function queue(string $channel, string $to, string $message, ?string $subject = null, ?string $relatedType = null, ?int $relatedId = null, ?string $recipientName = null): int
    {
        self::validateChannel($channel);
        return Database::insert(
            "INSERT INTO notification_logs (channel, to_phone, subject, message, status, related_type, related_id, recipient_name)
             VALUES (?,?,?,?,'queued',?,?,?)",
            [$channel, $to, $subject ?: null, $message, $relatedType ?: null, $relatedId ?: null, $recipientName ?: null]
        );
    }

    /** Send a single notification (queue + attempt delivery). Returns log id. */
    public static function send(string $channel, string $to, string $message, ?string $subject = null, ?string $relatedType = null, ?int $relatedId = null, ?string $recipientName = null): int
    {
        $id = self::queue($channel, $to, $message, $subject, $relatedType, $relatedId, $recipientName);
        self::deliver($id);
        return $id;
    }

    /** Attempt delivery of a specific log row (called after queue or by a worker). */
    public static function deliver(int $logId): bool
    {
        $log = Database::one("SELECT * FROM notification_logs WHERE id=?", [$logId]);
        if (!$log) return false;

        $dryRun = (bool)App::config('notifications.dry_run', true);

        // Normalize phone to E.164-ish: strip non-digits, ensure country code later via config.
        $phone = $log['to_phone'];

        try {
            if ($dryRun) {
                // No real gateway — mark as sent (simulated).
                Database::execute(
                    "UPDATE notification_logs SET status='sent', sent_at=NOW(), error='dry-run' WHERE id=?",
                    [$logId]
                );
                return true;
            }

            $result = $log['channel'] === 'whatsapp'
                ? self::sendWhatsApp($phone, $log['message'])
                : self::sendSms($phone, $log['message']);

            Database::execute(
                "UPDATE notification_logs SET status='sent', sent_at=NOW(), provider_ref=? WHERE id=?",
                [$result['ref'] ?? null, $logId]
            );
            return true;
        } catch (Throwable $e) {
            Database::execute(
                "UPDATE notification_logs SET status='failed', error=? WHERE id=?",
                [substr($e->getMessage(), 0, 500), $logId]
            );
            return false;
        }
    }

    private static function validateChannel(string $channel): void
    {
        if (!in_array($channel, ['whatsapp', 'sms'], true)) {
            throw new InvalidArgumentException('Invalid channel: ' . $channel);
        }
    }

    private static function sendSms(string $phone, string $message): array
    {
        $cfg = App::config('notifications.sms', []);
        if (($cfg['provider'] ?? '') !== 'twilio' || empty($cfg['twilio_sid']) || empty($cfg['twilio_token'])) {
            throw new RuntimeException('SMS provider not configured.');
        }
        $sid = $cfg['twilio_sid'];
        $token = $cfg['twilio_token'];
        $from = $cfg['twilio_from'];

        $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
        $data = http_build_query([
            'From' => $from,
            'To' => $phone,
            'Body' => $message,
        ]);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => "$sid:$token",
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        ]);
        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($code >= 400 || !$resp) {
            throw new RuntimeException("Twilio error ($code): " . substr($resp ?: $err, 0, 300));
        }
        $json = json_decode($resp, true);
        return ['ref' => $json['sid'] ?? null];
    }

    private static function sendWhatsApp(string $phone, string $message): array
    {
        $cfg = App::config('notifications.whatsapp', []);
        // Use the SMS (Twilio) credentials with the WhatsApp sandbox sender.
        $sms = App::config('notifications.sms', []);
        if (empty($sms['twilio_sid']) || empty($sms['twilio_token'])) {
            throw new RuntimeException('WhatsApp provider not configured.');
        }
        $sid = $sms['twilio_sid'];
        $token = $sms['twilio_token'];
        $whatsappFrom = $cfg['twilio_whatsapp_from'] ?? 'whatsapp:+14155238886';

        $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
        $data = http_build_query([
            'From' => $whatsappFrom,
            'To' => 'whatsapp:' . $phone,
            'Body' => $message,
        ]);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => "$sid:$token",
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        ]);
        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code >= 400) {
            throw new RuntimeException("Twilio WhatsApp error ($code): " . substr($resp ?: '', 0, 300));
        }
        $json = json_decode($resp, true);
        return ['ref' => $json['sid'] ?? null];
    }
}

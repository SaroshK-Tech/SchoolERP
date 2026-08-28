<?php
declare(strict_types=1);

/**
 * Route registration. Each controller is a class in app/controllers/
 * exposing methods. Lazy-load classes on first use via an autoloader.
 */

spl_autoload_register(function (string $class): void {
    $file = __DIR__ . '/controllers/' . $class . '.php';
    if (is_file($file)) {
        require $file;
    }
});

function register_routes(Router $route): void
{
    // Auth
    $auth = new AuthController();
    $route->get('login', fn() => $auth->loginForm());
    $route->post('login', fn() => $auth->login());
    $route->get('logout', fn() => $auth->logout());

    // Dashboard
    $dash = new DashboardController();
    $route->get('dashboard', fn() => $dash->index());
    $route->get('/', fn() => $dash->index());

    // Staff
    $staff = new StaffController();
    $route->get('staff', fn() => $staff->index());
    $route->get('staff/create', fn() => $staff->create());
    $route->post('staff/create', fn() => $staff->store());
    $route->get('staff/edit/{id}', fn($p) => $staff->edit($p['id']));
    $route->post('staff/edit/{id}', fn($p) => $staff->update($p['id']));
    $route->get('staff/view/{id}', fn($p) => $staff->show($p['id']));
    $route->post('staff/delete/{id}', fn($p) => $staff->destroy($p['id']));

    // Students
    $student = new StudentController();
    $route->get('students', fn() => $student->index());
    $route->get('students/create', fn() => $student->create());
    $route->post('students/create', fn() => $student->store());
    $route->get('students/edit/{id}', fn($p) => $student->edit($p['id']));
    $route->post('students/edit/{id}', fn($p) => $student->update($p['id']));
    $route->get('students/view/{id}', fn($p) => $student->show($p['id']));
    $route->post('students/delete/{id}', fn($p) => $student->destroy($p['id']));

    // Classes & sections
    $cls = new ClassController();
    $route->get('classes', fn() => $cls->index());
    $route->get('classes/create', fn() => $cls->create());
    $route->post('classes/create', fn() => $cls->store());
    $route->post('classes/delete/{id}', fn($p) => $cls->destroy($p['id']));
    $route->post('classes/section/create', fn() => $cls->sectionCreate());
    $route->post('classes/section/{id}/delete', fn($p) => $cls->sectionDelete($p['id']));

    // Bulk promotion
    $prom = new PromotionController();
    $route->get('promotion', fn() => $prom->index());
    $route->post('promotion/process', fn() => $prom->process());

    // Finance
    $fin = new FinanceController();
    $route->get('finance', fn() => $fin->overview());

    // Payroll
    $route->get('finance/payroll', fn() => $fin->payrollIndex());
    $route->get('finance/payroll/generate', fn() => $fin->payrollGenerateForm());
    $route->post('finance/payroll/generate', fn() => $fin->payrollGenerate());
    $route->post('finance/payroll/{periodId}/mark-paid', fn($p) => $fin->payrollMarkPaid($p['periodId']));

    // Fees
    $route->get('finance/fees', fn() => $fin->feeStructures());
    $route->post('finance/fees/create', fn() => $fin->feeStructureCreate());
    $route->post('finance/fees/{id}/delete', fn($p) => $fin->feeStructureDelete($p['id']));
    $route->post('finance/fees/collect', fn() => $fin->feeCollect());
    $route->get('finance/fee-payments', fn() => $fin->feePayments());

    // Petty income/expense
    $route->get('finance/petty', fn() => $fin->pettyLedger());
    $route->post('finance/petty/create', fn() => $fin->pettyCreate());

    // Exams & results
    $exam = new ExamController();
    $route->get('exams', fn() => $exam->index());
    $route->get('exams/create', fn() => $exam->create());
    $route->post('exams/create', fn() => $exam->store());
    $route->get('exams/{id}', fn($p) => $exam->show($p['id']));
    $route->get('exams/{id}/schedule', fn($p) => $exam->scheduleForm($p['id']));
    $route->post('exams/{id}/schedule', fn($p) => $exam->scheduleStore($p['id']));
    $route->get('exams/{id}/results', fn($p) => $exam->resultsForm($p['id']));
    $route->post('exams/{id}/results', fn($p) => $exam->resultsSave($p['id']));
    $route->get('exams/{id}/report-card', fn($p) => $exam->reportCard($p['id']));

    // Subjects
    $route->get('subjects', fn() => $exam->subjectsIndex());

    // Timetable
    $timetable = new TimetableController();
    $route->get('timetable', fn() => $timetable->index());
    $route->get('timetable/slots', fn() => $timetable->slots());
    $route->post('timetable/slots/create', fn() => $timetable->slotsCreate());
    $route->get('timetable/edit', fn() => $timetable->edit());
    $route->post('timetable/edit', fn() => $timetable->save());
    $route->get('timetable/print', fn() => $timetable->printView());

    // Subjects (standalone manage)
    $route->get('subjects/manage', fn() => $exam->subjectsManage());
    $route->post('subjects/manage', fn() => $exam->subjectsManageStore());

    // Notifications
    $notif = new NotificationController();
    $route->get('notifications', fn() => $notif->index());
    $route->get('notifications/send', fn() => $notif->compose());
    $route->post('notifications/send', fn() => $notif->send());
    $route->get('notifications/logs', fn() => $notif->logs());
}

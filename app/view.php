<?php
declare(strict_types=1);

/**
 * Render a view inside the admin layout, or standalone if $layout=false.
 */
function view(string $tpl, array $data = [], bool $layout = true): void
{
    $data['__tpl'] = $tpl;
    extract($data, EXTR_SKIP);
    ob_start();
    require App::viewPath($tpl);
    $content = ob_get_clean();

    $title = $title ?? App::config('app.name', 'SchoolERP');
    $page   = $page ?? '';
    $flashes = flash_drain();
    $user   = Auth::user();

    if ($layout) {
        require App::viewPath('layouts/app');
    } else {
        echo $content;
    }
}

function partial(string $tpl, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require App::viewPath($tpl);
}

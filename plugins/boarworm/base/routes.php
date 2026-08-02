<?php

use System\Classes\MailManager;
use System\Models\MailLayout;
use System\Models\MailTemplate;
use System\Helpers\View as ViewHelper;

// Only a logged-in backend admin can view these mail previews. These routes
// are registered via a plain `require` (not through RouteServiceProvider),
// so the "web" middleware group (session/cookies/auth) isn't attached
// automatically - without it, BackendAuth::check() can never see the
// session at all, regardless of actual login state. Route::middleware()
// below attaches it explicitly.
$guardPreviewMailRoute = function () {
    if (!\BackendAuth::check()) {
        abort(403);
    }
};

Route::middleware('web')->group(function () use ($guardPreviewMailRoute) {

// Visit /boarworm-base/preview-mail/contact-admin to view in the browser.
// Add ?lang=dk to preview the Danish translation.
Route::get('/boarworm-base/preview-mail/contact-admin', function () use ($guardPreviewMailRoute) {
    $guardPreviewMailRoute();

    // "dk" is the friendly query value, but the site's actual configured
    // locale code for Danish is "da" (see system_site_definitions / the
    // /da/ route prefix) - lang/da/lang.php must match that to be used by
    // real (non-preview) emails too, so we translate dk -> da here.
    if (request('lang') === 'dk') {
        \App::setLocale('da');
    }

    $data = ViewHelper::getGlobalVars() + [
        'id' => 42,
        'date' => now()->toDateTimeString(),
        'ip' => '127.0.0.1',
        'data' => [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'comments' => 'Hello, I would like to know more about your weekly activities.',
        ],
    ];

    $template = new MailTemplate;
    $template->fillFromContent(File::get(base_path('plugins/boarworm/base/views/mail/contact-admin.htm')));

    // fillFromContent() re-derives `layout` from the file's own `layout = "main"`
    // setting via a DB-first lookup, which doesn't know about file-registered
    // layouts until they're synced - so it must be assigned again, after.
    $layout = new MailLayout;
    $layout->fillFromCode('main');
    $template->layout = $layout;

    return MailManager::instance()->renderTemplate($template, $data);
});

// Visit /boarworm-base/preview-mail/contact-client to view in the browser.
// Add ?lang=dk to preview the Danish translation.
Route::get('/boarworm-base/preview-mail/contact-client', function () use ($guardPreviewMailRoute) {
    $guardPreviewMailRoute();

    // "dk" is the friendly query value, but the site's actual configured
    // locale code for Danish is "da" (see system_site_definitions / the
    // /da/ route prefix) - lang/da/lang.php must match that to be used by
    // real (non-preview) emails too, so we translate dk -> da here.
    if (request('lang') === 'dk') {
        \App::setLocale('da');
    }

    $data = ViewHelper::getGlobalVars() + [
        'name' => 'Jane Doe',
        'email' => 'jane.doe@example.com',
    ];

    $template = new MailTemplate;
    $template->fillFromContent(File::get(base_path('plugins/boarworm/base/views/mail/contact-client.htm')));

    // fillFromContent() re-derives `layout` from the file's own `layout = "main"`
    // setting via a DB-first lookup, which doesn't know about file-registered
    // layouts until they're synced - so it must be assigned again, after.
    $layout = new MailLayout;
    $layout->fillFromCode('main');
    $template->layout = $layout;

    return MailManager::instance()->renderTemplate($template, $data);
});

});

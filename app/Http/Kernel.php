<?php

namespace App\Http;


use App\Http\Middleware\CheckAccessToken;
use App\Http\Middleware\CheckAppKeySecret;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        // app key secreat  check
        'checkAppKeySecret' => CheckAppKeySecret::class,
        // app access token check
        'checkAccessToken' => CheckAccessToken::class,
        'admin' => \App\Http\Middleware\Admin::class,
        'admin.role' => \App\Http\Middleware\AdminRole::class,
        'admin.jwt.auth' => \App\Http\Middleware\AdminJwt::class,
        'admin.jwt.changeAuth' => \App\Http\Middleware\AdminJwtChange::class,
        'admin.jwt.permission' => \App\Http\Middleware\AdminJwtPermission::class,
        'self.jwt.refresh' => \App\Http\Middleware\RefreshToken::class,
        'self.jwt.auth' => \App\Http\Middleware\BevanJwtAuth::class,
        'check.request.data' => \App\Http\Middleware\VerificateRequstData::class,
        'api.count' => \App\Http\Middleware\CountApi::class,
        'api.checkIsDisable' => \App\Http\Middleware\CheckIsDisableService::class,
        'api.rewriteResp' => \App\Http\Middleware\RewriteResponse::class
    ];
}

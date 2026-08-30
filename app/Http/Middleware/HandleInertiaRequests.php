<?php

namespace App\Http\Middleware;

use App\Domain\Marketing\ContactInfo;
use App\Domain\Marketing\ErpSaasProduct;
use App\Domain\ErpSaasRegistration\ErpSaasRegisterModules;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'avatar' => $request->user()->avatar,
                ] : null,
            ],
            'flash' => [
                'error' => $request->session()->get('error'),
                'success' => $request->session()->get('success'),
            ],
            'products' => [
                ErpSaasProduct::toArray(),
            ],
            'contact' => [
                'email' => ContactInfo::EMAIL,
                'emailHref' => ContactInfo::EMAIL_HREF,
                'phone' => ContactInfo::PHONE,
                'phoneHref' => ContactInfo::PHONE_HREF,
                'zaloHref' => ContactInfo::ZALO_HREF,
                'zaloQrUrl' => ContactInfo::ZALO_QR_PATH,
                'zaloDisplayName' => ContactInfo::ZALO_DISPLAY_NAME,
            ],
            'erpSaasRegister' => [
                'submitUrl' => route('erp-saas.register'),
                'turnstileSiteKey' => config('services.turnstile.site_key'),
                'turnstileBypass' => (bool) config('services.turnstile.bypass'),
                'modules' => ErpSaasRegisterModules::catalog(),
                'maxModules' => ErpSaasRegisterModules::MAX,
            ],
        ];
    }
}

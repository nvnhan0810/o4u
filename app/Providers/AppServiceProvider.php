<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\AppAccess\ClientAppAccessRepository;
use App\Domain\ErpSaasRegistration\OdooTenantRegistrar;
use App\Domain\ErpSaasRegistration\RegistrationErrorCode;
use App\Domain\ErpSaasRegistration\RegistrationNotifier;
use App\Domain\ErpSaasRegistration\TurnstileVerifier;
use App\Infrastructure\AppAccess\EloquentClientAppAccessRepository;
use App\Infrastructure\ErpSaasRegistration\HttpOdooTenantRegistrar;
use App\Infrastructure\ErpSaasRegistration\HttpTurnstileVerifier;
use App\Infrastructure\ErpSaasRegistration\TelegramRegistrationNotifier;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            ClientAppAccessRepository::class,
            EloquentClientAppAccessRepository::class,
        );
        $this->app->bind(OdooTenantRegistrar::class, HttpOdooTenantRegistrar::class);
        $this->app->bind(TurnstileVerifier::class, HttpTurnstileVerifier::class);
        $this->app->bind(RegistrationNotifier::class, TelegramRegistrationNotifier::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') || $this->app->environment('development')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('erp-saas-register', function (Request $request) {
            $ip = $request->ip() ?: 'unknown';
            $dailyCap = max(1, (int) config('services.odoo_tenant.daily_cap', 50));

            $tooManyResponse = static function (Request $request, array $headers) {
                return response()->json([
                    'success' => false,
                    'message' => RegistrationErrorCode::messageFor(
                        RegistrationErrorCode::TOO_MANY_ATTEMPTS,
                    ),
                    'code' => RegistrationErrorCode::TOO_MANY_ATTEMPTS,
                    'data' => [],
                ], 429, $headers);
            };

            return [
                Limit::perMinute(3)->by('erp-reg-min:'.$ip)->response($tooManyResponse),
                Limit::perHour(10)->by('erp-reg-hour:'.$ip)->response($tooManyResponse),
                Limit::perDay($dailyCap)->by('erp-reg-day:'.$ip)->response($tooManyResponse),
            ];
        });

        Queue::failing(function (JobFailed $event): void {
            Log::error('Queue job failed', [
                'connection' => $event->connectionName,
                'queue' => $event->job->getQueue(),
                'job' => $event->job->resolveName(),
                'exception' => $event->exception->getMessage(),
            ]);
        });
    }
}

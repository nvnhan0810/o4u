<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Application\ErpSaasRegistration\RegisterErpSaasTenant;
use App\Domain\ErpSaasRegistration\ErpSaasRegisterModules;
use App\Domain\ErpSaasRegistration\RegisterTenantCommand;
use App\Domain\ErpSaasRegistration\RegistrationErrorCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\ErpSaasRegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;

class ErpSaasRegisterController extends Controller
{
    public function __invoke(
        ErpSaasRegisterRequest $request,
        RegisterErpSaasTenant $useCase,
    ): JsonResponse|RedirectResponse {
        $email = strtolower(trim((string) $request->input('email')));
        $emailKey = 'erp-saas-register-email:'.$email;
        if (RateLimiter::tooManyAttempts($emailKey, 3)) {
            return $this->respond(
                $request,
                false,
                RegistrationErrorCode::messageFor(RegistrationErrorCode::TOO_MANY_ATTEMPTS),
                RegistrationErrorCode::TOO_MANY_ATTEMPTS,
                429,
            );
        }

        $modules = ErpSaasRegisterModules::normalize(
            is_array($request->input('modules')) ? $request->input('modules') : [],
        );

        $result = $useCase->execute(
            new RegisterTenantCommand(
                companyName: trim((string) $request->input('company_name')),
                contactName: trim((string) $request->input('contact_name')),
                email: $email,
                phone: trim((string) $request->input('phone')),
                modules: $modules,
                notes: $request->filled('notes')
                    ? trim((string) $request->input('notes'))
                    : null,
            ),
            turnstileToken: (string) $request->input('cf-turnstile-response', ''),
            remoteIp: $request->ip(),
        );

        if ($result->success) {
            RateLimiter::hit($emailKey, 3600);
        }

        if (! $result->success) {
            $field = RegistrationErrorCode::validationFieldFor($result->code);
            if ($field !== null && ($request->expectsJson() || $request->wantsJson() || $request->ajax())) {
                return response()->json([
                    'success' => false,
                    'message' => $result->message,
                    'code' => $result->code,
                    'errors' => [$field => [$result->message]],
                ], 422);
            }

            return $this->respond(
                $request,
                false,
                $result->message,
                $result->code,
                $result->status >= 400 ? $result->status : 400,
            );
        }

        return $this->respond(
            $request,
            true,
            $result->message,
            null,
            201,
            [
                'tenant_url' => $result->tenantUrl(),
                'email' => $result->data['email'] ?? $email,
                'email_sent' => $result->emailSent(),
                'domain' => $result->data['domain'] ?? null,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function respond(
        ErpSaasRegisterRequest $request,
        bool $success,
        string $message,
        ?string $code,
        int $status,
        array $data = [],
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
                'code' => $code,
                'data' => $data,
            ], $success ? ($status === 201 ? 201 : 200) : $status);
        }

        if ($success) {
            return back()->with('success', $message)->with('tenant_url', $data['tenant_url'] ?? null);
        }

        return back()->withErrors(['register' => $message])->withInput();
    }
}

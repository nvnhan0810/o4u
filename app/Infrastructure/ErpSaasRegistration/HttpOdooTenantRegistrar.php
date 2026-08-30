<?php

declare(strict_types=1);

namespace App\Infrastructure\ErpSaasRegistration;

use App\Domain\ErpSaasRegistration\OdooTenantRegistrar;
use App\Domain\ErpSaasRegistration\RegisterTenantCommand;
use App\Domain\ErpSaasRegistration\RegisterTenantResult;
use App\Domain\ErpSaasRegistration\RegistrationErrorCode;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class HttpOdooTenantRegistrar implements OdooTenantRegistrar
{
    public function register(RegisterTenantCommand $command): RegisterTenantResult
    {
        $url = (string) config('services.odoo_tenant.registration_url');
        $key = (string) config('services.odoo_tenant.registration_key');

        if ($url === '' || $key === '') {
            Log::error('ERP SaaS registration: Odoo tenant config missing');

            return new RegisterTenantResult(
                success: false,
                code: RegistrationErrorCode::UPSTREAM_UNAVAILABLE,
                message: RegistrationErrorCode::messageFor(RegistrationErrorCode::UPSTREAM_UNAVAILABLE),
                status: 503,
            );
        }

        try {
            $response = Http::withHeaders([
                'X-Registration-Key' => $key,
                'Accept' => 'application/json',
            ])
                ->timeout(30)
                ->acceptJson()
                ->post($url, [
                    'company_name' => $command->companyName,
                    'contact_name' => $command->contactName,
                    'email' => $command->email,
                    'phone' => $command->phone,
                    'modules' => $command->modules,
                    'notes' => $command->notes,
                ]);
        } catch (ConnectionException $e) {
            Log::warning('ERP SaaS registration: Odoo connection failed', [
                'message' => $e->getMessage(),
            ]);

            return new RegisterTenantResult(
                success: false,
                code: RegistrationErrorCode::UPSTREAM_UNAVAILABLE,
                message: RegistrationErrorCode::messageFor(RegistrationErrorCode::UPSTREAM_UNAVAILABLE),
                status: 503,
            );
        }

        /** @var array<string, mixed> $json */
        $json = $response->json() ?? [];
        $code = is_string($json['code'] ?? null) ? $json['code'] : null;
        $message = is_string($json['message'] ?? null)
            ? $json['message']
            : RegistrationErrorCode::messageFor($code);
        /** @var array<string, mixed> $data */
        $data = is_array($json['data'] ?? null) ? $json['data'] : [];

        if ($response->status() === 201 && ($json['success'] ?? false) === true) {
            return new RegisterTenantResult(
                success: true,
                code: null,
                message: is_string($json['message'] ?? null)
                    ? $json['message']
                    : 'Đăng ký thành công.',
                data: $data,
                status: 201,
            );
        }

        $mappedCode = $code ?: match ($response->status()) {
            401 => RegistrationErrorCode::UPSTREAM_UNAVAILABLE,
            429 => RegistrationErrorCode::DAILY_CAP,
            default => RegistrationErrorCode::PROVISION_FAILED,
        };

        $known = RegistrationErrorCode::fieldMessages();
        $finalMessage = isset($known[$mappedCode])
            ? $known[$mappedCode]
            : ($message !== '' ? $message : RegistrationErrorCode::messageFor(RegistrationErrorCode::PROVISION_FAILED));

        return new RegisterTenantResult(
            success: false,
            code: $mappedCode,
            message: $finalMessage,
            data: $data,
            status: $response->status() >= 400 ? $response->status() : 502,
        );
    }
}

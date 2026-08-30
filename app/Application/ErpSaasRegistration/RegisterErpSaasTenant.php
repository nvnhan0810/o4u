<?php

declare(strict_types=1);

namespace App\Application\ErpSaasRegistration;

use App\Domain\ErpSaasRegistration\OdooTenantRegistrar;
use App\Domain\ErpSaasRegistration\RegisterTenantCommand;
use App\Domain\ErpSaasRegistration\RegisterTenantResult;
use App\Domain\ErpSaasRegistration\RegistrationErrorCode;
use App\Domain\ErpSaasRegistration\RegistrationNotifier;
use App\Domain\ErpSaasRegistration\TurnstileVerifier;

final class RegisterErpSaasTenant
{
    public function __construct(
        private readonly TurnstileVerifier $turnstile,
        private readonly OdooTenantRegistrar $registrar,
        private readonly RegistrationNotifier $notifier,
    ) {
    }

    public function execute(
        RegisterTenantCommand $command,
        string $turnstileToken,
        ?string $remoteIp,
    ): RegisterTenantResult {
        if (! $this->turnstile->verify($turnstileToken, $remoteIp)) {
            return new RegisterTenantResult(
                success: false,
                code: RegistrationErrorCode::CAPTCHA_FAILED,
                message: RegistrationErrorCode::messageFor(RegistrationErrorCode::CAPTCHA_FAILED),
                status: 422,
            );
        }

        $result = $this->registrar->register($command);
        if (! $result->success) {
            return $result;
        }

        $this->notifier->notifySuccess($result);
        if (! $result->emailSent()) {
            $this->notifier->notifyMailFailed($result);
        }

        return $result;
    }
}

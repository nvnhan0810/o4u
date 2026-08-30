<?php

declare(strict_types=1);

namespace App\Domain\ErpSaasRegistration;

interface RegistrationNotifier
{
    public function notifySuccess(RegisterTenantResult $result): void;

    public function notifyMailFailed(RegisterTenantResult $result): void;
}

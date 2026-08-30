<?php

declare(strict_types=1);

namespace App\Domain\ErpSaasRegistration;

interface OdooTenantRegistrar
{
    public function register(RegisterTenantCommand $command): RegisterTenantResult;
}

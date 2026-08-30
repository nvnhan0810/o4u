<?php

declare(strict_types=1);

namespace App\Domain\ErpSaasRegistration;

final class RegisterTenantCommand
{
    /**
     * @param  list<string>  $modules
     */
    public function __construct(
        public readonly string $companyName,
        public readonly string $contactName,
        public readonly string $email,
        public readonly string $phone,
        public readonly array $modules,
        public readonly ?string $notes = null,
    ) {
    }
}

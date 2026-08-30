<?php

declare(strict_types=1);

namespace App\Domain\ErpSaasRegistration;

final class RegisterTenantResult
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly bool $success,
        public readonly ?string $code = null,
        public readonly string $message = '',
        public readonly array $data = [],
        public readonly int $status = 200,
    ) {
    }

    public function tenantUrl(): ?string
    {
        $url = $this->data['tenant_url'] ?? null;

        return is_string($url) && $url !== '' ? $url : null;
    }

    public function emailSent(): bool
    {
        return (bool) ($this->data['email_sent'] ?? false);
    }
}

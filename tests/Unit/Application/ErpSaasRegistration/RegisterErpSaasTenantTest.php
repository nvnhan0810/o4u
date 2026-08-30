<?php

declare(strict_types=1);

namespace Tests\Unit\Application\ErpSaasRegistration;

use App\Application\ErpSaasRegistration\RegisterErpSaasTenant;
use App\Domain\ErpSaasRegistration\OdooTenantRegistrar;
use App\Domain\ErpSaasRegistration\RegisterTenantCommand;
use App\Domain\ErpSaasRegistration\RegisterTenantResult;
use App\Domain\ErpSaasRegistration\RegistrationErrorCode;
use App\Domain\ErpSaasRegistration\RegistrationNotifier;
use App\Domain\ErpSaasRegistration\TurnstileVerifier;
use PHPUnit\Framework\TestCase;

class RegisterErpSaasTenantTest extends TestCase
{
    public function test_should_reject_when_captcha_fails(): void
    {
        $useCase = new RegisterErpSaasTenant(
            turnstile: new class implements TurnstileVerifier
            {
                public function verify(string $token, ?string $remoteIp): bool
                {
                    return false;
                }
            },
            registrar: new class implements OdooTenantRegistrar
            {
                public function register(RegisterTenantCommand $command): RegisterTenantResult
                {
                    throw new \RuntimeException('registrar should not be called');
                }
            },
            notifier: new class implements RegistrationNotifier
            {
                public function notifySuccess(RegisterTenantResult $result): void
                {
                    throw new \RuntimeException('notifier should not be called');
                }

                public function notifyMailFailed(RegisterTenantResult $result): void
                {
                    throw new \RuntimeException('notifier should not be called');
                }
            },
        );

        $result = $useCase->execute(
            new RegisterTenantCommand('Acme', 'An', 'a@example.com', '0901234567', ['inventory']),
            'bad-token',
            '127.0.0.1',
        );

        $this->assertFalse($result->success);
        $this->assertSame(RegistrationErrorCode::CAPTCHA_FAILED, $result->code);
    }

    public function test_should_notify_success_and_mail_failed_when_email_not_sent(): void
    {
        $notified = ['success' => 0, 'mail_failed' => 0];

        $useCase = new RegisterErpSaasTenant(
            turnstile: new class implements TurnstileVerifier
            {
                public function verify(string $token, ?string $remoteIp): bool
                {
                    return true;
                }
            },
            registrar: new class implements OdooTenantRegistrar
            {
                public function register(RegisterTenantCommand $command): RegisterTenantResult
                {
                    return new RegisterTenantResult(
                        success: true,
                        data: [
                            'tenant_url' => 'https://erp-abc.nvnhan0810.com',
                            'email' => $command->email,
                            'email_sent' => false,
                            'company_name' => $command->companyName,
                        ],
                        status: 201,
                    );
                }
            },
            notifier: new class($notified) implements RegistrationNotifier
            {
                /**
                 * @param  array{success: int, mail_failed: int}  $notified
                 */
                public function __construct(private array &$notified)
                {
                }

                public function notifySuccess(RegisterTenantResult $result): void
                {
                    $this->notified['success']++;
                }

                public function notifyMailFailed(RegisterTenantResult $result): void
                {
                    $this->notified['mail_failed']++;
                }
            },
        );

        $result = $useCase->execute(
            new RegisterTenantCommand('Acme', 'An', 'a@example.com', '0901234567', ['sale']),
            'ok',
            '127.0.0.1',
        );

        $this->assertTrue($result->success);
        $this->assertSame(1, $notified['success']);
        $this->assertSame(1, $notified['mail_failed']);
    }
}

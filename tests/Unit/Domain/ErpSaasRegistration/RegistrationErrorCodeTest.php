<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ErpSaasRegistration;

use App\Domain\ErpSaasRegistration\RegistrationErrorCode;
use PHPUnit\Framework\TestCase;

class RegistrationErrorCodeTest extends TestCase
{
    public function test_should_map_duplicate_email_to_email_field(): void
    {
        $this->assertSame(
            'email',
            RegistrationErrorCode::validationFieldFor(RegistrationErrorCode::DUPLICATE_EMAIL),
        );
    }

    public function test_should_return_vietnamese_message_for_duplicate_phone(): void
    {
        $this->assertSame(
            'Số điện thoại đã được đăng ký.',
            RegistrationErrorCode::messageFor(RegistrationErrorCode::DUPLICATE_PHONE),
        );
    }
}

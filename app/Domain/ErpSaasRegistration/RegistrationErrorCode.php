<?php

declare(strict_types=1);

namespace App\Domain\ErpSaasRegistration;

final class RegistrationErrorCode
{
    public const INVALID_KEY = 'invalid_registration_key';

    public const VALIDATION = 'validation_error';

    public const DUPLICATE_EMAIL = 'duplicate_email';

    public const DUPLICATE_PHONE = 'duplicate_phone';

    public const DUPLICATE_COMPANY = 'duplicate_company';

    public const DAILY_CAP = 'daily_cap_exceeded';

    public const PROVISION_FAILED = 'provision_failed';

    public const CAPTCHA_FAILED = 'captcha_failed';

    public const INVALID_MODULES = 'invalid_modules';

    public const UPSTREAM_UNAVAILABLE = 'upstream_unavailable';

    public const TOO_MANY_ATTEMPTS = 'too_many_attempts';

    /**
     * @return array<string, string>
     */
    public static function fieldMessages(): array
    {
        return [
            self::DUPLICATE_EMAIL => 'Email đã được đăng ký.',
            self::DUPLICATE_PHONE => 'Số điện thoại đã được đăng ký.',
            self::DUPLICATE_COMPANY => 'Tên công ty đã được đăng ký.',
            self::DAILY_CAP => 'Đã vượt giới hạn đăng ký trong ngày. Vui lòng thử lại sau.',
            self::TOO_MANY_ATTEMPTS => 'Bạn gửi quá nhiều yêu cầu. Vui lòng thử lại sau vài phút.',
            self::CAPTCHA_FAILED => 'Xác minh captcha thất bại. Vui lòng thử lại.',
            self::INVALID_MODULES => 'Vui lòng chọn từ 1 đến 2 module hợp lệ.',
            self::UPSTREAM_UNAVAILABLE => 'Hệ thống đang bận. Vui lòng thử lại sau.',
            self::PROVISION_FAILED => 'Không thể tạo tài khoản dùng thử. Vui lòng thử lại sau.',
            self::VALIDATION => 'Thông tin đăng ký không hợp lệ.',
        ];
    }

    public static function messageFor(?string $code): string
    {
        $map = self::fieldMessages();

        return $map[$code ?? ''] ?? $map[self::PROVISION_FAILED];
    }

    public static function validationFieldFor(?string $code): ?string
    {
        return match ($code) {
            self::DUPLICATE_EMAIL => 'email',
            self::DUPLICATE_PHONE => 'phone',
            self::DUPLICATE_COMPANY => 'company_name',
            self::CAPTCHA_FAILED => 'cf-turnstile-response',
            self::INVALID_MODULES => 'modules',
            default => null,
        };
    }
}

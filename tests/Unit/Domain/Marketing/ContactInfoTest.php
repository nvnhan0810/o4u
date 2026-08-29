<?php

namespace Tests\Unit\Domain\Marketing;

use App\Domain\Marketing\ContactInfo;
use PHPUnit\Framework\TestCase;

class ContactInfoTest extends TestCase
{
    public function test_should_expose_email_phone_zalo_and_qr_asset(): void
    {
        $this->assertSame('nguyenvannhan0810@gmail.com', ContactInfo::EMAIL);
        $this->assertSame('0799833537', ContactInfo::PHONE);
        $this->assertSame('tel:+84799833537', ContactInfo::PHONE_HREF);
        $this->assertSame('mailto:nguyenvannhan0810@gmail.com', ContactInfo::EMAIL_HREF);
        $this->assertSame('https://zalo.me/0799833537', ContactInfo::ZALO_HREF);
        $this->assertSame('/images/contact/zalo-qr.jpg', ContactInfo::ZALO_QR_PATH);
        $this->assertSame('Nguyễn Văn Nhàn', ContactInfo::ZALO_DISPLAY_NAME);
    }
}

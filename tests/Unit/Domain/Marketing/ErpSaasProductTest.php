<?php

namespace Tests\Unit\Domain\Marketing;

use App\Domain\Marketing\ErpSaasProduct;
use PHPUnit\Framework\TestCase;

class ErpSaasProductTest extends TestCase
{
    public function test_should_expose_internal_landing_path_and_homepage_copy(): void
    {
        $this->assertSame('/erp-saas', ErpSaasProduct::PATH);
        $this->assertSame('Sản phẩm', ErpSaasProduct::NAV_LABEL);
        $this->assertSame('ERP SaaS', ErpSaasProduct::NAME);
        $this->assertNotSame('', ErpSaasProduct::TAGLINE);
        $this->assertSame(
            [
                'key' => ErpSaasProduct::KEY,
                'name' => ErpSaasProduct::NAME,
                'tagline' => ErpSaasProduct::TAGLINE,
                'navLabel' => ErpSaasProduct::NAV_LABEL,
                'url' => ErpSaasProduct::PATH,
            ],
            ErpSaasProduct::toArray()
        );
    }
}

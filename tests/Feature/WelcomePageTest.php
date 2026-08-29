<?php

namespace Tests\Feature;

use App\Domain\Marketing\ErpSaasProduct;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config([
            'inertia.testing.page_paths' => [resource_path('ts/Pages')],
        ]);
    }

    public function test_should_share_erp_saas_product_link_on_welcome(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->has('products', 1)
                ->where('products.0.key', ErpSaasProduct::KEY)
                ->where('products.0.name', ErpSaasProduct::NAME)
                ->where('products.0.navLabel', ErpSaasProduct::NAV_LABEL)
                ->where('products.0.url', ErpSaasProduct::PATH)
                ->where('contact.phone', '0799833537')
                ->where('contact.phoneHref', 'tel:+84799833537')
                ->where('contact.email', 'nguyenvannhan0810@gmail.com')
                ->where('contact.zaloHref', 'https://zalo.me/0799833537')
                ->where('contact.zaloQrUrl', '/images/contact/zalo-qr.jpg')
                ->where('contact.zaloDisplayName', 'Nguyễn Văn Nhàn')
            );
    }
}

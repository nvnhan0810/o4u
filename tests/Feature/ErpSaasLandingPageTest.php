<?php

namespace Tests\Feature;

use App\Domain\Marketing\ErpSaasProduct;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ErpSaasLandingPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config([
            'inertia.testing.page_paths' => [resource_path('ts/Pages')],
        ]);
    }

    public function test_should_render_erp_saas_landing_on_product_path(): void
    {
        $this->get(ErpSaasProduct::PATH)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ErpSaas')
                ->where('products.0.url', ErpSaasProduct::PATH)
            );
    }
}

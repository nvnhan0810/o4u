<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Marketing\ErpSaasProduct;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ErpSaasRegisterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config([
            'inertia.testing.page_paths' => [resource_path('ts/Pages')],
            'services.turnstile.bypass' => true,
            'services.odoo_tenant.registration_url' => 'http://odoo.test/api/tenant/public/register',
            'services.odoo_tenant.registration_key' => 'test-registration-key',
            'services.telegram.bot_token' => '',
            'services.telegram.registration_chat_id' => '',
        ]);
    }

    public function test_should_share_register_config_on_erp_saas_page(): void
    {
        $this->get(ErpSaasProduct::PATH)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ErpSaas')
                ->has('erpSaasRegister.submitUrl')
            );
    }

    public function test_should_validate_required_fields(): void
    {
        $this->postJson(route('erp-saas.register'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['company_name', 'contact_name', 'email', 'phone', 'modules']);
    }

    public function test_should_reject_more_than_two_modules(): void
    {
        $this->postJson(route('erp-saas.register'), [
            'company_name' => 'Cong Ty Demo',
            'contact_name' => 'Owner',
            'email' => 'owner-mod@demo.test',
            'phone' => '0901111222',
            'modules' => ['inventory', 'sale', 'purchase'],
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['modules']);
    }

    public function test_should_provision_via_odoo_and_return_tenant_url(): void
    {
        Http::fake([
            'odoo.test/*' => Http::response([
                'success' => true,
                'message' => 'Đăng ký thành công.',
                'data' => [
                    'company_id' => 99,
                    'company_name' => 'Cong Ty Demo',
                    'domain' => 'erp-demo.nvnhan0810.com',
                    'tenant_url' => 'https://erp-demo.nvnhan0810.com',
                    'email' => 'owner@demo.test',
                    'email_sent' => true,
                    'contact_name' => 'Owner',
                    'phone' => '0901111222',
                    'modules' => ['inventory', 'sale'],
                ],
            ], 201),
        ]);

        $this->postJson(route('erp-saas.register'), [
            'company_name' => 'Cong Ty Demo',
            'contact_name' => 'Owner',
            'email' => 'owner@demo.test',
            'phone' => '0901111222',
            'modules' => ['inventory', 'sale'],
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tenant_url', 'https://erp-demo.nvnhan0810.com');

        Http::assertSent(function ($request) {
            return $request->url() === 'http://odoo.test/api/tenant/public/register'
                && $request->hasHeader('X-Registration-Key', 'test-registration-key')
                && $request['email'] === 'owner@demo.test'
                && $request['modules'] === ['inventory', 'sale'];
        });
    }

    public function test_should_map_duplicate_email_from_odoo(): void
    {
        Http::fake([
            'odoo.test/*' => Http::response([
                'success' => false,
                'message' => 'Email đã được đăng ký.',
                'code' => 'duplicate_email',
                'data' => [],
            ], 409),
        ]);

        $this->postJson(route('erp-saas.register'), [
            'company_name' => 'Cong Ty Khac',
            'contact_name' => 'Owner',
            'email' => 'taken@demo.test',
            'phone' => '0903333444',
            'modules' => ['sale'],
        ])
            ->assertStatus(422)
            ->assertJsonPath('code', 'duplicate_email')
            ->assertJsonValidationErrors(['email']);
    }
}

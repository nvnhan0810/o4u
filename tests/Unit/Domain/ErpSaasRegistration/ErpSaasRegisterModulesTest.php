<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ErpSaasRegistration;

use App\Domain\ErpSaasRegistration\ErpSaasRegisterModules;
use PHPUnit\Framework\TestCase;

class ErpSaasRegisterModulesTest extends TestCase
{
    public function test_should_normalize_and_dedupe_allowed_codes(): void
    {
        $this->assertSame(
            ['inventory', 'sale'],
            ErpSaasRegisterModules::normalize(['Inventory', 'sale', 'sale', 'unknown']),
        );
    }

    public function test_should_expose_max_two(): void
    {
        $this->assertSame(2, ErpSaasRegisterModules::MAX);
        $this->assertCount(4, ErpSaasRegisterModules::codes());
    }
}

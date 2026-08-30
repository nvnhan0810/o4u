<?php

declare(strict_types=1);

namespace App\Domain\ErpSaasRegistration;

final class ErpSaasRegisterModules
{
    public const MAX = 2;

    public const MIN = 1;

    public const INVENTORY = 'inventory';

    public const SALE = 'sale';

    public const PURCHASE = 'purchase';

    public const EMPLOYEE = 'employee';

    /**
     * @return list<array{code: string, name: string}>
     */
    public static function catalog(): array
    {
        return [
            [
                'code' => self::INVENTORY,
                'name' => 'Tồn kho',
            ],
            [
                'code' => self::SALE,
                'name' => 'Bán hàng',
            ],
            [
                'code' => self::PURCHASE,
                'name' => 'Mua hàng',
            ],
            [
                'code' => self::EMPLOYEE,
                'name' => 'Nhân viên',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_map(
            static fn (array $row): string => $row['code'],
            self::catalog(),
        );
    }

    /**
     * @param  list<mixed>  $raw
     * @return list<string>
     */
    public static function normalize(array $raw): array
    {
        $allowed = array_flip(self::codes());
        $out = [];
        foreach ($raw as $item) {
            if (! is_string($item)) {
                continue;
            }
            $code = strtolower(trim($item));
            if ($code === '' || ! isset($allowed[$code]) || in_array($code, $out, true)) {
                continue;
            }
            $out[] = $code;
        }

        return $out;
    }
}

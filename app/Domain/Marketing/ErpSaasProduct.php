<?php

namespace App\Domain\Marketing;

final class ErpSaasProduct
{
    public const KEY = 'erp_saas';

    public const NAME = 'ERP SaaS';

    public const NAV_LABEL = 'Sản phẩm';

    public const TAGLINE = 'Cổng quản trị ERP trên web cho từng doanh nghiệp';

    public const PATH = '/erp-saas';

    /**
     * @return array{
     *     key: string,
     *     name: string,
     *     tagline: string,
     *     navLabel: string,
     *     url: string
     * }
     */
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'name' => self::NAME,
            'tagline' => self::TAGLINE,
            'navLabel' => self::NAV_LABEL,
            'url' => self::PATH,
        ];
    }
}

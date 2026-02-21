<?php

declare(strict_types=1);

namespace App\Domain\Shop\Constants;

class WebStoreConstant
{
    /** WebStoreトランザクションステータス */
    public const TRANSACTION_STATUS_PENDING = 'pending';
    public const TRANSACTION_STATUS_COMPLETED = 'completed';
    public const TRANSACTION_STATUS_FAILED = 'failed';

    /** @var int この年齢以上は、購入制限なし */
    public const PURCHASE_ALLOWED_AGE = 18;

    public const SANDBOX = 'sandbox';

    /**
     * エラーメッセージ
     */

    public const ERROR_RESOURCE_POSSESSION_LIMIT_EXCEEDED = '所持上限を超えるため、購入できません';
    public const ERROR_PRODUCT_NOT_FOUND = 'こちらの商品は現在購入できません。';
    public const ERROR_USER_ACCOUNT_BAN = '現在、お客様のアカウントでは商品が購入できません。';
}

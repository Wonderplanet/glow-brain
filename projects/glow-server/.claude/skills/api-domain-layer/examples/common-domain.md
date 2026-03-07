# Commonドメインの実装例

Commonドメインは、全ドメインから参照可能な共通ファイルです。ドメインロジックに依存しない純粋な汎用クラスを配置します。

## 特徴

- **Delegatorのreturnで使用可能**（CommonEntity）
- **全ドメインから参照可能**
- **ドメインロジックに依存しない純粋な汎用クラス**
- **CommonEntity同士の相互参照は可能**

## フォルダ構成

```
api/app/Domain/Common/
├── Constants/          # 全体共通定数
├── Entities/           # 全体共通Entity（CommonEntity）
├── Enums/              # 全体共通列挙型
├── Exceptions/         # 共通例外クラス
├── Facades/            # Facadeパターン
├── Factories/          # Factoryパターン
├── Managers/           # Manager（複雑な管理ロジック）
├── Models/             # 共通モデル
├── Notifications/      # 通知関連
├── Repositories/       # 共通Repository
├── Services/           # 全体共通サービス
├── Traits/             # 全体共通トレイト
└── Utils/              # ユーティリティクラス
```

## CommonEntityの実装例

### 1. 日時範囲を表すEntity

**ファイルパス:** `api/app/Domain/Common/Entities/DateTimeRange.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities;

use Carbon\CarbonImmutable;

class DateTimeRange  // CommonEntity
{
    public function __construct(
        private CarbonImmutable $start,
        private CarbonImmutable $end,
    ) {
    }

    /**
     * 指定日時が範囲内か判定
     */
    public function contains(CarbonImmutable $dateTime): bool
    {
        return $dateTime->gte($this->start) && $dateTime->lte($this->end);
    }

    /**
     * 範囲が有効か判定
     */
    public function isValid(): bool
    {
        return $this->start->lte($this->end);
    }

    public function getStart(): CarbonImmutable
    {
        return $this->start;
    }

    public function getEnd(): CarbonImmutable
    {
        return $this->end;
    }
}
```

**使用例:**

```php
// Delegatorでのreturn（OK）
class CampaignDelegator
{
    public function getCampaignPeriod(): DateTimeRange
    {
        return new DateTimeRange($start, $end);
    }
}

// 他ドメインから使用（OK）
class ShopService
{
    public function isInCampaignPeriod(CarbonImmutable $now): bool
    {
        $period = $this->campaignDelegator->getCampaignPeriod();
        return $period->contains($now);
    }
}
```

### 2. 現在のユーザーを表すEntity

**ファイルパス:** `api/app/Domain/Common/Entities/CurrentUser.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities;

class CurrentUser  // CommonEntity
{
    public function __construct(
        private string $usrUserId,
        private string $accessToken,
    ) {
    }

    public function getUsrUserId(): string
    {
        return $this->usrUserId;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }
}
```

## Constantsの実装例

**ファイルパス:** `api/app/Domain/Common/Constants/ErrorCode.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Common\Constants;

class ErrorCode
{
    // 共通エラーコード
    public const INTERNAL_SERVER_ERROR = 'INTERNAL_SERVER_ERROR';
    public const INVALID_PARAMETER = 'INVALID_PARAMETER';
    public const UNAUTHORIZED = 'UNAUTHORIZED';

    // ユニット関連
    public const UNIT_NOT_FOUND = 'UNIT_NOT_FOUND';
    public const UNIT_LEVEL_MAX = 'UNIT_LEVEL_MAX';

    // ショップ関連
    public const SHOP_ITEM_NOT_FOUND = 'SHOP_ITEM_NOT_FOUND';
    public const SHOP_ITEM_SOLD_OUT = 'SHOP_ITEM_SOLD_OUT';

    // マスタデータ関連
    public const MASTER_DATA_NOT_FOUND = 'MASTER_DATA_NOT_FOUND';

    // ... 他の多数のエラーコード
}
```

**使用例:**

```php
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;

class UnitService
{
    public function validateUnit(string $mstUnitId): void
    {
        if (!$this->mstUnitRepository->exists($mstUnitId)) {
            throw new GameException(
                ErrorCode::UNIT_NOT_FOUND,
                sprintf('Unit not found: %s', $mstUnitId)
            );
        }
    }
}
```

## Exceptionsの実装例

**ファイルパス:** `api/app/Domain/Common/Exceptions/GameException.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Common\Exceptions;

use Exception;

class GameException extends Exception
{
    public function __construct(
        private string $errorCode,
        string $message = '',
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
```

**使用例:**

```php
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;

class ShopService
{
    public function purchaseItem(string $shopItemId): void
    {
        $item = $this->shopItemRepository->find($shopItemId);

        if ($item === null) {
            throw new GameException(
                ErrorCode::SHOP_ITEM_NOT_FOUND,
                sprintf('Shop item not found: %s', $shopItemId)
            );
        }

        // 購入処理
    }
}
```

## Utilsの実装例

**ファイルパス:** `api/app/Domain/Common/Utils/StringUtil.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Common\Utils;

use Carbon\Carbon;

class StringUtil
{
    /**
     * 日時をISO8601形式に変換
     */
    public static function convertToISO8601(string $datetime): string
    {
        return Carbon::parse($datetime)->toIso8601String();
    }

    /**
     * ランダム文字列を生成
     */
    public static function generateRandomString(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * 配列をJSON文字列に変換
     *
     * @param array<mixed> $data
     */
    public static function toJson(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
```

**使用例:**

```php
use App\Domain\Common\Utils\StringUtil;

class CurrencyPurchase
{
    public function formatToResponse(): array
    {
        return [
            'purchasePrice' => $this->purchasePrice,
            'purchaseAmount' => $this->purchaseAmount,
            'currencyCode' => $this->currencyCode,
            // StringUtilを使用
            'purchaseAt' => StringUtil::convertToISO8601($this->purchaseAt),
        ];
    }
}
```

## Enumsの実装例

**ファイルパス:** `api/app/Domain/Common/Enums/Platform.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Common\Enums;

enum Platform: int
{
    case IOS = 1;
    case ANDROID = 2;
    case WEB = 3;

    public function isIos(): bool
    {
        return $this === self::IOS;
    }

    public function isAndroid(): bool
    {
        return $this === self::ANDROID;
    }

    public function isWeb(): bool
    {
        return $this === self::WEB;
    }
}
```

**使用例:**

```php
use App\Domain\Common\Enums\Platform;

class UserService
{
    public function login(string $usrUserId, int $platformValue): void
    {
        $platform = Platform::from($platformValue);

        if ($platform->isIos()) {
            // iOS特有の処理
        } elseif ($platform->isAndroid()) {
            // Android特有の処理
        }
    }
}
```

## Traitsの実装例

**ファイルパス:** `api/app/Domain/Common/Traits/SoftDeleteTrait.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Common\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;

trait SoftDeleteTrait
{
    use SoftDeletes;

    /**
     * ソフトデリート済みか判定
     */
    public function isDeleted(): bool
    {
        return $this->trashed();
    }

    /**
     * 有効なレコードか判定
     */
    public function isActive(): bool
    {
        return !$this->trashed();
    }
}
```

## まとめ

Commonドメインの用途:

1. **CommonEntity**: ドメインロジックに依存しない純粋な値オブジェクト（Delegatorのreturnで使用可能）
2. **Constants**: 全ドメインで使用される定数（ErrorCode等）
3. **Exceptions**: 共通例外クラス（GameException等）
4. **Utils**: 全ドメインで使用されるユーティリティクラス（StringUtil等）
5. **Enums**: 全ドメインで使用される列挙型（Platform等）
6. **Traits**: 全ドメインで使用される共通トレイト

Commonドメインに配置する判断基準:
- ドメインロジックに依存しない純粋な汎用クラスか
- 全ドメインで使用される想定か
- ビジネスロジックを持たないユーティリティか
- 共通の定数や例外クラスか

**注意:**
Commonドメインは便利ですが、何でもかんでもCommonに配置しないように注意してください。特定のドメイン固有のロジックはそのドメイン内に配置し、本当に全ドメインで共有する必要がある場合のみCommonに配置します。

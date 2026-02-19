# 通常ドメインの実装例（Unit）

通常ドメインの代表的な実装例として、Unitドメインの実装を紹介します。

## 目次

- [フォルダ構成](#フォルダ構成)
- [Delegator の実装例](#delegator-の実装例)
- [Entity の実装例](#entity-の実装例)
- [Model の実装例](#model-の実装例)
- [Repository の実装例](#repository-の実装例)
- [Service の実装例](#service-の実装例)
- [UseCase の実装例](#usecase-の実装例)

## フォルダ構成

Unitドメインの実際のフォルダ構成:

```
api/app/Domain/Unit/
├── Constants/
│   └── UnitConstant.php
├── Delegators/
│   └── UnitDelegator.php
├── Enums/
│   ├── UnitGradeType.php
│   ├── UnitRankType.php
│   └── ...
├── Models/
│   ├── Eloquent/
│   │   └── ...
│   ├── UsrUnit.php
│   ├── UsrUnitInterface.php
│   ├── UsrUnitSummary.php
│   ├── UsrUnitSummaryInterface.php
│   ├── LogUnit.php
│   └── ...
├── Repositories/
│   ├── UsrUnitRepository.php
│   ├── UsrUnitSummaryRepository.php
│   ├── LogUnitRepository.php
│   └── ...
├── UseCases/
│   ├── UnitLevelUpUseCase.php
│   ├── UnitGradeUpUseCase.php
│   └── UnitRankUpUseCase.php
└── Services/
    ├── UnitService.php
    ├── UnitLevelUpService.php
    ├── UnitGradeUpService.php
    ├── UnitRankUpService.php
    └── UnitStatusService.php
```

## Delegator の実装例

Delegatorは他ドメインから呼び出される公開インターフェースです。

**ファイルパス:** `api/app/Domain/Unit/Delegators/UnitDelegator.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Unit\Delegators;

use App\Domain\Resource\Entities\CheatCheckUnit;
use App\Domain\Resource\Entities\UnitAudit;
use App\Domain\Resource\Usr\Entities\UsrUnitEntity;
use App\Domain\Unit\Repositories\UsrUnitRepository;
use App\Domain\Unit\Repositories\UsrUnitSummaryRepository;
use App\Domain\Unit\Services\UnitService;
use App\Domain\Unit\Services\UnitStatusService;
use Illuminate\Support\Collection;

class UnitDelegator
{
    public function __construct(
        private UsrUnitRepository $usrUnitRepository,
        private UsrUnitSummaryRepository $usrUnitSummaryRepository,
        private UnitService $unitService,
        private UnitStatusService $unitStatusService,
    ) {
    }

    /**
     * ユーザーの全ユニットを取得
     *
     * @param string $usrUserId
     * @return Collection<UsrUnitEntity>  ← UsrModelEntityを返す（OK）
     */
    public function getUsrUnitsByUsrUserId(string $usrUserId): Collection
    {
        $entities = collect();
        $usrModels = $this->usrUnitRepository->getListByUsrUserId($usrUserId);

        foreach ($usrModels as $usrModel) {
            // toEntity()でUsrModelEntityに変換
            $entities->push($usrModel->toEntity());
        }

        return $entities;
    }

    /**
     * 指定されたマスタIDのユニットを取得
     *
     * @return Collection<string, UsrUnitEntity> key: mst_units.id
     */
    public function getByMstUnitIds(string $usrUserId, Collection $mstUnitIds): Collection
    {
        $entities = collect();
        $usrModels = $this->usrUnitRepository->getByMstUnitIds($usrUserId, $mstUnitIds);

        foreach ($usrModels as $usrModel) {
            $entity = $usrModel->toEntity();
            $entities->put($entity->getMstUnitId(), $entity);
        }

        return $entities;
    }

    /**
     * ユニットを一括作成
     *
     * @param Collection<string> $mstUnitIds
     */
    public function bulkCreate(string $usrUserId, Collection $mstUnitIds): void
    {
        $this->unitService->bulkCreate($usrUserId, $mstUnitIds);
    }

    /**
     * ユニットデータをステータスデータに変換
     *
     * @param Collection<CheatCheckUnit> $units ← ResourceEntity（OK）
     * @return Collection<UnitAudit> ← ResourceEntity（OK）
     */
    public function convertUnitDataListToUnitStatusDataList(Collection $units): Collection
    {
        return $this->unitStatusService->convertUnitDataListToUnitStatusDataList($units);
    }

    /**
     * グレードの合計値を取得
     */
    public function getGradeLevelTotalCount(string $usrUserId): int
    {
        return $this->usrUnitSummaryRepository->getGradeLevelTotalCount($usrUserId);
    }

    /**
     * バトル回数をインクリメント
     *
     * @param Collection<string> $usrUnitIds
     */
    public function incrementBattleCount(string $usrUserId, Collection $usrUnitIds): void
    {
        $this->unitService->incrementBattleCount($usrUserId, $usrUnitIds);
    }
}
```

**ポイント:**
- UsrModelInterfaceをtoEntity()でUsrModelEntityに変換してreturn
- ResourceEntity（CheatCheckUnit、UnitAudit）はreturn可能
- voidやプリミティブ型（int）もreturn可能

## Entity の実装例

**注意:** Unitドメインには独自のDomainEntityは存在しません（ResourceEntityを使用）。他のドメインの例を参照:

**ファイルパス:** `api/app/Domain/Shop/Entities/CurrencyPurchase.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Shop\Entities;

use App\Domain\Common\Utils\StringUtil;

class CurrencyPurchase  // DomainEntity
{
    public function __construct(
        private string $purchasePrice,
        private int $purchaseAmount,
        private string $currencyCode,
        private string $purchaseAt,
    ) {
    }

    public function getPurchasePrice(): string
    {
        return $this->purchasePrice;
    }

    public function getPurchaseAmount(): int
    {
        return $this->purchaseAmount;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function getPurchaseAt(): string
    {
        return $this->purchaseAt;
    }

    /**
     * レスポンス用のフォーマットに変換
     *
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'purchasePrice' => $this->purchasePrice,
            'purchaseAmount' => $this->purchaseAmount,
            'currencyCode' => $this->currencyCode,
            'purchaseAt' => StringUtil::convertToISO8601($this->purchaseAt),
        ];
    }
}
```

**ポイント:**
- ドメイン固有のビジネスロジックを持つ（formatToResponse()）
- ドメイン外へは渡さない（Delegatorのreturnで使用禁止）
- UseCase、Service内でのみ使用

## Model の実装例

### UsrModelInterface

**ファイルパス:** `api/app/Domain/Unit/Models/UsrUnitInterface.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Unit\Models;

use App\Domain\Resource\Usr\Entities\UsrUnitEntity;

interface UsrUnitInterface
{
    public function getId(): string;
    public function getUsrUserId(): string;
    public function getMstUnitId(): string;
    public function getLevel(): int;
    public function getGrade(): int;
    public function getRank(): int;
    public function getBattleCount(): int;
    public function getIsNewEncyclopedia(): int;

    // UsrModelEntityへの変換
    public function toEntity(): UsrUnitEntity;

    // 状態変更メソッド
    public function incrementBattleCount(): void;
    public function markAsCollected(): void;
}
```

### UsrModel

**ファイルパス:** `api/app/Domain/Unit/Models/UsrUnit.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Unit\Models;

use App\Domain\Resource\Usr\Entities\UsrUnitEntity;
use Illuminate\Database\Eloquent\Model;

class UsrUnit extends Model implements UsrUnitInterface
{
    protected $connection = 'usr';
    protected $table = 'usr_units';

    protected $fillable = [
        'usr_user_id',
        'mst_unit_id',
        'level',
        'grade',
        'rank',
        // ...
    ];

    public function getId(): string
    {
        return (string)$this->id;
    }

    public function getUsrUserId(): string
    {
        return (string)$this->usr_user_id;
    }

    public function getMstUnitId(): string
    {
        return (string)$this->mst_unit_id;
    }

    public function getLevel(): int
    {
        return (int)$this->level;
    }

    // ... その他のgetter

    /**
     * UsrModelEntityへの変換
     */
    public function toEntity(): UsrUnitEntity
    {
        return new UsrUnitEntity(
            id: $this->getId(),
            usrUserId: $this->getUsrUserId(),
            mstUnitId: $this->getMstUnitId(),
            level: $this->getLevel(),
            grade: $this->getGrade(),
            rank: $this->getRank(),
            battleCount: $this->getBattleCount(),
            isNewEncyclopedia: $this->getIsNewEncyclopedia(),
        );
    }

    /**
     * バトル回数をインクリメント
     */
    public function incrementBattleCount(): void
    {
        $this->battle_count++;
    }

    /**
     * 図鑑を取得済みにする
     */
    public function markAsCollected(): void
    {
        $this->is_new_encyclopedia = 0;
    }
}
```

**ポイント:**
- UsrModelInterfaceを実装
- toEntity()でUsrModelEntityに変換
- 状態変更メソッド（incrementBattleCount()等）を提供

## Repository の実装例

**ファイルパス:** `api/app/Domain/Unit/Repositories/UsrUnitRepository.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Unit\Repositories;

use App\Domain\Unit\Models\UsrUnit;
use App\Domain\Unit\Models\UsrUnitInterface;
use Illuminate\Support\Collection;

class UsrUnitRepository
{
    /**
     * ユーザーの全ユニットを取得
     *
     * @return Collection<UsrUnitInterface>
     */
    public function getListByUsrUserId(string $usrUserId): Collection
    {
        return UsrUnit::where('usr_user_id', $usrUserId)->get();
    }

    /**
     * 指定されたマスタIDのユニットを取得
     *
     * @param Collection<string> $mstUnitIds
     * @return Collection<UsrUnitInterface>
     */
    public function getByMstUnitIds(string $usrUserId, Collection $mstUnitIds): Collection
    {
        return UsrUnit::where('usr_user_id', $usrUserId)
            ->whereIn('mst_unit_id', $mstUnitIds->toArray())
            ->get();
    }

    /**
     * ユニットを作成
     */
    public function create(string $usrUserId, string $mstUnitId): UsrUnitInterface
    {
        return UsrUnit::create([
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => $mstUnitId,
            'level' => 1,
            'grade' => 1,
            'rank' => 1,
            'battle_count' => 0,
            'is_new_encyclopedia' => 1,
        ]);
    }

    /**
     * モデルを保存
     */
    public function syncModel(UsrUnitInterface $usrUnit): void
    {
        $usrUnit->save();
    }

    /**
     * 複数モデルを保存
     *
     * @param Collection<UsrUnitInterface> $usrUnits
     */
    public function syncModels(Collection $usrUnits): void
    {
        foreach ($usrUnits as $usrUnit) {
            $usrUnit->save();
        }
    }

    /**
     * ユニットを所持しているか確認
     */
    public function isCheckUnit(string $usrUserId, string $mstUnitId): bool
    {
        return UsrUnit::where('usr_user_id', $usrUserId)
            ->where('mst_unit_id', $mstUnitId)
            ->exists();
    }
}
```

**ポイント:**
- データアクセスのみを担当（ビジネスロジックは含まない）
- UsrModelInterfaceを返す
- syncModel()でモデルを保存

## Service の実装例

**ファイルパス:** `api/app/Domain/Unit/Services/UnitService.php`（抜粋）

```php
<?php

declare(strict_types=1);

namespace App\Domain\Unit\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Repositories\MstUnitRepository;
use App\Domain\Unit\Constants\UnitConstant;
use App\Domain\Unit\Models\UsrUnitInterface;
use App\Domain\Unit\Repositories\UsrUnitRepository;
use Illuminate\Support\Collection;

class UnitService
{
    public function __construct(
        private UsrUnitRepository $usrUnitRepository,
        private MstUnitRepository $mstUnitRepository,
        private UnitGradeUpService $unitGradeUpService,
    ) {
    }

    /**
     * 指定ユニットを新規獲得する
     *
     * @param Collection<string> $mstUnitIds
     */
    public function bulkCreate(string $usrUserId, Collection $mstUnitIds): void
    {
        // 重複を除去
        $mstUnitIds = $mstUnitIds->unique();

        // マスタデータを検証
        $validMstUnits = $this->mstUnitRepository->getByIds($mstUnitIds);

        // ユニットを作成
        foreach ($validMstUnits as $mstUnit) {
            $this->usrUnitRepository->create($usrUserId, $mstUnit->getId());
        }

        // グレードレベルサマリーを更新
        $this->unitGradeUpService->addGradeLevelTotalCount(
            $usrUserId,
            $validMstUnits->count() * UnitConstant::FIRST_UNIT_GRADE_LEVEL
        );
    }

    /**
     * ユーザがユニットを所持していて、有効なユニットか確認する
     *
     * @throws GameException
     */
    public function validateHasUsrUnitByMstUnitId(string $usrUserId, string $mstUnitId): void
    {
        // マスタデータを検証
        $this->mstUnitRepository->getByIdWithError($mstUnitId);

        // 所持確認
        if (!$this->usrUnitRepository->isCheckUnit($usrUserId, $mstUnitId)) {
            throw new GameException(
                ErrorCode::UNIT_NOT_FOUND,
                sprintf('usr_units record is not found. (mst_unit_id: %s)', $mstUnitId)
            );
        }
    }

    /**
     * バトル回数をインクリメント
     *
     * @param Collection<string> $usrUnitIds
     */
    public function incrementBattleCount(string $usrUserId, Collection $usrUnitIds): void
    {
        $usrUnits = $this->usrUnitRepository->getByIds($usrUserId, $usrUnitIds);
        $usrUnits->each(fn(UsrUnitInterface $usrUnit) => $usrUnit->incrementBattleCount());
        $this->usrUnitRepository->syncModels($usrUnits);
    }
}
```

**ポイント:**
- ビジネスロジックを実装
- Repository経由でデータアクセス
- 複数Repositoryやサービスを組み合わせる

## UseCase の実装例

**ファイルパス:** `api/app/Domain/Unit/UseCases/UnitLevelUpUseCase.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Unit\UseCases;

use App\Domain\Unit\Services\UnitLevelUpService;
use Illuminate\Support\Facades\DB;

class UnitLevelUpUseCase
{
    public function __construct(
        private UnitLevelUpService $unitLevelUpService,
    ) {
    }

    /**
     * ユニットのレベルアップを実行
     *
     * @param string $usrUserId
     * @param string $usrUnitId
     * @return void
     */
    public function __invoke(string $usrUserId, string $usrUnitId): void
    {
        DB::transaction(function () use ($usrUserId, $usrUnitId) {
            $this->unitLevelUpService->levelUp($usrUserId, $usrUnitId);
        });
    }
}
```

**ポイント:**
- Controllerから呼び出される
- トランザクション管理
- Serviceに処理を委譲

## まとめ

通常ドメイン（Unit）の実装パターン:

1. **Delegator**: 他ドメインへの公開インターフェース。UsrModelEntityに変換してreturn。
2. **Model**: UsrModelInterfaceを実装。toEntity()でUsrModelEntityに変換。
3. **Repository**: データアクセスのみ。UsrModelInterfaceを返す。
4. **Service**: ビジネスロジックを実装。Repository経由でデータアクセス。
5. **UseCase**: Controllerから呼び出され、トランザクション管理。Serviceに処理を委譲。

この構造により、ドメイン境界が明確になり、テストしやすく保守性の高いコードになります。

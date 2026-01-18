# Resourceドメインの実装例

Resourceドメインは、特定の複数ドメインから参照可能な共通ファイルです。マスタデータリポジトリやユーザーデータの共通Entityを含みます。

## 特徴

- **Delegatorのreturnで使用可能**（ResourceEntity、MstModelEntity、UsrModelEntity）
- **全ドメインから参照可能**
- **マスタデータ（Mst）は全ドメイン共有可能**

## フォルダ構成

```
api/app/Domain/Resource/
├── Constants/          # 共通定数
├── Dtos/               # データ転送オブジェクト
├── Dyn/                # 動的データ
├── Entities/           # 共通Entity（ResourceEntity）
├── Enums/              # 共通列挙型
├── Traits/             # 共通トレイト
├── Mst/                # マスタデータ（全ドメイン共通）
│   ├── Models/         # マスタデータのEloquent Model
│   ├── Repositories/   # マスタデータのRepository
│   ├── Entities/       # マスタデータのEntity（MstModelEntity）
│   ├── Services/
│   └── Traits/
├── Usr/                # ユーザーデータの共通Entity
│   ├── Entities/       # UsrModelEntity（Delegatorのreturnで使用可）
│   ├── Models/
│   ├── Repositories/
│   └── Services/
├── Log/                # ログデータの共通Repository
│   └── Repositories/
├── Mng/                # 管理データの共通Repository
│   └── Repositories/
└── Sys/                # システムデータの共通Repository
    └── Repositories/
```

## ResourceEntityの実装例

複数ドメインで共有されるEntity。Delegatorのreturnで使用可能。

**ファイルパス:** `api/app/Domain/Resource/Entities/CheatCheckUnit.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

class CheatCheckUnit  // ResourceEntity
{
    public function __construct(
        private string $usrUnitId,
        private int $level,
        private int $grade,
        private int $rank,
    ) {
    }

    public function getUsrUnitId(): string
    {
        return $this->usrUnitId;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getGrade(): int
    {
        return $this->grade;
    }

    public function getRank(): int
    {
        return $this->rank;
    }
}
```

**使用例（Delegatorでのreturn）:**

```php
class UnitDelegator
{
    /**
     * @return Collection<CheatCheckUnit>  ← ResourceEntity（OK）
     */
    public function getCheatCheckUnits(string $usrUserId): Collection
    {
        return $this->unitService->getCheatCheckUnits($usrUserId);
    }
}

// 他ドメインから使用
class CheatService
{
    public function checkUnits(string $usrUserId): void
    {
        // ResourceEntityを取得（OK）
        $units = $this->unitDelegator->getCheatCheckUnits($usrUserId);
    }
}
```

## MstModelEntityの実装例

マスタデータのEntity。全ドメインから参照可能。

**ファイルパス:** `api/app/Domain/Resource/Mst/Entities/MstUnitEntity.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstUnitEntity  // MstModelEntity
{
    public function __construct(
        private string $id,
        private string $unitLabel,
        private string $fragmentMstItemId,
        private int $rarity,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUnitLabel(): string
    {
        return $this->unitLabel;
    }

    public function getFragmentMstItemId(): string
    {
        return $this->fragmentMstItemId;
    }

    public function getRarity(): int
    {
        return $this->rarity;
    }
}
```

**MstModelの実装例:**

**ファイルパス:** `api/app/Domain/Resource/Mst/Models/MstUnit.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstUnitEntity;
use Illuminate\Database\Eloquent\Model;

class MstUnit extends Model
{
    protected $connection = 'mst';
    protected $table = 'mst_units';

    public function getId(): string
    {
        return (string)$this->id;
    }

    public function getUnitLabel(): string
    {
        return $this->unit_label;
    }

    public function getFragmentMstItemId(): string
    {
        return (string)$this->fragment_mst_item_id;
    }

    public function getRarity(): int
    {
        return (int)$this->rarity;
    }

    public function toEntity(): MstUnitEntity
    {
        return new MstUnitEntity(
            id: $this->getId(),
            unitLabel: $this->getUnitLabel(),
            fragmentMstItemId: $this->getFragmentMstItemId(),
            rarity: $this->getRarity(),
        );
    }
}
```

**MstRepositoryの実装例:**

**ファイルパス:** `api/app/Domain/Resource/Mst/Repositories/MstUnitRepository.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstUnitEntity;
use App\Domain\Resource\Mst\Models\MstUnit;
use Illuminate\Support\Collection;

class MstUnitRepository
{
    /**
     * IDでマスタデータを取得
     *
     * @param Collection<string> $ids
     * @return Collection<string, MstUnitEntity> key: id
     */
    public function getByIds(Collection $ids): Collection
    {
        $models = MstUnit::whereIn('id', $ids->toArray())->get();

        return $models->mapWithKeys(function (MstUnit $model) {
            $entity = $model->toEntity();
            return [$entity->getId() => $entity];
        });
    }

    /**
     * IDでマスタデータを取得（存在しない場合はエラー）
     *
     * @throws GameException
     */
    public function getByIdWithError(string $id): MstUnitEntity
    {
        $model = MstUnit::find($id);

        if ($model === null) {
            throw new GameException(
                ErrorCode::MASTER_DATA_NOT_FOUND,
                sprintf('mst_units record is not found. (id: %s)', $id)
            );
        }

        return $model->toEntity();
    }
}
```

## UsrModelEntityの実装例

ユーザーデータの共通Entity。Delegatorのreturnで使用可能。

**ファイルパス:** `api/app/Domain/Resource/Usr/Entities/UsrUnitEntity.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Entities;

class UsrUnitEntity  // UsrModelEntity
{
    public function __construct(
        private string $id,
        private string $usrUserId,
        private string $mstUnitId,
        private int $level,
        private int $grade,
        private int $rank,
        private int $battleCount,
        private int $isNewEncyclopedia,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsrUserId(): string
    {
        return $this->usrUserId;
    }

    public function getMstUnitId(): string
    {
        return $this->mstUnitId;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    // ... その他のgetter
}
```

**UsrModelからUsrModelEntityへの変換:**

```php
// api/app/Domain/Unit/Models/UsrUnit.php
class UsrUnit extends Model implements UsrUnitInterface
{
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
}

// Delegatorでの使用
class UnitDelegator
{
    /**
     * @return Collection<UsrUnitEntity>  ← UsrModelEntity（OK）
     */
    public function getUsrUnits(string $usrUserId): Collection
    {
        $usrModels = $this->usrUnitRepository->getListByUsrUserId($usrUserId);
        return $usrModels->map(fn($model) => $model->toEntity());
    }
}
```

## Dtoの実装例

データ転送オブジェクト。

**ファイルパス:** `api/app/Domain/Resource/Dtos/RewardDto.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Resource\Dtos;

class RewardDto
{
    public function __construct(
        private int $type,
        private string $resourceId,
        private int $amount,
    ) {
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }
}
```

## まとめ

Resourceドメインの用途:

1. **ResourceEntity**: 複数ドメインで共有されるEntity（Delegatorのreturnで使用可能）
2. **MstModelEntity**: マスタデータのEntity（全ドメインから参照可能、Delegatorのreturnで使用可能）
3. **UsrModelEntity**: ユーザーデータの共通Entity（Delegatorのreturnで使用可能）
4. **Dto**: データ転送オブジェクト
5. **各DBのRepository**: Log、Mng、Sys等のデータベースアクセス

Resourceドメインに配置する判断基準:
- 複数ドメインで共有される想定か
- Delegatorのreturnで使用する想定か
- マスタデータやユーザーデータの共通Entityか

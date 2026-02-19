---
applyTo: "api/app/Domain/**/*.{php}"
---

# テーブルデータに関するクラス作成指示書

## 共通方針
- DB接頭辞ごとに、モデル・リポジトリ・エンティティの配置場所・命名規則が異なるので、下記ルールに従うこと。
- 既存の各種ファイルの記述例・設計思想を踏襲すること。

## 1. DB接頭辞ごとの配置ルール
- **mst, opr（マスタ系）**
  - モデル・リポジトリ・エンティティは `api/app/Domain/Resource/Mst/` 配下に配置。
  - Entityクラスを必ず用意し、`Entities` ディレクトリに配置。
  - Repositoryクラスは `Repositories` ディレクトリに配置。
- **usr, log（ユーザ・ログ系）**
  - モデルは `api/app/Domain/Xxx/Models/` 配下。
  - リポジトリは `api/app/Domain/Xxx/Repositories/` 配下。
  - Entityクラスは原則不要。

## 2. モデルクラスの記述例
- モデルは `protected string $modelClass = XxxModel::class;` で自身のクラスを指定。
- Usr系は **基本的に `UsrEloquentModel` 抽象クラスを継承する**。
    - ただし、EloquentModelを外してパフォーマンス改善を図りたい場合のみ、`UsrModel` 抽象クラスを継承する。
- `UsrEloquentModel` を継承する場合は `modelKeyColumns` の指定は不要。
- `UsrModel` を継承する場合のみ `modelKeyColumns` で主キー列を指定。
- マスタ系はEloquentモデルを継承し、`toEntity()` でEntity変換を実装。

### Usr系モデル例（基本パターン）
```php
namespace App\Domain\Item\Models;

use App\Domain\Resource\Usr\Models\UsrEloquentModel;

class UsrItem extends UsrEloquentModel
{
    // ...
}
```

### Usr系モデル例（パフォーマンス重視・Eloquent非継承パターン）
```php
namespace App\Domain\Item\Models;

use App\Domain\Resource\Usr\Models\UsrModel;

class UsrItem extends UsrModel
{
    protected array $modelKeyColumns = ['usr_user_id', 'mst_item_id'];
    // ...
}
```

### マスタ系モデル例
```php
namespace App\Domain\Resource\Mst\Models;

use Illuminate\Database\Eloquent\Model;

class MstItem extends Model
{
    public function toEntity(): MstItemEntity
    {
        return new MstItemEntity(
            $this->id,
            $this->name,
            // ...
        );
    }
}
```

## 3. リポジトリクラスの記述例
- Usr系は `UsrModelSingleCacheRepository` または `UsrModelMultiCacheRepository` を継承。
- マスタ系は `MasterRepository` を継承。
- `protected string $modelClass` でモデルクラスを指定。
- `saveModels(Collection $models)` で一括保存ロジックを実装（必要な場合）。

### Usr系リポジトリ例
```php
namespace App\Domain\Item\Repositories;

use App\Domain\Item\Models\UsrItem;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Illuminate\Support\Collection;

class UsrItemRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrItem::class;

    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrItem $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                // ...
            ];
        })->toArray();
        // ...
    }
}
```

### マスタ系リポジトリ例
```php
namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Models\MstItem;
use App\Infrastructure\MasterRepository;

class MstItemRepository extends MasterRepository
{
    protected string $modelClass = MstItem::class;
    // ...
}
```

## 4. Entityクラスの記述例（マスタ系のみ）
- 不変値として `readonly` プロパティを持つ。
- コンストラクタで全プロパティを初期化。
- getterのみ実装。

```php
namespace App\Domain\Resource\Mst\Entities;

class MstItemEntity
{
    public function __construct(
        readonly private string $id,
        readonly private string $name,
        // ...
    ) {}

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    // ...
}
```

## 5. その他
- モデル・リポジトリ・エンティティの命名・配置・継承階層は、既存実装に必ず合わせること。
- キャッシュ・一括保存・Entity変換などの仕組みも、既存の親クラス・トレイトを活用すること。
- 既存の各種Repository/Model/Entityのサンプルを参照し、同じ設計思想・コーディングスタイルで実装すること。

---

この指示書に従い、テーブルデータに関するクラスを作成してください。

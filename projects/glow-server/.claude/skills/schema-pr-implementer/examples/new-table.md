# 実装例: 新規テーブル作成

このドキュメントでは、新規テーブル作成の実際のPR実装例を紹介します。

## PR #1873: ガシャ履歴テーブル作成

- **glow-server PR**: https://github.com/Wonderplanet/glow-server/pull/1873
- **glow-schema PR**: https://github.com/Wonderplanet/glow-schema/pull/474
- **実装内容**: ガシャ履歴APIの追加に伴う、Momentoキャッシュベースの履歴管理実装

### glow-schema PR #474の変更内容

新しいAPI定義の追加（テーブルは作成されず、キャッシュベース）:

```yaml
# API定義追加
api/gacha/history:
  description: ガシャ履歴取得API
  response:
    gachaHistories:
      type: array
      items:
        - oprGachaId: string
        - costType: string
        - costId: string
        - costNum: int
        - drawCount: int
        - playedAt: datetime
        - results: array
```

### glow-server PR #1873での実装

#### 1. Entityクラス作成

**ファイル**: `api/app/Domain/Gacha/Entities/GachaHistory.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Entities;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * ガシャ1回分(n連)の履歴
 */
class GachaHistory
{
    public function __construct(
        private string $oprGachaId,
        private string $costType,
        private ?string $costId,
        private int $costNum,
        private int $drawCount,
        private CarbonImmutable $playedAt,
        private Collection $results
    ) {
    }

    public function getPlayedAt(): CarbonImmutable
    {
        return $this->playedAt;
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'oprGachaId' => $this->oprGachaId,
            'costType' => $this->costType,
            'costId' => $this->costId,
            'costNum' => $this->costNum,
            'drawCount' => $this->drawCount,
            'playedAt' => StringUtil::convertToISO8601($this->playedAt->toDateTimeString()),
            'results' => $this->results->map(fn(GachaReward $result) => $result->formatToResponse())->toArray(),
        ];
    }
}
```

#### 2. Serviceクラス作成（キャッシュ管理）

**ファイル**: `api/app/Domain/Gacha/Services/GachaCacheService.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Services;

use App\Domain\Common\Managers\Cache\CacheClientManager;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Gacha\Constants\GachaConstants;
use App\Domain\Gacha\Entities\GachaHistory;
use Illuminate\Support\Collection;

class GachaCacheService
{
    public function __construct(
        private CacheClientManager $cacheClientManager,
    ) {
    }

    /**
     * ガシャ履歴の先頭にデータを追加する
     */
    public function prependGachaHistory(string $usrUserId, GachaHistory $gachaHistory): void
    {
        $cacheKey = CacheKeyUtil::getGachaHistoryKey($usrUserId);
        $gachaHistories = $this->getGachaHistories($usrUserId) ?? collect();

        // 先頭に追加(常に先頭が最新の履歴で末尾が古い履歴)
        $gachaHistories->prepend($gachaHistory);

        // 表示上限を超えた場合は上限までに調整
        if (GachaConstants::GACHA_HISTORY_LIMIT < $gachaHistories->count()) {
            $gachaHistories = $gachaHistories->take(GachaConstants::GACHA_HISTORY_LIMIT);
        }

        // 履歴の期限をttlとして設定する
        $ttl = GachaConstants::HISTORY_DAYS * 24 * 60 * 60;
        $this->cacheClientManager->getCacheClient()->set($cacheKey, $gachaHistories, $ttl);
    }

    /**
     * @return Collection|null
     */
    public function getGachaHistories(string $usrUserId): ?Collection
    {
        $cacheKey = CacheKeyUtil::getGachaHistoryKey($usrUserId);
        return $this->cacheClientManager->getCacheClient()->get($cacheKey);
    }
}
```

#### 3. Constantsクラス更新

**ファイル**: `api/app/Domain/Gacha/Constants/GachaConstants.php`

```php
class GachaConstants
{
    // ... 既存定数

    /**
     * ガシャ履歴の最大保持数
     */
    public const GACHA_HISTORY_LIMIT = 50;

    /**
     * ガシャ履歴の保持期間(日)
     */
    public const HISTORY_DAYS = 7;
}
```

#### 4. CacheKeyUtil更新

**ファイル**: `api/app/Domain/Common/Utils/CacheKeyUtil.php`

```php
class CacheKeyUtil
{
    // ... 既存メソッド

    /**
     * ガシャ履歴のキャッシュキー
     */
    public static function getGachaHistoryKey(string $usrUserId): string
    {
        return "gacha_history:user:{$usrUserId}";
    }
}
```

### 重要なポイント

#### キャッシュベース vs DB永続化

このPRでは **キャッシュベース** で履歴を管理しています：

**✅ キャッシュベースの利点:**
- 高速な読み書き
- TTLによる自動削除
- DBへの負荷軽減

**⚠️ 注意点:**
- データの永続性がない
- キャッシュクリア時にデータ消失
- 履歴の長期保存には不向き

#### もしDBテーブルとして実装する場合

```php
// マイグレーションファイル例
// api/database/migrations/usr/2025_XX_XX_create_usr_gacha_histories_table.php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::USR_CONNECTION;  // ← usrDB

    public function up(): void
    {
        Schema::create('usr_gacha_histories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usr_user_id')->index();
            $table->string('opr_gacha_id');
            $table->string('cost_type');
            $table->string('cost_id')->nullable();
            $table->integer('cost_num');
            $table->integer('draw_count');
            $table->dateTime('played_at');
            $table->json('results');  // ガシャ結果をJSON保存
            $table->dateTime('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usr_gacha_histories');
    }
};
```

### 新規テーブル作成のチェックリスト

- [ ] 対象DB（mst/mng/usr/log/sys）を確認
- [ ] マイグレーションファイルを作成（DB永続化の場合）
- [ ] Entityクラスを作成
- [ ] Repositoryインターフェースを作成（DB永続化の場合）
- [ ] Repository実装クラスを作成（DB永続化の場合）
- [ ] ServiceProviderに登録（DB永続化の場合）
- [ ] Serviceクラスを作成（必要に応じて）
- [ ] Resourceクラスを作成（APIレスポンス用）
- [ ] Constantsを追加（必要に応じて）
- [ ] マイグレーション実行確認（DB永続化の場合）
- [ ] テスト実装

---

## PR #892: チュートリアルTipsテーブル追加

- **glow-server PR**: https://github.com/Wonderplanet/glow-server/pull/892
- **実装内容**: mst_tutorial_tips_i18nテーブルの新規作成

### 実装の特徴

- **DB**: mst (マスターDB)
- **テーブルタイプ**: i18n（多言語対応テーブル）
- **用途**: チュートリアルTipsのテキストデータ管理

### マイグレーションファイル

```php
// api/database/migrations/mst/2025_XX_XX_create_mst_tutorial_tips_i18n_table.php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    public function up(): void
    {
        Schema::create('mst_tutorial_tips_i18n', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('mst_tutorial_tip_id');
            $table->string('language_type');
            $table->text('tips_text');

            // 複合ユニークキー
            $table->unique(['mst_tutorial_tip_id', 'language_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_tutorial_tips_i18n');
    }
};
```

### i18nテーブルのパターン

i18n（国際化）テーブルは以下の特徴があります：

```
命名規則: {base_table_name}_i18n
例: mst_tutorial_tips_i18n

必須カラム:
- id (主キー)
- {base_table_name}_id (親テーブルへの参照)
- language_type (言語識別子)
- {multilingual_fields} (多言語化するフィールド)

複合ユニークキー:
- ({base_table_name}_id, language_type)
```

### チェックリスト（i18nテーブル追加時）

- [ ] テーブル名が`_i18n`サフィックスを持つ
- [ ] `language_type`カラムがある
- [ ] 複合ユニークキーが設定されている
- [ ] 親テーブルへの参照カラムがある
- [ ] Entityクラスが作成されている
- [ ] 親テーブルとのリレーションが適切に設定されている

---

## まとめ

新規テーブル作成では以下を意識してください：

1. **データ保存方法の選択**: DB永続化 vs キャッシュベース
2. **対象DBの特定**: mst/mng/usr/log/sys
3. **テーブルタイプ**: 通常テーブル vs i18nテーブル
4. **必要なクラスの作成**: Entity、Repository、Service、Resource
5. **マイグレーションの正確性**: up()とdown()の両方を実装

詳細な実装パターンは [patterns.md](../guides/patterns.md#1-新規テーブル作成) を参照してください。

# mst/mng接続のマイグレーション実装例

## 概要

- **DB接続**: mst (MySQL), mng (MySQL)
- **ファイル配置**: `api/database/migrations/mst/`, `api/database/migrations/mng/`
- **$connectionプロパティ**: ✅ **必須** (`Database::MST_CONNECTION`, `Database::MNG_CONNECTION`)
- **テーブル接頭辞**: `mst_*`, `opr_*`, `mng_*`

**重要**: すべてのDB接続で共通のルール（timestampTz使用、created_at/updated_atの配置、after()指定など）は [common-rules.md](common-rules.md) を参照してください。

## テーブル作成の実装例

### 例1: mst接続 - PVP関連テーブル作成（複数テーブル）

**ファイル**: `api/database/migrations/mst/2025_06_03_024458_create_table_mst_pvps.php`

このマイグレーションは、PVP機能に関連する複数のテーブルを一度に作成します。

```php
<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $rankClassTypes = [
            'Bronze',
            'Silver',
            'Gold',
            'Platinum',
        ];

        $pvpRewardCategoryTypes = [
            'Ranking',
            'RankClass',
        ];

        $resourceTypes = [
            'Coin',
            'FreeDiamond',
            'Item',
            'Emblem',
        ];

        $pvpBonusTypes = [
            'ClearTime',
            'WinOverBonus',
            'WinNormalBonus',
            'WinUnderBonus',
        ];

        // mst_pvps.idはデフォルトデータであるid = default_pvp(11文字)を考慮して16文字に設定
        // 上書き設定などは西暦4桁と週番号2桁を使った自動採番IDを使用し、最大8文字を想定している。
        Schema::create('mst_pvps', function (Blueprint $table) use ($rankClassTypes) {
            $table->string('id', 16)->primary()->comment('西暦4桁と週番号2桁を使った自動採番IDを使用');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('reward_group_id', 255)->comment('mst_pvp_reward_groups.id');
            $table->enum('ranking_min_pvp_rank_class', $rankClassTypes)->nullable()->comment('ランキングに含む最小PVPランク区分');
            $table->unsignedInteger('max_daily_challenge_count')->default(0)->comment('1日のアイテム消費なし挑戦可能回数');
            $table->unsignedInteger('max_daily_item_challenge_count')->default(0)->comment('1日のアイテム消費あり挑戦可能回数');
            $table->unsignedInteger('item_challenge_cost_amount')->default(0)->comment('アイテム消費あり挑戦時の消費アイテム数');

            $table->comment('PVP情報のマスターテーブル');
        });

        // i18nテーブルは複数形にしない（mst_pvps_i18ns ではなく mst_pvps_i18n）
        Schema::create('mst_pvps_i18n', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('id');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('mst_pvp_id', 16)->comment('mst_pvps.id');
            $table->enum('language', ['ja'])->default('ja')->comment('言語');
            $table->string('name', 255)->nullable()->comment('PVP名');
            $table->string('description', 255)->default('')->comment('PVP説明');

            $table->unique(['mst_pvp_id', 'language'], 'mst_pvps_i18n_unique');
            $table->comment('PVP情報の多言語対応テーブル');
        });

        Schema::create('mst_pvp_reward_groups', function (Blueprint $table) use ($pvpRewardCategoryTypes) {
            $table->string('id', 255)->primary()->comment('id');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->enum('pvp_reward_category', $pvpRewardCategoryTypes)->comment('PVP報酬カテゴリ');
            $table->string('condition_value', 255)->comment('報酬条件値');

            $table->comment('PVP報酬グループのマスターテーブル');
        });

        Schema::create('mst_pvp_rewards', function (Blueprint $table) use ($resourceTypes) {
            $table->string('id', 255)->primary()->comment('id');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('mst_pvp_reward_group_id', 255)->comment('mst_pvp_reward_groups.id');
            $table->enum('resource_type', $resourceTypes)->comment('報酬タイプ');
            $table->string('resource_id', 255)->nullable()->comment('報酬ID');
            $table->unsignedInteger('resource_amount')->default(0)->comment('報酬数');

            $table->index('mst_pvp_reward_group_id');
            $table->comment('PVP報酬のマスターテーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_pvps');
        Schema::dropIfExists('mst_pvps_i18n');
        Schema::dropIfExists('mst_pvp_reward_groups');
        Schema::dropIfExists('mst_pvp_rewards');
    }
};
```

**ポイント**:
- `protected $connection = Database::MST_CONNECTION;` が必須
- i18nテーブルは `mst_pvps_i18n` で複数形にしない
- enum型の値を変数で定義して再利用可能に
- 複数テーブルを一度に作成する場合、drop時は作成順の逆順で削除

### 例2: mng接続 - メッセージ管理テーブル作成

**ファイル**: `api/database/migrations/mng/2025_05_07_120000_create_mng_messages_tables.php`

```php
<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = Database::MNG_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mng_message_rewards', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('mng_message_id')->comment('mng_messages.id');
            $table->enum('resource_type', ['Exp', 'Coin', 'FreeDiamond', 'Item', 'Emblem', 'Stamina', 'Unit'])->comment('リソースタイプ');
            $table->string('resource_id')->nullable()->comment('リソースID');
            $table->unsignedInteger('resource_amount')->nullable()->comment('リソース数量');
            $table->unsignedInteger('display_order')->comment('表示順');
            $table->unique(['mng_message_id', 'display_order'], 'uk_mng_message_id_display_order');
            $table->comment('メッセージ報酬テーブル');
        });

        Schema::create('mng_messages', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->timestamp('start_at')->comment('配布開始時日時');
            $table->timestamp('expired_at')->comment('表示期限日時');
            $table->enum('type', ['All', 'Individual'])->comment('配布種別');
            $table->timestamp('account_created_start_at')->nullable()->comment('全体配布条件とするアカウント作成日時(開始)');
            $table->timestamp('account_created_end_at')->nullable()->comment('全体配布条件とするアカウント作成日時(終了)');
            $table->integer('add_expired_days')->comment('ユーザー受け取り日時加算日数');
            $table->comment('メッセージ管理テーブル');
        });

        // i18nテーブルは複数形にしない
        Schema::create('mng_messages_i18n', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');
            $table->string('mng_message_id')->comment('mng_messages.id');
            $table->enum('language', ['ja'])->default('ja')->comment('言語');
            $table->text('title')->comment('タイトル');
            $table->text('body')->comment('本文');
            $table->unique(['mng_message_id', 'language'], 'uk_mng_message_id_language');
            $table->comment('メッセージ多言語テーブル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mng_message_rewards');
        Schema::dropIfExists('mng_messages_i18n');
        Schema::dropIfExists('mng_messages');
    }
};
```

**ポイント**:
- `protected $connection = Database::MNG_CONNECTION;` が必須
- `mng_messages_i18n` は複数形にしない（i18nテーブルの例外ルール）
- unique制約に分かりやすい名前を付ける（`uk_mng_message_id_language`）
- timestamp型を使用（mng接続ではtimestampでも可だが、新規作成時はtimestampTz推奨）

## コマンド実行

### マイグレーション作成

```bash
# glow-serverルートディレクトリから実行

# mst用マイグレーション作成
sail artisan make:migration create_mst_pvps_table

# 作成されたファイルをmstサブディレクトリに移動
mv api/database/migrations/20XX_XX_XX_XXXXXX_create_mst_pvps_table.php \
   api/database/migrations/mst/

# ファイルを編集して$connectionプロパティを追加
# protected $connection = Database::MST_CONNECTION;
```

### マイグレーション実行

```bash
# glow-serverルートディレクトリから実行

# mst接続でマイグレーション実行
sail artisan migrate --database=mst

# mng接続でマイグレーション実行
sail artisan migrate --database=mng

# ドライラン（SQLを確認するだけ）
sail artisan migrate --database=mst --pretend

# ステータス確認
sail artisan migrate:status --database=mst
```

## チェックポイント

- [ ] `protected $connection` プロパティを正しく設定しているか
- [ ] ファイルを適切なサブディレクトリ（mst/ または mng/）に配置しているか
- [ ] テーブル名に正しい接頭辞（mst_/opr_/mng_）を付けているか
- [ ] i18nテーブルは複数形にしていないか（`_i18n`で終わる）
- [ ] down()メソッドで適切にテーブルを削除しているか

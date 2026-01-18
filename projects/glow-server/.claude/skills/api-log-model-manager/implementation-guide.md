# 新規ログテーブル追加 実装ガイド

新しいログテーブルを追加する際の実装手順を説明します。

## 目次

- [実装の全体フロー](#実装の全体フロー)
- [ステップ1: Migrationファイル作成](#ステップ1-migrationファイル作成)
- [ステップ2: Modelクラス作成](#ステップ2-modelクラス作成)
- [ステップ3: Repositoryクラス作成](#ステップ3-repositoryクラス作成)
- [ステップ4: 動作確認](#ステップ4-動作確認)
- [チェックリスト](#チェックリスト)

## 実装の全体フロー

新規ログテーブルを追加するには、以下の3つのファイルを作成します：

```
1. Migrationファイル (api/database/migrations/)
   ↓
2. Modelクラス (api/app/Domain/{Domain}/Models/)
   ↓
3. Repositoryクラス (api/app/Domain/{Domain}/Repositories/)
```

## ステップ1: Migrationファイル作成

### 基本テンプレート

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('log_xxx', function (Blueprint $table) {
            // 必須カラム
            $table->string('id')->primary()->comment('主キー');
            $table->string('usr_user_id')->comment('usr_users.id');
            $table->string('nginx_request_id')->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id')->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');

            // 独自カラム（例）
            $table->string('action_type')->comment('Get: 獲得 Use: 消費');
            $table->unsignedInteger('amount')->default(0)->comment('変動数');

            // タイムスタンプ
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_xxx');
    }
};
```

### 必須カラム

すべてのログテーブルには以下のカラムが**必須**です：

| カラム名 | 型 | 説明 |
|---------|-----|------|
| id | string (primary) | 主キー（UUID v4） |
| usr_user_id | string | ユーザーID |
| nginx_request_id | string | Nginxが生成するリクエストID |
| request_id | string | クライアントが生成するリクエストID |
| logging_no | integer | APIリクエスト中のログ順番 |
| created_at | timestampTz | 作成日時 |
| updated_at | timestampTz | 更新日時 |

### インデックス設計

ログテーブルには、以下のインデックスを追加することを推奨します：

```php
// usr_user_id にインデックス
$table->index('usr_user_id');

// created_at にインデックス（検索用）
$table->index('created_at');
```

別のMigrationファイルでインデックスを追加する例：

```php
Schema::table('log_xxx', function (Blueprint $table) {
    $table->index('usr_user_id');
    $table->index('created_at');
});
```

### 実例: log_logins

**ファイル**: `api/database/migrations/2025_04_14_071059_create_log_logins_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('log_logins', function (Blueprint $table) {
            $table->string('id')->primary()->comment('主キー');
            $table->string('usr_user_id')->comment('usr_users.id');
            $table->string('nginx_request_id')->comment('APIリクエスト単位でNginxにて生成されるユニークID');
            $table->string('request_id')->comment('APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID');
            $table->integer('logging_no')->comment('APIリクエスト中でのログの順番');
            $table->integer('login_count')->comment('ログイン回数');
            $table->smallInteger('is_day_first_login')->comment('1日の最初のログインかどうかのフラグ (1: 初ログイン, 0: ログイン2回目以降)');
            $table->integer('login_day_count')->comment('ログイン日数');
            $table->integer('login_continue_day_count')->comment('連続ログイン日数');
            $table->integer('comeback_day_count')->comment('最終ログインから復帰にかかった日数');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_logins');
    }
};
```

### Migrationの実行

```bash
./tools/bin/sail-wp migrate
```

## ステップ2: Modelクラス作成

### 基本テンプレート

```php
<?php

declare(strict_types=1);

namespace App\Domain\{Domain}\Models;

use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property int $logging_no
 * @property string $nginx_request_id
 * @property string $request_id
 * @property string $action_type
 * @property int $amount
 */
class LogXxx extends LogModel
{
    use HasFactory;

    public function setActionType(string $actionType): void
    {
        $this->action_type = $actionType;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }
}
```

### 実装ポイント

#### 1. LogModelを継承

```php
class LogXxx extends LogModel
```

LogModelを継承することで、以下が自動的に設定されます：

- TiDB接続（`$connection = Database::TIDB_CONNECTION`）
- UUID主キー（`$keyType = 'string'`, `$incrementing = false`）
- 日付フォーマット（`$dateFormat = 'Y-m-d H:i:s.'`）

#### 2. HasFactoryトレイトを使用

```php
use HasFactory;
```

テストでFactoryを使ってモデルを生成できるようにします。

#### 3. プロパティをdocコメントで定義

```php
/**
 * @property string $id
 * @property string $usr_user_id
 * @property int $logging_no
 * @property string $nginx_request_id
 * @property string $request_id
 * @property string $action_type  // 独自カラム
 * @property int $amount           // 独自カラム
 */
```

IDEの補完が効くようになります。

#### 4. setterメソッドを実装

各プロパティに対してsetterメソッドを実装します。

```php
public function setActionType(string $actionType): void
{
    $this->action_type = $actionType;
}

public function setAmount(int $amount): void
{
    $this->amount = $amount;
}
```

**命名規則:**
- メソッド名: `set{PropertyName}`（キャメルケース）
- プロパティ名: `{property_name}`（スネークケース）

#### 5. 型変換を伴うsetter

boolをintに変換する例：

```php
public function setIsCompleted(bool $isCompleted): void
{
    $this->is_completed = (int)$isCompleted;
}
```

### 実例: LogLogin

**ファイル**: `api/app/Domain/User/Models/LogLogin.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $usr_user_id
 * @property int $login_count
 * @property int $is_day_first_login
 * @property int $login_day_count
 * @property int $login_continue_day_count
 * @property int $comeback_day_count
 */
class LogLogin extends LogModel
{
    use HasFactory;

    public function setLoginCount(int $loginCount): void
    {
        $this->login_count = $loginCount;
    }

    public function setIsDayFirstLogin(bool $isDayFirstLogin): void
    {
        $this->is_day_first_login = (int)$isDayFirstLogin;
    }

    public function setLoginDayCount(int $loginDayCount): void
    {
        $this->login_day_count = $loginDayCount;
    }

    public function setLoginContinueDayCount(int $loginContinueDayCount): void
    {
        $this->login_continue_day_count = $loginContinueDayCount;
    }

    public function setComebackDayCount(int $comebackDayCount): void
    {
        $this->comeback_day_count = $comebackDayCount;
    }
}
```

### 配置場所

Modelクラスは、ドメイン層の`Models`ディレクトリに配置します：

```
api/app/Domain/{Domain}/Models/LogXxx.php
```

例:
- `api/app/Domain/User/Models/LogLogin.php`
- `api/app/Domain/Gacha/Models/LogGacha.php`
- `api/app/Domain/Stage/Models/LogStageAction.php`

## ステップ3: Repositoryクラス作成

### 基本テンプレート

```php
<?php

declare(strict_types=1);

namespace App\Domain\{Domain}\Repositories;

use App\Domain\Resource\Log\Repositories\LogModelRepository;
use App\Domain\{Domain}\Models\LogXxx;

class LogXxxRepository extends LogModelRepository
{
    protected string $modelClass = LogXxx::class;

    public function create(
        string $usrUserId,
        string $actionType,
        int $amount,
    ): LogXxx {
        $model = new LogXxx();
        $model->setUsrUserId($usrUserId);
        $model->setActionType($actionType);
        $model->setAmount($amount);

        $this->addModel($model);

        return $model;
    }
}
```

### 実装ポイント

#### 1. LogModelRepositoryを継承

```php
class LogXxxRepository extends LogModelRepository
```

#### 2. $modelClassプロパティを定義

```php
protected string $modelClass = LogXxx::class;
```

このプロパティにより、Repository内でどのモデルクラスを扱うかが決まります。

#### 3. create()メソッドを実装

基本的なcreate()メソッドのパターン：

```php
public function create(/* パラメータ */): LogXxx
{
    // 1. モデルインスタンス生成
    $model = new LogXxx();

    // 2. プロパティ設定
    $model->setUsrUserId($usrUserId);
    $model->setXxx($xxx);

    // 3. LogModelManagerに追加
    $this->addModel($model);

    // 4. モデルを返す
    return $model;
}
```

#### 4. 複数モデル作成メソッド（任意）

```php
public function createBulk(array $dataList): Collection
{
    $models = collect();

    foreach ($dataList as $data) {
        $model = new LogXxx();
        $model->setUsrUserId($data['usr_user_id']);
        $model->setXxx($data['xxx']);
        $models->push($model);
    }

    $this->addModels($models);

    return $models;
}
```

### 実例: LogLoginRepository

**ファイル**: `api/app/Domain/User/Repositories/LogLoginRepository.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\Resource\Log\Repositories\LogModelRepository;
use App\Domain\User\Models\LogLogin;

class LogLoginRepository extends LogModelRepository
{
    protected string $modelClass = LogLogin::class;

    public function create(
        string $usrUserId,
        int $loginCount,
        bool $isDayFirstLogin,
        int $loginDayCount,
        int $loginContinueDayCount,
        int $comebackDayCount,
    ): LogLogin {
        $model = new LogLogin();
        $model->setUsrUserId($usrUserId);
        $model->setLoginCount($loginCount);
        $model->setIsDayFirstLogin($isDayFirstLogin);
        $model->setLoginDayCount($loginDayCount);
        $model->setLoginContinueDayCount($loginContinueDayCount);
        $model->setComebackDayCount($comebackDayCount);

        $this->addModel($model);

        return $model;
    }
}
```

### 配置場所

Repositoryクラスは、ドメイン層の`Repositories`ディレクトリに配置します：

```
api/app/Domain/{Domain}/Repositories/LogXxxRepository.php
```

例:
- `api/app/Domain/User/Repositories/LogLoginRepository.php`
- `api/app/Domain/Gacha/Repositories/LogGachaRepository.php`
- `api/app/Domain/Stage/Repositories/LogStageActionRepository.php`

## ステップ4: 動作確認

### UseCaseでの使用

```php
class XxxUseCase
{
    use UseCaseTrait;

    public function __construct(
        private LogXxxRepository $logXxxRepository,
    ) {}

    public function execute(/* パラメータ */): XxxResult
    {
        return $this->applyUserTransactionChanges(
            usrUserId: $usrUserId,
            callback: function () use ($usrUserId, /* 他のパラメータ */) {
                // ビジネスロジック

                // ログ作成
                $this->logXxxRepository->create(
                    usrUserId: $usrUserId,
                    actionType: 'Get',
                    amount: 100,
                );

                return new XxxResult(/* ... */);
            }
        );
    }
}
```

### テストでの確認

```php
public function test_xxx_creates_log()
{
    // Arrange
    $usrUserId = 'test-user-id';

    // Act
    $this->logXxxRepository->create(
        usrUserId: $usrUserId,
        actionType: 'Get',
        amount: 100,
    );

    $this->saveAllLogModel();

    // Assert
    $this->assertDatabaseHas('log_xxx', [
        'usr_user_id' => $usrUserId,
        'action_type' => 'Get',
        'amount' => 100,
    ]);
}
```

### DBに保存されたデータの確認

```bash
# TiDBコンテナに接続
docker exec -it tidb_container mysql -u root -p localDB

# ログテーブルを確認
SELECT * FROM log_xxx WHERE usr_user_id = 'test-user-id';
```

確認項目：
- [ ] idがUUID v4形式で保存されているか
- [ ] usr_user_idが正しく保存されているか
- [ ] logging_noが設定されているか
- [ ] nginx_request_idが設定されているか
- [ ] request_idが設定されているか
- [ ] created_at、updated_atが設定されているか

## チェックリスト

新規ログテーブル追加時のチェックリストです。

### Migration

- [ ] テーブル名が`log_{名前}`形式か
- [ ] 必須カラム（id, usr_user_id, nginx_request_id, request_id, logging_no）が含まれているか
- [ ] idがstring型でprimaryに設定されているか
- [ ] timestampsTz()が呼ばれているか
- [ ] 各カラムにcommentが付いているか
- [ ] down()メソッドでテーブルを削除しているか
- [ ] usr_user_idにインデックスが設定されているか（推奨）
- [ ] created_atにインデックスが設定されているか（推奨）

### Model

- [ ] LogModelを継承しているか
- [ ] HasFactoryトレイトを使用しているか
- [ ] プロパティがdocコメントで定義されているか
- [ ] 各プロパティにsetterメソッドが実装されているか
- [ ] setterメソッドの命名規則が正しいか（setXxx）
- [ ] bool型プロパティをint型に変換しているか（必要な場合）
- [ ] 配置場所が`api/app/Domain/{Domain}/Models/`か

### Repository

- [ ] LogModelRepositoryを継承しているか
- [ ] $modelClassプロパティが定義されているか
- [ ] create()メソッドが実装されているか
- [ ] create()内でsetUsrUserId()が呼ばれているか
- [ ] create()内でaddModel()が呼ばれているか
- [ ] create()がモデルを返しているか
- [ ] 配置場所が`api/app/Domain/{Domain}/Repositories/`か

### 動作確認

- [ ] Migrationが正常に実行されるか
- [ ] UseCaseでRepositoryを呼び出せるか
- [ ] ログがDBに保存されるか
- [ ] logging_no等のメタデータが自動設定されるか
- [ ] テストが通るか

## よくあるエラーと対処法

### エラー1: "Call to a member function makeModelKey() on null"

**原因**: Modelのコンストラクタでidが設定されていない

**対処法**: LogModelを継承していれば自動で設定されるため、親クラスのコンストラクタを呼び出す

```php
public function __construct(array $attributes = [])
{
    parent::__construct($attributes);  // これを忘れない
}
```

### エラー2: "Class 'LogXxx' not found"

**原因**: $modelClassプロパティのクラス名が間違っている

**対処法**: 完全修飾クラス名（FQCN）を指定する

```php
protected string $modelClass = LogXxx::class;  // 正しい
protected string $modelClass = 'LogXxx';       // 間違い
```

### エラー3: logging_noが0になる

**原因**: addModel()を呼び忘れている

**対処法**: create()メソッド内で必ずaddModel()を呼ぶ

```php
public function create(/* パラメータ */): LogXxx
{
    $model = new LogXxx();
    // プロパティ設定
    $this->addModel($model);  // これを忘れない
    return $model;
}
```

### エラー4: テーブルが見つからない

**原因**: Migrationが実行されていない

**対処法**: Migrationを実行する

```bash
./tools/bin/sail-wp migrate
```

## まとめ

新規ログテーブルを追加するには、以下の3つのファイルを作成します：

1. **Migration**: テーブル定義（必須カラム + 独自カラム）
2. **Model**: LogModelを継承し、setterメソッドを実装
3. **Repository**: LogModelRepositoryを継承し、create()メソッドを実装

これらを正しく実装すれば、LogModelManagerが自動的にログを管理・保存してくれます。

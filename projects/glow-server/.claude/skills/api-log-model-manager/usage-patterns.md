# LogModelManager 使用パターン

LogModelManagerの基本的な使用パターンと具体例を説明します。

## 目次

- [基本的な使用フロー](#基本的な使用フロー)
- [具体例: LogLoginRepository](#具体例-logloginrepository)
- [Repository実装パターン](#repository実装パターン)
- [Model実装パターン](#model実装パターン)
- [UseCaseでの使用](#usecaseでの使用)
- [テストでの使用](#テストでの使用)

## 基本的な使用フロー

LogModelManagerを使ったログ保存の基本フローです。

```
1. Repository::create() でログモデル作成
2. Repository::addModel() でLogModelManagerに追加
3. リクエスト終了時にUseCaseTrait::saveAllLog()で一括保存
```

## 具体例: LogLoginRepository

ログインログを記録する実装例です。

### Repository実装

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

**ポイント:**
- `LogModelRepository`を継承
- `$modelClass`に対象モデルクラスを指定
- `create()`メソッドでモデル作成＋addModel()で追加
- 作成したモデルを返すことで、呼び出し側でも参照可能

### Model実装

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

**ポイント:**
- `LogModel`を継承
- `HasFactory`トレイトを使用
- プロパティをdocコメントで定義
- setterメソッドでプロパティ設定

## Repository実装パターン

### パターン1: 単一モデル作成

最も一般的なパターンです。

```php
class LogXxxRepository extends LogModelRepository
{
    protected string $modelClass = LogXxx::class;

    public function create(/* パラメータ */): LogXxx
    {
        $model = new LogXxx();
        // プロパティ設定
        $model->setUsrUserId($usrUserId);
        $model->setXxx($xxx);

        // LogModelManagerに追加
        $this->addModel($model);

        return $model;
    }
}
```

### パターン2: 複数モデル作成

複数のログモデルをまとめて作成する場合です。

```php
class LogXxxRepository extends LogModelRepository
{
    protected string $modelClass = LogXxx::class;

    public function createBulk(array $dataList): Collection
    {
        $models = collect();

        foreach ($dataList as $data) {
            $model = new LogXxx();
            $model->setUsrUserId($data['usr_user_id']);
            $model->setXxx($data['xxx']);
            $models->push($model);
        }

        // 複数モデルを一度に追加
        $this->addModels($models);

        return $models;
    }
}
```

### パターン3: 条件付き作成

条件に応じてログを作成するかどうかを判定する場合です。

```php
class LogXxxRepository extends LogModelRepository
{
    protected string $modelClass = LogXxx::class;

    public function createIfNeeded(/* パラメータ */): ?LogXxx
    {
        // 条件判定
        if (!$this->shouldCreateLog($xxx)) {
            return null;
        }

        $model = new LogXxx();
        // プロパティ設定
        $this->addModel($model);

        return $model;
    }

    private function shouldCreateLog(/* パラメータ */): bool
    {
        // ログ作成条件をチェック
        return true;
    }
}
```

## Model実装パターン

### パターン1: シンプルなsetter

```php
class LogXxx extends LogModel
{
    use HasFactory;

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function setActionType(string $actionType): void
    {
        $this->action_type = $actionType;
    }
}
```

### パターン2: 型変換を伴うsetter

boolをintに変換する例です。

```php
class LogXxx extends LogModel
{
    use HasFactory;

    public function setIsCompleted(bool $isCompleted): void
    {
        $this->is_completed = (int)$isCompleted;
    }
}
```

### パターン3: バリデーションを伴うsetter

```php
class LogXxx extends LogModel
{
    use HasFactory;

    public function setActionType(string $actionType): void
    {
        if (!in_array($actionType, ['Get', 'Use'])) {
            throw new \InvalidArgumentException("Invalid action_type: {$actionType}");
        }
        $this->action_type = $actionType;
    }
}
```

### パターン4: makeModelKeyのオーバーライド

デフォルトではidをキーとして使用しますが、複合キーを使いたい場合にオーバーライドします。

```php
class LogXxx extends LogModel
{
    use HasFactory;

    public function makeModelKey(): string
    {
        // 複合キーの例
        return "{$this->usr_user_id}_{$this->mst_item_id}";
    }
}
```

## UseCaseでの使用

### 基本的な使用例

```php
class LoginUseCase
{
    use UseCaseTrait;

    public function __construct(
        private LogLoginRepository $logLoginRepository,
        // 他の依存
    ) {}

    public function execute(/* パラメータ */): LoginResult
    {
        return $this->applyUserTransactionChanges(
            usrUserId: $usrUserId,
            callback: function () use ($usrUserId, /* 他のパラメータ */) {
                // ビジネスロジック

                // ログ作成
                $this->logLoginRepository->create(
                    usrUserId: $usrUserId,
                    loginCount: $loginCount,
                    isDayFirstLogin: $isDayFirstLogin,
                    loginDayCount: $loginDayCount,
                    loginContinueDayCount: $loginContinueDayCount,
                    comebackDayCount: $comebackDayCount,
                );

                // 処理結果を返す
                return new LoginResult(/* ... */);
            }
        );

        // applyUserTransactionChanges()の中で
        // saveAllLog()が自動的に呼ばれ、ログが保存される
    }
}
```

**ポイント:**
- `UseCaseTrait::applyUserTransactionChanges()`を使用
- callback内でRepositoryのcreate()を呼ぶだけ
- saveAllLog()は自動で呼ばれる

### 複数種類のログを記録する例

```php
class StagePlayUseCase
{
    use UseCaseTrait;

    public function __construct(
        private LogStageActionRepository $logStageActionRepository,
        private LogExpRepository $logExpRepository,
        private LogItemRepository $logItemRepository,
        // 他の依存
    ) {}

    public function execute(/* パラメータ */): StagePlayResult
    {
        return $this->applyUserTransactionChanges(
            usrUserId: $usrUserId,
            callback: function () use ($usrUserId, /* 他のパラメータ */) {
                // ステージプレイログ
                $this->logStageActionRepository->create(
                    usrUserId: $usrUserId,
                    stageId: $stageId,
                    actionType: 'Clear',
                );

                // 経験値獲得ログ
                $this->logExpRepository->create(
                    usrUserId: $usrUserId,
                    actionType: 'Get',
                    amount: $expAmount,
                );

                // アイテム獲得ログ
                foreach ($rewardItems as $item) {
                    $this->logItemRepository->create(
                        usrUserId: $usrUserId,
                        actionType: 'Get',
                        mstItemId: $item->id,
                        amount: $item->amount,
                    );
                }

                return new StagePlayResult(/* ... */);
            }
        );
    }
}
```

**ポイント:**
- 複数種類のログを同時に記録できる
- すべてのログが一括で保存される
- logging_noは自動的にインクリメントされる

## テストでの使用

### テストケースでログを保存する

**ファイル**: `api/tests/TestCase.php`

```php
protected function saveAllLogModel(): void
{
    $logModelManager = app()->make(LogModelManager::class);
    $logModelManager->saveAll();
}
```

テスト内での使用例：

```php
public function test_login_creates_log()
{
    // Arrange
    $usrUserId = 'test-user-id';

    // Act
    $this->logLoginRepository->create(
        usrUserId: $usrUserId,
        loginCount: 1,
        isDayFirstLogin: true,
        loginDayCount: 1,
        loginContinueDayCount: 1,
        comebackDayCount: 0,
    );

    // ログを保存
    $this->saveAllLogModel();

    // Assert
    $this->assertDatabaseHas('log_logins', [
        'usr_user_id' => $usrUserId,
        'login_count' => 1,
        'is_day_first_login' => 1,
    ]);
}
```

**ポイント:**
- テスト内では明示的に`saveAllLogModel()`を呼ぶ
- 実際のAPIリクエストでは自動で保存される

## よくある使用例

### 例1: リソース獲得・消費ログ

```php
// 獲得
$this->logCoinRepository->create(
    usrUserId: $usrUserId,
    actionType: 'Get',
    amount: 100,
);

// 消費
$this->logCoinRepository->create(
    usrUserId: $usrUserId,
    actionType: 'Use',
    amount: 50,
);
```

### 例2: アクション実行ログ

```php
$this->logGachaActionRepository->create(
    usrUserId: $usrUserId,
    gachaId: $gachaId,
    drawCount: $drawCount,
    resultUnits: $resultUnits,
);
```

### 例3: 状態変更ログ

```php
$this->logUserLevelRepository->create(
    usrUserId: $usrUserId,
    beforeLevel: $beforeLevel,
    afterLevel: $afterLevel,
);
```

## 注意事項

### 1. setUsrUserId()は必須

すべてのログモデルは`usr_user_id`を持つ必要があります。

```php
$model->setUsrUserId($usrUserId);
```

### 2. logging_no等は自動設定される

以下のフィールドは**LogModelManagerが自動設定**するため、手動で設定してはいけません：

- `logging_no`
- `nginx_request_id`
- `request_id`

### 3. idは自動生成される

LogModelのコンストラクタでUUID v4が自動生成されます。手動で設定する必要はありません。

### 4. created_at, updated_atは自動設定される

`formatToInsert()`メソッド内で自動設定されます。

### 5. トランザクション外で保存される

ログはビジネストランザクションとは別に保存されます。ログ保存に失敗してもビジネスロジックはロールバックされません。

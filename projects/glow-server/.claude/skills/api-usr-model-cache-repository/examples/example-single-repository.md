# 実装例: UsrUserRepository (SingleCacheRepository)

## 概要

UsrUserRepositoryは、usr_userテーブル（1ユーザー1レコード）のRepositoryです。

**特徴:**
- UsrModelSingleCacheRepositoryを継承
- saveModelsは実装不要（親クラスのデフォルト実装を使用）
- cachedGetOneメソッドを使用
- idカラムを使用するため、dbSelectOneをオーバーライド

## 完全なコード例

```php
<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Enums\UserStatus;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Usr\Repositories\UsrModelSingleCacheRepository;
use App\Domain\User\Models\UsrUser;
use App\Domain\User\Models\UsrUserInterface;
use Carbon\CarbonImmutable;

class UsrUserRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrUser::class;

    /**
     * usr_userテーブルは、usr_user_idではなくidカラムを使用
     */
    protected function dbSelectOne(string $usrUserId): ?UsrUserInterface
    {
        return UsrUser::query()->where('id', $usrUserId)->first();
    }

    /**
     * ユーザーIDで取得（見つからなければエラー）
     */
    public function findById(string $userId): UsrUserInterface
    {
        $user = $this->cachedGetOne($userId);
        if ($user === null) {
            throw new GameException(ErrorCode::USER_NOT_FOUND);
        }

        return $user;
    }

    /**
     * モデルインスタンスの生成のみを実行する
     */
    public function make(CarbonImmutable $now, ?string $clientUuid = null): UsrUserInterface
    {
        $usrUser = new UsrUser();
        $usrUser->status = UserStatus::NORMAL->value;
        $usrUser->tutorial_status = '';
        $usrUser->tos_version = 0;
        $usrUser->privacy_policy_version = 0;
        $usrUser->global_consent_version = 0;
        $usrUser->iaa_version = 0;
        $usrUser->bn_user_id = null;
        $usrUser->client_uuid = $clientUuid;
        $usrUser->suspend_end_at = null;
        $usrUser->game_start_at = $now->toDateTimeString();

        return $usrUser;
    }

    /**
     * BNID連携情報を設定する
     */
    public function linkBnid(string $usrUserId, string $bnUserId): void
    {
        $usrUser = $this->findById($usrUserId);
        $usrUser->setBnUserId($bnUserId);
        $this->syncModel($usrUser);
    }

    /**
     * 直近に指定client_uuidで作成されたユーザーを取得
     *
     * APIリクエストしたユーザーとは別ユーザーのデータを取得するケースがあるので、
     * ユーザーキャッシュを介さずに、DBから直接取得する
     */
    public function findRecentlyCreatedAtByClientUuid(string $clientUuid): ?UsrUserInterface
    {
        return UsrUser::where('client_uuid', $clientUuid)
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
```

## ポイント解説

### 1. dbSelectOneのオーバーライド

usr_userテーブルは、usr_user_idではなくidカラムを使用するため、dbSelectOneをオーバーライドしています。

```php
protected function dbSelectOne(string $usrUserId): ?UsrUserInterface
{
    return UsrUser::query()->where('id', $usrUserId)->first();
}
```

### 2. cachedGetOneの使用

findByIdメソッドで、cachedGetOneを使用しています。

```php
public function findById(string $userId): UsrUserInterface
{
    $user = $this->cachedGetOne($userId);
    if ($user === null) {
        throw new GameException(ErrorCode::USER_NOT_FOUND);
    }
    return $user;
}
```

**動作:**
- 初回: DBから取得 → キャッシュに保存
- 2回目以降: キャッシュから取得（DBアクセスなし）

### 3. syncModelの使用

linkBnidメソッドで、BNID連携情報を更新した後、syncModelでキャッシュに反映しています。

```php
public function linkBnid(string $usrUserId, string $bnUserId): void
{
    $usrUser = $this->findById($usrUserId);
    $usrUser->setBnUserId($bnUserId);
    $this->syncModel($usrUser);  // キャッシュに反映
}
```

### 4. キャッシュを使わない例

findRecentlyCreatedAtByClientUuidメソッドは、他ユーザーのデータを取得する可能性があるため、キャッシュを介さずにDBから直接取得しています。

```php
public function findRecentlyCreatedAtByClientUuid(string $clientUuid): ?UsrUserInterface
{
    // キャッシュを使わず、DBから直接取得
    return UsrUser::where('client_uuid', $clientUuid)
        ->orderBy('created_at', 'desc')
        ->first();
}
```

## UsrUserLoginRepositoryの例

より単純な例として、UsrUserLoginRepositoryを紹介します。

```php
<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelSingleCacheRepository;
use App\Domain\User\Models\UsrUserLogin;
use App\Domain\User\Models\UsrUserLoginInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrUserLoginRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrUserLogin::class;

    public function get(string $usrUserId): ?UsrUserLoginInterface
    {
        return $this->cachedGetOne($usrUserId);
    }

    /**
     * ログイン判定前の初期値レコードを作成
     */
    public function create(string $usrUserId, CarbonImmutable $now): UsrUserLoginInterface
    {
        $model = new UsrUserLogin();

        $model->usr_user_id = $usrUserId;
        $model->first_login_at = null;
        $model->last_login_at = null;
        $model->hourly_accessed_at = $now->toDateTimeString();
        $model->login_count = 0;
        $model->login_day_count = 0;
        $model->login_continue_day_count = 0;
        $model->comeback_day_count = 0;

        $this->syncModel($model);

        return $model;
    }

    public function getOrCreate(string $usrUserId, CarbonImmutable $now): UsrUserLoginInterface
    {
        $model = $this->get($usrUserId);

        if ($model === null) {
            $model = $this->create($usrUserId, $now);
        }

        return $model;
    }

    public function updateHourlyAccessedAt(string $usrUserId, string $hourlyAccessedAt): UsrUserLoginInterface
    {
        $model = $this->get($usrUserId);
        $model->setHourlyAccessedAt($hourlyAccessedAt);
        $this->syncModel($model);

        return $model;
    }

    /**
     * BankF001の判定のために、DB即時保存も実行するメソッド
     */
    public function updateHourlyAccessedAtWithSave(string $usrUserId, string $hourlyAccessedAt): UsrUserLoginInterface
    {
        $model = $this->updateHourlyAccessedAt($usrUserId, $hourlyAccessedAt);
        $models = collect([$model]);
        $this->saveModels($models);

        return $model;
    }
}
```

## まとめ

SingleCacheRepositoryの実装ポイント:

- ✅ saveModelsは実装不要
- ✅ cachedGetOneメソッドを使用
- ✅ dbSelectOneをオーバーライド（カラム名が異なる場合）
- ✅ syncModelで更新をキャッシュに反映
- ✅ 他ユーザーのデータ取得時はキャッシュを使わない

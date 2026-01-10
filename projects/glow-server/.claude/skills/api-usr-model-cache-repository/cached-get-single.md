# SingleCacheRepository - cachedGetOneメソッド

## cachedGetOneの概要

UsrModelSingleCacheRepositoryで提供される、1レコードのみのテーブル向けのキャッシュ取得メソッドです。

**用途:**
- 1ユーザーあたり最大1レコードのテーブル（usr_user, usr_user_login等）
- 1回DB取得したら、以降はキャッシュから取得

## メソッドシグネチャ

```php
protected function cachedGetOne(string $usrUserId): mixed
```

**引数:**
- `$usrUserId` - 取得したいデータを所持しているユーザーのID

**戻り値:**
- モデルインスタンス（存在する場合）
- `null`（存在しない場合、または全取得済みでキャッシュにない場合）

## 動作フロー

```
1. 他人のデータを取得する場合は、毎回DBから取得
   → return $dbCallback()

2. キャッシュにデータがあれば、それを返す
   → return $model

3. 1回でもDB取得済み（isAllFetched）なら、DBから取得しない
   → return null

4. DBからデータ取得
   → markAllFetched($usrUserId)
   → syncModel($model)
   → return $model
```

## 実装パターン

### パターン1: 標準的な使い方

```php
class UsrUserLoginRepository extends UsrModelSingleCacheRepository
{
    protected string $modelClass = UsrUserLogin::class;

    public function get(string $usrUserId): ?UsrUserLoginInterface
    {
        return $this->cachedGetOne($usrUserId);
    }
}
```

**使用例:**
```php
// 初回: DBから取得 → キャッシュに保存
$usrUserLogin = $this->usrUserLoginRepository->get($usrUserId);

// 2回目以降: キャッシュから取得（DBアクセスなし）
$usrUserLogin = $this->usrUserLoginRepository->get($usrUserId);
```

### パターン2: getOrCreateパターン

```php
public function getOrCreate(string $usrUserId, CarbonImmutable $now): UsrUserLoginInterface
{
    $model = $this->get($usrUserId);

    if ($model === null) {
        $model = $this->create($usrUserId, $now);
    }

    return $model;
}
```

## dbSelectOneのオーバーライド

デフォルトでは、`usr_user_id`カラムでDB取得しますが、カラム名が異なる場合はオーバーライドしてください。

### 例: usr_userテーブル（idカラムを使用）

```php
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

    public function findById(string $userId): UsrUserInterface
    {
        $user = $this->cachedGetOne($userId);
        if ($user === null) {
            throw new GameException(ErrorCode::USER_NOT_FOUND);
        }
        return $user;
    }
}
```

## 重要な仕組み

### 1. 全取得済みフラグ（isAllFetched）

SingleCacheRepositoryでは、1回DB取得したら「全データ取得済み」とみなします。

```php
// 1回DB取得したら、以降はキャッシュにデータがなければnullを返す
if ($this->isAllFetched()) {
    return null;
}

// DBから取得したら、全取得済みフラグを立てる
$this->markAllFetched($usrUserId);
```

**理由:**
- 1ユーザーあたり最大1レコードのため、DBにデータがないことが確定

### 2. 他人のデータは毎回DB取得

```php
// 他人のデータを取得する場合は、毎回DBから取得する
if ($this->isOwnUsrUserId($usrUserId) === false) {
    return $dbCallback();
}
```

**理由:**
- キャッシュは本人のデータのみ管理
- 他人のデータは頻繁にアクセスしないため、キャッシュする必要がない

### 3. キャッシュから取得して返す

```php
// 最新の変更を反映するために、キャッシュから取得する
// DBから取得したものをそのまま返すと、もしキャッシュすでにある場合に、値がデグレする可能性があるため
return $this->getFirstCache();
```

**理由:**
- DBから取得したモデルをそのまま返すと、キャッシュに既に存在する場合に値がデグレする可能性
- キャッシュから取得することで、最新の変更を反映

## 使い分けガイド

### cachedGetOneを使うべきケース

- ✅ 1ユーザーあたり最大1レコードのテーブル
- ✅ 頻繁にアクセスするデータ
- ✅ 同一リクエスト内で複数回取得する可能性がある

### 例

| テーブル | 用途 | 理由 |
|---------|------|------|
| usr_user | ユーザー基本情報 | 1ユーザー1レコード、頻繁にアクセス |
| usr_user_login | ログイン情報 | 1ユーザー1レコード、ログイン判定で使用 |
| usr_user_profile | プロフィール | 1ユーザー1レコード、プロフィール表示で使用 |

### cachedGetOneを使わないケース

- ❌ 1ユーザーあたり2レコード以上のテーブル
  - → `UsrModelMultiCacheRepository`を使用
- ❌ 他ユーザーのデータを頻繁に取得する
  - → キャッシュを介さずにDBから直接取得

## まとめ

- **cachedGetOne**: SingleCacheRepository専用の1レコード取得メソッド
- **全取得済みフラグ**: 1回DB取得したら、以降はキャッシュから取得
- **他人のデータ**: 毎回DB取得（キャッシュしない）
- **dbSelectOneのオーバーライド**: カラム名が異なる場合は実装

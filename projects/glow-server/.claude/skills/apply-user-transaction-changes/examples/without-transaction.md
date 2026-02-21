# processWithoutUserTransactionChanges 使用例

## 基本パターン

```php
public function exec(CurrentUser $user, ...): ResultData
{
    $usrUserId = $user->getUsrUserId();
    $now = $this->clock->now();

    // データ取得のみ
    $data = $this->service->fetch($usrUserId, $now);

    // ログ保存・アクセス日時更新を実行
    $this->processWithoutUserTransactionChanges();

    return new ResultData($data);
}
```

## 実装コード参照

### GameFetchUseCase

**ファイル**: `api/app/Domain/Game/UseCases/GameFetchUseCase.php`

```php
public function exec(CurrentUser $user, string $language): GameFetchResultData
{
    $usrUserId = $user->getUsrUserId();
    $now = $this->clock->now();
    $gameStartAt = CarbonImmutable::parse($user->getGameStartAt());

    $gameFetchData = $this->gameService->fetch($usrUserId, $now, $language, $gameStartAt);

    $this->processWithoutUserTransactionChanges();
    return new GameFetchResultData($gameFetchData);
}
```

### UserInfoUseCase

**ファイル**: `api/app/Domain/User/UseCases/UserInfoUseCase.php`

```php
public function exec(CurrentUser $user): UserInfoResultData
{
    $usrUserId = $user->getUsrUserId();

    $userInfo = $this->userService->getUserInfo($usrUserId);

    $this->processWithoutUserTransactionChanges();
    return new UserInfoResultData($userInfo);
}
```

### GachaHistoryUseCase

**ファイル**: `api/app/Domain/Gacha/UseCases/GachaHistoryUseCase.php`

```php
public function exec(CurrentUser $user, string $oprGachaId): GachaHistoryResultData
{
    $usrUserId = $user->getUsrUserId();

    $histories = $this->gachaService->getGachaHistory($usrUserId, $oprGachaId);

    $this->processWithoutUserTransactionChanges();
    return new GachaHistoryResultData($histories);
}
```

## processWithoutUserTransactionChanges の内部処理

```php
public function processWithoutUserTransactionChanges(): void
{
    // 1時間ごとのアクセス日時の更新（即時保存あり）
    $this->updateHourlyAccessedAtAndCreateBankActiveLog(true);

    // ログデータの一括保存
    $this->saveAllLog();
}
```

**処理内容:**
1. `usr_user_logins.hourly_accessed_at` の更新（1時間ごと）
2. `log_bank_actives` へのアクティブログ作成
3. `LogModelManager` のログ一括保存

## 使用する場面

| 場面 | 例 |
|-----|-----|
| データ取得API | game/fetch, gacha/history, pvp/ranking |
| 情報参照API | user/info, shop/get_store_info |
| サーバー状態取得 | game/server_time, game/version |

## チェックポイント

- [ ] `use UseCaseTrait;` を宣言しているか
- [ ] return の前に `processWithoutUserTransactionChanges()` を呼んでいるか
- [ ] データ変更がないことを確認したか

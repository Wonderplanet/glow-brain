# TODO細分化ルール詳細

## 基本原則

**1TODO = 1メソッド実装 または 1クラスファイル追加**

## ドメイン別の細分化パターン

### API実装の場合

```
【悪い例】
- API実装

【良い例】
- XxxController クラス作成
- XxxController::action() メソッド実装
- XxxResultData クラス作成
- ResponseFactory::createXxxResponse() メソッド追加
- routes/api.php にルーティング追加
```

### ドメイン層実装の場合

```
【悪い例】
- ドメイン層実装

【良い例】
- XxxEntity クラス作成
- XxxModel クラス作成
- XxxRepository インターフェース作成
- XxxRepositoryImpl 実装クラス作成
- XxxRepositoryImpl::save() 実装
- XxxRepositoryImpl::find() 実装
- XxxService クラス作成
- XxxService::execute() メソッド実装
- XxxService::validate() メソッド実装
```

### テスト実装の場合

```
【悪い例】
- テスト作成

【良い例】
- XxxServiceTest クラス作成
- test_execute_success() テストケース実装
- test_execute_with_invalid_input() テストケース実装
- test_execute_with_edge_case() テストケース実装
- XxxControllerTest クラス作成
- test_endpoint_returns_200() テストケース実装
- test_endpoint_validates_input() テストケース実装
```

### マイグレーションの場合

```
【悪い例】
- DB準備

【良い例】
- xxx_create_users_table マイグレーションファイル作成
- マイグレーション実行
- シーダーファイル作成（必要な場合）
- シーダー実行（必要な場合）
```

### Admin実装の場合

```
【悪い例】
- 管理画面実装

【良い例】
- XxxResource クラス作成
- XxxResource::form() メソッド実装
- XxxResource::table() メソッド実装
- XxxResource::getRelations() メソッド実装
- CreateXxx ページクラス作成
- EditXxx ページクラス作成
- ListXxx ページクラス作成
```

## 判断基準チェックリスト

TODOを書く前に以下を確認：

- [ ] 1つのファイルの変更で完結するか？
- [ ] 1つのメソッドの実装で完結するか？
- [ ] 完了条件が明確か？
- [ ] 30分以内で完了できる粒度か？

上記のいずれかがNOなら、さらに分割が必要。

## 分割の具体例

### 例1: 「ユーザー認証API」

```
分割前: ユーザー認証API実装

分割後:
1. AuthController クラス作成
2. AuthController::login() メソッド実装
3. AuthController::logout() メソッド実装
4. AuthService クラス作成
5. AuthService::authenticate() メソッド実装
6. AuthService::validateCredentials() メソッド実装
7. AuthService::generateToken() メソッド実装
8. LoginResultData クラス作成
9. ResponseFactory::createLoginResponse() メソッド追加
10. routes/api.php に /auth/login 追加
11. routes/api.php に /auth/logout 追加
12. AuthServiceTest クラス作成
13. test_authenticate_success() 実装
14. test_authenticate_invalid_password() 実装
15. test_authenticate_user_not_found() 実装
16. AuthControllerTest クラス作成
17. test_login_success() 実装
18. test_login_validation_error() 実装
19. test_logout_success() 実装
```

### 例2: 「報酬付与機能」

```
分割前: 報酬付与機能実装

分割後:
1. RewardService クラス作成
2. RewardService::send() メソッド実装
3. RewardService::validateReward() メソッド実装
4. RewardService::applyReward() メソッド実装
5. RewardLogEntity クラス作成
6. RewardLogModel クラス作成
7. RewardLogRepository インターフェース作成
8. RewardLogRepositoryImpl::save() 実装
9. RewardLogRepositoryImpl::findByUserId() 実装
10. RewardServiceTest クラス作成
11. test_send_item_reward() 実装
12. test_send_currency_reward() 実装
13. test_send_multiple_rewards() 実装
14. test_send_with_overflow_handling() 実装
```

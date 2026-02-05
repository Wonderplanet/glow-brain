# 現在の glow-server API アーキテクチャ概要

## 全体構造

glow-server の API 部分は、部分的にドメイン駆動設計（DDD）の考え方を取り入れたアーキテクチャを採用しています。コードは主に以下の構造で組織化されています。

```
api/
└── app/
    ├── Console/          # コマンドライン関連
    ├── Domain/           # ドメイン層（ビジネスロジック）
    │   ├── User/         # Userドメイン
    │   ├── Item/         # Itemドメイン
    │   ├── Tutorial/     # Tutorialドメイン
    │   └── ...           # その他のドメイン
    ├── Exceptions/       # 例外クラス
    ├── Http/             # プレゼンテーション層
    │   ├── Controllers/  # コントローラー
    │   ├── Responses/    # レスポンス関連
    │   └── ...           # その他のHTTP関連
    ├── Infrastructure/   # インフラストラクチャ層
    ├── Providers/        # サービスプロバイダ
    │   └── Domain/       # ドメイン別サービスプロバイダ
    └── Traits/           # 共通特性
```


## ドメイン層の構造

各ドメインディレクトリ（例：`app/Domain/User/`）は、以下のような構成で組織化されています：
```
Domain/User/
├── Constants/      # ドメイン固有の定数
├── Delegators/     # 他サービスへの処理委譲クラス
├── Enums/          # 列挙型
├── Models/         # Eloquentモデルとインターフェース
├── Repositories/   # データアクセスリポジトリ
├── Services/       # ドメインサービス
└── UseCases/       # ユースケース（アプリケーションサービス）
```


## レイヤー構造と責務

### 1. プレゼンテーション層 (`app/Http/`)

主な責務：

- HTTP リクエストの受付と応答の返却
- リクエストデータのバリデーション
- UseCaseの呼び出し
- クライアントへのレスポンス形式変換

実装例（[UserController.php](vscode-file://vscode-app/Applications/Visual%20Studio%20Code.app/Contents/Resources/app/out/vs/code/electron-sandbox/workbench/workbench.html)）：
```php
<?php
public function info(UserInfoUseCase $useCase, Request $request): JsonResponse
{
    $resultData = $useCase->exec($request->user());
    return $this->responseFactory->createInfoResponse($resultData);
}
```

### 2. アプリケーション層 (`app/Domain/*/UseCases/`)

主な責務：

- ビジネスロジックのオーケストレーション
- ドメインオブジェクトやサービスの操作
- リポジトリを介したデータ永続化
- トランザクション管理
- 本レイヤーではexecメソッドのみを実装するようにしてください。
- 実装で日時を扱いたい場合はUseCaseでClockクラスをメソッドインジェクションしてください。
    - 後続処理へはUseCaseのClockクラスから`$now = $this->clock->now()`で現在日時を取り出して引数として渡すようにしてください。
    - テストクラス以外では日時の生成は行わないようにしてください。

実装例（[UserInfoUseCase.php](vscode-file://vscode-app/Applications/Visual%20Studio%20Code.app/Contents/Resources/app/out/vs/code/electron-sandbox/workbench/workbench.html)）：
```php
<?php
public function exec(CurrentUser $user): UserInfoResultData
{
    $usrUserId = $user->id;
    $usrUserProfile = $this->usrUserProfileRepository->findByUsrUserId($usrUserId);
    $this->saveUserAccessLog();
    return new UserInfoResultData($usrUserProfile);
}
```

### 3. ドメイン層 (`app/Domain/*/Models/, */Services/`)

主な責務：

- ドメインモデルの表現（Models）
- ビジネスルールの実装（Services）
- データ操作の抽象化（Repositoryインターフェース）

実装例（[UserService.php](vscode-file://vscode-app/Applications/Visual%20Studio%20Code.app/Contents/Resources/app/out/vs/code/electron-sandbox/workbench/workbench.html)）：
```php
<?php
public function setNewName(string $usrUserId, string $newName, CarbonImmutable $now): void
{
    // 名前のバリデーション
    // ユーザープロフィール更新
    // ログ記録
}
```

### 4. インフラストラクチャ層 (`app/Domain/*/Repositories/, app/Infrastructure/`)

主な責務：

- データの永続化と取得（Repository実装）
- 外部サービスとの連携
- フレームワーク固有の実装

実装例（[UsrUserRepository.php](vscode-file://vscode-app/Applications/Visual%20Studio%20Code.app/Contents/Resources/app/out/vs/code/electron-sandbox/workbench/workbench.html)）：
```php
<?php
public function findById(string $userId): UsrUserInterface
{
    $user = $this->cachedGetOne($userId);
    if ($user === null) {
        throw new GameException(ErrorCode::USER_NOT_FOUND);
    }
    return $user;
}
```

## 依存性注入

依存性注入は Laravel の機能を活用し、主に以下の方法で実現されています：

1. **コンストラクタインジェクション**：
```php
<?php
public function __construct(
    private UsrUserProfileRepository $usrUserProfileRepository,
) {
}
```
2. **サービスプロバイダによる登録**： 
```php
<?php
// app/Providers/Domain/UserServiceProvider.php
public function register(): void
{
    array_map(array($this->app, 'singleton'), $this->classes);
}
```

## データフロー

典型的なリクエスト処理のデータフローは以下の通りです：

1. ユーザーからのリクエストがコントローラーに到達
2. コントローラーがリクエストを検証し、必要なデータを抽出
3. コントローラーが適切な UseCase を呼び出し
4. UseCase が必要なサービスやリポジトリと連携してビジネスロジックを実行
5. 処理結果が ResultData オブジェクトとしてコントローラーに返される
6. コントローラーが ResponseFactory を使用して適切な形式の JSON レスポンスを返却

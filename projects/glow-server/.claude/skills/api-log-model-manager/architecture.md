# LogModelManager アーキテクチャ

LogModelManagerの設計思想と役割について説明します。

## 概要

LogModelManagerは、APIリクエスト中のログデータを効率的に管理するためのシステムです。以下の責務を持ちます：

- **遅延保存**: ログモデルをメモリ上で管理し、リクエスト終了時に一括保存
- **メタデータ自動設定**: logging_no、nginx_request_id、request_idを自動付与
- **Repository別管理**: Repository単位でモデルをグループ化

## ライフサイクル

LogModelManagerのインスタンスは、**1リクエスト中のみ有効**です。

```
1. リクエスト開始
   ↓
2. AppServiceProviderでLogModelManagerインスタンス生成
   - nginx_request_id、request_idを設定
   ↓
3. ビジネスロジック実行
   - Repository経由でログモデルを追加
   - LogModelManager内にメモリ上で保持
   ↓
4. リクエスト処理完了
   - UseCaseTrait::saveAllLog()を呼び出し
   - LogModelManager::saveAll()で一括DB保存
   ↓
5. リクエスト終了
   - LogModelManagerインスタンス破棄
```

### AppServiceProviderでの登録

**ファイル**: `api/app/Providers/AppServiceProvider.php`

```php
// LogModelManager
$this->app->scoped(LogModelManager::class, function () {
    return new LogModelManager(
        LogUtil::getNginxRequestId(),
        LogUtil::getRequestId(),
    );
});
```

- `scoped`により、1リクエスト中は同一インスタンスが使われる
- リクエスト終了時に自動的に破棄される

## 主要コンポーネント

### LogModelManager

**ファイル**: `api/app/Infrastructure/LogModelManager.php`

#### プロパティ

```php
// ログモデルをRepository別に管理
private Collection $models;  // Collection<string, Collection<LogModelInterface>>

// リクエストメタデータ
private string $nginxRequestId;
private string $requestId;
private int $loggingNo;  // ログの順番（自動インクリメント）
```

#### 主要メソッド

**addModels(string $repositoryClass, Collection $targetModels): void**
- Repositoryから呼び出され、ログモデルをメモリ上に追加
- 各モデルにlogging_no、nginx_request_id、request_idを自動設定

**saveAll(): void**
- リクエスト終了時に呼び出され、すべてのログを一括保存
- Repository毎にsaveModels()を実行

**setLogging(LogModelInterface $model): void**
- モデルにlogging_no、nginx_request_id、request_idを設定
- logging_noは自動インクリメント

### LogModel (抽象クラス)

**ファイル**: `api/app/Domain/Resource/Log/Models/LogModel.php`

すべてのログモデルの基底クラスです。

#### 主な特徴

```php
abstract class LogModel extends BaseModel implements LogModelInterface
{
    // TiDBに接続
    protected $connection = Database::TIDB_CONNECTION;

    // UUID主キー
    protected $keyType = 'string';
    public $incrementing = false;

    // 日付フォーマット（マイクロ秒対応）
    protected $dateFormat = 'Y-m-d H:i:s.';
}
```

#### 必須実装メソッド

- `makeModelKey()`: LogModelManager内でモデルを識別するキー（デフォルトはid）
- `isChanged()`: モデルが変更されているかチェック（デフォルトはisDirty()）
- `setLogging()`: メタデータを設定
- `formatToInsert()`: DB保存用のデータ配列を返す

### LogModelRepository (抽象クラス)

**ファイル**: `api/app/Domain/Resource/Log/Repositories/LogModelRepository.php`

すべてのログRepositoryの基底クラスです。

#### 主な特徴

```php
abstract class LogModelRepository implements LogModelRepositoryInterface
{
    // 対象モデルクラスを定義
    protected string $modelClass = '';

    public function __construct(
        protected LogModelManager $logModelManager,
    ) {}
}
```

#### 主要メソッド

**addModel(LogModelInterface $model): void**
- ログモデルを1件追加
- LogModelManager::addModels()を内部で呼び出す

**addModels(Collection $models): void**
- ログモデルを複数件追加

**saveModels(Collection $models): void**
- LogModelManagerから呼び出され、一括INSERT実行
- `Model::insert($insertValues)` を使用

## データフロー

```
1. UseCase/Service
   ↓ LogLoginRepository::create()
2. LogLoginRepository
   ↓ new LogLogin()
   ↓ $this->addModel($model)
3. LogModelRepository
   ↓ $this->logModelManager->addModels()
4. LogModelManager
   - メモリ上に保持
   - setLogging()でメタデータ設定

--- リクエスト処理完了 ---

5. UseCaseTrait::saveAllLog()
   ↓ LogModelManager::saveAll()
6. LogModelManager
   ↓ Repository::saveModels()
7. LogModelRepository
   ↓ Model::insert()
8. DB（TiDB）
```

## メタデータの自動設定

LogModelManagerは、各ログモデルに以下のメタデータを自動設定します：

### logging_no
- APIリクエスト中でのログの順番を示す連番
- 初期値: `LogConstant::LOGGING_NO_INITAL_VALUE`
- addModels()呼び出し毎に自動インクリメント

### nginx_request_id
- Nginxがリクエスト毎に生成するユニークID
- リクエスト全体を追跡するために使用

### request_id
- クライアントのHTTP Libraryが生成するユニークID
- クライアント側のログとの紐付けに使用

## 設計上の利点

### 1. 遅延保存による性能向上
- ログをメモリ上で保持し、最後に一括INSERT
- DB接続回数を削減

### 2. メタデータの自動管理
- logging_no、nginx_request_id、request_idを自動設定
- Repository実装時に設定漏れを防ぐ

### 3. トランザクション分離
- ログ保存はトランザクション外で実行
- ビジネスロジックのトランザクションに影響しない

### 4. エラーハンドリング
- ログ保存でエラーが発生してもリクエスト処理は継続
- エラーログ出力のみ（UseCaseTrait::saveAllLog()でtry-catch）

## 注意事項

### トランザクション外での保存

ログデータは、ビジネスロジックのトランザクション外で保存されます。

**理由:**
- ログはデバッグ・分析用であり、ビジネスロジックに影響を与えるべきではない
- ログ保存失敗でビジネストランザクションがロールバックされるのを防ぐ

**実装箇所**: `api/app/Domain/Common/Traits/UseCaseTrait.php`

```php
try {
    // ログデータの一括保存
    /** @var LogModelManager $logModelManager */
    $logModelManager = app()->make(LogModelManager::class);
    $logModelManager->saveAll();
} catch (\Throwable $e) {
    // ログ保存の例外はエラーログ出力のみとしておく
    Log::error('Exception occurred in saveAllLog: ' . $e->getMessage(), ['exception' => $e]);
}
```

### TiDB接続

すべてのログモデルは、TiDBに保存されます。

```php
protected $connection = Database::TIDB_CONNECTION;
```

### UUID主キー

ログテーブルの主キーはUUIDv4を使用します。

```php
protected $keyType = 'string';
public $incrementing = false;
```

LogModelのコンストラクタで自動生成されます：

```php
if (!isset($this->id)) {
    $this->id = $this->newUniqueId();  // UUID v4
}
```

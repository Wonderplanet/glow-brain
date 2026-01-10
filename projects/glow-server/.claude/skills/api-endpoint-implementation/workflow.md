# API実装の全体フロー

新規APIエンドポイントを追加する際の完全な手順を説明します。

## 目次

1. [実装前の準備](#実装前の準備)
2. [データベース設計](#データベース設計)
3. [Domain層実装](#domain層実装)
4. [Controller層実装](#controller層実装)
5. [テスト実装](#テスト実装)
6. [品質チェック](#品質チェック)

---

## 実装前の準備

### 1-1. glow-schemaで仕様を確認

新規APIの仕様をglow-schemaリポジトリのYAML定義から確認します。

**使用スキル:** **[api-schema-reference](../api-schema-reference/SKILL.md)**

確認項目：
- [ ] エンドポイントのパス（例: `/stage/start`）
- [ ] HTTPメソッド（POST/GET）
- [ ] リクエストパラメータ（`params`）
- [ ] レスポンス構造（`response`）
- [ ] 使用するデータ型定義（`data`セクション）

### 1-2. 既存実装を調査

類似のエンドポイントを探して実装パターンを理解します。

```bash
# 類似のControllerを検索
rg "class.*Controller" api/app/Http/Controllers/

# 類似のUseCaseを検索
rg "class.*UseCase" api/app/Domain/{対象ドメイン}/UseCases/
```

---

## データベース設計

### 2-1. 必要なテーブルを設計

API仕様から必要なテーブル構造を設計します。

### 2-2. マイグレーション実装

**使用スキル:** **[migration](../migration/SKILL.md)**

- テーブル作成・変更のマイグレーションを実装
- ロールバック処理も忘れずに実装
- 複数DBの場合、適切な接続先を指定

---

## Domain層実装

### 3-1. Domain層のコード実装

**使用スキル:** **[domain-layer](../domain-layer/SKILL.md)**

実装する順序：
1. **Model** - Eloquentモデルとドメインモデル
2. **Repository** - データアクセス層（CRUD操作）
3. **Service** - ビジネスロジック
4. **UseCase** - ユースケース（Controllerから呼ばれる）
5. **Delegator** - ドメイン間通信（必要に応じて）

### 3-2. Entityの実装

データ転送オブジェクト（DTO）を実装：
- **DomainEntity** - ドメイン固有のEntity
- **ResourceEntity** - 共有リソースEntity（Delegatorのreturnで使用）
- **CommonEntity** - 共通Entity

**重要:** Delegatorのreturn型はResourceEntity、CommonEntity、Collection、プリミティブ型のみ

---

## Controller層実装

### 4-1. ルーティング定義を追加

参照: **[routing.md](routing.md)**

`api/routes/api.php` にルート定義を追加：

```php
Route::controller(Controllers\StageController::class)->group(function () {
    Route::post('/stage/start', 'start');
    Route::post('/stage/end', 'end');
});
```

### 4-2. Controllerメソッドを実装

参照: **[controller.md](controller.md)**

実装パターン：
1. コンストラクタでRequest、ResponseFactoryを注入
2. メソッド引数でUseCaseを受け取る
3. バリデーション実行（**[api-request-validation](../api-request-validation/SKILL.md)** スキル参照）
4. UseCaseを実行してResultDataを取得
5. ResponseFactoryでJsonResponseを返す

### 4-3. ResultDataを実装

参照: **[result-data.md](result-data.md)**

UseCaseからResponseFactoryへのデータ受け渡し用クラスを実装：

```php
namespace App\Http\Responses\ResultData;

class StageStartResultData
{
    public function __construct(
        public UsrParameterData $usrUserParameter,
        public UsrStageStatusData $usrStageStatus
    ) {
    }
}
```

### 4-4. ResponseFactoryメソッドを実装

**使用スキル:** **[api-response](../api-response/SKILL.md)**

- ResponseDataFactoryを使ってレスポンス配列を構築
- 日時データは必ず `StringUtil::convertToISO8601()` で変換
- レスポンスキーはglow-schemaのYAML定義と一致させる

---

## テスト実装

### 5-1. テストコードを実装

**使用スキル:** **[api-test-implementation](../api-test-implementation/SKILL.md)**

実装するテスト：
- **Unit Test** - Service、Repositoryのテスト
- **Feature Test** - UseCase、Controllerのテスト
- **Scenario Test** - エンドツーエンドのテスト

### 5-2. テストを実行

```bash
# 全テスト実行
./tools/bin/sail-wp test

# 特定のテストのみ実行
./tools/bin/sail-wp test --filter=StageStartTest
```

---

## 品質チェック

### 6-1. コード品質チェックを実行

**使用スキル:** **[sail-check-fixer](../sail-check-fixer/SKILL.md)**

```bash
# 全品質チェック実行
./tools/bin/sail-wp check
```

以下をすべてクリアする必要があります：
- **phpcs** - コーディング規約
- **phpcbf** - コード整形
- **phpstan** - 静的解析
- **deptrac** - アーキテクチャ依存関係検証
- **phpunit** - テスト

### 6-2. 問題があれば修正

各チェックツールのエラーを修正：
- phpcs/phpcbf → **[api-phpcs-phpcbf-fixer](../sail-check-fixer/SKILL.md)**
- phpstan → **[api-phpstan-fixer](../sail-check-fixer/SKILL.md)**
- deptrac → **[api-deptrac-fixer](../sail-check-fixer/SKILL.md)**
- phpunit → **[api-test-runner](../api-test-runner/SKILL.md)**

---

## チェックリスト

実装完了前に以下を確認：

**仕様確認:**
- [ ] glow-schemaのYAML定義を確認した
- [ ] リクエストパラメータを把握した
- [ ] レスポンス構造を把握した

**DB実装:**
- [ ] 必要なマイグレーションを実装した
- [ ] ロールバック処理を実装した
- [ ] マイグレーションを実行して動作確認した

**Domain層実装:**
- [ ] Model、Repository、Service、UseCaseを実装した
- [ ] Delegatorを実装した（必要に応じて）
- [ ] Delegatorのreturn型がResourceEntity/CommonEntity/Collection/プリミティブ型のみ

**Controller層実装:**
- [ ] ルーティング定義を追加した
- [ ] 適切なミドルウェアを設定した
- [ ] Controllerメソッドを実装した
- [ ] バリデーションを実装した
- [ ] ResultDataを実装した
- [ ] ResponseFactoryメソッドを実装した
- [ ] 日時データを `StringUtil::convertToISO8601()` で変換した
- [ ] レスポンスキーがglow-schemaと一致している

**テスト実装:**
- [ ] Unit Testを実装した
- [ ] Feature Testを実装した
- [ ] Scenario Testを実装した（必要に応じて）
- [ ] 全テストが成功する

**品質チェック:**
- [ ] `./tools/bin/sail-wp check` が全て成功する
- [ ] phpcs/phpcbf違反がない
- [ ] phpstan違反がない
- [ ] deptrac違反がない
- [ ] phpunit全テスト成功

---

## 実装の順序

推奨される実装順序：

```
1. glow-schema確認 (api-schema-reference)
   ↓
2. マイグレーション実装 (migration)
   ↓
3. Domain層実装 (domain-layer)
   Model → Repository → Service → UseCase → Delegator
   ↓
4. Controller層実装 (このスキル)
   ルーティング → Controller → ResultData → ResponseFactory → バリデーション
   ↓
5. テスト実装 (api-test-implementation)
   Unit Test → Feature Test → Scenario Test
   ↓
6. 品質チェック (sail-check-fixer)
   phpcs → phpcbf → phpstan → deptrac → phpunit
```

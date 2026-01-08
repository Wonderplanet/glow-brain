# テスト構造・命名規則

## ディレクトリ構造

```
api/tests/
├── TestCase.php              # 親テストクラス
├── Unit/                     # ユニットテスト（完全モック）
├── Feature/
│   ├── Domain/               # Service/Repository（実DB）
│   ├── Http/Controllers/     # Controller（UseCaseモック）
│   └── Scenario/             # エンドツーエンド
└── Support/Traits/           # テスト支援Trait
```

## 配置ルール

| テスト種別 | 配置場所 | 対象 |
|-----------|---------|------|
| Unit | `tests/Unit/{ドメイン}/` | UseCase（全依存モック） |
| Feature | `tests/Feature/Domain/{ドメイン}/` | Service/Repository（実DB） |
| Controller | `tests/Feature/Http/Controllers/` | Controller（UseCaseモック） |
| Scenario | `tests/Feature/Scenario/` | 複数APIリクエスト |

## 命名規則

**ファイル名:** `{テスト対象クラス名}Test.php`
**メソッド名:** `test_{メソッド名}_{説明}`

```php
// 例
public function test_exec_正常動作()
public function test_apply_ランダムかけらボックスの場合()
```

**禁止:** `@test`アノテーション使用禁止

## TestCase主要メソッド

| メソッド | 用途 |
|---------|------|
| `fixTime($datetime)` | 時間固定（**必須**） |
| `createUsrUser($attrs)` | UsrUser作成 |
| `saveAll()` | usr/log保存（Service/Repositoryで**必須**） |
| `createDiamond()` / `getDiamond()` | 通貨操作 |
| `createMasterRelease()` | マスタ準備（Scenarioで**必須**） |
| `postJson($uri, $data)` | 自動ヘッダー付きリクエスト |

**重要:** 時間は必ず`$this->fixTime()`を使用。`CarbonImmutable::now()`禁止。

## phpunit.xml設定

```xml
<testsuites>
  <testsuite name="Unit">
    <directory>./tests/Unit</directory>
  </testsuite>
  <testsuite name="Feature">
    <directory>./tests/Feature</directory>
  </testsuite>
</testsuites>
```

環境: `APP_ENV=local_test`, `memory_limit=512M`

# glow-schemaからglow-serverへの変更取り込みワークフロー

## 0. 事前準備（PR情報の取得）
glow-schemaのPR URLが渡された場合、MCP経由で詳細情報を取得：

取得した情報から以下を確認：
- 変更されたYMLファイル
- 追加・変更されたテーブル/カラム
- 新規Enumの有無

## 1. glow-schemaでの変更内容確認
- **対象ファイル**: `Schema/*.yml`の各ドメインYMLファイル
- **変更タイプ**: 
  - **既存テーブルへのカラム追加**
  - **新規テーブルの作成**
  - **新規Enumの追加**
  - テーブルプレフィックスで接続先を判定：
    - `Mst*`: マスターデータ（読み取り専用）
    - `Opr*`: 運用データ
    - `Usr*`: ユーザーデータ

## 2. glow-serverへの取り込み手順

### ① ブランチ作成
```bash
git checkout -b yml/{PR番号}
```

### ② 変更タイプ別の対応

#### A. 既存テーブルへのカラム追加の場合

**モデルファイルの更新**:
- 対象: 各ドメインの既存モデルファイル
- `$casts`配列に新規カラムの型定義を追加

**マイグレーション作成**:
```bash
./tools/bin/sail-wp artisan make:migration add_{カラム名}_to_{テーブル名} --path={適切なパス}
```

#### B. 新規データテーブル作成の場合

**モデルファイルの新規作成**:
```php
// 例: api/app/Domain/{ドメイン}/Models/{テーブル名}.php
namespace App\Domain\{ドメイン}\Models;

use App\Domain\Base\Models\{BaseModel|MstModel};

class {テーブル名} extends {BaseModel|MstModel}
{
    protected $table = '{テーブル名（スネークケース）}';
    
    protected $casts = [
        // カラム定義
    ];
}
```

**マイグレーション作成**:
```bash
# 新規テーブル作成
./tools/bin/sail-wp artisan make:migration create_{テーブル名}_table --path={適切なパス}
```

**マイグレーションファイルの内容**:
```php
Schema::create('{テーブル名}', function (Blueprint $table) {
    $table->string('id')->primary();
    // その他のカラム定義
    $table->timestamps();
});
```

**ファクトリーの作成**（テスト用）:
```bash
./tools/bin/sail-wp artisan make:factory {テーブル名}Factory --model={テーブル名}
```

### ③ 接続別のパス指定

**モデル配置**:
- Mst: `api/app/Domain/Resource/Mst/Models/`
- その他: `api/app/Domain/{ドメイン}/Models/`

**マイグレーション配置**:
- Mst, Opr: `database/migrations/mst/`
- その他: `database/migrations/`

### ④ Enumの追加（必要な場合）
```php
// api/app/Domain/{ドメイン}/Enums/{Enum名}.php
namespace App\Domain\{ドメイン}\Enums;

enum {Enum名}: string
{
    case Small = 'small';
    case Medium = 'medium';
    case Large = 'large';
}
```

### ⑤ PR作成
- タイトル: `[yml] PR{元のPR番号}`
- 本文に元のglow-schema PRへのリンクを記載

## 3. 品質チェック
```bash
# コード規約チェック
./tools/bin/sail-wp phpcs

# 静的解析
./tools/bin/sail-wp phpstan

# アーキテクチャ依存関係
./tools/bin/sail-wp deptrac

# マイグレーション実行確認
./tools/bin/sail-wp artisan migrate
```

## 4. 注意事項
- **ドメイン選択**: 新規テーブルの場合、適切なドメインに配置
- **命名規則**: Laravelの規約に従う（モデルは単数形、テーブルは複数形）
- **インデックス**: パフォーマンスを考慮し、必要なインデックスをマイグレーションで定義
- **複数DB接続**: 複数のDB接続を使用しているため、テーブルプレフィックスに応じて適切な接続を選択
- **ドメイン構造**: モデルやリソースファイルの配置はドメイン駆動設計に従う
- **後方互換性**: 既存APIの互換性を保つよう注意

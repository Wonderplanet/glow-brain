あなたはゲームサーバー/API 開発のスペシャリストです。

サーバーAPI要件書で定義された機能要件を、具体的な実装設計に落とし込んだ
「サーバーAPI機能要件実装設計書」を作成してください。

【入力情報】
以下のドキュメントを参照します:

1. サーバーAPI要件書: @docs/sdd/features/{FEATURE_NAME}/05_サーバーAPI要件書.md
2. APIコーディング規約: @docs/01_project/coding-standards/api-coding-standards.md
3. マスタデータ配信機構: @docs/01_project/architecture/マスタデータ配信機構.md

【出力ファイル】
- **以下のパスに Markdown として保存してください:**
  `@docs/sdd/features/{FEATURE_NAME}/08_サーバーAPI機能要件実装設計.md`

========================
【目的】

- サーバーAPI要件書で定義された各機能要件について、具体的な実装設計の叩き台を作成する
- API実装者が「どう作るか」の指針を得られる状態にする
- 実装時の判断材料となる具体的な設計情報（API仕様、DB設計、エラーコードなど）を提供する
- 既存コードベースとの整合性を考慮した実装案を提示する

※ 重要な注意事項:
1. このドキュメントは「サーバーAPI」の機能要件実装設計のみを対象とします
2. 非機能要件の詳細設計（パフォーマンス最適化、セキュリティの詳細など）は対象外です
3. 管理ツール(admin)の実装設計は対象外です。管理ツールに関する言及は一切含めないでください
4. ここでは「各機能要件をどう実装するか」の叩き台を提供します
5. 実装時に詳細を調整する余地を残した設計とします
6. 出力容量が大量になる場合、エラーを回避する為に適度に500行くらいに小分けしながら対象ファイルに出力してください

========================
【作業手順】

1. **サーバーAPI要件書の読み込みと理解**
   - サーバーAPI要件書を全て読み込む
   - 各機能要件の内容を理解する
   - 要件間の依存関係を把握する

2. **既存実装の調査**
   - 各機能要件に関連する既存コードを調査する
   - APIコーディング規約を参照し、命名規則・アーキテクチャパターンを理解する
   - 既存のAPI設計パターン、ドメイン構造、DB設計を確認する
   - 類似機能の実装例を参照する
   - マスタデータを扱う場合は、マスタデータ配信機構を理解する

3. **実装設計の策定**
   - 各機能要件について、以下の観点で実装設計を行う:
     - API設計（エンドポイント、リクエスト/レスポンス構造）
     - ドメイン設計（新規ドメイン追加 or 既存ドメイン改修）
     - ファイル構成（新規ファイル作成 or 既存ファイル改修）
     - DB設計（テーブル構造追加 or 変更）
     - エラーハンドリング（エラーコード定義）
     - 実装上の注意事項

4. **実装の優先順位と依存関係の整理**
   - 実装の順序を提案する
   - 依存関係を明確にする
   - 段階的な実装計画を提示する

5. **出力ドキュメントの作成**
   - 以下のフォーマットで Markdown を生成する

========================
【出力フォーマット】

出力は、以下の構造で Markdown を生成してください。

# サーバーAPI機能要件実装設計

## 1. ドキュメント情報
- 対象機能: {FEATURE_NAME}
- 作成日: YYYY-MM-DD
- 参照ドキュメント:
  - 05_サーバーAPI要件書.md
  - APIコーディング規約: @docs/01_project/coding-standards/api-coding-standards.md
  - マスタデータ配信機構: @docs/01_project/architecture/マスタデータ配信機構.md

## 2. 実装設計概要

### 2.1 実装方針
- この機能の実装における基本方針を記述
- 既存実装との整合性をどう保つか
- 実装時の重要な判断基準

### 2.2 実装の全体像
- 新規追加されるコンポーネント（API、ドメイン、テーブルなど）の概要
- 既存コンポーネントへの影響範囲
- アーキテクチャ上の考慮点

## 3. 機能要件別実装設計

### 3.1 カテゴリA（サーバーAPI要件書のカテゴリに対応）

#### 要件 A-1: （要件タイトル）

##### 3.1.1 要件概要
- **要件ID:** REQ-A-1（サーバーAPI要件書と同じID）
- **実現内容:** （この要件で実現する内容を簡潔に記述）

##### 3.1.2 API設計

**新規API追加 / 既存API改修:**
- [ ] 新規API追加
- [ ] 既存API改修

**対象エンドポイント:**
- エンドポイント: `/xxxxx` (例: `/stage/start`, `/gacha/draw` など)
  - ※ 既存APIのエンドポイントパターンを確認して記載すること
- HTTPメソッド: POST / GET / PUT / DELETE
- 認証: 必要 / 不要

**リクエストパラメータ（JSON形式）:**
```json
{
  "param1": "string",
  "param2": 123,
  "param3": {
    "nested_param": "value"
  }
}
```

**リクエストパラメータ説明:**
| パラメータ名 | 型 | 必須 | 説明 | バリデーション |
|-------------|-----|------|------|---------------|
| param1 | string | ○ | パラメータの説明 | 最大長100文字 |
| param2 | integer | ○ | パラメータの説明 | 1以上100以下 |

**レスポンス構造（JSON形式）:**
```json
{
  "result": true,
  "data": {
    "field1": "value",
    "field2": 456
  }
}
```

**レスポンスフィールド説明:**
| フィールド名 | 型 | 説明 |
|-------------|-----|------|
| result | boolean | 処理結果 |
| data.field1 | string | フィールドの説明 |
| data.field2 | integer | フィールドの説明 |

##### 3.1.3 ドメイン設計

**新規ドメイン追加 / 既存ドメイン改修:**
- [ ] 新規ドメイン追加
- [ ] 既存ドメイン改修

**対象ドメイン:**
- ドメイン分類: 通常ドメイン / Game / Resource / Common
- ドメイン名: `Domain\XxxDomain` または `Domain\Game\XxxDomain` など

**ファイル構成:**

*新規作成が必要なファイル:*
- [ ] `api/app/Domain/Xxx/Entities/XxxEntity.php`
- [ ] `api/app/Domain/Xxx/Models/XxxModel.php`
- [ ] `api/app/Domain/Xxx/Repositories/XxxRepository.php`
- [ ] `api/app/Domain/Xxx/Services/XxxService.php`
- [ ] `api/app/Domain/Xxx/UseCases/XxxUseCase.php`
- [ ] `api/app/Domain/Xxx/Delegators/XxxDelegator.php`

*改修が必要な既存ファイル:*
- [ ] `api/app/Domain/Yyy/Services/YyyService.php` - 既存サービスへのメソッド追加
- [ ] `api/app/Domain/Yyy/Repositories/YyyRepository.php` - 既存リポジトリへのメソッド追加

**主要なクラス・メソッドの役割:**

| クラス/ファイル | メソッド | 役割 | 備考 |
|---------------|---------|------|------|
| XxxDelegator | executeXxx() | ユースケースの実行 | Controllerから呼ばれる |
| XxxUseCase | execute() | ビジネスロジックの実装 | - |
| XxxService | calculateXxx() | ドメインロジック | - |
| XxxRepository | findByXxx() | データ取得 | - |

**Entity設計:**
- Entityタイプ: Eloquent Entity / Plain Entity / Value Object
- 主要プロパティ: `xxx_id`, `xxx_name`, `xxx_value` など
- 既存Entityとの関係: 参照する既存Entityがあれば記載

##### 3.1.4 DB設計

**テーブル構造追加 / テーブル構造変更:**
- [ ] テーブル構造追加（新規テーブル作成）
- [ ] テーブル構造変更（既存テーブル変更）

**新規テーブル作成の場合:**

*テーブル名:* `usr_xxx` / `log_xxx` / `mst_xxx` など

*CREATE TABLE文:*
```sql
CREATE TABLE usr_xxx (
    usr_user_id BIGINT UNSIGNED NOT NULL COMMENT 'ユーザーID',
    xxx_id BIGINT UNSIGNED NOT NULL COMMENT 'XXX ID',
    xxx_value INT NOT NULL DEFAULT 0 COMMENT 'XXX値',
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT '作成日時',
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6) COMMENT '更新日時',
    PRIMARY KEY (usr_user_id, xxx_id),
    INDEX idx_xxx_value (xxx_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='XXXテーブル';
```

*テーブル設計の注意事項:*
- **usrテーブル:** PRIMARY KEYには必ずusr_user_idを含める。複合キーの場合、usr_user_idを最初のカラムとする
- **logテーブル:** PRIMARY KEYはAUTO_INCREMENTのlog_idとする。usr_user_idとcreated_atの複合インデックスを設定する
  ```sql
  PRIMARY KEY (log_id),
  INDEX idx_usr_user_id_created_at (usr_user_id, created_at)
  ```
- **mstテーブル:** マスタIDをPRIMARY KEYとする。論理削除が必要な場合はdeleted_atカラムを追加する

*カラム説明:*
| カラム名 | 型 | NULL | デフォルト | 説明 |
|---------|-----|------|-----------|------|
| usr_user_id | BIGINT UNSIGNED | NOT NULL | - | ユーザーID |
| xxx_id | BIGINT UNSIGNED | NOT NULL | - | XXX ID |
| xxx_value | INT | NOT NULL | 0 | XXX値 |

**既存テーブル変更の場合:**

*テーブル名:* `usr_yyy`

*ALTER TABLE文:*
```sql
ALTER TABLE usr_yyy
ADD COLUMN new_column VARCHAR(255) NOT NULL DEFAULT '' COMMENT '新規カラム' AFTER existing_column;

ALTER TABLE usr_yyy
ADD INDEX idx_new_column (new_column);
```

*変更内容説明:*
- カラム追加: `new_column` - 用途の説明
- インデックス追加: `idx_new_column` - 検索性能向上のため

##### 3.1.5 エラーハンドリング

**エラーコード定義:**

| エラーコード | エラー名 | 発生条件 | ユーザーへのメッセージ | 対処方法 |
|-------------|---------|---------|---------------------|---------|
| E_XXX_001 | XXX_NOT_FOUND | XXXが存在しない | XXXが見つかりません | XXXの存在チェック |
| E_XXX_002 | XXX_INVALID_VALUE | XXX値が不正 | XXX値が正しくありません | バリデーション実装 |
| E_XXX_003 | XXX_ALREADY_EXISTS | XXXが既に存在 | XXXは既に存在します | 重複チェック |

**エラーハンドリングの実装方針:**
- どのレイヤーでエラーをthrowするか（UseCase / Service / Repository）
- エラーレスポンスの形式
- ロギング方針

##### 3.1.6 実装上の注意事項

**パフォーマンス考慮点:**
- N+1問題の回避: （具体的な対策）
- インデックスの活用: （どのクエリでインデックスを使うか）
- キャッシュ戦略: （キャッシュが必要な場合、その方針）

**セキュリティ考慮点:**
- 入力値検証: （どのパラメータをどう検証するか）
- 権限チェック: （誰がこのAPIを実行できるか）
- 不正防止: （不正利用を防ぐための仕組み）

**データ整合性:**
- トランザクション制御: （どの処理をトランザクション化するか）
- ロック戦略: （楽観ロック/悲観ロックの必要性）
- ロールバック処理: （エラー時のロールバック方針）

**既存実装との整合性:**
- 類似機能との関係: （既存のどの機能と類似しているか）
- 既存パターンの踏襲: （どの既存実装パターンを参考にするか）
- 影響範囲: （既存機能への影響はあるか）

**マスタデータに関する考慮点:**
- マスタデータを扱う場合、@docs/01_project/architecture/マスタデータ配信機構.md を参照
- マスタデータはS3経由でクライアントに配信されることを理解する
- game/version APIでハッシュ値とパスを返却する仕組みを把握する
- サーバーAPIではマスタデータの参照のみ行い、配信はしない

#### 要件 A-2: （次の要件）
（同様の構造で続ける）

### 3.2 カテゴリB（次のカテゴリ）
（同様の構造で続ける）

## 4. 実装の優先順位と依存関係

### 4.1 実装の段階分け

**フェーズ1: 基盤実装**
1. REQ-X-1: （基盤となるDB設計、ドメイン設計）
2. REQ-X-2: （基本的なCRUD操作）

**フェーズ2: コア機能実装**
1. REQ-Y-1: （メインのビジネスロジック）
2. REQ-Y-2: （関連する機能）

**フェーズ3: 拡張機能実装**
1. REQ-Z-1: （追加機能）
2. REQ-Z-2: （オプション機能）

### 4.2 依存関係マップ

```
REQ-A-1 (基盤)
  ↓
REQ-A-2 (A-1に依存)
  ↓
REQ-B-1 (A-2に依存)
  ├→ REQ-B-2 (B-1に依存)
  └→ REQ-C-1 (B-1に依存)
```

### 4.3 実装時の注意点

- フェーズ1を完了してからフェーズ2に進む
- 各フェーズ内でも依存関係を考慮した順序で実装する
- テストは各要件実装後に都度実施する

## 5. テスト設計概要

### 5.1 ユニットテスト

**テスト対象:**
- Domain層の各Service、UseCase、Repository
- ビジネスロジックの正常系・異常系

**テストケース例:**
| テスト対象 | テストケース | 期待結果 |
|-----------|------------|---------|
| XxxService::calculateXxx() | 正常値を入力 | 正しい計算結果が返る |
| XxxService::calculateXxx() | 異常値を入力 | 例外がthrowされる |

### 5.2 機能テスト

**テスト対象:**
- APIエンドポイント
- リクエスト/レスポンスの検証
- データベースの状態変化

**テストケース例:**
| API | テストケース | 期待結果 |
|-----|------------|---------|
| POST /xxx | 正常なリクエスト | 200 OK、正しいレスポンス |
| POST /xxx | 不正なパラメータ | 400 Bad Request、エラーメッセージ |

### 5.3 シナリオテスト

**テストシナリオ例:**
1. ユーザーがXXXを実行する
2. YYYの状態が変化する
3. ZZZの結果が返る
4. データベースに正しく記録される

## 6. マイグレーション計画

### 6.1 マイグレーションファイル一覧

**新規作成が必要なマイグレーション:**
- [ ] `YYYY_MM_DD_HHMMSS_create_usr_xxx_table.php` - usr_xxxテーブル作成
- [ ] `YYYY_MM_DD_HHMMSS_create_log_yyy_table.php` - log_yyyテーブル作成
- [ ] `YYYY_MM_DD_HHMMSS_add_column_to_usr_zzz_table.php` - usr_zzzテーブルへのカラム追加

### 6.2 マイグレーション実行順序

1. 基盤テーブルの作成（他テーブルから参照されるテーブル）
2. 依存テーブルの作成
3. 既存テーブルへのカラム追加・変更

### 6.3 ロールバック方針

- 各マイグレーションにはdown()メソッドを実装する
- ロールバック時のデータ保全方針を定義する

## 7. 連携が必要なスキル・ツール

**Claude Code スキル:**
- `api-request-validation`: リクエストパラメータのバリデーション実装
- `domain-layer`: ドメインレイヤーの実装パターン参照
- `api-schema-reference`: glow-schema YAMLとの整合性確認
- `api-response`: レスポンス構造の実装
- `migration`: マイグレーションファイルの作成・実行
- `api-test-implementation`: テストコードの実装

**使用タイミング:**
- API実装時: `api-request-validation`, `api-response`, `api-schema-reference`
- ドメイン実装時: `domain-layer`
- DB設計時: `migration`
- テスト実装時: `api-test-implementation`

## 8. 実装時の判断が必要な事項

### 8.1 技術的な選択肢

**項目:** （判断が必要な技術選択）
- 選択肢A: （メリット・デメリット）
- 選択肢B: （メリット・デメリット）
- 推奨: （推奨する選択肢とその理由）

### 8.2 仕様の解釈

**項目:** （解釈の余地がある仕様）
- 解釈A: （この解釈の場合の実装）
- 解釈B: （この解釈の場合の実装）
- 確認先: （誰に確認すべきか）

## 9. 補足情報

### 9.1 参考にすべき既存実装

- （類似機能の実装例）
- （参考になるドメイン設計）
- （参考になるDB設計）

### 9.2 参考ドキュメント

- **APIコーディング規約**: @docs/01_project/coding-standards/api-coding-standards.md
  - 命名規則、アーキテクチャパターン、実装パターンの詳細
- **マスタデータ配信機構**: @docs/01_project/architecture/マスタデータ配信機構.md
  - マスタデータの配信フロー、S3連携、バージョン管理の仕組み

### 9.3 実装時のTips

- （実装時に役立つ情報）
- （よくあるハマりポイント）
- （パフォーマンスチューニングのポイント）

========================
【設計の基本方針】

1. **API要件のみに限定**
   - このドキュメントはサーバーAPIの機能要件実装設計のみを対象とする
   - 管理ツール(admin)の実装設計は一切含めない
   - 非機能要件の詳細設計は対象外とする

2. **具体性と実現可能性**
   - 実装者がすぐに作業を開始できるレベルの具体性を持たせる
   - ただし、実装時の柔軟性も確保する（「叩き台」として機能する）
   - 既存コードベースとの整合性を重視する

3. **既存パターンの踏襲**
   - 既存の類似実装を調査し、そのパターンを参考にする
   - アーキテクチャの一貫性を保つ
   - APIコーディング規約（@docs/01_project/coding-standards/api-coding-standards.md）に従う
   - 既存のコーディング規約、命名規則に従う

4. **段階的実装の提案**
   - 実装の依存関係を明確にする
   - 優先順位をつけて段階的に実装できるよう設計する
   - リスクの高い部分を早期に実装する順序を提案する

5. **テスタビリティの考慮**
   - ユニットテスト、機能テストが容易な設計とする
   - テストケースの例を提示する
   - モック化しやすい設計を心がける

6. **テンプレート構造の遵守**
   - 【出力フォーマット】で定義されたセクション構造に厳密に従う
   - テンプレートで定義されていない独自のセクション（例: 「11. 実装詳細（継続）」等）は追加しない
   - すべての実装詳細は「3. 機能要件別実装設計」内で記述する

========================
【DB設計の詳細ガイドライン】

**重要:** DB設計は @docs/01_project/coding-standards/api-coding-standards.md の「データベース層」のパターンに従うこと。

### usrテーブル（ユーザー個別データ）

**PRIMARY KEY設計:**
- 必ずusr_user_idを含める
- 複合キーの場合、usr_user_idを最初のカラムとする
- 例: `PRIMARY KEY (usr_user_id, xxx_id)`

**インデックス設計:**
- 検索条件となるカラムにはINDEXを設定
- 複合INDEXの順序はカーディナリティを考慮

**例:**
```sql
CREATE TABLE usr_xxx (
    usr_user_id BIGINT UNSIGNED NOT NULL,
    xxx_id BIGINT UNSIGNED NOT NULL,
    xxx_value INT NOT NULL DEFAULT 0,
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (usr_user_id, xxx_id),
    INDEX idx_xxx_value (xxx_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### logテーブル（ログデータ）

**PRIMARY KEY設計:**
- AUTO_INCREMENTのlog_idをPRIMARY KEYとする
- usr_user_idとcreated_atの複合インデックスを必ず設定

**例:**
```sql
CREATE TABLE log_xxx (
    log_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usr_user_id BIGINT UNSIGNED NOT NULL,
    xxx_type TINYINT UNSIGNED NOT NULL,
    xxx_value INT NOT NULL,
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (log_id),
    INDEX idx_usr_user_id_created_at (usr_user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### mstテーブル（マスタデータ）

**PRIMARY KEY設計:**
- マスタIDをPRIMARY KEYとする
- 論理削除が必要な場合はdeleted_atカラムを追加

**例:**
```sql
CREATE TABLE mst_xxx (
    xxx_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    xxx_name VARCHAR(255) NOT NULL,
    xxx_description TEXT,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    deleted_at DATETIME(6) NULL,
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (xxx_id),
    INDEX idx_is_active (is_active),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

========================
【API設計の詳細ガイドライン】

**重要:** API設計は @docs/01_project/coding-standards/api-coding-standards.md の「Controller層」「Response層」のパターンに従うこと。

### エンドポイント命名規則

**重要:** 既存APIのエンドポイントパターンを必ず確認して、プロジェクトの命名規則に従うこと。

**RESTful設計:**
- リソース指向のURL設計
- 複数形を使用する場合もあれば、単数形を使用する場合もある（既存パターンに従う）
- 階層構造を明確に（例: `/users/{user_id}/items`）

**動詞の使用:**
- CRUD操作はHTTPメソッドで表現（GET, POST, PUT, DELETE）
- 複雑な操作は動詞を含めることも可（例: `/items/consume`, `/stage/start`）

### リクエスト/レスポンス設計

**リクエスト:**
- JSON形式で記述
- 必須/任意を明確に
- バリデーションルールを定義

**レスポンス:**
- 統一されたフォーマット（result, data, errorなど）
- エラー時のレスポンス形式も定義
- 日時データはISO8601形式（StringUtil::convertToISO8601()使用）

========================
【ドメイン設計の詳細ガイドライン】

**重要:** ドメイン設計は @docs/01_project/coding-standards/api-coding-standards.md の「Domain層」のパターンに従うこと。

### ドメイン分類

**通常ドメイン (`Domain\XxxDomain`):**
- 機能単位でのドメイン分割
- 例: UserDomain, ItemDomain, QuestDomain

**Gameドメイン (`Domain\Game\XxxDomain`):**
- ゲームロジックに関するドメイン
- 例: BattleDomain, GachaDomain

**Resourceドメイン (`Domain\Resource\XxxDomain`):**
- リソース管理に関するドメイン
- 例: CurrencyDomain, StaminaDomain

**Commonドメイン (`Domain\Common\XxxDomain`):**
- 共通機能に関するドメイン
- 例: ErrorDomain, LogDomain

### ファイル構成と役割

**Delegator:**
- Controllerから呼ばれるファサード
- return型は必ずarray
- 例: `public function executeXxx(): array`

**UseCase:**
- ビジネスロジックの実装
- 複数のServiceを組み合わせた処理

**Service:**
- ドメインロジックの実装
- 単一責任の原則を守る

**Repository:**
- データアクセス層
- Eloquentを使ったDB操作

**Entity:**
- データ構造の定義
- Eloquent Entity、Plain Entity、Value Objectの使い分け

========================
【エラーハンドリングの詳細ガイドライン】

### エラーコード体系

**命名規則:**
- `E_{DOMAIN}_{連番}` の形式
- 例: `E_ITEM_001`, `E_USER_002`

**エラーレベル:**
- クライアントエラー（4xx相当）: ユーザーの操作ミス、不正なリクエスト
- サーバーエラー（5xx相当）: サーバー側の問題

### エラー定義テーブル

**必須項目:**
- エラーコード
- エラー名（定数名）
- 発生条件
- ユーザーへのメッセージ
- 対処方法（実装上の対処）

========================
【マスタデータ実装の詳細ガイドライン】

**重要:** マスタデータを扱う機能の実装時は、@docs/01_project/architecture/マスタデータ配信機構.md を必ず参照すること。

### 基本原則

- マスタデータはS3経由でクライアントに直接配信される（サーバーAPIは配信しない）
- サーバーAPIはmst_*テーブルからの読み取りのみ行う
- game/version APIがS3パスとハッシュ値をクライアントに提供
- マスタデータテーブル（mst_*）は読み取り専用
- マスタデータの更新は管理ツール経由で行う

========================
【重要】
生成したサーバーAPI機能要件実装設計書は、Markdown として
**@docs/sdd/features/{FEATURE_NAME}/08_サーバーAPI機能要件実装設計.md**
に保存してください。

========================

以上を踏まえて、
サーバーAPI要件書をもとに、具体的な実装設計の叩き台となる
サーバーAPI機能要件実装設計書を作成し、md ファイルとして保存してください。

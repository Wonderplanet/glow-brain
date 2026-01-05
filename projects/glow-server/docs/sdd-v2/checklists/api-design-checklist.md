# API設計書 品質チェックリスト

API設計書の作成・レビュー時に使用するチェックリストです。
SDD v2 の API設計フェーズで品質を担保するために使用してください。

## 目次

- [カテゴリA: API設計の規約チェック](#カテゴリa-api設計の規約チェック)
- [カテゴリB: レスポンス構造チェック](#カテゴリb-レスポンス構造チェック)
- [カテゴリC: 実装ガイダンスチェック](#カテゴリc-実装ガイダンスチェック)
- [カテゴリD: アーキテクチャ方針チェック](#カテゴリd-アーキテクチャ方針チェック)

---

## カテゴリA: API設計の規約チェック

### A1: パスパラメータの禁止

**ルール:** 全てのパラメータはリクエストボディで指定する。パスパラメータは使用禁止。

| チェック項目 | 確認内容 |
|------------|---------|
| - [ ] パスパラメータ不使用 | `/api/xxx/{id}` ではなく `/api/xxx` + リクエストボディで `id` を渡す設計になっているか<br>※ `Skill(api-architecture-guide)` で既存パターンを確認 |

**✅ 正しい例:**
```
POST /api/stage/end
リクエストボディ: { "mstStageId": 123 }
```

**❌ 間違った例:**
```
POST /api/stage/{mstStageId}/end
```

**根拠:** `api/routes/api.php` の既存エンドポイント定義に従う。

---

### A2: 一覧取得APIの禁止

**ルール:** 一覧データの取得は `game/update_and_fetch` の `fetchOther` パラメータで対応する。個別の一覧取得APIは作成しない。

| チェック項目 | 確認内容 |
|------------|---------|
| - [ ] 一覧取得APIなし | `GET /api/xxx/list` のような一覧取得専用APIを追加していないか |
| - [ ] fetchOther活用 | 一覧データが必要な場合、`fetchOther` での取得を設計しているか |

**背景:** クライアントは `game/update_and_fetch` API で必要なデータを一括取得する設計。

---

### A3: oprテーブルの新規追加禁止

**ルール:** マスタデータは全て `mst_*` テーブルに追加する。`opr_*` テーブルの新規追加は禁止。

| チェック項目 | 確認内容 |
|------------|---------|
| - [ ] oprテーブル不使用 | 新規の `opr_*` テーブルを追加していないか<br>※ `Skill(migration)` の命名規則を参照 |
| - [ ] mstテーブル使用 | マスタデータは `mst_*` テーブルで設計しているか |

**背景:** `opr_*` は旧仕様。現在はマスタデータを `mst_*` に統一。

---

## カテゴリB: レスポンス構造チェック

### B1: キー命名規則の統一

**ルール:** レスポンスキーは `camelCase` で統一し、glow-schema の YAML 定義と一致させる。

| チェック項目 | 確認方法 |
|------------|---------|
| - [ ] キー命名規則 | `Skill(api-response)` の common-rules.md で確認（camelCase + glow-schema一致） |

---

### B2: BaseReward構造の使用

**ルール:** 報酬データは `getRewardResponseData()` を経由した統一構造で返す。

| チェック項目 | 確認方法 |
|------------|---------|
| - [ ] BaseReward構造 | `Skill(api-response)` の common-rules.md で確認（getRewardResponseData()の使用） |

---

### B3: 日時データの変換ルール

**ルール:** 全ての日時データは `StringUtil::convertToISO8601()` で変換してレスポンスする。

| チェック項目 | 確認方法 |
|------------|---------|
| - [ ] 日時データ変換 | `Skill(api-response)` の common-rules.md で確認（StringUtil::convertToISO8601()の使用） |

---

### B4: ResponseDataFactoryの活用

**ルール:** 既存の `ResponseDataFactory` メソッドを活用し、レスポンス構造を統一する。

| チェック項目 | 確認方法 |
|------------|---------|
| - [ ] ResponseDataFactory活用 | `Skill(api-response)` の response-data-factory-guide.md と common-rules.md で確認 |

---

## カテゴリC: 実装ガイダンスチェック

### C1: トランザクション制御の記載

**ルール:** ユーザーデータ更新を伴う API は、`applyUserTransactionChanges` の使用を明記する。

| チェック項目 | 確認方法 |
|------------|---------|
| - [ ] トランザクション制御 | `Skill(apply-user-transaction-changes)` で使用条件と実装パターンを確認 |

---

### C2: データ出所の明確化

**ルール:** API設計書では、各データの出所（mst/usr/log）を明確にする。

| チェック項目 | 確認内容 |
|------------|---------|
| - [ ] データ出所明記 | レスポンスの各フィールドがどのテーブルから取得されるか記載されているか |
| - [ ] DB種別区別 | mst（マスタ）/ usr（ユーザー）/ log（ログ）の区別が明確か |

**✅ 良い例:**
```markdown
**レスポンス説明:**
- `usrParameter`: usr_user_parameters から取得
- `mstStageInfo`: mst_stages + mst_stage_rewards から取得
- `rewards`: 配布処理で生成、log_reward_histories に記録
```

---

## カテゴリD: アーキテクチャ方針チェック

### D1: テーブル設計方針

**ルール:** 新規テーブル追加より既存テーブルの拡張を優先する。

| チェック項目 | 確認方法 |
|------------|---------|
| - [ ] テーブル設計方針 | `Skill(migration)` の命名規則と `Skill(domain-layer)` でアーキテクチャ方針を確認 |

---

## 使用方法

### API設計書作成時

1. API設計書の初稿を作成
2. 本チェックリストの全項目を確認
3. 問題があれば修正
4. refine フェーズに進む

### レビュー時

1. 本チェックリストを用いてレビュー
2. 各カテゴリでNGがあれば指摘
3. 特に B（レスポンス構造）は重点的に確認

---

## 関連ドキュメント

### Skills

- `Skill(api-response)` - APIレスポンス実装（B1, B2, B3, B4）
  - `common-rules.md` - レスポンス共通ルール
  - `response-data-factory-guide.md` - ResponseDataFactory実装ガイド
- `Skill(api-architecture-guide)` - アーキテクチャガイド（A1）
- `Skill(apply-user-transaction-changes)` - トランザクション制御（C1）
- `Skill(migration)` - マイグレーション実装（A3, D1）
- `Skill(domain-layer)` - ドメイン層実装（D1）

### 参照ファイル

- `api/routes/api.php` - 既存エンドポイント定義
- `api/app/Http/ResponseFactories/ResponseDataFactory.php` - 既存メソッド一覧
- `api/app/Domain/Common/Traits/UseCaseTrait.php` - トランザクション制御の実装元

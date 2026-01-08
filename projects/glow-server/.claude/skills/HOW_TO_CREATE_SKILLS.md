# Claude Code スキル作成ガイド

migration skillの作成経験を基に、効果的なスキルを作成するためのガイドラインをまとめました。

## 目次

1. [スキル作成の基本フロー](#スキル作成の基本フロー)
2. [SKILL.mdの構造](#skillmdの構造)
3. [Progressive Disclosure（段階的情報開示）](#progressive-disclosure段階的情報開示)
4. [ファイル分割の戦略](#ファイル分割の戦略)
5. [実例の収集と活用](#実例の収集と活用)
6. [チェックリストとベストプラクティス](#チェックリストとベストプラクティス)

---

## スキル作成の基本フロー

### ステップ1: スキルの目的を明確にする

**質問すべきこと:**
- このスキルは何を支援するのか？
- どのような状況で使われるのか？
- 対象ユーザーの技術レベルは？

**migration skillの例:**
- 目的: 複数DB（mst/mng/usr/log/sys/admin）のLaravelマイグレーション実装を支援
- 状況: テーブル作成・変更、マイグレーション実行、ロールバック時
- ユーザー: glow-serverプロジェクトの開発者

### ステップ2: 既存コードを調査する

**調査すべきポイント:**
- 既存の実装パターンを確認
- 命名規則や規約を抽出
- よくある間違いを特定
- プロジェクト固有の制約を理解

**migration skillでの実施内容:**
```bash
# 既存のマイグレーションファイルを調査
find api/database/migrations/mst -name "*.php" -type f | head -5
find api/database/migrations/mng -name "*.php" -type f | head -5
find admin/database/migrations -name "*.php" -type f | head -5

# 実際のファイルを読み込んで実装パターンを理解
Read api/database/migrations/mst/2025_06_03_024458_create_table_mst_pvps.php
```

### ステップ3: ルールを分類・整理する

**分類軸:**
1. **共通ルール**: すべてのケースで適用
2. **パターン別ルール**: 特定の状況でのみ適用
3. **制約・禁止事項**: やってはいけないこと
4. **ベストプラクティス**: 推奨される方法

**migration skillの分類例:**
- 共通ルール → `common-rules.md`
- 命名規則 → `naming-conventions.md`
- DB接続別パターン → `examples-mst-mng.md`, `examples-usr-log-sys.md`, `examples-admin.md`
- コマンド実行 → `commands.md`

### ステップ4: ファイル構成を設計する

**原則:**
- SKILL.mdは500行以下（できれば100行以下）
- 参照ファイルは1レベル深まで
- ファイルが100行を超えたら目次を追加

---

## SKILL.mdの構造

### 必須フォーマット

```markdown
---
name: スキル名（簡潔に）
description: スキルの説明。何をするスキルか、いつ使うかを明記。1-2文で簡潔に。
---

# スキル名

## Instructions

[ステップバイステップのガイダンス]

## Examples

[具体的な実装例]
```

### 良いSKILL.mdの特徴

**✅ 良い例（migration skill）:**
```markdown
## Instructions

マイグレーション実装は以下の順序で進めてください：

1. **共通ルールを確認** → [common-rules.md](common-rules.md) で必須ルールを確認
2. **テーブル名からDB接続を判断** → テーブル接頭辞から判断
3. **命名規則を確認** → [naming-conventions.md](naming-conventions.md) を参照
4. **実装例を参照** → 該当するDB接続パターンの実装例を確認
5. **コマンド実行** → [commands.md](commands.md) で実行方法を確認
6. **最終チェック** → [reference.md](reference.md) でチェック

### 参照ドキュメント

- **[common-rules.md](common-rules.md)** - 全DB共通の必須ルール
- **[naming-conventions.md](naming-conventions.md)** - テーブル命名規則
...
```

**特徴:**
- 手順が明確（番号付きリスト）
- 各ステップで参照すべきファイルが明記
- 参照ドキュメントの役割が一目で分かる

**❌ 悪い例:**
```markdown
## Instructions

マイグレーションを実装してください。
詳細は以下を参照してください。
- 色々なルールがあります
- よく読んでください
```

**問題点:**
- 手順が不明確
- 参照先の役割が不明
- 何から始めればいいか分からない

### Examplesセクションの設計

**原則:**
- SKILL.mdには最小限の例のみ（または参照のみ）
- 詳細な実装例は別ファイルに分割

**migration skillの実装:**
```markdown
## Examples

具体的な実装例は、各DB接続パターンごとのドキュメントを参照してください：

- **mst/mng接続**: [examples-mst-mng.md](examples-mst-mng.md)
- **usr/log/sys接続**: [examples-usr-log-sys.md](examples-usr-log-sys.md)
- **admin接続**: [examples-admin.md](examples-admin.md)
```

---

## Progressive Disclosure（段階的情報開示）

### 基本原則

**レベル1（SKILL.md）: 概要と道筋**
- 何をするスキルか
- どの順序で進めるか
- どこを参照すべきか

**レベル2（参照ファイル）: 詳細ルールと実装例**
- 具体的なルール
- 実装パターン
- よくある間違い

### 情報の階層化例（migration skill）

```
SKILL.md (40行)
├── Instructions (手順の概要)
└── Examples (参照のみ)

common-rules.md (390行)
├── timestampTz()の使用
├── created_at/updated_atの配置
├── カラム追加時のafter()
├── enum型の使用制限
├── コメントの記述
└── down()メソッドの実装

naming-conventions.md (300行)
├── 基本ルール
├── 命名パターン
├── 実例集
└── よくある間違い

examples-mst-mng.md (278行)
├── 概要（このパターンの特徴）
├── テーブル作成例（実際のコードをそのまま掲載）
├── コマンド実行例
└── チェックポイント
```

### ファイル分割の判断基準

**1つのファイルにまとめる場合:**
- 内容が密接に関連している
- 合計100行以下
- 分割すると理解しにくくなる

**ファイルを分割する場合:**
- 独立したトピック
- 100行を超える
- パターンごとに内容が大きく異なる

**migration skillの分割判断:**
```
✅ 分割した理由:
- 共通ルール vs 命名規則 → 独立したトピック
- DB接続ごとの実装例 → パターンが大きく異なる
- コマンド実行 → 実行手順として独立

❌ 分割しなかった理由:
- timestampTz()とcreated_at配置 → 同じ「共通ルール」カテゴリ
```

---

## ファイル分割の戦略

### 良いファイル構成の例（migration skill）

```
.claude/skills/migration/
├── SKILL.md (40行)
│   └── 概要、手順、参照リスト
├── common-rules.md (390行)
│   └── 全DB共通のルール
├── naming-conventions.md (300行)
│   └── テーブル命名規則に特化
├── commands.md (305行)
│   └── sailコマンドの実行方法
├── examples-mst-mng.md (278行)
│   └── mst/mng接続の実装例
├── examples-usr-log-sys.md (352行)
│   └── usr/log/sys接続の実装例
├── examples-admin.md (365行)
│   └── admin接続の実装例
└── reference.md (528行)
    └── 詳細リファレンス（トラブルシューティング等）
```

### ファイル命名規則

**✅ 良い命名:**
- `common-rules.md` - 役割が明確
- `examples-mst-mng.md` - 対象が明確
- `naming-conventions.md` - 内容が一目で分かる

**❌ 悪い命名:**
- `misc.md` - 何が書いてあるか不明
- `rules1.md`, `rules2.md` - 番号では区別できない
- `important.md` - 抽象的すぎる

### 目次の活用

**100行を超えるファイルには必ず目次を追加:**

```markdown
# マイグレーション共通ルール

すべてのDB接続で共通して守るべきルールをまとめています。

## 目次

1. [timestampTz()の使用（必須）](#1-timestamptzの使用必須)
2. [created_at/updated_atは必ず最後の列](#2-created_atupdated_atは必ず最後の列)
3. [カラム追加時のafter()指定（必須）](#3-カラム追加時のafter指定必須)
4. [enum型の使用制限（TiDBでは禁止）](#4-enum型の使用制限tidbでは禁止)
5. [コメントの記述](#5-コメントの記述)
6. [down()メソッドの実装](#6-downメソッドの実装)
```

---

## 実例の収集と活用

### 既存コードから実例を収集

**migration skillでの実践:**

1. **パターンの特定:**
```bash
# mst接続のパターンを確認
ls api/database/migrations/mst/ | head -5

# 実際のファイルを読み込み
Read api/database/migrations/mst/2025_06_03_024458_create_table_mst_pvps.php
```

2. **実例をそのまま掲載:**
```markdown
### 例1: mst接続 - PVP関連テーブル作成

**ファイル**: `api/database/migrations/mst/2025_06_03_024458_create_table_mst_pvps.php`

```php
<?php

use App\Domain\Constants\Database;
// ... 実際のコードをそのまま掲載 ...
```

**ポイント**:
- ファイルパスを明記
- コード全体を掲載（一部省略しない）
- 重要な箇所にコメントを追加
```

### 良い例 vs 悪い例の対比

**効果的なパターン:**

```markdown
### ✅ 正しい例

```php
$table->timestampTz('created_at');
$table->timestampTz('updated_at');
```

### ❌ 間違った例

```php
$table->timestamp('created_at');  // ❌ timestamp()は使用禁止
$table->timestamps();             // ❌ ヘルパーも禁止
```

**理由**: timestampTz()を使用することで...
```

**効果:**
- 何が正しくて何が間違いか一目瞭然
- 理由も明記することで理解が深まる
- 間違いを未然に防げる

---

## チェックリストとベストプラクティス

### チェックリストの設計

**良いチェックリストの特徴:**
1. 具体的で実行可能
2. 優先度が分かる
3. Yes/Noで判断できる

**migration skillのチェックリスト:**
```markdown
## チェックリスト

マイグレーション作成時に必ず確認してください：

- [ ] timestampTz()を使用しているか（timestamp()は禁止）
- [ ] created_at/updated_atが最後の列になっているか
- [ ] カラム追加時にafter()を指定しているか
- [ ] **enum型はTiDB（usr/log/sys）で使用していないか** ★重要
- [ ] usr/log/sysでenum相当の値はvarchar(255)を使用しているか
- [ ] varchar使用時、コメントに取りうる値を明記しているか
- [ ] すべてのカラムにコメントを記述しているか
- [ ] down()メソッドを適切に実装しているか
- [ ] 外部キーのコメントは正確か（テーブル名.カラム名）
- [ ] 複数テーブル作成時、down()で逆順に削除しているか
```

### 比較表の活用

**複雑な使い分けは表で整理:**

```markdown
| DB接続 | enum型 | 推奨型 | 理由 |
|--------|--------|--------|------|
| mst | ✅ 使用可 | enum | マスターデータ、レコード数が少ない |
| usr | ❌ 禁止 | varchar(255) | ユーザーデータ、大量レコード |
```

**効果:**
- 複数の選択肢を一覧で比較できる
- 判断基準が明確になる

---

## スキル作成のワークフロー（実践編）

### フェーズ1: 調査と収集（1-2時間）

**タスク:**
1. 既存コードを検索・調査
2. パターンを抽出
3. ルールを仮リスト化

**migration skillでの実施:**
```bash
# 1. 既存ファイルの調査
find api/database/migrations -name "*.php" | wc -l  # 全体量を把握
ls -la api/database/migrations/mst/  # ディレクトリ構造確認

# 2. サンプル読み込み
Read api/database/migrations/mst/2025_06_03_024458_create_table_mst_pvps.php
Read api/database/migrations/mng/2025_05_07_120000_create_mng_messages_tables.php
...

# 3. 定数ファイル確認
Read api/app/Domain/Constants/Database.php
```

### フェーズ2: ドキュメント構造の設計（30分）

**タスク:**
1. ルールをカテゴリ分け
2. ファイル構成を決定
3. 各ファイルの役割を明確化

**migration skillの設計:**
```
SKILL.md: 概要とナビゲーション
├─ common-rules.md: 全DB共通ルール
├─ naming-conventions.md: 命名規則
├─ commands.md: コマンド実行
├─ examples-*.md: DB接続別の実装例
└─ reference.md: 詳細リファレンス
```

### フェーズ3: 初期ドラフト作成（1-2時間）

**タスク:**
1. SKILL.mdを作成
2. 主要な参照ファイルを作成
3. 実例を収集・掲載

**注意点:**
- 最初は詳細すぎても良い（後で削減）
- 実例は実際のコードをそのまま使う
- ルールには必ず理由を記載

### フェーズ4: 改善とリファクタリング（1-2時間）

**タスク:**
1. ファイルサイズをチェック（500行超えていないか）
2. 重複を排除
3. Progressive Disclosureを適用
4. チェックリストを追加

**migration skillでの改善例:**
```
Before:
- SKILL.md: 153行（詳細な実装例を含む）
- examples.md: 529行（すべてのDB接続を1ファイルに）

After:
- SKILL.md: 40行（参照のみ）
- examples-mst-mng.md: 278行
- examples-usr-log-sys.md: 352行
- examples-admin.md: 365行
```

### フェーズ5: レビューと検証（30分）

**チェック項目:**
- [ ] SKILL.mdは500行以下か
- [ ] 各ファイルの役割が明確か
- [ ] 参照は1レベル深までか
- [ ] 100行超のファイルに目次があるか
- [ ] チェックリストが実行可能か
- [ ] 実例が十分に掲載されているか

---

## よくある失敗パターンと対策

### 失敗1: SKILL.mdに詳細を詰め込みすぎ

**症状:**
- SKILL.mdが500行を超える
- スクロールしないと全体が見えない

**対策:**
- SKILL.mdは「目次」と割り切る
- 詳細は参照ファイルに分離

**migration skillでの対応:**
```
Before: SKILL.mdに実装例を3つ掲載（153行）
After: SKILL.mdは参照リストのみ（40行）
```

### 失敗2: ファイルを分割しすぎ

**症状:**
- 10個以上のファイルに分割
- どれを見ればいいか分からない
- ファイル間の関係が不明確

**対策:**
- 関連する内容は1ファイルにまとめる
- 分割は明確な理由がある場合のみ
- SKILL.mdで参照順序を明示

### 失敗3: 実例が不足

**症状:**
- ルールだけで実装例がない
- 架空の例ばかり
- 実際のコードとの乖離

**対策:**
- 既存コードを積極的に掲載
- ファイルパスを明記
- よくある間違いも例示

### 失敗4: 更新時の不整合

**症状:**
- ルール追加時、SKILL.mdの目次を更新し忘れ
- チェックリストが古いまま
- 複数ファイルで矛盾

**対策:**
- ルール追加時は影響範囲を確認
- チェックリストも同時更新
- 全ファイルの整合性を確認

---

## スキル作成のチェックリスト

### 設計段階

- [ ] スキルの目的が明確に定義されているか
- [ ] 対象ユーザーと使用状況が明確か
- [ ] 既存コードを十分に調査したか
- [ ] ルールのカテゴリ分けが適切か
- [ ] ファイル構成が設計されているか

### 実装段階

- [ ] SKILL.mdが500行以下か
- [ ] 各ファイルの役割が明確か
- [ ] 参照は1レベル深までか
- [ ] 100行超のファイルに目次があるか
- [ ] 実例が十分に掲載されているか
- [ ] 良い例と悪い例が対比されているか
- [ ] チェックリストが実行可能か

### 完成後

- [ ] 全ファイルの整合性が取れているか
- [ ] ファイルパスが正確か
- [ ] リンクが正しく機能するか
- [ ] 用語の使い方が統一されているか
- [ ] コメントや理由が十分に記載されているか

---

## まとめ

### 効果的なスキル作成の3原則

1. **Progressive Disclosure（段階的情報開示）**
   - SKILL.mdは概要とナビゲーション
   - 詳細は参照ファイルに分離
   - 情報を階層化する

2. **実例ベース**
   - 既存コードを積極的に活用
   - 架空の例より実際のコード
   - 良い例と悪い例を対比

3. **実行可能なチェックリスト**
   - 具体的で判断しやすい項目
   - Yes/Noで答えられる
   - 優先度を明示

### migration skillから学んだこと

**成功要因:**
- 既存コードの徹底調査
- DB接続ごとのファイル分割
- enum禁止などプロジェクト固有ルールの明記
- 実際のマイグレーションファイルをそのまま掲載

**改善プロセス:**
1. 最初は1ファイルに詰め込んだ
2. ユーザーフィードバックで分割の必要性を認識
3. Progressive Disclosureを適用
4. 共通ルールを別ファイルに分離
5. 実例をDB接続別に整理

### 次回スキル作成時の推奨フロー

```
1. 調査（既存コード・規約の理解）
   ↓
2. 設計（ファイル構成・カテゴリ分け）
   ↓
3. ドラフト作成（SKILL.md + 主要ファイル）
   ↓
4. 実例収集（既存コードから抽出）
   ↓
5. リファクタリング（Progressive Disclosure適用）
   ↓
6. 検証（チェックリスト確認）
   ↓
7. 完成
```

### 参考リンク

- [Official: Write SKILL.md](https://docs.claude.com/en/docs/claude-code/skills#write-skill-md)
- [Official: Best Practices](https://docs.claude.com/en/docs/agents-and-tools/agent-skills/best-practices)
- [Official: Overview](https://docs.claude.com/en/docs/agents-and-tools/agent-skills/overview#how-skills-work)
- [glow-server migration skill](.claude/skills/migration/)

---

**このガイドを活用して、効果的なClaude Codeスキルを作成してください！**

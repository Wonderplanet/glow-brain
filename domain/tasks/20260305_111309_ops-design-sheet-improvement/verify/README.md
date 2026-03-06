# 検証モード: masterdata-ops-creator スキル検証環境

`masterdata-ops-creator` スキルを過去リリース済みデータで答え合わせ検証するための環境です。
AIが「答え」に相当するCSVを直接読めないようアクセス制限を設けています。

---

## 参照ルール

| データ | パス | 参照可否 |
|--------|------|---------|
| 対象リリース前の状態（past_tables） | `verify/past_tables/` にコピー済み | **OK** |
| 対象リリースのテーブル（答え） | `domain/raw-data/masterdata/released/{key}/tables/` | **NG（ブロック）** |
| 現在の本番マスタデータ | `projects/glow-masterdata/**` | **NG（ブロック）** |

---

## 使い方

### Step 1: past_tables の準備

検証セッション起動前（ブロックなし状態）に、対象リリースキーの `past_tables` をコピーします。

```bash
# 例: 202602015 を検証する場合
cp -r domain/raw-data/masterdata/released/202602015/past_tables/. \
       domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/past_tables/
```

> **注意**: 必ず検証セッション起動前（通常モード）でコピーすること。
> コピー後のパス `verify/past_tables/` はブロック対象外なので自由に読める。

`masterdata-releasekey-reporter` スキルで `past_tables` を生成していない場合は先にそちらを実行してください。

### Step 2: 検証セッションを起動

プロジェクトルートから以下のコマンドで検証モードを起動します。

```bash
claude --settings domain/tasks/20260305_111309_ops-design-sheet-improvement/verify/verify-mode-settings.json
```

このセッションでは以下のパスへのアクセスがブロックされます:
- `domain/raw-data/masterdata/**`（Read ツール + Bash）
- `projects/glow-masterdata/**`（Read ツール + Bash）

### Step 3: スキルを呼び出してCSVを生成

検証セッション内でスキルを呼び出します。

```
/masterdata-ops-creator
```

スキルへの指示例:
```
リリースキー 202602015 のマスタデータを生成してください。
past_tables の参照先は verify/past_tables/ です。
```

### Step 4: 生成物と答えを比較

検証セッションを終了した後（通常モードで）、生成物と答えを比較します。

```bash
# 生成されたCSVと答えのdiff確認
diff -r {生成物の出力先}/ domain/raw-data/masterdata/released/202602015/tables/
```

---

## ブロックの仕組み

### Read ツール制限

`verify-mode-settings.json` の `permissions.deny` で設定:
```json
"deny": [
  "Read(./domain/raw-data/masterdata/**)",
  "Read(./projects/glow-masterdata/**)"
]
```

### Bash 迂回防止

`hooks/block-answer-files.sh` が PreToolUse フックとして動作し、
Bash コマンドにブロック対象パスが含まれる場合は exit 2 でブロックします。

---

## 残る制限の限界

| リスク | 説明 |
|--------|------|
| モデルの学習済み知識 | 防げない（設定で対処不可） |
| Glob でCSVファイル名列挙 | パス一覧取得はブロックされない（内容は読めない） |
| DBスキーマからの推論 | カラム名・型から正解の一部を推測できる可能性 |

→ 「直接ファイルを読んで値をコピーする」という最も明白なカンニング経路は塞いでいます。

---

## ファイル構成

```
verify/
├── README.md                  ← このファイル
├── past_tables/               ← masterdata-releasekey-reporter でコピーしたデータ置き場
│   └── .gitkeep               ← ディレクトリ維持用（コミット対象）
└── verify-mode-settings.json  ← --settings で渡す検証用設定
hooks/
└── block-answer-files.sh      ← PreToolUse hook スクリプト
```

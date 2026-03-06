# MstHomeKomaPattern 詳細説明

> CSVパス: `projects/glow-masterdata/MstHomeKomaPattern.csv`
> i18n CSVパス: `projects/glow-masterdata/MstHomeKomaPatternI18n.csv`

---

## 概要

`MstHomeKomaPattern` は**ホーム画面に配置されるコマ（ユニット）の配置パターンを定義するテーブル**。各パターンがどのような配置レイアウトを使うかをアセットキーで管理し、多言語対応のパターン名は `MstHomeKomaPatternI18n` で管理する。

「コマパターン」とは、ホーム画面においてどのように複数のキャラクターユニットを並べて表示するか（配置レイアウト）のプリセット設定のことを指す。

### ゲームプレイへの影響

- プレイヤーはホーム画面に表示するコマのパターンを選択できる。このテーブルが選択可能なパターン一覧を定義する。
- **asset_key** でパターンのレイアウト定義アセット（配置情報）を参照する。
- 多言語名称は `MstHomeKomaPatternI18n` の `name` カラムで管理され、UIに表示されるパターン名に使用される。

### 関連テーブルとの構造図

```
MstHomeKomaPattern（コマ配置パターン）
  └─ id → MstHomeKomaPatternI18n.mst_home_koma_pattern_id（多言語名称）
```

---

## 全カラム一覧

### mst_home_koma_patterns カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `id` | varchar(255) | 不可 | - | 主キー。パターンの一意識別子 |
| `asset_key` | varchar(255) | 不可 | - | コマ配置レイアウトのアセットキー |

### MstHomeKomaPatternI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `id` | varchar(255) | 不可 | - | 主キー。`{mst_home_koma_pattern_id}_{language}` 形式 |
| `mst_home_koma_pattern_id` | varchar(255) | 不可 | - | 対応するパターンID（`mst_home_koma_patterns.id`） |
| `language` | enum | 不可 | ja | 言語コード（現在 `ja` のみ対応） |
| `name` | varchar(255) | 不可 | - | UI表示用のパターン名称 |

---

## 命名規則 / IDの生成ルール

| 種類 | 命名パターン | 例 |
|------|------------|-----|
| id（パターン） | 数字または用途を表す文字列 | `1`, `2`, `3`, `4`, `jumble` |
| asset_key | パターン番号または識別子 | `01`, `02`, `03`, `jumble` |
| i18n id | `{mst_home_koma_pattern_id}_{language}` | `1_ja`, `jumble_ja` |

---

## 他テーブルとの連携

| 連携先テーブル | カラム | 関係 |
|-------------|-------|------|
| `mst_home_koma_patterns_i18n` | `id` → `mst_home_koma_pattern_id` | 多言語名称（1:N） |

---

## 実データ例

**パターン1: 通常コマパターン（番号指定）**
```
ENABLE: e
release_key: 202603020
id: 1
asset_key: 01
```
```
（MstHomeKomaPatternI18n）
ENABLE: e
release_key: 202603020
id: 1_ja
mst_home_koma_pattern_id: 1
language: ja
name: コマパターン1
```
- 最もシンプルな番号指定パターン
- アセットキー `01` でレイアウトアセットを参照

**パターン2: 特殊パターン（ジャンブルラッシュ）**
```
ENABLE: e
release_key: 202603020
id: jumble
asset_key: jumble
```
```
（MstHomeKomaPatternI18n）
ENABLE: e
release_key: 202603020
id: jumble_ja
mst_home_koma_pattern_id: jumble
language: ja
name: ジャンブルラッシュ
```
- 特殊な配置パターンを文字列IDで識別
- 特定のコンテンツ（ジャンブルラッシュ）向けのパターン

---

## 設定時のポイント

1. **i18nとの1対1対応**: パターン1件につき必ず `MstHomeKomaPatternI18n` のレコードを言語分作成する。現在は日本語（`ja`）のみ対応。
2. **asset_key の管理**: `asset_key` はAddressablesに登録されたコマ配置レイアウトアセットのキーに対応させる。アセットが存在しないキーを設定するとパターンが正常表示されない。
3. **id の設計**: 数字（`1`, `2`, `3`...）または用途を表す文字列（`jumble`）を使用する。追加するパターンは既存の最大番号の次を使用する。
4. **i18n の id 命名**: `{mst_home_koma_pattern_id}_{language}` の形式で統一する（例: `1_ja`, `2_ja`）。
5. **language enum**: 現在 `ja` のみ対応している（スキーマ定義 `enum('ja')`）。多言語対応が必要になった場合はスキーマ変更が必要。
6. **パターン追加の手順**: パターンを新規追加する場合は、まず `MstHomeKomaPattern` にレコードを追加し、次に `MstHomeKomaPatternI18n` に名称レコードを追加する順序で作業する。

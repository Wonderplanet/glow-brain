# MstEnemyCharacter 詳細説明

> CSVパス: `projects/glow-masterdata/MstEnemyCharacter.csv`
> i18n CSVパス: `projects/glow-masterdata/MstEnemyCharacterI18n.csv`

---

## 概要

`MstEnemyCharacter` は**ゲーム内に登場する敵キャラクター（ファントム）の設定テーブル**。

ゲーム内では敵キャラクターを「ファントム」と呼び、プレイアブルキャラクターとは別の存在として扱う。`is_phantomized` フラグでプレイアブルキャラが敵として登場するケース（キャラの「ファントム化」表現）と、純粋なオリジナル敵キャラクターを区別できる設計になっている。`is_displayed_encyclopedia` フラグで図鑑画面への表示を制御する。

`mst_enemy_characters_i18n` テーブルでファントム名・説明文の多言語対応を行う。

CSVの行数は159件（2026年3月現在）。

### ゲームプレイへの影響

- **`is_phantomized = 1`**: プレイアブルキャラクターが敵として登場する場合（キャラのアセットをそのまま使用）。`asset_key` にキャラIDを設定する
- **`is_phantomized = 0`**: オリジナルの敵キャラクター。`asset_key` に専用の敵アセットキーを設定する
- **`is_displayed_encyclopedia = 1`**: 図鑑画面に表示される。名称・説明文も表示されるため i18n が必須
- **`mst_series_id`**: 敵がどの作品に属するかを示し、作品別の敵一覧表示や関連コンテンツに使用

### 関連テーブルとの構造図

```
MstEnemyCharacter（敵キャラクター定義）
  └─ id → MstEnemyCharacterI18n.mst_enemy_character_id（多言語名称・説明文）
  └─ mst_series_id → MstSeries.id（作品マスタ）
  └─ id → MstEnemyStageParameter（敵のステージ別パラメータ）
```

---

## 全カラム一覧

### mst_enemy_characters カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。敵キャラクターID |
| `release_key` | bigint | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |
| `mst_series_id` | varchar(255) | 不可 | "" | 作品ID（`mst_series.id`）。オリジナル系は空文字またはglo（GLOW固有） |
| `asset_key` | varchar(255) | 不可 | - | アセットキー。`is_phantomized = 1` の場合はキャラIDを設定 |
| `is_phantomized` | tinyint | 不可 | 0 | プレイアブルキャラの敵化フラグ。`0` = オリジナル敵、`1` = キャラのファントム化 |
| `is_displayed_encyclopedia` | tinyint | 不可 | 0 | 図鑑表示フラグ。`0` = 非表示、`1` = 図鑑に表示 |

### MstEnemyCharacterI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `release_key` | int | 不可 | 1 | リリースキー |
| `mst_enemy_character_id` | varchar(255) | 不可 | - | 参照先敵キャラクターID（`mst_enemy_characters.id`） |
| `language` | enum | 不可 | - | 言語コード。`ja` のみ対応 |
| `name` | varchar(255) | 不可 | - | ファントム名称 |
| `description` | varchar(255) | 不可 | - | ファントムの説明文 |

---

## 命名規則 / IDの生成ルール

実データには3種類のIDパターンが存在する：

| パターン | 形式 | 用途 | 例 |
|---------|------|------|----|
| オリジナル敵 | `e_{作品略称}_{5桁コード}_{カテゴリ}_{ブロック種別}_{属性}` | 一般的な敵エネミー | `e_glo_00001_tutorial_Normal_Yellow` |
| ファントム化キャラ（図鑑表示） | `chara_{作品略称}_{5桁コード}` | キャラが図鑑に登場する敵として設定 | `chara_spy_00101` |
| ファントム化キャラ（非図鑑） | `c_{作品略称}_{5桁コード}_{カテゴリ}_{ブロック種別}_{属性}` | 特定ステージのみ出現のファントム化敵 | `c_spy_00101_general_Boss_Colorless` |

---

## 他テーブルとの連携

| テーブル | 参照方向 | 用途 |
|---------|---------|------|
| `mst_enemy_characters_i18n` | `id` ← `mst_enemy_character_id` | ファントム名・説明文の多言語テキスト |
| `mst_series` | `mst_series_id` → `id` | 作品マスタ |
| `mst_enemy_stage_parameters` | `id` ← `mst_enemy_character_id` | ステージ別の敵パラメータ（HP・攻撃力・速度等） |

---

## 実データ例

### パターン1: オリジナル敵（チュートリアル用）

```
[MstEnemyCharacter.csv]
ENABLE: e
release_key: 202509010
id: e_glo_00001_tutorial_Normal_Yellow
mst_series_id: glo
asset_key: enemy_glo_00001
is_phantomized: 0
is_displayed_encyclopedia: 0

[MstEnemyCharacterI18n.csv]
ENABLE: e
release_key: 202509010
id: e_glo_00001_tutorial_Normal_Yellow_ja
mst_enemy_character_id: e_glo_00001_tutorial_Normal_Yellow
language: ja
name: チュートリアル君
description: ジャンプラバースの世界を脅かす存在（仮）
```

チュートリアル専用のオリジナル敵。`is_displayed_encyclopedia = 0` のため図鑑には表示されない。

### パターン2: ファントム化キャラ（図鑑表示あり）

```
[MstEnemyCharacter.csv]
ENABLE: e
release_key: 202509010
id: chara_spy_00101
mst_series_id: spy
asset_key: chara_spy_00101
is_phantomized: 1
is_displayed_encyclopedia: 1

[MstEnemyCharacterI18n.csv]
ENABLE: e
release_key: 202509010
id: chara_spy_00101_ja
mst_enemy_character_id: chara_spy_00101
language: ja
name: ロイド（ファントム）
description: ファントム化されたフォージャー家の父。普段の姿とは打って変わって...
```

プレイアブルキャラがファントム化した敵。`asset_key` にキャラIDを使用し、`is_phantomized = 1` でファントム化表現を有効にする。図鑑に表示されるため専用の名称・説明文が必要。

---

## 設定時のポイント

1. **`is_phantomized = 1` の場合は `asset_key` にキャラクターIDを設定する**。プレイアブルキャラのアセットを敵として再利用するため、`chara_{作品略称}_{5桁コード}` 形式のキャラIDをそのまま設定する。

2. **`is_displayed_encyclopedia = 1` の場合は必ず i18n レコードも作成する**。図鑑に表示される敵は名称と説明文が表示されるため、i18n が存在しないと表示が壊れる。

3. **`is_displayed_encyclopedia = 0` の場合も i18n の作成を推奨**。一部のシステムで名称を参照する場合があり、「オリジナル」のような仮テキストでも設定しておくと安全。

4. **IDの命名パターンは役割ごとに使い分ける**。図鑑表示のファントム化キャラは `chara_` プレフィックス、通常の敵は `e_` プレフィックスを使用する既存の慣習を踏襲すること。

5. **`mst_series_id` は空文字より `glo`（GLOW固有）または対応する作品IDを設定することを推奨**。オリジナル敵はGLOWプロジェクト固有の存在として `glo` を設定するのが実データのパターン。

6. **新キャラクターのファントム化を追加する場合は、対応するステージパラメータ（`MstEnemyStageParameter`）も設定する**。敵キャラとして機能させるには強さパラメータが必要。

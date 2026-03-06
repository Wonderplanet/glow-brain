# イベント本体 要件テキストフォーマット

> **用途**: プランナーがヒアリング結果を記入し、Claudeに渡すことでイベント本体に関するマスタデータCSVを一括生成するための要件テキスト。
>
> **生成されるCSV**:
> - `MstEvent.csv`（イベント定義・1行）
> - `MstEventI18n.csv`（イベント名・吹き出しテキスト・1行）
> - `MstEventBonusUnit.csv`（ボーナスユニット設定・N行、既存CSVへの追記）
> - `MstEventDisplayUnit.csv`（クエスト表示ユニット設定・N行、既存CSVへの追記）
> - `MstEventDisplayUnitI18n.csv`（表示ユニットセリフ・N行、既存CSVへの追記）

---

## テンプレート

```
# イベント本体 要件テキスト

## 基本情報

- イベントID: {event_{作品略称}_{5桁連番} 形式で記入}
  例: event_spy_00002
- 作品略称 (mst_series_id): {作品を識別する英字略称}
  例: spy（SPY×FAMILY）, kai（怪獣8号）, sur（魔都精兵のスレイブ）
- リリースキー: {このリリースのリリースキーを記入}
  例: 202603010
- イベント名: {イベントTOP・バナーに表示する正式名称}
  例: ふつうの軽音部いいジャン祭
- JUMP+掲載作品か: はい / いいえ
  ※「はい」の場合は「作品を読む」ボタンを表示する

## 期間

- 開始: YYYY-MM-DD HH:MM
- 終了: YYYY-MM-DD HH:MM

  ※ 開始時刻の慣例: イベント開始日の 24:00（= 翌日 0:00）
  ※ 終了時刻の慣例: イベント終了日の 11:59（= 12:59 JST）
  ※ 時刻はすべてJST前提。UTC変換はClaudeが行う（-9時間）

## ホーム吹き出しテキスト

- 吹き出し1行目: {例: 株式会社マジルミエいいジャン祭}
- 吹き出し2行目: 開催中！（固定。省略時はデフォルトで「開催中！」を使用）

  ※ ホーム画面のキャラクター吹き出しに表示される短いテキスト（1〜2行）

## ボーナスユニット設定

event_bonus_group_id: {raid_{作品略称}{回数}_{5桁連番} 形式で記入}
  例: raid_hut1_00001（ふつうの軽音部 第1回）、raid_spy2_00001（SPY×FAMILY 第2回）

| ユニットID | ボーナス倍率(%) | 備考（キャラ名等） |
|-----------|---------------|----------------|
| chara_{略称}_XXXXX | 20 | 最新ガチャキャラ（イベントピックアップ） |
| chara_{略称}_XXXXX | 10 | 既存キャラ |
| chara_{略称}_XXXXX | 10 | 既存キャラ |
（キャラ数に応じて増減してください）

  ※ ボーナス倍率は最新ガチャキャラ 20〜30%、既存キャラ 10〜15%、旧キャラ 3〜5% が標準
  ※ 第1回イベントで全キャラ均一30%にするパターンもあり

## クエスト表示ユニット設定

各クエスト種別ごとに「どのキャラを表示するか」と「セリフ」を記入してください。

### 1日クエスト (quest_event_{作品略称}{回数}_1day)
- クエストID: quest_event_{略称}{回数}_1day
  表示ユニット:
  1. ユニットID: chara_{略称}_XXXXX / セリフ: {キャラのセリフ（1〜2行、改行は「\n」）}
  2. ユニットID: chara_{略称}_XXXXX / セリフ: {キャラのセリフ}
  （1体だけの場合は1行のみ）

### キャラゲットクエスト01 (quest_event_{作品略称}{回数}_charaget01)
- クエストID: quest_event_{略称}{回数}_charaget01
  表示ユニット:
  1. ユニットID: chara_{略称}_XXXXX / セリフ: {セリフ}
  2. ユニットID: chara_{略称}_XXXXX / セリフ: {セリフ}
  3. ユニットID: chara_{略称}_XXXXX / セリフ: {セリフ}
  （人数は問わない）

### キャラゲットクエスト02 (quest_event_{作品略称}{回数}_charaget02)
- クエストID: quest_event_{略称}{回数}_charaget02
  表示ユニット:
  1. ユニットID: chara_{略称}_XXXXX / セリフ: {セリフ}
  （charaget02 が存在しない場合は省略可）

### チャレンジクエスト (quest_event_{作品略称}{回数}_challenge)
- クエストID: quest_event_{略称}{回数}_challenge
  表示ユニット:
  1. ユニットID: chara_{略称}_XXXXX / セリフ: {セリフ}
  2. ユニットID: chara_{略称}_XXXXX / セリフ: {セリフ}
  3. ユニットID: chara_{略称}_XXXXX / セリフ: {セリフ}

### サベージクエスト (quest_event_{作品略称}{回数}_savage)
- クエストID: quest_event_{略称}{回数}_savage
  表示ユニット:
  1. ユニットID: chara_{略称}_XXXXX / セリフ: {セリフ}
  2. ユニットID: chara_{略称}_XXXXX / セリフ: {セリフ}
```

---

## 作品略称（mst_series_id）の選択肢

Claudeがこのテキストを解釈してIDに変換します。以下の表記を使ってください。

| 略称 | 作品名 | JUMP+掲載 |
|------|--------|----------|
| `kai` | 怪獣8号 | はい |
| `spy` | SPY×FAMILY | はい |
| `sur` | 魔都精兵のスレイブ | はい |
| `mag` | 株式会社マジルミエ | はい |
| `yuw` | ゆびさきと恋々 | はい |
| `jig` | ジグザグきらめき | はい |
| `kim` | 君のことが大大大大大好きな100人の彼女 | はい |
| `hut` | ふつうの軽音部 | はい |
| `glo` | GLOWオリジナル / 周年記念 | いいえ |

> **新規作品の場合**: 略称を任意で設定し「新規作品のため略称を新規作成」と明記してください。

---

## 記入済みサンプル（実データ: event_hut_00001 より）

```
# イベント本体 要件テキスト

## 基本情報

- イベントID: event_hut_00001
- 作品略称 (mst_series_id): hut
- リリースキー: 202603010
- イベント名: ふつうの軽音部いいジャン祭
- JUMP+掲載作品か: はい

## 期間

- 開始: 2026-03-03 00:00
- 終了: 2026-04-03 11:59

## ホーム吹き出しテキスト

- 吹き出し1行目: ふつうの軽音部いいジャン祭
- 吹き出し2行目: 開催中！

## ボーナスユニット設定

event_bonus_group_id: raid_hut1_00001

| ユニットID | ボーナス倍率(%) | 備考 |
|-----------|---------------|------|
| chara_hut_00001 | 20 | 最新ガチャキャラ（ピックアップ） |
| chara_hut_00101 | 10 | 既存キャラ |
| chara_hut_00201 | 10 | 既存キャラ |
| chara_hut_00301 | 10 | 既存キャラ |

## クエスト表示ユニット設定

### 1日クエスト (quest_event_hut1_1day)
- クエストID: quest_event_hut1_1day
  表示ユニット:
  1. ユニットID: chara_hut_00001 / セリフ: この状況は\n……何??

### キャラゲットクエスト01 (quest_event_hut1_charaget01)
- クエストID: quest_event_hut1_charaget01
  表示ユニット:
  1. ユニットID: chara_hut_00001 / セリフ: この状況は\n……何??

### キャラゲットクエスト02 (quest_event_hut1_charaget02)
- クエストID: quest_event_hut1_charaget02
  表示ユニット:
  1. ユニットID: chara_hut_00101 / セリフ: カラオケ行かん？
  2. ユニットID: chara_hut_00001 / セリフ: この状況は\n……何??

### チャレンジクエスト (quest_event_hut1_challenge)
- クエストID: quest_event_hut1_challenge
  表示ユニット:
  1. ユニットID: chara_hut_00001 / セリフ: この状況は\n……何??
  2. ユニットID: chara_hut_00101 / セリフ: カラオケ行かん？
  3. ユニットID: chara_hut_00201 / セリフ: うん…遊び行こ！

### サベージクエスト (quest_event_hut1_savage)
- クエストID: quest_event_hut1_savage
  表示ユニット:
  1. ユニットID: chara_hut_00301 / セリフ: どうでも\nええんやけど
```

---

## このフォーマットをClaudeに渡す際の依頼文例

```
以下の要件テキストをもとに、イベント本体のマスタデータCSVを生成してください。

【生成対象】
- MstEvent.csv（新規1行）
- MstEventI18n.csv（新規1行）
- MstEventBonusUnit.csv（追記N行。既存CSVの最大IDの続き番号から採番）
- MstEventDisplayUnit.csv（追記N行）
- MstEventDisplayUnitI18n.csv（追記N行）

【ID採番】
- MstEventBonusUnit の id は整数連番。既存CSVの最大値+1から採番してください。
- MstEventDisplayUnit・MstEventDisplayUnitI18n の id は命名規則に従って生成してください。

【時刻変換】
- 要件テキストの時刻はJST。UTC（-9時間）に変換してCSVに記録してください。

---
（要件テキストをここに貼り付け）
```

---

## 補足: テーブル間の関係

```
MstEvent（1行）
  ├─ id: event_{作品略称}_{5桁連番}
  ├─ mst_series_id: 作品略称
  ├─ is_displayed_series_logo: 1（固定）
  ├─ is_displayed_jump_plus: JUMP+掲載なら 1、それ以外は 0
  ├─ start_at: 開始日時（UTC）
  ├─ end_at: 終了日時（UTC）
  ├─ asset_key: 通常は id と同一値
  └─ release_key: リリースキー
    ↓
MstEventI18n（1行）
  ├─ id: {event_id}_ja
  ├─ mst_event_id: event_id
  ├─ language: ja（固定）
  ├─ name: イベント名称（例: ふつうの軽音部いいジャン祭）
  ├─ balloon: 吹き出しテキスト（例: ふつうの軽音部いいジャン祭\n開催中！）
  └─ release_key: リリースキー

MstEventBonusUnit（N行）
  ├─ id: 整数連番（全CSV共通・前レコードの続き番号）
  ├─ mst_unit_id: ボーナス対象ユニットID
  ├─ bonus_percentage: ボーナス割合（%）
  ├─ event_bonus_group_id: raid_{作品略称}{回数}_{5桁連番}
  ├─ is_pick_up: NULL（現在未使用）
  └─ release_key: リリースキー

MstEventDisplayUnit（クエスト × ユニット数の行数）
  ├─ id: {mst_quest_id}{クエスト内連番2桁} ※命名パターンは後述
  ├─ mst_quest_id: quest_event_{作品略称}{回数}_{クエスト種別}
  ├─ mst_unit_id: 表示ユニットID
  └─ release_key: リリースキー
    ↓
MstEventDisplayUnitI18n（MstEventDisplayUnit と同数の行）
  ├─ id: {display_unit_id}_ja
  ├─ mst_event_display_unit_id: display_unit_id
  ├─ language: ja（固定）
  ├─ speech_balloon_text1: セリフ（改行は \n）
  ├─ speech_balloon_text2: （基本空欄）
  ├─ speech_balloon_text3: （基本空欄）
  └─ release_key: リリースキー
```

---

## 注意事項

- **時刻はJST前提**: 要件テキストはJSTで記入する。Claude がUTC変換（-9時間）してCSVに記録する。
  - 例: JST 2026-03-03 00:00 → UTC 2025-03-02 15:00:00
  - 開始は慣例として「開幕日の翌0:00 JST」= UTC 15:00:00 の前日
  - 終了は慣例として「終了日の 11:59 JST」= UTC 02:59:59（翌日）

- **MstEventBonusUnit の id**: 整数連番。全イベント分が1本のCSVに積み上がる形式のため、既存CSVの最大値+1から採番すること。

- **MstEventDisplayUnit の id 命名パターン**:
  - 1クエスト1ユニット（単体）の場合: `{mst_quest_id}01`（末尾に `01` を付ける）
    - 例: `quest_event_hut1_1day01`、`quest_event_hut1_charaget0101`
  - 1クエスト複数ユニットの場合: `{mst_quest_id}01`、`{mst_quest_id}02`、...
    - 例: `quest_event_hut1_charaget0201`、`quest_event_hut1_charaget0202`
  - charaget01 の場合は mst_quest_id 自体が `01` で終わるため、連番は `01`、`02`、...と続く
    - 例: `quest_event_kim1_charaget0101`、`quest_event_kim1_charaget0102`

- **クエスト種別の有無**: チャレンジクエスト（`_challenge`）やキャラゲット02（`_charaget02`）はイベント設計によって存在しない場合がある。設定不要なクエスト種別は省略してよい。

- **ボーナス倍率の設計指針**:
  - 作品が初イベント（全キャラ数が少ない）場合: 全員均一 30% が多い（怪獣8号第1回、マジルミエ等）
  - 作品が複数キャラ揃ってきた段階: 段階設定 20%/15%/10%/5%/3% が多い（魔都精兵のスレイブ等）
  - 最新ガチャキャラ（当該リリースで追加）に最高倍率を設定するのが基本方針

- **asset_key**: 通常はイベントIDと同一の値（`event_hut_00001` など）を設定する。周年記念などの特別なアセットを使用する場合のみ異なるキーを指定する（例: `event_l05anniv_00001`）。

- **同一作品の2回目以降のイベント**: イベントID は `event_spy_00002` のように連番を増やし、event_bonus_group_id は `raid_spy2_00001` のように回数を数字で付与して区別する。

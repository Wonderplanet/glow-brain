# MstSpeechBalloonI18n 詳細説明

> CSVパス: `projects/glow-masterdata/MstSpeechBalloonI18n.csv`

---

## 概要

`MstSpeechBalloonI18n` は**インゲーム中にユニット（キャラ）が表示する吹き出しセリフの設定テーブル**（i18n専用テーブル）。

特定のアクション（召喚時・スペシャルアタックチャージ時・スペシャルアタック発動時）に応じて、キャラクターの頭上に吹き出しとセリフテキストが表示される。吹き出しの形状・向き・表示時間なども管理する。

メインテーブルの `mst_speech_balloons` は存在せず、本テーブルが直接ユニットIDに紐付く。

### ゲームへの影響

- **召喚時吹き出し** (`condition_type = Summon`): キャラがフィールドに召喚されたときのセリフ。
- **スペシャルチャージ時吹き出し** (`condition_type = SpecialAttackCharge`): 必殺技のゲージが溜まったときのセリフ。
- **スペシャルアタック時吹き出し** (`condition_type = SpecialAttack`): 必殺技発動時のセリフ。
- **吹き出し形状** (`balloon_type`): `Maru`（丸型）/ `Fuwa`（ふわふわ型）/ `Toge`（トゲ型）の3種類。キャラの感情表現に合わせて選択。
- **向き** (`side`): `Right`（右向き）/ `Left`（左向き）で吹き出しの方向を制御。
- **表示時間** (`duration`): 吹き出しが表示される秒数。

### テーブル連携図

```
（ペアとなるmst_speech_balloonsテーブルは存在しない）
MstUnit（ユニット）
  └─ id → MstSpeechBalloonI18n.mst_unit_id（1:N、複数の条件・吹き出し設定）
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（連番整数） |
| `mst_unit_id` | varchar(255) | 不可 | - | 対応するユニットID（`mst_units.id`） |
| `language` | enum | 不可 | `ja` | 言語設定（現状 `ja` のみ） |
| `condition_type` | enum | 不可 | - | 吹き出し表示条件 |
| `balloon_type` | enum | 不可 | - | 吹き出しの形状 |
| `side` | enum | 不可 | - | 吹き出しの向き |
| `duration` | double | 不可 | - | 表示時間（秒） |
| `text` | varchar(255) | 不可 | - | セリフテキスト（改行は `\n` で記述） |
| `release_key` | bigint | 不可 | 1 | リリースキー |

---

## SpeechBalloonConditionType（enum）

| 値 | 説明 |
|----|------|
| `Summon` | キャラ召喚時 |
| `SpecialAttackCharge` | スペシャルアタックゲージが満タンになったとき |
| `SpecialAttack` | スペシャルアタック発動時 |

## SpeechBalloonType（enum）

| 値 | 説明 |
|----|------|
| `Maru` | 丸型の吹き出し（通常のセリフ） |
| `Fuwa` | ふわふわした吹き出し（のんびり・やわらかいセリフ） |
| `Toge` | トゲ型の吹き出し（叫び・強調したセリフ） |

## SpeechBalloonSide（enum）

| 値 | 説明 |
|----|------|
| `Right` | 右向き（吹き出しが右に向く） |
| `Left` | 左向き（吹き出しが左に向く） |

---

## 命名規則 / IDの生成ルール

`id` は連番整数で管理する。

通常1ユニットにつき2レコードを設定する（`Summon` + `SpecialAttackCharge`）。`SpecialAttack` 用は必要に応じて追加。

---

## 実データ例

### パターン1: ダンダダンキャラ（召喚時・チャージ時の2セット）

```csv
ENABLE,id,mst_unit_id,language,condition_type,balloon_type,side,duration,text,release_key
e,1,chara_dan_00001,ja,SpecialAttackCharge,Maru,Right,0.5,全てを\n許すことができる,202509010
e,2,chara_dan_00001,ja,Summon,Maru,Left,1.5,強くならなきゃ\nもっと強く !!,202509010
```

- `SpecialAttackCharge` の `duration = 0.5`（短時間表示）
- `Summon` の `duration = 1.5`（やや長め）

### パターン2: 複数行セリフと特殊な改行

```csv
ENABLE,id,mst_unit_id,language,condition_type,balloon_type,side,duration,text,release_key
e,16,chara_dan_00202,ja,SpecialAttackCharge,Maru,Right,0.5,"お舞踏\n\n\n\n︵パーティー︶の\n時間ですわ",202510020
```

- 複数の `\n` で大きな行間を作るケースもある

---

## 設定時のポイント

1. **各キャラに最低2条件（`Summon` + `SpecialAttackCharge`）を設定するのが標準**。`SpecialAttack` 用セリフは必要に応じて追加する。
2. **セリフは原作キャラクターの台詞・口調に合わせて設定する**。コンテンツ担当者・版権元と連携してテキストを確定させる。
3. **`balloon_type` はセリフの感情・ニュアンスに応じて選択する**。叫んでいるシーンは `Toge`、優しいセリフは `Fuwa`、通常会話は `Maru` が一般的。
4. **`duration` は条件によって目安がある**。`Summon` は 1.0〜2.0秒、`SpecialAttackCharge` は 0.5秒程度が多い。
5. **テキストの改行は `\n` で記述する**。複数の `\n` を連続させると行間を広げられる。
6. **`side` はキャラのインゲームでの立ち位置に合わせる**。攻撃側（左から右）は `Right`、防御側（右から左）は `Left` が多い。
7. **新キャラ追加時は必ず本テーブルにセリフを設定する**。設定漏れがあると吹き出しが表示されず、キャラの表現力が失われる。
8. **`id` は連番整数で管理する**。既存レコードの最大IDを確認して次の番号を採番する。

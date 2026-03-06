# ガチャ（ピックアップ/スペシャル）要件テキストフォーマット

> **用途**: プランナーがヒアリング結果を記入し、Claudeに渡すことでガチャのマスタデータCSVを一括生成するための要件テキスト。
>
> **生成されるCSV**:
> - `OprGacha.csv`（ガチャ基本設定・1行または複数行）
> - `OprGachaI18n.csv`（ガチャ名・説明など多言語情報・同数行）
> - `OprGachaPrize.csv`（景品排出テーブル・通常グループ + 確定グループで多数行）
> - `OprGachaUpper.csv`（天井設定・1行または2行）
> - `OprGachaUseResource.csv`（消費リソース設定・2〜4行）
> - `OprGachaDisplayUnitI18n.csv`（表示キャラ情報・ピックアップキャラ数分の行）

---

## テンプレート（ピックアップガチャ）

```
# ガチャ 要件テキスト

## 種別

- ガチャ種別: Pickup
  ※ Pickup（期間限定ピックアップ）/ Special（常設スペシャルガチャ更新）のどちらか

## 基本情報

- リリースキー: {リリースキーを記入}
  例: 202603010
- 作品略称（IDに使用）: {シリーズ略称を英小文字で記入}
  例: hut（ふつうの軽音部の場合）
- 連番（今回の開催が何回目か）: {3桁ゼロ埋め}
  例: 001
- ガチャ本数: {1本 / 2本（A・B）のどちらか}
  ※ 2本の場合はガチャAとガチャBを別々に記入

## 期間

- 開始: YYYY-MM-DD HH:MM
- 終了: YYYY-MM-DD HH:MM

  ※ 開始時刻の慣例: 15:00
  ※ 終了時刻の慣例: 10:59

## ガチャ名・説明（OprGachaI18n）

- ガチャ名: {例: ○○ いいジャン祭ピックアップガシャ A}
- 説明文: {1〜2行。出現率UP中キャラを紹介する文。改行は \n で記入}
  例: 「ひたむきギタリスト 鳩野 ちひろ」と\n「幸山 厘」の出現率UP中!
- バナーURL（アセットキー）: {例: hut_00001}
- ロゴアセットキー: {例: pickup_00001（A/B 2本の場合は pickup_a_00001 / pickup_b_00001）}
- 背景色: Yellow
- バナーサイズ: SizeL
- 確定枠文言: SR以上1体確定
- ピックアップ天井文言: ピックアップURキャラ1体確定!

## ピックアップキャラ（OprGachaPrize の pickup=1 対象 / OprGachaDisplayUnitI18n）

{ピックアップ対象キャラを記入。URとSSRを区別して記入すること}

- 1体目（UR）: {キャラID または「キャラ名（chara_xxx_xxxxx）」}
  表示説明文: {ガチャ画面のキャラ説明。改行は \n で記入。最大4行程度}
- 2体目（SSR、任意）: {キャラID または日本語名}
  表示説明文: {説明文}
（ピックアップキャラが複数いる場合は続けて記入）

※ UR1体 + SSR0〜3体が標準パターン

## 排出キャラ構成（OprGachaPrize の全排出枠）

{追加や変更がある場合のみ記入。通常は直前のPickupガチャから引き継ぐ}

- 今回新規追加するキャラ: {キャラID} / レアリティ: {UR / SSR / R}
- 今回削除するキャラ（任意）: {キャラID}
- それ以外: 既存のPickupガチャの排出リストを引き継ぐ

## 天井設定（OprGachaUpper）

- 天井タイプ: Pickup
- 天井回数: 100

## 消費リソース（OprGachaUseResource）

{標準設定（変更がある場合のみ上書き記入）}
- ピックアップガシャチケット（ticket_glo_00003）× 1 → 1回（優先度2）
- ダイヤ 150 × 1 → 1回（優先度3）
- ダイヤ 1500 × 1 → 10回（優先度3）

## バナー表示順（gacha_priority）

- {数値を記入。数値が小さいほど上位表示}
  例: 71
  ※ 同時開催ガチャが複数ある場合は連番で指定（例: Aが71、Bが70）
```

---

## テンプレート（スペシャルガチャ更新）

スペシャルガチャ（`Special_001`）は常設ガチャのため、排出キャラを新作対応で更新する場合に使用する。

```
# ガチャ 要件テキスト

## 種別

- ガチャ種別: Special

## 基本情報

- リリースキー: {リリースキーを記入}
- ガチャID: Special_001（固定）

## 期間

- 開始: 2025-04-01 13:00（固定・変更不要）
- 終了: 2038-01-01 09:00（固定・変更不要）

## ガチャ名・説明（OprGachaI18n）

- ガチャ名: スペシャルガシャ（固定）
- 説明文: {更新後の紹介文。例: 少年ジャンプ＋のキャラが大集合!}
- バナーURL: special_00001（固定）
- 背景色: Blue（固定）
- バナーサイズ: SizeL（固定）
- 最高レアリティ天井文言: URキャラ1体確定！
- 確定枠文言: SR以上1体確定

## 排出キャラ構成（OprGachaPrize の更新内容）

- 今回新規追加するキャラ: {キャラID} / レアリティ: {UR / SSR / R}
- 今回削除するキャラ（任意）: {キャラID}
- それ以外: 既存Special_001の排出リストを引き継ぐ

## 天井設定（OprGachaUpper）

- 天井タイプ: MaxRarity（固定）
- 天井回数: 100（固定）

## 消費リソース（OprGachaUseResource）

{標準設定（変更がある場合のみ上書き記入）}
- 広告（Ad）× 1 → 1回（優先度1）
- スペシャルガシャチケット（ticket_glo_00002）× 1 → 1回（優先度2）
- ダイヤ 150 × 1 → 1回（優先度3）
- ダイヤ 1500 × 1 → 10回（優先度3）
```

---

## キャラIDの選択肢と対応表

Claudeがこのテキストを解釈してIDに変換します。以下の表記を使ってください。

### キャラID命名規則

| 命名パターン | 意味 | 例 |
|------------|------|-----|
| `chara_{作品略称}_{レアリティ連番}` | キャラID | `chara_kim_00001` |
| `{作品略称}` は英小文字2〜3文字 | 作品を識別する略称 | kim, sur, jig, mag, hut |
| 末尾番号 `00001` = UR、`00101` = SSR（1本目）、`00201` = SSR（2本目）、`00301` = SSR（3本目） | レアリティ目安 | `chara_kim_00201` = kim作品のSSR |

> **その他のキャラ**: IDが不明な場合は「キャラ名（chara_xxx_xxxxx）」と日本語名とIDを併記するか、「作品略称と何体目か」を記入してください（例: 「kimの1体目UR」→ `chara_kim_00001`）。

### チケット類

| 表記 | アイテムID | 備考 |
|------|-----------|------|
| ピックアップガシャチケット | `ticket_glo_00003` | PickupガチャのItem消費に使用 |
| スペシャルガシャチケット | `ticket_glo_00002` | Specialガチャ（常設）のItem消費に使用 |

---

## 記入済みサンプル（実データ例1: ピックアップ1本構成 — 202603010 / ふつうの軽音部）

```
# ガチャ 要件テキスト

## 種別

- ガチャ種別: Pickup

## 基本情報

- リリースキー: 202603010
- 作品略称（IDに使用）: hut
- 連番: 001
- ガチャ本数: 1本

## 期間

- 開始: 2026-03-02 15:00
- 終了: 2026-04-03 10:59

## ガチャ名・説明（OprGachaI18n）

- ガチャ名: ふつうの軽音部 いいジャン祭ピックアップガシャ
- 説明文: 「ひたむきギタリスト 鳩野 ちひろ」と\n「幸山 厘」の出現率UP中!
- バナーURL: hut_00001
- ロゴアセットキー: pickup_00001
- 背景色: Yellow
- バナーサイズ: SizeL
- 確定枠文言: SR以上1体確定
- ピックアップ天井文言: ピックアップURキャラ1体確定!

## ピックアップキャラ

- 1体目（UR）: chara_hut_00001（鳩野 ちひろ）
  表示説明文: ひたむきな努力と\nギターへの情熱で\n仲間を鼓舞する\nアタックキャラ!
- 2体目（SSR）: chara_hut_00101（幸山 厘）
  表示説明文: 持ち前の明るさで\n味方の攻撃力を\nサポートする\nバフキャラ!

## 排出キャラ構成

- 今回新規追加するキャラ: chara_hut_00001 / レアリティ: UR
- 今回新規追加するキャラ: chara_hut_00101 / レアリティ: SSR
- それ以外: 直前のPickupガチャ（Pickup_kim_001）の排出リストを引き継ぐ

## 天井設定

- 天井タイプ: Pickup
- 天井回数: 100

## 消費リソース

（標準設定のまま）

## バナー表示順

- 71
```

---

## 記入済みサンプル（実データ例2: ピックアップ2本構成 — 202511010 / 株式会社マジルミエ）

```
# ガチャ 要件テキスト

## 種別

- ガチャ種別: Pickup

## 基本情報

- リリースキー: 202511010
- 作品略称（IDに使用）: mag
- 連番: 001（A）/ 002（B）
- ガチャ本数: 2本（A・B）

## 期間

- 開始: 2025-11-06 15:00
- 終了: 2025-12-08 10:59

## ガチャ A（Pickup_mag_001）

### ガチャ名・説明

- ガチャ名: 株式会社マジルミエ いいジャン祭ピックアップガシャ A
- 説明文: 「絶対効率の体現者 土刃 メイ」と\n「葵 リリー」の出現率UP中!
- バナーURL: mag_00001
- ロゴアセットキー: pickup_a_00001
- 背景色: Yellow
- バナーサイズ: SizeL
- 確定枠文言: SR以上1体確定
- ピックアップ天井文言: ピックアップURキャラ1体確定!

### ピックアップキャラ

- 1体目（UR）: chara_mag_00201（土刃 メイ）
  表示説明文: 必殺ワザで遠距離から\n範囲攻撃できる\nアタックキャラ!\n特定の条件で攻撃UP
- 2体目（SSR）: chara_mag_00301（葵 リリー）
  表示説明文: 中距離のディフェンス\nキャラ!\n必殺ワザで自身が\n受けるダメージカット

### 排出キャラ構成

- 今回新規追加するキャラ: chara_mag_00201 / レアリティ: UR
- 今回新規追加するキャラ: chara_mag_00301 / レアリティ: SSR
- それ以外: 直前のPickupガチャ（Pickup_sur_002）の排出リストを引き継ぐ

### 天井設定

- 天井タイプ: Pickup
- 天井回数: 100

### 消費リソース

（標準設定のまま）

### バナー表示順

- 56

## ガチャ B（Pickup_mag_002）

### ガチャ名・説明

- ガチャ名: 株式会社マジルミエ いいジャン祭ピックアップガシャ B
- 説明文: 「新人魔法少女 桜木 カナ」と\n「葵 リリー」の出現率UP中!
- バナーURL: mag_00002
- ロゴアセットキー: pickup_b_00001
- 背景色: Yellow
- バナーサイズ: SizeL
- 確定枠文言: SR以上1体確定
- ピックアップ天井文言: ピックアップURキャラ1体確定!

### ピックアップキャラ

- 1体目（UR）: chara_mag_00001（桜木 カナ）
  表示説明文: 遠距離からの\n通常攻撃が強力!\n必殺ワザの範囲攻撃で\n複数の相手にダメージ
- 2体目（SSR）: chara_mag_00301（葵 リリー）
  表示説明文: 中距離のディフェンス\nキャラ!\n必殺ワザで自身が\n受けるダメージカット

### 排出キャラ構成

- ガチャAと同一の通常排出テーブル（Pickup_mag_001 と同じキャラ構成）
- ただしピックアップ対象のみ変更（1体目UR を chara_mag_00001 に変更）

### 天井設定

- 天井タイプ: Pickup
- 天井回数: 100

### 消費リソース

（標準設定のまま）

### バナー表示順

- 55
```

---

## 記入済みサンプル（実データ例3: ピックアップ1本 UR×1 + SSR×3構成 — 202602020 / 100カノ）

```
# ガチャ 要件テキスト

## 種別

- ガチャ種別: Pickup

## 基本情報

- リリースキー: 202602020
- 作品略称（IDに使用）: kim
- 連番: 001
- ガチャ本数: 1本

## 期間

- 開始: 2026-02-16 15:00
- 終了: 2026-03-16 10:59

## ガチャ名・説明（OprGachaI18n）

- ガチャ名: 100カノ いいジャン祭ピックアップガシャ
- 説明文: 100カノから新URキャラ1体と\n新SSRキャラ3体の出現率UP中!
- バナーURL: kim_00001
- ロゴアセットキー: pickup_00001
- 背景色: Yellow
- バナーサイズ: SizeL
- 確定枠文言: SR以上1体確定
- ピックアップ天井文言: ピックアップURキャラ1体確定!

## ピックアップキャラ

- 1体目（UR）: chara_kim_00001
  表示説明文: 必殺ワザで複数の相手\nに攻撃とノックバック\n特定の条件で自身への\n被ダメージカット!
- 2体目（SSR）: chara_kim_00101
  表示説明文: 必殺ワザで黄属性の\n相手に大ダメージ\nを与える!
- 3体目（SSR）: chara_kim_00201
  表示説明文: 必殺ワザで一定時間\n相手の攻撃ステータス\nをDOWNさせる!
- 4体目（SSR）: chara_kim_00301
  表示説明文: 必殺ワザで一定時間\n味方の黄属性キャラの\n攻撃ステータスをUP

## 排出キャラ構成

- 今回新規追加するキャラ: chara_kim_00001 / レアリティ: UR
- 今回新規追加するキャラ: chara_kim_00101 / レアリティ: SSR
- 今回新規追加するキャラ: chara_kim_00201 / レアリティ: SSR
- 今回新規追加するキャラ: chara_kim_00301 / レアリティ: SSR
- それ以外: 直前のPickupガチャ（Pickup_jig_002）の排出リストを引き継ぐ

## 天井設定

- 天井タイプ: Pickup
- 天井回数: 100

## 消費リソース

（標準設定のまま）

## バナー表示順

- 70
```

---

## このフォーマットをClaudeに渡す際の依頼文例

```
以下の要件テキストをもとに、ピックアップガチャのマスタデータCSVを生成してください。

【生成対象】
- OprGacha.csv（新規1行）
- OprGachaI18n.csv（新規1行、language=ja）
- OprGachaPrize.csv（通常排出グループ + 確定グループの全レコード）
- OprGachaUpper.csv（新規1行）
- OprGachaUseResource.csv（新規2〜3行）
- OprGachaDisplayUnitI18n.csv（ピックアップキャラ数分の行）

【ID採番】
- OprGacha.id: Pickup_{作品略称}_{連番3桁} 形式（例: Pickup_hut_001）
- OprGachaPrize の通常グループID: ガチャIDと同名（例: Pickup_hut_001）
- OprGachaPrize の確定グループID: fixd_ガチャID（例: fixd_Pickup_hut_001）
- OprGachaUpper.id / OprGachaUseResource.id: 既存CSVの最大整数連番 +1 から採番
- OprGachaDisplayUnitI18n.id: 既存CSVの最大整数連番 +1 から採番

【OprGachaPrize のweight計算】
排出確率はすべてのリリースで固定ルールに従います。以下のルールでweightを計算してください。

通常グループ（prize_group_id）総weight = 93,600
- UR PU 1体: weight = 702（0.75%固定）
- UR 非PU: weight = floor(2106 / 非PUキャラ数) で均等割り
- SSR PU 1体あたり: weight = 1,404（1.5%固定）
- SSR 非PU: weight = floor((10% − 1.5%×PU数) × 93600 / 非PUキャラ数) で均等割り
- SR: weight = floor(32,760 / SRキャラ数) で均等割り
- R: weight = floor(48,672 / Rキャラ数) で均等割り

確定グループ（fixed_prize_group_id）総weight = 234,000
- 同比率を234,000に適用。Rは含まず、SR確率 = 87%
- SR: weight = floor(203,580 / SRキャラ数)

「前リリースのOprGachaPrizeを参照して排出リスト（全キャラ）を把握した上で、
今回のピックアップキャラを差し替えてweightを再計算する」こと。
参照CSVパス: domain/raw-data/masterdata/released/{前リリースキー}/tables/OprGachaPrize.csv

---
（要件テキストをここに貼り付け）
```

---

## 補足: テーブル間の関係

```
OprGacha（1行）
  ├─ id: Pickup_{略称}_{連番}（例: Pickup_hut_001）
  ├─ gacha_type: Pickup
  ├─ upper_group: id と同じ値（例: Pickup_hut_001）
  ├─ prize_group_id: id と同じ値（例: Pickup_hut_001）
  ├─ fixed_prize_group_id: fixd_{id}（例: fixd_Pickup_hut_001）
  ├─ multi_draw_count: 10（10連固定）
  ├─ multi_fixed_prize_count: 1（確定枠1体）
  ├─ enable_ad_play: NULL（PickupはNULL、Specialは1）
  ├─ start_at / end_at: 開始・終了日時（JST）
  └─ gacha_priority: バナー表示順

    ↓ prize_group_id で参照

OprGachaPrize（通常グループ: Pickup_hut_001 / 多数行）
  ├─ group_id: Pickup_hut_001
  ├─ resource_type: Unit
  ├─ resource_id: キャラID
  ├─ resource_amount: 1（固定）
  ├─ weight: レアリティ・ピックアップ有無によって異なる
  └─ pickup: ピックアップ対象は 1、それ以外は 0

OprGachaPrize（確定グループ: fixd_Pickup_hut_001 / 多数行）
  ├─ group_id: fixd_Pickup_hut_001
  └─ 通常グループと同一キャラ構成だが weight 比率が異なる
     （SR以上確定のため低レアは含まない / ピックアップ補正が大きい）

    ↓ upper_group で参照

OprGachaUpper（1行）
  ├─ upper_group: Pickup_hut_001
  ├─ upper_type: Pickup（PickupガチャはPickup天井、SpecialはMaxRarity天井）
  └─ count: 100

    ↓ opr_gacha_id で参照

OprGachaUseResource（2〜4行）
  ├─ opr_gacha_id: Pickup_hut_001
  ├─ レコード1: cost_type=Item, cost_id=ticket_glo_00003, cost_num=1, draw_count=1, cost_priority=2
  ├─ レコード2: cost_type=Diamond, cost_num=150, draw_count=1, cost_priority=3
  └─ レコード3: cost_type=Diamond, cost_num=1500, draw_count=10, cost_priority=3

    ↓ opr_gacha_id で参照

OprGachaI18n（1行）
  ├─ opr_gacha_id: Pickup_hut_001
  ├─ language: ja
  ├─ name: ガチャ名
  ├─ description: 説明文（\n で改行）
  ├─ pickup_upper_description: ピックアップURキャラ1体確定!
  ├─ fixed_prize_description: SR以上1体確定
  ├─ banner_url: バナーアセットキー
  ├─ logo_asset_key: ロゴアセットキー
  ├─ gacha_background_color: Yellow（Pickupは Yellow、Specialは Blue）
  └─ gacha_banner_size: SizeL

    ↓ opr_gacha_id で参照

OprGachaDisplayUnitI18n（ピックアップキャラ数分の行）
  ├─ opr_gacha_id: Pickup_hut_001
  ├─ mst_unit_id: ピックアップキャラのID
  ├─ language: ja
  ├─ sort_order: 表示順（1から昇順）
  └─ description: キャラ説明文（\n で改行）
```

---

## 排出確率ルール（全リリース共通・固定）

> **重要**: 排出確率はすべてのリリースで完全に固定されている。プランナーが確率を指定する必要はない。「誰をピックアップにするか」だけを決めれば、Claudeがweightを自動計算する。

### 通常グループ（prize_group_id）の確率

| レアリティ | pickup | 確率 | 備考 |
|-----------|--------|------|------|
| UR | 1（ピックアップ） | **0.75%** | UR枠全体3%のうちピックアップ固定 |
| UR | 0（非ピックアップ） | **2.25%** を非PUキャラ数で均等割り | 残りのUR枠 |
| SSR | 1（ピックアップ） | **1.5% × ピックアップ数** | 各1.5%固定 |
| SSR | 0（非ピックアップ） | **（10% − 1.5%×PU数）** を非PUキャラ数で均等割り | 残りのSSR枠 |
| SR | 0 | **35%** を全SRキャラ数で均等割り | |
| R | 0 | **52%** を全Rキャラ数で均等割り | |

### 確定グループ（fixed_prize_group_id）の確率

10連の確定枠（SR以上確定）。UR/SSRの比率は通常グループと同一。Rが存在しない分をSRが吸収する。

| レアリティ | pickup | 確率 |
|-----------|--------|------|
| UR | 1 | **0.75%**（通常グループと同じ） |
| UR | 0 | **2.25%** 均等割り |
| SSR | 1 | **1.5% × PU数** |
| SSR | 0 | 残りSSR枠 均等割り |
| SR | 0 | **87%**（= 35% + 52%、R枠がSRに加算） |
| R | — | **含まない**（SR以上確定） |

### weightの計算方法（Claudeが自動計算）

```
通常グループ総weight = 93,600（固定）
  各キャラのweight = 93,600 × 確率 / キャラ数

確定グループ総weight = 234,000（固定）
  各キャラのweight = 234,000 × 確率 / キャラ数
```

**例: UR1体PU + SSR1体PU、非PU UR13体・SSR9体・SR10体・R9体の場合**

| レアリティ | PU | 確率 | 通常weight | 確定weight |
|-----------|-----|------|-----------|-----------|
| UR PU 1体 | 1 | 0.75% | 702 | 1,755 |
| UR 非PU（÷13） | 0 | 0.1731% | 162 | 405 |
| SSR PU 1体 | 1 | 1.5% | 1,404 | 3,510 |
| SSR 非PU（÷9） | 0 | 0.944% | 884 | 2,210 |
| SR（÷10） | 0 | 3.5% | 3,276 | 20,358 |
| R（÷9） | 0 | 5.778% | 5,408 | — |

---

## 注意事項

- **ピックアップ天井は pickup=1 と連動**: OprGachaPrize で pickup=1 に設定したキャラが OprGachaUpper の Pickup タイプ天井の保証対象になる。
- **A/B 2本構成の場合のUPPER**: 両方のガチャに別々の OprGachaUpper レコード（upper_group 別）が必要。
- **確定グループ（fixd_）にRキャラは含めない**: fixed_prize_group_id のグループは SR以上確定なので R相当のキャラを含まないこと。
- **OprGachaUseResource の id は整数連番**: 全リリース通算の最大整数 +1 から採番する。過去のリリースCSVを確認してから採番すること。
- **OprGachaDisplayUnitI18n の id も整数連番**: 同様に全リリース通算の最大整数 +1 から採番する。
- **description の改行**: `\n` で記入した箇所は CSV には `\n` という文字列をそのまま格納する（エスケープ不要）。
- **時刻はすべて JST 前提**: UTC 変換不要。DBには JST のまま入力する。
- **Specalガチャ（Special_001）は固定ID**: 新規作成ではなく排出テーブルの差し替え更新になるため、既存レコードとの整合性に注意。
- **OprGacha の display_mst_unit_id**: ピックアップキャラIDをカンマ区切りで設定する（例: `chara_hut_00001,chara_hut_00101`）。
- **OprGacha の display_information_id と dev-qa_display_information_id**: UUID形式。お知らせIDが確定していない場合はプレースホルダーを使用し、後から差し替えること。
- **OprGacha の display_gacha_caution_id**: UUID形式。作品共通の注意事項IDを設定する。

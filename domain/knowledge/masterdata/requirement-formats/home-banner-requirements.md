# ホームバナー 要件テキストフォーマット

> **用途**: プランナーがリリースの内容を記入し、Claudeに渡すことでホームバナーのマスタデータCSVを一括生成するための要件テキスト。
>
> **生成されるCSV**:
> - `MstHomeBanner.csv`（バナー定義レコード・N行、既存CSVへの追記）

---

## テンプレート

```
# ホームバナー 要件テキスト

## 基本情報

- リリースキー: {このリリースのリリースキーを記入}
  例: 202603010

## バナー一覧

以下に設定するバナーを列挙する。バナーは複数設定できる（1リリースあたり2〜5件が標準）。

---

### バナー1（イベント告知）

- 種別: Event
- 遷移先ID（destination_path）: {event_xxx_xxxxx 形式のイベントID}
  例: event_hut_00001
- アセットキー（asset_key）: {hometop_event_{コンテンツ識別子}_{連番5桁} 形式}
  例: hometop_event_hut_00001
- 表示順（sort_order）: 7
- 掲載開始: YYYY-MM-DD HH:MM
- 掲載終了: YYYY-MM-DD HH:MM

---

### バナー2（ガチャ告知 — ピックアップ）

- 種別: Gacha
- 遷移先ID（destination_path）: {Pickup_{コンテンツ識別子}_{連番3桁} 形式のガチャID}
  例: Pickup_hut_001
- アセットキー（asset_key）: {hometop_gacha_{コンテンツ識別子}_{連番5桁} 形式}
  例: hometop_gacha_hut_00001
- 表示順（sort_order）: 6
- 掲載開始: YYYY-MM-DD HH:MM
- 掲載終了: YYYY-MM-DD HH:MM

---

### バナー3（パック告知）  ← 必要な場合のみ記入

- 種別: Pack
- 遷移先ID（destination_path）: （不要。空欄でよい）
- アセットキー（asset_key）: {hometop_shop_pack_{連番5桁} 形式}
  例: hometop_shop_pack_00032
- 表示順（sort_order）: 6
- 掲載開始: YYYY-MM-DD HH:MM
- 掲載終了: YYYY-MM-DD HH:MM

---

### バナー4（クレジットショップ・キャンペーン告知）  ← 必要な場合のみ記入

- 種別: CreditShop
- 遷移先ID（destination_path）: （不要。空欄でよい）
- アセットキー（asset_key）: {hometop_campaign_{連番5桁} 形式}
  例: hometop_campaign_00004
- 表示順（sort_order）: 5
- 掲載開始: YYYY-MM-DD HH:MM
- 掲載終了: YYYY-MM-DD HH:MM
```

---

## バナー種別（destination）の選択肢

| 種別値 | 用途 | destination_path | asset_key の慣例 |
|--------|------|-----------------|-----------------|
| `Event` | イベント告知・誘導 | イベントID（例: `event_hut_00001`） | `hometop_event_{識別子}_{連番}` |
| `Gacha` | ガチャ告知・誘導 | ガチャID（例: `Pickup_hut_001`, `Fest_Xmas_001`） | `hometop_gacha_{識別子}_{連番}` |
| `Pack` | スターターパック・パック購入画面へ誘導 | 不要（空欄） | `hometop_shop_pack_{連番}` |
| `CreditShop` | クレジットショップ・有料キャンペーンへ誘導 | 不要（空欄） | `hometop_campaign_{連番}` |
| `BasicShop` | 基本ショップへ誘導 | 不要（空欄） | — |
| `Pass` | パス購入画面へ誘導 | 不要（空欄） | — |
| `Web` | 外部WebページへWV遷移 | URL文字列 | — |
| `BeginnerMission` | 初心者ミッション画面へ誘導 | 不要（空欄） | — |
| `AdventBattle` | アドベントバトル画面へ誘導 | 不要（空欄） | — |
| `Pvp` | PvP画面へ誘導（常設バナー用途が多い） | 不要（空欄） | `hometop_pvp_{連番}` |
| `None` | 遷移なし（タップしても何も起きない） | 不要（空欄） | — |

---

## 表示順（sort_order）の設計ルール

| sort_order | 典型的な用途 |
|-----------|-------------|
| 1〜3 | 常設バナー（PvPなど、長期間表示する固定バナー） |
| 5〜6 | ガチャ告知・パック告知・キャンペーン告知 |
| 7 | イベント告知 |
| 8〜10 | フェスガチャ・特別イベントなど、特に前面に出したいバナー |

- **昇順で表示**される（数値が小さいほど先頭）。
- 複数バナーに同じ `sort_order` を設定しても、**掲載期間が重複しない**限りは問題なし。
- ガチャバナーは同一 `sort_order` を使い回す運用が一般的。

---

## 記入済みサンプル（実データベース: 202603010 リリース）

```
# ホームバナー 要件テキスト

## 基本情報

- リリースキー: 202603010

## バナー一覧

---

### バナー1（イベント告知）

- 種別: Event
- 遷移先ID（destination_path）: event_hut_00001
- アセットキー（asset_key）: hometop_event_hut_00001
- 表示順（sort_order）: 7
- 掲載開始: 2026-03-02 15:00
- 掲載終了: 2026-03-16 14:59

---

### バナー2（ガチャ告知）

- 種別: Gacha
- 遷移先ID（destination_path）: Pickup_hut_001
- アセットキー（asset_key）: hometop_gacha_hut_00001
- 表示順（sort_order）: 6
- 掲載開始: 2026-03-02 15:00
- 掲載終了: 2026-03-16 14:59

---

### バナー3（パック告知）

- 種別: Pack
- 遷移先ID（destination_path）: （空欄）
- アセットキー（asset_key）: hometop_shop_pack_00032
- 表示順（sort_order）: 6
- 掲載開始: 2026-03-10 15:00
- 掲載終了: 2026-03-16 14:59
```

---

## 別パターンサンプル（フェスガチャ + パック + クレジットショップ混在: 202512020 リリース）

```
# ホームバナー 要件テキスト

## 基本情報

- リリースキー: 202512020

## バナー一覧

---

### バナー1（イベント告知）

- 種別: Event
- 遷移先ID（destination_path）: event_osh_00001
- アセットキー（asset_key）: hometop_event_osh_00001
- 表示順（sort_order）: 7
- 掲載開始: 2026-01-01 00:00
- 掲載終了: 2026-01-16 10:59

---

### バナー2（フェスガチャ告知）

- 種別: Gacha
- 遷移先ID（destination_path）: Fest_osh_001
- アセットキー（asset_key）: hometop_gacha_glo_00002
- 表示順（sort_order）: 6
- 掲載開始: 2026-01-01 00:00
- 掲載終了: 2026-02-02 10:59

---

### バナー3（ピックアップガチャ告知）

- 種別: Gacha
- 遷移先ID（destination_path）: Pickup_osh_001
- アセットキー（asset_key）: hometop_gacha_osh_00001
- 表示順（sort_order）: 6
- 掲載開始: 2026-01-01 00:00
- 掲載終了: 2026-01-16 10:59

---

### バナー4（パック告知）

- 種別: Pack
- 遷移先ID（destination_path）: （空欄）
- アセットキー（asset_key）: hometop_shop_pack_00018
- 表示順（sort_order）: 5
- 掲載開始: 2026-01-01 00:00
- 掲載終了: 2026-01-15 10:59

---

### バナー5（クレジットショップキャンペーン告知）

- 種別: CreditShop
- 遷移先ID（destination_path）: （空欄）
- アセットキー（asset_key）: hometop_campaign_00004
- 表示順（sort_order）: 5
- 掲載開始: 2026-01-01 00:00
- 掲載終了: 2026-01-15 10:59
```

---

## このフォーマットをClaudeに渡す際の依頼文例

```
以下の要件テキストをもとに、ホームバナーのマスタデータCSVを生成してください。

【生成対象】
- MstHomeBanner.csv（追記N行。既存CSVの最大IDの続き番号から採番）

【ID採番】
- MstHomeBanner の id は整数連番です。既存CSVの最大IDの次の値から採番してください。

【その他】
- 時刻はすべてJST表記のままCSVに出力してください（秒まで記載: HH:MM → HH:MM:SS）
- destination_path が不要な種別（Pack, CreditShop, Pvp 等）は空文字列（""）ではなく NULL として出力してください

---
（要件テキストをここに貼り付け）
```

---

## 補足: テーブル構造

```
MstHomeBanner（バナー定義・N行）
  ├─ id: 整数連番（自動採番）
  ├─ release_key: リリースキー（bigint）
  ├─ destination: 遷移先種別（enum）
  │    Gacha / Event / Pack / CreditShop / BasicShop / Pass /
  │    Web / BeginnerMission / AdventBattle / Pvp / None
  ├─ destination_path: 遷移先詳細（Gacha→ガチャID, Event→イベントID, Web→URL, それ以外→NULL）
  ├─ asset_key: バナー画像のAddressablesキー
  ├─ sort_order: 表示順（昇順・小さいほど先頭）
  ├─ start_at: 掲載開始日時（JST）
  └─ end_at: 掲載終了日時（JST）
```

このテーブルは単体で完結しており、他テーブルへの外部キー参照はない。ただし `destination_path` に設定するIDは、参照先（`mst_events.id` や対応するガチャID）と一致していなければならない。

---

## 注意事項

- **id の採番**: 整数の連番。既存 `MstHomeBanner.csv` の最大IDの次の値を使用すること
- **時刻フォーマット**: CSV上は `YYYY-MM-DD HH:MM:SS` 形式（秒まで記載）。時刻はすべてJST前提
- **掲載開始時刻の慣例**: `15:00:00`（JST 15時）開始が多い。イベント・ガチャの開始時刻と必ず合わせること
- **掲載終了時刻の慣例**: `14:59:59`（HH:59:59）終了が多い。イベント・ガチャの終了時刻と合わせること
- **destination_path の設定**: `Gacha` は必ずガチャIDを設定する。`Pack` / `CreditShop` / `Pvp` 等は NULL（設定漏れは遷移先不定のバグになる）
- **asset_key の一致**: 対応するアセットがAddressablesに登録されていないとバナー画像が表示されない。アセット担当者と連携してキー名を確定すること
- **1リリースあたりの件数**: 2〜5件が標準。イベント告知1件 + ガチャ告知1〜2件 + パック/キャンペーン0〜2件の構成が多い
- **フェスガチャのバナー**: フェスガチャは複数リリース期間にまたがることがある。その場合 `end_at` を次リリース以降の日付に設定する（例: フェスガチャを202512020で追加し、202602010の期間まで延長する場合、そのバナーは202512020で1件登録し `end_at` を延長日に設定する）
- **同一 sort_order の扱い**: 掲載期間が重複しない場合は同じ `sort_order` を複数バナーで使い回せる。重複する場合は異なる値にすること

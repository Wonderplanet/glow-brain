# MstHomeBanner 詳細説明

> CSVパス: `projects/glow-masterdata/MstHomeBanner.csv`

---

## 概要

`MstHomeBanner` は**ホーム画面（タイトル画面）のトップに表示されるバナー（告知画像）の設定テーブル**。バナーの表示期間・タップ時の遷移先・表示する画像アセット・表示順を管理する。期間外のバナーは表示されない。

### ゲームプレイへの影響

- **start_at / end_at** で表示期間を制御する。期間内のバナーのみホーム画面に表示される。
- **destination** でタップ時に遷移する画面を指定する。ガチャ・イベント・ショップなどへの誘導に使う。
- **destination_path** で遷移先の詳細（ガチャIDなど）を指定する。`None` や特に識別子が不要な遷移先ではNULL。
- **sort_order** で複数バナーが表示される場合の並び順を制御する（昇順）。
- **asset_key** でバナー画像のAddressablesキーを指定する。

### 関連テーブルとの構造図

```
MstHomeBanner（バナー設定）
  └─ destination = Event        → イベント画面へ遷移（destination_path にイベントIDを設定）
  └─ destination = Gacha        → ガチャ画面へ遷移（destination_path にガチャIDを設定）
  └─ destination = Pack         → パック購入画面へ遷移
  └─ destination = CreditShop   → クレジットショップへ遷移
  └─ destination = Pvp          → PvP画面へ遷移
  └─ destination = Web          → 外部WebURLへ遷移（destination_path にURLを設定）
```

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（整数連番） |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `destination` | enum | 不可 | None | タップ時の遷移先タイプ（`HomeBannerDestinationType` enum） |
| `destination_path` | varchar(255) | 不可 | "" | 遷移先の詳細情報（ガチャIDやURLなど）。不要な場合はNULL |
| `asset_key` | varchar(255) | 不可 | "" | バナー画像のAddressablesアセットキー |
| `sort_order` | int | 不可 | - | ホーム画面上の表示順（昇順） |
| `start_at` | timestamp | 不可 | - | 掲載開始日時 |
| `end_at` | timestamp | 不可 | - | 掲載終了日時 |

---

## HomeBannerDestinationType（遷移先種別）

| 値 | 説明 | destination_path |
|----|------|-----------------|
| `None` | 遷移なし（バナーをタップしても何も起きない） | NULL |
| `Gacha` | ガチャ画面へ遷移 | ガチャID（例: `Pickup_kai_001`） |
| `CreditShop` | クレジットショップ（有料アイテムショップ）へ遷移 | NULL |
| `BasicShop` | 基本ショップへ遷移 | NULL |
| `Event` | イベント画面へ遷移 | イベントID（例: `event_kai_00001`） |
| `Web` | 外部WebページへWV遷移 | URL文字列 |
| `Pack` | パック購入画面へ遷移 | NULL |
| `Pass` | パス購入画面へ遷移 | NULL |
| `BeginnerMission` | 初心者ミッション画面へ遷移 | NULL |
| `AdventBattle` | アドベントバトル画面へ遷移 | NULL |
| `Pvp` | PvP画面へ遷移 | NULL |

---

## 命名規則 / IDの生成ルール

| 種類 | 命名パターン | 例 |
|------|------------|-----|
| id | 整数連番（自動採番） | `1`, `2`, `46` |
| asset_key | `hometop_{コンテンツ種別}_{キャラ/コンテンツ}_{連番}` | `hometop_gacha_kai_00001`, `hometop_event_kai_00001` |

---

## 他テーブルとの連携

このテーブルは他マスタテーブルとの外部キー参照は持たないが、`destination_path` の値でコンテンツIDを参照することがある。

| 参照先 | destination | destination_path |
|-------|-------------|-----------------|
| ガチャID | `Gacha` | ガチャのID文字列 |
| イベントID | `Event` | `mst_events.id` の値 |

---

## 実データ例

**パターン1: PvPバナー（常設・長期掲載）**
```
ENABLE: e
id: 6
destination: Pvp
destination_path: NULL
asset_key: hometop_pvp_00001
sort_order: 1
start_at: 2024-01-01 00:00:00
end_at: 2030-01-02 00:00:00
release_key: 202509010
```
- PvP画面へ遷移するバナー
- `sort_order: 1` で最前面に表示
- 長期掲載設定

**パターン2: ガチャバナー（期間限定）**
```
ENABLE: e
id: 2
destination: Gacha
destination_path: Pickup_kai_001
asset_key: hometop_gacha_kai_00001
sort_order: 5
start_at: 2025-09-22 11:00:00
end_at: 2025-10-06 11:59:59
release_key: 202509010
```
- `Pickup_kai_001` ガチャへ遷移するバナー
- ガチャ開催期間中のみ表示

---

## 設定時のポイント

1. **期間の重複管理**: 同じ `sort_order` に複数のバナーを設定しても期間が重複しない限りは問題ない。ガチャバナーは定期的に切り替わるため `sort_order = 5` などを使い回す運用が一般的。
2. **destination_path の設定**: `Gacha` の場合は必ずガチャIDを設定する。`Pvp` `Pack` などはNULLで構わない。設定漏れがあると遷移先が不定になる。
3. **asset_key の命名規則**: `hometop_{コンテンツ種別}_{識別子}_{連番}` 形式で統一する。対応するアセットがAddressablesに登録されていないとバナー画像が表示されない。
4. **sort_order の設計**: 小さい値ほど上位（優先表示）に表示される。常設バナー（PvP等）には小さい値を、ガチャ・イベントなど頻繁に入れ替わるバナーには大きめの値を設定する。
5. **end_at の厳密な設定**: ガチャやイベントの終了日時と合わせてバナーの `end_at` も設定する。終了後もバナーが残ると終了済みコンテンツに遷移する問題が起きる。
6. **id の採番**: 整数の連番で採番する。既存の最大IDの次の値を使用する。
7. **長期バナーの end_at**: 常設に近い告知バナーは `end_at` を `2030-01-01` や `2034-01-01` などの遠い未来に設定して実質常設にする運用がされている。

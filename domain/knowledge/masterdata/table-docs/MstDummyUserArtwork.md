# MstDummyUserArtwork 詳細説明

> CSVパス: `projects/glow-masterdata/MstDummyUserArtwork.csv`

---

## 概要

`MstDummyUserArtwork` は**ダミーユーザーが所持している原画（アートワーク）の定義テーブル**。

PvPなどのマルチプレイ機能において、実際のプレイヤーが少ない場合やマッチング用のダミー対戦相手として使用される仮想ユーザー（`MstDummyUser`）が所持する原画をここで管理する。ダミーユーザーのプロフィール表示やフォロワー一覧表示時に参照される。

### ゲームプレイへの影響

- ダミーユーザーに割り当てられた原画は、そのダミーユーザーのプロフィール画面に表示される
- 強さ帯（ランク層）ごとに異なるダミーユーザーが設定され、それぞれが保有するアートワークもランクに応じて変化する
- CSVの行数は102件（2026年3月現在）

### 関連テーブルとの構造図

```
MstDummyUser（ダミーユーザー本体）
  └─ id → MstDummyUserArtwork.mst_dummy_user_id（1:N、所持原画リスト）
                └─ mst_artwork_id → MstArtwork.id（原画マスタ）

MstDummyUser
  └─ id → MstDummyUserUnit.mst_dummy_user_id（1:N、所持ユニットリスト）
```

---

## 全カラム一覧

### mst_dummy_user_artworks カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。`DummyUserArtwork{連番}` の形式 |
| `release_key` | bigint | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |
| `mst_dummy_user_id` | varchar(255) | 不可 | - | 参照先ダミーユーザーID（`mst_dummy_users.id`） |
| `mst_artwork_id` | varchar(255) | 不可 | - | 参照先原画ID（`mst_artwork.id`） |

**ユニーク制約**: `(mst_dummy_user_id, mst_artwork_id)` の組み合わせが重複不可

---

## 命名規則 / IDの生成ルール

| 項目 | 規則 | 例 |
|------|------|----|
| `id` | `DummyUserArtwork{0始まり連番}` | `DummyUserArtwork0`, `DummyUserArtwork99` |
| `mst_dummy_user_id` | `{ランク層}_{連番}_user{N}` | `bronze_0_user1`, `Platinum_1_user5` |
| `mst_artwork_id` | `artwork_{作品略称}_{4桁連番}` | `artwork_spy_0001`, `artwork_kai_0001` |

---

## 他テーブルとの連携

| テーブル | 参照方向 | 用途 |
|---------|---------|------|
| `mst_dummy_users` | `mst_dummy_user_id` → `id` | ダミーユーザー本体（ランク・メインユニット・エンブレム等） |
| `mst_artworks` | `mst_artwork_id` → `id` | 原画マスタ（アセットキーや解放条件） |

---

## 実データ例

### パターン1: ブロンズランクのダミーユーザー

```
ENABLE: e
id: DummyUserArtwork0
release_key: 202509010
mst_dummy_user_id: broze_0_user1
mst_artwork_id: artwork_spy_0001
```

ブロンズ0帯のuser1は `artwork_spy_0001`（SPY×FAMILY 原画）を所持。

### パターン2: プラチナランクのダミーユーザー

```
ENABLE: e
id: DummyUserArtwork78
release_key: 202509010
mst_dummy_user_id: Platinum_1_user1
mst_artwork_id: artwork_kai_0001
```

プラチナ1帯のuser1は `artwork_kai_0001`（怪獣8号 原画）を所持。ランクが高いユーザーはより多くの原画を所持しているケースがある。

---

## 設定時のポイント

1. **1ダミーユーザーに複数の原画を持たせることができる**。ユニーク制約は `(user_id, artwork_id)` の組み合わせなので、同じ原画を重複して持たせることはできない。

2. **ランク帯ごとに持たせる原画を変えることを推奨**。低ランク帯ダミーユーザーには少ない原画、高ランク帯ダミーユーザーにはイベント原画なども持たせることで、実際のプレイヤー分布に近い設定が可能。

3. **`mst_dummy_user_id` のランク表記の大文字・小文字に注意**。実データでは `broze_0_user1`（スペルミスあり）や `Platinum_1_user1`（先頭大文字）が混在しているため、既存データと表記を合わせること。

4. **`id` は `DummyUserArtwork{連番}` 形式**。CSVの行追加時は既存最大連番の次の番号を使用する。

5. **`mst_artwork_id` は `mst_artworks` テーブルに存在するIDのみ指定可能**。存在しないIDを設定するとデータロードエラーの原因になる。

6. **release_key はリリース時期に合わせて設定**。初期データは `202509010`。新規追加時は該当リリースキーを設定する。

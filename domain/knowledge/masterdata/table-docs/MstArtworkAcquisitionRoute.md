# MstArtworkAcquisitionRoute 詳細説明

> CSVパス: `projects/glow-masterdata/MstArtworkAcquisitionRoute.csv`

---

## 概要

原画の入手経路を管理するテーブル。プレイヤーがどのようなコンテンツ（パネルミッション・ユニットグレードアップなど）を通じて特定の原画を入手できるかを定義する。

1つの原画に対して複数の入手経路を設定でき、`content_type` と `content_id` の組み合わせで入手元コンテンツを指定する。クライアントはこの情報をもとに、原画詳細画面などで入手経路をユーザーに案内する。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | UUID。入手経路レコードを一意に識別するID |
| mst_artwork_id | varchar(255) | YES | 対象の原画の `mst_artworks.id` |
| content_type | varchar(255) | YES | コンテンツタイプ（入手元の種別） |
| content_id | varchar(255) | YES | コンテンツID（入手元の具体的なID） |
| release_key | bigint | YES | リリースキー |

---

## content_type（コンテンツタイプ）

実データで確認されているコンテンツタイプ:

| content_type 値 | 説明 | content_id の指す先 |
|----------------|------|-------------------|
| `PanelMission` | パネルミッション | パネルミッションのID |
| `UnitGrade` | ユニットグレードアップ | キャラのID |

---

## 命名規則 / IDの生成ルール

- `id`: 原画IDと同じ値を使用するケースが多い（例: `artwork_event_sur_0003`、`artwork_chara_spy_00001`）
- 複数の入手経路がある場合は連番や接尾辞で区別する

---

## 他テーブルとの連携

| テーブル | 関係 | 説明 |
|---------|------|------|
| `mst_artworks` | 多対1 | 対象の原画。`mst_artwork_id` で参照 |
| `mst_artwork_panel_missions` 等 | 多対1 | `content_type` に対応するコンテンツのテーブルを `content_id` で参照 |

---

## 実データ例

### パターン1: パネルミッション経由の入手

```
ENABLE: e
id: artwork_event_sur_0003
mst_artwork_id: artwork_event_sur_0003
content_type: PanelMission
content_id: artwork_panel_f05anniv_01
release_key: 202603020
```

### パターン2: ユニットグレードアップ経由の入手

```
ENABLE: e
id: artwork_chara_spy_00001
mst_artwork_id: artwork_chara_spy_00001
content_type: UnitGrade
content_id: chara_spy_00001
release_key: 202603020
```

```
ENABLE: e
id: artwork_chara_spy_00101
mst_artwork_id: artwork_chara_spy_00101
content_type: UnitGrade
content_id: chara_spy_00101
release_key: 202603020
```

---

## 設定時のポイント

1. **content_type と content_id の整合性**: `content_type` に応じた正しい `content_id` を設定する。`UnitGrade` ならキャラID、`PanelMission` ならパネルミッションIDを指定する。
2. **mst_artwork_id の確認**: 存在しない `mst_artwork_id` を参照しないよう、`mst_artworks` テーブルでの存在確認を必ず行う。
3. **id の命名**: 実データでは `mst_artwork_id` と同じ値を `id` に使用するケースが多い。1つの原画に対して1つの入手経路の場合は `mst_artwork_id` を id に使用することを推奨。
4. **複数入手経路の設定**: 1つの原画に対して複数の入手経路を設定したい場合は、異なる `id` で複数レコードを作成する。
5. **release_key の統一**: 同一の入手経路コンテンツに対して追加する原画は、コンテンツのリリースキーと合わせて設定する。
6. **コンテンツ廃止時の対応**: コンテンツが終了した場合、`ENABLE` を無効にして入手経路を非表示にする。

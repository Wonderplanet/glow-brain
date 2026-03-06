# MstArtworkFragmentPosition 詳細説明

> CSVパス: `projects/glow-masterdata/MstArtworkFragmentPosition.csv`

---

## 概要

原画のかけらの表示位置を管理するテーブル。原画はパズルのように複数のかけらが特定のマス目（1〜16）に配置されて完成する仕組みで、このテーブルで各かけらがどのマス目に表示されるかを定義する。

`mst_artwork_fragments` と1対1の関係にあり、かけらごとに1つの表示位置レコードが存在する。ゲーム内で原画の完成状況をビジュアルで確認する際に、このデータが使用される。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| release_key | bigint | YES | リリースキー |
| id | varchar(255) | YES | UUID。位置レコードを一意に識別するID（`mst_artwork_fragment_id` と同値） |
| mst_artwork_fragment_id | varchar(255) | YES | 紐付くかけらの `mst_artwork_fragments.id` |
| position | smallint unsigned | NO | 表示位置（1〜16の整数。NULL可） |

---

## position（表示位置）

原画は4×4のグリッドレイアウト（16マス）に分割されており、位置番号1〜16で各マスを指定する。

```
マスの配置イメージ（4×4グリッド）:
 1  2  3  4
 5  6  7  8
 9 10 11 12
13 14 15 16
```

NULL の場合は表示位置が未定義（非表示またはデフォルト位置）。

---

## 命名規則 / IDの生成ルール

- `id`: `mst_artwork_fragment_id` と同じ値を使用（例: `artwork_fragment_dan_00101`）

---

## 他テーブルとの連携

| テーブル | 関係 | 説明 |
|---------|------|------|
| `mst_artwork_fragments` | 1対1 | 対象のかけら。`mst_artwork_fragment_id` で参照 |

---

## 実データ例

### パターン1: ダンダダン作品のかけら位置設定

```
ENABLE: e
release_key: 202509010
id: artwork_fragment_dan_00101
mst_artwork_fragment_id: artwork_fragment_dan_00101
position: 13

ENABLE: e
release_key: 202509010
id: artwork_fragment_dan_00102
mst_artwork_fragment_id: artwork_fragment_dan_00102
position: 2

ENABLE: e
release_key: 202509010
id: artwork_fragment_dan_00103
mst_artwork_fragment_id: artwork_fragment_dan_00103
position: 15

ENABLE: e
release_key: 202509010
id: artwork_fragment_dan_00104
mst_artwork_fragment_id: artwork_fragment_dan_00104
position: 12

ENABLE: e
release_key: 202509010
id: artwork_fragment_dan_00105
mst_artwork_fragment_id: artwork_fragment_dan_00105
position: 8
```

### パターン2: 異なる位置番号のかけら（赤（aka）作品）

```
ENABLE: e
release_key: 202509010
id: artwork_fragment_aka_00101
mst_artwork_fragment_id: artwork_fragment_aka_00101
position: 16
```

---

## 設定時のポイント

1. **position の一意性確認**: 同一原画のかけら群（同じ `mst_artwork_id` を持つ fragments）の中で、各 `position` が重複していないことを確認する。同じ位置に2つのかけらは設定できない。
2. **fragment との1対1対応**: `mst_artwork_fragments` の各レコードに対して、必ず1つの `mst_artwork_fragment_positions` レコードを作成する。
3. **id の一致**: `id` と `mst_artwork_fragment_id` は同じ値を使用する慣例に従う。
4. **1〜16の範囲**: `position` は1〜16の範囲で指定する。4×4グリッドのどのマスに表示するかを意味する。
5. **かけらの分散配置**: 見た目のバランスを考慮して、かけらの位置がグリッド全体に分散するよう配置することが望ましい。連番で隣接するマスに集中させると視覚的にアンバランスになる場合がある。
6. **release_key の統一**: 対応する `mst_artwork_fragments` と同じ `release_key` を設定する。

# MstTutorialTipI18n 詳細説明

> CSVパス: `projects/glow-masterdata/MstTutorialTipI18n.csv`

---

## 概要

チュートリアルダイアログで表示するTips（ヒント）の画像アセットパスとタイトルを多言語で管理するテーブル。各チュートリアル（`mst_tutorials`）に関連付けられた複数のTipsを、言語・表示順序・タイトル・アセットキーで管理する。

- `mst_tutorial_id` で `mst_tutorials` テーブルと紐付く
- 1チュートリアルに複数のTipsを設定できる（`sort_order` で順序管理）
- 現在の対応言語は `ja`（日本語）のみ
- アセットキーは Addressables で管理される画像リソースのキー

---

## 全カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | 不可 | - | 連番ID（整数） |
| release_key | bigint | 不可 | `1` | リリースキー |
| mst_tutorial_id | varchar(255) | 不可 | `` | 対象チュートリアルID（`mst_tutorials.id`） |
| language | enum | 不可 | `ja` | 言語（現在は `ja` のみ対応） |
| sort_order | int | 不可 | `0` | このチュートリアル内でのTips表示順序（昇順） |
| title | varchar(255) | 不可 | `` | Tipsのタイトル |
| asset_key | varchar(255) | 不可 | `` | Tipsに表示する画像のアセットキー（Addressables） |

---

## Language（対応言語）

| 値 | 説明 |
|---|---|
| `ja` | 日本語（現在対応している唯一の言語） |

---

## 他テーブルとの連携

| 関連テーブル | カラム | 説明 |
|---|---|---|
| `mst_tutorials` | `mst_tutorial_id` → `mst_tutorials.id` | どのチュートリアルのTipsかを特定 |

---

## 実データ例

### 例1: TutorialStartIntroduction に関連するTips（属性紹介）

```
id   | release_key | mst_tutorial_id           | language | sort_order | title               | asset_key
1    | 202509010   | TutorialStartIntroduction | ja       | 1          | 属性について        | attribute_1
2    | 202509010   | TutorialStartIntroduction | ja       | 2          | 属性について        | attribute_2
3    | 202509010   | TutorialStartIntroduction | ja       | 3          | ロールについて      | role_1
4    | 202509010   | TutorialStartIntroduction | ja       | 4          | ロールについて      | role_2
```

「TutorialStartIntroduction」には属性とロールに関する4枚のTips画像が設定されている。

### 例2: 後から追加されたTips（JUMBLE RUSH紹介）

```
id   | release_key | mst_tutorial_id           | language | sort_order | title               | asset_key
1001 | 202603020   | TutorialStartIntroduction | ja       | 5          | JUMBLE RUSHについて | rush_1
1002 | 202603020   | TutorialStartIntroduction | ja       | 6          | JUMBLE RUSHについて | rush_2
1003 | 202603020   | Main1                     | ja       | 1          | キャラ強化について  | enhance_unit
```

後のリリース（202603020）で追加されたTips。sort_order 5・6 として既存Tipsの後に追加されている。

---

## 設定時のポイント

1. **sort_order で表示順序を設定**: 同一チュートリアルID内では sort_order 昇順で Tips が順番に表示される
2. **追加Tipsは末尾の sort_order を使用**: 既存のTipsに後から追加する場合は、既存の最大 sort_order より大きい値を使用する
3. **id は連番で管理**: 実データでは整数の連番（1, 2, 3...）で管理されている。後から追加するレコードはIDの空きを埋めないように注意し、新しい連番（1001以降など）を使用している
4. **asset_key は Addressables のキーと一致させる**: 指定した `asset_key` に対応するアセットがゲームクライアントのAddressablesリソースに登録されていなければ画像が表示されない
5. **クライアントクラス**: `MstTutorialTipI18nData`（`GLOW.Core.Data.Data`名前空間）。`id`・`mstTutorialId`・`language`（`Language` enum）・`sortOrder`・`title`・`assetKey` が配信される
6. **mst_tutorials との対応**: チュートリアルが存在する場合、対応するTipsを必要に応じてこのテーブルに追加する。Tipsが不要なチュートリアルにはレコードを追加しなくてよい
7. **title は表示テキスト、asset_key は画像リソース**: Tips表示はタイトルテキスト + 画像の構成になっているため、両方を適切に設定する

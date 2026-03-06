# MstDummyUser 詳細説明

> CSVパス: `projects/glow-masterdata/MstDummyUser.csv`
> i18n CSVパス: `projects/glow-masterdata/MstDummyUserI18n.csv`

---

## 概要

PVPなどの対戦コンテンツで使用するCPU対戦相手（ダミーユーザー）のプロフィール情報を管理するマスタテーブル。
代表ユニット・エンブレム・図鑑効果用グレードレベル合計などを設定する。
ランク帯別に複数のダミーユーザーを用意することで、マッチングの多様性を実現する。

ダミーユーザーの表示名は多言語対応のため `MstDummyUserI18n` テーブルで管理する。

クライアントクラス: `MstDummyUserData.cs` / `MstDummyUserI18nData.cs`

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---|---|---|---|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | ダミーユーザーID（主キー） |
| release_key | bigint | YES | リリースキー（デフォルト: 1） |
| mst_unit_id | varchar(255) | NO | 代表ユニットID（`mst_units.id`、NULL可） |
| mst_emblem_id | varchar(255) | NO | エンブレムID（`mst_emblems.id`、NULL可） |
| grade_unit_level_total_count | int | YES | 図鑑効果用グレードレベル合計（デフォルト: 1） |

### MstDummyUserI18n カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---|---|---|---|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | int | YES | レコードID（主キー、数値型） |
| release_key | bigint | YES | リリースキー（デフォルト: 1） |
| mst_dummy_user_id | varchar(255) | YES | 対応するダミーユーザーID（`mst_dummy_users.id`） |
| language | enum('ja') | YES | 言語コード（現在は `ja` のみ対応、デフォルト: ja） |
| name | varchar(255) | NO | ダミーユーザーの表示名称（NULL可） |

ユニークキー: `(mst_dummy_user_id, language)` の組み合わせで一意となる。

---

## 命名規則 / IDの生成ルール

ダミーユーザーIDはランク帯と連番で構成される。

| パターン | 意味 |
|---|---|
| `bronze_0_user{N}` | ブロンズランク0帯のダミーユーザーN番 |
| `bronze_1_user{N}` | ブロンズランク1帯のダミーユーザーN番 |
| `bronze_2_user{N}` | ブロンズランク2帯のダミーユーザーN番 |
| `bronze_3_user{N}` | ブロンズランク3帯のダミーユーザーN番 |

i18nテーブルのIDは数値連番（1, 2, 3...）で管理する。

---

## 他テーブルとの連携

| 参照先テーブル | カラム | 内容 |
|---|---|---|
| `mst_units` | `mst_unit_id` | 代表ユニットの参照 |
| `mst_emblems` | `mst_emblem_id` | エンブレムの参照 |

| 参照元テーブル | 用途 |
|---|---|
| `mst_dummy_outposts` | ダミーユーザーのアウトポスト設定 |
| `mst_dummy_user_units` | ダミーユーザーが保有するユニット設定 |
| `mst_dummy_users_i18n` | ダミーユーザーの多言語名称 |

---

## 実データ例

**MstDummyUser 例: ブロンズランク0のダミーユーザー**

| id | release_key | mst_unit_id | mst_emblem_id | grade_unit_level_total_count |
|---|---|---|---|---|
| bronze_0_user1 | 202509010 | chara_spy_00101 | （空） | 1 |
| bronze_0_user2 | 202509010 | chara_jig_00001 | （空） | 1 |
| bronze_0_user3 | 202509010 | chara_yuw_00101 | （空） | 1 |

**MstDummyUserI18n 例: ダミーユーザーの表示名**

| id | release_key | mst_dummy_user_id | language | name |
|---|---|---|---|---|
| 1 | 202509010 | bronze_0_user1 | ja | リーダー0001 |
| 2 | 202509010 | bronze_0_user2 | ja | リーダー0002 |
| 7 | 202509010 | bronze_1_user1 | ja | リーダー0007 |

---

## 設定時のポイント

1. `mst_unit_id` はダミーユーザーの代表ユニット（ランキング・マッチング画面で表示される）で、NULL可。
2. `mst_emblem_id` はダミーユーザーが装備しているエンブレムで、NULL可（エンブレムなしも許容）。
3. `grade_unit_level_total_count` は図鑑効果の計算に使用される値で、ランク帯に応じて設定する。
4. 各ランク帯（bronze_0 〜 bronze_3）に複数のダミーユーザーを用意することでマッチングの多様性を確保する。
5. i18nの `name` は `リーダー{4桁連番}` 形式で統一されている。新規追加時は既存の最大番号+1で命名する。
6. i18nのIDは数値連番。CSVに追加する際は既存の最大ID+1の値を設定する。
7. i18nは現在 `ja`（日本語）のみ対応。他言語追加時はスキーマのenum定義変更が必要。
8. ダミーユーザーを追加した場合は `mst_dummy_outposts` および `mst_dummy_user_units` テーブルへの設定追加も忘れずに行う。

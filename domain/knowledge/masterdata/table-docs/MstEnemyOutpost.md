# MstEnemyOutpost 詳細解説

## 1. 概要

`MstEnemyOutpost` はインゲームバトルにおける**敵側ゲート（アウトポスト）定義テーブル**です。

GLOWのインゲームバトルにはプレイヤー側と敵側の2つのゲートが存在します。プレイヤー側ゲートはプレイヤーのアウトポスト強化情報から動的に生成されますが、**敵側ゲートはこのテーブルで固定定義**されます。`MstInGame` テーブルの `mst_enemy_outpost_id` からこのテーブルを参照することで、各ステージ・クエストに対応した敵ゲートの HP・ビジュアルが決まります。

| 項目 | 内容 |
|------|------|
| **DBテーブル名** | `mst_enemy_outposts`（スキーマ定義なし、CSVのみ） |
| **CSVファイル名** | `MstEnemyOutpost.csv` |
| **クライアントデータクラス** | `MstEnemyOutPostData.cs` |
| **クライアントモデルクラス** | `MstEnemyOutpostModel.cs` |
| **総レコード数** | 512件 |
| **主なユースケース** | ステージ・イベント・PvP・レイドにおける敵ゲートのHP・ビジュアル定義 |

---

## 2. 全カラム一覧

| カラム名 | 型 | NULL | デフォルト値 | 説明 |
|---------|-----|------|-------------|------|
| `ENABLE` | varchar | - | - | 有効フラグ。`e` = 有効（CSVの共通制御カラム） |
| `id` | varchar | NO | - | 敵アウトポストの一意識別子（主キー） |
| `hp` | integer | NO | - | ゲートの最大HP |
| `is_damage_invalidation` | varchar | YES | 空文字（false） | ダメージ無効フラグ。`1` = ダメージを受けない |
| `outpost_asset_key` | varchar | YES | 空文字 | ゲート専用3Dモデルアセットのキー。設定時は専用モデルを使用 |
| `artwork_asset_key` | varchar | YES | 空文字 | アートワーク画像アセットのキー。`outpost_asset_key` が空の場合に使用 |
| `release_key` | integer | NO | - | リリースキー（マスタデータの段階的公開制御） |

### カラム補足

**`is_damage_invalidation` の型について**

CSVでは `varchar` として格納されており、`1` の文字列が設定されたレコードがダメージ無効を意味します。クライアント側（`EnemyOutpostDataTranslator.cs`）で `bool` 型（`OutpostDamageInvalidationFlag`）に変換されます。空文字は `false` 相当です。

**`outpost_asset_key` と `artwork_asset_key` の使い分け**

2つのアセットキーは排他的に使用されます。クライアント実装（`OutpostFactory.cs`）では以下のロジックで処理されます:

- `outpost_asset_key` が設定されている → ゲート専用の3Dモデルを使用（`OutpostAssetKey` として扱う）
- `outpost_asset_key` が空 かつ `artwork_asset_key` が設定されている → アートワーク画像のサムネイルを使用（`ArtworkAssetPath.CreateSmall` で生成）
- 両方とも空 → デフォルトのゲートモデルを使用（`OutpostAssetKey.EnemyDefault`）

---

## 3. 主要な enum / フラグの解説

### is_damage_invalidation（ダメージ無効フラグ）

| CSV値 | クライアントでの値 | 説明 |
|-------|-----------------|------|
| 空文字（未設定） | `OutpostDamageInvalidationFlag.False` | 通常のゲート。プレイヤーの攻撃でHPが減少する |
| `1` | `OutpostDamageInvalidationFlag.True` | ダメージを受けない特殊ゲート |

ダメージ無効フラグが設定されているケースは主に以下:

| IDパターン | 用途 | 代表例 |
|-----------|------|--------|
| `raid_*` | レイドバトル（HP 1,000,000 固定） | `raid_kai_00001`, `raid_dan1_00001` |
| `*_sur_*` | サーフェスキャラ関連 | `normal_sur_00003`, `hard_sur_00003` |
| `*_mag_*` | マグキャラ関連（難易度によって異なる） | `normal_mag_00001`, `hard_mag_00001` |
| `enhance_*` | 強化クエスト | `enhance_glo_00001` |

---

## 4. 命名規則 / IDの生成ルール

### id の命名規則

ID はステージ種別・キャラ識別子・連番の組み合わせで構成されます。

#### 通常ステージ・フリークエスト系

```
{difficulty}_{character_abbr}_{sequential_number}
```

| セグメント | 説明 | 取り得る値 |
|-----------|------|-----------|
| `difficulty` | 難易度 | `normal`, `hard`, `veryhard` |
| `character_abbr` | キャラ略称（3〜4文字） | `glo1`, `glo2`, `glo3`, `glo4`, `dan`, `spy`, `jig`, `gom`, `chi`, `rik`, `tak`, `mag`, `sum`, `sur`, `osh` など |
| `sequential_number` | 連番（5桁ゼロパディング） | `00001`, `00002`, ... |

**例:**
- `normal_glo1_00001` → 通常難易度 GLOキャラ1用 1番
- `hard_dan_00003` → ハード難易度 DANキャラ用 3番
- `veryhard_jig_00006` → ベリーハード難易度 JIGキャラ用 6番

#### イベント系

```
event_{character_abbr}_{event_type}_{sequential_number}
```

| セグメント | 説明 | 取り得る値 |
|-----------|------|-----------|
| `event_` | イベントを示すプレフィックス | 固定 |
| `character_abbr` | キャラ略称（数字付きの場合もあり） | `dan1`, `kai1`, `spy1`, `mag1`, `sur1`, `osh1`, `jig1`, `you1` など |
| `event_type` | イベント種別 | `1day`, `challenge01`, `challenge02`, `charaget01`, `charaget02`, `savage`, など |
| `sequential_number` | 連番（5桁ゼロパディング） | `00001`, `00002`, ... |

**例:**
- `event_dan1_challenge01_00001` → DANキャライベント チャレンジ01 1番
- `event_kai1_charaget02_00003` → KAIキャライベント キャラゲット02 3番

#### レイドバトル系

```
raid_{character_abbr}_{sequential_number}
```

**例:**
- `raid_kai_00001` → KAIキャラ レイドバトル（HP: 1,000,000）
- `raid_dan1_00001` → DANキャラ1 レイドバトル（HP: 1,000,000）

#### 特殊ID

| ID | 用途 |
|----|------|
| `pvp` | PvP対戦用の基準アウトポスト。実際のHPは `OutpostMaxHpCalculator` で計算され上書きされる |
| `enhance_glo_00001` | 強化クエスト（Enhance Quest）用 |
| `plan_test_stage001` | 企画テスト用ステージ |
| `1` 〜 `14` | 初期リリース段階の汎用番号。HPのみ設定（アセットキーなし） |

#### 数値ID（1〜14）のHP体系

初期実装として追加された汎用アウトポスト。HP値はペアで同一値を持ちます:

| ID | HP |
|----|----|
| 1, 2 | 1,000 |
| 3, 4 | 2,500 |
| 5, 6 | 3,500 |
| 7, 8 | 4,500 |
| 9, 10 | 5,500 |
| 11, 12 | 10,000 |
| 13, 14 | 15,000 |

---

## 5. 他テーブルとの連携

### 参照関係図

```
MstInGame (1)
    └── mst_enemy_outpost_id ──→ MstEnemyOutpost.id (1)
                                    ├── outpost_asset_key  → 3Dモデルアセット（Addressables）
                                    └── artwork_asset_key  → MstArtwork.asset_key への間接参照
```

| テーブル/リソース | 結合カラム | 説明 |
|----------------|---------|------|
| `MstInGame` | `MstInGame.mst_enemy_outpost_id` → `MstEnemyOutpost.id` | ステージごとに使用する敵ゲートを指定する。1つの `MstEnemyOutpost` を複数の `MstInGame` が参照できる |
| アートワークアセット（Addressables） | `artwork_asset_key` | `MstArtwork` テーブルの `asset_key` に対応する文字列。クライアントで `ArtworkAssetPath.CreateSmall()` を経由してサムネイルとして表示 |
| ゲートモデルアセット（Addressables） | `outpost_asset_key` | ゲート専用3Dモデルのアセットキー。`OutpostAssetKey` ValueObjectとして扱われる |

### クライアント実装での主な使用箇所

| ファイル | 役割 |
|---------|------|
| `MstEnemyOutPostData.cs` | マスタデータの生データクラス（SchemaBuilderで自動生成） |
| `MstEnemyOutpostModel.cs` | ドメイン層のモデル定義（record型） |
| `EnemyOutpostDataTranslator.cs` | Data → Model 変換（型変換・空文字チェック）|
| `IMstEnemyOutpostDataRepository.cs` | リポジトリインターフェース定義 |
| `OutpostInitializer.cs` | インゲーム初期化時に敵ゲートを生成 |
| `OutpostFactory.cs` | `MstEnemyOutpostModel` から `OutpostModel` を生成。PvP時はHP計算を上書き |
| `StageDataTranslator.cs` | ステージデータ変換で `mst_enemy_outpost_id` を解決 |
| `MstPvpBattleModelTranslator.cs` | PvPバトル設定時に参照 |
| `MstAdventBattleModelTranslator.cs` | 降臨バトル設定時に参照 |

---

## 6. 実データ例

### 例1: 通常ステージ用（アセットキーなし）

```
ENABLE:                   e
id:                       normal_glo1_00001
hp:                       60000
is_damage_invalidation:   (空)
outpost_asset_key:        (空)
artwork_asset_key:        glo1_0001
release_key:              202509010
```

→ アセットキーなし、通常ゲート。`artwork_asset_key` が設定されているためアートワーク画像をサムネイルとして表示。

### 例2: 3Dモデルアセットを使用（outpost_asset_key あり）

```
ENABLE:                   e
id:                       enhance_glo_00001
hp:                       10000
is_damage_invalidation:   1
outpost_asset_key:        glo_enemy_0001
artwork_asset_key:        (空)
release_key:              202509010
```

→ 強化クエスト用。専用3Dモデル `glo_enemy_0001` を使用。ダメージ無効フラグ ON。

### 例3: レイドバトル用（HP最大・ダメージ無効）

```
ENABLE:                   e
id:                       raid_dan1_00001
hp:                       1000000
is_damage_invalidation:   1
outpost_asset_key:        (空)
artwork_asset_key:        dan_0001
release_key:              202510020
```

→ レイドバトル用ゲート。HP は 1,000,000 固定、ダメージ無効フラグ ON。アートワーク画像で表示。

### 例4: PvP用（HP は計算で上書きされる）

```
ENABLE:                   e
id:                       pvp
hp:                       100
is_damage_invalidation:   (空)
outpost_asset_key:        pvpplayer_default
artwork_asset_key:        (空)
release_key:              202509010
```

→ PvP対戦相手ゲート用。CSVの `hp: 100` はダミー値で、実際の HP は `OutpostMaxHpCalculator` によりプレイヤーのアウトポスト強化情報から再計算される（`OutpostFactory.GenerateOpponentOutpost` 参照）。専用3Dモデル `pvpplayer_default` を使用。

### 例5: 汎用番号ID（初期実装）

```
ENABLE:                   e
id:                       5
hp:                       3500
is_damage_invalidation:   (空)
outpost_asset_key:        (空)
artwork_asset_key:        (空)
release_key:              202509010
```

→ アセットキーがすべて空。`OutpostAssetKey.EnemyDefault` のデフォルトモデルが使用される。

---

## 7. 設定時のポイント

### アセットキーの排他設定

`outpost_asset_key` と `artwork_asset_key` は **どちらか一方のみ設定する**。クライアントの `OutpostFactory.cs` は以下の優先順位で処理します:

1. `outpost_asset_key` が設定されている → 3Dモデルを使用（`artwork_asset_key` は無視）
2. `outpost_asset_key` が空 かつ `artwork_asset_key` が設定されている → アートワーク画像を使用
3. 両方とも空 → デフォルトの敵ゲートモデル（`OutpostAssetKey.EnemyDefault`）を使用

### レイドバトルの設定必須事項

レイドバトル用アウトポストは以下を必ず設定する:
- `hp`: `1000000`（全レイドで統一）
- `is_damage_invalidation`: `1`（ダメージ無効）
- IDプレフィックス: `raid_{character_abbr}_{sequential_number}` 形式

### PvP用アウトポストの HP はダミー値

`id = pvp` のレコードの `hp` は実際のバトルでは使用されません。PvP 時は `OutpostFactory.GenerateOpponentOutpost` が呼ばれ、対戦相手のアウトポスト強化レベルに基づいて HP が再計算されます。CSVの `hp` 値（100）はデフォルト値として残しておくだけで問題ありません。

### MstInGame との連携

新しいステージ（`MstInGame`）を追加する際は、必ず対応する `mst_enemy_outpost_id` を `MstEnemyOutpost.id` のいずれかに設定する必要があります。適切な HP が既存のレコードに存在する場合は新規追加せず既存IDを流用できます（複数の `MstInGame` が同一の `MstEnemyOutpost` を参照することは許容されています）。

### HP の規模感

実データから得られる HP の分布:

| ステージ種別 | HP の目安 |
|------------|----------|
| 通常（数値ID） | 1,000 〜 15,000 |
| 通常クエスト（normal） | 5,000 〜 100,000 程度 |
| ハードクエスト（hard） | 50,000 〜 200,000 程度 |
| ベリーハードクエスト（veryhard） | 100,000 〜 300,000 程度 |
| イベント系 | 500 〜 100,000（難易度により幅広い） |
| レイドバトル | 1,000,000（固定） |

### release_key の設定

`release_key` は対応する `MstInGame` の `release_key` と同一か、それ以前の値を設定する。イベント期間に紐づいているアウトポストは、イベント開始タイミングの `release_key` を設定してください。

### id の文字列型について

`id` カラムはクライアントで `MasterDataId`（string ラッパー）として扱われます。数値 `1`〜`14` も **文字列として** CSVに格納されており、`MastDataId` コンストラクタには string を渡す仕様です。数値に見えるIDも文字列として扱ってください。

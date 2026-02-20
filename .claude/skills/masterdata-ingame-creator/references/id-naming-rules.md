# ID命名規則の詳細

インゲーム関連マスタデータで使用するIDの命名規則。

---

## MstInGame.id（インゲームID）

インゲーム関連テーブルの中心となるID。以下のテーブルでも同じIDを使用する:
- `MstAutoPlayerSequence.sequence_set_id`
- `MstPage.id`
- `MstEnemyOutpost.id`

### 命名パターン

```
{種別}_{シリーズID}{番号}_{ステージ識別子}_{連番5桁}
```

### 実例

| 種別 | パターン | 例 |
|------|---------|-----|
| イベントキャラゲット | `event_{シリーズ1}_{識別子}_{連番}` | `event_you1_charaget01_00001` |
| イベントチャレンジ | `event_{シリーズ1}_challenge{N}_{連番}` | `event_kai1_challenge01_00001` |
| イベントサベージ | `event_{シリーズ1}_savage_{連番}` | `event_jig1_savage_00001` |
| 1日限定 | `event_{シリーズ1}_1day_{連番}` | `event_dan1_1day_00001` |
| レイド | `raid_{シリーズ1}_{連番}` | `raid_kai1_00001` |
| 通常クエスト | `normal_{シリーズ}_{連番}` | `normal_dan_00001` |
| ハード | `hard_{シリーズ}_{連番}` | `hard_dan_00001` |
| ベリーハード | `veryhard_{シリーズ}_{連番}` | `veryhard_dan_00001` |

### シリーズIDの例

| シリーズ略称 | 意味 |
|------------|------|
| `kai1` | KAIキャラ（イベント1） |
| `dan1` | DANキャラ（イベント1） |
| `spy1` | SPYキャラ（イベント1） |
| `jig1` | JIGキャラ（イベント1） |
| `you1` | YOUキャラ（イベント1） |
| `gom` | GOMキャラ |
| `chi` | CHIキャラ |

---

## MstEnemyStageParameter.id

### 命名パターン

```
{プレフィックス}_{キャラ略称}_{キャラ番号}_{インゲームID短縮形}_{character_unit_kind}_{color}
```

### プレフィックスの使い分け

| プレフィックス | 意味 | `mst_enemy_character_id` の形式 |
|--------------|------|-------------------------------|
| `c_` | プレイヤーキャラが敵として登場 | `chara_{シリーズ}_{番号}` |
| `e_` | 敵専用キャラのパラメータ | `enemy_{シリーズ}_{番号}` |

### インゲームID短縮形のパターン

インゲームIDの一部を短縮してパラメータIDに組み込む:

| インゲームIDの一部 | 短縮形 | 意味 |
|------------------|-------|------|
| `event_you1_charaget01` | `you1_charaget01` | イベントキャラゲット |
| `event_kai1_challenge01` | `kai1_challenge01` | チャレンジ |
| `event_jig1_savage` | `jig1_savage` | サベージ |
| `raid_kai1` | `kai1_raid` | レイド |
| 複数ステージで使い回す場合 | `general` | 汎用 |

### 実例

| 種別 | 例 |
|------|-----|
| プレイヤーキャラ（ボス） | `c_you_00201_you1_charaget01_Boss_Yellow` |
| 敵キャラ（雑魚） | `e_you_00001_you1_charaget01_Normal_Colorless` |
| 敵キャラ（別雑魚） | `e_you_00101_you1_charaget01_Normal_Yellow` |
| 降臨ボス | `e_kai_00301_kai1_advent_Boss_Red` |
| 汎用雑魚 | `e_chi_00101_general_Normal_Colorless` |
| 変身あり（変身前） | `c_chi_00001_general_chi_vh_Boss_Blue` |
| 変身あり（変身後） | `c_chi_00002_general_chi_vh_Boss_Blue` |

---

## MstAutoPlayerSequence.id

### 命名パターン

```
{sequence_set_id}_{sequence_element_id}
```

### 実例

| 行の種類 | 例 |
|---------|-----|
| 通常行（element 1） | `event_you1_charaget01_00001_1` |
| 通常行（element 2） | `event_you1_charaget01_00001_2` |
| グループ切り替え行 | `event_you1_charaget01_00001_groupchange_1` |
| グループ内の行 | `event_you1_charaget01_00001_5` |

---

## MstKomaLine.id

### 命名パターン

```
{mst_page_id}_{row番号}
```

### 実例

| 例 |
|-----|
| `event_you1_charaget01_00001_1` |
| `event_you1_charaget01_00001_2` |

---

## MstStage.id / MstStageEventSetting / MstStageEventReward

MstStage.id は MstInGame.id と同一にすることが多い:

```
event_you1_charaget01_00001
```

複数ステージがある場合は連番を変える:

```
event_you1_charaget01_00001  ← ステージ1
event_you1_charaget01_00002  ← ステージ2
event_you1_charaget01_00003  ← ステージ3
```

---

## 禁止パターン

以下はIDとして使用禁止:

| NG | 理由 |
|----|------|
| スペース（空白） | URLエンコード等の問題が発生する |
| 大文字英字 | snake_case が慣例 |
| ドット(`.`)、スラッシュ(`/`) | パス解釈の問題 |
| ダブルアンダースコア（`__`）の意図的使用 | `__NULL__` と混在するリスク |
| 日本語・全角文字 | DB・API処理での文字化けリスク |

---

## release_key

新規データを作成する際のリリースキーは、投入予定リリースのキーを使用する。
分からない場合は `999999999`（開発・テスト用）を使用し、後で正式なリリースキーに変更する。

```
# 最新リリースキーの確認
duckdb -c "SELECT DISTINCT release_key FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE) ORDER BY release_key DESC LIMIT 10;"
```

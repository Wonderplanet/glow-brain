# インゲームマスタデータ検証レポート

- 対象: `dungeon_kai_normal_00001` (dungeon_normal)
- 検証日時: 2026-03-02
- 検証ディレクトリ: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/kai/normal/generated/`

---

## 判定: ❌ 問題があります（修正が必要です）

MstInGame.csv に CRITICAL 問題が2件あります。修正後に再検証してください。

---

## フェーズ別結果サマリ

| フェーズ | 結果 | 備考 |
|---------|------|------|
| A: フォーマット | ❌ CRITICAL | MstInGame.csv カラム数不一致（21列 vs 期待22列 / データ列ずれ） |
| B: ID整合性 | ✅ OK | 全FK参照一致 |
| C: ゲームプレイ品質 | ⚠️ WARNING | boss_count=1（dungeon_normalはボスなし、0または空欄が正常） |
| D: バランス比較 | ✅ OK | 既存Normal/Defense系と同等範囲 |
| E: アセットキー | ✅ OK | 必須キーすべて設定済み |

---

## Phase A: フォーマット検証

### ✅ OK ファイル（5件）

| ファイル | 結果 |
|---------|------|
| MstAutoPlayerSequence.csv | ✅ PASS（6行） |
| MstEnemyOutpost.csv | ✅ PASS（4行） |
| MstEnemyStageParameter.csv | ✅ PASS（4行） |
| MstKomaLine.csv | ✅ PASS（6行） |
| MstPage.csv | ✅ PASS（4行） |

### ❌ CRITICAL: MstInGame.csv カラム数不一致 + データ列ずれ

`validate_all.py` の DBスキーマ検証で以下を検出:
- **期待カラム数**: 19（DBスキーマ定義）
- **実際のカラム数**: 21（ヘッダー22列 - ENABLE列1 = 21）

#### 根本原因

MstInGame.csv のヘッダー行（ENABLE行）に `result_tips.ja` と `description.ja`（MstInGameI18n用列）が付加されているが、**データ行（データ列）が1列少ない**（21列に対してデータは21値だが、カラム割り当てが正しくない）。

具体的には、データ行の各値がヘッダーに対してずれており：

| カラム名 | ヘッダー位置 | 実際の値 | 正しい値 |
|---------|------------|---------|---------|
| `boss_enemy_speed_coef` | [18] | `202604010` | `1` |
| `release_key` | [19] | `無属性の怪獣余獣が出現する…` | `202604010` |
| `result_tips.ja` | [20] | `怪獣討伐部隊として余獣の群れに…` | `無属性の怪獣余獣が出現する…` |
| `description.ja` | [21] | （欠落） | `怪獣討伐部隊として余獣の群れに…` |

#### 修正提案

データ行に `boss_enemy_speed_coef` の値（`1`）が抜けている。以下のように修正してください:

**修正前（データ行）:**
```
e,dungeon_kai_normal_00001,,dungeon_kai_normal_00001,SSE_SBG_003_001,,kai_00001,,dungeon_kai_normal_00001,dungeon_kai_normal_00001,,,1,1,1,1,1,1,202604010,無属性の怪獣余獣が出現する。どの属性のキャラでも戦いやすいが、素早く撃破してHP消耗を抑えよう。,怪獣討伐部隊として余獣の群れに立ち向かえ！無属性の怪獣が次々と押し寄せてくる。
```

**修正後（データ行）:**
```
e,dungeon_kai_normal_00001,,dungeon_kai_normal_00001,SSE_SBG_003_001,,kai_00001,,dungeon_kai_normal_00001,dungeon_kai_normal_00001,,,1,1,1,1,1,1,1,202604010,無属性の怪獣余獣が出現する。どの属性のキャラでも戦いやすいが、素早く撃破してHP消耗を抑えよう。,怪獣討伐部隊として余獣の群れに立ち向かえ！無属性の怪獣が次々と押し寄せてくる。
```

追加箇所: `boss_enemy_attack_coef`（`1`）の後に `boss_enemy_speed_coef`（`1`）を挿入。

---

## Phase B: ID整合性チェック

すべてのFK参照が正常です。

| チェック項目 | 結果 | 詳細 |
|------------|------|------|
| MstInGame → MstAutoPlayerSequence (sequence_set_id) | ✅ OK | `dungeon_kai_normal_00001` で一致 |
| MstInGame → MstPage (mst_page_id) | ✅ OK | `dungeon_kai_normal_00001` で一致 |
| MstInGame → MstEnemyOutpost (mst_enemy_outpost_id) | ✅ OK | `dungeon_kai_normal_00001` で一致 |
| MstInGame → MstEnemyStageParameter (boss_fk) | ✅ OK | 空欄（dungeon_normalはボスなし） |
| MstAutoPlayerSequence.sequence_set_id 一貫性 | ✅ OK | 全3行で `dungeon_kai_normal_00001` に統一 |
| SummonEnemy action_value → MstEnemyStageParameter.id | ✅ OK | `e_kai_00101_general_Normal_Colorless` が存在 |
| MstKomaLine → MstPage (mst_page_id) | ✅ OK | 全3行で `dungeon_kai_normal_00001` に一致 |

---

## Phase C: ゲームプレイ品質チェック

### ✅ dungeon_normal 仕様チェック

| チェック項目 | 期待値 | 実際値 | 結果 |
|------------|--------|--------|------|
| MstEnemyOutpost.hp | 100（固定） | 100 | ✅ OK |
| MstKomaLine コマ行数 | 3行（固定） | 3行 | ✅ OK |
| MstKomaLine row=1 コマ幅合計 | 1.0 | 1.0 | ✅ OK |
| MstKomaLine row=2 コマ幅合計 | 1.0 | 1.0 | ✅ OK |
| MstKomaLine row=3 コマ幅合計 | 1.0 | 1.0 | ✅ OK |
| ElapsedTime 単調増加 | 逆行なし | 500 → 2000 → 4000 | ✅ OK |

### ⚠️ WARNING: MstInGame.boss_count

- **現在値**: `1`
- **期待値**: `0` または空欄（dungeon_normalはボスを持たない）
- **参考**: SPY dungeon_normal の `boss_count = '0'`
- **確認事項**: dungeon_normal において boss_count=1 は設計意図的なものか確認が必要。ボス設定なし（boss_mst_enemy_stage_parameter_id=空欄）と矛盾するため、`0` への修正を推奨。

### MstAutoPlayerSequence サマリ

| action_type | 件数 |
|------------|------|
| SummonEnemy | 3件 |

召喚タイミング: 500フレーム、2000フレーム、4000フレーム（単調増加で正常）

---

## Phase D: バランス比較

### MstEnemyStageParameter 生成値

| パラメータ | 生成値 |
|----------|--------|
| id | `e_kai_00101_general_Normal_Colorless` |
| character_unit_kind | Normal |
| role_type | Defense |
| color | Colorless |
| hp | 25,000 |
| attack_power | 350 |
| move_speed | 45 |
| well_distance | 0.11 |

### 既存データとの比較（Normal/Defense、132件）

| パラメータ | 生成値 | 既存Min | 既存中央値 | 既存Max | 判定 |
|----------|--------|---------|----------|---------|------|
| hp | 25,000 | 1,000 | 10,000 | 600,000 | ✅ OK（±5倍範囲内） |
| attack_power | 350 | 50 | 100 | 1,200 | ✅ OK（±5倍範囲内） |
| move_speed | 45 | 10 | 34 | 65 | ✅ OK（±5倍範囲内） |

### エネミーステータスシート基準値との比較（Lv25-30 Normal/Defense）

| パラメータ | 基準範囲 | 生成値 | 判定 |
|----------|---------|--------|------|
| HP | 22,400〜26,600 | 25,000 | ✅ 範囲内 |
| ATK | 280〜350 | 350 | ✅ 範囲内 |

---

## Phase E: アセットキーチェック

| テーブル | カラム | 値 | 判定 |
|---------|------|----|------|
| MstInGame | bgm_asset_key | `SSE_SBG_003_001` | ✅ OK |
| MstInGame | boss_bgm_asset_key | （空欄） | ✅ OK（dungeon_normalはボスBGMなし） |
| MstInGame | loop_background_asset_key | `kai_00001` | ✅ OK |
| MstInGame | player_outpost_asset_key | （空欄） | ✅ OK（未設定は許容） |
| MstEnemyOutpost | artwork_asset_key | `kai_00001` | ✅ OK |
| MstEnemyOutpost | outpost_asset_key | `kai_00001` | ✅ OK |
| MstKomaLine (row=1) | koma1_asset_key | `kai_00001` | ✅ OK |
| MstKomaLine (row=2) | koma1_asset_key | `kai_00001` | ✅ OK |
| MstKomaLine (row=3) | koma1_asset_key | `kai_00001` | ✅ OK |

---

## 必要な修正まとめ

### [CRITICAL] MstInGame.csv データ行のカラムずれ

- **問題**: データ行の `boss_enemy_speed_coef`（値: `1`）が欠落し、それ以降のカラム値がずれている
- **影響**: `boss_enemy_speed_coef` が `202604010`（release_keyの値）になっており、`release_key` に日本語テキストが入る → インポートエラーまたは不正データとなる
- **修正**: データ行の `boss_enemy_attack_coef=1` の後に `boss_enemy_speed_coef=1` を追加

### [WARNING] MstInGame.boss_count

- **問題**: `boss_count = 1` だが dungeon_normal はボスなし
- **推奨修正**: `boss_count = 0`（SPYの dungeon_normal 参考値に合わせる）

---

## 修正後の再検証手順

```
/masterdata-ingame-verifier
```

対象: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/kai/normal/generated/`

# インゲームコンテンツ種別ごとの設定パターン

このドキュメントは `projects/glow-masterdata/` の実データ（MstInGame.csv / MstAutoPlayerSequence.csv / MstEnemyStageParameter.csv）を調査して作成したリファレンスです。

---

## コンテンツ種別一覧

MstInGame の `id` プレフィックスから確認されるコンテンツ種別:

| 種別 | ID数 | IDプレフィックス例 | 用途 |
|------|----:|-------|------|
| **event** | 264 | `event_kai1_charaget01_00001` | イベント系（期間限定）バトル |
| **normal** | 78 | `normal_spy_00001` | 通常難度ステージ |
| **hard** | 78 | `hard_spy_00001` | ハード難度ステージ |
| **veryhard** | 78 | `veryhard_spy_00001` | ベリーハード難度ステージ |
| **pvp** | 21 | `pvp_spy_01` | PVPコンテンツ |
| **raid** | 13 | `raid_kai_00001` | レイド系ボスバトル（複数フェーズ） |
| **tutorial** | 3 | `tutorial_1` | チュートリアル |
| **plan** | 18 | `plan_test_stage001` | 開発・テスト用 |
| **develop** | 4 | `develop_001` | 開発専用 |

---

## コンテンツ種別ごとの特徴

### event（イベント系・最多ボリューム）

- **規模**: 最多（264件）。キャラクターコラボや季節イベント等で頻繁に追加される
- **MstInGame設定**: `boss_mst_enemy_stage_parameter_id` は全件設定あり
- **MstAutoPlayerSequence**:
  - `aura_type`: `Default`（雑魚）+ `Boss`（通常ボス）の2パターンのみ
  - `action_type`: `SummonEnemy` 中心。ギミック（`SummonGimmickObject` / `TransformGimmickObjectToEnemy`）も使用
  - `SwitchSequenceGroup` を29件使用（複数フェーズ管理にも対応）
- **難易度パラメータ傾向**（MstAutoPlayerSequence係数の平均）:
  - HP係数: 約12.6x
  - 攻撃係数: 約3.2x
  - 速度係数: 1.0x（固定）
- **質問すべきこと**: イベントキャラID・ギミックの有無・フェーズ構成

---

### normal / hard / veryhard（通常難度3段階）

- **規模**: 各78件（合計234件）で難度別に3段階を管理
- **MstInGame設定**: `boss_mst_enemy_stage_parameter_id` は全件設定あり
- **MstAutoPlayerSequence**:
  - `aura_type`: `Default` + `Boss` のみ
  - `action_type`: `SummonEnemy` 中心だが `SwitchSequenceGroup`・ギミックも使用
- **難易度パラメータ傾向**（HP係数の平均）:

  | 種別 | HP係数 | 攻撃係数 | 速度係数 |
  |------|-------:|--------:|--------:|
  | normal | 8.3x | 2.2x | 1.0x |
  | hard | 22.7x | 7.8x | 1.0x |
  | veryhard | 17.5x | 5.6x | 1.0x |

  > ※ veryhard < hard という逆転現象が観測されているが、これは実データ平均値であり、個々のステージは異なる可能性がある
- **質問すべきこと**: 難度を表す3段階をすべて作るのか、特定の難度のみか

---

### raid（レイド系・複数フェーズ対応）

- **規模**: 13件と少数だが、1件あたりのシーケンス量が多い
- **MstInGame設定**: `boss_mst_enemy_stage_parameter_id` は全件設定あり
- **MstAutoPlayerSequence の最大の特徴**:
  - `aura_type` に `AdventBoss1` / `AdventBoss2` / `AdventBoss3` を多用（合計100件）
  - `SwitchSequenceGroup` を90件使用 → 複数フェーズを `SwitchSequenceGroup` で切り替える設計
  - フェーズ管理パターン: 「グループAを消費 → SwitchSequenceGroup でグループBに切り替え → ...」
- **難易度パラメータ傾向**:
  - HP係数: 約48.8x（高耐久・長期戦設計）
  - 攻撃係数: 約6.0x
- **質問すべきこと**: フェーズ数・各フェーズのAdventBoss段階（1〜3）・フェーズ切り替えのトリガー条件

---

### pvp（PVPコンテンツ）

- **規模**: 21件
- **MstInGame設定**:
  - `mst_enemy_outpost_id` は全21件で同じIDを共有（1種類のアウトポストを全PVPで使用）
  - `boss_mst_enemy_stage_parameter_id` は全件設定あり
- **MstAutoPlayerSequence**: 詳細データは少ないが基本的な `SummonEnemy` + `Boss` 構成と思われる
- **質問すべきこと**: PVP特有のIDルール・共通アウトポストIDの確認

---

### tutorial（チュートリアル）

- **規模**: 3件のみ
- **MstAutoPlayerSequence の特徴**:
  - `aura_type`: `Default` + `Boss` のみ（最もシンプル）
  - `action_type`: `SummonEnemy` のみ（ギミックなし）
  - 速度係数: 1.24x（わずかに速い）
- **難易度パラメータ傾向**: HP係数 3.5x・攻撃係数 1.5x（全種別中最も低い）
- **質問すべきこと**: チュートリアルステップ番号・スキップ可能かどうか

---

## aura_type 解説

| aura_type | 用途 | 使用コンテンツ |
|-----------|------|--------------|
| `Default` | 通常の雑魚敵 | 全コンテンツ |
| `Boss` | 通常ボス（単一フェーズ） | event / normal / hard / veryhard / raid / tutorial |
| `AdventBoss1` | アドベント型ボス（第1フェーズ） | raid（一部 veryhard） |
| `AdventBoss2` | アドベント型ボス（第2フェーズ） | raid |
| `AdventBoss3` | アドベント型ボス（第3フェーズ） | raid |

**AdventBoss の特徴**:
- 複数フェーズで形態変化するボス
- `SwitchSequenceGroup` と組み合わせてフェーズ遷移を管理
- Raid系でほぼ専用使用

---

## action_type 解説

| action_type | 用途 | 頻度 |
|-------------|------|------|
| `SummonEnemy` | 敵ユニット召喚（基本アクション） | 全コンテンツで使用（4,375件） |
| `SwitchSequenceGroup` | シーケンスグループ切り替え（フェーズ管理） | 主にRaid（173件） |
| `TransformGimmickObjectToEnemy` | ギミックオブジェクトを敵に変形 | Event / Normal系（41件） |
| `SummonGimmickObject` | ギミックオブジェクト召喚 | Event / Normal系（27件） |

---

## コンテンツ種別を判断するための質問（Step 0 への追加項目）

以下をStep 0で確認する:

```
Q: 作成するインゲームのコンテンツ種別は何ですか？
  → event / normal / hard / veryhard / raid / pvp / tutorial / その他

（raidの場合）Q: フェーズは何段階ありますか？各フェーズでのボス形態（AdventBoss1/2/3）は？

（raidの場合）Q: フェーズ切り替えのトリガーは何ですか？（ボスHP〇%等）
```

---

## 既存データ参照クエリ

コンテンツ種別ごとの既存データを参照する:

```bash
# 特定コンテンツ種別の MstInGame を確認
duckdb -c "SELECT id, boss_mst_enemy_stage_parameter_id, mst_page_id, mst_enemy_outpost_id FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') WHERE id LIKE 'event_%' LIMIT 5;"

# aura_type の分布確認
duckdb -c "SELECT DISTINCT aura_type, count(*) FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') WHERE sequence_set_id LIKE 'raid_%' GROUP BY 1;"

# action_type の分布確認
duckdb -c "SELECT DISTINCT action_type, count(*) FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') WHERE sequence_set_id LIKE 'raid_%' GROUP BY 1;"
```

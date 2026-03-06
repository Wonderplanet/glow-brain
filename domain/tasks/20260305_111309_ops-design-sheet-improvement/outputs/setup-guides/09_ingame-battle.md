# インゲーム・バトル設定 マスタデータ設定手順書

## 概要

インゲームバトルステージの設定手順書。敵配置・AI シーケンス・ページ構成といった複雑な設定を管理する。本手順書は **「masterdata-ingame-creator スキルへの入力準備チェックリスト型」** であり、詳細設定は既存ドキュメントへ誘導する方針とする。

- **report.md 対応セクション**: 各ステージの `mst_in_game_id` 一覧

> **推奨**: インゲームデータは `masterdata-ingame-creator` スキルを直接実行することで生成する。本手順書は入力情報の準備と確認に使用する。

---

## 対象テーブル一覧と設定順序

| 作業順 | テーブル名 | 役割 | 詳細ドキュメント |
|-------|-----------|------|---------------|
| 1 | MstInGame | インゲームステージ定義 | `table-docs/MstInGame.md` |
| 2 | MstInGameI18n | インゲーム多言語名 | — |
| 3 | MstAutoPlayerSequence | AI 敵出現シーケンス | `table-docs/MstAutoPlayerSequence.md` |
| 4 | MstEnemyStageParameter | 敵パラメータ定義 | `table-docs/MstEnemyStageParameter.md` |
| 5 | MstEnemyOutpost | 前哨戦（アウトポスト）定義 | `table-docs/MstEnemyOutpost.md` |
| 6 | MstEnemyCharacter | 敵キャラクター定義 | — |
| 7 | MstEnemyCharacterI18n | 敵キャラクター多言語名 | — |
| 8 | MstPage | ステージページ構成 | `table-docs/MstPage.md` |
| 9 | MstKomaLine | コマライン（ステージ横幅）設定 | `table-docs/MstKomaLine.md` |

---

## 前提条件・依存関係

- **MstSeries / MstUnit の登録完了が前提**（`01_event.md`, `02_unit.md`）
- インゲームデータは MstStage から参照される（`04_quest-stage.md` より後でも作成可能だが、MstStage.mst_in_game_id を設定するには先に必要）
- MstAdventBattle.mst_in_game_id も参照するため、降臨バトル設定（`06_advent-battle.md`）の前に完了が必要

---

## masterdata-ingame-creator スキルへの入力準備チェックリスト

スキル実行前に以下の情報を収集・整理する。

### ステージ基本情報

- [ ] MstInGame.id 一覧（通常: `{stage_id}` と同名）
- [ ] BGM アセットキー（`bgm_asset_key`）
- [ ] ステージ種別（デイリー/ストーリー/チャレンジ/高難易度/降臨/限界）

### 敵設定

- [ ] 敵キャラクター一覧と属性・HP・攻撃力
- [ ] 敵出現パターン（前半/後半/ボス戦の構成）
- [ ] ボス MstEnemyStageParameter.id

### ページ・コマライン設定

- [ ] ステージのページ数（通常 2〜4 ページ）
- [ ] 各ページの横幅（komaLine 数）
- [ ] アウトポスト（MstEnemyOutpost）の有無

---

## スキル実行方法

```
/masterdata-ingame-creator
```

スキル起動後に以下を入力する：
1. インゲーム ID（例: `event_you1_1day_00001`）
2. ステージ種別とレベル帯
3. 敵構成の説明（report.md の記載内容）

---

## MstInGame の主要カラム確認

**過去データ参照クエリ（masterdata-explorer）**

```duckdb
SELECT id, mst_auto_player_sequence_id, mst_page_id, mst_enemy_outpost_id,
       boss_mst_enemy_stage_parameter_id, bgm_asset_key, release_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstInGame.csv')
LIMIT 5;
```

**MstInGame カラム一覧**

| カラム名 | 役割 |
|---------|------|
| id | インゲームステージ ID（MstStage.mst_in_game_id で参照） |
| mst_auto_player_sequence_id | AI シーケンス ID |
| mst_auto_player_sequence_set_id | AI シーケンスセット ID |
| bgm_asset_key | BGM アセットキー |
| mst_page_id | ページ ID |
| mst_enemy_outpost_id | アウトポスト ID |
| boss_mst_enemy_stage_parameter_id | ボス敵 ID（NULL=ボスなし） |
| normal_enemy_hp_coef | 通常敵 HP 倍率（基本 1） |
| normal_enemy_attack_coef | 通常敵攻撃倍率（基本 1） |
| boss_enemy_hp_coef | ボス敵 HP 倍率（基本 1） |

---

## 既存ドキュメント参照先

詳細な設定方法は以下のドキュメントを参照する。

| テーブル | ドキュメントパス |
|---------|---------------|
| MstInGame | `domain/knowledge/masterdata/table-docs/MstInGame.md` |
| MstAutoPlayerSequence | `domain/knowledge/masterdata/table-docs/MstAutoPlayerSequence.md` |
| MstEnemyStageParameter | `domain/knowledge/masterdata/table-docs/MstEnemyStageParameter.md` |
| MstEnemyOutpost | `domain/knowledge/masterdata/table-docs/MstEnemyOutpost.md` |
| MstPage | `domain/knowledge/masterdata/table-docs/MstPage.md` |
| MstKomaLine | `domain/knowledge/masterdata/table-docs/MstKomaLine.md` |
| 実装例 34 件 | `domain/knowledge/masterdata/in-game/guides/` |

---

## 検証方法

- masterdata-ingame-verifier スキルを使用
  ```
  /masterdata-ingame-verifier
  ```
- MstStage.mst_in_game_id → MstInGame.id が存在するか
- MstAdventBattle.mst_in_game_id → MstInGame.id が存在するか

---

## 参照リソース

- 利用スキル: `masterdata-ingame-creator`（生成）, `masterdata-ingame-verifier`（検証）
- 既存ドキュメント: `domain/knowledge/masterdata/table-docs/`
- 実装ガイド: `domain/knowledge/masterdata/in-game/guides/`
- 過去リリース: `domain/raw-data/masterdata/released/202602015/tables/MstInGame.csv`

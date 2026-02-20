# テーブル生成順序と依存関係

インゲームマスタデータを生成する際の、テーブル依存関係に基づいた正しい生成順序。

---

## テーブル依存関係マトリクス

| テーブル | 参照するテーブル | 参照されるテーブル |
|---------|--------------|----------------|
| `MstEnemyCharacter` | MstSeries | MstEnemyStageParameter |
| `MstEnemyStageParameter` | MstEnemyCharacter, MstUnitAbility | MstAutoPlayerSequence(action_value), MstInGame(boss_id) |
| `MstEnemyOutpost` | なし | MstInGame |
| `MstPage` | なし | MstKomaLine, MstInGame |
| `MstKomaLine` | MstPage | なし |
| `MstAutoPlayerSequence` | MstEnemyStageParameter, MstInGameGimmickObject | MstInGame |
| `MstInGame` | MstAutoPlayerSequence, MstPage, MstEnemyOutpost, MstEnemyStageParameter(boss) | MstStage |
| `MstStage` | MstInGame, MstQuest | MstStageEventSetting, MstStageEventReward, MstStageClearTimeReward |
| `MstStageEventSetting` | MstStage | なし |
| `MstStageEventReward` | MstStage | なし |
| `MstStageClearTimeReward` | MstStage | なし |
| `MstInGameSpecialRule` | MstStage（target_id） | なし |
| `MstInGameI18n` | MstInGame | なし |

---

## 生成順序（14ステップ）

### フェーズ1: 基礎データ（他に依存しない）

**Step 1: MstEnemyStageParameter**

最も重要な基礎データ。MstAutoPlayerSequenceとMstInGameが参照する。

```
生成ポイント:
- ボスと雑魚を分けて作成
- id命名: {c_/e_}_{キャラ略称}_{インゲームID短縮}_{UnitKind}_{Color}
- 変身ありの場合は変身前・変身後を同一CSVに含める
```

**Step 2: MstEnemyOutpost**

敵砦のHP・アセット設定。MstInGameが参照する。

```
生成ポイント:
- id = MstInGame.id と同じ値にする
- HPは種別ごとの目安に従う（stage-type-patterns.md参照）
- レイドはis_damage_invalidation=1 + HP=1000000
```

**Step 3: MstPage**

バトルフィールドのページID。MstKomaLineとMstInGameが参照する。

```
生成ポイント:
- id = MstInGame.id と同じ値にする
- カラムはENABLE, id, release_key のみ
```

---

### フェーズ2: フェーズ1に依存するデータ

**Step 4: MstKomaLine**

MstPageに紐づくコマライン。MstPage.idを参照する。

```
生成ポイント:
- id = {mst_page_id}_{row番号}
- コマ幅の合計が1.0になること
- 通常は2行（row=1, row=2）
```

**Step 5: MstAutoPlayerSequence**

敵出現シーケンス。MstEnemyStageParameter.idを参照する。

```
生成ポイント:
- sequence_set_id = MstInGame.id（これから作る）
- action_value の敵IDが MstEnemyStageParameter に存在することを確認
- グループ切り替えがある場合は sequence_element_id を groupchange_N 形式にする
```

---

### フェーズ3: フェーズ1・2に依存するデータ

**Step 6: MstInGame**

インゲーム全体設定。フェーズ1・2の全テーブルを参照する。

```
生成ポイント:
- id は Step 2,3,5 の値と一致させる
- mst_auto_player_sequence_set_id = sequence_set_id（Step 5）
- mst_page_id = Step 3 の id
- mst_enemy_outpost_id = Step 2 の id
- boss_mst_enemy_stage_parameter_id = Step 1 のボスID
```

---

### フェーズ4: MstInGameに依存するデータ

**Step 7: MstStage**

ステージ設定。MstInGame.idを参照する。

```
生成ポイント:
- mst_in_game_id = MstInGame.id（Step 6）
- mst_quest_id は既存クエストIDか新規作成が必要か確認
- auto_lap_type = AfterClear（多くの場合）
```

---

### フェーズ5: MstStageに依存するデータ

**Step 8: MstStageEventSetting**

ステージのイベント設定。MstStage.idを参照する。

```
生成ポイント:
- mst_stage_id = MstStage.id（Step 7）
- start_at / end_at はイベント期間と合わせる
- clearable_count は 1日1回制限の場合 1
```

**Step 9: MstStageEventReward**

ステージの報酬設定。MstStage.idを参照する。

```
生成ポイント:
- mst_stage_id = MstStage.id（Step 7）
- 初回クリア報酬（FirstClear）を必ず設定
- ランダムドロップ（Random）は任意
```

---

### フェーズ6: オプションデータ（必要な場合のみ）

**Step 10: MstStageClearTimeReward**（challenge/savage系）

クリアタイム報酬。チャレンジ・サベージ系のみ使用。

**Step 11: MstInGameSpecialRule**（challenge/savage系）

特別ルール。SpeedAttack/NoContinue等。

**Step 12: MstInGameI18n**（任意）

説明文・ティップス。バトル前画面や結果画面に表示するテキスト。

---

## FK参照チェックリスト

CSV生成後に以下を確認する:

```
□ MstAutoPlayerSequence.action_value（SummonEnemy時）
    → MstEnemyStageParameter.id が存在するか

□ MstInGame.boss_mst_enemy_stage_parameter_id
    → MstEnemyStageParameter.id が存在するか

□ MstInGame.mst_auto_player_sequence_set_id
    → MstAutoPlayerSequence.sequence_set_id が存在するか

□ MstInGame.mst_page_id
    → MstPage.id が存在するか

□ MstInGame.mst_enemy_outpost_id
    → MstEnemyOutpost.id が存在するか

□ MstStage.mst_in_game_id
    → MstInGame.id が存在するか

□ MstStageEventSetting.mst_stage_id
    → MstStage.id が存在するか

□ MstStageEventReward.mst_stage_id
    → MstStage.id が存在するか

□ MstKomaLine.mst_page_id
    → MstPage.id が存在するか
```

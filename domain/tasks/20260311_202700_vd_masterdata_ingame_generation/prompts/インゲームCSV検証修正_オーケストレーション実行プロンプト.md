# VDインゲームマスタデータ CSV検証・修正 - オーケストレーション実行プロンプト

## 目的

全32ブロックの生成済みCSVデータを並列で検証・修正する。

- **検証・修正手段**: `vd-masterdata-ingame-data-creator` スキルを使い、SQLiteを介してCSVを再生成する
  - スキル実行時にCHECK制約でenum値検証・リレーション整合性チェックが自動で走る
- **修正方針**: スキルのフローに従って問題を検出し、SQLite経由でCSVを生成し直す（design.mdの修正は対象外）

---

## 対象ブロック一覧（全32ブロック）

| 作品 | 作品ID | Normalブロック | Bossブロック |
|------|--------|--------------|------------|
| SPY×FAMILY | `spy` | vd_spy_normal_00001 | vd_spy_boss_00001 |
| ダンダダン | `dan` | vd_dan_normal_00001 | vd_dan_boss_00001 |
| 姫様"拷問"の時間です | `gom` | vd_gom_normal_00001 | vd_gom_boss_00001 |
| チェンソーマン | `chi` | vd_chi_normal_00001 | vd_chi_boss_00001 |
| 株式会社マジルミエ | `mag` | vd_mag_normal_00001 | vd_mag_boss_00001 |
| 怪獣８号 | `kai` | vd_kai_normal_00001 | vd_kai_boss_00001 |
| 2.5次元の誘惑 | `yuw` | vd_yuw_normal_00001 | vd_yuw_boss_00001 |
| 魔都精兵のスレイブ | `sur` | vd_sur_normal_00001 | vd_sur_boss_00001 |
| サマータイムレンダ | `sum` | vd_sum_normal_00001 | vd_sum_boss_00001 |
| 地獄楽 | `jig` | vd_jig_normal_00001 | vd_jig_boss_00001 |
| タコピーの原罪 | `tak` | vd_tak_normal_00001 | vd_tak_boss_00001 |
| 【推しの子】 | `osh` | vd_osh_normal_00001 | vd_osh_boss_00001 |
| 幼稚園WARS | `you` | vd_you_normal_00001 | vd_you_boss_00001 |
| 100カノ | `kim` | vd_kim_normal_00001 | vd_kim_boss_00001 |
| ふつうの軽音部 | `hut` | vd_hut_normal_00001 | vd_hut_boss_00001 |
| あやかしトライアングル | `aya` | vd_aya_normal_00001 | vd_aya_boss_00001 |

---

## 実行フロー

```
メインセッション（オーケストレーター）
│
├── [Phase 1] 全ブロック検証・修正（3ブロック × サブエージェント並列起動 × 繰り返し）
│   └── 各サブエージェントがスキルを実行し DONE / MANUAL を報告
│
└── [Phase 2] スキルで対応不可だったブロックの確認・追加修正
    └── MANUAL が残っている場合のみ実行
```

---

## Phase 1: 全ブロック検証・修正

### 手順

1. **サブエージェント並列起動（3ブロックずつ）**

   全32ブロックを3つずつグループ化し、**1メッセージで3つのTask toolを並列起動**する。
   全サブエージェントの完了を待ち、次のグループへ進む。

   **各サブエージェントへの指示**:

   ```
   以下のブロックのVDインゲームマスタデータCSVを検証・修正してください。

   ブロックID: {ブロックID}

   作業内容:
   /vd-masterdata-ingame-data-creator ブロックID={ブロックID}

   スキルのフローに従って実行してください。
   スキルがSQLiteを介してCSVの検証と再生成を行います。
   ※ design.md の修正は行わないこと

   完了したら以下の形式で出力してください:
   - 正常完了（問題なし or 修正済み）: "DONE: {ブロックID}"
   - スキルで対応不可: "MANUAL: {ブロックID} - {要対応内容の詳細}"

   作業ディレクトリ:
   /Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-shuna
   ```

   **Task toolパラメータ**:
   - `subagent_type`: `general-purpose`
   - 3ブロック分を同一メッセージ内で並列起動（依存関係なし）

2. **全ブロックの結果を集約**

   各ラウンド完了後に結果をメモし、全32ブロックの DONE / MANUAL を一覧化する。

---

## Phase 2: スキルで対応不可だったブロックの追加修正

Phase 1 で `MANUAL` が出たブロックについて、問題内容を確認して個別対応する。

1. MANUAL ブロックの問題内容を精査する
2. 問題の原因が design.md の記述にある場合はユーザーに報告して対応方針を確認する
3. ユーザーの指示に従って修正後、再度スキルを実行して DONE を確認する

---

## スキルが検証する主な内容（参考）

`vd-masterdata-ingame-data-creator` スキルは以下を自動検証する:

- **enum値・CHECK制約**: SQLite の CHECK 制約により不正な値を検出
- **テーブル間リレーション整合性**:
  - `MstAutoPlayerSequence.sequence_set_id` = `MstInGame.id`
  - `MstInGame.mst_page_id` = `MstKomaLine.mst_page_id`
  - `SummonEnemy` の `action_value` → `MstEnemyStageParameter.id` に存在すること
- **VD固有の設定値**:
  - `MstInGame.content_type` = `Dungeon`
  - `MstInGame.stage_type` = `vd_normal` または `vd_boss`
  - `MstEnemyOutpost.hp` = `100`

---

## 検証チェックリスト

### Phase 1 完了確認

```
全32ブロックが DONE になっているか確認
MANUAL が残っている場合は Phase 2 へ
```

### Phase 2 完了確認

```
全ての MANUAL ブロックが解消されているか確認
```

---

## 作業量見積もり

| フェーズ | 対象ブロック数 | ラウンド数（3並列） |
|---------|------------|-----------------|
| Phase 1: 検証・修正 | 32ブロック | 約11ラウンド |
| Phase 2: 手動対応 | 問題発生分のみ | 問題数による |

---

## 注意事項

- サブエージェントは `general-purpose` タイプを使用する
- 各ラウンドはサブエージェント全員の完了を待ってから次に進む
- `MANUAL` が多数発生した場合、共通の問題パターンを分析してからユーザーに一括報告する

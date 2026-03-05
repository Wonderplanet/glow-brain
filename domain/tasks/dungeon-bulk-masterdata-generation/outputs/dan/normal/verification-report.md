# インゲームマスタデータ検証レポート

- 対象: `dungeon_dan_normal_00001` (dungeon_normal)
- 検証日時: 2026-03-02
- 生成ディレクトリ: `domain/tasks/dungeon-bulk-masterdata-generation/outputs/dan/normal/generated/`

---

## 判定サマリー

### 合格（ゲームプレイに支障なし）、一部 WARNING あり（確認推奨）

| フェーズ | 結果 | 備考 |
|---------|------|------|
| A: フォーマット | WARNING | ヘッダー形式は既存CSVと同一。MstInGame.csvに追加カラムあり（後述） |
| B: ID整合性 | OK | 全FK参照一致（6項目すべて PASS） |
| C-1: 敵パラメータ妥当性 | OK | HP/ATK/Speed ともに汎用許容範囲内 |
| C-2: コマ配置整合性 | OK | 3行すべて幅合計 1.000、行数 = 3（仕様通り） |
| C-3: シーケンス合理性 | OK | ElapsedTime 単調増加、召喚総数 11体（目安 10〜30 の範囲内） |
| C-4: ステージ種別固有ルール | OK | MstEnemyOutpost.hp = 100、コマ行数 = 3（dungeon_normal 固定値一致） |
| C-5: ボス設定 | OK | boss_mst_enemy_stage_parameter_id = NULL、boss_count = 0（ボスなし・通常ブロック仕様通り） |
| D: バランス比較 | WARNING | HP・ATK が既存平均の 0.2 倍未満（後述） |
| E: アセットキー | WARNING | MstEnemyOutpost.outpost_asset_key が NULL（後述） |

---

## 検証詳細

### A: フォーマット検証

#### A-1: ヘッダー構造
validate_all.py は「memo行, TABLE行, ENABLE行, データ行」の4行形式を期待するが、生成CSVおよび既存の実CSV（`projects/glow-masterdata/MstInGame.csv` 等）はいずれも「ENABLE行, データ行」の2行形式で統一されている。スクリプトの誤検知であり、実運用上の問題はない。

#### A-2: カラム構成比較

| テーブル | 既存CSV との一致 | 備考 |
|---------|----------------|------|
| MstEnemyStageParameter | 完全一致 | |
| MstEnemyOutpost | 完全一致 | |
| MstPage | 完全一致 | |
| MstKomaLine | 完全一致 | |
| MstAutoPlayerSequence | 完全一致 | |
| MstInGame | **追加カラムあり** | `result_tips.ja`, `description.ja` の 2 列が追加（後述） |

#### A-3: MstInGame.csv の追加カラムについて

生成CSVには既存の実CSV（projects/glow-masterdata/MstInGame.csv）にない `result_tips.ja` と `description.ja` が存在する。

- これらは `projects/glow-masterdata/sheet_schema/MstInGame.csv` に定義されており（TABLE行: `MstInGameI18n`）、スキーマ上は正当な列である
- `description.ja` の値: 「赤属性の敵が登場するので青属性のキャラが有利に戦うことができるぞ！防御型と高速型の2種が入り混じる攻勢に備えよ！」
- `result_tips.ja` の値: NULL（未設定）
- 既存の実CSVでこれらの列が含まれていない理由は不明。XLSXへの投入時に問題が発生する可能性があるため、投入前に確認を推奨

**推奨アクション**: XLSX変換・投入時に `result_tips.ja` と `description.ja` カラムの扱いを確認する。不要であれば当該列を削除、またはMstInGameI18nに分離して投入する。

---

### B: ID整合性チェック

verify_id_integrity.py の実行結果（全項目 PASS）:

| チェック項目 | 結果 |
|-----------|------|
| ingame_sequence_fk | PASS |
| ingame_page_fk | PASS |
| ingame_outpost_fk | PASS |
| ingame_boss_fk | PASS |
| sequence_set_id_consistency | PASS |
| sequence_action_value_fk | PASS |

---

### C: ゲームプレイ品質チェック

#### C-1: 敵パラメータ（MstEnemyStageParameter）

| ID | role_type | HP | ATK | 移動速度 | 汎用許容範囲 |
|----|-----------|-----|-----|---------|-----------|
| e_dan_00001_general_n_Normal_Red | Defense | 10,000 | 50 | 34 | HP: 1,000〜100,000 OK / ATK: 21〜2,380 OK |
| e_dan_00101_general_n_Normal_Red | Attack | 10,000 | 50 | 47 | HP: 1,000〜100,000 OK / ATK: 21〜2,380 OK |

エネミーステータスシート（Normal種HP中央値 10,000）との比較でも適切な値。

#### C-2: コマ配置整合性（MstKomaLine）

| 行（row） | コマ幅合計 | 仕様 |
|----------|----------|------|
| 1 | 1.000（0.6 + 0.4） | OK |
| 2 | 1.000（0.25 + 0.5 + 0.25） | OK |
| 3 | 1.000（0.6 + 0.4） | OK |

行数 = 3（dungeon_normal 固定値一致）

#### C-3: シーケンス合理性（MstAutoPlayerSequence）

ElapsedTime 単調増加チェック: 逆転なし（全6行 OK）

| 順序 | トリガー | 条件値 | 召喚キャラ | 召喚数 |
|-----|---------|--------|----------|--------|
| 1 | ElapsedTime | 350 | e_dan_00001_general_n_Normal_Red | 1 |
| 2 | ElapsedTime | 1,000 | e_dan_00001_general_n_Normal_Red | 2 |
| 3 | ElapsedTime | 2,500 | e_dan_00101_general_n_Normal_Red | 2 |
| 4 | ElapsedTime | 4,000 | e_dan_00101_general_n_Normal_Red | 3 |
| 5 | ElapsedTime | 6,000 | e_dan_00001_general_n_Normal_Red | 1 |
| 6 | ElapsedTime | 6,500 | e_dan_00101_general_n_Normal_Red | 2 |

召喚総数 = 11体（バランスガイドライン目安 10〜30体の範囲内）

#### C-4: ステージ種別固有ルール（dungeon_normal）

| 確認項目 | 期待値 | 実際の値 | 判定 |
|---------|--------|---------|------|
| MstEnemyOutpost.hp | 100 | 100 | OK |
| MstKomaLine 行数 | 3行 | 3行 | OK |

#### C-5: ボス設定

- `boss_mst_enemy_stage_parameter_id` = NULL → ボスなし設定（通常ブロック仕様通り）
- `boss_count` = 0 → OK
- `InitialSummon` シーケンス行: なし → OK

---

### D: バランス比較（既存データとの比較）

既存 MstEnemyStageParameter.csv（全ステージ種別含む）の Normal 種平均値との比較:

| ID | role_type | 生成HP | 既存平均HP | HP比率 | 生成ATK | 既存平均ATK | ATK比率 | 判定 |
|----|-----------|-------|----------|--------|--------|-----------|--------|------|
| e_dan_00001_general_n_Normal_Red | Defense | 10,000 | 62,519 | 0.160 | 50 | 312 | 0.160 | WARNING |
| e_dan_00101_general_n_Normal_Red | Attack | 10,000 | 69,771 | 0.143 | 50 | 411 | 0.122 | WARNING |

**[WARNING] 既存平均の 0.2 倍未満**

- これはdungeonコンテンツのエネミーが既存データに少なく、メインクエスト高レベルや降臨バトル等の高パラメータエネミーが平均を引き上げているため
- 絶対値（HP=10,000、ATK=50）はエネミーステータスシートの基準値（Normal種HP中央値 10,000、Defense ATK中央値 100）と整合しており、実害はない
- SPY×FAMILYのdungeon_normal参考データ（HP=1,000、ATK=5,000）と比較すると方向性は異なるが、HPが高くATKが低いパターンは dungeon_normal として別設計として許容範囲

**確認事項**: この「HP=10,000 / ATK=50」という設定は、エネミーステータスシートの基準値に従った仮パラメータとして意図的なものか確認を推奨。

---

### E: アセットキーチェック

| テーブル | カラム | 値 | 判定 |
|---------|-------|-----|------|
| MstInGame | bgm_asset_key | SSE_SBG_003_001 | OK |
| MstInGame | boss_bgm_asset_key | NULL | OK（dungeon_normal はボスBGMなし） |
| MstInGame | loop_background_asset_key | dan_00007 | OK |
| MstEnemyOutpost | outpost_asset_key | NULL | WARNING（後述） |
| MstEnemyOutpost | artwork_asset_key | dan_0001 | OK（MstArtworkに存在確認済み） |
| MstKomaLine | koma1_asset_key（各行） | dan_00007 | OK |

**[WARNING] MstEnemyOutpost.outpost_asset_key = NULL**

- SPY×FAMILYの参考データ（dungeon_spy_normal_00001）でも同様に NULL が設定されている
- 既存の実CSVでも dungeon 系は outpost_asset_key が NULL が多数
- dungeon_normal では outpost_asset_key は不要な可能性が高いが、意図的な設定か確認推奨

---

## 総合判定

```
判定: PASS with WARNING
```

CRITICALエラーなし。以下の WARNING 事項は確認・判断を推奨するが、いずれも実機プレイ上の致命的な問題ではない。

### WARNING 事項一覧

| # | 分類 | 内容 | 対応 |
|---|------|------|------|
| 1 | フォーマット | MstInGame.csv に result_tips.ja / description.ja カラムが追加されており、既存の実CSVには存在しない | XLSX変換・投入時の扱いを確認 |
| 2 | バランス | HP・ATK が既存平均比 0.2 倍未満（全体平均が高い既存エネミーの影響）。絶対値はエネミーステータスシート基準内 | 意図的なパラメータ設定として確認 |
| 3 | アセットキー | MstEnemyOutpost.outpost_asset_key = NULL（SPY参考データでも同様のため、dungeon用として許容される可能性が高い） | dungeon仕様として問題ないか確認 |

### CRITICAL エラー

なし

---

## 参考データ

| 項目 | 値 |
|------|-----|
| 検証スクリプト（フォーマット） | .claude/skills/masterdata-csv-validator/scripts/validate_all.py |
| 検証スクリプト（ID整合性） | .claude/skills/masterdata-ingame-verifier/scripts/verify_id_integrity.py |
| エネミーステータスシート | domain/knowledge/masterdata/in-game/エネミーステータスシート.md |
| SPY参考データ | domain/tasks/masterdata-entry/masterdata-ingame-creator/20260301_131508_dungeon_spy_normal_block/ |

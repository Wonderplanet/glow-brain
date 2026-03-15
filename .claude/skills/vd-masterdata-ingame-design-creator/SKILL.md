---
name: vd-masterdata-ingame-design-creator
description: VDインゲーム設計書（design.md）の作成と調整に特化したスキル。作品ID・ブロック種別・敵構成をヒアリングし、設計書MDを生成、ユーザーが承認するまで修正ループを繰り返します。「VD設計書作成」「VDインゲーム設計」「VD設計調整」「design.md作成」「VDブロック設計」などのキーワードで使用します。
---

# VDインゲーム設計書作成スキル（design.md 専用）

## 概要

限界チャレンジ（VD）の**インゲーム設計書（design.md）の作成と調整**に特化したスキル。

- **このスキルが行うこと**: design.md の生成・修正ループ

---

## 出力先

```
domain/tasks/20260311_202700_vd_masterdata_ingame_generation/vd-ingame-design-creator/{ブロックID}/design.md
```

**例:**
```
domain/tasks/20260311_202700_vd_masterdata_ingame_generation/vd-ingame-design-creator/vd_kai_boss_00001/design.md
domain/tasks/20260311_202700_vd_masterdata_ingame_generation/vd-ingame-design-creator/vd_kai_normal_00001/design.md
```

---

## VD固有の固定値（変更不可）

| 項目 | bossブロック | normalブロック |
|------|------------|--------------|
| `MstEnemyOutpost.hp` | **100**（固定） | **100**（固定） |
| `MstKomaLine` 行数 | **1行**（固定） | **3行固定**（各行ごとにコマ数1〜4でランダム独立抽選） |
| フェーズ切り替え | **禁止**（SwitchSequenceGroup使用不可） | **禁止** |
| BGM | `SSE_SBG_003_004` | `SSE_SBG_003_010` |
| `mst_defense_target_id` | `__NULL__` | `__NULL__` |
| `mst_auto_player_sequence_id` | `""`（空文字） | `""`（空文字） |
| `boss_bgm_asset_key` | `""`（空文字） | `""`（空文字） |
| 全coefカラム（6カラム） | `1.0`（固定） | `1.0`（固定） |

### IDプレフィックス

| ブロック種別 | MstInGame.id パターン | 例 |
|------------|---------------------|-----|
| boss | `vd_{作品ID}_boss_{連番5桁}` | `vd_kai_boss_00001` |
| normal | `vd_{作品ID}_normal_{連番5桁}` | `vd_kai_normal_00001` |

---

## 4ステップワークフロー

### Step 0: 情報確認

以下が揃っているか確認し、不足があれば確認する。

| 確認項目 | 内容 |
|---------|------|
| 作品ID | シリーズ略称（`kai` / `dan` / `spy` 等）。未指定なら確認する |
| ブロック種別 | `boss` または `normal` のどちらか |
| ボスキャラ | bossブロックのみ：ボスキャラID・色属性 |
| 雑魚キャラ | 雑魚キャラID・色属性・体数（condition_type区切りごと） |
| 連番 | 開始番号（通常は `00001`） |

作品別の登場キャラは [vd-character-list.md](references/vd-character-list.md) を参照。

**テーブル詳細ドキュメント読み込み（必須）**: 設計書生成前に以下のドキュメントを Read tool で読み込み、各カラムの定義・enum値・制約を把握する。

| テーブル | ドキュメントパス |
|---------|----------------|
| MstInGame | `domain/knowledge/masterdata/table-docs/MstInGame.md` |
| MstEnemyStageParameter | `domain/knowledge/masterdata/table-docs/MstEnemyStageParameter.md` |
| MstAutoPlayerSequence | `domain/knowledge/masterdata/table-docs/MstAutoPlayerSequence.md` |
| MstKomaLine | `domain/knowledge/masterdata/table-docs/MstKomaLine.md` |
| MstEnemyOutpost | `domain/knowledge/masterdata/table-docs/MstEnemyOutpost.md` |
| MstPage | `domain/knowledge/masterdata/table-docs/MstPage.md` |
| MstAttack | `domain/knowledge/masterdata/table-docs/MstAttack.md` |
| MstAttackElement | `domain/knowledge/masterdata/table-docs/MstAttackElement.md` |

**シーケンス設計参考ドキュメント読み込み（必須）**: 多彩なシーケンス設計のために以下のドキュメントも Read tool で読み込む。

| ドキュメント | パス |
|------------|------|
| MstAutoPlayerSequence 具体例集 | `references/MstAutoPlayerSequence_具体例集.md` |
| MstAutoPlayerSequence 設計パターン集 | `references/MstAutoPlayerSequence_設計パターン集.md` |

---

## レベルデザイン方針

設計書を生成する際は、以下の方針を守ること。

### 設計共通方針

#### 難易度

- **既存のnormalクエストNormal難易度**程度のレベル感で設計する

#### condition_type 禁止事項（VD共通）

- `InitialSummon` および `ElapsedTime` は **VDでは使用禁止**
- 以下から選択すること：
  - `FriendUnitDead` — 友軍キャラが倒されたとき
  - `OutpostHpPercentage` — 拠点HPが指定%以下になったとき
  - `EnterTargetKomaIndex` — 対象コマインデックスに入ったとき
  - `DarknessKomaCleared` — 闇コマをクリアした数に応じて
  - `FriendUnitTransform` — フレンドキャラが変身したとき
  - `OutpostDamage` — 拠点がダメージを受けたとき
  - `FriendUnitSummoned` — 友軍キャラが召喚されたとき
  - `FoeEnterSameKomaLine` — 自分のコマライン（行）に敵対者が入ったとき
  - `OnFieldPlayerCharacterCount` — フィールド上の通常プレイヤーキャラ数

#### 対抗キャラによる調整

- **雑魚キャラ**: 対抗キャラが有利になるカラーで用意する
- **ファントム・ボス**: 無属性にする
- **コマ効果**: 対抗キャラが有利な効果のコマを **1〜2個**（ブロックごとにランダムに決定）入れる

##### 対抗キャラ弱点・軽減情報の調べ方

1. `vd-character-list.md` の対抗キャラエントリを参照
2. キャラのスキル・パッシブ説明から「〇〇ダメージ軽減」「〇〇耐性」を特定
3. 軽減している属性に対応するコマ効果を1〜2個選択する（例: ロイドが対抗 → 毒軽減 → 毒コマを1〜2個）

#### ボスブロック

- 出現させるのは**ボスのみ**（雑魚なし）
- ゲートあり（MstEnemyOutpost設定）
  - **全作品・全ボスブロック共通の1レコード**（`vd_all/data/MstEnemyOutpost.csv`）を使用
  - HP=100固定。ブロックごとに個別設定不要
  - `MstInGame.mst_enemy_outpost_id` には共通レコードのIDを設定する

#### ノーマルブロック

- 雑魚キャラを**最低15体以上**出現させる
- ゲートのマスタ設定**なし**（MstEnemyOutpost設定なし）

#### 敵シーケンスの召喚場所指定

- 召喚位置は `0〜1` が1行目、`1〜2` が2行目、`2〜3` が3行目（行インデックス）
- **ボスブロック**: 1行固定 → 値は必ず `0〜1` の範囲内
- **ノーマルブロック**: 3行固定 → 値は必ず `0〜3` の範囲内
- 0付近はプレイヤー陣地のため、召喚位置として設定しない（通常は1.0以上を使用）

---

### Step 1: 敵キャラ基礎ステータス設計（MstEnemyStageParameter）

ヒアリング内容を基に、登場する敵キャラのステータスを設計する。

- 作品別の登場キャラは [vd-character-list.md](references/vd-character-list.md) を参照して敵キャラを選定
- ステータス設計: `base_hp` / `base_atk` / `base_spd` / `knockback` / `combo` / `drop_bp` 等を決定
- 行動パターン設計: 各敵キャラの `MstAttack` / `MstAttackElement` の構成（攻撃種別・効果・対象・ダメージ種別）を設計する
  - `MstAttackElement.damage_type` で毒・炎ダメージを指定すると対抗キャラの軽減システムと連動する

---

### UR対抗キャラ・ギミックの活用

- **その作品のURキャラの対抗となるギミックやコマ効果を持つ敵キャラを使用することを基本とする**
- `vd-character-list.md` の「UR対抗キャラ」列を確認し、そのキャラに対応したギミック・コマ効果を持つ敵キャラを優先的に採用する
- UR対抗の観点で設計書に反映できない場合は、Step 3 のユーザー確認時にその旨をコメントする

### 登場体数の設計目標

| ブロック種別 | 雑魚敵の最低体数 | 備考 |
|------------|----------------|------|
| normalブロック | **15体以上** | 雑魚扱いの敵キャラの合計（ボスを除く） |
| bossブロック | 制約なし | ボス1体 + 雑魚は任意体数で設計する |

### シーケンス設計の多様性

**多彩なトリガーを活用して、面白みのある設計を目指す（`InitialSummon` / `ElapsedTime` は使用禁止）。**
具体例集・設計パターン集から適切なパターンを選択すること。

| トリガー | 設計のねらい | 活用例 |
|---------|------------|--------|
| `FriendUnitDead` | 「倒すほど強くなる」プレッシャー | N体倒したら強化雑魚追加・c_キャラ登場・無限補充開始 |
| `OutpostHpPercentage` | 拠点防衛プレッシャー | 拠点50%削れたら覚醒ボス登場 |
| `EnterTargetKomaIndex` | コマ進行連動 | コマ位置に合わせた伏兵出現 |
| `DarknessKomaCleared` | 難易度自動調整 | 闇コマクリア数に応じたボス追加 |
| `FriendUnitTransform` | 変身演出 | フレンド変身後に敵大量召喚 |
| `FoeEnterSameKomaLine` | 行侵入連動 | 自コマラインに敵が入ったら追加召喚 |
| `OnFieldPlayerCharacterCount` | フィールド人数連動 | キャラ数に応じた難易度調整 |

**summon_count の活用パターン**:
- `99` + 適切な interval = 実質無限補充（終盤強化に有効）
- `10〜20` 体一気召喚 = 大規模ラッシュ演出
- `1` 体精密召喚 = ボス・特殊キャラの確実な1体出現

**推奨設計パターン（3種）**:
- **A. FriendUnitDead型**: FriendUnitDead で段階強化 → 終盤 summon_count=99 無限補充
- **B. 拠点防衛型**: OutpostHpPercentage で残HP連動 → c_キャラ最終ボス
- **D. キャラ変身型**: FriendUnitTransform=1 で変身後に大量召喚

---

### Step 2: 設計書MD生成

Step 1 の敵キャラ設計を踏まえて `design.md` を生成して出力先ディレクトリに保存する。

**設計書フォーマット**: [design-format.md](references/design-format.md) を参照。

**コマ設計時の参照先**:
- `koma1_asset_key` の決定: [series-koma-assets.csv](references/series-koma-assets.csv) を参照して作品IDに合った値を設定する
- `koma1_back_ground_offset` の決定: [koma-background-offset.md](references/koma-background-offset.md) を参照して推奨値を設定する

**空欄になりがちなカラムのデフォルト値**: [vd-column-defaults.md](references/vd-column-defaults.md) を参照する。

---

### Step 3: ユーザー確認・修正ループ

```
設計書を生成しました（design.md）。内容をご確認ください。

修正がなければ「OK」または「承認」とお伝えください。
修正がある場合は具体的にご指示ください。
```

**ユーザーが「OK」または「承認」と言うまで修正ループを繰り返す。**

**Step 3 完了時**: design.md のパスを案内して終了する。

---

## ガードレール（必ず守ること）

1. **IDプレフィックスは `vd_`**
2. **ゲート(Outpost)HP固定**: 全作品・全ボスブロック共通の1レコード（`vd_all/data/MstEnemyOutpost.csv`）を使用。HP=100固定（変更不可）
3. **フェーズ切り替え禁止**: `SwitchSequenceGroup` は使用しない
4. **承認前に完了しない**: ユーザーが「OK」と言うまで修正ループを続ける
5. **コマアセットキーは series-koma-assets.csv を参照**: 作品IDに合った `koma1_asset_key` を設定する
6. **koma1_back_ground_offset は koma-background-offset.md を参照**: 推奨仮値を設定する
7. **空欄カラムのデフォルト値は vd-column-defaults.md を参照**: 設定漏れを防ぐ
8. **ボスは `MstInGame.boss_mst_enemy_stage_parameter_id` で設定**: `InitialSummon` は使用禁止。ボスキャラは必ず `boss_mst_enemy_stage_parameter_id` に設定する
9. **normalブロックのMstKomaLineは3行固定**: row=1〜3 の3エントリを設計する
10. **`InitialSummon` / `ElapsedTime` は使用禁止**: VDでは condition_type として `InitialSummon` と `ElapsedTime` を使用してはならない。使用可能な condition_type は設計共通方針のリストから選ぶこと
11. **c_キャラ複数体は FriendUnitDead でチェーン**: c_キャラ（`c_` プレフィックス）が複数体登場する場合、2体目以降は必ず `FriendUnitDead` で前の c_キャラの撃破を待ってから召喚するよう設計する（フィールドに同時に2体以上出現させない）。また c_キャラのエントリは必ず `summon_count = 1` とする（summon_count を2以上にすると同時複数体が出現してしまうため）
12. **複数 c_キャラの同時召喚は禁止**: 複数の c_キャラを同じタイミング・トリガーで召喚する設計は原則禁止。≤500ms の短時間連続召喚（演出目的）が必要な場合はプランナーに確認する
13. **e_glo_* はこの制約の対象外**: `e_glo_*`（グロー本体）は c_キャラではないため、同時出現制約の対象外
14. **normalブロックは雑魚敵を最低15体以上**: normalブロックでは雑魚扱いの敵キャラ（c_キャラ含む）の合計が**最低15体以上**になるよう設計する
15. **bossブロックの体数制約なし**: bossブロックは雑魚15体以上の制約はない。ボス1体 + 雑魚は任意体数で設計する
16. **mst_defense_target_id は `__NULL__`**: 空文字だと参照エラー。`__NULL__`（NULLリテラル）が必須
17. **mst_auto_player_sequence_idは空文字**: レガシーカラム。値を設定するとバリデーションエラーになる
18. **全coefカラムは1.0固定**: 6カラム（normal_enemy_hp/attack/speed_coef・boss_enemy_hp/attack/speed_coef）全て1.0固定

---

## バッチモード（--batch）

サブエージェントや一括処理での実行時は `--batch` フラグを指定する。

### バッチモード引数

| 引数 | 必須 | 説明 |
|---|---|---|
| `作品ID` | ✓ | kai / dan / spy 等 |
| `ブロック種別` | ✓ | Normal または Boss |
| `--batch` | ✓ | バッチモード有効化フラグ |

### バッチモードの動作変更

1. **Step 0（ヒアリング）をスキップ**
   - 連番は `00001` を使用（既存フォルダが存在する場合は次の連番を使用）
   - ユーザーへの確認なしで設計書の内容を決定する

2. **Step 3（承認ループ）をスキップ**
   - 設計書生成後、確認なしで直接 `design.md` に書き込む
   - 「設計書を保存しました: {パス}」とのみ出力して終了

### バッチモード呼び出し例

```
/vd-masterdata-ingame-design-creator 作品ID=dan ブロック種別=Normal --batch
```

---

## リファレンス一覧

- [design-format.md](references/design-format.md) — design.md フォーマットテンプレート定義
- [vd-character-list.md](references/vd-character-list.md) — 作品別登場キャラ一覧
- [duckdb-vd-queries.md](references/duckdb-vd-queries.md) — VD用DuckDBクエリ集
- [vd-column-defaults.md](references/vd-column-defaults.md) — デザインフェーズで設定が必要なカラムのデフォルト値
- [series-koma-assets.csv](references/series-koma-assets.csv) — 作品別コマアセットキー・back_ground_offset対応表
- [koma-background-offset.md](references/koma-background-offset.md) — コマアセットキー別推奨back_ground_offset値
### マスタテーブル詳細ドキュメント（カラム定義・enum値の正確な参照元）

- `domain/knowledge/masterdata/table-docs/MstInGame.md`
- `domain/knowledge/masterdata/table-docs/MstEnemyStageParameter.md`
- `domain/knowledge/masterdata/table-docs/MstAutoPlayerSequence.md`
- `domain/knowledge/masterdata/table-docs/MstKomaLine.md`
- `domain/knowledge/masterdata/table-docs/MstEnemyOutpost.md`
- `domain/knowledge/masterdata/table-docs/MstPage.md`
- `domain/knowledge/masterdata/table-docs/MstAttack.md`
- `domain/knowledge/masterdata/table-docs/MstAttackElement.md`

### シーケンス設計参考ドキュメント

- [MstAutoPlayerSequence_具体例集.md](references/MstAutoPlayerSequence_具体例集.md) — 過去15ステージの実例集（N-1〜N-15。トリガー・体数・c_キャラ使用例）
- [MstAutoPlayerSequence_設計パターン集.md](references/MstAutoPlayerSequence_設計パターン集.md) — condition_type・summon_count・aura_type等の設計パターン解説

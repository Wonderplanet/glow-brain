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
| `MstInGame.content_type` | `Dungeon` | `Dungeon` |
| `MstInGame.stage_type` | `vd_boss` | `vd_normal` |
| `MstEnemyOutpost.hp` | **1,000**（固定） | **100**（固定） |
| `MstKomaLine` 行数 | **1行**（固定） | **3行固定**（各行ごとにコマ数1〜4でランダム独立抽選） |
| フェーズ切り替え | **禁止**（SwitchSequenceGroup使用不可） | **禁止** |
| BGM | `SSE_SBG_003_004` | `SSE_SBG_003_010` |

### IDプレフィックス

| ブロック種別 | MstInGame.id パターン | 例 |
|------------|---------------------|-----|
| boss | `vd_{作品ID}_boss_{連番5桁}` | `vd_kai_boss_00001` |
| normal | `vd_{作品ID}_normal_{連番5桁}` | `vd_kai_normal_00001` |

---

## 3ステップワークフロー

### Step 0: 情報確認

以下が揃っているか確認し、不足があれば確認する。

| 確認項目 | 内容 |
|---------|------|
| 作品ID | シリーズ略称（`kai` / `dan` / `spy` 等）。未指定なら確認する |
| ブロック種別 | `boss` または `normal` のどちらか |
| ボスキャラ | bossブロックのみ：ボスキャラID・色属性 |
| 雑魚キャラ | 雑魚キャラID・色属性・体数（ElapsedTime区切りごと） |
| 連番 | 開始番号（通常は `00001`） |

作品別の登場キャラは [vd-character-list.md](references/vd-character-list.md) を参照。
**MstEnemyStageParameter.id の選出元**: 必ず `vd_all/data/MstEnemyStageParameter.csv` を読み込み、指定作品の `id` を確認して選出すること。

**テーブル詳細ドキュメント読み込み（必須）**: 設計書生成前に以下のドキュメントを Read tool で読み込み、各カラムの定義・enum値・制約を把握する。

| テーブル | ドキュメントパス |
|---------|----------------|
| MstInGame | `domain/knowledge/masterdata/table-docs/MstInGame.md` |
| MstEnemyStageParameter | `domain/knowledge/masterdata/table-docs/MstEnemyStageParameter.md` |
| MstAutoPlayerSequence | `domain/knowledge/masterdata/table-docs/MstAutoPlayerSequence.md` |
| MstKomaLine | `domain/knowledge/masterdata/table-docs/MstKomaLine.md` |
| MstEnemyOutpost | `domain/knowledge/masterdata/table-docs/MstEnemyOutpost.md` |
| MstPage | `domain/knowledge/masterdata/table-docs/MstPage.md` |

**シーケンス設計参考ドキュメント読み込み（必須）**: 多彩なシーケンス設計のために以下のドキュメントも Read tool で読み込む。

| ドキュメント | パス |
|------------|------|
| MstAutoPlayerSequence 具体例集 | `references/MstAutoPlayerSequence_具体例集.md` |
| MstAutoPlayerSequence 設計パターン集 | `references/MstAutoPlayerSequence_設計パターン集.md` |

---

## レベルデザイン方針

設計書を生成する際は、以下の方針を守ること。

### UR対抗キャラ・ギミックの活用

- **その作品のURキャラの対抗となるギミックやコマ効果を持つ敵キャラを使用することを基本とする**
- `vd-character-list.md` の「UR対抗キャラ」列を確認し、そのキャラに対応したギミック・コマ効果を持つ敵キャラを優先的に採用する
- UR対抗の観点で設計書に反映できない場合は、Step 2 のユーザー確認時にその旨をコメントする

### 登場体数の設計目標

| ブロック種別 | 雑魚敵の最低体数 | 備考 |
|------------|----------------|------|
| normalブロック | **15体以上** | 雑魚扱いの敵キャラの合計（ボスを除く） |
| bossブロック | 制約なし | ボス1体 + 雑魚は任意体数で設計する |

### シーケンス設計の多様性

**ElapsedTime だけでなく多彩なトリガーを活用して、面白みのある設計を目指す。**
具体例集・設計パターン集から適切なパターンを選択すること。

| トリガー | 設計のねらい | 活用例 |
|---------|------------|--------|
| `FriendUnitDead` | 「倒すほど強くなる」プレッシャー | N体倒したら強化雑魚追加・c_キャラ登場・無限補充開始 |
| `OutpostHpPercentage` | 拠点防衛プレッシャー | 拠点50%削れたら覚醒ボス登場 |
| `InitialSummon` | 開幕演出・ボス即配置 | 複数体を異なる位置で同時配置 |
| `EnterTargetKomaIndex` | コマ進行連動 | コマ位置に合わせた伏兵出現 |
| `DarknessKomaCleared` | 難易度自動調整 | 闇コマクリア数に応じたボス追加 |
| `FriendUnitTransform` | 変身演出 | フレンド変身後に敵大量召喚 |

**summon_count の活用パターン**:
- `99` + 適切な interval = 実質無限補充（終盤強化に有効）
- `10〜20` 体一気召喚 = 大規模ラッシュ演出
- `1` 体精密召喚 = ボス・特殊キャラの確実な1体出現

**推奨設計パターン（4種）**:
- **A. FriendUnitDead型**: ElapsedTime 開幕 → FriendUnitDead で段階強化 → 終盤 summon_count=99 無限補充
- **B. 拠点防衛型**: OutpostHpPercentage で残HP連動 → c_キャラ最終ボス
- **C. ストーリー演出型**: InitialSummon でボス即配置 → FriendUnitDead チェーン
- **D. キャラ変身型**: FriendUnitTransform=1 で変身後に大量召喚

---

### Step 1: 設計書MD生成

ヒアリング内容を基に `design.md` を生成して出力先ディレクトリに保存する。

**設計書フォーマット**: [design-format.md](references/design-format.md) を参照。

**コマ設計時の参照先**:
- `koma1_asset_key` の決定: [series-koma-assets.csv](references/series-koma-assets.csv) を参照して作品IDに合った値を設定する
- `koma1_back_ground_offset` の決定: [koma-background-offset.md](references/koma-background-offset.md) を参照して推奨値を設定する

**空欄になりがちなカラムのデフォルト値**: [vd-column-defaults.md](references/vd-column-defaults.md) を参照する。

---

### Step 2: ユーザー確認・修正ループ

```
設計書を生成しました（design.md）。内容をご確認ください。

修正がなければ「OK」または「承認」とお伝えください。
修正がある場合は具体的にご指示ください。
```

**ユーザーが「OK」または「承認」と言うまで修正ループを繰り返す。**

**Step 2 完了時**: design.md のパスを案内して終了する。

---

## ガードレール（必ず守ること）

1. **IDプレフィックスは `vd_`**
2. **ゲート(Outpost)HP固定**: boss=1,000固定、normal=100固定（変更不可）
3. **フェーズ切り替え禁止**: `SwitchSequenceGroup` は使用しない
4. **承認前に完了しない**: ユーザーが「OK」と言うまで修正ループを続ける
5. **コマアセットキーは series-koma-assets.csv を参照**: 作品IDに合った `koma1_asset_key` を設定する
6. **koma1_back_ground_offset は koma-background-offset.md を参照**: 推奨仮値を設定する
7. **空欄カラムのデフォルト値は vd-column-defaults.md を参照**: 設定漏れを防ぐ
8. **ボスの二重設定**: `MstInGame.boss_mst_enemy_stage_parameter_id` + `MstAutoPlayerSequence`のInitialSummonで設定することを設計書に明記する
9. **normalブロックのMstKomaLineは3行固定**: row=1〜3 の3エントリを設計する
10. **MstEnemyStageParameter.id の選出元は vd_all CSV から**: `domain/tasks/20260311_202700_vd_masterdata_ingame_generation/vd-ingame-design-creator/vd_all/data/MstEnemyStageParameter.csv` を読み込んで選出する。masterdata全体ではなくこのキュレーション済みCSVを使う
11. **c_キャラ複数体は FriendUnitDead でチェーン**: c_キャラ（`c_` プレフィックス）が複数体登場する場合、初回のみ `ElapsedTime` でタイミングを制御し、2体目以降は必ず `FriendUnitDead` で前の c_キャラの撃破を待ってから召喚するよう設計する（フィールドに同時に2体以上出現させない）。また c_キャラのエントリは必ず `summon_count = 1` とする（summon_count を2以上にすると同時複数体が出現してしまうため）
12. **ElapsedTime での複数 c_キャラ召喚は禁止**: `ElapsedTime` のみで複数の c_キャラを召喚する設計は原則禁止。≤500ms の短時間連続召喚（演出目的）が必要な場合はプランナーに確認する
13. **e_glo_* はこの制約の対象外**: `e_glo_*`（グロー本体）は c_キャラではないため、同時出現制約の対象外
14. **normalブロックは雑魚敵を最低15体以上**: normalブロックでは雑魚扱いの敵キャラ（c_キャラ含む）の合計が**最低15体以上**になるよう設計する
15. **bossブロックの体数制約なし**: bossブロックは雑魚15体以上の制約はない。ボス1体 + 雑魚は任意体数で設計する

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

2. **Step 2（承認ループ）をスキップ**
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
- `vd_all/data/MstEnemyStageParameter.csv` — VD専用の敵キャラステージパラメータ一覧（選出元。47件。Normal/Boss・全作品分）
  - パス: `domain/tasks/20260311_202700_vd_masterdata_ingame_generation/vd-ingame-design-creator/vd_all/data/MstEnemyStageParameter.csv`

### マスタテーブル詳細ドキュメント（カラム定義・enum値の正確な参照元）

- `domain/knowledge/masterdata/table-docs/MstInGame.md`
- `domain/knowledge/masterdata/table-docs/MstEnemyStageParameter.md`
- `domain/knowledge/masterdata/table-docs/MstAutoPlayerSequence.md`
- `domain/knowledge/masterdata/table-docs/MstKomaLine.md`
- `domain/knowledge/masterdata/table-docs/MstEnemyOutpost.md`
- `domain/knowledge/masterdata/table-docs/MstPage.md`

### シーケンス設計参考ドキュメント

- [MstAutoPlayerSequence_具体例集.md](references/MstAutoPlayerSequence_具体例集.md) — 過去15ステージの実例集（N-1〜N-15。トリガー・体数・c_キャラ使用例）
- [MstAutoPlayerSequence_設計パターン集.md](references/MstAutoPlayerSequence_設計パターン集.md) — condition_type・summon_count・aura_type等の設計パターン解説

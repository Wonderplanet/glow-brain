# 【降臨バトル】誰の依頼だ？ マスタデータ

このディレクトリには、降臨バトル「誰の依頼だ？」の運営仕様書から生成されたマスタデータCSVファイルが含まれています。

## 生成されたファイル

### MstAdventBattle.csv
降臨バトルの基本情報を定義するマスタデータです。

**主要設定:**
- **バトルID**: `quest_raid_you1_00001`
- **イベントID**: `event_you_00001`
- **インゲームID**: `raid_you1_00001`
- **バトルタイプ**: ScoreChallenge (スコアチャレンジ)
- **開催期間**: 2026年2月1日 15:00 ～ 2026年2月28日 14:59
- **報酬**: コイン300、リーダーEXP100
- **リリースキー**: 202602010

**検証結果:**
- ✓ テンプレート照合: 合格
- ✓ フォーマット検証: 合格
- ⚠ スキーマ検証: カラム数不一致 (テンプレートにI18n列が含まれるため)

### MstEnemyStageParameter.csv
バトルに登場する敵キャラクターのパラメータを定義するマスタデータです。

**登場キャラクター (11体):**
1. `e_you_00001_you1_advent_Normal_Green` - 不良系金髪イケメン (Defense)
2. `e_you_00101_you1_advent_Normal_Green` - イケメンじゃない殺し屋 (Attack)
3. `c_you_00001_you1_advent_Normal_Colorless` - 元殺し屋の新人教諭 リタ (Attack)
4. `c_you_00201_you1_advent_Boss_Green` - ダグ (Boss/Technical)
5. `c_you_00101_you1_advent_Boss_Green` - ルーク (Boss/Technical)
6. `c_you_00301_you1_advent_Boss_Green` - ハナ (Boss/Attack)
7. `c_you_00301_you1_advent_Boss_Colorless` - ハナ (Boss/Attack)
8. `e_you_00101_you1_advent_Boss_Colorless` - イケメンじゃない殺し屋 (Boss/Attack)
9. `c_you_00001_you1_advent_Boss_Green` - 元殺し屋の新人教諭 リタ (Boss/Attack)
10. `e_you_00001_you1_advent_Boss_Colorless` - 不良系金髪イケメン (Boss/Defense)
11. `c_you_00201_you1_advent_Normal_Green` - ダグ (Normal/Technical)

**検証結果:**
- ✓ テンプレート照合: 合格
- ✓ フォーマット検証: 合格
- ✓ スキーマ検証: 合格
- **DB投入可能**

## バトル構成

このバトルはウェーブ方式 (w1～w6) で、w6の後はw1に戻るループ構造になっています。

**ウェーブ構成:**
- **w1**: リタ、ダグ（ボス）、不良系金髪イケメン×2
- **w2**: ルーク（ボス）、不良系金髪イケメン×2、イケメンじゃない殺し屋×2
- **w3**: ハナ（ボス）、不良系金髪イケメン×4
- **w4**: イケメンじゃない殺し屋（ボス）×2、不良系金髪イケメン×2、イケメンじゃない殺し屋×3
- **w5**: リタ（ボス）、ダグ×2、ハナ×2、不良系金髪イケメン×3
- **w6**: 不良系金髪イケメン（ボス）×2、イケメンじゃない殺し屋（ボス）×2、イケメンじゃない殺し屋×3、不良系金髪イケメン×3

## 報酬設定

### クリア報酬
- コイン: 300
- リーダーEXP: 100

### ドロップアイテム (ランダム)
1. カラーメモリー・グレー (memory_glo_00001) - 3個 @ 20%
2. カラーメモリー・レッド (memory_glo_00002) - 3個 @ 20%
3. カラーメモリー・ブルー (memory_glo_00003) - 3個 @ 20%
4. カラーメモリー・イエロー (memory_glo_00004) - 3個 @ 20%
5. カラーメモリー・グリーン (memory_glo_00005) - 3個 @ 20%

## 生成方法

これらのCSVファイルは、`Sequence.html`から自動生成されました。

```bash
python3 scripts/parse_sequence_html.py "マスタデータ/運営仕様/【降臨バトル】誰の依頼だ？/Sequence.html"
```

## 検証方法

生成されたCSVファイルは以下のコマンドで検証できます:

```bash
python3 .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv "マスタデータ/運営仕様/【降臨バトル】誰の依頼だ？/MstEnemyStageParameter.csv"
```

## 注意事項

- **MstAdventBattle.csv**: テンプレートにI18n列（name.ja, boss_description.ja）が含まれているため、DBスキーマとのカラム数が一致しませんが、これは仕様通りです。
- **MstEnemyStageParameter.csv**: 完全に検証済みで、そのままDB投入可能です。
- このディレクトリのファイルは読み取り専用の参照用です。実際のマスタデータ変更は元のリポジトリ（glow-masterdata）で行ってください。

## 関連ファイル

- **Sequence.html**: 元の運営仕様書 (Google Sheets エクスポート)
- **scripts/parse_sequence_html.py**: CSV生成スクリプト

# リリースキー 202602010 マスタデータレポート

## 概要

| 項目 | 値 |
|------|-----|
| **リリースキー** | 202602010 |
| **抽出日時** | 2026-02-10T14:40:28Z |
| **総テーブル数** | 30テーブル |
| **総行数** | 218行 |
| **主要テーマ** | バレンタインイベント + PvPステージ拡充 |

### カテゴリ別集計

| カテゴリ | テーブル数 | 行数 | 説明 |
|---------|----------|------|------|
| **Mst（固定マスタ）** | 16 | 108 | ゲームの基本設定・コンテンツ定義 |
| **Opr（運営施策）** | 8 | 77 | 期間限定イベント・キャンペーン |
| **I18n（多言語）** | 6 | 33 | 日本語テキスト |

### 過去データとの比較

| 項目 | 値 |
|------|-----|
| **過去データのテーブル数** | 155テーブル |
| **過去データの総行数** | 42,947行 |
| **このリリースの割合** | 総行数の約0.5% |

---

## データ投入サマリー

このリリースでは、**バレンタインイベント**を中心とした大規模な施策リリースを実施しました。

### 主要な追加内容

1. **バレンタインピックアップガチャ** - 女の子キャラ限定のピックアップガチャ
2. **バレンタインキャラパック** - 7種類のキャラパック販売
3. **PvPステージ拡充** - 特殊ルール付きの新ステージ2種類
4. **有償ダイヤ商品** - 6段階の価格帯で販売
5. **スタミナ・強化パック** - プレイヤー支援アイテムパック3種類
6. **ボックスガチャ** - いいジャンくじ（テスト版）

---

## 機能別データ詳細

### 1. バレンタインピックアップガチャ（OprGacha）

#### ガチャ基本設定
- **ガチャID**: Pickup_Valentine_001
- **名称**: バレンタインピックアップガシャ
- **説明**: バレンタインピックアップガシャチケットでも引ける! 女の子のみ登場ガシャ!
- **期間**: 2026-02-10 15:00:00 ～ 2026-03-02 10:59:59
- **ガチャタイプ**: Pickup
- **10連ガチャ**: 対応（固定報酬1体確定）
- **特徴**: ピックアップURキャラ1体確定、SSR以上1体確定

#### ガチャ報酬構成（OprGachaPrize - 35行）

**ピックアップキャラ8体（weight 105）:**
- chara_spy_00201（ヨル）
- chara_spy_00001
- chara_gom_00001（姫様）
- chara_yuw_00001（天乃 リリサ）
- chara_yuw_00101（橘 美花莉）
- chara_sur_00101（羽前 京香）
- chara_mag_00001（桜木 カナ）
- chara_sum_00101（小舟 潮）

**その他URキャラ7体（weight 400）:**
- chara_dan_00101, chara_chi_00301, chara_kai_00301, chara_aha_00101, chara_jig_00101, chara_dos_00001, chara_dos_00101

**SSRキャラ5体（weight 4872）:**
- chara_gom_00101, chara_sur_00201, chara_sur_00301, chara_mag_00101, chara_sum_00201

**固定報酬グループ（fixd_Pickup_Valentine_001）:**
- 同様のキャラ構成だが、weightが異なる（ピックアップ21、その他776）

#### ガチャ利用リソース（OprGachaUseResource - 3行）
- **チケット**: ticket_glo_10002（バレンタインピックアップガシャチケット） × 1枚 → 1回
- **ダイヤ**: 150個 → 1回
- **ダイヤ**: 1500個 → 10回

#### ガチャ表示キャラ説明（OprGachaDisplayUnitI18n - 4行）
- chara_sum_00101: 前線の維持に特化したサポートキャラ!
- chara_gom_00001: 必殺ワザと特性で耐久性抜群のディフェンスキャラ!
- chara_chi_00301: 必殺ワザで複数の相手にダメージを与える!
- chara_dos_00001: 前線を支える遠距離サポートキャラ!

---

### 2. バレンタインキャラパック（MstPack）

#### パック一覧（MstPack - 10行）

**バレンタインキャラパック7種類（event_item_pack_14～20）:**
- event_item_pack_14: 「ヨル」バレンタインキャラパック
- event_item_pack_15: 「姫様」バレンタインキャラパック
- event_item_pack_16: 「天乃 リリサ」バレンタインキャラパック
- event_item_pack_17: 「橘 美花莉」バレンタインキャラパック
- event_item_pack_18: 「羽前 京香」バレンタインキャラパック
- event_item_pack_19: 「桜木 カナ」バレンタインキャラパック
- event_item_pack_20: 「小舟 潮」バレンタインキャラパック

**共通仕様:**
- 価格: 1980円
- 購入制限: お一人様1回まで
- 期間: 2026-02-10 15:00:00 ～ 2026-03-02 10:59:59

**スタミナ・強化パック3種類:**
- event_item_pack_21: いいジャン!スタミナドリンクパック（480円、2026-02-02開始）
- event_item_pack_22: スタミナブースト応援パック（1480円、2026-02-02開始）
- event_item_pack_23: 即戦力!キャラ強化パック（1980円、2026-02-09～2026-02-16）

#### パック内容詳細（MstPackContent - 57行）

**バレンタインキャラパック（各パック共通構成）:**
1. キャラ1体（各パック固有）
2. ticket_glo_00003（チケット）× 1枚
3. メモリー × 750個（キャラごとに異なる種類）
4. memoryfragment_glo_00001（メモリーフラグメント）× 50個
5. memoryfragment_glo_00002（メモリーフラグメント）× 30個
6. コイン × 15,000（ボーナス）

**スタミナドリンクパック（event_item_pack_21）:**
- stamina_item_glo_00001（スタミナドリンク）× 10個

**スタミナブースト応援パック（event_item_pack_22）:**
- stamina_item_glo_00001（スタミナドリンク）× 20個
- memoryfragment_glo_00001 × 50個
- memoryfragment_glo_00002 × 30個
- memoryfragment_glo_00003 × 3個
- コイン × 20,000（ボーナス）

**キャラ強化パック（event_item_pack_23）:**
- memory_glo_00001～00005（各種メモリー）× 1,500個ずつ
- memoryfragment_glo_00001 × 50個
- memoryfragment_glo_00002 × 30個
- memoryfragment_glo_00003 × 3個
- コイン × 30,000（ボーナス）

---

### 3. ストア商品（MstStoreProduct）

#### 商品一覧（MstStoreProduct / OprProduct - 16行）

**バレンタインパック対応商品（ID 54～63）:**
- ID 54～60: 1980円（バレンタインキャラパック7種）
- ID 61: 480円（スタミナドリンクパック）
- ID 62: 1480円（スタミナブースト応援パック）
- ID 63: 1980円（キャラ強化パック）

**有償ダイヤ商品6種類（ID 79～84）:**
- ID 79: 160円（150個）
- ID 80: 480円（300個）
- ID 81: 980円（620個）
- ID 82: 2480円（1,580個）
- ID 83: 4980円（3,200個）
- ID 84: 9480円（6,300個）

期間: 2026-02-02 15:00:00 ～ 2026-02-28 23:59:59

---

### 4. PvPステージ拡充（MstPvp）

#### PvPステージ2種類追加（MstPvp - 2行）

**PvP 2026006 - 攻撃DOWNコマステージ:**
- **説明**: 3段のステージで戦うぞ! 攻撃DOWNコマが登場するぞ!
- **特別ルール**: リーダーP 1000から開始、全キャラ体力3倍UP
- **推奨戦略**: 特性で攻撃DOWNコマ無効化を持っているキャラを編成しよう!
- **ゲーム内ID**: pvp_you_01
- **制限時間**: 180秒
- **最大挑戦回数**: 1日10回
- **アイテム使用挑戦**: 1日10回（コスト1）

**PvP 2026007 - ダメージコマステージ:**
- **説明**: 3段のステージで戦うぞ! ダメージコマが登場するぞ!
- **特別ルール**: リーダーP 1000から開始、全キャラ体力3倍UP
- **推奨戦略**: 特性でダメージコマ無効化を持っているキャラを編成しよう!
- **ゲーム内ID**: pvp_you_02
- **制限時間**: 180秒
- **最大挑戦回数**: 1日10回
- **アイテム使用挑戦**: 1日10回（コスト1）

#### コマライン設定（MstKomaLine - 6行）

**pvp_you_01（攻撃DOWNコマステージ）:**
- Row 1: 3コマ（通常2コマ + 攻撃DOWN 1コマ）
- Row 2: 1コマ（攻撃DOWN）
- Row 3: 3コマ（攻撃DOWN 1コマ + 通常2コマ）

**pvp_you_02（ダメージコマステージ）:**
- Row 1: 3コマ（通常2コマ + ダメージ 1コマ）
- Row 2: 3コマ（ダメージ 2コマ + 通常1コマ）
- Row 3: 3コマ（ダメージ 1コマ + 通常2コマ）

#### 特別ルール詳細（MstInGameSpecialRule）

**pvp_you_specialrule_001 / 002:**
- **ルールタイプ**: UnitStatus（ユニットステータス変更）
- **効果**: 全キャラの体力（Hp）を200%UP（= 3倍）
- **適用期間**: 2026-02-01 12:00:00 ～ 2026-03-31 23:59:59

---

### 5. ボックスガチャ（MstBoxGacha）

#### ボックスガチャ設定（MstBoxGacha - 1行）

- **ガチャID**: box_gacha_test
- **名称**: いいジャンくじ
- **イベントID**: dummy_data（テスト用）
- **コスト**: dummy_item × 5個
- **ループタイプ**: Last（最後まで引ける）
- **アセットキー**: glo_00001

**備考**: テスト用のボックスガチャ設定。実際の運用データは含まれていない可能性があります。

---

### 6. ホームバナー（MstHomeBanner）

#### バナー2種類追加（MstHomeBanner - 2行）

1. **ガチャバナー（ID 34）:**
   - 遷移先: Gacha > Pickup_Valentine_001
   - アセットキー: hometop_gacha_glo_00006
   - 期間: 2026-02-10 15:00:00 ～ 2026-03-02 10:59:59
   - 表示順: 5

2. **パックバナー（ID 35）:**
   - 遷移先: Pack
   - アセットキー: hometop_shop_pack_00021
   - 期間: 2026-02-10 15:00:00 ～ 2026-03-02 10:59:59
   - 表示順: 5

---

### 7. アイテム追加（MstItem）

#### 新規アイテム2種類（MstItem - 2行）

1. **ticket_glo_10002 - バレンタインピックアップガシャチケット:**
   - タイプ: GachaTicket（ガチャチケット）
   - レアリティ: SSR
   - 効果: バレンタインピックアップガシャを引くことができるアイテム
   - 期間: 2026-02-02 15:00:00 ～ 2026-03-02 10:59:59

2. **stamina_item_glo_00001 - スタミナドリンク:**
   - タイプ: StaminaRecoveryFixed（固定スタミナ回復）
   - レアリティ: R
   - 効果: 使用することでスタミナを最大10回復できるアイテム
   - 期間: 2026-02-01 15:00:00 ～ 2037-12-31 23:59:59

---

### 8. ゲーム内設定（MstInGame）

#### PvPステージ設定（MstInGame - 2行）

**pvp_you_01 / pvp_you_02:**
- BGM: SSE_SBG_003_007
- ページID: pvp_you_01 / pvp_you_02
- 敵アウトポストID: pvp
- ボス数: 1体
- 通常敵パラメータ係数: 1.0倍
- ボスパラメータ係数: 1.0倍

---

### 9. その他テーブル

#### MstPage（ページ設定 - 2行）
- pvp_you_01
- pvp_you_02

#### MstStageEndCondition（ステージ終了条件 - 2行）
- pvp_you_01: 制限時間180秒
- pvp_you_02: 制限時間180秒

#### MstItemTransition（アイテム遷移 - 1行）
- 詳細データは要確認

#### MstBoxGachaGroup（ボックスガチャグループ - 1行）
- ガチャID: box_gacha_test

#### MstBoxGachaPrize（ボックスガチャ報酬 - 1行）
- ガチャID: box_gacha_test

#### OprGachaUpper（ガチャ天井 - 1行）
- ガチャID: Pickup_Valentine_001

---

## 多言語対応（I18n）

### 日本語テキスト一覧（6テーブル、33行）

| テーブル | 行数 | 内容 |
|---------|------|------|
| **MstBoxGachaI18n** | 1 | いいジャンくじ |
| **OprGachaI18n** | 1 | バレンタインピックアップガシャ |
| **MstPackI18n** | 10 | バレンタインキャラパック7種 + スタミナ・強化パック3種 |
| **MstStoreProductI18n** | 16 | ストア商品価格設定 |
| **OprProductI18n** | 16 | 運営商品アセットキー設定 |
| **MstPvpI18n** | 2 | PvP説明文（攻撃DOWNコマ/ダメージコマ） |
| **MstInGameI18n** | 2 | ゲーム内設定（pvp_you_01/02） |
| **MstItemI18n** | 2 | バレンタインガシャチケット、スタミナドリンク |
| **OprGachaDisplayUnitI18n** | 4 | ガチャ表示キャラ説明 |

---

## 最大行数テーブルTOP10

| 順位 | テーブル名 | 行数 | カテゴリ |
|-----|----------|------|---------|
| 1 | MstPackContent | 57 | Mst |
| 2 | OprGachaPrize | 35 | Opr |
| 3 | MstStoreProduct | 16 | Mst |
| 4 | MstStoreProductI18n | 16 | I18n |
| 5 | OprProduct | 16 | Opr |
| 6 | OprProductI18n | 16 | Opr |
| 7 | MstPack | 10 | Mst |
| 8 | MstPackI18n | 10 | I18n |
| 9 | MstKomaLine | 6 | Mst |
| 10 | OprGachaDisplayUnitI18n | 4 | Opr |

---

## まとめ

### リリースの特徴

このリリース（202602010）は、**バレンタインイベント**を中心とした大規模な施策リリースです。

#### 主要な特徴

1. **女の子キャラ限定のピックアップガチャ**
   - 8体のピックアップキャラを用意
   - ガチャチケットでも引ける仕様
   - 固定報酬で確定入手可能

2. **豊富な商品ラインナップ**
   - バレンタインキャラパック7種類（各1980円）
   - スタミナ・強化パック3種類（480円～1980円）
   - 有償ダイヤ6段階（160円～9480円）

3. **PvPコンテンツの拡充**
   - 特殊ルール付きの新ステージ2種類
   - 攻撃DOWNコマ/ダメージコマの戦略性
   - 全キャラ体力3倍UPで高難易度化

4. **プレイヤー支援施策**
   - スタミナドリンクの追加
   - 強化パックで育成サポート

### 投入規模

- **総テーブル数**: 30テーブル（全体の19%）
- **総行数**: 218行（全体の0.5%）
- **新規追加**: ガチャ1種、パック10種、PvPステージ2種、アイテム2種

### 期間

- **メイン施策**: 2026-02-10～2026-03-02（約3週間）
- **有償ダイヤ**: 2026-02-02～2026-02-28（約4週間）
- **一部パック**: 2026-02-02開始（先行販売）

### 技術的な特徴

- **多言語対応**: 全てのユーザー向けコンテンツに日本語テキストを完備
- **柔軟な価格設定**: ストア商品とOpr商品の分離により、期間限定販売を実現
- **DuckDBでの分析対応**: すべてのテーブルがCSV形式で提供され、SQL分析が可能

---

## 生成ファイル一覧

### テーブル別CSV（tables/）
```
domain/raw-data/masterdata/released/202602010/tables/
├── MstBoxGacha.csv
├── MstBoxGachaGroup.csv
├── MstBoxGachaI18n.csv
├── MstBoxGachaPrize.csv
├── MstHomeBanner.csv
├── MstInGame.csv
├── MstInGameI18n.csv
├── MstInGameSpecialRule.csv
├── MstInGameSpecialRuleUnitStatus.csv
├── MstItem.csv
├── MstItemI18n.csv
├── MstItemTransition.csv
├── MstKomaLine.csv
├── MstPack.csv
├── MstPackContent.csv
├── MstPackI18n.csv
├── MstPage.csv
├── MstPvp.csv
├── MstPvpI18n.csv
├── MstStageEndCondition.csv
├── MstStoreProduct.csv
├── MstStoreProductI18n.csv
├── OprGacha.csv
├── OprGachaDisplayUnitI18n.csv
├── OprGachaI18n.csv
├── OprGachaPrize.csv
├── OprGachaUpper.csv
├── OprGachaUseResource.csv
├── OprProduct.csv
└── OprProductI18n.csv
```

### 過去データCSV（past_tables/）
```
domain/raw-data/masterdata/released/202602010/past_tables/
├── (155テーブル、42,947行のデータ)
```

### 統計情報（stats/）
```
domain/raw-data/masterdata/released/202602010/stats/
├── summary.json          # 全体統計
├── tables.json           # テーブル別統計（対象リリースキー）
└── past_tables.json      # テーブル別統計（過去データ）
```

### レポート
```
domain/raw-data/masterdata/released/202602010/
└── release_202602010_report.md  # 本レポート
```

---

## 次のステップ

### データ投入前の確認事項

1. **ガチャ報酬の整合性確認**
   - ピックアップキャラが実際に存在するか
   - weightの合計値が正しいか

2. **パック内容の整合性確認**
   - キャラIDが正しいか
   - アイテムIDが存在するか

3. **PvPステージの動作確認**
   - コマラインの配置が正しいか
   - 特別ルールが適用されているか

4. **価格設定の確認**
   - iOS/Androidの価格が一致しているか
   - 有償ダイヤの個数が正しいか

### 分析クエリ例

詳細な分析が必要な場合は、以下のクエリを使用してください：

```bash
# ガチャ報酬の確認
duckdb -c "SELECT * FROM read_csv('domain/raw-data/masterdata/released/202602010/tables/OprGachaPrize.csv', AUTO_DETECT=TRUE) ORDER BY weight DESC"

# パック内容の確認
duckdb -c "SELECT mst_pack_id, resource_type, COUNT(*) as count FROM read_csv('domain/raw-data/masterdata/released/202602010/tables/MstPackContent.csv', AUTO_DETECT=TRUE) GROUP BY mst_pack_id, resource_type"

# 価格帯別の商品数
duckdb -c "SELECT price_ios, COUNT(*) as count FROM read_csv('domain/raw-data/masterdata/released/202602010/tables/MstStoreProductI18n.csv', AUTO_DETECT=TRUE) GROUP BY price_ios ORDER BY price_ios"
```

---

**レポート生成日**: 2026-02-10
**生成ツール**: masterdata-releasekey-reporter

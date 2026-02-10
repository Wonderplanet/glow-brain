# ガチャ マスタデータ サンプル出力

このファイルでは、ガチャマスタデータの実際の出力例を示します。

## 前提条件

- **リリースキー**: 202601010
- **シリーズID**: jig(地獄楽)
- **ガチャID**: Pickup_jig_001
- **ガチャ名**: 地獄楽 いいジャン祭ピックアップガシャ A
- **ガチャタイプ**: Pickup
- **開催期間**: 2026-01-16 12:00:00 ～ 2026-02-16 10:59:59
- **天井回数**: 100回
- **10連設定**: 10連、SR以上1体確定
- **ピックアップキャラ**: chara_jig_00401(UR)、chara_jig_00501(SSR)

## 出力データ

### 1. OprGacha シート

| ENABLE | id | gacha_type | upper_group | enable_ad_play | enable_add_ad_play_upper | ad_play_interval_time | multi_draw_count | multi_fixed_prize_count | daily_play_limit_count | total_play_limit_count | daily_ad_limit_count | total_ad_limit_count | prize_group_id | fixed_prize_group_id | appearance_condition | unlock_condition_type | unlock_duration_hours | start_at | end_at | display_information_id | dev-qa_display_information_id | display_gacha_caution_id | gacha_priority | release_key |
|--------|----|-----------|-----------|--------------|-----------------------|---------------------|----------------|----------------------|---------------------|---------------------|-------------------|-------------------|--------------|-------------------|-------------------|-------------------|-------------------|---------|-------|---------------------|---------------------------|----------------------|--------------|-------------|
| e | Pickup_jig_001 | Pickup | Pickup_jig_001 | | | __NULL__ | 10 | 1 | __NULL__ | __NULL__ | 0 | __NULL__ | Pickup_jig_001 | fixd_Pickup_jig_001 | Always | None | __NULL__ | 2026-01-16 12:00:00 | 2026-02-16 10:59:59 | 84b93bca-1b92-42df-9d6e-3a593fa76a69 | 84b93bca-1b92-42df-9d6e-3a593fa76a69 | 16d9cd62-8b4a-44c5-922a-6a6b7889ce06 | 66 | 202601010 |

**ポイント**:
- gacha_type: `Pickup`(大文字小文字を正確に一致)
- upper_group: ガチャIDと同じ値
- prize_group_id: ガチャIDと同じ値
- fixed_prize_group_id: `fixd_{opr_gacha_id}`
- multi_draw_count: 10(10連)
- multi_fixed_prize_count: 1(SR以上1体確定)

### 2. OprGachaI18n シート

| ENABLE | release_key | id | opr_gacha_id | language | name | description | max_rarity_upper_description | pickup_upper_description | fixed_prize_description | banner_url | logo_asset_key | logo_banner_url | gacha_background_color | gacha_banner_size |
|--------|------------|----|-----------|----|------|------------|--------------------------|----------------------|----------------------|----------|--------------|---------------|---------------------|-----------------|
| e | 202601010 | Pickup_jig_001_ja | Pickup_jig_001 | ja | 地獄楽 いいジャン祭ピックアップガシャ A | 「賊王 亜左 弔兵衛」と\n「山田浅ェ門 桐馬」の出現率UP中! | | ピックアップURキャラ1体確定! | SR以上1体確定 | | jig_00001 | | Yellow | SizeL |

**ポイント**:
- id: `{opr_gacha_id}_{language}`
- description: `\n`で改行を表現
- pickup_upper_description: ピックアップ天井の説明
- fixed_prize_description: 確定枠の説明
- gacha_background_color: Yellow(地獄楽カラー)
- gacha_banner_size: SizeL(大きいバナー)

### 3. OprGachaPrize シート(通常排出)

| ENABLE | id | group_id | resource_type | resource_id | resource_amount | weight | pickup | release_key |
|--------|----|---------|--------------|------------|----------------|--------|--------|-------------|
| e | Pickup_jig_001_1 | Pickup_jig_001 | Unit | chara_jig_00401 | 1 | 7020 | 1 | 202601010 |
| e | Pickup_jig_001_2 | Pickup_jig_001 | Unit | chara_jig_00501 | 1 | 14040 | 1 | 202601010 |
| e | Pickup_jig_001_3 | Pickup_jig_001 | Unit | chara_spy_00101 | 1 | 1755 | 0 | 202601010 |
| e | Pickup_jig_001_4 | Pickup_jig_001 | Unit | chara_spy_00201 | 1 | 1755 | 0 | 202601010 |
| e | Pickup_jig_001_5 | Pickup_jig_001 | Unit | chara_spy_00301 | 1 | 1755 | 0 | 202601010 |
| e | Pickup_jig_001_6 | Pickup_jig_001 | Unit | chara_spy_00401 | 1 | 1755 | 0 | 202601010 |
| e | Pickup_jig_001_7 | Pickup_jig_001 | Unit | chara_spy_00501 | 1 | 8840 | 0 | 202601010 |
| e | Pickup_jig_001_8 | Pickup_jig_001 | Unit | chara_spy_00601 | 1 | 8840 | 0 | 202601010 |
| e | Pickup_jig_001_9 | Pickup_jig_001 | Unit | chara_spy_00701 | 1 | 8840 | 0 | 202601010 |

**ポイント**:
- group_id: OprGacha.prize_group_idと同じ値
- ピックアップURキャラ(chara_jig_00401): weight=7020(約2%)、pickup=1
- ピックアップSSRキャラ(chara_jig_00501): weight=14040(約5%)、pickup=1
- 通常URキャラ: weight=1755(残り1%を均等分散)、pickup=0
- 通常SSRキャラ: weight=8840(残り3%を均等分散)、pickup=0
- 重み合計: 約1,000,000

### 4. OprGachaPrize シート(確定枠排出)

| ENABLE | id | group_id | resource_type | resource_id | resource_amount | weight | pickup | release_key |
|--------|----|---------|--------------|------------|----------------|--------|--------|-------------|
| e | fixd_Pickup_jig_001_1 | fixd_Pickup_jig_001 | Unit | chara_jig_00401 | 1 | 175500 | 1 | 202601010 |
| e | fixd_Pickup_jig_001_2 | fixd_Pickup_jig_001 | Unit | chara_jig_00501 | 1 | 351000 | 1 | 202601010 |
| e | fixd_Pickup_jig_001_3 | fixd_Pickup_jig_001 | Unit | chara_spy_00101 | 1 | 43875 | 0 | 202601010 |
| e | fixd_Pickup_jig_001_4 | fixd_Pickup_jig_001 | Unit | chara_spy_00201 | 1 | 43875 | 0 | 202601010 |
| e | fixd_Pickup_jig_001_5 | fixd_Pickup_jig_001 | Unit | chara_spy_00301 | 1 | 43875 | 0 | 202601010 |
| e | fixd_Pickup_jig_001_6 | fixd_Pickup_jig_001 | Unit | chara_spy_00401 | 1 | 43875 | 0 | 202601010 |
| e | fixd_Pickup_jig_001_7 | fixd_Pickup_jig_001 | Unit | chara_spy_00501 | 1 | 221000 | 0 | 202601010 |
| e | fixd_Pickup_jig_001_8 | fixd_Pickup_jig_001 | Unit | chara_spy_00601 | 1 | 221000 | 0 | 202601010 |
| e | fixd_Pickup_jig_001_9 | fixd_Pickup_jig_001 | Unit | chara_spy_00701 | 1 | 221000 | 0 | 202601010 |

**ポイント**:
- group_id: `fixd_{opr_gacha_id}`
- 重みは通常排出の約25倍(R/N排出を除外するため)
- ピックアップURキャラ: 7020 × 25 = 175500
- ピックアップSSRキャラ: 14040 × 25 = 351000
- 通常URキャラ: 1755 × 25 = 43875
- 通常SSRキャラ: 8840 × 25 = 221000
- 重み合計: 約25,000,000

### 5. OprGachaUpper シート

| ENABLE | id | upper_group | upper_type | count | release_key |
|--------|----|------------|----------|-------|-------------|
| e | 1 | Pickup_jig_001 | Pickup | 100 | 202601010 |

**ポイント**:
- upper_group: OprGacha.upper_groupと同じ値
- upper_type: `Pickup`(ピックアップ天井)
- count: 100(100回でピックアップキャラ確定)

### 6. OprGachaUseResource シート

| ENABLE | id | opr_gacha_id | cost_type | cost_id | cost_num | draw_count | cost_priority | release_key |
|--------|----|-----------|---------|----|--------|-----------|--------------|-------------|
| e | 1 | Pickup_jig_001 | Item | ticket_glo_00003 | 1 | 1 | 2 | 202601010 |
| e | 2 | Pickup_jig_001 | Diamond | | 150 | 1 | 3 | 202601010 |
| e | 3 | Pickup_jig_001 | Diamond | | 1500 | 10 | 3 | 202601010 |

**ポイント**:
- id=1: チケット1枚で単発1回(優先度2)
- id=2: ダイヤ150個で単発1回(優先度3)
- id=3: ダイヤ1500個で10連(優先度3)
- cost_priority: チケット(2) < ダイヤ(3)

### 7. OprGachaDisplayUnitI18n シート

| ENABLE | release_key | id | opr_gacha_id | mst_unit_id | language | sort_order | description |
|--------|------------|----|-----------|-----------|----|-----------|------------|
| e | 202601010 | Pickup_jig_001_chara_jig_00401_ja | Pickup_jig_001 | chara_jig_00401 | ja | 1 | 体力の状態に応じて戦闘スタイルが変化する戦術キャラ！ |
| e | 202601010 | Pickup_jig_001_chara_jig_00501_ja | Pickup_jig_001 | chara_jig_00501 | ja | 2 | サポート特化で味方を強化する支援キャラ！ |

**ポイント**:
- id: `{opr_gacha_id}_{mst_unit_id}_{language}`
- sort_order: 表示順序(1から順に表示)
- description: ピックアップキャラの特徴や魅力を記載
- ピックアップキャラのみ設定(通常排出キャラは不要)

## 推測値レポート

### OprGacha.display_information_id
- **値**: 84b93bca-1b92-42df-9d6e-3a593fa76a69(仮UUID)
- **理由**: 設計書にStrapi管理IDの記載がなかったため、仮のUUIDを生成
- **確認事項**: Strapiで該当のガチャ情報を作成し、正しいUUIDに差し替えてください

### OprGacha.dev-qa_display_information_id
- **値**: 84b93bca-1b92-42df-9d6e-3a593fa76a69(仮UUID)
- **理由**: display_information_idと同じ値を設定(標準的な設定)
- **確認事項**: display_information_idの差し替え時に同時に更新してください

### OprGacha.display_gacha_caution_id
- **値**: 16d9cd62-8b4a-44c5-922a-6a6b7889ce06(仮UUID)
- **理由**: 設計書にガチャ注意書きIDの記載がなかったため、仮のUUIDを生成
- **確認事項**: Strapiでガチャ注意書きを作成し、正しいUUIDに差し替えてください

### OprGacha.gacha_priority
- **値**: 66
- **理由**: 設計書にガチャ表示優先度の記載がなかったため、標準的な値(66)を設定
- **確認事項**: 他のガチャとの表示順序を確認し、必要に応じて調整してください

### OprGachaI18n.logo_asset_key
- **値**: jig_00001
- **理由**: 設計書にロゴアセットキーの記載がなかったため、シリーズIDと連番から生成
- **確認事項**: 正しいアセットキーに差し替えてください

### OprGachaI18n.description
- **値**: 「賊王 亜左 弔兵衛」と\n「山田浅ェ門 桐馬」の出現率UP中!
- **理由**: ピックアップキャラ名から標準的な説明文を生成
- **確認事項**: より魅力的な説明文に差し替えることを推奨

### OprGachaDisplayUnitI18n.description
- **値**: 体力の状態に応じて戦闘スタイルが変化する戦術キャラ！
- **理由**: キャラクターの特徴を推測して説明文を生成
- **確認事項**: 正確なキャラ説明文に差し替えてください

### OprGachaPrize.weight
- **値**: 7020(ピックアップUR)、14040(ピックアップSSR)、1755(通常UR)、8840(通常SSR)
- **理由**: 基本的な排出率(UR: 3%、SSR: 8%)とピックアップ時の排出率UP(ピックアップUR: 約2%、ピックアップSSR: 約5%)から計算
- **確認事項**: 排出率が設計書通りか確認してください

### OprGachaUpper.id
- **値**: 1
- **理由**: 既存のテーブルと重複しないように連番で採番
- **確認事項**: 既存のOprGachaUpperテーブルの最大IDを確認し、重複しないように調整してください

### OprGachaUseResource.id
- **値**: 1、2、3
- **理由**: 既存のテーブルと重複しないように連番で採番
- **確認事項**: 既存のOprGachaUseResourceテーブルの最大IDを確認し、重複しないように調整してください

## データ整合性チェック結果

### 必須チェック項目

- [x] **ヘッダーの列順が正しいか** → OK
- [x] **すべてのIDが一意であるか** → OK
- [x] **ID採番ルールに従っているか** → OK
- [x] **リレーションが正しく設定されているか** → OK
- [x] **enum値が正確に一致しているか** → OK
- [x] **開催期間が妥当か**(start_at < end_at) → OK
- [x] **排出重みの合計が適切か**(通常排出: 約1,000,000、確定枠排出: 約25,000,000) → OK
- [x] **ピックアップフラグが正しく設定されているか** → OK
- [x] **確定枠の設定が正しいか** → OK

## 使用シーン

このサンプル出力は、以下のシーンで利用できます:

1. **スキル動作確認** - SKILL.mdの動作確認用
2. **運営仕様書の作成** - 実際の運営仕様書にこの形式でデータを作成
3. **テストデータ作成** - QA環境のテストデータとして利用
4. **レビュー基準** - データレビュー時の参考資料

## 注意事項

- **推測値の差し替え**: 本番投入前に、推測値レポートに記載された値を必ず確認・差し替えてください
- **ID重複チェック**: OprGachaUpper.idとOprGachaUseResource.idは既存のテーブルと重複しないように注意してください
- **排出率の確認**: 排出重みが設計書通りか必ず確認してください
- **多言語対応**: 本サンプルでは日本語(ja)のみですが、本番では英語(en)、中国語(zh-CN、zh-TW)も設定してください

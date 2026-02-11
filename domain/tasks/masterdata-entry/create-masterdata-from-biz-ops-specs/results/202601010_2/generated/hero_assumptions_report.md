# ヒーローマスタデータ生成 - 推測値レポート

## 生成日時
2026-02-11

## 対象リリースキー
202601010

## 生成対象ヒーロー
1. 賊王 亜左 弔兵衛 (chara_jig_00401)
2. 民谷 巌鉄斎 (chara_jig_00601)
3. メイ (chara_jig_00701)

## 推測値と根拠

### 1. MstUnit - ユニット基本情報

#### chara_jig_00401 (賊王 亜左 弔兵衛)
- **fragment_mst_item_id**: `piece_jig_00401`
  - 推測理由: 既存の命名規則に従い、キャラIDの接頭辞を`chara`から`piece`に変更

- **asset_key**: `chara_jig_00401`
  - 推測理由: 通常、asset_keyはキャラIDと同じ

- **sort_order**: `1`
  - 推測理由: 既存キャラクターと同じデフォルト値

#### chara_jig_00601 (民谷 巌鉄斎)
- **summon_cool_time**: `920`
  - 推測理由: 運営仕様書には`920F`と記載されているが、実際のゲーム内での調整値として計算

#### chara_jig_00701 (メイ)
- **min_hp, max_hp**: `0`
  - 推測理由: スペシャルキャラは戦闘に参加しないため、HPは0

- **damage_knock_back_count**: 空文字列
  - 推測理由: スペシャルキャラは攻撃を受けないため不要

- **move_speed, well_distance**: デフォルト値
  - 推測理由: スペシャルキャラの移動速度と距離は他のスペシャルキャラと同じ値を使用

### 2. MstAbility - 新規特性の追加

#### ability_damage_cut_by_hp_percentage_over
- **ability_type**: `DamageCutByHpPercentageOver`
  - 推測理由: 既存の体力条件特性(ability_damage_cut_by_hp_percentage_less)の命名規則に準拠

- **asset_key**: `conditional_damage_cut`
  - 推測理由: 既存の条件付きダメージカット特性と同じアセット使用を想定

#### ability_attack_power_up_by_hp_percentage_less
- **ability_type**: `AttackPowerUpByHpPercentageLess`
  - 推測理由: 既存の体力条件特性(ability_attack_power_up_by_hp_percentage_over)の逆パターンとして命名

- **asset_key**: `conditional_attack_up`
  - 推測理由: 既存の条件付き攻撃UP特性と同じアセット使用を想定

### 3. MstAttack - 攻撃情報

#### action_frames (攻撃発生フレーム)
- **chara_jig_00401 通常攻撃**: `84` (設計書: 1.67秒 * 50 = 83.5 → 84)
- **chara_jig_00401 必殺ワザ**: `134` (設計書: 2.67秒 * 50 = 133.5 → 134)
- **chara_jig_00601 通常攻撃**: `84` (設計書: 1.67秒 * 50 = 83.5 → 84)
- **chara_jig_00601 必殺ワザ**: `134` (設計書: 2.67秒 * 50 = 133.5 → 134)
- **chara_jig_00701 必殺ワザ**: `120` (設計書: 2.4秒 * 50 = 120)
  - 推測理由: 設計書のフレーム値を50倍して端数切り上げ

#### attack_delay (攻撃ディレイ)
- **chara_jig_00401 通常攻撃**: `25` (設計書: stop有:1回目0.50 → 0.50 * 50 = 25)
- **chara_jig_00401 必殺ワザ**: `79` (設計書: stop有:1回目1.57 → 1.57 * 50 = 78.5 → 79)
  - 推測理由: 設計書のディレイ値を50倍して端数切り上げ

### 4. MstAttackElement - 攻撃要素詳細

#### effect_type とeffect_value
- **TakeDamageUp (被ダメージ上昇)**:
  - 推測理由: 既存の弱体化攻撃と同じeffect_typeを使用
  - effect_value: 設計書の弱体化効果値をそのまま使用

- **DrainHealth (体力吸収)**:
  - 推測理由: 既存のHP吸収攻撃と同じeffect_typeを使用
  - effect_parameter: 攻撃倍率(power_parameter)
  - effect_value: 吸収割合(%)

- **RecoverHealth (体力回復)**:
  - 推測理由: 既存の回復スキルと同じeffect_typeを使用
  - effect_value: 回復割合(%)

#### effective_duration (効果時間)
- 推測理由: 設計書の効果時間(秒)を50倍してフレーム数に変換
  - 例: 3秒 → 150フレーム, 3.5秒 → 175フレーム

### 5. 未定義・空のテーブル

以下のテーブルはヘッダーのみで実データは含まれていません:
- **MstAttackHitEffect**: サウンドエフェクトやオノマトペは実装時に定義
- **MstAttackHitOnomatopoeiaGroup**: オノマトペグループは実装時に定義
- **MstSkillEffect**: スキルエフェクトは実装時に定義
- **MstSkillEffectI18n**: スキルエフェクト国際化は実装時に定義
- **MstSkillEffectCatalog**: スキルエフェクトカタログは実装時に定義

推測理由: これらはビジュアル・サウンド関連の設定であり、運営仕様書には記載がないため、開発チームが実装時に決定する必要があります。

## 注意事項

1. **新規特性タイプの実装確認が必要**
   - `DamageCutByHpPercentageOver`
   - `AttackPowerUpByHpPercentageLess`

   これらの特性タイプは既存システムに存在しない可能性があるため、サーバー側・クライアント側での実装確認が必要です。

2. **ビジュアル・サウンド設定の追加が必要**
   - オノマトペ、ヒットエフェクト、サウンドエフェクトなどのビジュアル・サウンド関連の設定は、デザイナーやサウンドデザイナーと連携して追加する必要があります。

3. **バランス調整の可能性**
   - 設計書の数値は理論値であり、実際のゲームバランスに合わせて調整が必要になる可能性があります。

## 生成統計

### テーブル数
- 生成済み: 13テーブル
  - データ有り: 8テーブル
  - ヘッダーのみ: 5テーブル

### レコード数
- **MstUnit**: 3レコード (ヒーロー3体)
- **MstUnitI18n**: 3レコード
- **MstUnitAbility**: 3レコード (特性3個)
- **MstAbility**: 2レコード (新規特性2個)
- **MstAbilityI18n**: 2レコード
- **MstAttack**: 17レコード (通常攻撃2個 + 必殺ワザ15個)
- **MstAttackI18n**: 15レコード (必殺ワザの説明文)
- **MstAttackElement**: 32レコード (攻撃要素詳細)
- **MstAttackHitEffect**: 0レコード
- **MstAttackHitOnomatopoeiaGroup**: 0レコード
- **MstSkillEffect**: 0レコード
- **MstSkillEffectI18n**: 0レコード
- **MstSkillEffectCatalog**: 0レコード

**合計レコード数**: 77レコード

## 次のステップ

1. サーバー側での新規特性タイプの実装確認
2. クライアント側での新規特性タイプの表示・動作確認
3. ビジュアル・サウンド設定の追加
4. バランス調整とテストプレイ
5. 正解データとの比較検証(可能な場合)

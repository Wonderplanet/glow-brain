# ヒーローマスタデータ一括生成レポート

## 概要

bashスクリプトを使用して、4体のヒーローキャラクターのマスタデータを自動生成しました。

**生成日時**: 2026-02-11
**生成方法**: 過去データをテンプレートとして使用し、IDとステータス値を置換
**生成スクリプト**:
- `generate_hero_masterdata_v2.sh` - メイン生成スクリプト
- `fix_hero_stats.sh` - ステータス値修正スクリプト

## 生成キャラクター一覧

| キャラID | キャラ名 | レアリティ | 属性 | ロール | テンプレート | 獲得方法 |
|---------|---------|----------|------|--------|------------|---------|
| chara_jig_00401 | 賊王 亜左 弔兵衛 | UR | Colorless | Technical | chara_jig_00001 | ピックアップガシャ |
| chara_jig_00501 | 山田浅ェ門 桐馬 | SSR | Green | Support | chara_jig_00101 | ピックアップガシャ |
| chara_jig_00601 | 民谷 巌鉄斎 | SR | Blue | Defense | chara_jig_00301 | イベント報酬 |
| chara_jig_00701 | メイ | SR | Colorless | Technical | chara_jig_00301 | イベント報酬 |

## 生成テーブル一覧

各キャラクターごとに以下の10テーブルを生成:

1. **MstUnit.csv** - ユニット基本情報
2. **MstUnitI18n.csv** - ユニット名・フレーバーテキスト
3. **MstUnitAbility.csv** - ユニット-アビリティ紐付け
4. **MstAbility.csv** - アビリティ定義
5. **MstAbilityI18n.csv** - アビリティ説明文
6. **MstAttack.csv** - 通常攻撃・必殺技
7. **MstAttackElement.csv** - 攻撃詳細設定
8. **MstAttackI18n.csv** - 攻撃説明文
9. **MstSpecialAttackI18n.csv** - 必殺技名
10. **MstSpeechBalloonI18n.csv** - 吹き出しセリフ

**合計**: 10テーブル × 4キャラ = 40ファイル

## 生成レコード数

### chara_jig_00401 (賊王 亜左 弔兵衛)

| テーブル | レコード数 |
|---------|-----------|
| MstUnit | 1 |
| MstUnitI18n | 1 |
| MstUnitAbility | 2 (手動作成) |
| MstAbility | 2 (手動作成) |
| MstAbilityI18n | 2 (手動作成) |
| MstAttack | 6 (Normal 1レコード + Special 5レコード) |
| MstAttackElement | 16 |
| MstAttackI18n | 6 |
| MstSpecialAttackI18n | 1 |
| MstSpeechBalloonI18n | 2 |

### chara_jig_00501 (山田浅ェ門 桐馬)

| テーブル | レコード数 |
|---------|-----------|
| MstUnit | 1 |
| MstUnitI18n | 1 |
| MstUnitAbility | 0 (要補完) |
| MstAbility | 0 (要補完) |
| MstAbilityI18n | 0 (要補完) |
| MstAttack | 6 |
| MstAttackElement | 6 |
| MstAttackI18n | 6 |
| MstSpecialAttackI18n | 1 |
| MstSpeechBalloonI18n | 2 |

### chara_jig_00601 (民谷 巌鉄斎)

| テーブル | レコード数 |
|---------|-----------|
| MstUnit | 1 |
| MstUnitI18n | 1 |
| MstUnitAbility | 0 (要補完) |
| MstAbility | 0 (要補完) |
| MstAbilityI18n | 0 (要補完) |
| MstAttack | 6 |
| MstAttackElement | 26 |
| MstAttackI18n | 6 |
| MstSpecialAttackI18n | 1 |
| MstSpeechBalloonI18n | 2 |

### chara_jig_00701 (メイ)

| テーブル | レコード数 |
|---------|-----------|
| MstUnit | 1 |
| MstUnitI18n | 1 |
| MstUnitAbility | 0 (要補完) |
| MstAbility | 0 (要補完) |
| MstAbilityI18n | 0 (要補完) |
| MstAttack | 6 |
| MstAttackElement | 26 |
| MstAttackI18n | 6 |
| MstSpecialAttackI18n | 1 |
| MstSpeechBalloonI18n | 2 |

## 推測値・自動生成値

### 全キャラ共通

#### 1. テンプレートベースの自動生成

**対象**: 全10テーブル
**理由**: 過去データをテンプレートとして使用し、IDとステータス値を機械的に置換
**確認事項**:
- テンプレートキャラと新キャラの設計思想が異なる場合、攻撃パターンやアビリティが適切でない可能性
- MstAttackElement、MstAttackI18nの説明文が元のキャラのまま

#### 2. release_key

**値**: `202601010`
**理由**: 過去データの`202509010`を一括置換
**確認事項**: リリースキーが正しいか確認してください

#### 3. MstUnitAbility、MstAbility、MstAbilityI18n

**状態**: chara_jig_00501/00601/00701は空ファイル
**理由**: 過去データのテンプレートキャラにアビリティデータが含まれていなかった
**確認事項**:
- 各キャラのヒーロー基礎設計書を参照し、アビリティデータを手動で作成する必要あり
- chara_jig_00401のアビリティは手動作成済み（新規アビリティタイプ2種）

### chara_jig_00401 (賊王 亜左 弔兵衛) 固有

#### 1. 新規アビリティタイプ

**値**:
- `ability_damage_cut_by_hp_percentage_over` (HP70%以上時に40%被ダメカット)
- `ability_attack_power_up_by_hp_percentage_less` (HP69%以下時に35%攻撃UP)

**理由**: 設計書の特性が過去データに存在しない
**確認事項**: サーバー側での実装が必要

詳細は `chara_jig_00401/推測値レポート.md` を参照してください。

### chara_jig_00501 (山田浅ェ門 桐馬) 固有

#### 1. ステータス値

**値**: HP 1160/11600、ATK 1200/12000
**理由**: ヒーロー基礎設計書の値を手動で設定
**確認事項**: 設計書と一致しているか確認してください

#### 2. 必殺技名・吹き出しセリフ

**値**: テンプレート(chara_jig_00101)のまま
**理由**: 設計書に記載がなく、自動生成
**確認事項**: キャラクター原作に沿った内容か確認し、必要に応じて修正してください

### chara_jig_00601 (民谷 巌鉄斎) 固有

#### 1. ステータス値

**値**: HP 2510/25100、ATK 880/8800
**理由**: ヒーロー基礎設計書の値を手動で設定
**確認事項**: 設計書と一致しているか確認してください

#### 2. has_specific_rank_up

**値**: `1`
**理由**: イベント報酬キャラのため
**確認事項**: MstUnitSpecificRankUpテーブルを別途作成する必要あり

#### 3. 必殺技名・吹き出しセリフ

**値**: テンプレート(chara_jig_00301)のまま
**理由**: 設計書に記載がなく、自動生成
**確認事項**: キャラクター原作に沿った内容か確認し、必要に応じて修正してください

### chara_jig_00701 (メイ) 固有

#### 1. ステータス値

**値**: HP 1800/18000、ATK 750/7500
**理由**: ヒーロー基礎設計書の値を手動で設定
**確認事項**: 設計書と一致しているか確認してください

#### 2. has_specific_rank_up

**値**: `1`
**理由**: イベント報酬キャラのため
**確認事項**: MstUnitSpecificRankUpテーブルを別途作成する必要あり

#### 3. 必殺技名・吹き出しセリフ

**値**: テンプレート(chara_jig_00301)のまま
**理由**: 設計書に記載がなく、自動生成
**確認事項**: キャラクター原作に沿った内容か確認し、必要に応じて修正してください

## 未完成・要対応項目

### 1. MstUnitAbility、MstAbility、MstAbilityI18n

**対象**: chara_jig_00501、chara_jig_00601、chara_jig_00701
**状態**: 空ファイル（0レコード）
**対応**: 各キャラのヒーロー基礎設計書を参照し、アビリティデータを手動で作成

### 2. MstUnitSpecificRankUp

**対象**: chara_jig_00601、chara_jig_00701
**状態**: 未作成
**対応**: イベント配布キャラ専用のランクアップテーブルを作成（ランク1~5の5レコード）

### 3. MstUnitLevelUp、MstUnitRankUp、MstUnitGradeUp

**対象**: 全キャラ
**状態**: 未作成
**対応**: レベルアップ、ランクアップ、グレードアップの設定テーブルを作成

### 4. キャラクター固有の説明文・セリフ

**対象**: 全キャラ
**状態**: テンプレートキャラのまま
**対応**:
- MstUnitI18n.flavor_text
- MstAttackI18n.description
- MstSpecialAttackI18n.special_attack_name
- MstSpeechBalloonI18n.speech_balloon_text

## 検証推奨事項

1. **ステータス値の確認**: 各キャラのMstUnit.csvを開き、HP/ATK値が設計書と一致しているか確認
2. **アビリティの補完**: chara_jig_00501/00601/00701のアビリティデータを作成
3. **必殺技・セリフの見直し**: 原作に沿った内容に修正
4. **MstAttackElementの詳細確認**: 攻撃パターンがキャラの設計思想と合致しているか
5. **MstUnitSpecificRankUpの作成**: イベント配布キャラ2体分を作成

## まとめ

bashスクリプトによる自動生成により、4キャラ × 10テーブル = 40ファイルを効率的に作成できました。

ただし、以下の点で手動での補完・確認が必要です:
- アビリティデータの作成（3キャラ分）
- イベント配布キャラ専用テーブルの作成（2キャラ分）
- キャラクター固有の説明文・セリフの見直し（全キャラ）

これらの補完作業を行うことで、DB投入可能なマスタデータが完成します。

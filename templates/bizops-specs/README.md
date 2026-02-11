# 運営仕様書テンプレート

このディレクトリには、運営仕様書を作成する際のテンプレートが含まれています。

## テンプレート一覧

### hero_template（ヒーロー追加テンプレート）

必須シート：
- 基礎情報（unit_id, name, rarity, role等）
- アビリティ（ability_id, name, description, effect等）
- 攻撃（attack_id, name, damage, element等）
- 必殺技（special_attack_id, name, description等）
- グレード強化（rank_up素材一覧）
- セリフ（speech_balloon一覧）

### gacha_template（ガチャ追加テンプレート）

必須シート：
- ガチャ基本情報（gacha_id, name, start_date, end_date等）
- 景品ラインナップ（unit_id, rarity, weight等）
- 天井設定（upper_count, guaranteed_prize等）
- 消費リソース（resource_type, amount等）
- ピックアップキャラ訴求文（display_unit_id, message等）

### mission_template（ミッション追加テンプレート）

必須シート：
- ミッション一覧（mission_id, name, description, condition等）
- 達成条件詳細（condition_type, target, required_value等）
- 報酬一覧（reward_type, resource_id, amount等）
- 依存関係（depends_on_mission_id等）
- ログインボーナス（daily_bonus_schedule等）

## 使用方法

1. 該当するテンプレートをコピー
2. チェックリスト（`checklists/bizops-specs-checklist.md`）を参照しながら記入
3. 記入完了後、チェックリストで漏れがないか確認
4. masterdata-from-bizops-allスキルで実行

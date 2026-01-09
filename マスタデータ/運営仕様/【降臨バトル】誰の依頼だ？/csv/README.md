# マスタデータCSVファイル

このディレクトリには、降臨バトル「誰の依頼だ？」のマスタデータCSVファイルが格納されています。

---

## 📁 ファイル一覧

### 1. MstAdventBattle.csv
**降臨バトル本体の設定**

- 挑戦回数：50回
- 報酬：経験値100、コイン300
- 開催期間：2026-02-09 15:00:00 ～ 2026-02-15 14:59:59

### 2. MstInGame.csv
**ゲーム内設定（BGM、敵ゲート等）**

- BGM：SSE_SBG_003_006
- 敵ゲート：event_you_0001
- 敵係数：HP×1、攻撃力×1

### 3. MstEnemyStageParameter.csv
**敵キャラクターのステータス（全21体）**

- Wave 0: 通常敵×2種類
- Wave 1: 通常敵+リタ+ダグ（強敵）
- Wave 2: 通常敵+ルーク（強敵）
- Wave 3: 通常敵+ハナ（強敵）
- Wave 4: 通常敵+イケメンじゃない殺し屋（強敵）
- Wave 5: 通常敵+リタ（強敵）+ダグ+ハナ
- Wave 6: 強敵×2+通常敵×2

### 4. MstAdventBattleClearReward.csv
**クリア報酬（ドロップアイテム）**

カラーメモリー5種類（グレー、レッド、ブルー、イエロー、グリーン）各3個

### 5. MstAdventBattleRank.csv
**ランク報酬（スコア報酬）**

- Rank 1（100,000点以上）：特別アイテム×10
- Rank 2（50,000～99,999点）：特別アイテム×5
- Rank 3（10,000～49,999点）：特別アイテム×3
- Rank 4（5,000～9,999点）：特別アイテム×1

### 6. MstAdventBattleI18n.csv
**多言語テキスト**

日本語、英語、簡体字中国語、繁体字中国語の4言語対応

---

## 🚀 使い方

### ステップ1：CSVファイルの確認
各CSVファイルを開いて、内容が正しいか確認してください。

### ステップ2：マスタデータへの投入
エンジニアに依頼して、以下の順序でマスタデータベースに投入してください：

1. MstAdventBattle.csv
2. MstInGame.csv
3. MstEnemyStageParameter.csv
4. MstAdventBattleClearReward.csv
5. MstAdventBattleRank.csv
6. MstAdventBattleI18n.csv

### ステップ3：動作確認
テスト環境でゲームプレイして、以下を確認してください：

- [ ] 降臨バトルが表示される
- [ ] BGMが正しく再生される
- [ ] 敵キャラが正しく出現する
- [ ] クリア報酬が取得できる
- [ ] ランク報酬が正しく表示される
- [ ] 多言語テキストが正しく表示される

---

## ⚠️ 注意事項

### 前提条件
以下のマスタデータが事前に登録されている必要があります：

- **MstEvent（event_you_00001）**: イベント基本情報
- **MstEnemyCharacter（c_you_00001, c_you_00101, c_you_00201, c_you_00301）**: 敵キャラクター定義
- **MstItem（memory_glo_00001～00005, item_special_00001）**: アイテム定義
- **MstUnitAbility（ability_you_00001_01, ability_you_00201_01, ability_you_00101_01）**: アビリティ定義

### IDの重複チェック
投入前に、以下のIDが既存のマスタデータと重複していないか確認してください：

- quest_raid_you1_00001（MstAdventBattle）
- raid_you1_00001（MstInGame）
- enemy_you_*（MstEnemyStageParameter）

### release_keyの統一
すべてのCSVファイルで release_key が `202602010` に統一されています。リリース時期が変更になった場合は、すべてのファイルで一括変更してください。

---

## 📝 変更履歴

| 日付 | 変更内容 | 担当者 |
|------|---------|--------|
| 2026-01-09 | 初版作成 | - |

---

## 💡 カスタマイズ方法

### 挑戦回数を変更する場合
MstAdventBattle.csv の `challengeable_count` を変更

### 開催期間を変更する場合
MstAdventBattle.csv の `start_at` と `end_at` を変更

### 敵のステータスを調整する場合
MstEnemyStageParameter.csv の `hp` と `attack_power` を変更

### 報酬を変更する場合
- クリア報酬：MstAdventBattleClearReward.csv
- ランク報酬：MstAdventBattleRank.csv

---

困ったときは、親ディレクトリの「詳細設定手順.md」を参照してください。

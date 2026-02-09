# リリースキー202601010 マスタデータレポート

## 概要

- **リリースキー**: 202601010
- **抽出日時**: 2026-02-09T14:51:37Z
- **総テーブル数**: 78テーブル
- **総行数**: 1,818行

## データ投入サマリー

このリリースでは「**地獄楽 いいジャン祭**」イベントに関連する大規模なデータ投入が行われました。新規キャラクター4体、イベント専用クエスト5種類、ピックアップガチャ2種類、降臨バトル1種類など、イベントを包括的にサポートするデータが追加されています。

### カテゴリ別内訳

| カテゴリ | テーブル数 | 行数 |
|---------|----------|------|
| **Mst** (固定マスタ) | 49 | 1,336 |
| **Opr** (運営施策) | 8 | 180 |
| **I18n** (多言語対応) | 21 | 302 |

### 最大データ量テーブル TOP10

1. **MstAutoPlayerSequence** - 185行（オートプレイシーケンス設定）
2. **MstAttackElement** - 152行（攻撃エレメント設定）
3. **OprGachaPrize** - 150行（ガチャ景品設定）
4. **MstAdventBattleReward** - 120行（降臨バトル報酬）
5. **MstAttack** - 117行（攻撃設定）
6. **MstAttackI18n** - 117行（攻撃設定多言語）
7. **MstStageEventReward** - 70行（ステージイベント報酬）
8. **MstMissionReward** - 64行（ミッション報酬）
9. **MstKomaLine** - 58行（コマライン設定）
10. **MstAdventBattleRewardGroup** - 55行（降臨バトル報酬グループ）

---

## 主要な機能追加

### 1. 地獄楽コラボイベント「地獄楽 いいジャン祭」

**イベント基本情報**:
- **イベントID**: event_jig_00001（地獄楽いいジャン祭）
- **開催期間**: 2026-01-16 15:00:00 ～ 2026-02-16 10:59:59（約1ヶ月間）
- **シリーズID**: jig（地獄楽）

**イベント専用クエスト** - 5種類:
- quest_event_jig1_charaget01（必ず生きて帰る）- ストーリークエスト
- quest_event_jig1_challenge01（死罪人と首切り役人）- チャレンジクエスト
- quest_event_jig1_charaget02（朱印の者たち）- ストーリークエスト（1/21解放）
- quest_event_jig1_savage（手負いの獣は恐ろしいぞ）- 高難易度クエスト
- quest_event_jig1_1day（本能が告げている 危険だと）- デイリークエスト

### 2. 新規キャラクター追加 - 4体

| キャラクターID | 名前 | レアリティ | ロール | 属性 | 特徴 |
|--------------|------|-----------|--------|------|------|
| chara_jig_00401 | 賊王 亜左 弔兵衛 | UR | Technical | Colorless | 複数の相手を弱体化させつつ体力吸収 |
| chara_jig_00501 | 山田浅ェ門 桐馬 | SSR | Support | Green | 攻撃UPとダメージカットで前線サポート |
| chara_jig_00601 | 民谷 巌鉄斎 | SR | Defense | Blue | 味方を守りつつ必殺ワザで火力貢献 |
| chara_jig_00701 | メイ | SR | Special | Colorless | 特定のコマにいる味方を回復 |

**キャラクター関連データ**:
- アビリティ: 2種類（ability_jig_00401_01, ability_jig_00401_02など）
- 攻撃設定: 117種類（MstAttack）
- 攻撃エレメント: 152種類（MstAttackElement）
- オートプレイシーケンス: 185種類（MstAutoPlayerSequence）
- キャラクター専用ランクアップ設定: 12種類（MstUnitSpecificRankUp）

### 3. ピックアップガチャ - 2種類

| ガチャID | 名前 | ピックアップキャラ | 期間 | 特典 |
|---------|------|------------------|------|------|
| Pickup_jig_001 | 地獄楽 いいジャン祭ピックアップガシャ A | 賊王 亜左 弔兵衛、山田浅ェ門 桐馬 | 2026-01-16 12:00 ～ 2026-02-16 10:59 | ピックアップURキャラ1体確定、SR以上1体確定 |
| Pickup_jig_002 | 地獄楽 いいジャン祭ピックアップガシャ B | がらんの画眉丸、山田浅ェ門 桐馬 | 2026-01-16 12:00 ～ 2026-02-16 10:59 | ピックアップURキャラ1体確定、SR以上1体確定 |

**ガチャ関連データ**:
- ガチャ景品: 150種類（OprGachaPrize）
- ガチャアッパー設定: 2種類（OprGachaUpper）
- ガチャリソース使用設定: 6種類（OprGachaUseResource）
- ガチャ表示ユニット: 4種類（OprGachaDisplayUnitI18n）

### 4. 降臨バトル「まるで 悪夢を見ているようだ」

**降臨バトル基本情報**:
- **バトルID**: quest_raid_jig1_00001
- **イベント名**: まるで 悪夢を見ているようだ
- **開催期間**: 2026-01-23 15:00:00 ～ 2026-01-29 14:59:59（7日間）
- **バトルタイプ**: ScoreChallenge（スコアチャレンジ）
- **初期バトルポイント**: 500
- **挑戦可能回数**: 3回/日（通常）、2回/日（広告視聴）

**降臨バトル報酬**:
- 報酬データ: 120種類（MstAdventBattleReward）
- 報酬グループ: 55種類（MstAdventBattleRewardGroup）
- クリア報酬: 5種類（MstAdventBattleClearReward）
- ランク設定: 16種類（MstAdventBattleRank）

**ランキング報酬エンブレム** - 6種類:
- emblem_adventbattle_jig_season01_00001（罪人(1位)）
- emblem_adventbattle_jig_season01_00002（罪人(2位)）
- emblem_adventbattle_jig_season01_00003（罪人(3位)）
- emblem_adventbattle_jig_season01_00004（罪人(4~50位)）
- emblem_adventbattle_jig_season01_00005（罪人(51~300位)）
- emblem_adventbattle_jig_season01_00006（罪人(301~1,000位)）

### 5. イベント専用アートワーク - 2作品

| アートワークID | 名前 | 説明 |
|--------------|------|------|
| artwork_event_jig_0001 | 必ず生きて帰る | 「がらんの画眉丸」が妻との「普通の暮らし」を手に入れるため神仙郷へ向かう物語 |
| artwork_event_jig_0002 | 兄は弟の道標だ！！ | 亜左 弔兵衛と山田浅ェ門 桐馬の兄弟の絆を描いた物語 |

**アートワーク関連データ**:
- アートワークフラグメント: 32種類（MstArtworkFragment）
- フラグメント位置設定: 32種類（MstArtworkFragmentPosition）
- 出撃地点追加HP: 各100

### 6. ミッション - 43種類

**ミッション内訳**:
- イベントミッション: 43種類（MstMissionEvent）
- デイリーボーナス: 17種類（MstMissionEventDailyBonus）
- デイリーボーナススケジュール: 1種類（MstMissionEventDailyBonusSchedule）
- ミッション依存関係: 39種類（MstMissionEventDependency）
- 期間限定ミッション: 4種類（MstMissionLimitedTerm）
- ミッション報酬: 64種類（MstMissionReward）

### 7. ステージ設定 - 20ステージ

**ステージ関連データ**:
- ステージ基本設定: 20種類（MstStage）
- ステージイベント報酬: 70種類（MstStageEventReward）
- ステージクリアタイム報酬: 21種類（MstStageClearTimeReward）
- ステージイベント設定: 20種類（MstStageEventSetting）
- ステージ終了条件: 3種類（MstStageEndCondition）

### 8. 敵キャラクター設定

**敵関連データ**:
- 敵キャラクター: 5種類（MstEnemyCharacter）
- 敵出撃地点: 21種類（MstEnemyOutpost）
- 敵ステージパラメータ: 50種類（MstEnemyStageParameter）

### 9. イベント専用アイテム - 6種類

| アイテムID | 名前 | タイプ | レアリティ |
|-----------|------|--------|-----------|
| piece_jig_00401 | 賊王 亜左 弔兵衛のかけら | CharacterFragment | UR |
| piece_jig_00501 | 山田浅ェ門 桐馬のかけら | CharacterFragment | SSR |
| piece_jig_00601 | 民谷 巌鉄斎のかけら | CharacterFragment | SR |
| piece_jig_00701 | メイのかけら | CharacterFragment | SR |
| memory_chara_jig_00601 | 民谷 巌鉄斎のメモリー | RankUpMaterial | SR |
| memory_chara_jig_00701 | メイのメモリー | RankUpMaterial | SR |

**アイテム用途**:
- かけら: キャラクターのグレードアップに使用
- メモリー: キャラクターのLv.上限開放に使用

### 10. 課金パック - 2種類

| パックID | 名前 | タイプ |
|---------|------|--------|
| event_item_pack_12 | 【お一人様1回まで購入可】いいジャン祭 開催記念パック | 現金購入 |
| monthly_item_pack_3 | 【お一人様1回まで購入可】お得強化パック | 現金購入 |

**パック関連データ**:
- パックコンテンツ: 7種類（MstPackContent）
- 運営商品: 7種類（OprProduct）
- ストア商品: 7種類（MstStoreProduct）

### 11. PvPシーズン追加 - 2シーズン

| PvpID | 開催期間 | 初期バトルポイント | 特別ルール |
|-------|---------|------------------|-----------|
| 2026004 | リリースキー202601010で追加 | 1,000 | リーダーP1,000開始、全キャラ体力3倍UP、毒コマ登場 |
| 2026005 | リリースキー202601010で追加 | 1,000 | リーダーP1,000開始、全キャラ体力3倍UP、突風コマ登場 |

### 12. その他の追加コンテンツ

**ホームバナー**: 3種類（MstHomeBanner）

**イベントボーナス設定**:
- イベントボーナスユニット: 7種類（MstEventBonusUnit）
- イベント表示ユニット: 7種類（MstEventDisplayUnit）
- クエストボーナスユニット: 8種類（MstQuestBonusUnit）
- クエストイベントボーナススケジュール: 1種類（MstQuestEventBonusSchedule）

**ゲームシステム設定**:
- インゲーム設定: 23種類（MstInGame）
- インゲーム特別ルール: 22種類（MstInGameSpecialRule）
- 特別ルールユニットステータス: 1種類（MstInGameSpecialRuleUnitStatus）
- 特別ロールレベルアップ攻撃エレメント: 5種類（MstSpecialRoleLevelUpAttackElement）

**UI/演出設定**:
- コマライン設定: 58種類（MstKomaLine）
- 漫画アニメーション: 24種類（MstMangaAnimation）
- ページ設定: 23種類（MstPage）
- 吹き出し設定: 8種類（MstSpeechBalloonI18n）
- 必殺技設定: 4種類（MstSpecialAttackI18n）

---

## 多言語対応（I18n）

以下のテーブルに日本語（ja）の多言語データが追加されました：

| テーブル名 | 行数 | 対応内容 |
|-----------|------|---------|
| MstEventI18n | 1 | イベント名・説明 |
| MstUnitI18n | 4 | キャラクター名・説明 |
| MstAbilityI18n | 2 | アビリティ名・説明 |
| MstAdventBattleI18n | 1 | 降臨バトル名・説明 |
| MstArtworkI18n | 2 | アートワーク名・説明 |
| MstArtworkFragmentI18n | 32 | アートワークフラグメント名 |
| MstAttackI18n | 117 | 攻撃設定説明 |
| MstEmblemI18n | 7 | エンブレム名・説明 |
| MstEnemyCharacterI18n | 5 | 敵キャラクター名 |
| MstEventDisplayUnitI18n | 7 | イベント表示ユニット名 |
| MstInGameI18n | 23 | インゲーム設定名 |
| MstItemI18n | 6 | アイテム名・説明 |
| MstMissionEventI18n | 43 | ミッション説明 |
| MstMissionLimitedTermI18n | 4 | 期間限定ミッション説明 |
| MstPackI18n | 2 | パック名 |
| MstPvpI18n | 2 | PvP説明 |
| MstQuestI18n | 5 | クエスト名 |
| MstSpecialAttackI18n | 4 | 必殺技説明 |
| MstSpeechBalloonI18n | 8 | 吹き出しテキスト |
| MstStageI18n | 20 | ステージ名 |
| MstStoreProductI18n | 7 | ストア商品名 |
| OprGachaDisplayUnitI18n | 4 | ガチャ表示ユニット名 |
| OprGachaI18n | 2 | ガチャ名・説明 |
| OprProductI18n | 7 | 運営商品名 |

**合計**: 21テーブル、302行の多言語データ

---

## まとめ

リリースキー202601010は、「地獄楽 いいジャン祭」という大型コラボイベントの実装に特化した大規模なデータ投入でした。

### 規模感
- **78テーブル**、**1,818行**のデータ投入
- **新規キャラクター4体**（UR×1、SSR×1、SR×2）
- **イベント専用クエスト5種類**
- **ピックアップガチャ2種類**（150景品）
- **降臨バトル1種類**（185報酬設定）
- **ミッション43種類**（64報酬）
- **ステージ20種類**（70イベント報酬）
- **PvPシーズン2種類**

### 特徴
1. **包括的なイベント実装**: クエスト、ガチャ、降臨バトル、ミッション、アートワーク、エンブレムなど、イベントに必要な全要素を網羅
2. **バランスの取れたキャラクター構成**: UR/SSR/SR、各ロール（Technical/Support/Defense/Special）をバランスよく配置
3. **充実した報酬システム**: 降臨バトル報酬120種類、ミッション報酬64種類など、プレイヤーのモチベーションを維持する豊富な報酬設定
4. **完全な多言語対応**: 21テーブル302行の日本語データで、ユーザー体験を統一
5. **段階的なコンテンツ解放**: 一部クエストを1/21解放、降臨バトルを1/23開始など、期間中にプレイヤーを飽きさせない設計

このリリースは、GLOWプロジェクトにおける大型コラボイベントの標準的な実装パターンを示す好例と言えます。

---

## 生成ファイル

### テーブル別CSVファイル
- 保存先: `domain/raw-data/masterdata/released/202601010/tables/`
- ファイル数: 78ファイル
- 各ファイルサイズ: 平均4KB（最大40KB）

### 統計JSONファイル
- `domain/raw-data/masterdata/released/202601010/stats/summary.json` - 全体統計
- `domain/raw-data/masterdata/released/202601010/stats/tables.json` - テーブル別詳細統計

---

**作成日**: 2026-02-09
**分析ツール**: masterdata-releasekey-reporter skill
**データソース**: projects/glow-masterdata/

# masterdata-from-bizops-allスキルのロジック分析

## 実行日時
2026-02-11

## スキル構成

### メインスキル
- **スキル名**: masterdata-from-bizops-all
- **役割**: 運営仕様書全体から全マスタデータを一括作成する統合親スキル
- **対応範囲**: 全95テーブル（設計上）

### 子スキル数と対応テーブル
- **子スキル数**: 14個
- **対応テーブル数（合計）**: 74個 / 95個
- **カバレッジ**: 約78%

## 子スキル一覧と対応テーブル

| # | 子スキル名 | 対応テーブル | テーブル数 | 備考 |
|---|----------|-------------|----------|------|
| 1 | masterdata-from-bizops-gacha | OprGacha, OprGachaI18n, OprGachaPrize, OprGachaUpper, OprGachaUseResource, OprGachaDisplayUnitI18n | 6 | ガチャ機能 |
| 2 | masterdata-from-bizops-hero | MstUnit, MstUnitI18n, MstUnitAbility, MstAbility, MstAbilityI18n, MstAttack, MstAttackElement, MstAttackI18n, MstSpecialAttackI18n, MstSpeechBalloonI18n, MstUnitSpecificRankUp, MstEnemyCharacter, MstEnemyCharacterI18n | 13 | ヒーロー機能 |
| 3 | masterdata-from-bizops-mission | MstMissionEvent, MstMissionEventI18n, MstMissionEventDependency, MstMissionReward, MstMissionEventDailyBonus, MstMissionEventDailyBonusSchedule, MstMissionLimitedTerm, MstMissionLimitedTermI18n | 8 | ミッション機能 |
| 4 | masterdata-from-bizops-quest-stage | MstQuest, MstQuestI18n, MstQuestBonusUnit, MstStage, MstStageI18n, MstStageEventReward, MstStageEventSetting, MstStageClearTimeReward, MstStageEndCondition, MstQuestEventBonusSchedule | 10 | クエスト・ステージ機能 |
| 5 | masterdata-from-bizops-advent-battle | MstAdventBattle, MstAdventBattleI18n, MstAdventBattleRank, MstAdventBattleClearReward, MstAdventBattleRewardGroup, MstAdventBattleReward, MstEventBonusUnit | 7 | 降臨バトル機能 |
| 6 | masterdata-from-bizops-pvp | MstPvp, MstPvpI18n | 2 | PVP機能 |
| 7 | masterdata-from-bizops-item | MstItem, MstItemI18n | 2 | アイテム機能 |
| 8 | masterdata-from-bizops-reward | MstMissionReward, MstQuestFirstTimeClearReward, MstEventExchangeReward 他14個 | 17 | 報酬汎用（全報酬テーブル対応） |
| 9 | masterdata-from-bizops-emblem | MstEmblem, MstEmblemI18n | 2 | エンブレム機能 |
| 10 | masterdata-from-bizops-artwork | MstArtwork, MstArtworkI18n, MstArtworkFragment, MstArtworkFragmentI18n, MstArtworkFragmentPosition | 5 | 原画機能 |
| 11 | masterdata-from-bizops-event-basic | MstEvent, MstEventI18n, MstHomeBanner | 3 | イベント基本設定 |
| 12 | masterdata-from-bizops-shop-pack | MstStoreProduct, MstStoreProductI18n, OprProduct, OprProductI18n, MstPack, MstPackI18n, MstPackContent | 7 | ショップ・パック機能 |
| 13 | masterdata-from-bizops-enemy-autoplayer | MstEnemyCharacter, MstEnemyCharacterI18n, MstEnemyStageParameter, MstEnemyOutpost, MstAutoPlayerSequence | 5 | 敵・自動行動機能 |
| 14 | masterdata-from-bizops-ingame | MstInGame, MstInGameI18n, MstPage, MstKomaLine, MstInGameSpecialRule, MstInGameSpecialRuleUnitStatus, MstMangaAnimation | 7 | インゲーム設定 |
| | **合計** | | **94** | ※一部重複あり（MstEnemyCharacterなど） |

**注意**:
- MstEnemyCharacter, MstEnemyCharacterI18nは、hero と enemy-autoplayer の両方に含まれています（条件分岐）
- 実質的な対応テーブル数は **74個** 程度と推定

## 未対応テーブル分析（推定21個）

全95テーブル - 74個対応 = **約21個が未対応**

### 推定される未対応カテゴリ

#### 1. システム系テーブル（推定7-10個）
| テーブル候補 | 説明 | 対応すべき子スキル候補 | 優先度 |
|------------|------|---------------------|-------|
| MstTips* | チュートリアル・Tips系 | [新規スキル必要] | 低 |
| MstNotification* | お知らせ・通知系 | [新規スキル必要] | 低 |
| MstSetting* | ゲーム設定系 | [新規スキル必要] | 低 |
| MstDebug* | デバッグ用テーブル | [不要] | - |

#### 2. 集計・ログ系テーブル（推定5-8個）
| テーブル候補 | 説明 | 対応すべき子スキル候補 | 優先度 |
|------------|------|---------------------|-------|
| MstUserAction* | ユーザーアクション集計 | [マスタデータではない] | - |
| MstEventLog* | イベントログ | [マスタデータではない] | - |
| MstRanking* | ランキング集計 | [マスタデータではない] | - |

#### 3. バトル・ゲームメカニクス系（推定3-5個）
| テーブル候補 | 説明 | 対応すべき子スキル候補 | 優先度 |
|------------|------|---------------------|-------|
| MstBattleEffect* | バトルエフェクト定義 | ingame拡張 or 新規スキル | 中 |
| MstBuff* | バフ・デバフ定義 | hero拡張 or 新規スキル | 中 |
| MstSkill* | スキル効果定義 | hero拡張 or 新規スキル | 中 |

#### 4. その他マスタ（推定2-3個）
| テーブル候補 | 説明 | 対応すべき子スキル候補 | 優先度 |
|------------|------|---------------------|-------|
| MstBackground* | 背景画像定義 | ingame拡張 or event-basic拡張 | 低 |
| MstBGM* | BGM定義 | ingame拡張 | 低 |
| MstCutscene* | カットシーン定義 | [新規スキル必要] | 低 |

**注**: 実際のテーブル名は、DBスキーマファイルを確認して特定する必要があります。

### 未対応テーブルの特徴

1. **運営仕様書に記載されない情報**
   - システム設定、デバッグ設定など、運営が直接管理しない内容
   - エンジニアが直接DB投入するテーブル

2. **汎用的すぎる設定**
   - 背景、BGM、エフェクトなど、各機能横断的に使われる内容
   - 専用の管理ツールが必要な可能性

3. **マスタデータではないテーブル**
   - ユーザーデータ、ログデータなど

## 実行順序と依存関係

### 依存関係グラフ

```
         ┌─────────────────────────────────────┐
         │  Level 0: 依存なし                    │
         └─────────────────────────────────────┘
                      ↓
    ┌────────────────┬──────────────┬──────────────┐
    │ item(2)        │ hero(13)     │ emblem(2)    │
    │                │              │ event-basic(3)│
    └────────┬───────┴──────┬───────┴──────┬───────┘
             │              │              │
         ┌───┴──────────────┴──────────────┴───┐
         │  Level 1: アイテム・ヒーロー依存      │
         └───────────────────────────────────────┘
                      ↓
    ┌────────────────┬──────────────┬──────────────┐
    │ reward(17)     │ gacha(6)     │              │
    └────────┬───────┴──────────────┴──────────────┘
             │
         ┌───┴──────────────────────────────────┐
         │  Level 2: 報酬・ガチャ生成後          │
         └───────────────────────────────────────┘
                      ↓
    ┌────────────────┬──────────────┬──────────────┐
    │ quest-stage(10)│ mission(8)   │ advent-battle(7)│
    │ shop-pack(7)   │ pvp(2)       │              │
    └────────────────┴──────────────┴──────────────┘
                      ↓
         ┌───────────────────────────────────────┐
         │  Level 3: 各種イベント・機能           │
         └───────────────────────────────────────┘
                      ↓
    ┌────────────────┬──────────────┬──────────────┐
    │ artwork(5)     │ enemy-autoplayer(5)│ ingame(7)│
    └────────────────┴──────────────┴──────────────┘
```

### 推奨実行順序（workflow-logic.mdより）

```
1. masterdata-from-bizops-item          (2個)   - Level 0
2. masterdata-from-bizops-hero          (13個)  - Level 0
3. masterdata-from-bizops-emblem        (2個)   - Level 0
4. masterdata-from-bizops-event-basic   (3個)   - Level 0
5. masterdata-from-bizops-reward        (17個)  - Level 1 (item, hero, emblem依存)
6. masterdata-from-bizops-gacha         (6個)   - Level 1 (hero依存)
7. masterdata-from-bizops-quest-stage   (10個)  - Level 2 (hero, reward依存)
8. masterdata-from-bizops-mission       (8個)   - Level 2 (reward依存)
9. masterdata-from-bizops-advent-battle (7個)   - Level 2 (reward, event-basic依存)
10. masterdata-from-bizops-pvp          (2個)   - Level 2 (event-basic依存)
11. masterdata-from-bizops-shop-pack    (7個)   - Level 2 (item依存)
12. masterdata-from-bizops-artwork      (5個)   - Level 3
13. masterdata-from-bizops-enemy-autoplayer (5個) - Level 3
14. masterdata-from-bizops-ingame       (7個)   - Level 3 (hero依存)
```

### 依存関係の定義（DEPENDENCY_GRAPH）

```javascript
DEPENDENCY_GRAPH = {
    "item": [],
    "hero": [],
    "emblem": [],
    "event-basic": [],
    "reward": ["item", "hero", "emblem"],
    "gacha": ["hero"],
    "quest-stage": ["hero", "reward"],
    "mission": ["reward"],
    "advent-battle": ["reward", "event-basic"],
    "pvp": ["event-basic"],
    "shop-pack": ["item"],
    "artwork": [],
    "enemy-autoplayer": [],
    "ingame": ["hero"],
}
```

### 実行順序決定アルゴリズム

**トポロジカルソート（Kahn's algorithm）**を使用:

1. 各ノードの入次数を計算
2. 入次数が0のノードをキューに追加
3. キューから取り出し、推奨順序でソート
4. 依存元の入次数を減らし、0になったらキューに追加
5. 循環依存がある場合はエラー

## 推測値生成のロジック

### 推測値が発生する主なケース

#### 1. 運営仕様書に記載されていない必須カラム

| カラム | 推測ロジック | 優先度 |
|-------|------------|-------|
| strapi_uuid | 仮UUID "00000000-0000-0000-0000-000000000001" を生成 | High |
| display_size | 仮値 "Medium" を設定 | High |
| sort_order | 連番を自動生成（100, 200, 300...） | Medium |
| is_active | デフォルト値 1 を設定 | Medium |
| description | 名前から自動生成（「{名前}の説明」） | Medium |

#### 2. デフォルト値が必要なカラム

| カラム | 推測ロジック | 優先度 |
|-------|------------|-------|
| is_event | イベント系は1、それ以外は0 | Low |
| can_reset | デフォルト 0 | Low |
| is_visible | デフォルト 1 | Low |

#### 3. 外部キー参照の推測

| 参照先 | 推測ロジック | 優先度 |
|-------|------------|-------|
| mst_item_id | アイテム名から既存のMstItem.idを検索 | High |
| mst_unit_id | キャラ名から既存のMstUnit.idを検索 | High |

### 推測値レポートの統合方法

1. **各子スキルから推測値レポートを収集**
2. **機能別にセクション分け**
3. **優先度（High/Medium/Low）でソート**
4. **全体サマリーを生成**

#### レポート形式

```markdown
# 統合推測値レポート

## 概要
- 総推測値数: 42件
- High（要確認）: 12件
- Medium（推奨確認）: 18件
- Low（参考）: 12件

## 機能別レポート

### ガチャ (masterdata-from-bizops-gacha)
#### High
- OprGacha.display_size: 仮値 "Medium" を設定（運営仕様書に記載なし）
- OprGacha.strapi_uuid: 仮UUID "00000000-0000-0000-0000-000000000001" を設定
...
```

## データ整合性チェック

### 1. 外部キー整合性チェック

**チェック対象**:
- MstMissionReward.resource_id → MstItem.id または MstUnit.id
- OprGachaPrize.unit_id → MstUnit.id
- その他、全テーブル間のリレーション

**チェック方法**:
- 各CSVファイルを読み込み
- 外部キー定義に基づいて参照先の存在を確認
- 不整合があればエラーレポートに記録

### 2. ID採番の一貫性チェック

**チェック対象**:
- リリースキーが全テーブルで統一されているか
- ID命名規則が守られているか（例: `chara_jig_00401`）

### 3. 必須カラムの存在チェック

**チェック対象**:
- 必須カラムが全て埋まっているか
- NULL禁止カラムにNULLがないか

## エラーハンドリング

### エラー発生時の対応

**基本方針**:
- エラーが発生した子スキルをスキップし、次の子スキルに進む
- エラー内容は全体レポートに記録
- 依存関係がある場合、後続の子スキルもスキップする可能性あり

**エラー伝播ルール**:
1. item失敗 → reward, shop-packもスキップ
2. hero失敗 → gacha, quest-stage, mission, ingameもスキップ
3. reward失敗 → quest-stage, mission, advent-battleもスキップ
4. event-basic失敗 → advent-battle, pvpもスキップ

## 改善ポイント

### 1. カバレッジ拡大（優先度：高）

**課題**: 現在74個/95個のテーブルしか対応していない（78%）

**対策**:
- DBスキーマファイルを全件精査し、未対応21個のテーブルを特定
- 運営仕様書で作成可能なテーブルのみ、新規スキルを追加
- システム系・ログ系テーブルは除外

### 2. 精度向上（優先度：高）

**課題**: 推測値が多く、手動修正が必要

**対策**:
- 運営仕様書のテンプレートを整備し、必須情報を明記
- 各子スキルのマニュアル (references/manual.md) を充実化
- デフォルト値の定義を明確化

### 3. 依存関係の精度向上（優先度：中）

**課題**: 現在の依存関係は静的定義で、実行時の動的依存は考慮されていない

**対策**:
- 実行時に生成されたCSVファイルを解析し、動的に依存関係を判定
- 外部キー参照が存在する場合のみ、依存関係を有効化

### 4. 実行時間の最適化（優先度：低）

**課題**: 14個のスキルを順次実行するため、時間がかかる

**対策**:
- 並列実行可能なスキルを特定し、並列処理を導入
- Level 0のスキル（item, hero, emblem, event-basic）は並列実行可能

### 5. 推測値生成ロジックの改善（優先度：中）

**課題**: 固定値（"Medium"、仮UUID等）が多く、品質が低い

**対策**:
- 過去のリリースキーのデータを学習し、より精度の高い推測値を生成
- 運営仕様書のコンテキストを活用した推測ロジックの構築
- LLMを活用した自然言語からの情報抽出精度向上

## 実行フロー全体像

```
┌──────────────────────────────────────────────────┐
│ 1. 運営仕様書の解析                                │
│    - ファイル名から機能カテゴリを推定              │
│    - ファイル内容から該当する機能スキルを特定      │
│    - 各機能スキルに必要なパラメータを抽出          │
└─────────────────┬────────────────────────────────┘
                  ↓
┌──────────────────────────────────────────────────┐
│ 2. 必要な機能の特定                                │
│    - 解析結果から必要な機能スキルを特定            │
│    - 14個の機能スキルから該当するものを選択        │
└─────────────────┬────────────────────────────────┘
                  ↓
┌──────────────────────────────────────────────────┐
│ 3. 依存関係の解析                                  │
│    - DEPENDENCY_GRAPHを使用                       │
│    - 各機能スキル間の依存関係を解析                │
└─────────────────┬────────────────────────────────┘
                  ↓
┌──────────────────────────────────────────────────┐
│ 4. 実行順序の決定                                  │
│    - トポロジカルソートで最適な順序を算出          │
│    - 推奨実行順序を考慮                            │
└─────────────────┬────────────────────────────────┘
                  ↓
┌──────────────────────────────────────────────────┐
│ 5. 各機能スキルの順次実行                          │
│    - 決定した順序で各機能スキルを呼び出し          │
│    - 実行結果（CSV、推測値レポート）を収集         │
│    - エラーがあれば記録し、次の機能スキルへ        │
└─────────────────┬────────────────────────────────┘
                  ↓
┌──────────────────────────────────────────────────┐
│ 6. データ整合性の全体チェック                      │
│    - 外部キー整合性チェック                        │
│    - ID採番の一貫性チェック                        │
│    - 必須カラムの存在チェック                      │
└─────────────────┬────────────────────────────────┘
                  ↓
┌──────────────────────────────────────────────────┐
│ 7. 推測値レポートの統合                            │
│    - 各機能スキルの推測値レポートを収集            │
│    - 機能別にセクション分け                        │
│    - 重要度でソート                                │
└─────────────────┬────────────────────────────────┘
                  ↓
┌──────────────────────────────────────────────────┐
│ 8. 全体レポートの出力                              │
│    - 実行サマリー                                  │
│    - 統合推測値レポート                            │
│    - データ整合性チェック結果                      │
│    - 次のステップ                                  │
└──────────────────────────────────────────────────┘
```

## まとめ

### 強み

1. **14個の機能スキルを統合**: 主要なゲーム機能をカバー
2. **依存関係を考慮した実行順序**: トポロジカルソートで最適化
3. **エラーハンドリング**: エラー発生時も処理を継続
4. **データ整合性チェック**: 自動検証機能
5. **推測値レポート統合**: 全機能の推測値を可視化

### 弱み・課題

1. **カバレッジが78%**: 21個のテーブルが未対応
2. **推測値が多い**: 手動修正が必要な箇所が多い
3. **静的依存関係**: 動的な依存関係は考慮されていない
4. **順次実行のみ**: 並列実行による最適化がされていない
5. **運営仕様書の品質依存**: 記載が不十分だと精度が低下

### 今後の改善方向性

1. **未対応テーブルの分析と対応**: DBスキーマ全件精査
2. **推測値生成ロジックの高度化**: 過去データ学習、LLM活用
3. **並列実行の導入**: Level 0スキルの並列化
4. **運営仕様書テンプレートの整備**: 必須情報の明記
5. **動的依存関係の判定**: 実行時のCSV解析

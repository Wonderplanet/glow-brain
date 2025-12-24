# PvP決闘報酬一覧画面

## 基本情報

| 項目 | 内容 |
|------|------|
| **画面名** | PvP決闘報酬一覧画面 |
| **画面番号** | 043-01（報酬一覧画面） |
| **画面種別** | メイン画面 |

## 概要

PvP決闘イベント期間中に獲得できるすべての報酬を確認する画面です。プレイヤーは、ランキング報酬、ランククラス報酬、総スコア報酬の3つのカテゴリーから報酬を確認できます。各報酬には必要な条件（ランキング順位、ランククラスレベル、総スコア）と報酬内容が表示されます。

## プレイヤーができること

### 報酬情報を確認する

#### ランキング報酬タブ
- **1位報酬を確認**: 最高順位の報酬を表示
- **順位帯別報酬を確認**: 複数の順位帯（例：2位～10位、11位～100位など）ごとの報酬内容を表示
- **区間報酬の表示**: 連続していない順位帯は「XX位～YY位」という形式で表示
- **単一順位報酬の表示**: 特定の順位にのみ該当する報酬は「XX位」という形式で表示

#### ランククラス報酬タブ
- **ランククラスごとの報酬を確認**: ブロンズ～上位ランククラスごとの報酬を表示
- **ランククラスレベルを確認**: 各ランククラス内のレベル段階（例：ブロンズレベル1～10など）
- **必要ポイントを確認**: 該当ランククラスに達するために必要なPvPポイント

#### 総スコア報酬タブ
- **達成条件を確認**: 一定の総スコア到達で獲得可能
- **受取状況を確認**: すでに受け取った報酬と未受取の報酬を区別
- **グレードごと報酬を確認**: 高スコアほど豪華な報酬が設定されている

### 報酬の詳細を確認する

- **報酬アイコンをタップ**: 報酬アイテムの詳細情報を表示
- **報酬種別の確認**: ダイヤ、コイン、アイテムなど報酬の種別
- **報酬数量の確認**: 各報酬の獲得数量

### 期間情報を確認する

- **残り時間を表示**: PvPイベント終了までの時間を表示（例：「05:12:34」）
- **週間リセット**: 毎週同じ時間にリセットされることを表示

### 画面から戻る

- **戻るボタンをタップ**: ホーム画面またはPvPメニューに戻る

## 画面の要素

### ヘッダー部分
- タイトル「報酬一覧」
- 戻るボタン（左上）
- 残り時間の表示

### タブエリア
- **ランキング**: ランキング順位による報酬
- **ランククラス**: ランククラスと段階による報酬
- **総スコア**: 総累積スコアによる報酬

### メイン表示エリア
- **タブごとの報酬リスト**: 選択中のタブに対応する報酬セルを表示
- **報酬セル構成**:
  - 左側：条件情報（順位、ランククラス、スコアなど）
  - 右側：報酬アイコンの一覧表示
  - タップで詳細情報を表示可能

### 情報テキスト部分
- **ランキング/ランククラスタブ**: 「期間後受け取り」テキスト表示
- **総スコアタブ**: 「達成時受け取り」テキスト表示

## 画面遷移

### この画面への遷移
- **PvPメニュー**: 「報酬確認」ボタンをタップ
- **ホーム画面**: PvP関連メニューから選択

### この画面からの遷移
- **ホーム画面**: 戻るボタンをタップ
- **報酬詳細画面**: 報酬アイコンをタップして詳細情報を表示（オーバーレイ）

## ゲーム仕様・制約事項

### タブの動作仕様

#### タブ切り替え
- 同じタブを再度タップしても遷移しない（二重遷移を防止）
- タブ切り替え時に報酬リストが更新される

#### タブの表示内容
- **ランキング報酬**: 順位が高い順に並べ替えられて表示（降順）
- **ランククラス報酬**: 必要ポイントが高い順に並べ替えられて表示（降順）
- **総スコア報酬**: 必要スコアが高い順に並べ替えられて表示（降順）

### 報酬セルの種類

#### ランキング報酬セル
- **単一順位型**: 「1位」など特定の順位のみの報酬
- **区間型**: 「2位～10位」など複数の順位帯による報酬
  - 連続していない順位帯が複数ある場合は区間表示

#### ランククラス報酬セル
- **ランククラスとレベル**: 「ブロンズレベル5」など細かい段階で報酬設定

#### 総スコア報酬セル
- **受取状況フラグ**: IsReceived フラグで受取済みかどうかを判定
- **スコア条件**: 必要な総スコア数を表示

### 期間管理

- **リセット周期**: 毎週同じ日時でリセット（日単位ではなく週単位）
- **残り時間**: 次のリセットまでの時間をリアルタイム表示

### データの保証範囲

- 報酬データはマスタデータから取得（変動なし）
- ユーザーの総スコアはGameFetchから取得（リアルタイム）
- 表示順の並べ替えはクライアント側で実行

## 技術参考情報

### 画面実装パス
- `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpRewardList/`

### 主要コンポーネント

#### Presenter
- `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpRewardList/Presentation/Presenter/PvpRewardListPresenter.cs`
  - 画面初期化時にShowPvpRewardListUseCaseでデータ取得
  - タブ切り替えを処理（OnRankingTabButtonTapped、OnRankRewardTabButtonTapped、OnTotalScoreTabButtonTapped）
  - 報酬アイコンタップでItemDetailを表示

#### ViewModel
- `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpRewardList/Presentation/ViewModel/PvpRewardListViewModel.cs`
  - RemainingTimeSpan: 残り時間情報
  - RankingRewardCellViewModels: ランキング報酬セル（複数種類）
  - PointRankRewardCellViewModels: ランククラス報酬セル
  - TotalPointRewardCellViewModels: 総スコア報酬セル

- `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpRewardList/Presentation/ViewModel/IPvpRankingRewardCellViewModel.cs`
  - ランキング報酬セル用インターフェース
  - PvpSingleRankingRewardCellViewModel（単一順位用）
  - PvpIntervalRankingRewardCellViewModel（区間用）

- `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpRewardList/Presentation/ViewModel/PvpRankRewardCellViewModel.cs`
  - RankType: ランククラスの種別（Bronze, Silver など）
  - RankLevel: ランククラスのレベル
  - RequiredPoint: 到達に必要なPvPポイント

- `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpRewardList/Presentation/ViewModel/PvpTotalScoreRewardCellViewModel.cs`
  - RequiredPoint: 必要な総スコア
  - IsReceived: 受取状況フラグ

#### View
- `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpRewardList/Presentation/View/PvpRewardListViewController.cs`
- `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpRewardList/Presentation/View/PvpRewardListView.cs`

#### UseCase
- `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpRewardList/Domain/UseCase/ShowPvpRewardListUseCase.cs`
  - FetchPvpRewardListModel(): 報酬データを取得
  - IGameRepository から SysPvpSeasonId を取得
  - IDailyResetTimeCalculator で週間リセット時間を計算

#### Translator
- `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpRewardList/Presentation/Translator/PvpRewardListViewModelTranslator.cs`
  - Domain層モデルをPresentation層ViewModelに変換

### PvpRewardListViewModelの構造

```csharp
public record PvpRewardListViewModel(
    RemainingTimeSpan RemainingTimeSpan,                           // 残り時間
    IReadOnlyList<IPvpRankingRewardCellViewModel> RankingRewardCellViewModels,           // ランキング報酬セル
    IReadOnlyList<PvpRankRewardCellViewModel> PointRankRewardCellViewModels,   // ランククラス報酬セル
    IReadOnlyList<PvpTotalScoreRewardCellViewModel> TotalPointRewardCellViewModels)     // 総スコア報酬セル
```

### 報酬セルの表示ロジック

#### ランキング報酬の処理フロー
1. MstPvpRewardGroupModelsから PvpRewardCategory.Ranking を抽出
2. ConditionValue（順位）でソート（降順）
3. 最高順位（最後）を単一ランキング報酬として取得
4. その他を順位帯で分析：
   - 連続している場合（差分が1）→ 単一順位型
   - 連続していない場合 → 区間型

#### ランククラス報酬の処理フロー
1. MstPvpRewardGroupModelsから PvpRewardCategory.RankClass を抽出
2. MstPvpRanks テーブルと結合（ConditionValue で紐付け）
3. RequiredLowerPoint でソート（降順）

#### 総スコア報酬の処理フロー
1. MstPvpRewardGroupModelsから PvpRewardCategory.TotalScore を抽出
2. ユーザーの MaxReceivedTotalScore と比較して IsReceived を設定
3. ConditionValue でソート（降順）

### Enum: PvpRewardListTabType

```csharp
public enum PvpRewardListTabType
{
    Ranking,      // ランキング報酬
    RankClass,    // ランククラス報酬
    TotalScore    // 総スコア報酬
}
```

## 使用しているDBデータ

### マスタデータ

| クライアント定義 | サーバー定義 | クライアント説明 |
|----------------|-------------|----------------|
| **MstPvpRewardGroup**<br>PvP報酬グループ | **mst_pvp_reward_groups**<br>報酬グループマスタ | 報酬グループの基本情報 |
| MstPvpRewardGroup.Id | mst_pvp_reward_groups.id | 報酬グループID |
| MstPvpRewardGroup.RewardCategory | mst_pvp_reward_groups.reward_category | 報酬カテゴリー（Ranking/RankClass/TotalScore） |
| MstPvpRewardGroup.ConditionValue | mst_pvp_reward_groups.condition_value | 条件値（順位/ポイント/スコア） |
| **MstPvpReward**<br>報酬内容 | **mst_pvp_rewards**<br>報酬アイテム | 実際の報酬アイテム |
| MstPvpReward.ResourceType | mst_pvp_rewards.resource_type | 報酬種別（Coin/Diamond/Item など） |
| MstPvpReward.ResourceId | mst_pvp_rewards.resource_id | 報酬ID（アイテムID等） |
| MstPvpReward.Amount | mst_pvp_rewards.amount | 報酬数量 |
| **MstPvpRank**<br>ランククラス定義 | **mst_pvp_ranks**<br>ランククラスマスタ | ランククラスの定義 |
| MstPvpRank.RankClassType | mst_pvp_ranks.rank_class_type | ランククラス種別（Bronze/Silver など） |
| MstPvpRank.RankLevel | mst_pvp_ranks.rank_level | ランククラス内レベル |
| MstPvpRank.RequiredLowerPoint | mst_pvp_ranks.required_lower_point | 到達に必要なPvPポイント |

### ユーザーデータ（GameFetch）

| クライアント定義 | サーバー定義 | クライアント説明 |
|----------------|-------------|----------------|
| **UserPvpStatus**<br>ユーザーPvPステータス | **user_pvp_statuses**<br>ユーザーPvP情報 | ユーザーの現在のPvPステータス |
| UserPvpStatus.MaxReceivedTotalScore | user_pvp_statuses.max_received_total_score | これまで到達した最高スコア（総スコア報酬の受取判定に使用） |

## 呼び出しているAPI

画面初期化時に以下のデータを取得していますが、API呼び出しはなく、すべてローカルのマスタデータとGameFetchから取得しています。

- **マスタデータ**: IMstPvpDataRepository経由で MstPvpRewardGroup、MstPvpRank などを取得
- **ユーザーデータ**: IGameRepository.GetGameFetchOther() で UserPvpStatus.MaxReceivedTotalScore を取得

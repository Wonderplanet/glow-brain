# QuestSelect画面

## 基本情報

| 項目 | 内容 |
|------|------|
| **画面名** | クエスト選択画面 |
| **画面種別** | モーダル画面 |

## 概要

ホーム画面からクエスト挑戦時に表示される画面です。プレイヤーは複数のクエスト（クエストグループ）の中から挑戦するクエストを選択し、そのクエストの難易度（ノーマル、ハード、エクストラ）を選択して確定できます。カルーセルUI で複数のクエストを閲覧でき、各クエストの開放状況、必要な前提条件、フレーバーテキスト、各難易度での原画のかけらの入手情報などを確認できます。

## プレイヤーができること

### クエストの選択

#### クエストの閲覧
- **カルーセル表示**: 複数のクエストが横スクロール形式で表示される
- **左右ボタン**: 矢印ボタンでクエストを前後に移動可能
- **スイプジェスチャー**: タッチでのドラッグによるカルーセル移動に対応
- **クエスト画像**: 選択中のクエストが大きく表示される
- **クエスト情報**: 選択中のクエストの詳細情報が表示される

#### クエストサムネイル
- **クエスト名**: 選択中のクエストの名前を表示
- **フレーバーテキスト**: クエストの説明文やストーリー背景を表示
  - クエストが未開放の場合は表示されない
- **Newアイコン**: 未クリアで新規追加されたクエストに表示
- **未開放表示**: 開放条件を満たしていない場合は施錠アイコンと開放条件が表示される

### 難易度の選択

#### 難易度ボタン
- **ノーマル難易度**: 基本難易度のボタン（常に表示）
- **ハード難易度**: より高い難易度のボタン（開放条件を満たした場合のみ表示）
- **エクストラ難易度**: さらに高い難易度のボタン（開放条件を満たした場合のみ表示）

#### 難易度の状態表示
- **開放済み**: ボタンが有効で、タップして選択可能
- **未開放**: ボタンが無効（グレーアウト）で、開放条件を表示
  - 開放条件例：「○○クエスト ノーマル第X ステージをクリア」
- **選択中**: タップで選択した難易度が視覚的にハイライトされる

#### 原画のかけら情報
- **入手可能数**: その難易度でのクエストをクリアすると入手できる原画のかけら数を表示
- **既取得数**: プレイヤーが既に取得済みの原画のかけら数を表示
- 各難易度ごとに異なる原画のかけらが設定されている場合あり

### キャンペーン情報

- **難易度ごとのキャンペーン**: 各難易度にキャンペーン（報酬アップなど）が設定されている場合、バルーンアイコンで表示される

### その他の操作

- **戻るボタン**: クエスト選択画面を閉じてホーム画面に戻る
  - 現在の選択が確定される
  - バックキー（Android）でも同じ動作

## 画面の要素

### ヘッダー部分
- **クエスト名**: 選択中のクエストの名前
- **戻るボタン**: 画面を閉じる

### メイン表示エリア

#### カルーセルエリア
- **クエストサムネイル**: 複数のクエスト画像がカルーセル状に表示される
  - 中央のクエストが選択中の状態
  - 左右のクエストは縮小表示される
- **左右ナビゲーションボタン**: 矢印ボタン
  - クエスト一覧の最初では左ボタンが非表示
  - クエスト一覧の最後では右ボタンが非表示
- **Newアイコン**: 新規クエスト表示
- **未開放オーバーレイ**: 開放条件未達成時に画像の上に表示される

#### クエスト詳細エリア
- **クエスト名**: 選択中のクエストの名前
- **フレーバーテキスト**: ストーリー背景やクエスト説明
  - スクロール可能な領域
- **未開放の場合**: 施錠アイコンと「○月○日 開放予定」などの表示

### ボタンエリア

#### 難易度選択ボタンエリア
- **ノーマルボタン**: 常に表示
- **ハードボタン**: 開放済みの場合のみ表示
  - ボタン: 「Hard」と表示
  - 原画のかけら情報
- **エクストラボタン**: 開放済みの場合のみ表示
  - ボタン: 「Extra」と表示
  - 原画のかけら情報

#### キャンペーン表示
- 各難易度のボタンの下に、設定されたキャンペーン情報を表示するバルーンアイコン

## 画面遷移

### この画面への遷移
- **ホーム画面**: 「クエストに挑戦」ボタンをタップ
  - 前回選択していたクエストと難易度が復元される
  - 初回はデフォルト（最初のクエスト、ノーマル難易度）が表示される

### この画面からの遷移
- **ホーム画面**: 戻るボタンまたはバックキーをタップ
  - 最終的に選択していたクエストが確定される
  - 難易度の選択も確定される
  - コールバック通知が行われる（ユーザーが実際にクエストを選択変更した場合のみ）

## ゲーム仕様・制約事項

### クエストの開放状況

#### Released（開放済み）
- クエストの開催期間中で、すべての開放条件を満たしている状態
- カルーセルで選択可能

#### NotOpenQuest（開催期間外）
- クエスト開催期間外の状態
- 施錠アイコンと「○月○日 開放予定」が表示される

#### NoClearRequiredStage（開放条件クリア待ち）
- イベントクエストの前提ステージをまだクリアしていない状態
- 施錠アイコンと「○○クエスト ノーマル第X をクリア」が表示される

#### QuestEnded（終了）
- クエスト開催期間が終了した状態

#### NoPlayableStage（プレイ可能ステージなし）
- 回数上限に達してプレイできるステージがない状態

### 難易度の開放

各難易度は以下の条件で開放される：

#### ノーマル難易度
- クエストの開催期間中であれば常に開放される

#### ハード・エクストラ難易度
- **前提条件**:
  - クエストの開催期間中
  - 該当難易度の最初のステージをクリア条件で開放
- **開放条件の確認**:
  - 未開放の場合、ボタンがグレーアウトされ、開放条件が表示される
  - 例：「○○クエスト ノーマル第X をクリア」
- **条件判定**:
  - ノーマルクエストの場合：プレイヤーの通常ステージクリア情報で判定
  - イベントクエストの場合：プレイヤーのイベントステージクリア情報で判定

### 原画のかけら情報

- **gettableArtworkFragmentNum**: その難易度のすべてのステージでドロップ可能な原画のかけらの合計数を表示
- **acquiredArtworkFragmentNum**: プレイヤーが既に取得済みの原画のかけら数を表示
- 難易度ごとに異なるドロップグループが設定されている場合がある

### デフォルトクエストの決定

画面初期化時に表示するクエストは以下の優先順で決定される：

1. **指定されたMstQuestId**: 画面呼び出し時に引数で指定されたクエスト
2. **前回選択済みクエスト**: ホーム画面の選択キャッシュに保存されているクエスト
3. **デフォルトクエスト**: システムで最初に設定されているクエスト
   - 通常は最初のノーマルクエストのノーマル難易度

### クエスト選択の確定

- **選択変更判定**:
  - 新しく選択したクエストが、前回選択していたクエストと異なる場合のみコールバックが実行される
  - 同じクエストを選択している場合はコールバックは実行されない
- **状態のリセット**:
  - クエスト選択後、「最後にプレイしたステージ」の記録がリセットされる
  - これにより、ユーザーが意図的に別のクエストを選んだことが明確になる

## 画面初期化時の引数

```csharp
QuestSelectViewController.Argument(
    Action StageSelected,                    // クエスト選択完了時のコールバック
    MasterDataId InitialSelectedQuestId      // 初期表示するクエストID（空の場合は前回選択or デフォルト）
)
```

### 引数の説明

- **StageSelected**: クエスト選択が確定した時に実行されるコールバック
  - ユーザーが実際にクエスト変更を行った場合のみ呼び出される
- **InitialSelectedQuestId**: 画面初期表示時のクエスト
  - 空（Empty）の場合は、PreferenceRepository から前回選択を復元
  - それもない場合は、システムのデフォルトクエストを使用

## 使用しているDBデータ

### マスタデータ

| クライアント定義 | サーバー定義 | クライアント説明 |
|----------------|-------------|----------------|
| **MstQuest**<br>クエスト基本情報 | **mst_quests**<br>クエスト基本情報 |  |
| MstQuest.Id | mst_quests.id | クエストID |
| MstQuest.GroupId | mst_quests.group_id | クエストグループID（複数難易度をグループ化） |
| MstQuest.Name | mst_quests.name | クエスト名 |
| MstQuest.Difficulty | mst_quests.difficulty | 難易度（Normal, Hard, Extra） |
| MstQuest.QuestType | mst_quests.quest_type | クエストタイプ（Normal, Event, Enhance, Tutorial） |
| MstQuest.AssetKey | mst_quests.asset_key | クエスト画像のアセットキー |
| MstQuest.QuestFlavorText | mst_quests.flavor_text | クエスト説明テキスト |
| MstQuest.StartDate | mst_quests.start_at | クエスト開始日時 |
| MstQuest.EndDate | mst_quests.end_at | クエスト終了日時 |
| **MstStage**<br>ステージ情報 | **mst_stages**<br>ステージ情報 |  |
| MstStage.Id | mst_stages.id | ステージID |
| MstStage.MstQuestId | mst_stages.mst_quest_id | 所属クエストID |
| MstStage.StageNumber | mst_stages.stage_number | ステージナンバー |
| MstStage.ReleaseRequiredMstStageId | mst_stages.release_required_mst_stage_id | 難易度開放条件となるステージID |
| MstStage.MstArtworkFragmentDropGroupId | mst_stages.mst_artwork_fragment_drop_group_id | 原画のかけらドロップグループID |
| **MstArtworkFragment**<br>原画のかけらドロップ情報 | **mst_artwork_fragments**<br>原画のかけらドロップ情報 |  |
| MstArtworkFragment.MstDropGroupId | mst_artwork_fragments.mst_drop_group_id | ドロップグループID（MstStage.MstArtworkFragmentDropGroupIdと紐付け） |
| MstArtworkFragment.MstArtworkId | mst_artwork_fragments.mst_artwork_id | 原画ID |

### ユーザーデータ（GameFetch）

| クライアント定義 | サーバー定義 | クライアント説明 |
|----------------|-------------|----------------|
| **UserStage**<br>ユーザーステージ進行状況 | **user_stages**<br>ユーザーステージ進行状況 |  |
| UserStage.MstStageId | user_stages.mst_stage_id | ステージID |
| UserStage.ClearCount | user_stages.clear_count | クリア回数（難易度開放条件判定に使用） |
| **UserStageEvent**<br>イベントステージ進行状況 | **user_stage_events**<br>ユーザーイベントステージ進行状況 |  |
| UserStageEvent.MstStageId | user_stage_events.mst_stage_id | ステージID |
| UserStageEvent.ClearCount | user_stage_events.clear_count | クリア回数（イベント難易度開放条件判定に使用） |
| **UserArtworkFragment**<br>ユーザー原画のかけら | **user_artwork_fragments**<br>ユーザー原画のかけら |  |
| UserArtworkFragment.MstArtworkId | user_artwork_fragments.mst_artwork_id | 原画ID（取得済み原画判定に使用） |
| UserArtworkFragment.MstArtworkFragmentId | user_artwork_fragments.mst_artwork_fragment_id | 原画のかけらID |

### キャンペーン関連マスタ

| クライアント定義 | サーバー定義 | クライアント説明 |
|----------------|-------------|----------------|
| **MstCampaign**<br>キャンペーン基本情報 | **mst_campaigns**<br>キャンペーン基本情報 |  |
| MstCampaign.Id | mst_campaigns.id | キャンペーンID |
| MstCampaign.TargetType | mst_campaigns.target_type | キャンペーン対象タイプ |
| MstCampaign.TargetIdType | mst_campaigns.target_id_type | ターゲットID種別 |
| MstCampaign.TargetId | mst_campaigns.target_id | ターゲットID（クエストID） |
| MstCampaign.Difficulty | mst_campaigns.difficulty | 難易度（キャンペーンが特定難易度のみの場合） |
| MstCampaign.AssetKey | mst_campaigns.asset_key | キャンペーンバルーンのアセットキー |

## 技術参考情報

### 画面実装パス
- `/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/QuestSelect/`

### 主要コンポーネント

#### Presenter
- `Presentation/QuestSelectPresenter.cs` (/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/QuestSelect/Presentation/QuestSelectPresenter.cs)
  - **主要メソッド**:
    - `OnViewDidLoad()`: 画面初期化時に呼び出される。クエスト一覧データを取得してViewModelを構築
    - `OnDifficultySelected()`: 難易度ボタンがタップされた際に呼び出される。開放状況をチェックし、未開放の場合はトーストメッセージを表示
    - `ApplySelectedQuest()`: クエスト選択を確定する際に呼び出される。選択内容を保存してホーム画面に戻る

#### ViewController
- `Presentation/QuestSelectViewController.cs` (/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/QuestSelect/Presentation/QuestSelectViewController.cs)
  - **主要メソッド**:
    - `Initialize()`: ViewModel を受け取り、カルーセルと難易度ボタンを初期化
    - `SelectDifficulty()`: 難易度が選択されたときの画面更新（ボタンアニメーション、表示難易度の変更）
  - **主要インターフェース実装**:
    - `IGlowCustomCarouselViewDataSource`: カルーセルのデータ提供
    - `IGlowCustomCarouselViewDelegate`: カルーセルのイベント処理
    - `IEscapeResponder`: バックキーの処理

#### ViewModel
- `Presentation/QuestSelectViewModel.cs` (/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/QuestSelect/Presentation/QuestSelectViewModel.cs)
  - **プロパティ**:
    - `CurrentIndex`: 現在表示中のクエスト（カルーセルのインデックス）
    - `Items`: 表示対象のクエスト一覧（`QuestSelectContentViewModel` の配列）

- `Presentation/QuestSelectContentViewModel.cs` (/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/QuestSelect/Presentation/QuestSelectContentViewModel.cs)
  - **プロパティ**:
    - `MstGroupQuestId`: クエストグループID
    - `QuestName`: クエスト名
    - `CurrentDifficulty`: 現在選択中の難易度
    - `AssetPath`: クエスト画像のアセットパス
    - `FlavorText`: クエスト説明テキスト
    - `OpenStatus`: クエスト全体の開放状況
    - `NewQuestExists`: 新規クエスト判定フラグ
    - `RequiredSentence`: クエスト開放条件の説明文
    - `QuestDifficultyItemViewModels`: 難易度ごとの詳細情報（Normal/Hard/Extra）
    - `NormalCampaignViewModels`, `HardCampaignViewModels`, `ExtraCampaignViewModels`: 各難易度のキャンペーン情報

- `Presentation/QuestDifficultySelect/QuestDifficultyItemViewModel.cs` (/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/QuestSelect/Presentation/QuestDifficultySelect/QuestDifficultyItemViewModel.cs)
  - **プロパティ**:
    - `MstQuestId`: 難易度別のクエストID
    - `Difficulty`: 難易度（Normal/Hard/Extra）
    - `DifficultyOpenStatus`: 難易度の開放状況（Released/NotRelease）
    - `ReleaseRequiredSentence`: 開放条件の説明文
    - `GettableArtworkFragmentNum`: 入手可能な原画のかけら数
    - `AcquiredArtworkFragmentNum`: 既取得の原画のかけら数

#### View
- `Presentation/QuestSelectView.cs` (/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/QuestSelect/Presentation/QuestSelectView.cs)
  - UIコンポーネントの管理と配置レイアウト

- `Presentation/QuestSelectCell.cs` (/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/QuestSelect/Presentation/QuestSelectCell.cs)
  - カルーセルセルの実装（クエストサムネイル）

#### UseCase
- `Domain/UseCases/QuestSelectUseCase.cs` (/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/QuestSelect/Domain/UseCases/QuestSelectUseCase.cs)
  - **主要メソッド**:
    - `GetQuestSelectUseCaseModels()`: 表示対象のクエスト一覧を取得。初期選択クエストの判定ロジックを含む

- `Domain/UseCases/SelectQuestUseCase.cs` (/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/QuestSelect/Domain/UseCases/SelectQuestUseCase.cs)
  - **主要メソッド**:
    - `SelectQuest()`: クエスト選択を確定する。選択内容の保存とリセット処理を実行

#### Translator
- `Presentation/QuestSelectViewModelTranslator.cs` (/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/QuestSelect/Presentation/QuestSelectViewModelTranslator.cs)
  - UseCaseModel → ViewModel への変換
  - クエスト開放条件の説明テキスト生成

#### ModelFactory
- `Domain/QuestSelectUseCaseModelItemFactory.cs` (/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/QuestSelect/Domain/QuestSelectUseCaseModelItemFactory.cs)
  - マスタデータから UseCaseModel を構築
  - 開放状況判定ロジック

- `Domain/QuestDifficultyUseCaseModelItemFactory.cs` (/Users/junki.mizutani/Documents/workspace/glow/glow-brain/projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/QuestSelect/Domain/QuestDifficultyUseCaseModelItemFactory.cs)
  - 難易度ごとの詳細情報を構築
  - 原画のかけら情報の集計
  - 難易度開放条件の判定

### QuestSelectViewModelの構造

```
QuestSelectViewModel
├── CurrentIndex: 現在のカルーセルインデックス
└── Items: QuestSelectContentViewModel[]
    ├── MstGroupQuestId: クエストグループID
    ├── QuestName: クエスト名
    ├── CurrentDifficulty: 現在の難易度選択
    ├── AssetPath: クエスト画像パス
    ├── FlavorText: 説明テキスト
    ├── OpenStatus: クエスト開放状況
    ├── NewQuestExists: 新規フラグ
    ├── RequiredSentence: 開放条件文
    ├── QuestDifficultyItemViewModels[]
    │   ├── MstQuestId: 難易度別クエストID
    │   ├── Difficulty: Normal/Hard/Extra
    │   ├── DifficultyOpenStatus: 難易度開放状況
    │   ├── ReleaseRequiredSentence: 開放条件文
    │   ├── GettableArtworkFragmentNum: 入手可能数
    │   └── AcquiredArtworkFragmentNum: 既取得数
    └── CampaignViewModels (Normal/Hard/Extra)
        └── キャンペーン情報
```

### 表示制御ロジック

#### クエスト開放状況の判定（QuestOpenStatusEvaluator）
- **Released**: 開催期間中かつ前提条件クリア
- **NotOpenQuest**: 開催期間外
- **NoClearRequiredStage**: イベント前提クエスト未クリア
- **QuestEnded**: クエスト終了
- **NoPlayableStage**: 回数上限到達

#### 難易度開放状況の判定
- **開放条件**:
  - クエスト開催期間中
  - 該当難易度の最初のステージがクリア可能な状態
- **Normal難易度**: 常に開放（開催期間中）
- **Hard/Extra難易度**: 前提ステージクリア時に開放

#### 新規クエスト判定（NewQuestEvaluator）
- クエストが未クリアで、かつ開放状態にある場合に "New" アイコンを表示

#### 原画のかけら情報
- **GettableArtworkFragmentNum**: 難易度配下のすべてのステージの原画ドロップグループから、ドロップ対象の原画かけら数をカウント
- **AcquiredArtworkFragmentNum**: プレイヤーがユーザーデータで既に取得済みの原画かけら数をカウント

### 依存関係

**主要Repository**:
- `IMstQuestDataRepository`: クエストマスタデータ
- `IMstStageDataRepository`: ステージマスタデータ
- `IMstArtworkFragmentDataRepository`: 原画のかけらドロップ情報
- `IGameRepository`: プレイヤーユーザーデータ（GameFetch）
- `IPreferenceRepository`: クエスト選択のプリファレンス保存

**主要Factory**:
- `IQuestSelectUseCaseModelItemFactory`: コンテンツモデル構築
- `IQuestDifficultyUseCaseModelItemFactory`: 難易度詳細情報構築
- `ICampaignModelFactory`: キャンペーン情報構築
- `IQuestReleaseCheckSampleFinder`: 開放条件判定用ステージの検出
- `INewQuestEvaluator`: 新規クエスト判定
- `IQuestOpenStatusEvaluator`: クエスト開放状況判定

### データフロー

```
QuestSelectViewController.Argument
  ↓
QuestSelectPresenter.OnViewDidLoad()
  ↓
QuestSelectUseCase.GetQuestSelectUseCaseModels()
  ├─ IMstQuestDataRepository: クエスト一覧取得
  ├─ QuestSelectUseCaseModelItemFactory: 各クエストのUseCaseModel構築
  │  ├─ INewQuestEvaluator: 新規判定
  │  ├─ IQuestOpenStatusEvaluator: クエスト開放状況判定
  │  ├─ QuestDifficultyUseCaseModelItemFactory: 難易度情報構築
  │  │  ├─ IMstStageDataRepository: ステージ情報取得
  │  │  ├─ IMstArtworkFragmentDataRepository: 原画情報取得
  │  │  ├─ IGameRepository: ユーザーデータ取得
  │  │  └─ 開放条件判定ロジック
  │  └─ ICampaignModelFactory: キャンペーン情報構築
  └─ 選択クエスト位置計算
  ↓
QuestSelectViewModelTranslator.CreateQuestSelectViewModel()
  ↓
QuestSelectViewController.Initialize()
  ├─ CarouselView初期化
  ├─ DifficultyButtons初期化
  └─ クエスト詳細表示
  ↓
画面表示完了
```

### イベントハンドラー

#### Presenterから呼び出されるView操作
- `ViewController.SelectDifficulty()`: 難易度ボタン選択時
- `ViewController.TryPop()`: 戻る処理（ホームナビゲーション）

#### ViewControllerから呼び出されるPresenter操作
- `ViewDelegate.OnDifficultySelected()`: 難易度ボタンタップ時
- `ViewDelegate.ApplySelectedQuest()`: クエスト確定時

---

**最終更新**: 2025-12-24

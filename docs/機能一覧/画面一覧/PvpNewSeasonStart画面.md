# PvpNewSeasonStart画面

## 基本情報

| 項目 | 内容 |
|------|------|
| **画面名** | PvP/決闘シーズン開始画面 |
| **画面種別** | モーダル画面 |
| **表示トリガー** | PvpTop画面で前シーズン結果演出後に自動表示 |

## 概要

シーズン開始時にプレイヤーの新しいランク帯を演出とともに表示する画面です。前シーズンの最終ランク（ブロンズ、シルバー、ゴールド、プラチナのいずれか）とランクレベルを大きなアイコンで表示し、シーズン開始を演出します。

## プレイヤーができること

### 画面閉じる

- 画面が自動アニメーション再生後、「タップして閉じる」というメッセージが表示される
- メッセージをタップすると画面が閉じられる
- クローズボタンからも画面を閉じることが可能

## 画面の要素

### ランクアイコン

- プレイヤーのランク帯（ブロンズ、シルバー、ゴールド、プラチナ）を示すアイコンを表示
- ランクレベル（1～4）に応じたティアアニメーションが自動再生される
- ランクアイコンに合わせた視覚的な演出

### ランクテキスト表示

- 「{ランク名}{ランクレベル}からスタート!!」というテキストを表示
  - 例：「ゴールド3からスタート!!」
  - 例：「プラチナ2からスタート!!」

### アニメーション演出

- 画面表示時に開始アニメーション（`in`トリガー）が自動再生される
- アニメーション完了後にボタンとタップメッセージが表示される
- ユーザーが操作するまで待機

## 画面遷移

### この画面への遷移

- **PvpTop画面**: 前シーズン結果演出の完了後に自動表示される
  - PvpTopPresenter.OnViewDidLoad()の処理フロー内で呼び出される
  - 前シーズン結果（PvpPreviousSeasonResult）が表示され、その後にこの画面が表示される
  - 新シーズンで初期化されたプレイヤーの新しいランク帯を表示

### この画面からの遷移

- **PvpTop画面へ戻る**: ボタンタップまたはクローズボタンで画面が閉じられ、PvpTop画面へ戻る
  - 閉じた後、PvpTop画面では累積ポイント報酬受け取りの処理へ進む可能性あり

## ゲーム仕様・制約事項

### ランク帯の種類

| ランク帯 | 説明 |
|---------|------|
| ブロンズ | PvPランキング最低ランク帯 |
| シルバー | ブロンズの1段上 |
| ゴールド | シルバーの1段上 |
| プラチナ | PvPランキング最高ランク帯 |

### ランクレベル

- ランクレベルは1～4の値を持つ
- 各ランク帯内で4段階の細分化が可能
- 画面表示時にレベルに応じたアニメーション演出が再生される

### 画面表示フロー

1. **前シーズン結果画面表示**
   - PvpTop画面ロード時に前シーズン結果の演出が表示される
   - ユーザーがタップして画面を閉じるまで待機

2. **新シーズン開始画面表示** ← 現在の画面
   - 前シーズン結果画面を閉じた後、自動的にこの画面が表示される
   - プレイヤーの新しいランク帯を演出とともに表示

3. **累積ポイント報酬画面（条件付き）**
   - この画面を閉じた後、受け取り可能な報酬があれば表示される

### ユーザーデータの参照

- **PvpRankClassType**: プレイヤーの現在のランク帯（ブロンズ～プラチナ）
- **ScoreRankLevel**: ランク帯内のレベル（1～4）
- これらは `UserPvpStatusModel` から取得される

### 時間軸

- シーズン終了時点でプレイヤーが達成していた最終ランク帯を表示
- シーズン開始と同時にプレイヤーのランク情報が初期化されたことを示す演出
- 新シーズンのマッチング対象決定時には、このランク帯に基づいて行われる

## 技術参考情報

### 画面実装パス

- `Assets/GLOW/Scripts/Runtime/Scenes/PvpNewSeasonStart/`

### 主要コンポーネント

#### Presenter

- `Presentation/Presenters/PvpNewSeasonStartPresenter.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpNewSeasonStart/Presentation/Presenters/PvpNewSeasonStartPresenter.cs)
  - `ViewDidLoad()`: ViewController初期化時の処理
  - `OnCloseButtonTapped()`: クローズボタン押下時の処理

#### ViewController

- `Presentation/Views/PvpNewSeasonStartViewController.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpNewSeasonStart/Presentation/Views/PvpNewSeasonStartViewController.cs)
  - 画面遷移の管理
  - `Argument`レコード: `PvpNewSeasonStartViewModel`を受け取る

#### View

- `Presentation/Views/PvpNewSeasonStartView.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpNewSeasonStart/Presentation/Views/PvpNewSeasonStartView.cs)
  - UI要素の管理（ランクアイコン、テキスト、ボタン）
  - アニメーション制御
  - `PlayStartAnimation()`: 開始アニメーション再生

#### ViewModel

- `Presentation/ViewModels/PvpNewSeasonStartViewModel.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpNewSeasonStart/Presentation/ViewModels/PvpNewSeasonStartViewModel.cs)

### PvpNewSeasonStartViewModelの構造

```csharp
public record PvpNewSeasonStartViewModel(
    PvpRankClassType PvpRankClassType,  // ランク帯（Bronze, Silver, Gold, Platinum）
    ScoreRankLevel ScoreRankLevel       // ランクレベル（1～4）
)
```

**プロパティ説明**:
- `PvpRankClassType`: enum値で以下のいずれかを保持
  - `Bronze`: ブロンズ帯
  - `Silver`: シルバー帯
  - `Gold`: ゴールド帯
  - `Platinum`: プラチナ帯
- `ScoreRankLevel`: ObscuredInt値で1～4のレベルを保持

### 画面初期化時の引数

```csharp
PvpNewSeasonStartViewController.Argument(ViewModel)
```

**引数**:
- `ViewModel`: PvpNewSeasonStartViewModel型
  - PvpRankClassType: プレイヤーの新シーズンランク帯
  - ScoreRankLevel: ランク帯内のレベル

### 呼び出し元の処理フロー

**PvpTopPresenter** (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpTop/Presentation/Presenter/PvpTopPresenter.cs)

```csharp
async UniTask ShowNewSeasonStart(
    PvpRankClassType rankClassType,
    ScoreRankLevel scoreRankLevel,
    CancellationToken cancellationToken)
{
    var viewModel = new PvpNewSeasonStartViewModel(rankClassType, scoreRankLevel);
    var argument = new PvpNewSeasonStartViewController.Argument(viewModel);
    var viewController = ViewFactory
        .Create<PvpNewSeasonStartViewController, PvpNewSeasonStartViewController.Argument>(argument);
    ViewController.PresentModally(viewController);

    await UniTask.WaitUntil(viewController.View.IsDestroyed, cancellationToken: cancellationToken);
}
```

**呼び出しタイミング**:
- `OnViewDidLoad()` → `ShowPreviousSeasonResult()` 完了後に呼び出される
- 前シーズン結果画面が閉じられた後、自動的に表示される

**表示データの取得元**:
- `useCaseModel.PvpTopUserState.PvpUserRankStatus.PvpRankClassType`: 新シーズンランク帯
- `useCaseModel.PvpTopUserState.PvpUserRankStatus.ToScoreRankLevel()`: ランクレベル

### 使用しているDBデータ

この画面は、表示用データとしてユーザーのPvpランク情報を参照しますが、APIやマスタデータの直接参照はなく、PvpTop画面で取得したデータをそのまま表示するシンプルな構成です。

#### ユーザーデータ（GameFetch）

| クライアント定義 | サーバー定義 | クライアント説明 |
|----------------|-------------|----------------|
| UserPvpStatus.PvpRankClassType | user_pvp_statuses.rank_class_type | プレイヤーの現在のランク帯（Bronze/Silver/Gold/Platinum） |
| UserPvpStatus.ScoreRankLevel | user_pvp_statuses.rank_level | ランク帯内のレベル（1～4） |

**備考**: これらのデータは PvpTopPresenter の `PvpTopUseCase.UpdateAndGetModel()` で取得され、その結果から `ShowNewSeasonStart()` に渡されます。この画面では表示のみを行い、データ取得やAPI呼び出しは行いません。

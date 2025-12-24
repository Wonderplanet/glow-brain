# PvP新シーズン開始画面

## 基本情報

| 項目 | 内容 |
|------|------|
| **画面名** | PvP新シーズン開始画面 |
| **画面種別** | モーダル画面 |

## 概要

PvP（決闘）シーズンが終了し、新しいシーズンが開始されたときに表示される演出画面です。プレイヤーが前シーズンで達成したランク（ブロンズ、シルバー、ゴールド、プラチナのいずれか）に基づいて、新シーズンの開始ランクと詳細なランク情報を表示します。この画面はPvPトップ画面への遷移前に表示される演出として機能します。

## プレイヤーができること

### 画面を閉じる

- **閉じるボタンをタップ**: 画面を閉じてPvPトップ画面に戻る
- **タップして閉じる案内表示**: アニメーション完了後、タップ可能な状態になることを示す案内が表示される

## 画面の要素

### ランク表示エリア

- **ランクアイコン**: 前シーズンで達成したランク（ブロンズ、シルバー、ゴールド、プラチナ）を表す大きなアイコン
- **ランク名テキスト**: 「{ランク名}からスタート!!」という形式でランク名と詳細レベル情報を表示
  - 例：「ゴールド Ⅲ からスタート!!」
  - ランク名: ブロンズ、シルバー、ゴールド、プラチナのいずれか
  - レベル: Ⅰ～Ⅴの5段階

### アニメーション

- **開始アニメーション**: 画面が表示されると、ランクアイコンとテキストが演出付きで表示される
- **アニメーション完了後**: 以下の要素が表示される
  - 閉じるボタン
  - 「タップして閉じる」案内ラベル

## 画面遷移

### この画面への遷移

- **PvPトップ画面**: 前シーズン結果演出表示後に自動遷移
  - 前シーズンで獲得したランク情報を受け取る
  - 前シーズン結果ダイアログ表示後に続いて表示

### この画面からの遷移

- **PvPトップ画面**: 閉じるボタンタップ時に遷移
  - 新シーズンの開始ランクが確定した状態で遷移
  - 以降のPvP活動がこのランクから開始される

## ゲーム仕様・制約事項

### ランク制度

- **PvPランク**: プレイヤーのPvP実績に基づいて付与される
- **ランククラス**: ブロンズ、シルバー、ゴールド、プラチナの4段階
- **ランクレベル**: 各ランククラス内で、さらに Ⅰ～Ⅴ の5段階に細分化される
- **シーズン引き継ぎ**: 前シーズン終了時のランク情報がそのまま新シーズンの開始ランクになる

### 表示タイミング

- 新シーズン開始時、PvPトップ画面の初期化処理で表示
- 前シーズン結果演出（PvPPreviousSeasonResult画面）の直後に表示
- 画面が閉じられるまでユーザーインタラクションは制限される

### アニメーション挙動

- ランクアイコンとテキストは同時にアニメーション開始
- アニメーション再生時間はアニメーター定義に基づく（最小0.1秒）
- アニメーション完了後にボタンと案内が表示される

## 技術参考情報

### 画面実装パス

- `Assets/GLOW/Scripts/Runtime/Scenes/PvpNewSeasonStart/`

### 主要コンポーネント

#### Presenter
- `Presentation/Presenters/PvpNewSeasonStartPresenter.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpNewSeasonStart/Presentation/Presenters/PvpNewSeasonStartPresenter.cs)
  - ViewControllerの生成と画面表示を管理
  - ボタンタップ時の画面クローズ処理を実行

#### ViewController
- `Presentation/Views/PvpNewSeasonStartViewController.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpNewSeasonStart/Presentation/Views/PvpNewSeasonStartViewController.cs)
  - View との相互作用を管理
  - `SetUp(PvpNewSeasonStartViewModel)` メソッドで画面初期化
  - `OnCloseButtonTapped()` でボタンタップを処理

#### View
- `Presentation/Views/PvpNewSeasonStartView.cs`
  - ランクアイコン表示（RankingRankIcon）
  - ランクテキスト表示（UIText）
  - アニメーション制御（Animator）
  - タップ案内表示（VictoryResultTapLabelComponent）

#### ViewDelegate インターフェース
- `Presentation/Views/IPvpNewSeasonStartViewDelegate.cs`
  - `ViewDidLoad()`: 画面読み込み時の処理
  - `OnCloseButtonTapped()`: 閉じるボタンタップ時の処理

#### ViewControllerInstaller
- `Application/Installers/PvpNewSeasonStartViewControllerInstaller.cs`
  - DI設定（ViewController、Presenter、Argumentのバインディング）

### PvpNewSeasonStartViewModel の構造

```csharp
public record PvpNewSeasonStartViewModel(
    PvpRankClassType PvpRankClassType,    // ランククラス: Bronze, Silver, Gold, Platinum
    ScoreRankLevel ScoreRankLevel          // ランクレベル: I～V の数値
)
```

**プロパティの説明**:
- `PvpRankClassType`: 前シーズン終了時のランククラス（4段階）
- `ScoreRankLevel`: 各ランククラス内の詳細レベル（5段階）

### 画面初期化時の引数

```csharp
new PvpNewSeasonStartViewController.Argument(viewModel)
```

- `viewModel`: `PvpNewSeasonStartViewModel` インスタンス
  - 前シーズンで達成したランククラスとレベル情報を保持

### 呼び出し元

**PvpTopPresenter.cs** (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpTop/Presentation/Presenter/PvpTopPresenter.cs:381-392)

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

**呼び出しタイミング**: PvPトップ画面の`OnViewDidLoad`で、前シーズン結果演出表示直後に呼び出される

### 使用しているUI/演出コンポーネント

| コンポーネント | 説明 |
|-------------|------|
| **RankingRankIcon** | ランククラスのアイコン表示コンポーネント |
| **UIText** | ランク情報のテキスト表示 |
| **Animator** | ランクアイコンのアニメーション制御 |
| **VictoryResultTapLabelComponent** | タップ案内ラベル表示 |

### アニメーション制御

- **トリガー名**: `in` (Animator.StringToHash("in"))
- **アニメーション再生**: `_animator.SetTrigger(TriggerIn)`
- **再生時間**: アニメーター定義に基づく（デフォルト0.1秒）
- **完了待機**: `UniTask.Delay`でアニメーション完了を待つ

---

**最終更新**: 2025-12-24

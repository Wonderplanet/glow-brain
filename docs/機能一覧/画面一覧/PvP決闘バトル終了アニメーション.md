# PvP決闘バトル終了アニメーション

## 基本情報

| 項目 | 内容 |
|------|------|
| **画面名** | PvP決闘バトル終了アニメーション |
| **画面種別** | モーダル画面 |

## 概要

PvPバトル終了後、結果を表現する短いアニメーションを表示する画面です。バトル終了の理由（相手の砦HP破壊 または 時間終了）に応じた異なるアニメーションが再生され、プレイヤーと対戦相手の進出距離を比率で示すプログレッシュバーが表示されます。このアニメーション画面は自動的に再生され、プレイヤーはアニメーション完了後、画面をタップして結果詳細画面へ進むことができます。

## プレイヤーができること

### アニメーション観賞

#### 砦破壊時のアニメーション
- 相手の砦のHP がゼロになった場合に再生
- より短い時間（約1.2秒）でプログレッシュバーが満たされる
- 勝利時と敗北時で異なるアニメーション演出が表示される

#### 時間終了時のアニメーション
- バトル時間がゼロになった場合に再生
- より長い演出時間（約2秒）でプログレッシュバー が満たされる
- 初期アニメーション後、プログレッシュバー表示後に最終判定アニメーションが再生される

### 結果の確認

#### プログレッシュバーの表示
- プレイヤーの進出距離を左側のバーで表示
- 対戦相手の進出距離を右側のバーで表示
- バーの長さで互いの距離比率が視覚的に表現される
- 勝利時はプレイヤーバーが緑色、敗北時は赤色で表示される

### 画面を閉じる

#### タップで画面を閉じる
- アニメーション完了後、画面のいずれかをタップすると次の画面に進む
- アニメーション再生中のタップはトースト表示で無効化される
- 自動的に結果詳細画面（PvPバトル結果画面）へ遷移

## 画面の要素

### アニメーション表示エリア
- **背景アニメーション**: バトル終了を表現する背景演出
- **テキスト・エフェクト**: 勝敗を表示するテキストとビジュアルエフェクト

### プログレッシュバーエリア
- **対戦相手距離バー**: 対戦相手の進出距離を表示（デフォルトは灰色）
- **プレイヤー距離バー（勝利時）**: 勝利時のプレイヤー進出距離を表示（緑色）
- **プレイヤー距離バー（敗北時）**: 敗北時のプレイヤー進出距離を表示（赤色）

### ボタン・操作エリア
- **画面タップ領域**: 画面全体がタップ可能
  - アニメーション完了前: 無効（トースト表示）
  - アニメーション完了後: 有効

## 画面遷移

### この画面への遷移
- **InGame画面（PvPバトル）**: バトル終了判定時に自動で表示
  - PvpResultModel（勝敗と終了理由）がViewModelとして渡される

### この画面からの遷移
- **PvPバトル結果画面**: 画面をタップして閉じた後に表示
  - 同じ PvpResultViewModel が渡される
  - 詳細な報酬情報やリトライ選択肢が表示される

## ゲーム仕様・制約事項

### バトル終了の判定方法

#### 砦HP破壊による終了（PvpFinishType.OutPostHpZero）
- プレイヤーまたは対戦相手の砦HP がゼロになった場合
- 砦が破壊されたプレイヤー側が敗北
- HP破壊時の進出距離は `Empty`（0.0）か `One`（1.0）のいずれかになる

#### 時間終了による終了（PvpFinishType.MaxDistance）
- バトル時間が終了した場合
- 最も遠くまで進出したユニットの距離で勝敗が決定
- 距離が同じ場合はプレイヤー側が勝利と判定される

### ギブアップ判定
- プレイヤーがバトル中にギブアップした場合は、進出距離に関わらず敗北
- ギブアップ時はプレイヤーの距離 = Empty（0.0）、対戦相手の距離 = One（1.0）で表示

### 進出距離の計算（時間終了時のみ）
- 各側の残存ユニットの最大X座標を算出
- プレイヤー距離 = プレイヤー最大距離 / (プレイヤー最大距離 + 対戦相手最大距離)
- 対戦相手距離 = 対戦相手最大距離 / (プレイヤー最大距離 + 対戦相手最大距離)
- 両側ユニットが全滅している場合は両者 0.5 で表示

### 表示制御ロジック
- **初期化**: ViewDidLoad時にプログレッシュバーを初期化（fill = 0.0）
- **アニメーション開始**: PlayFinishAnimation() でバトル終了の理由に応じたアニメーションを開始
- **クローズボタン制御**: アニメーション再生中はボタン無効、完了後に有効化
- **タップ無効化**: アニメーション再生中のタップは CommonToastWireFrame.ShowInvalidOperationMessage() で通知

## 技術参考情報

### 画面実装パス
- `Assets/GLOW/Scripts/Runtime/Scenes/PvpBattleFinishAnimation/`

### 主要コンポーネント

#### Presenter
- `Presentation/Presenter/PvpBattleFinishAnimationPresenter.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpBattleFinishAnimation/Presentation/Presenter/PvpBattleFinishAnimationPresenter.cs)
  - ViewDidLoad時にアニメーション再生開始
  - 画面タップ時の処理（アニメーション完了判定）
  - アニメーション完了後は OnCloseButtonTappedAction を実行して画面を閉じる

#### View
- `Presentation/View/PvpBattleFinishAnimationView.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpBattleFinishAnimation/Presentation/View/PvpBattleFinishAnimationView.cs)
  - InitializeProgressBar(): プログレッシュバーの初期化と表示切り替え
  - PlayOutPostHpZeroFinishAnimation(): 砦破壊時のアニメーション再生（duration: 0.2秒、delay: 1.2秒）
  - PlayTimeUpFinishAnimation(): 時間終了時のアニメーション再生（duration: 1.0秒）
  - PlayProgressBarAnimation(): DOTween を使用したプログレッシュバーの段階的な満填

#### ViewController
- `Presentation/View/PvpBattleFinishAnimationViewController.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpBattleFinishAnimation/Presentation/View/PvpBattleFinishAnimationViewController.cs)
  - Argument: PvpResultViewModel を受け取る
  - InitializeProgressBar(): 結果タイプに応じてプログレッシュバーの表示を初期化
  - PlayFinishAnimation(): 終了理由に応じたアニメーションの非同期再生
  - SetCloseButtonInteractable(): ボタンの有効/無効を制御

#### ViewDelegate
- `Presentation/View/IPvpBattleFinishAnimationViewDelegate.cs`
  - OnViewDidLoad(): ビューロード時の初期化処理をトリガー
  - OnScreenTapped(): 画面タップ時の処理

#### Installer
- `Application/View/PvpBattleFinishAnimationViewControllerInstaller.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpBattleFinishAnimation/Application/View/PvpBattleFinishAnimationViewControllerInstaller.cs)
  - DIコンテナの設定

### PvpResultViewModel の構造
```csharp
public record PvpResultViewModel(
    PvpResultEvaluator.PvpResultType ResultType,           // 勝敗（Victory / Defeat）
    PvpMaxDistanceRatio PlayerDistanceRatio,               // プレイヤーの進出距離比率
    PvpMaxDistanceRatio OpponentDistanceRatio,             // 対戦相手の進出距離比率
    PvpResultEvaluator.PvpFinishType FinishType)           // 終了理由
```

#### 列挙型定義

**PvpResultEvaluator.PvpResultType**
- `Victory`: 勝利
- `Defeat`: 敗北

**PvpResultEvaluator.PvpFinishType**
- `OutPostHpZero`: 砦HP破壊による終了
- `MaxDistance`: 時間終了（距離判定）による終了

**PvpMaxDistanceRatio**
- `Value`: float 型の距離比率（0.0 ～ 1.0）
- `Empty`: 0.0
- `One`: 1.0

### 画面初期化時の引数
```csharp
PvpBattleFinishAnimationViewController.Argument(PvpResultViewModel viewModel)
```
- `viewModel`: PvpResultViewModel - バトル結果を表現するビューモデル

### 使用しているDBデータ

この画面はデータベースのマスタデータやユーザーデータを直接参照しません。代わりに、バトル処理後の結果データ（PvpResultModel）をビューモデルに翻訳して使用します。

**参考**: マスタデータやユーザーデータは、この画面が遷移する先の **PvPバトル結果画面** で取得・表示されます。

### 呼び出しているAPI

この画面はAPI呼び出しを行いません。バトル終了後の計算結果とアニメーション表示のみを実行します。

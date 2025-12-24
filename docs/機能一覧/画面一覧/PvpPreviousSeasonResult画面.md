# PvpPreviousSeasonResult画面

## 基本情報

| 項目 | 内容 |
|------|------|
| **画面名** | PvP/決闘前シーズン結果画面 |
| **画面種別** | モーダル画面 |

## 概要

PvP前シーズン（過去シーズン）の最終成績を表示するモーダル画面です。プレイヤーが新しいシーズンに突入する際に、前シーズンでの最終的なランク、獲得ポイント、ランキングの順位、そして獲得した報酬を表示します。画面には演出アニメーションが含まれており、ランク表示時に視覚的なフィードバックが演出されます。

## プレイヤーができること

### 結果確認
- **前シーズンの最終ランク表示**: 前シーズン終了時のランク（ブロンズ～ゴールドなど）と細かいレベルが表示される
- **前シーズンの最終ポイント表示**: 前シーズン終了時に獲得していた総ポイント数を表示
- **前シーズンの順位表示**: ランキングでの順位（1位～3位）または「ランク外」の表示
- **獲得報酬表示**: 前シーズン終了時に獲得できる報酬（ダイヤ、アイテムなど）の一覧表示

### 画面操作
- **画面を閉じる**: 画面下部の「タップして閉じる」ボタンをタップしてモーダルを閉じることができる
  - アニメーション演出が完了する前は、ボタンは非表示で画面をタップしても反応しない
  - アニメーション完了後にボタンが表示される

## 画面の要素

### ランク表示エリア
- **ランク情報**: ランククラス（Bronze, Silver, Gold など）と細分化されたレベルを表示
- **ランクアイコン**: ランククラスに対応したビジュアルアイコンを表示
- **アニメーション演出**: ページオープン時にランク情報にアニメーションを再生
  - 順位が1位～3位の場合と、ランク外の場合で異なるアニメーション

### ポイント・ランキング表示エリア
- **総ポイント表示**: 前シーズン終了時の総ポイント数をカンマ区切り表示
  - 例：「10,500」
- **順位表示**: 前シーズンランキングでの順位
  - 1位～3位：順位数（例：「1位」）
  - ランク外または参加なし：「ランク外」

### 報酬リスト
- **獲得報酬一覧**: 前シーズン終了時に獲得できる報酬アイテムが表示される
  - 報酬がロードされるまでは非表示
  - ロード完了後に順番にアニメーション付きで表示される

### クローズボタン
- **タップインジケーター**: 「タップして閉じる」というラベルが表示される
  - アニメーション演出が完了するまでは非表示状態
  - 演出完了後に画面下部に表示される

## 画面遷移

### この画面への遷移
- **PvpTop画面（PvP/決闘トップ画面）**:
  - 新しいシーズンが開始される際、PvpTop画面の初期化時に前シーズン結果がある場合、自動的にこの画面が表示される
  - 前シーズンで結果が存在する場合のみ表示（1回限りの演出）

### この画面からの遷移
- **PvpNewSeasonStart画面（新シーズン開始画面）**:
  - この画面を閉じた後、新シーズン開始画面へ自動的に遷移する
  - 演出完了後にプレイヤーの閉じる操作を待つ

## ゲーム仕様・制約事項

### 表示条件
- 前シーズン結果が存在する場合のみ表示
- PvpTop画面の初回読み込み時のみ表示される（その後キャッシュから前シーズン結果は削除される）
- 複数回PvpTop画面を訪問しても、この画面は一度だけの表示

### ランク情報の表示ルール
- **ランククラス**: ブロンズ、シルバー、ゴールド、プラチナなどのクラス分けに対応
- **レベル**: 各ランククラス内での細分化されたレベル表示
  - 見た目上は「Lv.1」から「Lv.10」などで表示される場合がある

### ランキング順位の表示ルール
- **1位～3位**: その順位が表示される
- **4位以下またはランク外**: 「ランク外」と表示される
- **シーズン未参加**: 「ランク外」と表示される

### アニメーション動作
- ページ表示時の自動演出：
  1. ランク情報のアニメーション開始
  2. ポイント・ランキング情報の表示
  3. アニメーション完了待機
  4. 報酬リストのロード・表示
  5. 「タップして閉じる」ボタン表示

### 報酬表示
- 前シーズン終了時に獲得できる報酬を表示
- 報酬は複数存在する場合がある
- 報酬の種類（ダイヤ、ゴールド、アイテムなど）に応じたアイコン表示

## 技術参考情報

### 画面実装パス
- `Assets/GLOW/Scripts/Runtime/Scenes/PvpPreviousSeasonResult/`

### 主要コンポーネント

#### Presenter
- `Presentation/Presenters/PvpPreviousSeasonResultPresenter.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpPreviousSeasonResult/)

#### ViewController
- `Presentation/Views/PvpPreviousSeasonResultViewController.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpPreviousSeasonResult/)

#### View
- `Presentation/Views/PvpPreviousSeasonResultView.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpPreviousSeasonResult/)

#### ViewModel
- `Presentation/ViewModels/PvpPreviousSeasonResultViewModel.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpPreviousSeasonResult/)

#### Domain Model
- `Domain/Models/PvpPreviousSeasonResultAnimationModel.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpPreviousSeasonResult/)

#### Translator
- `Presentation/Presenters/PvpPreviousSeasonResultTranslator.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpPreviousSeasonResult/)

### 画面初期化時の引数

```csharp
PvpPreviousSeasonResultViewController.Argument(ViewModel: PvpPreviousSeasonResultViewModel)
```

引数として `PvpPreviousSeasonResultViewModel` を受け取り、画面の初期化時に表示データを設定します。

### PvpPreviousSeasonResultViewModel の構造

| プロパティ | 説明 |
|-----------|------|
| `PvpRankClassType` | 前シーズン終了時のランククラス（Bronze, Silver, Gold, Platinum など） |
| `RankClassLevel` | ランククラス内での細分化されたレベル |
| `Point` | 前シーズン終了時の総ポイント数 |
| `Ranking` | ランキングでの順位（1位～3位の場合）またはランク外を示す値 |
| `PvpRewards` | 前シーズン終了時に獲得できる報酬リスト（PlayerResourceIconViewModel のコレクション） |

### 使用しているDBデータ

この画面は API から直接取得したデータを表示するため、マスタデータへの直接的な依存はありません。ただし、表示される報酬情報はサーバーから返却された後、プレイヤーリソース情報に変換されます。

#### API から取得するデータ

**PvpService.Top() API コール**
- **POST `api/pvp/top`**: PvpTop画面の初期データを取得
  - 前シーズン結果情報（`pvpPreviousSeasonResult`）を含む
  - ユーザーのPvP統計情報も同時に取得
  - 用途: PvpTop画面の全データ初期化時に呼び出され、その際に前シーズン結果が返却される場合がある

#### レスポンスデータ構造

API レスポンスの `pvpPreviousSeasonResult` フィールドに以下の情報が含まれます（存在する場合）：

| フィールド | 説明 |
|-----------|------|
| `pvpRankClassType` | ランククラス |
| `rankClassLevel` | ランク内レベル（整数値） |
| `score` | 前シーズンの総ポイント |
| `ranking` | ランキング順位（0 の場合は未参加またはランク外） |
| `pvpRewards` | 報酬リスト |

### 画面表示フロー

1. **PvpTop画面初期化時**
   - `PvpTopPresenter.OnViewDidLoad()` が実行
   - `PvpTopUseCase.UpdateAndGetModel()` を呼び出し、API から最新データを取得（キャッシュ判定により取得方法が異なる）
   - `PvpTopModelFactory.CreatePvpPreviousSeasonResultAnimationModel()` で `PvpPreviousSeasonResultAnimationModel` を生成

2. **前シーズン結果表示判定**
   - `PvpPreviousSeasonResultAnimationModel` が Empty でない場合、この画面を表示
   - `PvpPreviousSeasonResultTranslator.Translate()` で ViewModel に変換

3. **画面表示とアニメーション**
   - `PvpPreviousSeasonResultViewController` でモーダル表示
   - `PvpPreviousSeasonResultView.SetUp()` で ViewModel を画面に反映
   - `PlayRankAnimation()` でランク表示アニメーション開始
   - アニメーション完了後に報酬リストを表示
   - ボタンを表示可能にする

4. **画面クローズ**
   - ボタンタップで `PvpPreviousSeasonResultPresenter.OnCloseButtonClicked()` 実行
   - `ViewController.Dismiss()` でモーダル画面を閉じる

### アニメーション実装

**ランク表示アニメーション**
- Animator に `Rank` パラメータを設定し、順位に応じた異なるアニメーションを再生
  - 1位: `rankAnimValue = 1`
  - 2位: `rankAnimValue = 2`
  - 3位: `rankAnimValue = 3`
  - ランク外: `rankAnimValue = 0`
  - 未参加: `rankAnimValue = 4`
- アニメーション完了を `RankingResultPanel@Loop` ステートで判定

**報酬リスト表示**
- `PlayerResourceIconList` コンポーネント の `SetupAndReload()` で報酬を表示
- 報酬のロード完了を `onComplete` コールバックで検出

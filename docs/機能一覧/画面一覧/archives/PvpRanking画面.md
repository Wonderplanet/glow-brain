# PvpRanking画面（PvP決闘ランキング画面）

## 基本情報

| 項目 | 内容 |
|------|------|
| **画面名** | PvP決闘ランキング画面 |
| **画面種別** | メイン画面 |

## 概要

PvP決闘ランキング画面は、プレイヤーが決闘ユーザー同士の実力を競い合うランキングシステムの閲覧画面です。現シーズンと前シーズンの2つのランキングを切り替えて表示でき、プレイヤーの順位、スコア、保有キャラクター、エンブレムなどが確認できます。また、自分のランキング参加状況（集計中、未参加、除外中など）もリアルタイムで表示されます。

## プレイヤーができること

### ランキング閲覧

#### 現シーズンランキングの表示
- 現在開催中のシーズンの決闘ランキングを表示
- TOP 100までのユーザーが順位順に一覧表示される
- 各ユーザーの以下の情報が確認できる：
  - 順位（ランク）
  - ユーザー名
  - PvPスコア（ポイント）
  - 使用キャラクター（ユニット）のアイコン
  - ユーザーのエンブレム（ステータスアイコン）
  - ランクアップ時の階級（ブロンズ、シルバー、ゴールド、プラチナなど）
  - ランクレベル（各階級内でのレベル）

#### 前シーズンランキングの表示
- 前回のシーズンの最終ランキング結果を表示
- 現シーズンと同様のユーザー情報が表示される
- 集計中の状態が表示されない（すべて確定した結果を表示）

#### 自分の情報表示
- 現在のランキング上での自分の順位とスコアが表示される
- 以下の状態が画面上で視覚的に区別される：
  - **通常状態**: 通常のランキング参加者として表示
  - **集計中**: スコアが更新中の状態を示すインジケーターが表示
  - **未参加**: ランキングに参加していない場合の表示（未参加理由は問わない）
  - **除外中**: 何らかの理由でランキングから除外されている状態の表示
  - **未達成**: ランキング参加条件（最小スコア）に未到達の状態

### ランキング表示の切り替え

#### シーズン切り替えボタン
- 「現シーズン」と「前シーズン」のタブボタンが用意されている
- アクティブなタブは視覚的に強調表示（オン状態）される
- 前シーズンのタブをタップするとランキングが更新され、前シーズンのデータが表示される
- 現シーズンのタブをタップすると、現在のランキングデータに戻る

### ヘルプ・ナビゲーション

#### ヘルプボタン
- ランキングシステムの遊び方を確認できる
- 現在の実装では「ヘルプボタンが押されました|n|n遊び方表示予定」というトーストメッセージが表示される

#### 戻るボタン
- ホーム画面へ戻る
- ランキング画面を閉じる

## 画面の要素

### ヘッダー部分
- **戻るボタン**: 画面を閉じてホーム画面に戻る
- **ヘルプボタン**: ランキングシステムの説明を表示（実装予定）

### メイン表示エリア

#### 自分のランキング情報セクション
- ユーザー名（自分の名前）
- PvPスコア（現在のポイント）
- 使用キャラクター（ユニット）のアイコン
- ユーザーエンブレム
- 現在のランク（順位）
- ランク階級（ブロンズなど）
- ランクレベル
- ランキング参加状況のインジケーター
- 集計中の場合は視覚的なマーカーが表示される

#### ランキング一覧セクション
- TOP 100ユーザーのランキング一覧
- 各ユーザーカードに以下が表示される：
  - 順位番号
  - ユーザー名
  - PvPスコア
  - 使用ユニットのアイコン
  - ユーザーエンブレム
  - ランク階級とレベル
  - 自分の場合は特別な表示がされる可能性あり

### ボタンエリア

#### シーズン切り替えボタン
- **「現シーズン」ボタン**: 現在のシーズンランキングを表示
  - 押下時は視覚的にオン状態になる
  - 既に現シーズンが表示されている場合は反応しない
- **「前シーズン」ボタン**: 前回のシーズンランキングを表示
  - 押下時は視覚的にオン状態になる
  - 既に前シーズンが表示されている場合は反応しない

### ランキング背景（装飾要素）
- 現シーズンと前シーズンで異なる背景バンドが表示される
- この背景切り替えにより、ユーザーが視覚的に現在どちらのシーズンを見ているかが判断できる

## 画面遷移

### この画面への遷移
- **PvpTop画面（PvP上部メニュー）**: 「ランキング」ボタンをタップ
- **他のPvP関連画面**: PvPメニューからランキング画面への遷移

### この画面からの遷移
- **ホーム画面**: 戻るボタンをタップ
  - ランキング画面を終了し、ホーム画面に戻る

## ゲーム仕様・制約事項

### ランキング参加条件
- スコア（PvPポイント）が一定値（ランキング参加の最小スコア）に到達していることが参加条件
- 参加条件に未達の場合、「未参加」として表示される

### 表示ユーザー範囲
- TOP 100までのランキング上位ユーザーのみが表示される
- スコア0のユーザー（ランキング参加条件未達）は除外される

### スコア計算と集計
- **現シーズン**の場合：
  - ユーザーのゲーム内スコア（UserPvpStatusModel.Score）と異なる場合は「集計中」と判定される
  - ランキング上のスコアとプレイヤーのリアルタイムスコアが異なる場合、集計処理が進行中を示す

- **前シーズン**の場合：
  - スコアは完全に確定している
  - 集計中フラグは表示されない
  - ランクが確定している場合のみランキング参加と判定される

### 除外ランキング処理
- ユーザーが除外設定されている場合、ランキングに参加していてもランキング対象外となる
- これは不正検出やシステムメンテナンス時に使用される可能性がある

### ランク階級システム
- **階級の種類**: ブロンズ、シルバー、ゴールド、プラチナなど複数の階級が存在
- **階級決定基準**: PvPスコアに基づいて自動決定される
  - マスタデータ「MstPvpRank」に定義された各階級の必須最小スコア（RequiredLowerPoint）により判定
  - プレイヤーのスコアを超える最大要件値を持つ階級が当該プレイヤーの階級となる

### ランクレベル
- 同じ階級内でのレベル表示
- ランク階級とセットで表示される

## 技術参考情報

### 画面実装パス
- `Assets/GLOW/Scripts/Runtime/Scenes/PvpRanking/`

### 主要コンポーネント

#### Presenter
- `Presentation/Presenters/PvpRankingPresenter.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpRanking/Presentation/Presenters/PvpRankingPresenter.cs)

#### ViewController
- `Presentation/Views/PvpRankingViewController.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpRanking/Presentation/Views/PvpRankingViewController.cs)

#### View
- `Presentation/Views/PvpRankingView.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpRanking/Presentation/Views/PvpRankingView.cs)

#### ViewModels
- `Presentation/ViewModels/PvpRankingViewModel.cs` - 現シーズン・前シーズンのランキングデータを保持
- `Presentation/ViewModels/PvpRankingElementViewModel.cs` - ランキング全体（自分のデータ+他ユーザーリスト）を保持
- `Presentation/ViewModels/PvpRankingMyselfUserViewModel.cs` - プレイヤー自身のランキング情報
- `Presentation/ViewModels/PvpRankingOtherUserViewModel.cs` - 他のプレイヤーのランキング情報

#### UseCase
- `Domain/UseCases/PvpRankingUseCase.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpRanking/Domain/UseCases/PvpRankingUseCase.cs)

#### ModelFactory
- `Domain/ModelFactories/PvpRankingModelFactory.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpRanking/Domain/ModelFactories/PvpRankingModelFactory.cs)

### データ構造

#### PvpRankingViewModel
- **CurrentRanking**: PvpRankingElementViewModel - 現シーズンのランキング全体
- **PrevRanking**: PvpRankingElementViewModel - 前シーズンのランキング全体

#### PvpRankingElementViewModel
- **OtherUserViewModels**: IReadOnlyList<PvpRankingOtherUserViewModel> - 他のプレイヤーのランキング情報リスト
- **MyselfUserViewModel**: PvpRankingMyselfUserViewModel - 自分のランキング情報

#### PvpRankingMyselfUserViewModel
- **UserName**: プレイヤー名
- **Score**: PvPスコア
- **EmblemIconAssetPath**: エンブレムアイコンのアセットパス
- **UnitIconAssetPath**: ユニット（キャラクター）アイコンのアセットパス
- **Rank**: ランキング上での順位
- **RankClassType**: ランク階級（ブロンズ、シルバーなど）
- **RankLevel**: 階級内でのレベル
- **CalculatingRankings**: 集計中フラグ（現シーズンでスコアが更新中の場合、true）
- **ViewStatus**: 表示ステータス（Normal/EmptyCurrentRanking/EmptyPrevRanking/NotAchieveRanking/ExcludeRanking）
- **PvpUserRankStatus**: ランク別のステータス情報

#### PvpRankingOtherUserViewModel
- **UserName**: プレイヤー名
- **Score**: PvPスコア
- **EmblemIconAssetPath**: エンブレムアイコンのアセットパス
- **UnitIconAssetPath**: ユニットアイコンのアセットパス
- **Rank**: ランキング上での順位
- **IsMyself**: 自分かどうかのフラグ（ランキング内に自分が存在する場合に他ユーザーリストに含まれるため、判別のために使用）
- **RankClassType**: ランク階級
- **RankLevel**: 階級内でのレベル
- **PvpUserRankStatus**: ランク別のステータス情報

### 画面初期化時の引数

```csharp
PvpRankingViewController.Argument(
    PvpRankingViewModel = new PvpRankingViewModel(
        CurrentRanking: PvpRankingElementViewModel,  // 現シーズンランキング
        PrevRanking: PvpRankingElementViewModel      // 前シーズンランキング
    )
)
```

### APIデータフロー

1. **PvpRankingUseCase.GetPvpRanking()**: 呼び出し時に以下を実行
   - `PvpService.Ranking(cancellationToken, false)` - 現シーズンランキング取得
   - `PvpService.Ranking(cancellationToken, true)` - 前シーズンランキング取得
   - 両方の結果を並行（UniTask.WhenAll）で待機

2. **PvpRankingModelFactory.CreatePvpRankingElementUseCaseModel()**:
   - APIから取得したランキング結果モデルをプレゼンテーション層用に変換
   - マスタデータ（MstEmblem, MstCharacter, MstPvpRank）とユーザーデータを組み合わせ

### 使用しているマスタデータ

#### マスタデータ

| テーブル名 | カラム名 | 説明 |
|-----------|---------|------|
| **MstEmblem**<br>エンブレム（ステータスアイコン） | Id | エンブレムID |
| | AssetKey | エンブレムアイコン表示用のアセットキー |
| **MstCharacter** (mst_units)<br>キャラクター基本情報 | Id | キャラクターID（ユニットID） |
| | AssetKey | キャラクター画像表示用のアセットキー |
| **MstPvpRank**<br>PvPランク定義 | RankClassType | ランク階級（ブロンズ、シルバー、ゴールドなど） |
| | RankLevel | 階級内でのレベル |
| | RequiredLowerPoint | その階級に必要な最小スコア |

#### ユーザーデータ（GameFetch）

| テーブル名 | カラム名 | 説明 |
|-----------|---------|------|
| **UserProfile**<br>ユーザープロフィール | Name | ユーザー名 |
| | MstEmblemId | 設定中のエンブレムID |
| | MstUnitId | 代表ユニットID（ホーム画面に表示されるユニット） |
| **UserPvpStatus**<br>ユーザーPvPステータス | Score | 現シーズンの現在スコア（ゲーム内リアルタイム） |

### 呼び出しているAPI

#### ランキング初期化時
- **GET `/api/pvp/ranking`**: PvpRankingランキング取得API
  - パラメータ: `isPreviousSeason` (bool) - false:現シーズン、true:前シーズン
  - 用途: 現シーズン・前シーズンの決闘ランキングデータを取得
  - レスポンス形式: PvpRankingResultData
    - OtherUserRanking: ランキング上位ユーザーの一覧（最大TOP100）
    - MyRanking: 自分のランキング情報（順位、スコア、除外フラグなど）


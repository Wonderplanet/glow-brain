# PvP決闘対戦相手詳細画面

## 基本情報

| 項目 | 内容 |
|------|------|
| **画面名** | PvP決闘対戦相手詳細画面 |
| **画面番号** | 106-2-1（PvP/決闘画面から） |
| **画面種別** | モーダル画面 |

## 概要

PvPトップ画面で表示される対戦相手の詳細情報を確認するモーダル画面です。プレイヤーは選択した対戦相手のプロフィール、パーティー構成、ステータスなどを詳しく確認してから対戦を開始するかどうかを判断できます。

## プレイヤーができること

### 対戦相手の情報を確認する

#### プロフィール情報
- **相手プレイヤー名**: 対戦相手のユーザー名
- **相手のレベル**: プレイヤーレベル
- **勝利ポイント**: この対戦に勝つと獲得できるポイント量
- **総合ポイント**: 相手プレイヤーの累積PvPポイント
- **称号/紋章**: 相手が装備している紋章やアバター要素
- **PvPランク**: 相手の現在のランク（ランク帯のアイコンが表示される）

#### パーティー構成の確認
- **パーティー総合ステータス**: 攻撃力・防御力・HPなどの合計値
  - ステータスが自分のパーティーより高い場合、上昇矢印が表示される
- **編成ユニット一覧**: 相手が編成している最大5体のユニット情報
  - ユニットアイコン
  - ユニット名
  - ユニットレベル
  - ユニットグレード（限界突破の段階）
  - ロール（アタッカー・ディフェンダーなど）
  - カラー（属性）
  - レアリティ（枠の色で区別される）

### この情報からできる判断
- **相手の強さ評価**: 総合ステータスから相手との実力差を判断
- **パーティー構成分析**: 編成ユニットの種類やレアリティから戦い方を予想
- **勝利の見込み評価**: ステータス比較で勝利可能性を判断

### 画面を閉じる
- **閉じるボタン**: モーダル画面を閉じてPvPトップ画面に戻る

## 画面の要素

### ヘッダー部分
- 閉じるボタン（×ボタン）
- 「対戦相手情報」などのタイトル表示

### プロフィール情報エリア
- **相手のプロフィール画像/キャラクターアイコン**
  - メインキャラクター（パーティー編成の1番目）のアイコン
- **相手のプレイヤー情報**
  - プレイヤー名
  - 勝利時のポイント獲得量（「+◎◎」の形式）
  - 総合PvPポイント
- **紋章/エンブレム表示**
  - プレイヤーが装備している紋章アイコン（装備していない場合は非表示）
- **PvPランクアイコン**
  - 現在のランク（ブロンズ・シルバー・ゴールド・プラチナ・ダイヤモンドなど）
  - ランク内のスコアランクレベル（例：ダイヤモンド1）

### パーティー情報エリア
- **総合ステータス表示**
  - 攻撃力・防御力・HP などを合計値で表示
  - 自分のパーティーより高い場合に上昇矢印マークが表示
- **パーティー編成ユニット一覧**
  - 5体までのユニットアイコン
  - 各ユニットのアイコンには以下の情報が重ねられて表示される
    - ユニットレベル
    - ユニットグレード（限界突破段階）
    - ロールアイコン
    - カラー（属性）アイコン
    - レアリティフレーム（背景色）

### 画面トランジション
- **出現アニメーション**
  - プロフィール情報とパーティー情報が段階的にスケールアップして表示される
  - 演出効果で相手情報の重要性を演出

## 画面遷移

### この画面への遷移
- **PvPトップ画面**: 対戦相手情報アイコンをタップ
  - 各対戦相手のアイコン横に情報表示ボタンがあり、タップするとこのモーダルが開く
  - 相手プレイヤーの詳細情報を確認してから対戦を判断できる

### この画面からの遷移
- **PvPトップ画面**: 閉じるボタンをタップ
  - 確認した相手の詳細情報を考慮して、対戦を開始するか相手を選び直すかを判断

## ゲーム仕様・制約事項

### 表示データ
- **リアルタイム更新なし**: このモーダルを開いている間に相手のデータが変わることはない
- **スナップショット表示**: モーダルを開いた時点での相手情報を表示
- **パーティー編成**: 最大5体のユニット構成を表示

### ステータス表示
- **総合ステータス**: 編成ユニットの攻撃力・防御力・HPを合計したもの
- **上昇矢印表示**: プレイヤーのパーティーの総合ステータスより相手が高い場合のみ表示
  - 自分より弱い場合は矢印は表示されない

### ユニット情報
- **レアリティフレーム**: R（白）、SR（青）、SSR（金）、UR（虹色）など
- **ロールアイコン**: 各ユニットのロール（役割）を視覚的に表示
- **カラー表示**: ユニットのカラー属性（火・水・風・光・闇など）

### ランク表示
- **PvPランク**: 複数のランク階級（ブロンズ～ダイヤモンド）
- **スコアランクレベル**: 各ランク内での細かいレベル（例：ダイヤモンド1～5）
- **ランクアイコン**: ランクごとに異なるアイコンが表示される

## 技術参考情報

### 画面実装パス
- `Assets/GLOW/Scripts/Runtime/Scenes/PvpOpponentDetail/`

### 主要コンポーネント

#### Presenter
- `Presentation/PvpOpponentDetailPresenter.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpOpponentDetail/Presentation/PvpOpponentDetailPresenter.cs)
  - 画面の初期化ロジック
  - ViewControllerへのセットアップ命令

#### ViewController
- `Presentation/Views/PvpOpponentDetailViewController.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpOpponentDetail/Presentation/Views/PvpOpponentDetailViewController.cs)
  - モーダル画面の制御
  - `Argument`: `PvpTopOpponentViewModel` を受け取る
  - 閉じるボタンのハンドリング

#### View
- `Presentation/Views/PvpOpponentDetailView.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpOpponentDetail/Presentation/Views/PvpOpponentDetailView.cs)
  - UIの実装詳細
  - 各UIコンポーネントへのデータ設定
  - アニメーション制御

#### Components
- `Presentation/Components/PvpOpponentDetailUnitIcon.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpOpponentDetail/Presentation/Components/PvpOpponentDetailUnitIcon.cs)
  - パーティーユニットアイコンの表示制御
  - ユニット情報（レベル、グレード、ロール、カラー、レアリティ）の表示

### Application層（DI設定）
- `Application/PvpOpponentDetailViewInstaller.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpOpponentDetail/Application/PvpOpponentDetailViewInstaller.cs)
  - Zenjectコンテナへのバインディング
  - ViewControllerの引数（Argument）のバインディング

### PvpTopOpponentViewModelの構造

```csharp
public record PvpTopOpponentViewModel(
    UserMyId UserId,                                      // 対戦相手のユーザーID
    UserName UserName,                                    // プレイヤー名
    CharacterIconAssetPath CharacterIconAssetPath,        // メインキャラクターのアイコンパス
    EmblemIconAssetPath EmblemIconAssetPath,              // 紋章のアイコンパス
    PvpPoint Point,                                       // 勝利時の獲得ポイント
    PvpPoint TotalPoint,                                  // 総合PvPポイント
    PvpUserRankStatus PvpUserRankStatus,                  // ランク情報（ランク階級+スコアランク）
    IReadOnlyList<PvpTopOpponentPartyUnitViewModel> PartyUnits,  // パーティーユニット情報
    TotalPartyStatus TotalPartyStatus,                    // 総合ステータス（攻撃力、防御力、HP）
    TotalPartyStatusUpperArrowFlag TotalPartyStatusUpperArrowFlag // 上昇矢印の表示判定
);
```

### パーティーユニット情報の構造

```csharp
public record PvpTopOpponentPartyUnitViewModel(
    CharacterIconAssetPath UnitIconAssetPath,     // ユニットアイコンパス
    CharacterUnitRoleType RoleType,               // ロール（アタッカー、ディフェンダーなど）
    CharacterColor Color,                         // カラー属性（火・水・風・光・闇）
    Rarity Rarity,                                // レアリティ（R, SR, SSR, UR）
    UnitLevel Level,                              // ユニットレベル
    UnitGrade Grade                               // ユニットグレード（限界突破段階）
);
```

### 画面初期化時の引数

```csharp
PvpOpponentDetailViewController.Argument(PvpTopOpponentViewModel PvpTopOpponentViewModel)
```

PvPトップ画面の `PvpTopPresenter.OnOpponentInfoButtonTapped()` メソッドで生成される。
対戦相手の詳細情報ViewModel全体が渡される。

### 画面の表示フロー

1. **PvpTopPresenter** で「対戦相手情報」ボタンをタップ
2. `ViewFactory.Create<PvpOpponentDetailViewController, PvpOpponentDetailViewController.Argument>(argument)` でコントローラーを生成
3. `ViewController.PresentModally(controller)` でモーダル表示
4. **PvpOpponentDetailPresenter** の `OnViewDidLoad()` が実行
5. **ViewController** の `SetUp()` が `PvpTopOpponentViewModel` を受け取る
6. **View** の各 `SetUp*()` メソッドがUIコンポーネントにデータを設定
7. `PlayPlayerInfoAppearanceAnimation()` でアニメーション実行
8. ユーザーが閉じるボタンをタップで `Dismiss()` を呼び出し

### 主要なSetUpメソッド

- `SetUpUnitIcon()`: メインキャラクターアイコンの表示
- `SetUpEmblem()`: 紋章アイコンの表示（非表示の場合あり）
- `SetUpVictoryPoint()`: 勝利時のポイント獲得量の表示
- `SetUpUserName()`: プレイヤー名の表示
- `SetUpTotalPoint()`: 総合PvPポイントの表示
- `SetUpTotalPartyStatus()`: 総合ステータスの表示
- `SetUpTotalPartyStatusUpperArrowFlag()`: ステータス上昇矢印の表示判定
- `SetUpUnitIcons()`: パーティーユニット一覧の動的生成（PvpOpponentDetailUnitIconプリファブをインスタンス化）
- `SetUpPvpRankIcon()`: PvPランクアイコンとレベル表示の設定
- `PlayPlayerInfoAppearanceAnimation()`: スケールアップアニメーション実行

### 使用しているDBデータ

このモーダル画面は、PvPトップ画面で事前に取得済みの `PvpTopOpponentViewModel` を受け取るため、直接的なAPI呼び出しやデータベースアクセスは行いません。

表示されるすべてのデータは以下のようにして取得されています：

1. **PvPトップ画面の読み込み時**: `PvpTopUseCase.UpdateAndGetModel()` で対戦相手リストを取得
2. **対戦相手の選択時**: 既に取得済みの `PvpTopOpponentViewModel` をこのモーダルに渡す

#### マスタデータ（参照情報）

| テーブル名 | カラム名 | クライアント説明 |
|-----------|---------|----------------|
| **PvpRank**<br>PvPランク定義 | RankType | プレイヤーのPvPランク階級（ブロンズ～ダイヤモンド）の表示に使用 |
| | AssetKey | ランクアイコンの表示用アセットキー |

#### ユーザーデータ（GameFetch）

| テーブル名 | カラム名 | クライアント説明 |
|-----------|---------|----------------|
| **UserPvpProfile**<br>ユーザーPvpプロフィール | UserId | 対戦相手のユーザーID |
| | UserName | 対戦相手のプレイヤー名 |
| | MainCharacterIconAssetPath | 対戦相手のメインキャラクターアイコンパス |
| | EmblemIconAssetPath | 対戦相手の装備中の紋章アイコンパス |
| | PvpRankStatus | 対戦相手の現在のランク情報 |
| **UserPvpParty**<br>ユーザーPvpパーティー | PartyUnits | 対戦相手の編成ユニット情報（最大5体） |
| | TotalPartyStatus | パーティーの総合ステータス（攻撃力・防御力・HP） |
| **UserGacha / UserUnit**<br>ユーザーユニット情報 | Level | パーティーに編成されているユニットのレベル |
| | Grade | パーティーに編成されているユニットのグレード（限界突破段階） |
| **MstCharacter**<br>キャラクターマスタ（参照） | Rarity | ユニットのレアリティ（R, SR, SSR, URなど） |
| | RoleType | ユニットのロール（アタッカー、ディフェンダーなど） |
| | Color | ユニットのカラー属性 |

### API呼び出し

このモーダル画面では新たなAPI呼び出しは発生しません。PvPトップ画面で既に取得済みのデータを参照するのみです。


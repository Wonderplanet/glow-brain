# PvpInfo画面

## 基本情報

| 項目 | 内容 |
|------|------|
| **画面名** | PvP情報画面（決闘情報画面） |
| **画面種別** | モーダル画面 |

## 概要

PvP（決闘）のシーズン情報を確認するための画面です。プレイヤーはこの画面で、対戦ステージの基本情報、コマ効果、特別ルールなどの詳細な説明を確認できます。PvPトップ画面からアクセスでき、ステージへの挑戦前に戦闘の仕様を理解することができます。

## プレイヤーができること

### 決闘情報の確認

- **ステージ説明**: PvPシーズンの詳細な説明テキストを表示
  - 基本情報（ステージ数、距離感、編成のコツなど）
  - コマ効果情報（登場するコマ効果と対策キャラの紹介）
  - 特別ルール情報（初期P、ステータス補正など）
- **その他の操作**
  - **閉じるボタン**: 画面を閉じてPvPトップに戻る

## 画面の要素

### メイン表示エリア

- **説明テキスト**: マークダウン形式のテキストが表示される
  - 複数の情報セクション（基本情報、コマ効果、特別ルール）を含む
  - テキストは改行を含めてフォーマットされて表示

### ボタンエリア

- **閉じるボタン**: 画面を閉じる

## 画面遷移

### この画面への遷移
- **PvPトップ画面**: 「ステージ詳細」ボタンをタップ

### この画面からの遷移
- **PvPトップ画面**: 「閉じる」ボタンをタップ

## ゲーム仕様・制約事項

### 表示内容

#### ステージ説明テキスト
- 現在開催中のPvPシーズンの説明を表示
- 最新のシーズンが見つからない場合は、デフォルトのシーズン説明を表示
- テキストは複数の情報セクション（【基本情報】【コマ効果情報】【特別ルール情報】）で構成
- テキストには改行を含む複数行の説明が含まれる

### 表示時の挙動

- 画面初期化時に、引数で指定されたシーズンIDに対応する説明テキストを取得
- シーズンIDが見つからない場合は、デフォルトシーズンの説明を使用
- 一度取得した説明テキストはそのまま表示される（自動更新なし）

## 技術参考情報

### 画面実装パス
- `Assets/GLOW/Scripts/Runtime/Scenes/PvpInfo/`

### 主要コンポーネント

#### Presenter
- `Presentation/Presenter/PvpInfoPresenter.cs` (projects/glow-client/...)

#### ViewModel
- `Presentation/ViewModel/PvpInfoViewModel.cs`

#### View
- `Presentation/View/PvpInfoViewController.cs`
- `Presentation/View/PvpInfoView.cs`

#### UseCase
- `Domain/UseCase/PvpInfoUseCase.cs`

#### Translator
- `Presentation/Translator/PvpInfoViewModelTranslator.cs`

### PvpInfoViewModelの構造

```csharp
public record PvpInfoViewModel(PvpDescription Description);
```

- **Description**: PvPシーズンの説明テキスト（複数行、マークダウン形式）

### 画面初期化時の引数

```csharp
PvpInfoViewController.Argument(ContentSeasonSystemId SysPvpSeasonId)
```

- **SysPvpSeasonId**: 表示するPvPシーズンのID

### 使用しているマスタデータ

#### マスタデータ

| テーブル名 | カラム名 | 説明 |
|-----------|---------|------|
| **mst_pvps_i18n**<br>PVP情報の多言語対応テーブル | mst_pvp_id | PvPシーズンのID（mst_pvps.idと紐付け） |
| | description | シーズンの詳細説明テキスト（基本情報、コマ効果、特別ルール） |

**参考**:
- マスタデータはMstCurrentPvpModelResolverを通じて取得
- 指定されたシーズンIDが存在しない場合はデフォルトシーズン（DefaultSysPvpSeasonId）のデータを使用

#### ユーザーデータ（GameFetch）

なし（この画面はマスタデータのみで構成）

# Pvp決闘バトル結果画面

## 基本情報

| 項目 | 内容 |
|------|------|
| **画面名** | Pvp決闘バトル結果画面 |
| **画面種別** | フルスクリーン画面 |

## 概要

PvP決闘バトルの終了後に表示される結果画面です。プレイヤーは獲得したポイント、ランク変動、ランクアップ演出などを確認できます。詳細には勝利ボーナスポイント、相手ボーナスポイント、時間ボーナスポイントなど細かなポイント内訳が表示され、それらが画面に段階的にアニメーションで表示されていきます。

## プレイヤーができること

### バトル結果の確認

#### ポイント獲得の確認
- **詳細スコア表示**: 以下の3つのポイント内訳がスライドインアニメーションで順番に表示される
  - 勝利ポイント: 相手を倒したことで獲得
  - 相手ボーナスポイント: 相手の強さに応じた追加ポイント
  - 時間ボーナスポイント: バトル時間に応じた追加ポイント
- **合計ポイント表示**: 詳細スコアの後、今回獲得した総ポイントがカウントアップアニメーションで表示
- **累計ポイント表示**: 現在のシーズン通算ポイントが表示

#### ランク変動の確認
- **現在のランク帯**: 現在のランク（青銅、銀、金、プラチナなど）と段位が表示
- **ポイント進捗ゲージ**: ランクアップに必要なポイントまでの進捗をゲージで表示
- **ランク昇降**: ポイント獲得によってランクが変わった場合、段階的にランク変動アニメーションが再生される
  - ランクアップ: 新しいランク帯に到達した場合は専用の演出が再生
  - ランクダウン: ポイント低下によってランク帯が下がった場合も表示

#### ランクアップ演出（該当時のみ）
- ランクアップ達成時に別ウィンドウで専用演出が表示される
- ランク上げ演出アニメーション（キャラクターやエフェクトの表示）
- 達成したランクがフェードインで強調表示される

### アニメーション操作

#### スキップ・高速化
- **タップして早送り**: 再生中のアニメーションをタップするとスキップまたは高速化できる
- 詳細スコア表示アニメーション中のタップ: アニメーションをスキップして最終状態を表示
- 合計ポイント表示アニメーション中のタップ: アニメーションをスキップ
- ランク変動アニメーション中のタップ: アニメーションをスキップ

### 画面を閉じる

#### 閉じるボタン
- すべてのアニメーションが完了後、画面下部に「タップで閉じる」ボタンが表示
- タップで結果画面をクローズし、PvpTop画面に戻る

## 画面の要素

### ヘッダー部分
- **背景**: バトル勝利時の演出背景（通常は祝賀ポーズのキャラクターやエフェクト）

### 詳細スコア表示エリア
- **詳細スコアパネル**: アニメーションで右からスライドイン
  - 勝利ポイント（Victory Point）: カウントアップアニメーション付きで表示
  - 相手ボーナスポイント（Opponent Bonus Point）: 遅延後にカウントアップ表示
  - 時間ボーナスポイント（Time Bonus Point）: さらに遅延後にカウントアップ表示

### 合計スコア表示エリア
- **今回獲得ポイント**: 詳細スコアの合計がスライドインアニメーションで出現
- **獲得ポイントのカウントアップ**: 数値がアニメーション付きでカウントアップ
- **スコアズームアニメーション**: 数値が拡大するアニメーション演出
- **累計ポイント**: 獲得後に「累計」として現在のシーズン通算ポイントが表示
  - フェードインアニメーション付きで出現
  - その後、累計ポイントまでカウントアップアニメーション

### ランク帯表示エリア
- **スクロール**: メイン表示からランク帯へのスムーズなスクロール
- **ランクパネル**: 画面中央に配置される
  - **ランクアイコン**: 現在のランク帯を示すアイコン
  - **ランク名**: ランク帯の名前（例: Bronze, Silver, Gold）
  - **段位**: 各ランク帯内の段位が数値で表示
  - **ポイント進捗ゲージ**: ランクアップまでの進捗を視覚的に表示
  - **必要ポイント**: 次のランクアップに必要なポイント数が表示

### ランク変動アニメーション
- ランクアップの場合: 新しいランクアイコンへのチェンジアニメーション、段位の上昇アニメーション
- ランクダウンの場合: ランクダウンエフェクト、段位の低下アニメーション
- 複数段階のランク変動がある場合: 段階ごとにアニメーション再生

### 下部要素
- **下矢印**: スクロール可能な領域があることを示す矢印（スクロール後はフェードアウト）
- **スクロールバー**: スクロール領域のバーが表示
- **「タップで閉じる」ボタン**: すべてのアニメーション完了後に表示

## 画面遷移

### この画面への遷移
- **PvpBattleFinishAnimation画面から**: PvP決闘バトル終了後、勝敗アニメーション完了時に遷移
- **遷移パターン**: バトル結果を引数として受け取り初期化

### この画面からの遷移
- **PvpTop画面へ**: 「タップで閉じる」ボタンタップで遷移
- **遷移後の状態**: ユーザーのランクとポイント情報が更新された状態で表示される

## ゲーム仕様・制約事項

### ポイント計算
- **勝利ポイント**: バトル勝利時に基本ポイントとして付与
- **相手ボーナス**: 相手のランクレベルが高いほどボーナスが大きい
- **時間ボーナス**: バトルにかかった時間が短いほどボーナスが多い
- **合計ポイント**: VictoryPoint + OpponentBonusPoint + TimeBonusPoint

### ランク仕様
- **ランク帯**: Bronze, Silver, Gold, Platinum など複数階級あり
- **段位**: 各ランク帯内で複数の段位が存在（レベル表示）
- **最大ランク**: PvpMaxRankClassType（最高ランク帯）に到達可能
- **ランクアップ条件**: 次のランク帯に必要なポイント到達時
- **ランクダウン条件**: ポイント低下により前のランク帯に下がる可能性あり
- **最大レベル判定**: 最高ランク帯での最高段位に到達時は特別表示

### アニメーション仕様
- **連続実行**: 複数のアニメーションが順序立てて実行される
  1. 詳細スコアスライドイン
  2. 詳細スコアカウント
  3. 合計ポイントスライドイン
  4. 合計ポイントカウント
  5. ランクパネル表示とスクロール
  6. ランク変動アニメーション
  7. ランクアップエフェクト表示（該当時）
- **スキップ動作**: 各段階でタップするとその段階以降をスキップ可能
- **SE（効果音）**: ポイントカウント時、ランク変動時に効果音再生
- **BGM**: 勝利結果用のBGMがクロスフェードで再生

### ランクアップエフェクト画面
- 実際にランクまたは段位がアップした時のみ表示
- モーダルダイアログとして別ウィンドウで表示
- エフェクトアニメーション + ランク情報の表示
- 「タップで閉じる」で元の結果画面に戻る

### ランク最大レベル判定
- 現在のランクが最高ランク帯かつ最高段位の場合
- ランクダウンがない場合（ポイントの大幅低下がない限り）
- 画面上で特別な状態表示あり

## 技術参考情報

### 画面実装パス
- `Assets/GLOW/Scripts/Runtime/Scenes/PvpBattleResult/`

### 主要コンポーネント

#### Presenter
- `Presentation/Presenter/PvpBattleResultPresenter.cs` (projects/glow-client/...)
  - バトル結果画面のメイン処理を担当
  - 各種アニメーション実行の制御とタイミング管理
  - ViewControllerへの指示を実行
- `Presentation/Presenter/PvpBattleResultRankUpEffectPresenter.cs`
  - ランクアップエフェクト画面の処理を担当
  - ランクアップ演出アニメーションの制御

#### ViewController
- `Presentation/View/PvpBattleResultViewController.cs`
  - 結果画面のビューコントローラ
  - アニメーション実行メソッドをViewに指示
  - 引数: `PvpBattleResultViewController.Argument(PvpBattleResultPointViewModel ViewModel)`
- `Presentation/View/PvpBattleResultRankUpEffectViewController.cs`
  - ランクアップエフェクト画面のビューコントローラ

#### View
- `Presentation/View/PvpBattleResultView.cs`
  - 実際のUI表示と各種アニメーション実装
  - 詳細スコア表示のスライドインとカウントアップ
  - 合計ポイント表示のアニメーション
  - ランクパネルのスクロールと変動表示
  - スクロール、フェード、ズーム等の演出
- `Presentation/View/PvpBattleResultRankUpEffectView.cs`
  - ランクアップエフェクトの表示

#### ViewModel
- `Presentation/ViewModel/PvpBattleResultPointViewModel.cs`
  - 画面表示用の集約データモデル
  - ランクアップ判定ロジックを含む
- `Presentation/ViewModel/PvpBattleResultPointRankTargetViewModel.cs`
  - ランク変動の各段階情報

#### Component
- `Presentation/Component/PvpBattleResultRankPanelComponent.cs`
  - ランクパネルのUI処理
  - ランクアイコン変更アニメーション
  - ランク段位変動アニメーション

#### Factory
- `Presentation/Factory/PvpBattleResultPointViewModelFactory.cs`
  - ViewModelの生成処理

#### Domain Model
- `Domain/Model/PvpBattleResultPointModel.cs`
  - ランク情報とポイント情報を持つモデル
  - CurrentRankType, CurrentRankLevel, PvpResultPointRankTargetModels など
- `Domain/Model/PvpBattleResultPointRankTargetModel.cs`
  - ランク変動の段階ごとの情報
  - BeforePoint, AfterPoint, TargetRankType, TargetScoreRankLevel など

### PvpBattleResultPointViewModel の構造

```csharp
public record PvpBattleResultPointViewModel(
    PvpRankClassType CurrentRankType,          // 現在のランク帯
    PvpRankLevel CurrentRankLevel,             // 現在の段位
    IReadOnlyList<PvpBattleResultPointRankTargetViewModel>
        PvpResultPointRankTargetModels,        // ランク変動情報の配列
    PvpPoint VictoryPoint,                     // 勝利ポイント
    PvpPoint OpponentBonusPoint,               // 相手ボーナスポイント
    PvpPoint TimeBonusPoint,                   // 時間ボーナスポイント
    PvpPoint TotalPoint)                       // 累計ポイント
```

**主要プロパティ**:
- `GainedTotalPoint`: 今回獲得した合計ポイント（VictoryPoint + OpponentBonusPoint + TimeBonusPoint）
- `FillRate()`: ランクパネルのゲージ充填率を計算
- `IsRankOrRankLevelUp()`: ランクまたは段位がアップしたかの判定
- `LastAchievedRankAndLevel()`: 最後に達成したランクと段位を取得
- `IsCurrentRankMaxLevel()`: 最高ランク帯の最高段位に到達しているかの判定
- `IsKeepRankMaxLevel()`: 最高ランクを維持している状態かの判定

### ViewController初期化

```csharp
PvpBattleResultViewController.Argument(PvpBattleResultPointViewModel ViewModel)
```

ViewModel がこの画面に必要なすべての情報を含む形で渡される

### 使用しているDBデータ

マスタデータ参照なし。この画面はバトル結果情報（ポイント計算、ランク変動）をバトル直後のサーバー応答データから処理表示するモーダル画面です。

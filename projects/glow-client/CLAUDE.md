# CLAUDE.md

このファイルは、このリポジトリでコードを扱う際のClaude Code (claude.ai/code) 向けのガイダンスです。

## プロジェクト概要

GLOW Unityモバイルゲームクライアントプロジェクト。Unity 6000.0.53f1を使用し、AndroidとiOSプラットフォーム向けに開発。

## ビルド・開発コマンド

### Unity エディタ
- Unity 6000.0.53f1でプロジェクトを開く

### テストの実行
次のコマンドで実行
`/Applications/Unity/Hub/Editor/6000.0.53f1/Unity.app/Contents/MacOS/Unity -runTests -projectPath ./ -batchmode -testPlatform EditMode -testResults ./TestResults.xml -testFilter テストクラス名`

- テスト結果は`./TestResults.xml`に出力される
- 「テストクラス名」には、実行したいテストクラスの名前を指定
- コマンドの実行結果がErrorでもテストは実行されている可能性があるのでテスト結果を確認すること
- コマンドの`6000.0.53f1`部分は、使用しているUnityのバージョンに合わせて変更すること

## アーキテクチャとコード構成

### コア機能
`Assets/GLOW/Scripts/Runtime/Core`

#### クリーンアーキテクチャレイヤー
- **ドメイン層** (`Assets/GLOW/Scripts/Runtime/Core/Domain/`): ビジネスロジック、Models、ValueObjects、UseCases
- **データ層** (`Assets/GLOW/Scripts/Runtime/Core/Data/`): Repositories、Services、Translators
- **プレゼンテーション層** (`Assets/GLOW/Scripts/Runtime/Core/Presentation/`): ViewModels、Views、Presenters
- **アプリケーション層** (`Assets/GLOW/Scripts/Runtime/Core/Application/`): シーン管理、DI設定

### 画面実装
- `Assets/GLOW/Scripts/Runtime/Scenes/`以下に各画面の実装
- 画面ごとにクリーンアーキテクチャレイヤーを持つ

### 主要なデザインパターン
- **依存性注入**: Zenject/Extenjectコンテナ
- **リポジトリパターン**: データアクセスの抽象化
- **ユースケースパターン**: ビジネスロジックのカプセル化
- **MVVM/MVP**: UIアーキテクチャ
- **トランスレータパターン**: レイヤー間のデータ変換

### コーディング規約
- privateのアクセス修飾子は省略
- privateメソッドはpublicメソッドより下に記述
- インターフェースは別ファイルに定義
- `using`ディレクティブを使用し、インライン名前空間は避ける
- 文字列整形: `ZString.Format`を使用
- ログ出力: `ApplicationLog`を使用
- ModelとValueObject: C#の`record`型を使用
- Model/ValueObjectは`Empty`プロパティを定義（get専用で初期値を設定）
- フラグ用ValueObject以外のModelとValueObjectは`IsEmpty()`メソッドを定義
- Model/ValueObjectのEmptyチェックはIsEmpty()メソッドを使用
- セキュリティ重要な値: `Obscured**`型を使用
- `Obscured`型とのキャストは暗黙的キャストにする
- フラグ用ValueObjectと`Obscured`型以外のValueObjectに暗黙的キャストは定義しない
- フラグ用ValueObject: 暗黙的boolキャスト、`True`/`False`静的フィールドを定義（`Empty`なし）
- 非同期処理: `UniTask`を使用
- 依存性注入: privateプロパティにInject（get専用アクセサ）

### コード整形
- 1行最大130文字
- 130文字を超える行がメソッドチェインの場合は1メソッドごとに改行（最初のメソッドの手前でも一度改行）
- 130文字を超える行が複数引数のメソッド呼び出しの場合は1引数ごとに改行（最初の引数の手前でも一度改行）
- if文の{}を省略する場合は1行で書く

### テストガイドライン
- ベーステストクラス: DI使用クラスのテストは`ZenjectUnitTestFixture`を継承
- モック: Moqライブラリを使用
- モックセットアップ: モックインスタンス生成後、すぐにBind設定を追加
- モックメソッドの引数: 可能なかぎり`It.IsAny<>()`を使用せずに、想定される具体的な引数を指定する
- テストで使用するModelのインスタンスは`Empty`から`with`を使用して生成
- 非同期テスト: テストメソッドから`Task`を返す
- テストインスタンス化: DIが必要なクラスは`Container.Instantiate`を使用
- 結果変数の命名: テスト結果は`actual`を使用
- アサーション: `Assert.That`を使用
- `EditMode test can only yield null`のエラーは無視

### 実装注意点
- .metaファイルはUnityが生成するので自分で作成、編集しないこと
- MasterDataIdのコンストラクタに渡す型はstring

### 開発要件
コード変更時の注意事項：
1. メソッドやフィールド変更時は全ての使用箇所を更新
2. 実装変更時は対応するインターフェースも更新
3. クラス変更時はユニットテストも更新

## 主要技術

- **Addressables**: 動的コンテンツ読み込みシステム
- **Firebase**: Analytics、Crashlytics、Messaging
- **Localization**: 多言語サポート
- **Spine**: 2Dスケルタルアニメーション
- **URP**: Universal Render Pipeline
- **Google Mobile Ads**: 収益化
- **Adjust SDK**: アナリティクストラッキング
- **UniTask**: Unity用のasync/await
- **UniRx**: リアクティブプログラミング
- **Zenject**: 依存性注入

## 重要事項

- APIキーやシークレットの取り扱いに注意
- 作業ブランチは`task/`プレフィックスのフィーチャーブランチを使用
- PRのメインブランチは通常`release/`ブランチ
- やり取りは日本語で
- テストメソッドの戻り値がTaskのとき`EditMode test can only yield null`エラーが出るが、このエラーメッセージは不具合なので無視してOK
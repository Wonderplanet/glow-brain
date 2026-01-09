# GLOW Client

GLOWモバイルゲームのUnityクライアントプロジェクトです。

## 開発環境要件

### Unity
- Unity 6000.0.37f1

### プラットフォーム
- Android
- iOS

## 主要技術・パッケージ

### Unity標準パッケージ
- Universal Render Pipeline (URP) 17.0.3
- Addressables 2.2.2
- Localization 1.5.2
- Timeline 1.8.7

### 外部パッケージ
- Zenject 9.3.1 (依存性注入)
- UniTask 2.3.3 (非同期処理)
- UniRx (リアクティブプログラミング)
- Spine 4.2 (アニメーション)
- Firebase (Analytics, Crashlytics, Messaging)
- Google Mobile Ads 9.1.0
- Adjust SDK 5.1.3 (アナリティクス)

### WonderPlanet カスタムパッケージ
- unity-build-integration (ビルド統合)
- unity-resource-management (リソース管理)
- unity-analytics-bridge (アナリティクス橋渡し)
- その他多数のゲーム開発支援パッケージ

## プロジェクト構成

```
Assets/
├── GLOW/                    # メインゲームコンテンツ
│   ├── Scenes/             # ゲームシーン
│   │   ├── Application.unity
│   │   ├── Splash.unity
│   │   ├── Title.unity
│   │   ├── Home.unity
│   │   └── InGame.unity
│   ├── Scripts/            # ゲームスクリプト
│   ├── Graphics/           # グラフィックリソース
│   └── Data/              # ゲームデータ
├── Framework/              # フレームワークコード
└── Plugins/               # プラグイン

Packages/                   # カスタムパッケージ
Jenkins/                    # ビルド設定
Rubstone/                   # ライブラリ管理ツール
```

## ビルド環境

### Jenkins設定
- Development / QA / Production 環境対応
- Android / iOS プラットフォーム対応
- Addressable アセット自動ビルド

### ビルドターゲット
- `BuildApplicationAndroid-Develop`
- `BuildApplicationAndroid-Stable` 
- `BuildApplicationAndroid-Production`
- `BuildApplication-iOS-Develop`
- `BuildApplication-iOS-Stable`
- `BuildApplication-iOS-Production`

## ライブラリ管理

プロジェクトでは外部ライブラリの管理にRubstoneを使用しています。

### Rubstone セットアップ
```bash
cd Rubstone
bundle install
```

### ライブラリのインストール・更新
```bash
# Docker経由で実行
./run_rubstone install
./run_rubstone update <library_name>
```

詳細については `Rubstone/README.md` を参照してください。

## 開発開始手順

1. Unity 6000.0.37f1 をインストール
2. プロジェクトをUnityで開く
3. Rubstoneでライブラリを更新 (必要に応じて)
4. Addressableアセットをビルド (必要に応じて)

## 注意事項

- Firebase設定ファイルは環境ごとに適切に配置してください
- ビルド前にAddressableアセットの更新を確認してください
- 各種APIキーやシークレットファイルの管理に注意してください

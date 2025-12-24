---
name: unity-screen-doc
description: Unity/ゲームクライアントの画面機能ドキュメントを作成。指定された画面のPresenter、ViewModel、UseCaseなどのコードを調査し、プレイヤー視点でのゲーム体験に焦点を当てた詳細なドキュメントをMarkdown形式で生成します。glow-clientプロジェクト、Unity画面、C#コード、ゲーム機能ドキュメント、画面仕様書作成で使用。
---

# Unity Screen Doc

## 概要

Unity/ゲームクライアント（特にglow-clientプロジェクト）の画面機能ドキュメントを作成するスキルです。画面のコードを体系的に調査し、プレイヤー視点でのゲーム体験を重視した読みやすいドキュメントを生成します。

## ワークフロー

ユーザーから画面名が指定されたら、以下の手順で実行：

### 1. 画面の特定と基本調査

```bash
# 画面ディレクトリの確認
ls -la projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/[画面名]/

# 基本構造の確認
ls -la projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/[画面名]/Presentation/
ls -la projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/[画面名]/Domain/
```

### 2. 主要ファイルの読み込み

以下のファイルを**必ず**読み込む：

1. **Presenter**: `Presentation/Presenters/*Presenter.cs`
   - 画面のロジックとユーザー操作を理解
   - デリゲートメソッド名から機能を把握

2. **ViewModel**: `Presentation/ViewModels/*ViewModel.cs`
   - 画面に表示されるデータ構造を理解
   - プロパティ名から表示要素を把握

3. **UseCase**: `Domain/UseCases/*UseCase.cs`
   - ビジネスロジックとデータ取得方法を理解
   - API呼び出しの有無を確認

### 3. API呼び出しの調査（該当する場合）

UseCaseから以下を特定：
- Serviceクラスの呼び出し
- Repositoryからのデータ取得のみか、API呼び出しがあるか
- API呼び出しがある場合、Serviceファイルを読んでエンドポイントを特定

### 4. マスタデータの特定（必須）

UseCaseとModelFactoryから使用しているマスタデータを特定：
- 使用されているRepositoryを確認（例：`IOprGachaRepository`, `IMstCharacterDataRepository`）
- Repository名からDBテーブル名を特定（例：`IOprGachaRepository` → `OprGacha` → `opr_gacha`）
- 各Modelのプロパティから使用カラムを特定
- ユーザーデータ（User*）の使用も確認

詳細は `references/masterdata-guide.md` を参照。

### 5. ドキュメント作成

`docs/機能一覧/画面一覧/[日本語画面名].md` を作成。

**重要**: 画面名は必ず日本語で記載すること（例：`ガチャコンテンツ画面.md`）。英語のクラス名（例：GachaContent）をそのまま使用しないこと。

テンプレートは `references/document-template.md` を参照。
記述スタイルは `references/writing-guidelines.md` を参照。

## 調査チェックリスト

ドキュメント作成前に以下を確認：

- [ ] Presenterファイルを読み、主要な機能を把握した
- [ ] ViewModelファイルを読み、表示データ構造を理解した
- [ ] UseCaseファイルを読み、ビジネスロジックを理解した
- [ ] 使用しているRepository（マスタデータ）を特定した
- [ ] マスタデータのテーブル名・カラム名・使われ方を整理した（表形式）
- [ ] API呼び出しがある場合、エンドポイントを特定した
- [ ] 画面遷移パターンを理解した（Presenterのナビゲーションメソッドから）
- [ ] 制約事項や条件分岐を把握した（UseCaseやPresenterのロジックから）

## 重要な制約事項

### 絶対に守るべきルール

- **実装詳細を前面に出さない**: 技術的な情報は「技術参考情報」セクションに集約
- **プレイヤー視点で記述**: 「何ができるか」を中心に書く
- **ファイルパスは正確に**: コピペ可能な形式で記載
- **推測で書かない**: コードを読んで確認した内容のみ記載

### 優先順位

1. **ゲーム体験の説明**（最優先）
2. **操作フローと画面遷移**
3. **ゲーム仕様・制約事項**
4. **技術参考情報**（最後）

## 完成後の報告形式

```
## 画面ドキュメント作成完了

**作成ファイル**: `docs/機能一覧/画面一覧/[日本語画面名].md`

**ドキュメント概要**:
- 画面名: [日本語画面名]（例：ガチャコンテンツ画面）
- 主要機能: [機能1], [機能2], [機能3]
- API呼び出し: [あり/なし]
- ゲーム仕様の特徴: [特記事項]

**調査したファイル**:
- Presenter: [ファイルパス]
- ViewModel: [ファイルパス]
- UseCase: [ファイルパス]
- その他: [追加で読んだファイル]
```

## リファレンス

このスキルには以下の詳細ガイドが含まれています：

- **`references/document-template.md`**: ドキュメント構造のテンプレート
- **`references/writing-guidelines.md`**: 記述スタイルガイド（ゲーム体験視点での書き方）
- **`references/masterdata-guide.md`**: マスタデータ調査・記載方法のガイド

必要に応じてこれらのファイルを参照してください。

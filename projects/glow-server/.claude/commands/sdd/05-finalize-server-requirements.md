# SDD サーバーAPI要件書まとめ

これまでのステップで作成された全てのドキュメントを統合し、
サーバーAPI側で実現すべき要件を完全な形でまとめた「サーバーAPI要件書」を作成します。

## 使用方法

```
/sdd:finalize-server-requirements {機能名}
```

例: `/sdd:finalize-server-requirements スタミナブースト`

## 実行内容

引数: $ARGUMENTS

### 処理ステップ

1. **機能名の取得**
   - 第1引数から機能名を取得

2. **テンプレートファイルの読み込み**
   - `docs/sdd/prompts/05_サーバーAPI要件書まとめ_テンプレート.md` を読み込む

3. **テンプレートの置換**
   - テンプレート内の全ての `{FEATURE_NAME}` を第1引数の機能名に置換

4. **プロンプトの実行**
   - 置換後の内容をプロンプトとして実行
   - テンプレートファイルに記載されたルールと手順に従ってサーバーAPI要件書を作成

## 前提条件

以下の4つのドキュメントが全て存在すること:

1. `docs/sdd/features/{機能名}/01_サーバー要件抽出.md`
   - `/sdd:extract-server-requirements` で作成
2. `docs/sdd/features/{機能名}/02_サーバー要件_コード調査追記.md`
   - `/sdd:investigate-code-requirements` で作成
3. `docs/sdd/features/{機能名}/03_サーバー仕様レビュー.md`
   - `/sdd:review-server-spec` で作成
4. `docs/sdd/features/{機能名}/04_ゲーム体験仕様確認結果まとめ.md`
   - `/sdd:confirm-game-experience-spec` で作成

## 出力

- `docs/sdd/features/{機能名}/05_サーバーAPI要件書.md` にサーバーAPI要件書が出力されます
- 詳細なフォーマットはテンプレートファイルで定義されています

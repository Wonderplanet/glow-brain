# エラーハンドリング & トラブルシューティング

## エラーハンドリング

各段階で以下のエラーチェックを実行します。

### Stage 1エラー

| エラー | 原因 | 対策 |
|--------|------|------|
| **ゲーム体験仕様書PDFが見つからない** | PDFが指定パスに存在しない | ユーザーにPDFの場所を確認 |
| **要件が不明確** | 仕様書の記載が曖昧 | 追加質問を実施 |
| **サーバー要件が抽出できない** | クライアント側のみの機能 | ユーザーに確認し、サーバー要件がないことを明示 |

**対策コマンド：**
```bash
# PDFの場所を確認
ls -la docs/game-specs/

# Stage 1を再実行（PDFパスを指定）
/sdd:extract-server-requirements {機能名}
```

---

### Stage 2エラー

| エラー | 原因 | 対策 |
|--------|------|------|
| **既存実装が見つからない** | 完全に新規の機能 | 新規実装として扱う（エラーではない） |
| **コード調査タイムアウト** | コードベースが大きい | 既知パターンのみ反映、詳細は後で追加 |
| **類似機能が複数見つかる** | どのパターンに従うべきか不明 | ユーザーに確認し、従うべきパターンを決定 |

**対策コマンド：**
```bash
# 既存実装が見つからない場合（新規実装）
# → そのままStage 3へ進む（エラーではない）

# コード調査を再実行（範囲を絞る）
/sdd:investigate-code-requirements {機能名}
```

---

### Stage 3エラー

| エラー | 原因 | 対策 |
|--------|------|------|
| **前段階の出力ファイルが見つからない** | Stage 1-2が未実行または失敗 | Stage 1-2を再実行 |
| **曖昧さの評価が困難** | 仕様が複雑すぎる | ユーザーに確認、追加情報を取得 |
| **プランナー確認項目が多すぎる** | 仕様の不明点が多い | 優先度を付けて段階的に確認 |

**対策コマンド：**
```bash
# 前段階を再実行
/sdd:extract-server-requirements {機能名}
/sdd:investigate-code-requirements {機能名}

# Stage 3を再実行
/sdd:review-server-spec {機能名}
```

---

### Stage 4エラー

| エラー | 原因 | 対策 |
|--------|------|------|
| **プランナー確認結果が不完全** | 一部の項目が未回答 | ユーザーに追加確認を依頼 |
| **不明点が解決されていない** | プランナーも不明 | 仮の仕様で進めるか、仕様策定を待つか判断 |
| **確認結果が矛盾している** | 回答内容が一貫していない | プランナーに再確認 |

**対策コマンド：**
```bash
# 確認結果を整理してStage 4を再実行
/sdd:confirm-game-experience-spec {機能名}
```

---

### Stage 5エラー

| エラー | 原因 | 対策 |
|--------|------|------|
| **前段階の出力ファイルが見つからない** | Stage 1-4が未実行または失敗 | 該当段階を再実行 |
| **情報の整合性に問題** | Stage 1-4の内容が矛盾 | ユーザーに確認、矛盾を解消 |
| **要件書が不完全** | 必要な情報が不足 | 不足している段階を再実行 |

**対策コマンド：**
```bash
# 不足している段階を再実行
/sdd:extract-server-requirements {機能名}
/sdd:investigate-code-requirements {機能名}
/sdd:review-server-spec {機能名}
/sdd:confirm-game-experience-spec {機能名}

# Stage 5を再実行
/sdd:finalize-server-requirements {機能名}
```

---

### Stage 6-8エラー

| エラー | 原因 | 対策 |
|--------|------|------|
| **サーバーAPI要件書が見つからない** | Stage 5が未実行または失敗 | Stage 5を再実行 |
| **glow-schema不整合** | スキーマ定義が見つからない | glow-schemaを再確認、必要に応じて更新 |
| **既存実装パターンとの整合性問題** | コード調査が不十分 | Stage 2を再実行 |
| **設計内容が矛盾** | Stage 6-8を並列実行した際の問題 | 順次実行で再実施 |

**対策コマンド：**
```bash
# Stage 5を再実行
/sdd:finalize-server-requirements {機能名}

# glow-schemaを確認
cd ../glow-schema
git pull origin main

# 設計フェーズを順次実行（並列実行を避ける）
/sdd:overview-api-design {機能名}
/sdd:create-api-design {機能名}
/sdd:design-api-implementation {機能名}
```

---

## トラブルシューティング

### 問題: Stage 4で人間確認が必要になった

**原因:** これは正常な動作です。Stage 4ではプランナーへの確認が必須です。

**対策:**
```bash
# 1. Stage 3の出力ファイルを確認
cat docs/sdd/features/{機能名}/サーバー仕様レビュー.md

# 2. プランナー確認項目リストを使って確認を実施
# （メール、チャット、会議など）

# 3. 確認結果を準備（テキストファイル、メモ、チャットログなど）

# 4. Stage 4を実行
/sdd:confirm-game-experience-spec {機能名}
# → AIが確認結果を求めるので、準備した内容を提供
```

---

### 問題: Stage 6で「glow-schemaが見つからない」エラー

**原因:** glow-schemaリポジトリがクローンされていない

**対策:**
```bash
# glow-schemaをクローン
cd ..
git clone git@github.com:Wonderplanet/glow-schema.git

# 確認
ls -la glow-schema/

# Stage 6を再実行
cd glow-server
/sdd:overview-api-design {機能名}
```

---

### 問題: トークン使用量が多すぎる

**原因:** 既存ファイルを大量に読み込んでいる、またはコンテキストが肥大化

**対策:**
```bash
# コンテキストをクリアしてから再実行
/clear
/sdd:run-full-flow {機能名}

# または、段階別に実行して必要最小限のコンテキストで実行
/sdd:extract-server-requirements {機能名}
/clear
/sdd:investigate-code-requirements {機能名}
/clear
# ...（以下同様）
```

---

### 問題: 特定の段階で停止する

**原因:** 前段階の出力が不完全、またはエラーが発生

**対策:**
```bash
# 前の段階を再実行
/sdd:{前段階のコマンド} {機能名}

# 停止した段階の出力ファイルを確認
cat docs/sdd/features/{機能名}/{出力ファイル名}.md

# その後、続きを実行
/sdd:{停止した段階のコマンド} {機能名}
```

---

### 問題: 設計フェーズ（Stage 6-8）で不整合が発生

**原因:** 要件定義フェーズの出力が不完全、またはStage 6-8の並列実行による問題

**対策1: 要件定義フェーズを再確認**
```bash
# 要件定義フェーズを再確認
cat docs/sdd/features/{機能名}/サーバーAPI要件書.md

# 必要に応じて要件定義フェーズを再実行
/sdd:finalize-server-requirements {機能名}
```

**対策2: 設計フェーズを順次実行**
```bash
# 設計フェーズを順次実行（並列実行を避ける）
/sdd:overview-api-design {機能名}

# Stage 6の結果を確認
cat docs/sdd/features/{機能名}/API実装全体概要設計.md

# Stage 7, 8を実行
/sdd:create-api-design {機能名}
/sdd:design-api-implementation {機能名}
```

---

### 問題: 出力ファイルが上書きされてしまった

**原因:** 同じ機能名で再実行した場合、出力ファイルは上書きされます

**対策:**
```bash
# Gitで履歴を確認
git log -- docs/sdd/features/{機能名}/

# 以前のバージョンを復元
git checkout HEAD~1 -- docs/sdd/features/{機能名}/{ファイル名}.md

# または、別のブランチで実行
git checkout -b feature/{機能名}-v2
/sdd:run-full-flow {機能名}
```

---

### 問題: プランナー確認項目が多すぎて確認が大変

**原因:** 仕様の不明点が多い、または複雑な機能

**対策1: 優先度を付けて段階的に確認**
```bash
# Stage 3の結果を確認
cat docs/sdd/features/{機能名}/サーバー仕様レビュー.md

# 優先度の高い項目のみ先に確認

# 確認結果を反映してStage 4を実行
/sdd:confirm-game-experience-spec {機能名}

# 残りの項目は後で確認し、必要に応じて設計を修正
```

**対策2: 機能を分割**
```bash
# 複雑な機能を複数の小さな機能に分割

# 機能A（コア機能）
/sdd:run-full-flow {機能A}

# 機能B（追加機能）
/sdd:run-full-flow {機能B}
```

---

### 問題: 既存実装パターンが複数あり、どれに従うべきか不明

**原因:** プロジェクトの歴史的経緯により、複数のパターンが混在

**対策:**
```bash
# Stage 2の結果を確認
cat docs/sdd/features/{機能名}/サーバー要件_コード調査追記.md

# プロジェクトのコーディング規約を確認
cat .claude/api.md
cat docs/architecture/

# 最新のパターンに従うか、ユーザーに確認
# → ユーザーに確認して決定

# Stage 2を再実行（パターンを明示）
/sdd:investigate-code-requirements {機能名}
```

---

### 問題: Stage 7でglow-schemaとの整合性が取れない

**原因:** glow-schemaが古い、またはローカル変更がある

**対策:**
```bash
# glow-schemaを最新化
cd ../glow-schema
git pull origin main

# glow-schemaの状態を確認
git status
git diff

# 必要に応じてローカル変更をstash
git stash

# Stage 7を再実行
cd ../glow-server
/sdd:create-api-design {機能名}
```

---

### 問題: Stage 8でドメインレイヤーの設計が複雑すぎる

**原因:** 機能が複雑、または既存実装との整合性が困難

**対策1: 設計を簡素化**
```bash
# サーバーAPI要件書を再確認
cat docs/sdd/features/{機能名}/サーバーAPI要件書.md

# 要件を簡素化できないか検討
# → プランナーに確認

# Stage 5から再実行
/sdd:finalize-server-requirements {機能名}
/sdd:design-api-implementation {機能名}
```

**対策2: 段階的に実装**
```bash
# 機能を複数のフェーズに分割

# Phase 1: 最小限の機能
# Phase 2: 追加機能
# Phase 3: 拡張機能

# Phase 1のみでStage 8を実行
/sdd:design-api-implementation {機能名}-phase1
```

---

### 問題: 実行中にタイムアウトが発生

**原因:** サブエージェントの処理が長すぎる

**対策:**
```bash
# 該当段階を再実行
/sdd:{該当コマンド} {機能名}

# または、段階別に実行して進捗を確認
/sdd:extract-server-requirements {機能名}
# 完了を確認
/sdd:investigate-code-requirements {機能名}
# 完了を確認
# ...（以下同様）
```

---

## よくある質問（FAQ）

### Q1. 全段階を自動実行できますか？

**A:** Stage 4の人間確認を除き、Stage 1-3とStage 5-8は自動実行可能です。
ただし、Stage 4では必ずプランナーへの確認が必要です。

### Q2. Stage 4をスキップできますか？

**A:** 推奨しませんが、プランナー確認項目が少ない場合や、仕様が明確な場合は、
Stage 3の結果をそのまま使用してStage 5に進むことも可能です。
ただし、仕様の不明点が残る可能性があります。

### Q3. 設計フェーズを並列実行すべきですか？

**A:** 通常は並列実行を推奨します（約60%の時間短縮）。
ただし、以下の場合は順次実行を検討してください：
- 初回実行時
- 複雑な機能で全体像の把握が重要な場合
- Stage 6-8間で不整合が発生した場合

### Q4. 複数の機能を同時に設計できますか？

**A:** はい、機能ごとに独立したディレクトリ（`docs/sdd/features/{機能名}/`）に
出力されるため、複数の機能を並行して設計可能です。

```bash
# 機能Aの設計
/sdd:run-full-flow スタミナブースト

# 機能Bの設計（並行して実行可能）
/sdd:run-full-flow ガチャ排出確率アップ
```

### Q5. 出力ファイルを手動で編集しても良いですか？

**A:** はい、出力ファイルは手動で編集可能です。
ただし、再実行時に上書きされる可能性があるため、
重要な変更はGitでコミットしておくことを推奨します。

```bash
# 手動編集後にコミット
git add docs/sdd/features/{機能名}/
git commit -m "手動でサーバーAPI要件書を修正"

# 再実行時に差分を確認
/sdd:finalize-server-requirements {機能名}
git diff docs/sdd/features/{機能名}/サーバーAPI要件書.md
```

### Q6. 既存機能の改修でも使えますか？

**A:** はい、既存機能の改修でも使用できます。
Stage 2のコード調査で既存実装を徹底的に調査し、
既存パターンとの整合性を確保します。

### Q7. エラーが発生した場合、最初からやり直す必要がありますか？

**A:** いいえ、エラーが発生した段階から再実行可能です。
各段階の出力ファイルは独立しているため、
必要な段階のみ再実行すれば問題ありません。

---

## デバッグのヒント

### 1. 出力ファイルを確認

各段階の出力ファイルを確認して、期待通りの内容が生成されているか確認：

```bash
# 全ての出力ファイルを一覧表示
ls -la docs/sdd/features/{機能名}/

# 各ファイルの内容を確認
cat docs/sdd/features/{機能名}/サーバー要件抽出.md
cat docs/sdd/features/{機能名}/サーバー要件_コード調査追記.md
# ...（以下同様）
```

### 2. ログを確認

サブエージェントの実行ログを確認：

```bash
# 実行ログを確認（存在する場合）
cat logs/sdd-orchestrator.log
```

### 3. 段階的に実行

問題が発生した場合、段階的に実行して問題箇所を特定：

```bash
# Stage 1のみ実行
/sdd:extract-server-requirements {機能名}
# 結果を確認

# Stage 2のみ実行
/sdd:investigate-code-requirements {機能名}
# 結果を確認

# ...（以下同様）
```

### 4. コンテキストをクリア

トークン消費が多い場合や、不要なコンテキストが蓄積している場合：

```bash
# コンテキストをクリア
/clear

# 再実行
/sdd:{該当コマンド} {機能名}
```

---

## サポート

問題が解決しない場合は、以下を含めて報告してください：

1. 実行したコマンド
2. エラーメッセージ（あれば）
3. 出力ファイルの内容
4. 機能の概要
5. 環境情報（glow-server、glow-schemaのバージョンなど）

# Claude Code ガイド相談レポート

**日付**: 2025年12月27日
**トピック**: Skills の frontmatter 記述方法とベストプラクティス

## 相談内容

- Claude Code skills の frontmatter の正しい記述方法
- スキルの自動認識がされない問題の原因
- 1リポジトリあたりのスキル数の推奨値
- 40個のスキルは多すぎるか

## 重要な結論・学び

### Frontmatter の基本形式

公式ドキュメントによると、現在のプロジェクトの frontmatter は**良好な形式**になっています。

基本的な frontmatter の記述形式：

```markdown
---
name: スキル名
description: スキルの説明（「いつ使うのか」を明示することが重要）
---
```

> 出典: [Claude Code Skills ドキュメント](https://code.claude.com/docs/en/skills.md)

**ベストプラクティス**:
- `description` は単なる機能説明ではなく、**「いつ使うのか」を明示**する
- 例：`Extract text and tables from PDF files, fill forms, merge documents. Use when working with PDF files or when the user mentions PDFs, forms, or document extraction.`
- トリガーとなるキーワードや状況を含めることで、自動認識の精度が向上

**注意点**:
- description が曖昧すぎると、Claude がいつそのスキルを使うべきか判断できない
- 具体的なユースケースやトリガーワードを含めることが重要

### スキル数とパフォーマンス

**40個のスキルは多すぎるか？**

公式ドキュメントには「リポジトリあたりの最適スキル数」という明確な記載はありません。ただし、以下の点に注意：

- **起動時のコスト**: 各スキルのメタデータが読み込まれる（約100トークン/スキル）
- **40スキル × 100トークン = 約4,000トークン**の固定コスト
- 必要に応じてスキルを複数のプラグインに分割することを検討

> 出典: [Agent Skills ベストプラクティス](https://platform.claude.com/docs/en/agents-and-tools/agent-skills/best-practices)

**推奨アプローチ**:
- スキル群を**カテゴリー別に分類**
- 40個を1つのディレクトリに置くのではなく、グループ化を検討
- CLAUDE.md や README で説明文書を作成し、スキルの使い分けを明示

### 自動認識の問題を解決するために

1. **description を詳細化**
   - 「何ができるか」だけでなく「いつ使うか」を明記
   - トリガーとなるキーワードを含める

2. **スキルのグループ化**
   - カテゴリー別に整理
   - 関連するスキルをまとめて説明

3. **パフォーマンスへの配慮**
   - 使用頻度の低いスキルは別プラグインに分離
   - コアなスキルとオプショナルなスキルを区別

> 出典: [Agent Skills 概要](https://platform.claude.com/docs/en/agents-and-tools/agent-skills/overview)

## 参照ドキュメント

相談で参照した公式ドキュメントのURL一覧：

- [Claude Code Skills ドキュメント](https://code.claude.com/docs/en/skills.md)
- [Agent Skills ベストプラクティス](https://platform.claude.com/docs/en/agents-and-tools/agent-skills/best-practices)
- [Agent Skills 概要](https://platform.claude.com/docs/en/agents-and-tools/agent-skills/overview)

## 次のアクション

1. **既存の40スキルの description を見直し**
   - 「いつ使うか」が明確に記載されているか確認
   - トリガーワードやユースケースを追加

2. **スキルのカテゴリー分類を検討**
   - マスターデータ関連
   - Unity/ゲームクライアント関連
   - GitHub/CI/CD関連
   - など、グループ化できるか検討

3. **使用頻度の分析**
   - よく使うスキルとそうでないスキルを分類
   - 必要に応じて複数プラグインへの分割を検討
   - **詳細は別レポート参照**: [プラグイン分割の詳細ガイド](./20251227_プラグイン分割の詳細ガイド.md)

4. **CLAUDE.md の整備**
   - スキルの使い分けガイドを作成
   - カテゴリー別のスキル一覧を記載

---

*このレポートは `/guide-chat` コマンドにより自動生成されました*

# glow-server ドキュメント

このディレクトリには、glow-serverプロジェクトの各種ドキュメントが格納されています。

## 📚 ドキュメント一覧

### プロジェクト全般

- [01_project/](01_project/) - プロジェクト全体の設計ドキュメント
  - アーキテクチャ設計
  - コーディング規約
  - 開発ガイドライン

### 設計・仕様

- [sdd-v2/](sdd-v2/) - **SDD v2 (Spec-Driven Development) ドキュメント**
  - 機能要件の定義
  - プランナー確認結果
  - API設計書
  - **CodeRabbitと連携した機能要件チェック**

- [sdd/](sdd/) - SDD v1（旧バージョン）ドキュメント

### 実装資料

- [api-sequence-diagrams/](api-sequence-diagrams/) - APIエンドポイントのシーケンス図
- [impl_check_sheet/](impl_check_sheet/) - 実装チェックシート
- [plans/](plans/) - 実装計画書
- [research/](research/) - 調査資料

## 🔍 重要なドキュメント

### CodeRabbit機能要件チェック設定

**📖 [CodeRabbit 機能要件チェック設定ガイド](coderabbit-functional-requirements-check.md)**

CodeRabbitを使用して、PRレビュー時に機能要件の実装状況を自動チェックする仕組みについて説明しています。

**主な効果：**
- ✅ 機能要件が列挙され、実装箇所がレポートされる
- ✅ 機能要件の漏れがないかを判断できる
- ✅ レビュイーはチームレビュー前に要件漏れに気付ける
- ✅ レビュワーは非機能要件のレビューに集中できる
- ✅ バグが少なく、将来の追加/変更に強い実装成果物が出来上がる

**関連ドキュメント：**
- [SDD v2ドキュメント](sdd-v2/) - 機能要件の定義元
- [.coderabbit.yaml](../.coderabbit.yaml) - CodeRabbit設定ファイル
- [PRテンプレート](../.github/pull_request_template.md) - PR作成時のテンプレート

### SDD v2 設計フロー

**📖 [SDD v2 README](sdd-v2/README.md)**

Spec-Driven Development (SDD) v2の設計フローと、CodeRabbitとの連携について説明しています。

**主な特徴：**
- ゲーム体験仕様書PDFとコードベースから要件を抽出
- プランナー確認を経て仕様を確定
- API設計書を作成
- **CodeRabbitが機能要件の実装状況を自動チェック**

## 🚀 クイックスタート

### 新機能を実装する場合

1. **SDD v2ドキュメントを作成**
   ```bash
   /api:sdd-v2:00-run-full {機能名}
   ```

2. **機能要件を確認**
   - `docs/sdd-v2/features/{機能名}/01_要件調査.md` の「統合要件一覧」を参照

3. **実装する**
   - 要件IDを確認しながら実装

4. **PRを作成**
   - PRテンプレートの「機能要件チェック」セクションに記入
   - 実装した要件IDにチェックを入れる

5. **CodeRabbitのレビューを確認**
   - 機能要件の実装状況レポートを確認
   - 未実装の要件があれば対応

### 既存機能を修正する場合

1. **該当する機能のSDD v2ドキュメントを確認**
   - `docs/sdd-v2/features/` から該当する機能を探す

2. **影響する要件を確認**
   - 修正が既存の要件に影響しないか確認

3. **PRを作成**
   - PRテンプレートに従って記入

## 📝 ドキュメント作成ガイドライン

### SDD v2ドキュメントの作成

新機能実装時は、必ずSDD v2ドキュメントを作成してください。これにより：

- 要件が明確化される
- プランナーとの認識齟齬が減る
- CodeRabbitが機能要件漏れを自動チェックできる
- 将来の保守性が向上する

詳細は [SDD v2 README](sdd-v2/README.md) を参照してください。

### APIシーケンス図の作成

API実装後は、シーケンス図を作成することを推奨します：

```bash
/api:generate-sequence-diagram {エンドポイントURL} {サフィックス}
```

詳細は [api-sequence-diagrams/README.md](api-sequence-diagrams/README.md) を参照してください。

## 🔗 関連リンク

- [プロジェクトルートREADME](../README.md) - 環境構築ガイド
- [CLAUDE.md](../CLAUDE.md) - Claude Code用のプロジェクトガイド
- [.claude/api.md](../.claude/api.md) - API開発ガイド
- [.claude/admin.md](../.claude/admin.md) - Admin開発ガイド

## ❓ よくある質問

### Q: CodeRabbitの機能要件チェックが動作しない

**A:** 以下を確認してください：

1. SDD v2ドキュメントが存在するか（`docs/sdd-v2/features/{機能名}/`）
2. PRテンプレートの「機能要件チェック」セクションに情報が記載されているか
3. `.coderabbit.yaml`の設定が正しいか

詳細は [CodeRabbit 機能要件チェック設定ガイド](coderabbit-functional-requirements-check.md#トラブルシューティング) を参照してください。

### Q: SDD v2ドキュメントはいつ作成すべきか

**A:** 新機能実装の場合は、実装開始前に作成してください。これにより：

- 要件が明確になる
- プランナーとの認識齟齬を早期に発見できる
- 実装の方向性が定まる

詳細は [SDD v2 README](sdd-v2/README.md) を参照してください。

### Q: 既存機能の修正時にSDD v2ドキュメントは必要か

**A:** 既存機能の軽微な修正であれば不要ですが、以下の場合は更新を検討してください：

- 機能要件が変更される場合
- 新しい要件が追加される場合
- 既存の要件の解釈が変わる場合

## 📞 お問い合わせ

ドキュメントに関する質問や改善提案がある場合は、GitHubのIssueまたはPRでお知らせください。

---
description: 新しいClaude Codeスキルを作成する。公式ベストプラクティスに従い、Progressive Disclosureパターンでスキルを構築する。
---

# スキル作成コマンド

新しいClaude Codeスキルの作成を支援します。

**重要:** このコマンドは、スキルのスコープを評価し、肥大化しそうな場合は**複数のスキルに分割する提案**を行います。

## 実行方法

`create-skill` スキルを使って、ベストプラクティスに従ったスキル作成を行います。

**スキルの内容:**
- 基本情報の収集
- 既存コードの調査
- ルールの整理・分類
- スコープ評価と分割提案
- ファイル構成の設計
- SKILL.mdと参照ファイルの生成
- 検証とフィードバック

## 参考資料

- [公式: Write SKILL.md](https://docs.claude.com/en/docs/claude-code/skills#write-skill-md)
- [公式: Best Practices](https://docs.claude.com/en/docs/agents-and-tools/agent-skills/best-practices)
- [プロジェクト: スキル作成ガイド](.claude/skills/HOW_TO_CREATE_SKILLS.md)
- [create-skill スキル](.claude/skills/create-skill/SKILL.md)

---

それでは、**create-skill スキル**を使って新しいスキルを作成します。

まず、基本情報をお聞かせください：

## 質問

### 1. スキルの目的
何をサポートするスキルですか？

### 2. 使用状況
どのような場面で使われますか？

### 3. 対象範囲
何を対象とするか？（例: 特定のディレクトリ、機能、ツール）

---

回答いただいた内容を元に、**create-skill スキル**が詳細な構成設計とファイル生成を行います。

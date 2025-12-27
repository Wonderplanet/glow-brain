# Claude Code ガイド相談レポート

**日付**: 2025年12月27日
**トピック**: Skills の標準化と Marketplace のポータビリティ

## 相談内容

- Skills が GitHub Copilot などで標準化されたという話の真偽
- Claude Code と GitHub Copilot で Skills を共有できるか
- Plugin Marketplace は Claude Code 専用か
- Skills と Marketplace の違いと運用への影響

## 重要な結論・学び

### ユーザーの理解は完全に正しい

✓ **Skills は標準化されている** - 複数ツール間で共有可能
✓ **Marketplace は Claude Code 専用** - 他のツールでは使えない

### 1. Skills の標準化は事実

> 出典: [GitHub Copilot Agent Skills Documentation](https://docs.github.com/en/copilot/concepts/agents/about-agent-skills)

**公式ドキュメントの引用**：

> "Agent Skills are folders of instructions, scripts, and resources that Copilot can load when relevant to improve its performance in specialized tasks. They function as an open standard compatible with multiple AI agents and work across Copilot coding agent, GitHub Copilot CLI, and VS Code Insiders."

**2025年12月18日の GitHub 公式発表**：

> 出典: [GitHub Copilot now supports Agent Skills - GitHub Changelog](https://github.blog/changelog/2025-12-18-github-copilot-now-supports-agent-skills/)

> "GitHub Copilot now supports Agent Skills" - Agent Skills support was announced as a feature that works across multiple AI agents.

**Agent Skills 対応ツール一覧**：

- ✓ Claude Code
- ✓ GitHub Copilot（Copilot coding agent、Copilot CLI、VS Code Insiders）
- ✓ Claude API
- ✓ Claude Agent SDK
- ✓ Claude.ai

### 2. Skills の標準仕様

> 出典: [Agent Skills Overview - Anthropic Platform](https://platform.claude.com/docs/en/agents-and-tools/agent-skills/overview)

**必須ファイル**: `SKILL.md` with YAML frontmatter

```yaml
---
name: your-skill-name
description: Brief description of what this Skill does and when to use it
---

# Your Skill Name

## Instructions
[Clear, step-by-step guidance for Claude to follow]

## Examples
[Concrete examples of using this Skill]
```

**必須フィールド要件**：

```
name:
- Maximum 64 characters
- 小文字、数字、ハイフンのみ
- "anthropic", "claude" などの予約語は使用不可

description:
- Maximum 1024 characters
- 必須（空欄不可）
```

**標準ディレクトリ構造**：

```
.github/skills/              # GitHub Copilot が優先認識
├── skill-name/
│   ├── SKILL.md (必須)
│   ├── reference.md (オプション)
│   ├── scripts/ (オプション)
│   └── examples.md (オプション)
```

または

```
.claude/skills/              # Claude Code が優先認識
└── skill-name/
    └── SKILL.md
```

### 3. Skills の共有可能性

> 出典: [GitHub Copilot Agent Skills Documentation](https://docs.github.com/en/copilot/concepts/agents/about-agent-skills)

**公式ドキュメントの引用**：

> "Skills are organized in either `.github/skills` or `.claude/skills` directories. If you've already set up skills for Claude Code in the `.claude/skills` directory in your repository, Copilot will pick them up automatically."

**重要**：

- **`.github/skills/` に配置**: GitHub Copilot が優先的に認識
- **`.claude/skills/` に配置**: Claude Code が優先的に認識
- **どちらに配置しても**: 両ツールが自動認識可能

### 4. Marketplace は Claude Code 専用

> 出典: [Claude Code Plugin Marketplaces](https://code.claude.com/docs/en/plugin-marketplaces.md)

**公式ドキュメントの引用**：

> Plugin marketplaces are catalogs that help you distribute plugins to others.

**重要な事実**：

- ✓ Plugin Marketplace は **Claude Code 専用**の配布メカニズム
- ✗ GitHub Copilot には Marketplace 機能が**存在しない**
- ✗ Marketplace の仕様は**標準化されていない**（Claude Code 固有）

**GitHub Copilot の管理方式**：

GitHub Copilot では Marketplace を使わず、リポジトリに直接 Skills を配置する方式を採用：

```bash
# GitHub Copilot 方式（Marketplace なし）
your-repository/
└── .github/skills/
    └── skill-name/
        └── SKILL.md
```

### 5. Skills と Marketplace の明確な違い

| 項目 | Skills | Marketplace |
|------|--------|-------------|
| **標準化** | ✓ Yes - YAML frontmatter で標準化 | ✗ No - Claude Code 固有 |
| **ポータビリティ** | ✓ Yes - 複数ツールで共有可能 | ✗ No - Claude Code 専用 |
| **対応ツール** | Claude Code, GitHub Copilot, Claude API, SDK, Claude.ai | Claude Code のみ |
| **配布方式** | ファイルベース（`.github/skills/`、`.claude/skills/`） | `marketplace.json` による集中管理 |
| **GitHub Copilot** | ✓ 対応 | ✗ 非対応（機能がない） |

### 6. 各ツールでの Skills 管理方法の比較

| ツール | Skills の配置 | 配布方式 | Marketplace対応 |
|--------|--------------|--------|-----------------|
| **Claude Code** | `.claude/skills/` または `.github/skills/` | Plugin Marketplace または直接 | ✓ Yes |
| **GitHub Copilot** | `.github/skills/` のみ | リポジトリに直接配置 | ✗ No |
| **Claude API** | アップロード（REST API） | Skills API | N/A |
| **Claude Agent SDK** | `.claude/skills/` | ファイルベース | N/A |
| **Claude.ai** | アップロード（Zip形式） | Web UI | N/A |

### 7. 複数ツール対応のベストプラクティス

> 出典: [Agent Skills Overview - Anthropic Platform](https://platform.claude.com/docs/en/agents-and-tools/agent-skills/overview)

**公式ドキュメントの引用**：

> **Custom Skills** let you package domain expertise and organizational knowledge. They're available across Claude's products: create them in Claude Code, upload them via the API, or add them in claude.ai settings.

**推奨構成**：

```
glow-shared-skills/
├── .github/                          # ← GitHub Copilot + Claude Code
│   └── skills/
│       ├── masterdata-tools/
│       │   ├── SKILL.md
│       │   └── references/
│       │       └── tables.md
│       ├── screen-tools/
│       │   ├── SKILL.md
│       │   └── scripts/
│       │       └── analyze.py
│       └── game-content-tools/
│           └── SKILL.md
│
├── .claude-plugin/                   # ← Claude Code Marketplace 用（オプション）
│   └── marketplace.json
│
├── docs/
│   └── SKILLS.md
│
└── README.md
```

**Marketplace を併用する場合**：

`marketplace.json` で `.github/skills/` を参照：

```json
{
  "name": "glow-skills-marketplace",
  "owner": {"name": "GLOW Development Team"},
  "plugins": [
    {
      "name": "glow-masterdata-tools",
      "source": "./.github/skills/masterdata-tools",
      "description": "マスタデータ調査スキル"
    },
    {
      "name": "glow-screen-tools",
      "source": "./.github/skills/screen-tools",
      "description": "画面ドキュメント作成スキル"
    }
  ]
}
```

## 実際の運用への影響

### シナリオ1: Claude Code のみ使用

**選択肢A: Marketplace を使用**

```
glow-skills-marketplace/
├── .claude-plugin/marketplace.json
└── plugins/
    ├── masterdata-tools/
    │   └── skills/skill1/SKILL.md
    └── screen-tools/
        └── skills/skill2/SKILL.md
```

**選択肢B: 直接配置**

```
glow-brain/
└── .claude/skills/
    ├── masterdata-tools/SKILL.md
    └── screen-tools/SKILL.md
```

### シナリオ2: Claude Code + GitHub Copilot 両方使用

**推奨: `.github/skills/` に配置**

```
glow-brain/
└── .github/skills/              # ← 両ツール対応
    ├── masterdata-tools/SKILL.md
    └── screen-tools/SKILL.md
```

**Claude Code 設定**（オプション）:

```json
{
  "extraKnownMarketplaces": {
    "glow-skills": {
      "source": {
        "source": "github",
        "repo": "your-org/glow-brain"
      }
    }
  }
}
```

**GitHub Copilot 設定** (VS Code Insiders):

```json
{
  "chat.useAgentSkills": true
}
```

### シナリオ3: 社内チーム全体で Skills を共有

**推奨構成**:

```
# 1つの共有リポジトリ
glow-shared-skills/
├── .github/skills/              # ← 全ツール対応
│   ├── masterdata-tools/
│   ├── screen-tools/
│   └── game-content-tools/
│
├── .claude-plugin/              # ← Claude Code Marketplace 用
│   └── marketplace.json
│
└── README.md
```

**チームメンバーの使用方法**：

**Claude Code ユーザー**:
```bash
# オプション1: Marketplace 経由
/plugin marketplace add your-org/glow-shared-skills

# オプション2: プロジェクトに直接配置
# プロジェクトの .claude/settings.json で参照
```

**GitHub Copilot ユーザー**:
```bash
# .github/skills/ を自動認識（設定不要）
# プロジェクト内に配置すれば使用可能
```

## 具体的な設定例

### Claude Code での使用

**方法1: Marketplace 経由**

`.claude/settings.json`:

```json
{
  "extraKnownMarketplaces": {
    "glow-skills": {
      "source": {
        "source": "github",
        "repo": "your-org/glow-shared-skills"
      }
    }
  },
  "enabledPlugins": {
    "glow-masterdata-tools@glow-skills": true
  }
}
```

**方法2: 直接参照**

プロジェクト内に `.github/skills/` があれば自動認識。

### GitHub Copilot での使用

**VS Code Insiders の設定**:

```json
{
  "chat.useAgentSkills": true
}
```

リポジトリ内の `.github/skills/` が自動認識される。

### Claude API での使用

```python
import anthropic

client = anthropic.Anthropic()

# Skills をアップロード
skill = client.beta.skills.create(
    display_title="GLOW Masterdata Tools",
    files=files_from_dir(".github/skills/masterdata-tools"),
    betas=["skills-2025-10-02"]
)

# API で使用
response = client.beta.messages.create(
    model="claude-opus-4-5-20251101",
    max_tokens=4096,
    betas=["skills-2025-10-02"],
    container={
        "skills": [{
            "type": "custom",
            "skill_id": skill.id,
            "version": "latest"
        }]
    },
    messages=[{"role": "user", "content": "マスタデータを調査"}]
)
```

## 参照ドキュメント

相談で参照した公式ドキュメントのURL一覧：

- [Agent Skills Overview - Anthropic Platform](https://platform.claude.com/docs/en/agents-and-tools/agent-skills/overview)
- [Using Agent Skills with the Claude API](https://platform.claude.com/docs/en/build-with-claude/skills-guide)
- [GitHub Copilot Agent Skills Documentation](https://docs.github.com/en/copilot/concepts/agents/about-agent-skills)
- [VS Code Agent Skills Customization](https://code.visualstudio.com/docs/copilot/customization/agent-skills)
- [GitHub Copilot now supports Agent Skills - GitHub Changelog](https://github.blog/changelog/2025-12-18-github-copilot-now-supports-agent-skills/)
- [Claude Code Plugin Marketplaces](https://code.claude.com/docs/en/plugin-marketplaces.md)

## まとめ

### ユーザーの質問への回答

**Q: Skills は GitHub Copilot などでも使えるように標準化された？**
- **A: はい、正しいです。** 2025年12月18日に GitHub が公式発表しました。

**Q: Skills は Claude Code と GitHub Copilot で共有できる？**
- **A: はい、可能です。** `.github/skills/` に配置すれば両ツールで使用できます。

**Q: Marketplace は共有できない？**
- **A: その通りです。** Plugin Marketplace は Claude Code 専用で、GitHub Copilot には対応していません（そもそも Marketplace 機能がありません）。

### 重要な結論

| 項目 | 結論 |
|------|------|
| **Skills の標準化** | ✓ Yes - YAML frontmatter で標準化、複数ツール対応 |
| **Skills の共有** | ✓ Yes - `.github/skills/` に配置すれば Claude Code + GitHub Copilot で共有可能 |
| **Marketplace の標準化** | ✗ No - Claude Code 専用、他ツールでは使えない |
| **ベストプラクティス** | `.github/skills/` に配置（両ツール対応）+ Marketplace は Claude Code の追加機能として使用 |

### 推奨される運用方針

1. **Skills は `.github/skills/` に配置** - 複数ツール対応
2. **Marketplace は Claude Code の付加価値** - 社内配布を効率化したい場合のみ使用
3. **40個のスキルを分類** - カテゴリー別にディレクトリ分割
4. **プライベートリポジトリで管理** - 会社外に共有されない

これにより、**Claude Code と GitHub Copilot の両方で同じ Skills を使いつつ、Claude Code では Marketplace による効率的な管理も可能**になります。

---

*このレポートは `/guide-chat` コマンドにより自動生成されました*

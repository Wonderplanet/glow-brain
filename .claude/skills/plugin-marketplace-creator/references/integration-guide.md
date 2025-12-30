# Integration Guide: Working with Existing Claude Code Tools

This guide explains how to integrate the Plugin Marketplace Creator with existing Claude Code creation tools.

## Table of Contents

1. [Overview](#overview)
2. [Integration with /claude-code:create-skill](#integration-with-claude-codecreate-skill)
3. [Integration with /claude-code:create-command](#integration-with-claude-codecreate-command)
4. [Integration with /claude-code:create-subagent](#integration-with-claude-codecreate-subagent)
5. [Complete Workflow Examples](#complete-workflow-examples)

## Overview

The Plugin Marketplace Creator works seamlessly with Claude Code's existing creation tools:

| Tool | Purpose | Output Location |
|------|---------|----------------|
| `/claude-code:create-skill` | Create new skills | `.claude/skills/[skill-name]/` |
| `/claude-code:create-command` | Create custom commands | `.claude/commands/[category]/[command].md` |
| `/claude-code:create-subagent` | Create subagents | `.claude/agents/[agent-name].md` |

**Integration Strategy**: Create components first, then package them into a plugin and add to marketplace.

## Integration with /claude-code:create-skill

### Workflow

```
1. Create Skill
   ↓
2. Package as Plugin
   ↓
3. Add to Marketplace
```

### Step-by-Step Example

**Step 1: Create a new skill**

```bash
/claude-code:create-skill PDFファイルからテキストと表を抽出し、複数のPDFをマージする機能
```

This creates: `.claude/skills/pdf-processor/`

**Step 2: Move to marketplace plugin directory**

```bash
# Assuming marketplace is at ./my-marketplace/
mkdir -p ./my-marketplace/plugins/pdf-processor
mv .claude/skills/pdf-processor/* ./my-marketplace/plugins/pdf-processor/
```

**Step 3: Create plugin.json**

```bash
python3 ${CLAUDE_SKILL_ROOT}/scripts/generate_plugin.py \
  --name pdf-processor \
  --description "PDF processing utilities" \
  --version 1.0.0 \
  --category productivity \
  --output ./my-marketplace/plugins/pdf-processor/.claude-plugin/plugin.json \
  --pretty
```

**Step 4: Add to marketplace**

Update `marketplace.json`:

```json
{
  "plugins": [
    {
      "name": "pdf-processor",
      "source": "./plugins/pdf-processor",
      "description": "PDF processing utilities",
      "version": "1.0.0",
      "category": "productivity"
    }
  ]
}
```

## Integration with /claude-code:create-command

### Workflow

```
1. Create Command
   ↓
2. Move to Plugin
   ↓
3. Configure Plugin
   ↓
4. Add to Marketplace
```

### Step-by-Step Example

**Step 1: Create a new command**

```bash
/claude-code:create-command 指定したイシューを修正するコマンド
```

This creates: `.claude/commands/[category]/fix-issue.md`

**Step 2: Create plugin structure**

```bash
mkdir -p ./my-marketplace/plugins/issue-fixer/{.claude-plugin,commands}
```

**Step 3: Move command to plugin**

```bash
mv .claude/commands/general/fix-issue.md \
   ./my-marketplace/plugins/issue-fixer/commands/
```

**Step 4: Create plugin.json**

```bash
python3 ${CLAUDE_SKILL_ROOT}/scripts/generate_plugin.py \
  --name issue-fixer \
  --description "Automated issue fixing utilities" \
  --version 1.0.0 \
  --category devops \
  --output ./my-marketplace/plugins/issue-fixer/.claude-plugin/plugin.json \
  --pretty
```

**Step 5: Add to marketplace.json**

```json
{
  "plugins": [
    {
      "name": "issue-fixer",
      "source": "./plugins/issue-fixer",
      "description": "Automated issue fixing utilities",
      "version": "1.0.0",
      "category": "devops",
      "commands": [
        "./commands/fix-issue.md"
      ]
    }
  ]
}
```

## Integration with /claude-code:create-subagent

### Workflow

```
1. Create Subagent
   ↓
2. Move to Plugin
   ↓
3. Configure Plugin
   ↓
4. Add to Marketplace
```

### Step-by-Step Example

**Step 1: Create a new subagent**

```bash
/claude-code:create-subagent テストを自動実行してエラーを修正するsubagent
```

This creates: `.claude/agents/test-runner.md`

**Step 2: Create plugin structure**

```bash
mkdir -p ./my-marketplace/plugins/test-automation/{.claude-plugin,agents}
```

**Step 3: Move subagent to plugin**

```bash
mv .claude/agents/test-runner.md \
   ./my-marketplace/plugins/test-automation/agents/
```

**Step 4: Create plugin.json**

```bash
python3 ${CLAUDE_SKILL_ROOT}/scripts/generate_plugin.py \
  --name test-automation \
  --description "Automated testing and error fixing" \
  --version 1.0.0 \
  --category testing \
  --output ./my-marketplace/plugins/test-automation/.claude-plugin/plugin.json \
  --pretty
```

**Step 5: Add to marketplace.json**

```json
{
  "plugins": [
    {
      "name": "test-automation",
      "source": "./plugins/test-automation",
      "description": "Automated testing and error fixing",
      "version": "1.0.0",
      "category": "testing",
      "agents": [
        "./agents/test-runner.md"
      ]
    }
  ]
}
```

## Complete Workflow Examples

### Example 1: Multi-Component Plugin

Create a plugin with multiple components (skill + command + subagent).

**Step 1: Create all components**

```bash
/claude-code:create-skill Excelデータ分析機能
/claude-code:create-command Excelレポート生成コマンド
/claude-code:create-subagent Excel検証エージェント
```

**Step 2: Create plugin structure**

```bash
mkdir -p ./my-marketplace/plugins/excel-toolkit/{.claude-plugin,skills,commands,agents}
```

**Step 3: Move all components**

```bash
# Move skill
mv .claude/skills/excel-analyzer \
   ./my-marketplace/plugins/excel-toolkit/skills/

# Move command
mv .claude/commands/data/excel-report.md \
   ./my-marketplace/plugins/excel-toolkit/commands/

# Move agent
mv .claude/agents/excel-validator.md \
   ./my-marketplace/plugins/excel-toolkit/agents/
```

**Step 4: Create plugin.json**

```bash
python3 ${CLAUDE_SKILL_ROOT}/scripts/generate_plugin.py \
  --name excel-toolkit \
  --description "Complete Excel data analysis toolkit" \
  --version 1.0.0 \
  --category data \
  --keywords "excel,data,analysis" \
  --output ./my-marketplace/plugins/excel-toolkit/.claude-plugin/plugin.json \
  --pretty
```

**Step 5: Add to marketplace**

```json
{
  "plugins": [
    {
      "name": "excel-toolkit",
      "source": "./plugins/excel-toolkit",
      "description": "Complete Excel data analysis toolkit",
      "version": "1.0.0",
      "category": "data",
      "keywords": ["excel", "data", "analysis"],
      "skills": [
        "./skills/excel-analyzer/"
      ],
      "commands": [
        "./commands/excel-report.md"
      ],
      "agents": [
        "./agents/excel-validator.md"
      ]
    }
  ]
}
```

### Example 2: Rapid Plugin Creation from Scratch

Use integration helpers to create and package a plugin quickly.

**Scenario**: Create a deployment automation plugin

```bash
# Step 1: Create marketplace
python3 ${CLAUDE_SKILL_ROOT}/scripts/init_marketplace.py company-tools --path .

# Step 2: Create deployment command
/claude-code:create-command アプリケーションをステージング環境にデプロイ

# Step 3: Create deployment subagent
/claude-code:create-subagent デプロイメント監視エージェント

# Step 4: Create plugin directory
mkdir -p ./company-tools/plugins/deployment-automation/{.claude-plugin,commands,agents}

# Step 5: Move components
mv .claude/commands/devops/deploy-staging.md \
   ./company-tools/plugins/deployment-automation/commands/
mv .claude/agents/deployment-monitor.md \
   ./company-tools/plugins/deployment-automation/agents/

# Step 6: Generate plugin.json
python3 ${CLAUDE_SKILL_ROOT}/scripts/generate_plugin.py \
  --name deployment-automation \
  --description "Automated deployment to staging and production" \
  --version 1.0.0 \
  --category devops \
  --license MIT \
  --output ./company-tools/plugins/deployment-automation/.claude-plugin/plugin.json \
  --pretty

# Step 7: Update marketplace.json (manually or via script)
# Add plugin entry to ./company-tools/.claude-plugin/marketplace.json

# Step 8: Validate
cd ./company-tools
claude plugin validate .
```

## Best Practices

### 1. Component Organization

Organize components logically within plugins:

```
my-plugin/
├── .claude-plugin/
│   └── plugin.json
├── skills/              # Complex, multi-file workflows
│   └── analyzer/
├── commands/            # Simple, single-file commands
│   ├── generate.md
│   └── validate.md
├── agents/              # Autonomous agents
│   └── reviewer.md
└── scripts/             # Utility scripts
    └── helper.py
```

### 2. Version Management

When updating components:
1. Update component files
2. Increment plugin version in `plugin.json`
3. Update marketplace.json entry
4. Re-validate marketplace

### 3. Testing Before Distribution

```bash
# Test locally
/plugin marketplace add ./my-marketplace
/plugin install my-plugin@my-marketplace

# Test the plugin functionality
# ... use the plugin ...

# If successful, push to repository
git add .
git commit -m "Add my-plugin to marketplace"
git push
```

### 4. Component Reuse

Don't duplicate components across plugins. Instead:
- Create shared utilities as separate plugins
- Use plugin dependencies (if supported in future)
- Document which plugins work well together

## Troubleshooting

### Issue: Component not found after moving

**Symptom**: Plugin installs but components don't work

**Solution**: Verify paths in plugin.json or marketplace.json:

```json
{
  "commands": [
    "./commands/my-command.md"  // Relative to plugin root
  ]
}
```

### Issue: ${CLAUDE_PLUGIN_ROOT} not resolving

**Symptom**: Scripts can't find files

**Solution**: Always use `${CLAUDE_PLUGIN_ROOT}` in component paths:

```bash
# Wrong
python3 ./scripts/helper.py

# Correct
python3 ${CLAUDE_PLUGIN_ROOT}/scripts/helper.py
```

### Issue: Marketplace validation fails

**Symptom**: `claude plugin validate .` reports errors

**Solution**: Check:
1. JSON syntax (use `jq . < marketplace.json`)
2. Required fields present
3. Plugin source paths exist
4. No reserved marketplace names

## References

- Marketplace Schema: `marketplace-schema.md`
- Plugin Schema: `plugin-schema.md`
- Official Documentation: https://code.claude.com/docs/en/plugins.md

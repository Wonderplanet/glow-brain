# Marketplace.json Schema Reference

Complete schema specification for Claude Code Plugin Marketplace configuration files.

## Table of Contents

1. [Overview](#overview)
2. [Required Fields](#required-fields)
3. [Optional Metadata](#optional-metadata)
4. [Plugin Entries](#plugin-entries)
5. [Plugin Sources](#plugin-sources)
6. [Complete Example](#complete-example)

## Overview

The `marketplace.json` file defines a Plugin Marketplace catalog containing multiple plugins that users can discover and install.

**Location**: `.claude-plugin/marketplace.json`

## Required Fields

### name

- **Type**: `string`
- **Format**: kebab-case (lowercase letters, numbers, hyphens only)
- **Description**: Unique identifier for the marketplace
- **Example**: `"company-tools"`, `"team-plugins"`

### owner

- **Type**: `object`
- **Description**: Information about the marketplace maintainer

#### owner.name

- **Type**: `string`
- **Required**: Yes
- **Description**: Name of the maintainer/organization
- **Example**: `"DevTools Team"`, `"Engineering Department"`

#### owner.email

- **Type**: `string`
- **Required**: No
- **Description**: Contact email for the maintainer
- **Example**: `"devtools@example.com"`

### plugins

- **Type**: `array`
- **Description**: Array of plugin definitions
- **Example**: See [Plugin Entries](#plugin-entries)

## Optional Metadata

The `metadata` object can include:

### metadata.description

- **Type**: `string`
- **Description**: Brief description of the marketplace
- **Example**: `"Company-wide development tools and plugins"`

### metadata.version

- **Type**: `string`
- **Format**: Semantic versioning (MAJOR.MINOR.PATCH)
- **Description**: Marketplace version
- **Example**: `"1.0.0"`, `"2.1.3"`

### metadata.pluginRoot

- **Type**: `string`
- **Description**: Base directory for all plugin paths
- **Benefits**: Simplifies plugin source paths
- **Example**: `"./plugins"`

**Usage Example**:

```json
{
  "metadata": {
    "pluginRoot": "./plugins"
  },
  "plugins": [
    {
      "name": "formatter",
      "source": "formatter"  // Resolves to "./plugins/formatter"
    }
  ]
}
```

## Plugin Entries

Each entry in the `plugins` array defines a single plugin.

### Required Plugin Fields

#### name

- **Type**: `string`
- **Format**: kebab-case
- **Max Length**: 64 characters
- **Description**: Unique plugin identifier
- **Example**: `"code-formatter"`, `"deploy-tools"`

#### source

- **Type**: `string` or `object`
- **Description**: Plugin source location
- **See**: [Plugin Sources](#plugin-sources)

### Optional Plugin Fields

#### description

- **Type**: `string`
- **Description**: Plugin description
- **Example**: `"Automatic code formatting on save"`

#### version

- **Type**: `string`
- **Format**: Semantic versioning
- **Example**: `"2.1.0"`

#### author

- **Type**: `object`
- **Fields**:
  - `name` (string): Author name
  - `email` (string, optional): Author email

#### homepage

- **Type**: `string`
- **Format**: URL
- **Description**: Plugin documentation URL
- **Example**: `"https://docs.example.com/plugins/formatter"`

#### repository

- **Type**: `string`
- **Format**: URL
- **Description**: Source code repository URL
- **Example**: `"https://github.com/company/formatter-plugin"`

#### license

- **Type**: `string`
- **Format**: SPDX license identifier
- **Example**: `"MIT"`, `"Apache-2.0"`, `"GPL-3.0"`

#### keywords

- **Type**: `array` of `string`
- **Description**: Search and discovery keywords
- **Example**: `["formatting", "code-quality", "linter"]`

#### category

- **Type**: `string`
- **Description**: Plugin category
- **Examples**: `"productivity"`, `"devops"`, `"testing"`, `"security"`

#### tags

- **Type**: `array` of `string`
- **Description**: Additional classification tags
- **Example**: `["automation", "ci-cd"]`

#### strict

- **Type**: `boolean`
- **Default**: `true`
- **Description**:
  - `true`: Plugin must have valid `plugin.json`
  - `false`: Plugin can be defined entirely in marketplace entry

## Plugin Sources

The `source` field specifies where to find the plugin.

### Type 1: Relative Path

For plugins in the same repository:

```json
{
  "name": "my-plugin",
  "source": "./plugins/my-plugin"
}
```

### Type 2: GitHub Repository

For plugins hosted on GitHub:

```json
{
  "name": "github-plugin",
  "source": {
    "source": "github",
    "repo": "owner/repository-name"
  }
}
```

### Type 3: Git URL

For plugins hosted on GitLab, Bitbucket, or private Git servers:

```json
{
  "name": "git-plugin",
  "source": {
    "source": "url",
    "url": "https://gitlab.com/team/plugin.git"
  }
}
```

## Component Overrides

Marketplace entries can override specific plugin components:

### commands

- **Type**: `array` of `string`
- **Description**: Custom command file paths

```json
{
  "commands": [
    "./commands/core/",
    "./commands/experimental/preview.md"
  ]
}
```

### agents

- **Type**: `array` of `string`
- **Description**: Custom agent file paths

```json
{
  "agents": [
    "./agents/security-reviewer.md",
    "./agents/compliance-checker.md"
  ]
}
```

### hooks

- **Type**: `object`
- **Description**: Custom hook configurations

```json
{
  "hooks": {
    "PostToolUse": [
      {
        "matcher": "Write|Edit",
        "hooks": [
          {
            "type": "command",
            "command": "${CLAUDE_PLUGIN_ROOT}/scripts/validate.sh"
          }
        ]
      }
    ]
  }
}
```

### mcpServers

- **Type**: `object`
- **Description**: MCP server configurations

```json
{
  "mcpServers": {
    "enterprise-db": {
      "command": "${CLAUDE_PLUGIN_ROOT}/servers/db-server",
      "args": ["--config", "${CLAUDE_PLUGIN_ROOT}/config.json"]
    }
  }
}
```

**Important**: Always use `${CLAUDE_PLUGIN_ROOT}` for paths within plugins.

## Complete Example

```json
{
  "name": "company-tools",
  "owner": {
    "name": "DevTools Team",
    "email": "devtools@example.com"
  },
  "metadata": {
    "description": "Company-wide development tools and plugins",
    "version": "2.0.0",
    "pluginRoot": "./plugins"
  },
  "plugins": [
    {
      "name": "code-formatter",
      "source": "formatter",
      "description": "Automatic code formatting on save",
      "version": "2.1.0",
      "author": {
        "name": "DevTools Team"
      },
      "category": "productivity",
      "tags": ["formatting", "code-quality"],
      "license": "MIT"
    },
    {
      "name": "deployment-tools",
      "source": {
        "source": "github",
        "repo": "company/deploy-plugin"
      },
      "description": "Deployment automation tools",
      "version": "1.5.0",
      "homepage": "https://docs.example.com/deploy",
      "repository": "https://github.com/company/deploy-plugin",
      "license": "Apache-2.0",
      "category": "devops",
      "keywords": ["deployment", "automation", "ci-cd"]
    },
    {
      "name": "security-scanner",
      "source": {
        "source": "url",
        "url": "https://gitlab.com/company/security-plugin.git"
      },
      "description": "Security vulnerability scanning",
      "version": "3.0.0",
      "keywords": ["security", "vulnerability", "scanning"],
      "strict": false
    }
  ]
}
```

## Reserved Names

The following marketplace names are reserved and cannot be used:

- `claude-code-marketplace`
- `claude-code-plugins`
- `claude-plugins-official`
- `anthropic-marketplace`
- `anthropic-plugins`
- `agent-skills`
- `life-sciences`

## Validation

Validate your marketplace.json:

```bash
claude plugin validate .
```

Or:

```bash
/plugin validate
```

## References

- Official Documentation: https://code.claude.com/docs/en/plugin-marketplaces.md
- Plugin Reference: https://code.claude.com/docs/en/plugins-reference.md

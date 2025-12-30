# Plugin.json Schema Reference

Complete schema specification for Claude Code Plugin configuration files.

## Table of Contents

1. [Overview](#overview)
2. [Required Fields](#required-fields)
3. [Optional Fields](#optional-fields)
4. [Complete Example](#complete-example)

## Overview

The `plugin.json` file defines metadata for a single Claude Code plugin.

**Location**: `.claude-plugin/plugin.json` (within the plugin directory)

**Relationship with marketplace.json**:
- If `strict: true` (default) in marketplace entry: `plugin.json` is required
- If `strict: false`: Plugin can be defined entirely in marketplace entry
- Fields in marketplace entry override fields in `plugin.json`

## Required Fields

### name

- **Type**: `string`
- **Format**: kebab-case (lowercase letters, numbers, hyphens only)
- **Max Length**: 64 characters
- **Description**: Unique plugin identifier
- **Example**: `"code-formatter"`, `"deploy-tools"`

### description

- **Type**: `string`
- **Description**: Brief description of plugin functionality
- **Recommended Length**: 30-50 characters
- **Example**: `"Automatic code formatting on save"`

### version

- **Type**: `string`
- **Format**: Semantic versioning (MAJOR.MINOR.PATCH)
- **Description**: Plugin version
- **Example**: `"1.0.0"`, `"2.1.3"`

## Optional Fields

### author

- **Type**: `object`
- **Description**: Plugin author/creator information

#### author.name

- **Type**: `string`
- **Description**: Author's name or team name
- **Example**: `"DevTools Team"`, `"John Smith"`

#### author.email

- **Type**: `string`
- **Format**: Valid email address
- **Description**: Author's contact email
- **Example**: `"devtools@example.com"`

**Example**:

```json
{
  "author": {
    "name": "DevTools Team",
    "email": "devtools@example.com"
  }
}
```

### homepage

- **Type**: `string`
- **Format**: Valid URL
- **Description**: Plugin documentation or homepage URL
- **Example**: `"https://docs.example.com/plugins/formatter"`

### repository

- **Type**: `string`
- **Format**: Valid URL
- **Description**: Source code repository URL
- **Example**: `"https://github.com/company/formatter-plugin"`

### license

- **Type**: `string`
- **Format**: SPDX license identifier
- **Description**: Plugin license
- **Common Values**:
  - `"MIT"`
  - `"Apache-2.0"`
  - `"GPL-3.0"`
  - `"BSD-3-Clause"`
  - `"Proprietary"`

### keywords

- **Type**: `array` of `string`
- **Description**: Keywords for plugin discovery and search
- **Example**: `["formatting", "code-quality", "linter", "prettier"]`

### category

- **Type**: `string`
- **Description**: Primary plugin category
- **Common Categories**:
  - `"productivity"` - Tools that improve development efficiency
  - `"devops"` - Deployment, CI/CD, infrastructure tools
  - `"testing"` - Testing frameworks and utilities
  - `"security"` - Security scanning and validation
  - `"data"` - Data processing and analysis
  - `"documentation"` - Documentation generation
  - `"utilities"` - General-purpose utilities

### tags

- **Type**: `array` of `string`
- **Description**: Additional classification tags
- **Example**: `["automation", "ci-cd", "docker"]`

## Complete Example

### Minimal plugin.json

```json
{
  "name": "code-formatter",
  "description": "Automatic code formatting on save",
  "version": "1.0.0"
}
```

### Full plugin.json

```json
{
  "name": "code-formatter",
  "description": "Automatic code formatting with Prettier, ESLint, and Black",
  "version": "2.1.0",
  "author": {
    "name": "DevTools Team",
    "email": "devtools@example.com"
  },
  "homepage": "https://docs.example.com/plugins/formatter",
  "repository": "https://github.com/company/formatter-plugin",
  "license": "MIT",
  "keywords": [
    "formatting",
    "prettier",
    "eslint",
    "black",
    "code-quality"
  ],
  "category": "productivity",
  "tags": [
    "automation",
    "linting",
    "style"
  ]
}
```

## Validation

When `strict: true` in marketplace entry:
- `plugin.json` must exist
- Must be valid JSON
- Must contain required fields: `name`, `description`, `version`

When `strict: false`:
- `plugin.json` is optional
- All fields can be defined in marketplace entry

## Merging Behavior

When both marketplace entry and `plugin.json` exist:

1. **Marketplace entry takes precedence**
2. Fields specified in marketplace override `plugin.json`
3. Unspecified fields use values from `plugin.json`

**Example**:

`plugin.json`:
```json
{
  "name": "formatter",
  "description": "Code formatter",
  "version": "1.0.0"
}
```

Marketplace entry:
```json
{
  "name": "formatter",
  "source": "./plugins/formatter",
  "version": "2.0.0",
  "license": "MIT"
}
```

**Effective configuration**:
```json
{
  "name": "formatter",
  "description": "Code formatter",  // From plugin.json
  "version": "2.0.0",               // Overridden by marketplace
  "license": "MIT"                  // From marketplace
}
```

## Best Practices

### Version Numbering

Follow semantic versioning:
- **MAJOR**: Incompatible API changes
- **MINOR**: New functionality, backwards-compatible
- **PATCH**: Bug fixes, backwards-compatible

Examples:
- `1.0.0` - Initial release
- `1.1.0` - Added new feature
- `1.1.1` - Fixed bug
- `2.0.0` - Breaking changes

### Keywords

Choose keywords that users might search for:
- **Too generic**: `"tool"`, `"helper"` ❌
- **Too specific**: `"formatter-for-javascript-using-prettier-2.8"` ❌
- **Just right**: `"formatting"`, `"prettier"`, `"javascript"` ✅

### Description

Write clear, concise descriptions:
- **Bad**: `"A plugin"` ❌
- **Bad**: `"This is an amazing plugin that formats your code beautifully..."` ❌
- **Good**: `"Automatic code formatting with Prettier and ESLint"` ✅

## References

- Official Documentation: https://code.claude.com/docs/en/plugins-reference.md
- Marketplace Schema: See `marketplace-schema.md`

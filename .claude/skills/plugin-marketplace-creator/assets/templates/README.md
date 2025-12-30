# [Marketplace Name]

Claude Code Plugin Marketplace

## Overview

This is a Claude Code Plugin Marketplace containing custom plugins for [your team/organization/project].

## Installation

To add this marketplace to your Claude Code installation:

```bash
# From GitHub
/plugin marketplace add your-org/your-marketplace-repo

# From local path
/plugin marketplace add ./path/to/marketplace
```

## Available Plugins

<!-- Auto-generated plugin list will go here -->

| Plugin Name | Description | Version | Category |
|-------------|-------------|---------|----------|
| example-plugin | Example plugin description | 1.0.0 | productivity |

## Usage

After adding the marketplace, install individual plugins:

```bash
# Install a specific plugin
/plugin install <plugin-name>@<marketplace-name>

# Example
/plugin install example-plugin@my-marketplace
```

## Plugin Development

### Adding a New Plugin

1. Create your plugin using Claude Code creation tools:
   ```bash
   /claude-code:create-skill <skill-description>
   /claude-code:create-command <command-description>
   /claude-code:create-subagent <subagent-description>
   ```

2. Move components to the marketplace:
   ```bash
   mkdir -p ./plugins/<plugin-name>
   mv .claude/skills/<skill-name> ./plugins/<plugin-name>/skills/
   ```

3. Create plugin.json:
   ```bash
   # Use the generation script or create manually
   cp assets/templates/plugin.json ./plugins/<plugin-name>/.claude-plugin/
   ```

4. Update marketplace.json to include the new plugin

5. Validate:
   ```bash
   claude plugin validate .
   ```

### Validation

Before committing changes, always validate your marketplace:

```bash
cd /path/to/marketplace
claude plugin validate .
```

Or use the shorthand:

```bash
/plugin validate
```

## Contributing

<!-- Add your contribution guidelines here -->

### Pull Request Process

1. Create a new branch for your plugin
2. Add your plugin to the marketplace
3. Update this README with plugin information
4. Run validation: `claude plugin validate .`
5. Submit a pull request

## License

<!-- Specify your marketplace license here -->

This marketplace is licensed under [LICENSE]. Individual plugins may have their own licenses.

## Support

<!-- Add support information here -->

For issues or questions:
- GitHub Issues: [your-repo-url]/issues
- Email: [support-email]
- Documentation: [docs-url]

## Changelog

### [1.0.0] - YYYY-MM-DD

- Initial marketplace release
- Added example-plugin

---

Generated with [Plugin Marketplace Creator](https://github.com/your-link-here)

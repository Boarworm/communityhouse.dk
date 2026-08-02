# AI Translate Plugin

Seamlessly translate October CMS Tailor records using AI. Works with multisite.

> **Note**: Utilizing the AI translation features requires a valid API key from OpenAI or Anthropic (Claude). Please be aware that usage fees may apply according to your provider's pricing.

## Installation

To install run command:

```
php artisan plugin:install boarworm.aitranslate
```

## Getting Started

1) Go to **Settings > CMS > AI Translate** and select your preferred AI provider (OpenAI or Claude) and enter your API key.

2) In the same settings page, select the Tailor Blueprints you want to enable translation for.

3) Ensure the fields you want to translate in your Tailor blueprints are marked with `translatable: true`.

4) Done! Visit your Tailor records. You will see a "AI Translate" tab.

## Features

- **Multi-Provider Support**: Choose between OpenAI and Claude for your translations.
- **Tailor Integration**: Seamlessly integrates with October CMS Tailor blueprints.
- **Configurable**: Select specific blueprints to enable translation for.

## Bulk Translation (Console)

Translate every entry of a Tailor blueprint from one site into one or more other sites:

```
php artisan aitranslate:bulk Blog\Post
```

Options:

- `--from=<code>` Source site code (default: the site marked default/primary).
- `--to=<code>` Target site code, repeatable (default: all other sites).
- `--mode=create|update` `create` skips entries that already exist on the target site; `update` overwrites them (default: `create`).

Example translating only into `de` and `fr` from the `en` site, overwriting existing translations:

```
php artisan aitranslate:bulk Blog\Post --from=en --to=de --to=fr --mode=update
```

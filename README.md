# Posted Social MCP Abilities

WordPress plugin that exposes site content, Rank Math SEO meta, internal links, site structure, and Gravity Forms data to AI assistants via the [MCP (Model Context Protocol)](https://modelcontextprotocol.io/) Abilities API. Includes read and write capabilities for programmatic SEO management.

Built for use with [Claude](https://claude.ai) and the WordPress MCP Adapter.

## Abilities

### Read

| Ability | Description |
|---|---|
| `postedsocial/get-content` | Returns published pages/posts with full content, SEO meta (Rank Math), URL, dates, and word count. Supports filtering by post type and search. |
| `postedsocial/seo-audit` | Returns SEO meta for all published content: title tag, meta description, focus keyword, robots, canonical, schema type, Rank Math score, and auto-detected issues. |
| `postedsocial/site-structure` | Returns the page hierarchy with parent/child relationships, slugs, menu order, depth, and template assignments. |
| `postedsocial/internal-links` | Analyzes internal linking for a specific page or all pages. Returns outbound internal links found in body content with anchor text. |
| `postedsocial/plugins-status` | Returns all installed plugins with name, version, active status, update availability, and author. |
| `postedsocial/gravity-forms` | Lists all Gravity Forms with entry counts, or returns recent entries for a specific form with field labels mapped. |

### Write

| Ability | Description |
|---|---|
| `postedsocial/update-seo-meta` | Updates Rank Math SEO fields for a specific page/post: focus keyword, title tag, meta description, robots directives, canonical URL, and schema type. |

## Requirements

- WordPress 6.x+
- [Rank Math SEO](https://rankmath.com/) (Free or PRO)
- [WordPress MCP Adapter](https://wordpress.org/plugins/mcp-adapter/) plugin
- [Gravity Forms](https://www.gravityforms.com/) (optional — required only for the `gravity-forms` ability)

## Installation

1. Download `posted-social-mcp-abilities.php`
2. Upload to `/wp-content/plugins/` or install as a must-use plugin in `/wp-content/mu-plugins/`
3. Activate the plugin (skip if using mu-plugins)
4. Ensure the MCP Adapter plugin is installed and active
5. Connect your site as an MCP server in Claude

## Usage

Once connected, Claude can query your site data and manage SEO meta through natural language:

```
"Run an SEO audit on all my pages"
"Set the focus keyword for the homepage to 'st louis marketing agency'"
"Show me which pages have no internal links"
"What plugins need updates?"
"How many form submissions did we get this week?"
```

### Example: Bulk Set Focus Keywords

Claude can programmatically set Rank Math focus keywords across all pages in a single conversation using the `update-seo-meta` ability — no need to open each page in the editor.

### Example: SEO Audit

The `seo-audit` ability returns structured data including auto-detected issues like missing focus keywords, thin content, title tags over 60 characters, and missing meta descriptions — making it easy for Claude to generate a prioritized action plan.

## Auto-Detected SEO Issues

The `seo-audit` ability flags the following issues per page:

- `missing_seo_title` — No Rank Math title tag set
- `missing_meta_description` — No meta description set
- `missing_focus_keyword` — No focus keyword set
- `thin_content` — Page has fewer than 300 words
- `meta_description_too_long` — Meta description exceeds 160 characters
- `seo_title_too_long` — Title tag exceeds 60 characters

## Extending

The plugin follows a simple pattern for adding new abilities. To add a new one:

1. Register the ability inside `ps_register_abilities()` using `wp_register_ability()`
2. Define the input/output schema
3. Add the execute callback function

All abilities use `'permission_callback' => '__return_true'` and `'mcp' => array( 'public' => true )` for MCP access. Adjust permissions as needed for your security requirements.

## Stack

This plugin was built for [Posted Social](https://posted-social.com), a full-service digital marketing agency in St. Louis, MO. Our WordPress stack includes:

- **Builder:** Bricks Builder + ACSS
- **SEO:** Rank Math PRO
- **Hosting:** Kinsta
- **Performance:** FlyingPress + ShortPixel
- **CDN/Security:** Cloudflare
- **Forms:** Gravity Forms + Cloudflare Turnstile
- **Management:** MainWP

## License

MIT

# Posted Social MCP Abilities

WordPress plugin that exposes site content, Rank Math SEO meta, Bricks Builder page content, internal links, site structure, and Gravity Forms data to AI assistants via the [MCP (Model Context Protocol)](https://modelcontextprotocol.io/) Abilities API. Includes read and write capabilities for programmatic SEO and content management.

Built for use with [Claude](https://claude.ai) and the WordPress MCP Adapter.

## Abilities

### Read

| Ability | Description |
|---|---|
| `postedsocial/get-content` | Returns published pages/posts with full content, SEO meta (Rank Math), URL, dates, and word count. Supports filtering by post type and search. |
| `postedsocial/seo-audit` | Returns SEO meta for all published content: title tag, meta description, focus keyword, robots, canonical, schema type, Rank Math score, and auto-detected issues. |
| `postedsocial/site-structure` | Returns the page hierarchy with parent/child relationships, slugs, menu order, depth, and template assignments. |
| `postedsocial/internal-links` | Analyzes internal linking for a specific page or all pages. Returns outbound internal links found in body content with anchor text. |
| `postedsocial/get-bricks-content` | Returns Bricks Builder elements for a page with element IDs, types, parent relationships, and settings. Supports filtering by element type and optional raw settings output. |
| `postedsocial/plugins-status` | Returns all installed plugins with name, version, active status, update availability, and author. |
| `postedsocial/gravity-forms` | Lists all Gravity Forms with entry counts, or returns recent entries for a specific form with field labels mapped. |

### Write

| Ability | Description |
|---|---|
| `postedsocial/update-seo-meta` | Updates Rank Math SEO fields for a specific page/post: focus keyword, title tag, meta description, robots directives, canonical URL, and schema type. |
| `postedsocial/update-bricks-content` | Updates specific Bricks Builder elements by element ID. Supports text, links, tags, and other settings. Merges changes (preserving unmodified fields), creates a timestamped backup before writing, and clears render cache after. Includes a dry-run mode to preview changes before saving. |

## Requirements

- WordPress 6.x+
- [Rank Math SEO](https://rankmath.com/) (Free or PRO)
- [WordPress MCP Adapter](https://wordpress.org/plugins/mcp-adapter/) plugin
- [Bricks Builder](https://bricksbuilder.io/) (required for `get-bricks-content` and `update-bricks-content` abilities)
- [Gravity Forms](https://www.gravityforms.com/) (optional — required only for the `gravity-forms` ability)

## Installation

1. Download `posted-social-mcp-abilities.php`
2. Upload to `/wp-content/plugins/` or install as a must-use plugin in `/wp-content/mu-plugins/`
3. Activate the plugin (skip if using mu-plugins)
4. Ensure the MCP Adapter plugin is installed and active
5. Connect your site as an MCP server in Claude

## Usage

Once connected, Claude can query your site data and manage SEO and content through natural language:

```
"Run an SEO audit on all my pages"
"Set the focus keyword for the homepage to 'st louis marketing agency'"
"Show me which pages have no internal links"
"Show me all the headings and text blocks on the SEO service page"
"Update the H1 on the homepage to include our target keyword"
"What plugins need updates?"
"How many form submissions did we get this week?"
```

### Example: Bulk Set Focus Keywords

Claude can programmatically set Rank Math focus keywords across all pages in a single conversation using the `update-seo-meta` ability — no need to open each page in the editor.

### Example: SEO Audit

The `seo-audit` ability returns structured data including auto-detected issues like missing focus keywords, thin content, title tags over 60 characters, and missing meta descriptions — making it easy for Claude to generate a prioritized action plan.

### Example: Bricks Content Editing

The Bricks abilities follow a read-first, write-second workflow:

1. **Read** — Use `get-bricks-content` to inspect the element tree and find the element IDs you need
2. **Preview** — Use `update-bricks-content` with `dry_run: true` to see exactly what would change
3. **Write** — Run the update for real; a timestamped backup is created automatically

```
"Show me all the headings on the homepage"
→ get-bricks-content with element_types: ["heading"]

"Change the H1 text to 'St. Louis Marketing Agency Built for Growth'"
→ update-bricks-content with dry_run: true (preview)
→ update-bricks-content (apply)
```

Bricks stores content as a flat JSON array in `_bricks_page_content_2` post meta. Each element has an `id`, `name` (element type), `parent`, and `settings` object. The update ability targets elements by ID and merges only the specified settings fields, leaving everything else untouched.

## Auto-Detected SEO Issues

The `seo-audit` ability flags the following issues per page:

- `missing_seo_title` — No Rank Math title tag set
- `missing_meta_description` — No meta description set
- `missing_focus_keyword` — No focus keyword set
- `thin_content` — Page has fewer than 300 words
- `meta_description_too_long` — Meta description exceeds 160 characters
- `seo_title_too_long` — Title tag exceeds 60 characters

## Safety Features

### Bricks Content Backups

Every `update-bricks-content` write operation automatically creates a timestamped backup of the existing content in post meta (e.g. `_bricks_page_content_2_backup_20260317_143022`). If an update causes issues, the backup can be restored by copying it back to `_bricks_page_content_2`.

### Dry Run Mode

The `update-bricks-content` ability supports `dry_run: true`, which returns a full diff of what would change without modifying any data. Always recommended before bulk or unfamiliar edits.

### Settings Merge

Updates use a merge strategy — only the fields you specify in `settings` are changed. All other element settings (styling, responsive breakpoints, conditions, etc.) are preserved untouched.

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

# Posted Social MCP Abilities

**Version:** 2.2
**Requires:** WordPress 6.0+, WP Abilities API, Bricks Builder (for Bricks abilities), Rank Math SEO (for SEO abilities)

WordPress plugin that exposes site content, SEO data, page structure, and Bricks Builder content to AI assistants via the MCP (Model Context Protocol) adapter. Includes a visual admin meta box for managing JSON-LD schema directly in the WordPress editor.

---

## Abilities Overview

| # | Ability | Type | Description |
|---|---------|------|-------------|
| 1 | `postedsocial/get-content` | Read | Pages/posts with full content and SEO meta |
| 2 | `postedsocial/seo-audit` | Read | SEO meta for all content with issue flags |
| 3 | `postedsocial/site-structure` | Read | Page hierarchy with parent/child relationships |
| 4 | `postedsocial/internal-links` | Read | Outbound internal links in post content |
| 5 | `postedsocial/plugins-status` | Read | Installed plugins with versions and update status |
| 6 | `postedsocial/gravity-forms` | Read | Forms list and recent entries |
| 7 | `postedsocial/get-bricks-content` | Read | Bricks Builder elements with IDs, types, and settings |
| 8 | `postedsocial/update-seo-meta` | Write | Update Rank Math title, description, keywords, robots, canonical |
| 9 | `postedsocial/update-bricks-content` | Write | Modify existing Bricks elements by ID |
| 10 | `postedsocial/manage-page-schema` | Write | Add/remove/list JSON-LD schema blocks rendered via wp_head |

---

## Admin Meta Box

v2.2 adds a **"Page Schemas (JSON-LD)"** meta box to the page and post editor in WP admin. This provides a visual interface for the same schema data managed by the `manage-page-schema` MCP ability.

### Features

- Collapsible accordion per schema showing key name and @type badge
- Editable JSON textarea with monospace font for each schema
- **Validate JSON** button with inline feedback (checks for valid JSON and @type field)
- **Format** button to pretty-print JSON
- **Delete** per schema with confirmation dialog
- **Add New Schema** form with key input and JSON textarea
- Client-side validation before adding (checks key format, valid JSON, @type present)
- Duplicate key detection with replace confirmation
- Saves on normal WordPress Update/Publish

### Where to find it

Edit any page or post in WP admin. Scroll below the main content area to find the "Page Schemas (JSON-LD)" meta box. If you don't see it, check Screen Options at the top of the editor and make sure it's enabled.

### Data storage

Both the admin meta box and the MCP ability read and write to the same `_ps_page_schemas` post meta field. Changes made in either place are immediately reflected in the other.

---

## Ability Reference

### 1. Get Site Content

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_type` | string | `all` | `post`, `page`, or `all` |
| `per_page` | integer | `50` | Number of items to return |
| `search` | string | `""` | Optional keyword filter |

### 2. SEO Audit

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_type` | string | `all` | `post`, `page`, or `all` |
| `per_page` | integer | `100` | Number of items to return |

**Issue flags:** `missing_seo_title`, `missing_meta_description`, `missing_focus_keyword`, `thin_content`, `meta_description_too_long`, `seo_title_too_long`.

### 3. Site Structure

No parameters. Returns page hierarchy with parent/child relationships, slugs, menu order, depth, and template.

### 4. Internal Links

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_id` | integer | `0` | Specific page ID, or `0` for all |
| `per_page` | integer | `50` | Number of pages to analyze |

**Note:** Scans `post_content` only. Links rendered by Bricks Builder are not detected.

### 5. Plugins Status

No parameters. Returns all installed plugins with version, active status, update availability.

### 6. Gravity Forms Data

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `form_id` | integer | `0` | Form ID for entries, or `0` to list all |
| `per_page` | integer | `20` | Number of entries to return |

### 7. Get Bricks Content

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_id` | integer | *required* | Page/post ID |
| `element_types` | array | `[]` | Filter by element type |
| `include_raw` | boolean | `false` | Include full raw settings |

### 8. Update SEO Meta

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_id` | integer | *required* | Page/post ID |
| `seo_title` | string | `""` | Title tag |
| `seo_description` | string | `""` | Meta description |
| `focus_keyword` | string | `""` | Primary focus keyword |
| `robots` | array | `[]` | e.g., `["noindex"]` |
| `canonical` | string | `""` | Canonical URL |
| `schema_type` | string | `""` | Rich snippet type |

**Meta key mapping:**

| Parameter | Rank Math Meta Key |
|-----------|--------------------|
| `seo_title` | `rank_math_title` |
| `seo_description` | `rank_math_description` |
| `focus_keyword` | `rank_math_focus_keyword` |
| `robots` | `rank_math_robots` |
| `canonical` | `rank_math_canonical_url` |
| `schema_type` | `rank_math_rich_snippet` |

### 9. Update Bricks Content

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_id` | integer | *required* | Page/post ID |
| `updates` | array | *required* | Array of `{element_id, settings}` objects |
| `dry_run` | boolean | `false` | Preview changes without saving |

### 10. Manage Page Schema

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_id` | integer | *required* | Page/post ID |
| `action` | string | `"list"` | `"add"`, `"remove"`, `"list"`, or `"clear"` |
| `key` | string | `""` | Unique schema key. Required for `add` and `remove` |
| `data` | object | — | Schema.org JSON-LD object. Required for `add`. Must include `@type` |

**Actions:**

| Action | Description |
|--------|-------------|
| `add` | Adds or replaces a schema by key. Requires `key` and `data`. |
| `remove` | Removes a schema by key. Requires `key`. |
| `list` | Lists all schemas for the page with keys, types, and timestamps. |
| `clear` | Removes all schemas for the page. |

**How it works:**

1. Schema data is stored in `_ps_page_schemas` post meta
2. On page load, the `wp_head` hook outputs each schema as `<script type="application/ld+json">` in the `<head>`
3. The `@context` field is auto-added if not provided
4. Same data is viewable and editable in the admin meta box

**Example: Add Service schema**

```json
{
  "post_id": 20,
  "action": "add",
  "key": "service",
  "data": {
    "@type": "Service",
    "name": "Precision Milling Services",
    "description": "CNC and manual milling for heavy equipment parts.",
    "provider": {
      "@type": "LocalBusiness",
      "name": "Wesco Machine Works",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "851 Westwood Industrial Park Drive",
        "addressLocality": "Weldon Spring",
        "addressRegion": "MO",
        "postalCode": "63304",
        "addressCountry": "US"
      },
      "telephone": "+1-636-939-5905",
      "url": "https://wescoworks.com"
    },
    "serviceType": "Precision Milling",
    "url": "https://wescoworks.com/capabilities/milling/"
  }
}
```

---

## Safety Features

- **Bricks backup on write:** Every Bricks content modification saves a timestamped backup
- **Dry run mode:** `update-bricks-content` supports `dry_run: true`
- **Cache clearing:** Bricks render cache and WP object cache cleared after writes
- **Input sanitization:** SEO meta via `sanitize_text_field`, schema keys via `sanitize_key`
- **Nonce verification:** Admin meta box uses WordPress nonce for CSRF protection
- **Permission check:** Meta box save checks `edit_post` capability
- **Autosave skip:** Meta box save skipped during WordPress autosave

---

## Dependencies

| Dependency | Required For | Status |
|------------|-------------|--------|
| WP Abilities API | All abilities | Required |
| MCP Adapter | MCP protocol access | Required |
| Rank Math SEO | SEO abilities | Optional |
| Bricks Builder | Bricks abilities | Optional |
| Gravity Forms | `gravity-forms` | Optional |

---

## Changelog

### 2.2
- Added admin meta box "Page Schemas (JSON-LD)" to page and post editors
- Visual UI for viewing, editing, adding, and deleting schemas without MCP
- JSON validation and formatting buttons
- Nonce verification, capability checks, and autosave handling on save

### 2.1
- Replaced `add-bricks-element` with `manage-page-schema`
- Schema stored in `_ps_page_schemas` post meta, rendered via `wp_head`
- Supports add, remove, list, and clear actions with unique keys

### 2.0
- Added Bricks read/write abilities and `update-seo-meta`
- Extracted shared helpers for Bricks content management

### 1.0
- Initial release with read-only abilities

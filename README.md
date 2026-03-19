# Posted Social MCP Abilities

**Version:** 2.0
**Requires:** WordPress 6.0+, WP Abilities API, Bricks Builder (for Bricks abilities), Rank Math SEO (for SEO abilities)
**Tested up to:** WordPress 6.7

WordPress plugin that exposes site content, SEO data, page structure, and Bricks Builder content to AI assistants via the MCP (Model Context Protocol) adapter.

---

## Abilities Overview

The plugin registers 10 abilities under the `postedsocial` category. Each ability is accessible through the MCP adapter and follows a read/write pattern with consistent error handling and input validation.

### Read Abilities

| # | Ability | Description |
|---|---------|-------------|
| 1 | `postedsocial/get-content` | Returns published pages and posts with full content, SEO meta, URL, dates, and word count |
| 2 | `postedsocial/seo-audit` | Returns SEO meta for all published content with issue flags for missing or invalid fields |
| 3 | `postedsocial/site-structure` | Returns the page hierarchy with parent/child relationships, slugs, and menu order |
| 4 | `postedsocial/internal-links` | Analyzes outbound internal links found in post content for one or all pages |
| 5 | `postedsocial/plugins-status` | Returns installed plugins with version, active status, and available updates |
| 6 | `postedsocial/gravity-forms` | Lists Gravity Forms with entry counts, or returns recent entries for a specific form |
| 7 | `postedsocial/get-bricks-content` | Returns Bricks Builder elements with IDs, types, parent relationships, and settings |

### Write Abilities

| # | Ability | Description |
|---|---------|-------------|
| 8 | `postedsocial/update-seo-meta` | Updates Rank Math fields: title, description, focus keyword, robots, canonical, schema type |
| 9 | `postedsocial/update-bricks-content` | Modifies existing Bricks elements by ID. Merges settings, preserves unspecified fields |
| 10 | `postedsocial/add-bricks-element` | Inserts new Bricks elements (code, text, heading, etc.) with positioning control |

---

## Ability Reference

### 1. Get Site Content

**Name:** `postedsocial/get-content`

Retrieves published pages and posts with their full text content and Rank Math SEO meta.

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_type` | string | `all` | `post`, `page`, or `all` |
| `per_page` | integer | `50` | Number of items to return |
| `search` | string | `""` | Optional keyword filter |

**Returns:** `items` array with `id`, `title`, `url`, `post_type`, `date`, `modified`, `word_count`, `content`, `excerpt`, `seo_title`, `seo_description`, `focus_keyword`, `robots`, `canonical`.

---

### 2. SEO Audit

**Name:** `postedsocial/seo-audit`

Returns SEO meta for all published content with automated issue detection.

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_type` | string | `all` | `post`, `page`, or `all` |
| `per_page` | integer | `100` | Number of items to return |

**Returns:** `items` array with all SEO fields plus `seo_title_len`, `seo_desc_len`, `seo_score`, and `issues` array.

**Issue flags:** `missing_seo_title`, `missing_meta_description`, `missing_focus_keyword`, `thin_content` (under 300 words), `meta_description_too_long` (over 160 chars), `seo_title_too_long` (over 60 chars).

---

### 3. Site Structure

**Name:** `postedsocial/site-structure`

Returns the page hierarchy showing parent/child relationships.

**Parameters:** None.

**Returns:** `pages` array with `id`, `title`, `url`, `slug`, `parent_id`, `parent_title`, `menu_order`, `depth`, `template`.

---

### 4. Internal Links

**Name:** `postedsocial/internal-links`

Analyzes internal linking by scanning `post_content` for anchor tags pointing to the same domain.

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_id` | integer | `0` | Specific page ID, or `0` for all |
| `per_page` | integer | `50` | Number of pages to analyze |

**Returns:** `items` array with `id`, `title`, `url`, `internal_links` (array of `url` and `anchor_text`), `link_count`.

**Note:** This scans `post_content` only. Links rendered by Bricks Builder or other page builders that store content in custom meta fields will not be detected.

---

### 5. Plugins Status

**Name:** `postedsocial/plugins-status`

Returns all installed plugins with their current state.

**Parameters:** None.

**Returns:** `plugins` array with `file`, `name`, `version`, `active`, `update_available`, `update_version`, `author`.

---

### 6. Gravity Forms Data

**Name:** `postedsocial/gravity-forms`

Lists forms or retrieves entries. Requires Gravity Forms to be active.

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `form_id` | integer | `0` | Specific form ID for entries, or `0` to list all forms |
| `per_page` | integer | `20` | Number of entries to return |

**Returns:** When listing forms: `forms` array with `id`, `title`, `entry_count`, `is_active`. When fetching entries: `form` title and `entries` array with `entry_id`, `date_created`, `source_url`, `fields`.

---

### 7. Get Bricks Content

**Name:** `postedsocial/get-bricks-content`

Reads Bricks Builder elements from `_bricks_page_content_2` post meta.

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_id` | integer | *required* | Page/post ID |
| `element_types` | array | `[]` | Filter by element type (e.g., `["heading", "text-basic"]`). Empty returns all |
| `include_raw` | boolean | `false` | Include full raw settings object per element |

**Returns:** `elements` array with `element_id`, `name`, `parent`, `label`, and common content fields (`text`, `tag`, `link`, `code`, `executeCode`, `noRender`, etc.). When `include_raw` is true, includes `raw_settings`.

---

### 8. Update SEO Meta

**Name:** `postedsocial/update-seo-meta`

Updates Rank Math SEO fields for a specific page or post via `update_post_meta`.

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_id` | integer | *required* | Page/post ID |
| `seo_title` | string | `""` | Rank Math title tag |
| `seo_description` | string | `""` | Meta description |
| `focus_keyword` | string | `""` | Primary focus keyword |
| `robots` | array | `[]` | Robots directives, e.g., `["noindex"]` |
| `canonical` | string | `""` | Canonical URL |
| `schema_type` | string | `""` | Rich snippet type (e.g., `Service`, `LocalBusiness`) |

Empty fields are skipped (not cleared). All string values are sanitized with `sanitize_text_field`.

**Returns:** `success`, `post_id`, `updated` (array of field names that were written).

**Meta key mapping:**

| Parameter | Rank Math Meta Key |
|-----------|--------------------|
| `seo_title` | `rank_math_title` |
| `seo_description` | `rank_math_description` |
| `focus_keyword` | `rank_math_focus_keyword` |
| `robots` | `rank_math_robots` |
| `canonical` | `rank_math_canonical_url` |
| `schema_type` | `rank_math_rich_snippet` |

---

### 9. Update Bricks Content

**Name:** `postedsocial/update-bricks-content`

Modifies existing Bricks elements by element ID. Settings are merged so unspecified fields are preserved.

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_id` | integer | *required* | Page/post ID |
| `updates` | array | *required* | Array of `{element_id, settings}` objects |
| `dry_run` | boolean | `false` | Preview changes without saving |

**Behavior:**
- Array settings are shallow-merged (`array_merge`)
- Scalar settings are replaced
- A timestamped backup of the original content is saved to `_bricks_page_content_2_backup_YYYYMMDD_HHMMSS`
- Bricks render cache (`_html` and `_css` meta) is cleared after save
- Post object cache is flushed

**Returns:** `success`, `changes` (detailed diff per element), `not_found` (unmatched IDs), `elements_updated`, `backup_key`.

---

### 10. Add Bricks Element

**Name:** `postedsocial/add-bricks-element`

Inserts a new element into the Bricks page content array. This is the ability to use for injecting JSON-LD schema, adding new text blocks, or inserting any Bricks-compatible element.

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_id` | integer | *required* | Page/post ID |
| `element` | object | *required* | Element definition (see below) |
| `position` | string | `"last"` | Insertion point (see below) |
| `dry_run` | boolean | `false` | Preview without saving |

**Element object:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | yes | Bricks element type: `code`, `text-basic`, `heading`, `html`, `block`, `container`, `section` |
| `parent` | string | no | Parent element ID, or `"0"` for top-level. Default `"0"` |
| `label` | string | no | Admin label visible in Bricks editor |
| `settings` | object | yes | Element settings (varies by type) |

**Position values:**

| Value | Behavior |
|-------|----------|
| `"first"` | Before the first sibling with the same parent |
| `"last"` | End of the element array (default) |
| `"before:element_id"` | Directly before the specified element |
| `"after:element_id"` | After the specified element and all its descendants |

**Common settings by element type:**

```
code:       {"code": "<script>...</script>", "executeCode": true, "noRender": true}
text-basic: {"text": "Your content here"}
heading:    {"text": "Your heading", "tag": "h2"}
section:    {} (structural wrapper)
container:  {} (structural wrapper)
block:      {} (structural wrapper)
```

**Example: Inject Service schema JSON-LD**

```json
{
  "post_id": 20,
  "element": {
    "name": "code",
    "parent": "0",
    "label": "Service Schema",
    "settings": {
      "code": "<script type=\"application/ld+json\">{\"@context\":\"https://schema.org\",\"@type\":\"Service\",\"name\":\"Milling Services\"}</script>",
      "executeCode": true,
      "noRender": true
    }
  },
  "position": "after:lujewj"
}
```

**Returns:** `success`, `element_id` (generated), `element` (full object), `position` (array index), `backup_key`.

---

## Safety Features

All write abilities include the following protections:

- **Backup on write:** Every Bricks content modification saves a timestamped backup to a separate meta key (`_bricks_page_content_2_backup_YYYYMMDD_HHMMSS`) before overwriting
- **Dry run mode:** Both `update-bricks-content` and `add-bricks-element` accept `dry_run: true` to preview changes without saving
- **Cache clearing:** Bricks render cache and WordPress object cache are cleared after every write to ensure changes are reflected immediately
- **Input sanitization:** All SEO meta values are passed through `sanitize_text_field`; robots directives are individually sanitized
- **Collision-free IDs:** New Bricks element IDs are generated using random lowercase strings and checked against existing IDs to prevent collisions

---

## Dependencies

| Dependency | Required For | Status |
|------------|-------------|--------|
| WP Abilities API | All abilities | Required |
| MCP Adapter | MCP protocol access | Required |
| Rank Math SEO | `seo-audit`, `get-content`, `update-seo-meta` | Optional (SEO fields return empty if not installed) |
| Bricks Builder | `get-bricks-content`, `update-bricks-content`, `add-bricks-element` | Optional (returns empty/error if no Bricks content) |
| Gravity Forms | `gravity-forms` | Optional (returns error message if not active) |

---

## Changelog

### 2.0
- Added `postedsocial/add-bricks-element` ability for inserting new elements into Bricks pages
- Added `code`, `executeCode`, and `noRender` to common fields in `get-bricks-content`
- Extracted shared helpers: `ps_generate_bricks_id`, `ps_get_bricks_elements`, `ps_save_bricks_elements`
- Added `defined('ABSPATH')` security guard
- Code cleanup and documentation improvements

### 1.2
- Added `postedsocial/update-seo-meta` for writing Rank Math meta fields
- Added `postedsocial/get-bricks-content` for reading Bricks Builder elements
- Added `postedsocial/update-bricks-content` for modifying existing Bricks elements

### 1.0
- Initial release with read-only abilities: get-content, seo-audit, site-structure, internal-links, plugins-status, gravity-forms

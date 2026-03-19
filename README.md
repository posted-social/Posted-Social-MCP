# Posted Social MCP Abilities

**Version:** 2.1
**Requires:** WordPress 6.0+, WP Abilities API, Bricks Builder (for Bricks abilities), Rank Math SEO (for SEO abilities)

WordPress plugin that exposes site content, SEO data, page structure, and Bricks Builder content to AI assistants via the MCP (Model Context Protocol) adapter.

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

## Ability Reference

### 1. Get Site Content

Returns published pages and posts with their full text content and Rank Math SEO meta.

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_type` | string | `all` | `post`, `page`, or `all` |
| `per_page` | integer | `50` | Number of items to return |
| `search` | string | `""` | Optional keyword filter |

---

### 2. SEO Audit

Returns SEO meta for all published content with automated issue detection.

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_type` | string | `all` | `post`, `page`, or `all` |
| `per_page` | integer | `100` | Number of items to return |

**Issue flags:** `missing_seo_title`, `missing_meta_description`, `missing_focus_keyword`, `thin_content`, `meta_description_too_long`, `seo_title_too_long`.

---

### 3. Site Structure

Returns the page hierarchy showing parent/child relationships. No parameters.

---

### 4. Internal Links

Scans `post_content` for internal anchor tags. Note: links rendered by Bricks Builder are not detected.

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_id` | integer | `0` | Specific page ID, or `0` for all |
| `per_page` | integer | `50` | Number of pages to analyze |

---

### 5. Plugins Status

Returns all installed plugins with their current state. No parameters.

---

### 6. Gravity Forms Data

Lists forms or retrieves entries. Requires Gravity Forms to be active.

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `form_id` | integer | `0` | Specific form ID for entries, or `0` to list all |
| `per_page` | integer | `20` | Number of entries to return |

---

### 7. Get Bricks Content

Reads Bricks Builder elements from `_bricks_page_content_2` post meta.

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_id` | integer | *required* | Page/post ID |
| `element_types` | array | `[]` | Filter by element type |
| `include_raw` | boolean | `false` | Include full raw settings |

---

### 8. Update SEO Meta

Updates Rank Math SEO fields for a specific page or post.

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

---

### 9. Update Bricks Content

Modifies existing Bricks elements by element ID. Settings are merged.

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_id` | integer | *required* | Page/post ID |
| `updates` | array | *required* | Array of `{element_id, settings}` objects |
| `dry_run` | boolean | `false` | Preview changes without saving |

---

### 10. Manage Page Schema

Add, list, or remove JSON-LD schema blocks for any page. Schemas are stored in the `_ps_page_schemas` post meta field and rendered in the document `<head>` via a `wp_head` hook. This approach bypasses Bricks Builder entirely and works reliably with any theme or page builder.

Each schema is identified by a unique key (e.g., "service", "localbusiness") so individual schemas can be added, replaced, or removed without affecting others on the same page.

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

1. Schema data is stored as a serialized array in `_ps_page_schemas` post meta
2. On every page load, the `wp_head` hook checks for stored schemas
3. Each schema is output as a `<script type="application/ld+json">` block in the `<head>`
4. The `@context` field is auto-added if not provided

**Example: Add Service schema to a page**

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
    "areaServed": {
      "@type": "GeoCircle",
      "geoMidpoint": {
        "@type": "GeoCoordinates",
        "latitude": 38.6631,
        "longitude": -90.6879
      },
      "geoRadius": "80467"
    },
    "serviceType": "Precision Milling",
    "url": "https://wescoworks.com/capabilities/milling/"
  }
}
```

**Example: List schemas for a page**

```json
{
  "post_id": 20,
  "action": "list"
}
```

**Example: Remove a schema**

```json
{
  "post_id": 20,
  "action": "remove",
  "key": "service"
}
```

---

## Safety Features

- **Bricks backup on write:** Every Bricks content modification saves a timestamped backup
- **Dry run mode:** `update-bricks-content` supports `dry_run: true` for previewing changes
- **Cache clearing:** Bricks render cache and WP object cache are cleared after writes
- **Input sanitization:** SEO meta values are passed through `sanitize_text_field`
- **Schema keys:** `sanitize_key` is applied to all schema keys

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

### 2.1
- **Replaced** `add-bricks-element` with `manage-page-schema` — Bricks strips code elements added outside its editor, so schema is now stored in its own post meta field and rendered via `wp_head`
- Added `wp_head` hook (`ps_render_page_schema`) that outputs JSON-LD from `_ps_page_schemas` post meta
- Schema management supports add, remove, list, and clear actions with unique keys per schema
- Removed `add-bricks-element` ability (Bricks Builder overwrites elements added via `update_post_meta`)

### 2.0
- Added Bricks read/write abilities and `update-seo-meta`
- Extracted shared helpers for Bricks content management

### 1.0
- Initial release with read-only abilities

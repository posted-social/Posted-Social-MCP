# Posted Social MCP Abilities

**Version:** 2.3  
**Author:** Posted Social  
**Requires:** WordPress with WP Abilities API

Exposes WordPress site content, SEO data, structure, Bricks Builder content, and media library management to AI assistants via the Model Context Protocol (MCP).

---

## Overview

This plugin registers a set of MCP abilities that allow AI assistants (like Claude) to read and update your WordPress site directly — auditing SEO, updating meta, managing schema, analyzing content structure, and now writing image alt text by visually inspecting each image.

All abilities are registered under the `postedsocial` category.

---

## Abilities

### 1. `postedsocial/get-content`
Returns published pages and posts with full content, SEO meta (Rank Math), URL, date, and word count.

**Input**
| Parameter | Type | Default | Description |
|---|---|---|---|
| `post_type` | string | `all` | `post`, `page`, or `all` |
| `per_page` | integer | `50` | Number of items to return |
| `search` | string | `""` | Optional keyword filter |

**Output:** `{ items: [], total: int }`

---

### 2. `postedsocial/seo-audit`
Returns SEO meta for all published pages/posts with issue flags. Flags include: `missing_seo_title`, `missing_meta_description`, `missing_focus_keyword`, `thin_content`, `meta_description_too_long`, `seo_title_too_long`.

**Input**
| Parameter | Type | Default | Description |
|---|---|---|---|
| `post_type` | string | `all` | `post`, `page`, or `all` |
| `per_page` | integer | `100` | Number of items to return |

**Output:** `{ items: [], total: int }`

---

### 3. `postedsocial/site-structure`
Returns the full page hierarchy with parent/child relationships, menu order, depth, and page template.

**Input:** None

**Output:** `{ pages: [], total: int }`

---

### 4. `postedsocial/internal-links`
Analyzes internal linking for a specific page or all pages. Returns each page's outbound internal links with anchor text.

**Input**
| Parameter | Type | Default | Description |
|---|---|---|---|
| `post_id` | integer | `0` | Specific post/page ID, or `0` for all |
| `per_page` | integer | `50` | Number of pages to analyze |

**Output:** `{ items: [] }`

---

### 5. `postedsocial/plugins-status`
Returns all installed plugins with version, active status, and whether an update is available.

**Input:** None

**Output:** `{ plugins: [], total: int }`

---

### 6. `postedsocial/gravity-forms`
Returns Gravity Forms list or recent entries for a specific form.

**Input**
| Parameter | Type | Default | Description |
|---|---|---|---|
| `form_id` | integer | `0` | Form ID for entries, or `0` for all forms |
| `per_page` | integer | `20` | Number of entries to return |

**Output:** `{ forms: [] }` or `{ form: string, entries: [] }`

---

### 7. `postedsocial/get-bricks-content`
Returns Bricks Builder elements with IDs, types, parent relationships, and settings for a specific page.

**Input**
| Parameter | Type | Default | Description |
|---|---|---|---|
| `post_id` | integer | — | Page/post ID. **Required.** |
| `element_types` | array | `[]` | Filter by element type. Empty returns all. |
| `include_raw` | boolean | `false` | Include full raw settings object |

**Output:** `{ success: bool, post_id: int, builder: string, elements: [], total: int }`

---

### 8. `postedsocial/update-seo-meta`
Updates Rank Math SEO meta fields for a page or post: title, description, focus keyword, robots, canonical URL, and schema type.

**Input**
| Parameter | Type | Description |
|---|---|---|
| `post_id` | integer | Page/post ID. **Required.** |
| `seo_title` | string | Rank Math SEO title |
| `seo_description` | string | Rank Math meta description |
| `focus_keyword` | string | Rank Math focus keyword |
| `robots` | array | Robots directives e.g. `["index", "follow"]` |
| `canonical` | string | Canonical URL override |
| `schema_type` | string | Rank Math rich snippet type |

**Output:** `{ success: bool, post_id: int, updated: [], message: string }`

---

### 9. `postedsocial/update-bricks-content`
Updates existing Bricks Builder elements by element ID. Merges new settings into existing settings and creates a timestamped backup before writing.

**Input**
| Parameter | Type | Default | Description |
|---|---|---|---|
| `post_id` | integer | — | Page/post ID. **Required.** |
| `updates` | array | — | Array of `{ element_id, settings }`. **Required.** |
| `dry_run` | boolean | `false` | Preview changes without writing |

**Output:** `{ success: bool, post_id: int, dry_run: bool, changes: [], elements_updated: int, backup_key: string }`

---

### 10. `postedsocial/manage-page-schema`
Add, list, or remove JSON-LD schema blocks rendered in `<head>` via `wp_head`. Schemas are also editable in the WP admin page editor via the Page Schemas meta box.

**Input**
| Parameter | Type | Default | Description |
|---|---|---|---|
| `post_id` | integer | — | Page/post ID. **Required.** |
| `action` | string | `list` | `add`, `remove`, `list`, or `clear` |
| `key` | string | `""` | Unique schema key. Required for `add`/`remove`. |
| `data` | object | — | Schema.org JSON-LD object. Required for `add`. Must include `@type`. |

**Output:** `{ success: bool, post_id: int, action: string, schemas: [], total: int }`

---

### 11. `postedsocial/get-images-missing-alt`
Returns all media library images with empty or missing alt text (`_wp_attachment_image_alt`). Returns the public URL for each image so an AI assistant can visually inspect it before writing alt text.

**Input**
| Parameter | Type | Default | Description |
|---|---|---|---|
| `per_page` | integer | `100` | Max number of images to return |

**Output:** `{ images: [], total: int }`

Each image item includes: `id`, `title`, `filename`, `url`, `alt`.

---

### 12. `postedsocial/update-image-alt`
Batch-updates alt text for one or more media library images by attachment ID. Writes to `_wp_attachment_image_alt`. Returns per-image status.

**Input**
| Parameter | Type | Description |
|---|---|---|
| `updates` | array | Array of `{ id, alt }` pairs. **Required.** |

**Output:** `{ success: bool, updated: int, items: [] }`

---

## Recommended Workflow: Image Alt Text

The two image alt abilities are designed to work together with an AI assistant that can visually inspect images:

1. Call `get-images-missing-alt` → returns up to 100 image URLs
2. AI fetches and views each image URL
3. AI writes contextually accurate alt text based on what it sees
4. Call `update-image-alt` with a batch of `{ id, alt }` pairs

> **Note:** In the claude.ai browser interface, `web_fetch` cannot load URLs that originate from tool results due to sandbox restrictions. Run this workflow via the Anthropic API or Claude Code for full image inspection capability.

**Decorative images** (SVGs used as shapes, arrows, icons, UI elements) should have alt text set to `""` — this tells screen readers to skip them, which is correct accessibility behavior.

---

## Admin UI

The plugin adds a **Page Schemas (JSON-LD)** meta box to posts and pages in the WordPress admin. This provides a GUI for managing JSON-LD schema blocks without using the MCP connector, including JSON validation and formatting tools.

---

## Changelog

### 2.3
- Added `postedsocial/get-images-missing-alt` — scan media library for images missing alt text
- Added `postedsocial/update-image-alt` — batch-update image alt text by attachment ID

### 2.2
- Added `postedsocial/manage-page-schema` — add/remove/list JSON-LD schema blocks per page
- Added Page Schemas admin meta box with JSON validation UI

### 2.1
- Added `postedsocial/update-bricks-content` with backup support
- Added `postedsocial/get-bricks-content`

### 2.0
- Added `postedsocial/update-seo-meta` for Rank Math integration
- Added `postedsocial/gravity-forms`

### 1.0
- Initial release: `get-content`, `seo-audit`, `site-structure`, `internal-links`, `plugins-status`

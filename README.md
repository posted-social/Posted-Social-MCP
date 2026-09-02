# Posted Social MCP Abilities

**Version:** 2.8  
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

### 13. `postedsocial/create-post`
Creates a new WordPress post or page. Can set taxonomies, sideload a featured image from an external URL, and write Rank Math SEO meta in the same call.

**Input**
| Parameter | Type | Default | Description |
|---|---|---|---|
| `title` | string | — | Post title. **Required.** |
| `content` | string | — | HTML body content. **Required.** |
| `post_type` | string | `post` | `post` or `page` |
| `status` | string | `draft` | `draft`, `pending`, `publish`, or `private` |
| `slug` | string | auto | URL slug. Generated from the title if omitted |
| `excerpt` | string | `""` | Manual excerpt |
| `categories` | array | `[]` | Category names or slugs. Auto-created if missing. Posts only |
| `tags` | array | `[]` | Tag names. Auto-created if missing. Posts only |
| `author_id` | integer | current user | WP user ID for the author |
| `featured_image_url` | string | `""` | External URL to sideload as the featured image |
| `meta` | object | — | Rank Math meta: `seo_title`, `seo_description`, `focus_keyword`, `canonical`, `schema_type`, `robots` |

**Output:** `{ success: bool, post_id: int, edit_url: string, view_url: string, status: string, slug: string, post_type: string, featured_image_id: int, meta_updated: [] }`

Categories and tags are ignored for `post_type: page`. A failed featured-image sideload does not fail the call — the post is still created and `featured_image_id` stays `0`.

---

### 14. `postedsocial/update-post`
Updates an existing post or page. **Only the fields you pass are changed** — anything omitted is left exactly as it was, so a partial update is safe.

**Input**
| Parameter | Type | Description |
|---|---|---|
| `post_id` | integer | ID of the post or page to update. **Required.** |
| `title` | string | New title. Cannot be set to `""` |
| `content` | string | New HTML body. Replaces existing content entirely. Cannot be set to `""` |
| `status` | string | `draft`, `pending`, `publish`, or `private` |
| `slug` | string | New URL slug. Pass `""` to let WordPress regenerate it from the title (takes effect once the post is published) |
| `excerpt` | string | New excerpt. Pass `""` to clear it |
| `categories` | array | Replaces existing categories. Auto-created if missing. Pass `[]` to clear. Posts only |
| `tags` | array | Replaces existing tags. Auto-created if missing. Pass `[]` to clear. Posts only |
| `author_id` | integer | WP user ID for the author |
| `featured_image_url` | string | External URL to sideload, replacing any existing featured image |
| `meta` | object | Rank Math meta, same keys as `create-post` |

**Output:** `{ success: bool, post_id: int, updated: [], skipped: [], edit_url: string, view_url: string, status: string, slug: string, post_type: string, featured_image_id: int, meta_updated: [], message: string }`

**Semantics worth knowing**

- **Omitted vs. empty.** Omitting a key leaves the field untouched. Passing `""` clears `excerpt`, and hands `slug` back to WordPress to regenerate from the title. `title` and `content` reject `""` outright rather than silently blanking the post — omit them instead.
- **`content` replaces, it does not append.** Read the current body with `get-content` first if you intend to extend it.
- **`categories` / `tags` replace the whole set** rather than adding to it. Pass the full intended list.
- **`updated` vs. `skipped`.** `updated` lists the fields that actually changed. `skipped` explains anything that was requested but not applied — categories/tags on a page, or a featured-image sideload that failed — so a partial success is never silent.
- **`meta` only writes non-empty values,** matching `update-seo-meta`. It cannot clear an existing SEO field.
- **Posts and pages only.** Any other post type is rejected with an error rather than partially updated.
- **Bricks pages.** This ability writes `post_content`. Pages built with Bricks store their content in `_bricks_page_content_2` and are unaffected by it — use `update-bricks-content` for those.

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

### 2.8
- Added `postedsocial/update-post` — partial updates to an existing post or page, with `updated`/`skipped` reporting
- Added `featured_image_id` to the documented `create-post` output (it was already being returned)

### 2.7
- Added `postedsocial/create-post` — create posts and pages with taxonomies, featured image sideload, and Rank Math meta in one call

### 2.4 – 2.6
- Hardened `postedsocial/update-bricks-content` writes: verify the write landed by reading `wp_postmeta` directly, fall back to a direct `$wpdb` write when a filter silently rejects `update_post_meta`, and invalidate persistent object caches (Kinsta Redis, etc.)
- `ps_get_bricks_elements()` now returns an empty array instead of a malformed value when the stored meta will not decode

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

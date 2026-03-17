<?php
/**
 * Plugin Name: Posted Social MCP Abilities
 * Description: Exposes site content, SEO data, structure, and Bricks Builder content to AI via MCP.
 * Version: 1.2
 */

// ─── Category ───────────────────────────────────────────────────────────────

add_action( 'wp_abilities_api_categories_init', 'ps_register_ability_category' );

function ps_register_ability_category() {
    wp_register_ability_category(
        'postedsocial',
        array(
            'label'       => 'Posted Social',
            'description' => 'Abilities for Posted Social site analysis and content management.',
        )
    );
}

// ─── Abilities ──────────────────────────────────────────────────────────────

add_action( 'wp_abilities_api_init', 'ps_register_abilities' );

function ps_register_abilities() {

    // 1. Get Pages/Posts with full content + SEO meta
    wp_register_ability(
        'postedsocial/get-content',
        array(
            'category'            => 'postedsocial',
            'label'               => 'Get Site Content',
            'description'         => 'Returns published pages and posts with full content, SEO meta (Rank Math), URL, date, and word count.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'post_type' => array(
                        'type'        => 'string',
                        'description' => 'Post type to retrieve: post, page, or all. Default all.',
                        'default'     => 'all',
                    ),
                    'per_page'  => array(
                        'type'        => 'integer',
                        'description' => 'Number of items to return. Default 50.',
                        'default'     => 50,
                    ),
                    'search'    => array(
                        'type'        => 'string',
                        'description' => 'Optional search keyword to filter results.',
                        'default'     => '',
                    ),
                ),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'items' => array( 'type' => 'array' ),
                    'total' => array( 'type' => 'integer' ),
                ),
            ),
            'permission_callback' => '__return_true',
            'execute_callback'    => 'ps_get_content_execute',
            'meta'                => array(
                'mcp' => array( 'public' => true ),
            ),
        )
    );

    // 2. Get SEO overview for all pages
    wp_register_ability(
        'postedsocial/seo-audit',
        array(
            'category'            => 'postedsocial',
            'label'               => 'SEO Audit',
            'description'         => 'Returns SEO meta for all published pages/posts: title tag, meta description, focus keyword, robots, canonical, and schema type from Rank Math.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'post_type' => array(
                        'type'        => 'string',
                        'description' => 'Post type: post, page, or all. Default all.',
                        'default'     => 'all',
                    ),
                    'per_page'  => array(
                        'type'        => 'integer',
                        'description' => 'Number of items. Default 100.',
                        'default'     => 100,
                    ),
                ),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'items' => array( 'type' => 'array' ),
                    'total' => array( 'type' => 'integer' ),
                ),
            ),
            'permission_callback' => '__return_true',
            'execute_callback'    => 'ps_seo_audit_execute',
            'meta'                => array(
                'mcp' => array( 'public' => true ),
            ),
        )
    );

    // 3. Get site structure / page hierarchy
    wp_register_ability(
        'postedsocial/site-structure',
        array(
            'category'            => 'postedsocial',
            'label'               => 'Site Structure',
            'description'         => 'Returns the page hierarchy showing parent/child relationships, URLs, and menu order.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'pages' => array( 'type' => 'array' ),
                    'total' => array( 'type' => 'integer' ),
                ),
            ),
            'permission_callback' => '__return_true',
            'execute_callback'    => 'ps_site_structure_execute',
            'meta'                => array(
                'mcp' => array( 'public' => true ),
            ),
        )
    );

    // 4. Get internal links for a specific page
    wp_register_ability(
        'postedsocial/internal-links',
        array(
            'category'            => 'postedsocial',
            'label'               => 'Internal Links',
            'description'         => 'Analyzes internal linking for a specific page or all pages. Returns outbound internal links found in content.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'post_id'  => array(
                        'type'        => 'integer',
                        'description' => 'Specific post/page ID to analyze. Leave empty for all.',
                        'default'     => 0,
                    ),
                    'per_page' => array(
                        'type'        => 'integer',
                        'description' => 'Number of pages to analyze if no post_id. Default 50.',
                        'default'     => 50,
                    ),
                ),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'items' => array( 'type' => 'array' ),
                ),
            ),
            'permission_callback' => '__return_true',
            'execute_callback'    => 'ps_internal_links_execute',
            'meta'                => array(
                'mcp' => array( 'public' => true ),
            ),
        )
    );

    // 5. Get plugin list with status and versions
    wp_register_ability(
        'postedsocial/plugins-status',
        array(
            'category'            => 'postedsocial',
            'label'               => 'Plugins Status',
            'description'         => 'Returns list of all installed plugins with name, version, active status, and whether updates are available.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'plugins' => array( 'type' => 'array' ),
                    'total'   => array( 'type' => 'integer' ),
                ),
            ),
            'permission_callback' => '__return_true',
            'execute_callback'    => 'ps_plugins_status_execute',
            'meta'                => array(
                'mcp' => array( 'public' => true ),
            ),
        )
    );

    // 6. Get Gravity Forms list and recent entries
    wp_register_ability(
        'postedsocial/gravity-forms',
        array(
            'category'            => 'postedsocial',
            'label'               => 'Gravity Forms Data',
            'description'         => 'Returns list of Gravity Forms with entry counts, or recent entries for a specific form.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'form_id'  => array(
                        'type'        => 'integer',
                        'description' => 'Specific form ID to get entries for. Leave empty to list all forms.',
                        'default'     => 0,
                    ),
                    'per_page' => array(
                        'type'        => 'integer',
                        'description' => 'Number of entries to return. Default 20.',
                        'default'     => 20,
                    ),
                ),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'forms'   => array( 'type' => 'array' ),
                    'entries' => array( 'type' => 'array' ),
                ),
            ),
            'permission_callback' => '__return_true',
            'execute_callback'    => 'ps_gravity_forms_execute',
            'meta'                => array(
                'mcp' => array( 'public' => true ),
            ),
        )
    );

    // 7. Update SEO meta (Rank Math)
    wp_register_ability(
        'postedsocial/update-seo-meta',
        array(
            'category'            => 'postedsocial',
            'label'               => 'Update SEO Meta',
            'description'         => 'Updates Rank Math SEO meta for a specific page/post: focus keyword, title, description, robots, canonical, and schema type.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'post_id'         => array(
                        'type'        => 'integer',
                        'description' => 'The post/page ID to update. Required.',
                    ),
                    'focus_keyword'   => array(
                        'type'        => 'string',
                        'description' => 'Primary focus keyword for Rank Math.',
                        'default'     => '',
                    ),
                    'seo_title'       => array(
                        'type'        => 'string',
                        'description' => 'SEO title tag. Leave empty to skip.',
                        'default'     => '',
                    ),
                    'seo_description' => array(
                        'type'        => 'string',
                        'description' => 'Meta description. Leave empty to skip.',
                        'default'     => '',
                    ),
                    'robots'          => array(
                        'type'        => 'array',
                        'description' => 'Robots directives array, e.g. ["noindex"]. Leave empty to skip.',
                        'default'     => array(),
                    ),
                    'canonical'       => array(
                        'type'        => 'string',
                        'description' => 'Canonical URL. Leave empty to skip.',
                        'default'     => '',
                    ),
                    'schema_type'     => array(
                        'type'        => 'string',
                        'description' => 'Rank Math rich snippet type, e.g. "Article", "LocalBusiness", "Service". Leave empty to skip.',
                        'default'     => '',
                    ),
                ),
                'required'   => array( 'post_id' ),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'success' => array( 'type' => 'boolean' ),
                    'updated' => array( 'type' => 'array' ),
                    'post_id' => array( 'type' => 'integer' ),
                ),
            ),
            'permission_callback' => '__return_true',
            'execute_callback'    => 'ps_update_seo_meta_execute',
            'meta'                => array(
                'mcp' => array( 'public' => true ),
            ),
        )
    );

    // 8. Get Bricks Builder content
    wp_register_ability(
        'postedsocial/get-bricks-content',
        array(
            'category'            => 'postedsocial',
            'label'               => 'Get Bricks Content',
            'description'         => 'Returns Bricks Builder elements for a page with element IDs, types, parent relationships, and settings (text, links, tags). Use to inspect page structure before making edits.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'post_id'       => array(
                        'type'        => 'integer',
                        'description' => 'The post/page ID to retrieve Bricks content for. Required.',
                    ),
                    'element_types' => array(
                        'type'        => 'array',
                        'description' => 'Optional filter: only return elements of these types, e.g. ["heading", "text-basic", "button"]. Leave empty for all.',
                        'default'     => array(),
                    ),
                    'include_raw'   => array(
                        'type'        => 'boolean',
                        'description' => 'If true, includes the full raw settings object for each element. Default false returns only common fields.',
                        'default'     => false,
                    ),
                ),
                'required'   => array( 'post_id' ),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'success'  => array( 'type' => 'boolean' ),
                    'post_id'  => array( 'type' => 'integer' ),
                    'builder'  => array( 'type' => 'string' ),
                    'elements' => array( 'type' => 'array' ),
                    'total'    => array( 'type' => 'integer' ),
                ),
            ),
            'permission_callback' => '__return_true',
            'execute_callback'    => 'ps_get_bricks_content_execute',
            'meta'                => array(
                'mcp' => array( 'public' => true ),
            ),
        )
    );

    // 9. Update Bricks Builder content
    wp_register_ability(
        'postedsocial/update-bricks-content',
        array(
            'category'            => 'postedsocial',
            'label'               => 'Update Bricks Content',
            'description'         => 'Updates specific Bricks Builder elements by element ID. Can modify text, links, tags, and other settings. Backs up existing content before writing and clears render cache after.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'post_id'  => array(
                        'type'        => 'integer',
                        'description' => 'The post/page ID to update. Required.',
                    ),
                    'updates'  => array(
                        'type'        => 'array',
                        'description' => 'Array of element updates. Each item must have "element_id" (string) and "settings" (object) with the fields to update. Settings are merged — unspecified fields are preserved.',
                        'items'       => array(
                            'type'       => 'object',
                            'properties' => array(
                                'element_id' => array(
                                    'type'        => 'string',
                                    'description' => 'The Bricks element ID to update (e.g. "abcdef").',
                                ),
                                'settings'   => array(
                                    'type'        => 'object',
                                    'description' => 'Settings fields to update. Common fields: "text" (string, supports HTML), "tag" (string, e.g. "h1", "h2", "p"), "link" (object with "url", "type", "newTab" etc). Only specified fields are changed; all others are preserved.',
                                ),
                            ),
                            'required' => array( 'element_id', 'settings' ),
                        ),
                    ),
                    'dry_run'  => array(
                        'type'        => 'boolean',
                        'description' => 'If true, returns what would change without saving. Default false.',
                        'default'     => false,
                    ),
                ),
                'required'   => array( 'post_id', 'updates' ),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'success'          => array( 'type' => 'boolean' ),
                    'post_id'          => array( 'type' => 'integer' ),
                    'dry_run'          => array( 'type' => 'boolean' ),
                    'changes'          => array( 'type' => 'array' ),
                    'elements_updated' => array( 'type' => 'integer' ),
                ),
            ),
            'permission_callback' => '__return_true',
            'execute_callback'    => 'ps_update_bricks_content_execute',
            'meta'                => array(
                'mcp' => array( 'public' => true ),
            ),
        )
    );
}

// ─── Callbacks ──────────────────────────────────────────────────────────────

/**
 * 1. Get content with full body + SEO meta.
 */
function ps_get_content_execute( $input ) {
    $post_type = isset( $input['post_type'] ) ? $input['post_type'] : 'all';
    $per_page  = isset( $input['per_page'] ) ? intval( $input['per_page'] ) : 50;
    $search    = isset( $input['search'] ) ? sanitize_text_field( $input['search'] ) : '';

    $args = array(
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_type'      => 'all' === $post_type ? array( 'post', 'page' ) : $post_type,
    );

    if ( ! empty( $search ) ) {
        $args['s'] = $search;
    }

    $query = new WP_Query( $args );
    $items = array();

    foreach ( $query->posts as $post ) {
        $content = wp_strip_all_tags( $post->post_content );

        $item = array(
            'id'               => $post->ID,
            'title'            => $post->post_title,
            'url'              => get_permalink( $post->ID ),
            'post_type'        => $post->post_type,
            'date'             => get_the_date( 'Y-m-d', $post->ID ),
            'modified'         => get_the_modified_date( 'Y-m-d', $post->ID ),
            'word_count'       => str_word_count( $content ),
            'content'          => $content,
            'excerpt'          => has_excerpt( $post->ID )
                ? get_the_excerpt( $post )
                : wp_trim_words( $post->post_content, 40 ),
            'seo_title'        => get_post_meta( $post->ID, 'rank_math_title', true ),
            'seo_description'  => get_post_meta( $post->ID, 'rank_math_description', true ),
            'focus_keyword'    => get_post_meta( $post->ID, 'rank_math_focus_keyword', true ),
            'robots'           => get_post_meta( $post->ID, 'rank_math_robots', true ),
            'canonical'        => get_post_meta( $post->ID, 'rank_math_canonical_url', true ),
        );

        $items[] = $item;
    }

    return array(
        'items' => $items,
        'total' => $query->found_posts,
    );
}

/**
 * 2. SEO audit — focused view of meta across all content.
 */
function ps_seo_audit_execute( $input ) {
    $post_type = isset( $input['post_type'] ) ? $input['post_type'] : 'all';
    $per_page  = isset( $input['per_page'] ) ? intval( $input['per_page'] ) : 100;

    $query = new WP_Query( array(
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'post_type'      => 'all' === $post_type ? array( 'post', 'page' ) : $post_type,
    ) );

    $items = array();

    foreach ( $query->posts as $post ) {
        $content    = wp_strip_all_tags( $post->post_content );
        $seo_title  = get_post_meta( $post->ID, 'rank_math_title', true );
        $seo_desc   = get_post_meta( $post->ID, 'rank_math_description', true );
        $focus_kw   = get_post_meta( $post->ID, 'rank_math_focus_keyword', true );

        $issues = array();
        if ( empty( $seo_title ) ) {
            $issues[] = 'missing_seo_title';
        }
        if ( empty( $seo_desc ) ) {
            $issues[] = 'missing_meta_description';
        }
        if ( empty( $focus_kw ) ) {
            $issues[] = 'missing_focus_keyword';
        }
        if ( str_word_count( $content ) < 300 ) {
            $issues[] = 'thin_content';
        }
        if ( ! empty( $seo_desc ) && strlen( $seo_desc ) > 160 ) {
            $issues[] = 'meta_description_too_long';
        }
        if ( ! empty( $seo_title ) && strlen( $seo_title ) > 60 ) {
            $issues[] = 'seo_title_too_long';
        }

        $items[] = array(
            'id'              => $post->ID,
            'title'           => $post->post_title,
            'url'             => get_permalink( $post->ID ),
            'post_type'       => $post->post_type,
            'word_count'      => str_word_count( $content ),
            'seo_title'       => $seo_title,
            'seo_title_len'   => strlen( $seo_title ?: '' ),
            'seo_description' => $seo_desc,
            'seo_desc_len'    => strlen( $seo_desc ?: '' ),
            'focus_keyword'   => $focus_kw,
            'robots'          => get_post_meta( $post->ID, 'rank_math_robots', true ),
            'canonical'       => get_post_meta( $post->ID, 'rank_math_canonical_url', true ),
            'schema_type'     => get_post_meta( $post->ID, 'rank_math_rich_snippet', true ),
            'seo_score'       => get_post_meta( $post->ID, 'rank_math_seo_score', true ),
            'issues'          => $issues,
        );
    }

    return array(
        'items' => $items,
        'total' => $query->found_posts,
    );
}

/**
 * 3. Site structure — page hierarchy.
 */
function ps_site_structure_execute( $input ) {
    $pages = get_pages( array(
        'sort_column' => 'menu_order, post_title',
        'sort_order'  => 'ASC',
    ) );

    $items = array();

    foreach ( $pages as $page ) {
        $parent_title = '';
        if ( $page->post_parent ) {
            $parent = get_post( $page->post_parent );
            $parent_title = $parent ? $parent->post_title : '';
        }

        $items[] = array(
            'id'           => $page->ID,
            'title'        => $page->post_title,
            'url'          => get_permalink( $page->ID ),
            'slug'         => $page->post_name,
            'parent_id'    => $page->post_parent,
            'parent_title' => $parent_title,
            'menu_order'   => $page->menu_order,
            'depth'        => count( get_post_ancestors( $page->ID ) ),
            'template'     => get_page_template_slug( $page->ID ) ?: 'default',
        );
    }

    return array(
        'pages' => $items,
        'total' => count( $items ),
    );
}

/**
 * 4. Internal links analysis.
 */
function ps_internal_links_execute( $input ) {
    $post_id  = isset( $input['post_id'] ) ? intval( $input['post_id'] ) : 0;
    $per_page = isset( $input['per_page'] ) ? intval( $input['per_page'] ) : 50;

    $site_url = home_url();

    if ( $post_id > 0 ) {
        $posts = array( get_post( $post_id ) );
    } else {
        $query = new WP_Query( array(
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'post_type'      => array( 'post', 'page' ),
        ) );
        $posts = $query->posts;
    }

    $items = array();

    foreach ( $posts as $post ) {
        if ( ! $post ) {
            continue;
        }

        $content = $post->post_content;
        $links   = array();

        if ( preg_match_all( '/<a\s[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/si', $content, $matches ) ) {
            foreach ( $matches[1] as $i => $href ) {
                if ( strpos( $href, $site_url ) === 0 || strpos( $href, '/' ) === 0 ) {
                    $links[] = array(
                        'url'         => $href,
                        'anchor_text' => wp_strip_all_tags( $matches[2][ $i ] ),
                    );
                }
            }
        }

        $items[] = array(
            'id'             => $post->ID,
            'title'          => $post->post_title,
            'url'            => get_permalink( $post->ID ),
            'internal_links' => $links,
            'link_count'     => count( $links ),
        );
    }

    return array(
        'items' => $items,
    );
}

/**
 * 5. Plugin status.
 */
function ps_plugins_status_execute( $input ) {
    if ( ! function_exists( 'get_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    if ( ! function_exists( 'get_plugin_updates' ) ) {
        require_once ABSPATH . 'wp-admin/includes/update.php';
    }

    $all_plugins    = get_plugins();
    $active_plugins = get_option( 'active_plugins', array() );
    $updates        = get_plugin_updates();
    $items          = array();

    foreach ( $all_plugins as $file => $data ) {
        $has_update    = isset( $updates[ $file ] );
        $update_version = $has_update ? $updates[ $file ]->update->new_version : '';

        $items[] = array(
            'file'             => $file,
            'name'             => $data['Name'],
            'version'          => $data['Version'],
            'active'           => in_array( $file, $active_plugins, true ),
            'update_available' => $has_update,
            'update_version'   => $update_version,
            'author'           => $data['AuthorName'],
        );
    }

    return array(
        'plugins' => $items,
        'total'   => count( $items ),
    );
}

/**
 * 6. Gravity Forms data.
 */
function ps_gravity_forms_execute( $input ) {
    if ( ! class_exists( 'GFAPI' ) ) {
        return array(
            'error' => 'Gravity Forms is not active on this site.',
        );
    }

    $form_id  = isset( $input['form_id'] ) ? intval( $input['form_id'] ) : 0;
    $per_page = isset( $input['per_page'] ) ? intval( $input['per_page'] ) : 20;

    // List all forms.
    if ( 0 === $form_id ) {
        $forms = GFAPI::get_forms();
        $items = array();

        foreach ( $forms as $form ) {
            $count = GFAPI::count_entries( $form['id'] );
            $items[] = array(
                'id'          => $form['id'],
                'title'       => $form['title'],
                'entry_count' => $count,
                'is_active'   => (bool) $form['is_active'],
            );
        }

        return array(
            'forms' => $items,
        );
    }

    // Get entries for a specific form.
    $entries = GFAPI::get_entries(
        $form_id,
        array( 'status' => 'active' ),
        array( 'key' => 'date_created', 'direction' => 'DESC' ),
        array( 'offset' => 0, 'page_size' => $per_page )
    );

    $form   = GFAPI::get_form( $form_id );
    $fields = array();
    if ( $form && isset( $form['fields'] ) ) {
        foreach ( $form['fields'] as $field ) {
            $fields[ $field->id ] = $field->label;
        }
    }

    $items = array();
    foreach ( $entries as $entry ) {
        $entry_data = array(
            'entry_id'     => $entry['id'],
            'date_created' => $entry['date_created'],
            'source_url'   => $entry['source_url'],
            'fields'       => array(),
        );

        foreach ( $fields as $field_id => $label ) {
            $value = rgar( $entry, $field_id );
            if ( ! empty( $value ) ) {
                $entry_data['fields'][ $label ] = $value;
            }
        }

        $items[] = $entry_data;
    }

    return array(
        'form'    => $form['title'],
        'entries' => $items,
    );
}

/**
 * 7. Update Rank Math SEO meta for a page/post.
 */
function ps_update_seo_meta_execute( $input ) {
    $post_id = isset( $input['post_id'] ) ? intval( $input['post_id'] ) : 0;

    if ( ! $post_id || ! get_post( $post_id ) ) {
        return array(
            'success' => false,
            'error'   => 'Invalid post ID.',
        );
    }

    $fields_map = array(
        'focus_keyword'   => 'rank_math_focus_keyword',
        'seo_title'       => 'rank_math_title',
        'seo_description' => 'rank_math_description',
        'canonical'       => 'rank_math_canonical_url',
        'schema_type'     => 'rank_math_rich_snippet',
    );

    $updated = array();

    foreach ( $fields_map as $input_key => $meta_key ) {
        if ( ! empty( $input[ $input_key ] ) ) {
            $value = sanitize_text_field( $input[ $input_key ] );
            update_post_meta( $post_id, $meta_key, $value );
            $updated[] = $input_key;
        }
    }

    // Handle robots as array.
    if ( ! empty( $input['robots'] ) && is_array( $input['robots'] ) ) {
        $robots = array_map( 'sanitize_text_field', $input['robots'] );
        update_post_meta( $post_id, 'rank_math_robots', $robots );
        $updated[] = 'robots';
    }

    return array(
        'success' => true,
        'post_id' => $post_id,
        'updated' => $updated,
        'message' => sprintf( 'Updated %d field(s) for post %d.', count( $updated ), $post_id ),
    );
}

/**
 * 8. Get Bricks Builder content for a page.
 */
function ps_get_bricks_content_execute( $input ) {
    $post_id       = isset( $input['post_id'] ) ? intval( $input['post_id'] ) : 0;
    $element_types = isset( $input['element_types'] ) ? (array) $input['element_types'] : array();
    $include_raw   = isset( $input['include_raw'] ) ? (bool) $input['include_raw'] : false;

    if ( ! $post_id || ! get_post( $post_id ) ) {
        return array(
            'success' => false,
            'error'   => 'Invalid post ID.',
        );
    }

    // Bricks stores content in _bricks_page_content_2.
    $bricks_meta = get_post_meta( $post_id, '_bricks_page_content_2', true );

    if ( empty( $bricks_meta ) ) {
        return array(
            'success'  => true,
            'post_id'  => $post_id,
            'builder'  => 'none',
            'elements' => array(),
            'total'    => 0,
            'message'  => 'No Bricks content found. Page may use a different builder or the classic editor.',
        );
    }

    // Bricks meta can be a JSON string or already an array.
    $elements = is_string( $bricks_meta ) ? json_decode( $bricks_meta, true ) : $bricks_meta;

    if ( ! is_array( $elements ) ) {
        return array(
            'success' => false,
            'error'   => 'Could not parse Bricks content data.',
        );
    }

    $items = array();

    foreach ( $elements as $element ) {
        $name = isset( $element['name'] ) ? $element['name'] : '';

        // Filter by element type if specified.
        if ( ! empty( $element_types ) && ! in_array( $name, $element_types, true ) ) {
            continue;
        }

        $settings = isset( $element['settings'] ) ? $element['settings'] : array();

        // Build a clean element representation.
        $item = array(
            'element_id' => isset( $element['id'] ) ? $element['id'] : '',
            'name'       => $name,
            'parent'     => isset( $element['parent'] ) ? $element['parent'] : 0,
            'label'      => isset( $element['label'] ) ? $element['label'] : '',
        );

        // Extract common content fields from settings.
        $common_fields = array( 'text', 'tag', 'link', 'title', 'content', 'label', 'placeholder', 'icon', '_cssClasses' );
        foreach ( $common_fields as $field ) {
            if ( isset( $settings[ $field ] ) ) {
                $item[ $field ] = $settings[ $field ];
            }
        }

        // Include full raw settings if requested.
        if ( $include_raw ) {
            $item['raw_settings'] = $settings;
        }

        $items[] = $item;
    }

    return array(
        'success'  => true,
        'post_id'  => $post_id,
        'builder'  => 'bricks',
        'elements' => $items,
        'total'    => count( $items ),
    );
}

/**
 * 9. Update Bricks Builder content for specific elements.
 */
function ps_update_bricks_content_execute( $input ) {
    $post_id = isset( $input['post_id'] ) ? intval( $input['post_id'] ) : 0;
    $updates = isset( $input['updates'] ) ? (array) $input['updates'] : array();
    $dry_run = isset( $input['dry_run'] ) ? (bool) $input['dry_run'] : false;

    if ( ! $post_id || ! get_post( $post_id ) ) {
        return array(
            'success' => false,
            'error'   => 'Invalid post ID.',
        );
    }

    if ( empty( $updates ) ) {
        return array(
            'success' => false,
            'error'   => 'No updates provided.',
        );
    }

    // Read existing Bricks content.
    $bricks_meta = get_post_meta( $post_id, '_bricks_page_content_2', true );

    if ( empty( $bricks_meta ) ) {
        return array(
            'success' => false,
            'error'   => 'No Bricks content found for this page.',
        );
    }

    $elements = is_string( $bricks_meta ) ? json_decode( $bricks_meta, true ) : $bricks_meta;

    if ( ! is_array( $elements ) ) {
        return array(
            'success' => false,
            'error'   => 'Could not parse Bricks content data.',
        );
    }

    // Build a lookup of element IDs to their index in the array.
    $id_to_index = array();
    foreach ( $elements as $index => $element ) {
        if ( isset( $element['id'] ) ) {
            $id_to_index[ $element['id'] ] = $index;
        }
    }

    $changes          = array();
    $not_found        = array();
    $elements_updated = 0;

    foreach ( $updates as $update ) {
        $element_id   = isset( $update['element_id'] ) ? $update['element_id'] : '';
        $new_settings = isset( $update['settings'] ) ? (array) $update['settings'] : array();

        if ( empty( $element_id ) || empty( $new_settings ) ) {
            continue;
        }

        if ( ! isset( $id_to_index[ $element_id ] ) ) {
            $not_found[] = $element_id;
            continue;
        }

        $index            = $id_to_index[ $element_id ];
        $current_settings = isset( $elements[ $index ]['settings'] ) ? $elements[ $index ]['settings'] : array();
        $element_name     = isset( $elements[ $index ]['name'] ) ? $elements[ $index ]['name'] : '';

        // Build change log.
        $change = array(
            'element_id' => $element_id,
            'name'       => $element_name,
            'fields'     => array(),
        );

        foreach ( $new_settings as $key => $new_value ) {
            $old_value = isset( $current_settings[ $key ] ) ? $current_settings[ $key ] : null;

            $change['fields'][] = array(
                'field' => $key,
                'old'   => $old_value,
                'new'   => $new_value,
            );

            // Merge the setting (deep merge for arrays like 'link').
            if ( is_array( $new_value ) && is_array( $old_value ) ) {
                $elements[ $index ]['settings'][ $key ] = array_merge( $old_value, $new_value );
            } else {
                $elements[ $index ]['settings'][ $key ] = $new_value;
            }
        }

        $changes[] = $change;
        $elements_updated++;
    }

    // Dry run — return what would change without saving.
    if ( $dry_run ) {
        return array(
            'success'          => true,
            'post_id'          => $post_id,
            'dry_run'          => true,
            'changes'          => $changes,
            'not_found'        => $not_found,
            'elements_updated' => $elements_updated,
            'message'          => sprintf( 'Dry run: %d element(s) would be updated.', $elements_updated ),
        );
    }

    // Backup existing content before writing.
    $backup_key = '_bricks_page_content_2_backup_' . gmdate( 'Ymd_His' );
    update_post_meta( $post_id, $backup_key, $bricks_meta );

    // Save updated elements back to post meta.
    update_post_meta( $post_id, '_bricks_page_content_2', $elements );

    // Clear Bricks render cache for this page.
    delete_post_meta( $post_id, '_bricks_page_content_2_html' );
    delete_post_meta( $post_id, '_bricks_page_content_2_css' );

    // Also clear any object cache for this post.
    clean_post_cache( $post_id );

    return array(
        'success'          => true,
        'post_id'          => $post_id,
        'dry_run'          => false,
        'changes'          => $changes,
        'not_found'        => $not_found,
        'elements_updated' => $elements_updated,
        'backup_key'       => $backup_key,
        'message'          => sprintf( 'Updated %d element(s) for post %d. Backup saved as %s.', $elements_updated, $post_id, $backup_key ),
    );
}

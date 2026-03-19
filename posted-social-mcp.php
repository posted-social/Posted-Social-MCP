<?php
/**
 * Plugin Name: Posted Social MCP Abilities
 * Description: Exposes site content, SEO data, structure, and Bricks Builder content to AI via MCP.
 * Version: 2.0
 * Author: Posted Social
 */

defined( 'ABSPATH' ) || exit;

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

// ─── Register All Abilities ─────────────────────────────────────────────────

add_action( 'wp_abilities_api_init', 'ps_register_abilities' );

function ps_register_abilities() {

    // ── READ ABILITIES ──────────────────────────────────────────────────

    // 1. Get content
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
            'meta'                => array( 'mcp' => array( 'public' => true ) ),
        )
    );

    // 2. SEO audit
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
            'meta'                => array( 'mcp' => array( 'public' => true ) ),
        )
    );

    // 3. Site structure
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
            'meta'                => array( 'mcp' => array( 'public' => true ) ),
        )
    );

    // 4. Internal links
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
            'meta'                => array( 'mcp' => array( 'public' => true ) ),
        )
    );

    // 5. Plugins status
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
            'meta'                => array( 'mcp' => array( 'public' => true ) ),
        )
    );

    // 6. Gravity Forms
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
            'meta'                => array( 'mcp' => array( 'public' => true ) ),
        )
    );

    // 7. Get Bricks content
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
                        'description' => 'Optional filter: only return elements of these types. Leave empty for all.',
                        'default'     => array(),
                    ),
                    'include_raw'   => array(
                        'type'        => 'boolean',
                        'description' => 'If true, includes the full raw settings object for each element. Default false.',
                        'default'     => false,
                    ),
                ),
                'required' => array( 'post_id' ),
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
            'meta'                => array( 'mcp' => array( 'public' => true ) ),
        )
    );

    // ── WRITE ABILITIES ─────────────────────────────────────────────────

    // 8. Update SEO meta
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
                'required' => array( 'post_id' ),
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
            'meta'                => array( 'mcp' => array( 'public' => true ) ),
        )
    );

    // 9. Update Bricks content
    wp_register_ability(
        'postedsocial/update-bricks-content',
        array(
            'category'            => 'postedsocial',
            'label'               => 'Update Bricks Content',
            'description'         => 'Updates specific Bricks Builder elements by element ID. Can modify text, links, tags, and other settings. Backs up existing content before writing and clears render cache after.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'post_id' => array(
                        'type'        => 'integer',
                        'description' => 'The post/page ID to update. Required.',
                    ),
                    'updates' => array(
                        'type'        => 'array',
                        'description' => 'Array of element updates. Each item needs "element_id" and "settings" to merge.',
                        'items'       => array(
                            'type'       => 'object',
                            'properties' => array(
                                'element_id' => array(
                                    'type'        => 'string',
                                    'description' => 'The Bricks element ID to update.',
                                ),
                                'settings'   => array(
                                    'type'        => 'object',
                                    'description' => 'Settings fields to update. Only specified fields are changed.',
                                ),
                            ),
                            'required' => array( 'element_id', 'settings' ),
                        ),
                    ),
                    'dry_run' => array(
                        'type'        => 'boolean',
                        'description' => 'If true, returns what would change without saving. Default false.',
                        'default'     => false,
                    ),
                ),
                'required' => array( 'post_id', 'updates' ),
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
            'meta'                => array( 'mcp' => array( 'public' => true ) ),
        )
    );

    // 10. Add Bricks element
    wp_register_ability(
        'postedsocial/add-bricks-element',
        array(
            'category'            => 'postedsocial',
            'label'               => 'Add Bricks Element',
            'description'         => 'Inserts a new Bricks Builder element into a page. Supports adding code elements (for JSON-LD schema, custom scripts), text, headings, and other element types. Backs up content before writing.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'post_id'  => array(
                        'type'        => 'integer',
                        'description' => 'The post/page ID to add the element to. Required.',
                    ),
                    'element'  => array(
                        'type'        => 'object',
                        'description' => 'The element to insert.',
                        'properties'  => array(
                            'name'     => array(
                                'type'        => 'string',
                                'description' => 'Bricks element type: "code", "text-basic", "heading", "html", "block", "container", "section". Required.',
                            ),
                            'parent'   => array(
                                'type'        => 'string',
                                'description' => 'Parent element ID to nest under. Use "0" or empty for top-level (root). Default "0".',
                                'default'     => '0',
                            ),
                            'label'    => array(
                                'type'        => 'string',
                                'description' => 'Optional admin label for the element in Bricks editor.',
                                'default'     => '',
                            ),
                            'settings' => array(
                                'type'        => 'object',
                                'description' => 'Element settings. For "code" elements use {"code": "<script>...</script>", "executeCode": true, "noRender": true}. For "text-basic" use {"text": "..."}. For "heading" use {"text": "...", "tag": "h2"}.',
                            ),
                        ),
                        'required' => array( 'name', 'settings' ),
                    ),
                    'position' => array(
                        'type'        => 'string',
                        'description' => 'Where to insert: "first" (beginning of parent), "last" (end of parent), "before:element_id", "after:element_id". Default "last".',
                        'default'     => 'last',
                    ),
                    'dry_run'  => array(
                        'type'        => 'boolean',
                        'description' => 'If true, returns what would be added without saving. Default false.',
                        'default'     => false,
                    ),
                ),
                'required' => array( 'post_id', 'element' ),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'success'    => array( 'type' => 'boolean' ),
                    'post_id'    => array( 'type' => 'integer' ),
                    'element_id' => array( 'type' => 'string' ),
                    'dry_run'    => array( 'type' => 'boolean' ),
                    'element'    => array( 'type' => 'object' ),
                    'position'   => array( 'type' => 'integer' ),
                ),
            ),
            'permission_callback' => '__return_true',
            'execute_callback'    => 'ps_add_bricks_element_execute',
            'meta'                => array( 'mcp' => array( 'public' => true ) ),
        )
    );
}

// ─── Helper: Generate Bricks Element ID ─────────────────────────────────────

function ps_generate_bricks_id( $length = 6 ) {
    $chars = 'abcdefghijklmnopqrstuvwxyz';
    $id    = '';
    for ( $i = 0; $i < $length; $i++ ) {
        $id .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
    }
    return $id;
}

// ─── Helper: Load and Parse Bricks Content ──────────────────────────────────

function ps_get_bricks_elements( $post_id ) {
    $meta = get_post_meta( $post_id, '_bricks_page_content_2', true );

    if ( empty( $meta ) ) {
        return array();
    }

    return is_string( $meta ) ? json_decode( $meta, true ) : (array) $meta;
}

// ─── Helper: Save Bricks Content with Backup ────────────────────────────────

function ps_save_bricks_elements( $post_id, $elements, $original_meta = null ) {
    // Backup.
    if ( null !== $original_meta ) {
        $backup_key = '_bricks_page_content_2_backup_' . gmdate( 'Ymd_His' );
        update_post_meta( $post_id, $backup_key, $original_meta );
    }

    // Save.
    update_post_meta( $post_id, '_bricks_page_content_2', $elements );

    // Clear Bricks render cache.
    delete_post_meta( $post_id, '_bricks_page_content_2_html' );
    delete_post_meta( $post_id, '_bricks_page_content_2_css' );
    clean_post_cache( $post_id );

    return isset( $backup_key ) ? $backup_key : '';
}

// ─── Callback: Get Content ──────────────────────────────────────────────────

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

        $items[] = array(
            'id'              => $post->ID,
            'title'           => $post->post_title,
            'url'             => get_permalink( $post->ID ),
            'post_type'       => $post->post_type,
            'date'            => get_the_date( 'Y-m-d', $post->ID ),
            'modified'        => get_the_modified_date( 'Y-m-d', $post->ID ),
            'word_count'      => str_word_count( $content ),
            'content'         => $content,
            'excerpt'         => has_excerpt( $post->ID )
                ? get_the_excerpt( $post )
                : wp_trim_words( $post->post_content, 40 ),
            'seo_title'       => get_post_meta( $post->ID, 'rank_math_title', true ),
            'seo_description' => get_post_meta( $post->ID, 'rank_math_description', true ),
            'focus_keyword'   => get_post_meta( $post->ID, 'rank_math_focus_keyword', true ),
            'robots'          => get_post_meta( $post->ID, 'rank_math_robots', true ),
            'canonical'       => get_post_meta( $post->ID, 'rank_math_canonical_url', true ),
        );
    }

    return array(
        'items' => $items,
        'total' => $query->found_posts,
    );
}

// ─── Callback: SEO Audit ────────────────────────────────────────────────────

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
        $content   = wp_strip_all_tags( $post->post_content );
        $seo_title = get_post_meta( $post->ID, 'rank_math_title', true );
        $seo_desc  = get_post_meta( $post->ID, 'rank_math_description', true );
        $focus_kw  = get_post_meta( $post->ID, 'rank_math_focus_keyword', true );

        $issues = array();
        if ( empty( $seo_title ) )                          $issues[] = 'missing_seo_title';
        if ( empty( $seo_desc ) )                           $issues[] = 'missing_meta_description';
        if ( empty( $focus_kw ) )                           $issues[] = 'missing_focus_keyword';
        if ( str_word_count( $content ) < 300 )             $issues[] = 'thin_content';
        if ( ! empty( $seo_desc ) && strlen( $seo_desc ) > 160 ) $issues[] = 'meta_description_too_long';
        if ( ! empty( $seo_title ) && strlen( $seo_title ) > 60 ) $issues[] = 'seo_title_too_long';

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

// ─── Callback: Site Structure ───────────────────────────────────────────────

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

// ─── Callback: Internal Links ───────────────────────────────────────────────

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

    return array( 'items' => $items );
}

// ─── Callback: Plugins Status ───────────────────────────────────────────────

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
        $has_update     = isset( $updates[ $file ] );
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

// ─── Callback: Gravity Forms ────────────────────────────────────────────────

function ps_gravity_forms_execute( $input ) {
    if ( ! class_exists( 'GFAPI' ) ) {
        return array( 'error' => 'Gravity Forms is not active on this site.' );
    }

    $form_id  = isset( $input['form_id'] ) ? intval( $input['form_id'] ) : 0;
    $per_page = isset( $input['per_page'] ) ? intval( $input['per_page'] ) : 20;

    if ( 0 === $form_id ) {
        $forms = GFAPI::get_forms();
        $items = array();

        foreach ( $forms as $form ) {
            $items[] = array(
                'id'          => $form['id'],
                'title'       => $form['title'],
                'entry_count' => GFAPI::count_entries( $form['id'] ),
                'is_active'   => (bool) $form['is_active'],
            );
        }

        return array( 'forms' => $items );
    }

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

// ─── Callback: Get Bricks Content ───────────────────────────────────────────

function ps_get_bricks_content_execute( $input ) {
    $post_id       = isset( $input['post_id'] ) ? intval( $input['post_id'] ) : 0;
    $element_types = isset( $input['element_types'] ) ? (array) $input['element_types'] : array();
    $include_raw   = isset( $input['include_raw'] ) ? (bool) $input['include_raw'] : false;

    if ( ! $post_id || ! get_post( $post_id ) ) {
        return array( 'success' => false, 'error' => 'Invalid post ID.' );
    }

    $elements = ps_get_bricks_elements( $post_id );

    if ( empty( $elements ) ) {
        return array(
            'success'  => true,
            'post_id'  => $post_id,
            'builder'  => 'none',
            'elements' => array(),
            'total'    => 0,
            'message'  => 'No Bricks content found.',
        );
    }

    $items = array();

    foreach ( $elements as $element ) {
        $name = isset( $element['name'] ) ? $element['name'] : '';

        if ( ! empty( $element_types ) && ! in_array( $name, $element_types, true ) ) {
            continue;
        }

        $settings = isset( $element['settings'] ) ? $element['settings'] : array();

        $item = array(
            'element_id' => isset( $element['id'] ) ? $element['id'] : '',
            'name'       => $name,
            'parent'     => isset( $element['parent'] ) ? $element['parent'] : 0,
            'label'      => isset( $element['label'] ) ? $element['label'] : '',
        );

        $common_fields = array( 'text', 'tag', 'link', 'title', 'content', 'label', 'placeholder', 'icon', '_cssClasses', 'code', 'executeCode', 'noRender' );
        foreach ( $common_fields as $field ) {
            if ( isset( $settings[ $field ] ) ) {
                $item[ $field ] = $settings[ $field ];
            }
        }

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

// ─── Callback: Update SEO Meta ──────────────────────────────────────────────

function ps_update_seo_meta_execute( $input ) {
    $post_id = isset( $input['post_id'] ) ? intval( $input['post_id'] ) : 0;

    if ( ! $post_id || ! get_post( $post_id ) ) {
        return array( 'success' => false, 'error' => 'Invalid post ID.' );
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
            update_post_meta( $post_id, $meta_key, sanitize_text_field( $input[ $input_key ] ) );
            $updated[] = $input_key;
        }
    }

    if ( ! empty( $input['robots'] ) && is_array( $input['robots'] ) ) {
        update_post_meta( $post_id, 'rank_math_robots', array_map( 'sanitize_text_field', $input['robots'] ) );
        $updated[] = 'robots';
    }

    return array(
        'success' => true,
        'post_id' => $post_id,
        'updated' => $updated,
        'message' => sprintf( 'Updated %d field(s) for post %d.', count( $updated ), $post_id ),
    );
}

// ─── Callback: Update Bricks Content ────────────────────────────────────────

function ps_update_bricks_content_execute( $input ) {
    $post_id = isset( $input['post_id'] ) ? intval( $input['post_id'] ) : 0;
    $updates = isset( $input['updates'] ) ? (array) $input['updates'] : array();
    $dry_run = isset( $input['dry_run'] ) ? (bool) $input['dry_run'] : false;

    if ( ! $post_id || ! get_post( $post_id ) ) {
        return array( 'success' => false, 'error' => 'Invalid post ID.' );
    }
    if ( empty( $updates ) ) {
        return array( 'success' => false, 'error' => 'No updates provided.' );
    }

    $original_meta = get_post_meta( $post_id, '_bricks_page_content_2', true );
    $elements      = ps_get_bricks_elements( $post_id );

    if ( empty( $elements ) ) {
        return array( 'success' => false, 'error' => 'No Bricks content found for this page.' );
    }

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

        $change = array(
            'element_id' => $element_id,
            'name'       => isset( $elements[ $index ]['name'] ) ? $elements[ $index ]['name'] : '',
            'fields'     => array(),
        );

        foreach ( $new_settings as $key => $new_value ) {
            $old_value = isset( $current_settings[ $key ] ) ? $current_settings[ $key ] : null;

            $change['fields'][] = array(
                'field' => $key,
                'old'   => $old_value,
                'new'   => $new_value,
            );

            if ( is_array( $new_value ) && is_array( $old_value ) ) {
                $elements[ $index ]['settings'][ $key ] = array_merge( $old_value, $new_value );
            } else {
                $elements[ $index ]['settings'][ $key ] = $new_value;
            }
        }

        $changes[] = $change;
        $elements_updated++;
    }

    if ( $dry_run ) {
        return array(
            'success'          => true,
            'post_id'          => $post_id,
            'dry_run'          => true,
            'changes'          => $changes,
            'not_found'        => $not_found,
            'elements_updated' => $elements_updated,
        );
    }

    $backup_key = ps_save_bricks_elements( $post_id, $elements, $original_meta );

    return array(
        'success'          => true,
        'post_id'          => $post_id,
        'dry_run'          => false,
        'changes'          => $changes,
        'not_found'        => $not_found,
        'elements_updated' => $elements_updated,
        'backup_key'       => $backup_key,
        'message'          => sprintf( 'Updated %d element(s) for post %d.', $elements_updated, $post_id ),
    );
}

// ─── Callback: Add Bricks Element ───────────────────────────────────────────

function ps_add_bricks_element_execute( $input ) {
    $post_id  = isset( $input['post_id'] ) ? intval( $input['post_id'] ) : 0;
    $element  = isset( $input['element'] ) ? (array) $input['element'] : array();
    $position = isset( $input['position'] ) ? $input['position'] : 'last';
    $dry_run  = isset( $input['dry_run'] ) ? (bool) $input['dry_run'] : false;

    if ( ! $post_id || ! get_post( $post_id ) ) {
        return array( 'success' => false, 'error' => 'Invalid post ID.' );
    }

    $name = isset( $element['name'] ) ? $element['name'] : '';
    if ( empty( $name ) ) {
        return array( 'success' => false, 'error' => 'Element name is required.' );
    }

    $settings = isset( $element['settings'] ) ? (array) $element['settings'] : array();
    if ( empty( $settings ) ) {
        return array( 'success' => false, 'error' => 'Element settings are required.' );
    }

    $original_meta = get_post_meta( $post_id, '_bricks_page_content_2', true );
    $elements      = ps_get_bricks_elements( $post_id );

    // Generate a unique ID that doesn't collide with existing ones.
    $existing_ids = wp_list_pluck( $elements, 'id' );
    do {
        $new_id = ps_generate_bricks_id();
    } while ( in_array( $new_id, $existing_ids, true ) );

    // Resolve parent.
    $parent = isset( $element['parent'] ) ? $element['parent'] : '0';
    if ( '0' === $parent || '' === $parent ) {
        $parent = 0;
    }

    // Build the new element.
    $new_element = array(
        'id'       => $new_id,
        'name'     => $name,
        'parent'   => $parent,
        'settings' => $settings,
    );

    if ( ! empty( $element['label'] ) ) {
        $new_element['label'] = $element['label'];
    }

    // Determine insertion index.
    $insert_index = count( $elements ); // Default: end of array.

    if ( 'first' === $position ) {
        // Find the first element with the same parent and insert before it.
        foreach ( $elements as $i => $el ) {
            $el_parent = isset( $el['parent'] ) ? $el['parent'] : 0;
            if ( $el_parent === $parent ) {
                $insert_index = $i;
                break;
            }
        }
    } elseif ( strpos( $position, 'before:' ) === 0 ) {
        $ref_id = substr( $position, 7 );
        foreach ( $elements as $i => $el ) {
            if ( isset( $el['id'] ) && $el['id'] === $ref_id ) {
                $insert_index = $i;
                break;
            }
        }
    } elseif ( strpos( $position, 'after:' ) === 0 ) {
        $ref_id = substr( $position, 6 );
        foreach ( $elements as $i => $el ) {
            if ( isset( $el['id'] ) && $el['id'] === $ref_id ) {
                // Find the last descendant of this element to insert after the whole subtree.
                $insert_index = $i + 1;
                $ref_descendants = array( $ref_id );
                for ( $j = $i + 1; $j < count( $elements ); $j++ ) {
                    $el_parent = isset( $elements[ $j ]['parent'] ) ? $elements[ $j ]['parent'] : 0;
                    if ( in_array( $el_parent, $ref_descendants, true ) ) {
                        $ref_descendants[] = $elements[ $j ]['id'];
                        $insert_index = $j + 1;
                    }
                }
                break;
            }
        }
    }
    // 'last' keeps the default (end of array).

    if ( $dry_run ) {
        return array(
            'success'      => true,
            'post_id'      => $post_id,
            'dry_run'      => true,
            'element_id'   => $new_id,
            'element'      => $new_element,
            'position'     => $insert_index,
            'total_before' => count( $elements ),
            'message'      => sprintf( 'Dry run: would insert "%s" element (ID: %s) at position %d.', $name, $new_id, $insert_index ),
        );
    }

    // Insert the element at the calculated position.
    array_splice( $elements, $insert_index, 0, array( $new_element ) );

    $backup_key = ps_save_bricks_elements( $post_id, $elements, $original_meta );

    return array(
        'success'      => true,
        'post_id'      => $post_id,
        'dry_run'      => false,
        'element_id'   => $new_id,
        'element'      => $new_element,
        'position'     => $insert_index,
        'total_after'  => count( $elements ),
        'backup_key'   => $backup_key,
        'message'      => sprintf( 'Inserted "%s" element (ID: %s) at position %d for post %d.', $name, $new_id, $insert_index, $post_id ),
    );
}

<?php
/**
 * Plugin Name: Posted Social MCP Abilities
 * Description: Exposes site content, SEO data, structure, and Bricks Builder content to AI via MCP.
 * Version: 2.1
 * Author: Posted Social
 */

defined( 'ABSPATH' ) || exit;

// ─── Schema Output via wp_head ──────────────────────────────────────────────

add_action( 'wp_head', 'ps_render_page_schema', 1 );

function ps_render_page_schema() {
    if ( ! is_singular() ) {
        return;
    }

    $post_id = get_the_ID();
    if ( ! $post_id ) {
        return;
    }

    $schemas = get_post_meta( $post_id, '_ps_page_schemas', true );
    if ( empty( $schemas ) || ! is_array( $schemas ) ) {
        return;
    }

    foreach ( $schemas as $schema ) {
        if ( empty( $schema['data'] ) ) {
            continue;
        }

        $json = wp_json_encode( $schema['data'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
        if ( $json ) {
            echo "\n" . '<script type="application/ld+json">' . "\n" . $json . "\n" . '</script>' . "\n";
        }
    }
}

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
            'description'         => 'Returns Bricks Builder elements for a page with element IDs, types, parent relationships, and settings.',
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
                    'post_id'         => array( 'type' => 'integer', 'description' => 'The post/page ID to update. Required.' ),
                    'focus_keyword'   => array( 'type' => 'string', 'description' => 'Primary focus keyword.', 'default' => '' ),
                    'seo_title'       => array( 'type' => 'string', 'description' => 'SEO title tag.', 'default' => '' ),
                    'seo_description' => array( 'type' => 'string', 'description' => 'Meta description.', 'default' => '' ),
                    'robots'          => array( 'type' => 'array', 'description' => 'Robots directives, e.g. ["noindex"].', 'default' => array() ),
                    'canonical'       => array( 'type' => 'string', 'description' => 'Canonical URL.', 'default' => '' ),
                    'schema_type'     => array( 'type' => 'string', 'description' => 'Rank Math rich snippet type.', 'default' => '' ),
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
            'description'         => 'Updates specific Bricks Builder elements by element ID. Merges settings, preserves unspecified fields. Backs up before writing.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'post_id' => array( 'type' => 'integer', 'description' => 'The post/page ID to update. Required.' ),
                    'updates' => array(
                        'type'        => 'array',
                        'description' => 'Array of {element_id, settings} objects.',
                        'items'       => array(
                            'type'       => 'object',
                            'properties' => array(
                                'element_id' => array( 'type' => 'string' ),
                                'settings'   => array( 'type' => 'object' ),
                            ),
                            'required' => array( 'element_id', 'settings' ),
                        ),
                    ),
                    'dry_run' => array( 'type' => 'boolean', 'description' => 'Preview without saving.', 'default' => false ),
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

    // 10. Manage page schema
    wp_register_ability(
        'postedsocial/manage-page-schema',
        array(
            'category'            => 'postedsocial',
            'label'               => 'Manage Page Schema',
            'description'         => 'Add, list, or remove JSON-LD schema blocks for a page. Schemas are stored in post meta (_ps_page_schemas) and rendered in <head> via wp_head. Supports any schema.org type. Each schema has a unique key for management.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'post_id' => array( 'type' => 'integer', 'description' => 'The post/page ID. Required.' ),
                    'action'  => array( 'type' => 'string', 'description' => '"add", "remove", "list", or "clear". Default "list".', 'default' => 'list' ),
                    'key'     => array( 'type' => 'string', 'description' => 'Unique key for the schema. Required for "add" and "remove".', 'default' => '' ),
                    'data'    => array( 'type' => 'object', 'description' => 'Schema.org JSON-LD data object. Required for "add". Must include @type.' ),
                ),
                'required' => array( 'post_id' ),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'success' => array( 'type' => 'boolean' ),
                    'post_id' => array( 'type' => 'integer' ),
                    'action'  => array( 'type' => 'string' ),
                    'schemas' => array( 'type' => 'array' ),
                ),
            ),
            'permission_callback' => '__return_true',
            'execute_callback'    => 'ps_manage_page_schema_execute',
            'meta'                => array( 'mcp' => array( 'public' => true ) ),
        )
    );
}

// ─── Helpers ────────────────────────────────────────────────────────────────

function ps_get_bricks_elements( $post_id ) {
    $meta = get_post_meta( $post_id, '_bricks_page_content_2', true );
    if ( empty( $meta ) ) {
        return array();
    }
    return is_string( $meta ) ? json_decode( $meta, true ) : (array) $meta;
}

function ps_save_bricks_elements( $post_id, $elements, $original_meta = null ) {
    if ( null !== $original_meta ) {
        $backup_key = '_bricks_page_content_2_backup_' . gmdate( 'Ymd_His' );
        update_post_meta( $post_id, $backup_key, $original_meta );
    }
    update_post_meta( $post_id, '_bricks_page_content_2', $elements );
    delete_post_meta( $post_id, '_bricks_page_content_2_html' );
    delete_post_meta( $post_id, '_bricks_page_content_2_css' );
    clean_post_cache( $post_id );
    return isset( $backup_key ) ? $backup_key : '';
}

// ─── Callbacks ──────────────────────────────────────────────────────────────

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
            'excerpt'         => has_excerpt( $post->ID ) ? get_the_excerpt( $post ) : wp_trim_words( $post->post_content, 40 ),
            'seo_title'       => get_post_meta( $post->ID, 'rank_math_title', true ),
            'seo_description' => get_post_meta( $post->ID, 'rank_math_description', true ),
            'focus_keyword'   => get_post_meta( $post->ID, 'rank_math_focus_keyword', true ),
            'robots'          => get_post_meta( $post->ID, 'rank_math_robots', true ),
            'canonical'       => get_post_meta( $post->ID, 'rank_math_canonical_url', true ),
        );
    }

    return array( 'items' => $items, 'total' => $query->found_posts );
}

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
        if ( empty( $seo_title ) )                                $issues[] = 'missing_seo_title';
        if ( empty( $seo_desc ) )                                 $issues[] = 'missing_meta_description';
        if ( empty( $focus_kw ) )                                 $issues[] = 'missing_focus_keyword';
        if ( str_word_count( $content ) < 300 )                   $issues[] = 'thin_content';
        if ( ! empty( $seo_desc ) && strlen( $seo_desc ) > 160 )  $issues[] = 'meta_description_too_long';
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

    return array( 'items' => $items, 'total' => $query->found_posts );
}

function ps_site_structure_execute( $input ) {
    $pages = get_pages( array( 'sort_column' => 'menu_order, post_title', 'sort_order' => 'ASC' ) );
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

    return array( 'pages' => $items, 'total' => count( $items ) );
}

function ps_internal_links_execute( $input ) {
    $post_id  = isset( $input['post_id'] ) ? intval( $input['post_id'] ) : 0;
    $per_page = isset( $input['per_page'] ) ? intval( $input['per_page'] ) : 50;
    $site_url = home_url();

    if ( $post_id > 0 ) {
        $posts = array( get_post( $post_id ) );
    } else {
        $query = new WP_Query( array(
            'post_status' => 'publish', 'posts_per_page' => $per_page, 'post_type' => array( 'post', 'page' ),
        ) );
        $posts = $query->posts;
    }

    $items = array();
    foreach ( $posts as $post ) {
        if ( ! $post ) continue;
        $links = array();
        if ( preg_match_all( '/<a\s[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/si', $post->post_content, $matches ) ) {
            foreach ( $matches[1] as $i => $href ) {
                if ( strpos( $href, $site_url ) === 0 || strpos( $href, '/' ) === 0 ) {
                    $links[] = array( 'url' => $href, 'anchor_text' => wp_strip_all_tags( $matches[2][ $i ] ) );
                }
            }
        }
        $items[] = array(
            'id' => $post->ID, 'title' => $post->post_title, 'url' => get_permalink( $post->ID ),
            'internal_links' => $links, 'link_count' => count( $links ),
        );
    }

    return array( 'items' => $items );
}

function ps_plugins_status_execute( $input ) {
    if ( ! function_exists( 'get_plugins' ) ) require_once ABSPATH . 'wp-admin/includes/plugin.php';
    if ( ! function_exists( 'get_plugin_updates' ) ) require_once ABSPATH . 'wp-admin/includes/update.php';

    $all_plugins    = get_plugins();
    $active_plugins = get_option( 'active_plugins', array() );
    $updates        = get_plugin_updates();
    $items          = array();

    foreach ( $all_plugins as $file => $data ) {
        $has_update = isset( $updates[ $file ] );
        $items[]    = array(
            'file'             => $file,
            'name'             => $data['Name'],
            'version'          => $data['Version'],
            'active'           => in_array( $file, $active_plugins, true ),
            'update_available' => $has_update,
            'update_version'   => $has_update ? $updates[ $file ]->update->new_version : '',
            'author'           => $data['AuthorName'],
        );
    }

    return array( 'plugins' => $items, 'total' => count( $items ) );
}

function ps_gravity_forms_execute( $input ) {
    if ( ! class_exists( 'GFAPI' ) ) return array( 'error' => 'Gravity Forms is not active.' );

    $form_id  = isset( $input['form_id'] ) ? intval( $input['form_id'] ) : 0;
    $per_page = isset( $input['per_page'] ) ? intval( $input['per_page'] ) : 20;

    if ( 0 === $form_id ) {
        $forms = GFAPI::get_forms();
        $items = array();
        foreach ( $forms as $form ) {
            $items[] = array( 'id' => $form['id'], 'title' => $form['title'], 'entry_count' => GFAPI::count_entries( $form['id'] ), 'is_active' => (bool) $form['is_active'] );
        }
        return array( 'forms' => $items );
    }

    $entries = GFAPI::get_entries( $form_id, array( 'status' => 'active' ), array( 'key' => 'date_created', 'direction' => 'DESC' ), array( 'offset' => 0, 'page_size' => $per_page ) );
    $form    = GFAPI::get_form( $form_id );
    $fields  = array();
    if ( $form && isset( $form['fields'] ) ) {
        foreach ( $form['fields'] as $field ) $fields[ $field->id ] = $field->label;
    }

    $items = array();
    foreach ( $entries as $entry ) {
        $entry_data = array( 'entry_id' => $entry['id'], 'date_created' => $entry['date_created'], 'source_url' => $entry['source_url'], 'fields' => array() );
        foreach ( $fields as $fid => $label ) {
            $val = rgar( $entry, $fid );
            if ( ! empty( $val ) ) $entry_data['fields'][ $label ] = $val;
        }
        $items[] = $entry_data;
    }

    return array( 'form' => $form['title'], 'entries' => $items );
}

function ps_get_bricks_content_execute( $input ) {
    $post_id       = isset( $input['post_id'] ) ? intval( $input['post_id'] ) : 0;
    $element_types = isset( $input['element_types'] ) ? (array) $input['element_types'] : array();
    $include_raw   = isset( $input['include_raw'] ) ? (bool) $input['include_raw'] : false;

    if ( ! $post_id || ! get_post( $post_id ) ) return array( 'success' => false, 'error' => 'Invalid post ID.' );

    $elements = ps_get_bricks_elements( $post_id );
    if ( empty( $elements ) ) {
        return array( 'success' => true, 'post_id' => $post_id, 'builder' => 'none', 'elements' => array(), 'total' => 0, 'message' => 'No Bricks content found.' );
    }

    $items = array();
    foreach ( $elements as $element ) {
        $name = isset( $element['name'] ) ? $element['name'] : '';
        if ( ! empty( $element_types ) && ! in_array( $name, $element_types, true ) ) continue;

        $settings = isset( $element['settings'] ) ? $element['settings'] : array();
        $item = array(
            'element_id' => isset( $element['id'] ) ? $element['id'] : '',
            'name'       => $name,
            'parent'     => isset( $element['parent'] ) ? $element['parent'] : 0,
            'label'      => isset( $element['label'] ) ? $element['label'] : '',
        );

        foreach ( array( 'text', 'tag', 'link', 'title', 'content', 'label', 'placeholder', 'icon', '_cssClasses', 'code', 'executeCode', 'noRender' ) as $field ) {
            if ( isset( $settings[ $field ] ) ) $item[ $field ] = $settings[ $field ];
        }
        if ( $include_raw ) $item['raw_settings'] = $settings;

        $items[] = $item;
    }

    return array( 'success' => true, 'post_id' => $post_id, 'builder' => 'bricks', 'elements' => $items, 'total' => count( $items ) );
}

function ps_update_seo_meta_execute( $input ) {
    $post_id = isset( $input['post_id'] ) ? intval( $input['post_id'] ) : 0;
    if ( ! $post_id || ! get_post( $post_id ) ) return array( 'success' => false, 'error' => 'Invalid post ID.' );

    $map = array(
        'focus_keyword' => 'rank_math_focus_keyword', 'seo_title' => 'rank_math_title',
        'seo_description' => 'rank_math_description', 'canonical' => 'rank_math_canonical_url',
        'schema_type' => 'rank_math_rich_snippet',
    );

    $updated = array();
    foreach ( $map as $key => $meta_key ) {
        if ( ! empty( $input[ $key ] ) ) {
            update_post_meta( $post_id, $meta_key, sanitize_text_field( $input[ $key ] ) );
            $updated[] = $key;
        }
    }
    if ( ! empty( $input['robots'] ) && is_array( $input['robots'] ) ) {
        update_post_meta( $post_id, 'rank_math_robots', array_map( 'sanitize_text_field', $input['robots'] ) );
        $updated[] = 'robots';
    }

    return array( 'success' => true, 'post_id' => $post_id, 'updated' => $updated, 'message' => sprintf( 'Updated %d field(s) for post %d.', count( $updated ), $post_id ) );
}

function ps_update_bricks_content_execute( $input ) {
    $post_id = isset( $input['post_id'] ) ? intval( $input['post_id'] ) : 0;
    $updates = isset( $input['updates'] ) ? (array) $input['updates'] : array();
    $dry_run = isset( $input['dry_run'] ) ? (bool) $input['dry_run'] : false;

    if ( ! $post_id || ! get_post( $post_id ) ) return array( 'success' => false, 'error' => 'Invalid post ID.' );
    if ( empty( $updates ) ) return array( 'success' => false, 'error' => 'No updates provided.' );

    $original_meta = get_post_meta( $post_id, '_bricks_page_content_2', true );
    $elements      = ps_get_bricks_elements( $post_id );
    if ( empty( $elements ) ) return array( 'success' => false, 'error' => 'No Bricks content found.' );

    $id_map = array();
    foreach ( $elements as $i => $el ) { if ( isset( $el['id'] ) ) $id_map[ $el['id'] ] = $i; }

    $changes = array(); $not_found = array(); $count = 0;

    foreach ( $updates as $upd ) {
        $eid = isset( $upd['element_id'] ) ? $upd['element_id'] : '';
        $ns  = isset( $upd['settings'] ) ? (array) $upd['settings'] : array();
        if ( empty( $eid ) || empty( $ns ) ) continue;
        if ( ! isset( $id_map[ $eid ] ) ) { $not_found[] = $eid; continue; }

        $idx = $id_map[ $eid ];
        $cs  = isset( $elements[ $idx ]['settings'] ) ? $elements[ $idx ]['settings'] : array();
        $ch  = array( 'element_id' => $eid, 'name' => $elements[ $idx ]['name'] ?? '', 'fields' => array() );

        foreach ( $ns as $k => $v ) {
            $ch['fields'][] = array( 'field' => $k, 'old' => $cs[ $k ] ?? null, 'new' => $v );
            $elements[ $idx ]['settings'][ $k ] = ( is_array( $v ) && is_array( $cs[ $k ] ?? null ) ) ? array_merge( $cs[ $k ], $v ) : $v;
        }
        $changes[] = $ch; $count++;
    }

    if ( $dry_run ) return array( 'success' => true, 'post_id' => $post_id, 'dry_run' => true, 'changes' => $changes, 'not_found' => $not_found, 'elements_updated' => $count );

    $bk = ps_save_bricks_elements( $post_id, $elements, $original_meta );
    return array( 'success' => true, 'post_id' => $post_id, 'dry_run' => false, 'changes' => $changes, 'not_found' => $not_found, 'elements_updated' => $count, 'backup_key' => $bk );
}

function ps_manage_page_schema_execute( $input ) {
    $post_id = isset( $input['post_id'] ) ? intval( $input['post_id'] ) : 0;
    $action  = isset( $input['action'] ) ? $input['action'] : 'list';
    $key     = isset( $input['key'] ) ? sanitize_key( $input['key'] ) : '';
    $data    = isset( $input['data'] ) ? (array) $input['data'] : array();

    if ( ! $post_id || ! get_post( $post_id ) ) return array( 'success' => false, 'error' => 'Invalid post ID.' );

    $schemas = get_post_meta( $post_id, '_ps_page_schemas', true );
    if ( ! is_array( $schemas ) ) $schemas = array();

    switch ( $action ) {
        case 'add':
            if ( empty( $key ) ) return array( 'success' => false, 'error' => 'Key is required.' );
            if ( empty( $data ) || empty( $data['@type'] ) ) return array( 'success' => false, 'error' => 'Schema data with @type is required.' );
            if ( empty( $data['@context'] ) ) $data['@context'] = 'https://schema.org';

            $schemas[ $key ] = array( 'key' => $key, 'type' => $data['@type'], 'data' => $data, 'added' => gmdate( 'Y-m-d H:i:s' ) );
            update_post_meta( $post_id, '_ps_page_schemas', $schemas );

            return array( 'success' => true, 'post_id' => $post_id, 'action' => 'add', 'key' => $key, 'schema_type' => $data['@type'], 'total' => count( $schemas ), 'message' => sprintf( 'Schema "%s" (%s) added for post %d.', $key, $data['@type'], $post_id ) );

        case 'remove':
            if ( empty( $key ) ) return array( 'success' => false, 'error' => 'Key is required.' );
            if ( ! isset( $schemas[ $key ] ) ) return array( 'success' => false, 'error' => sprintf( 'Schema "%s" not found.', $key ) );
            unset( $schemas[ $key ] );
            update_post_meta( $post_id, '_ps_page_schemas', $schemas );
            return array( 'success' => true, 'post_id' => $post_id, 'action' => 'remove', 'key' => $key, 'total' => count( $schemas ) );

        case 'clear':
            delete_post_meta( $post_id, '_ps_page_schemas' );
            return array( 'success' => true, 'post_id' => $post_id, 'action' => 'clear', 'total' => 0 );

        case 'list':
        default:
            $list = array();
            foreach ( $schemas as $sk => $sv ) {
                $list[] = array( 'key' => $sk, 'type' => $sv['type'] ?? '', 'added' => $sv['added'] ?? '' );
            }
            return array( 'success' => true, 'post_id' => $post_id, 'action' => 'list', 'schemas' => $list, 'total' => count( $list ) );
    }
}

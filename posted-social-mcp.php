<?php
/**
 * Plugin Name: Posted Social MCP Abilities
 * Description: Exposes site content, SEO data, and structure to AI via MCP.
 * Version: 1.0
 */

// ─── Category ───────────────────────────────────────────────────────────────

add_action( 'wp_abilities_api_categories_init', 'ps_register_ability_category' );

function ps_register_ability_category() {
    wp_register_ability_category(
        'postedsocial',
        array(
            'label'       => 'Posted Social',
            'description' => 'Abilities for Posted Social site analysis.',
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

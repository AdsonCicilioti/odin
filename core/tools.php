<?php

/**
 * Pagination.
 *
 * @global array $wp_query Global wordpress query tag.
 *
 * @param int $mid         Total of items that will show along with the current page.
 * @param int $end         Total of items displayed for the last few pages.
 * @param bool $show       Show all items.
 *
 * @return string          Return the pagination.
 */
function odin_pagination( $mid = 2, $end = 1, $show = false ) {

    // Prevent show pagination number if Infinite Scroll of JetPack is active.
    if ( ! isset( $_GET[ 'infinity' ] ) ) {

        global $wp_query;
        $total_pages = $wp_query->max_num_pages;

        if ( $total_pages > 1 ) {
            $current_page = max( 1, get_query_var( 'paged' ) );

            $pagination = '<div class="page-nav">';
            $pagination .= paginate_links( array(
                'base' => get_pagenum_link(1) . '%_%',
                'format' => '/page/%#%',
                'current' => $current_page,
                'total' => $total_pages,
                'show_all' => $show,
                'end_size' => $end,
                'mid_size' => $mid,
                'prev_text' => __( '&laquo; Anterior', 'odin' ),
                'next_text' => __( 'Pr&oacute;ximo &raquo;', 'odin' ),
                    ));
            $pagination .= '</div>';

            // Prevents duplicate bars in the middle of the url.
            $html = str_replace( '//page/', '/page/', $pagination );

            return $html;
        }
    }
}

/**
 * Related Posts.
 *
 * Usage:
 * To show related by categories:
 * Add in single.php <?php odin_related_posts(); ?>
 * To show related by tags:
 * Add in single.php <?php odin_related_posts( 'tag' ); ?>
 *
 * @global array $post         WP global post.
 *
 * @param string $display      Set category or tag.
 * @param int    $qty          Number of posts to be displayed (default 5).
 * @param string $title        Set the widget title.
 * @param bool   $thumb        Enable or disable displaying images.
 *
 * @return string              Related Posts.
 */
function odin_related_posts( $display = 'category', $qty = 5, $title = 'Artigos Relacionados', $thumb = true ) {
    global $post;

    $show = false;
    $post_qty = (int) $qty;

    // Creates arguments for WP_Query.
    switch ( $display ) {
        case 'tag':
            $tags = wp_get_post_tags( $post->ID );

            if ( $tags ) {
                // Enables the display.
                $show = true;

                $tag_ids = array();
                foreach ( $tags as $individual_tag ) {
                    $tag_ids[] = $individual_tag->term_id;
                }

                $args = array(
                    'tag__in' => $tag_ids,
                    'post__not_in' => array( $post->ID ),
                    'posts_per_page' => $post_qty,
                    'ignore_sticky_posts' => 1
                );
            }
            break;

        default :
            $categories = get_the_category( $post->ID );

            if ( $categories ) {

                // Enables the display.
                $show = true;

                $category_ids = array();
                foreach ( $categories as $individual_category ) {
                    $category_ids[] = $individual_category->term_id;
                }

                $args = array(
                    'category__in' => $category_ids,
                    'post__not_in' => array( $post->ID ),
                    'showposts' => $post_qty,
                    'ignore_sticky_posts' => 1,
                );
            }
            break;
    }

    if ( $show ) {

        $related = new WP_Query( $args );
        if ( $related->have_posts() ) {

            $layout = '<div id="related-post">';
            $layout .= '<h3>' . esc_attr( $title ) . '</h3>';
            $layout .= '<ul>';

            while ( $related->have_posts() ) {
                $related->the_post();

                $layout .= '<li>';

                if ( $thumb ) {
                    // Filter for use the functions of thumbnails.php in place of the_post_thumbnails().
                    $image = apply_filters( 'odin_related_posts', get_the_post_thumbnail( get_the_ID(), 'thumbnail' ) );

                    $layout .= '<span class="thumb">';
                    $layout .= sprintf( '<a href="%s" title="%s">%s</a>', get_permalink(), get_the_title(), $image );
                    $layout .= '</span>';
                }

                $layout .= '<span class="text">';
                $layout .= sprintf( '<a href="%1$s" title="%2$s">%2$s</a>', get_permalink(), get_the_title() );
                $layout .= '</span>';

                $layout .= '</li>';
            }

            $layout .= '</ul>';
            $layout .= '</div>';

            echo $layout;
        }
        wp_reset_postdata();
    }
}

/**
 * Custom excerpt for content or title.
 *
 * Usage:
 * Place: <?php echo odin_excerpt( '', value ); ?>
 *
 * @param string $type  Sets excerpt or title.
 * @param int    $limit Sets the length of excerpt.
 *
 * @return string       Return the excerpt.
 */
function odin_excerpt( $type = 'excerpt', $limit = '40' ) {
    $limit = (int) $limit;

    // Breaking the string and turns it into an array limiting by size.
    switch ( $type ) {
        case 'title':
            $excerpt = explode( ' ', get_the_title(), $limit );
            break;

        default :
            $excerpt = explode( ' ', get_the_excerpt(), $limit );
            break;
    }

    // Checks whether the number of items in the array is smaller than the defined.
    if ( count( $excerpt ) >= $limit ) {
        // Pop the element off the end of array.
        array_pop( $excerpt );
        $excerpt = implode( ' ', $excerpt ) . '...';
    } else {
        $excerpt = implode( ' ', $excerpt );
    }

    // Makes cleaning the string.
    $excerpt = preg_replace( '`\[[^\]]*\]`', '', $excerpt );

    return $excerpt;
}

/**
 * Breadcrumbs.
 *
 * @param  string $homepage  Homepage name.
 * @param  string $delimiter Breadcrumb item separator.
 *
 * @return string            HTML of breadcrumbs.
 */
function odin_breadcrumbs( $homepage = 'In&iacute;cio', $delimiter = '&raquo;' ) {
    // Default html.
    $current_before = '<span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb/">';
    $current_after  = '</span>';

    if ( ! is_home() && ! is_front_page() || is_paged() ) {
        global $post;

        // First level.
        echo '<p id="breadcrumbs" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';
        echo '<span itemprop="title"><a href="' . home_url() . '" rel="nofollow" itemprop="url"><span itemprop="title">' . $homepage . '</span></a></span> ' . $delimiter . ' ';

        // Single post.
        if ( is_single() && ! is_attachment() ) {
            global $post;

            // Checks if is a custom post type.
            if ( 'post' != $post->post_type ) {
                // Gets post type taxonomies.
                $taxonomy = get_object_taxonomies( $post->post_type );

                if ( $taxonomy ) {
                    // Gets post terms.
                    $term = get_the_terms( $post->ID, $taxonomy[0] ) ? array_shift( get_the_terms( $post->ID, $taxonomy[0] ) ) : '';

                    if ( $term ) {
                        echo '<strong itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb/"><a itemprop="url" href="' . get_term_link( $term ) . '"><span itemprop="title">' . $term->name . '</span></a></strong> ' . $delimiter . ' ';
                    }
                }
            } else {
                $category = get_the_category();
                $category = $category[0];

                echo '<span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a itemprop="url" href="' . get_category_link( $category->term_id ) . '"><span itemprop="title">' . $category->name . '</span></a></span> ' . $delimiter . ' ';
            }

            echo $current_before . '<strong class="current" itemprop="title">' . get_the_title() . '</strong>' . $current_after;

        // Single attachment.
        } elseif ( is_attachment() ) {
            $parent   = get_post( $post->post_parent );
            $category = get_the_category( $parent->ID );
            $category = $category[0];

            echo '<span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb/"><a itemprop="url" href="' . get_category_link( $category->term_id ) . '"><span itemprop="title">' . $category->name . '</span></a></span> ' . $delimiter . ' ';

            echo '<strong itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb/"><a itemprop="url" href="' . get_permalink( $parent ) . '"><span itemprop="title">' . $parent->post_title . '</span></a></strong> ' . $delimiter . ' ';

            echo $current_before . get_the_title() . $current_after;

        // Page without parents.
        } elseif ( is_page() && ! $post->post_parent ) {
            echo $current_before . get_the_title() . $current_after;

        // Page with parents.
        } elseif ( is_page() && $post->post_parent ) {
            $parent_id   = $post->post_parent;
            $breadcrumbs = array();

            while ( $parent_id ) {
                $page = get_page( $parent_id );

                $breadcrumbs[] = '<strong itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a itemprop="url" href="' . get_permalink( $page->ID ) . '"><span itemprop="title">' . get_the_title( $page->ID ) . '</span></a></strong>';
                $parent_id  = $page->post_parent;
            }

            $breadcrumbs = array_reverse( $breadcrumbs );

            foreach ( $breadcrumbs as $crumb ) {
                echo $crumb . ' ' . $delimiter . ' ';
            }

            echo $current_before . get_the_title() . $current_after;

        // Category archive.
        } elseif ( is_category() ) {
            global $wp_query;

            $category_object  = $wp_query->get_queried_object();
            $category_id      = $category_object->term_id;
            $current_category = get_category( $category_id );
            $parent_category  = get_category( $current_category->parent );

            // Displays parent category.
            if ( 0 != $current_category->parent ) {
                echo get_category_parents( $parent_category, TRUE, ' ' . $delimiter . ' ' );
            }

            printf( __( '%sCategoria: %s%s', 'odin' ), $current_before, single_cat_title( '', false ), $current_after );

        // Tags archive.
        } elseif ( is_tag() ) {
            printf( __( '%sTag: %s%s', 'odin' ), $current_before, single_tag_title( '', false ), $current_after );

        // Custom post type archive.
        } elseif ( is_post_type_archive() ) {
            echo $current_before . post_type_archive_title( '', false ) . $current_after;

        // Search page.
        } elseif ( is_search() ) {
            printf( __( '%sResultado da busca por: &quot;%s&quot;%s', 'odin' ), $current_before, get_search_query(), $current_after );

        // Author archive.
        } elseif ( is_author() ) {
            global $author;
            $userdata = get_userdata( $author );

            echo $current_before . __( 'Artigos postados por', 'odin' ) . ' ' . $userdata->display_name . $current_after;

        // Archives per days.
        } elseif ( is_day() ) {
            echo '<span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb/"><a itemprop="url" href="' . get_year_link( get_the_time( 'Y' ) ) . '"><span itemprop="title">' . get_the_time( 'Y' ) . '</span></a></span> ' . $delimiter . ' ';

            echo '<span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a itemprop="url" href="' . get_month_link( get_the_time( 'Y' ),get_the_time( 'm' ) ) . '"><span itemprop="title">' . get_the_time( 'F' ) . '</span></a></span> ' . $delimiter . ' ';

            echo $current_before . '<strong class="current" itemprop="title">' . get_the_time( 'd' ) . $current_after . '</strong>';

        // Archives per month.
        } elseif ( is_month() ) {
            echo '<span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a itemprop="url" href="' . get_year_link( get_the_time( 'Y' ) ) . '"><span itemprop="title">' . get_the_time( 'Y' ) . '</span></a></span> ' . $delimiter . ' ';

            echo $current_before . '<strong class="current" itemprop="title">' . get_the_time( 'F' ) . $current_after . '</strong>';

        // Archives per year.
        } elseif ( is_year() ) {
            echo $current_before . '<strong class="current" itemprop="title">' . get_the_time( 'Y' ) . $current_after . '</strong>';

        // Archive fallback for custom taxonomies.
        } elseif ( is_archive() ) {
            global $wp_query;

            $current_object = $wp_query->get_queried_object();
            $taxonomy        = get_taxonomy( $current_object->taxonomy );
            $term_name       = $current_object->name;

            // Displays parent term.
            if ( 0 != $current_object->parent ) {
                $parent_term = get_term( $current_object->parent, $current_object->taxonomy );

                echo '<span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb/"><a itemprop="url" href="' . get_term_link( $parent_term ) . '"><span itemprop="title">' . $parent_term->name . '</span></a></span> ' . $delimiter . ' ';
            }

            echo $current_before . $taxonomy->label . ': ' . $term_name . $current_after;

        // 404 page.
        } elseif ( is_404() ) {
            echo $current_before . __(' Erro 404', 'odin' ) . $current_after;
        }

        // Gets pagination.
        if ( get_query_var( 'paged' ) ) {

            if ( is_archive() ) {
                echo ' (' . sprintf( __( 'P&aacute;gina %s', 'odin' ), get_query_var('paged') ) . ')';
            } else {
                printf( __( 'P&aacute;gina %s', 'odin' ), get_query_var('paged') );
            }
        }

        echo '</p>';
    }
}

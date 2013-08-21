<?php get_header(); ?>
<div id="primary" class="col-md-8">
    <section id="content" role="main">
        <?php if ( have_posts() ) : ?>
            <header class="page-header">
                <h1 class="page-title" itemprop="name headline">
                    <?php
                        if ( is_day() ) {
                            echo __( 'Daily Archives:', 'odin' ) . ' <span>' . get_the_date() . '</span>';
                        } elseif ( is_month() ) {
                            echo __( 'Monthly Archives:', 'odin' ) . ' <span>' . get_the_date( 'F Y' ) . '</span>';
                        } elseif ( is_year() ) {
                            echo __( 'Yearly Archives:', 'odin' ) . ' <span>' . get_the_date( 'Y' ) . '</span>';
                        } else {
                            _e( 'Blog Archives', 'odin' );
                        }
                    ?>
                </h1>
            </header>
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part( 'content', get_post_format() ); ?>
            <?php endwhile; ?>
            <?php echo odin_pagination(); ?>
        <?php else : ?>
            <?php get_template_part( 'no-results' ); ?>
        <?php endif; ?>
    </section><!-- #content -->
</div><!-- #primary -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>

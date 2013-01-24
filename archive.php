<?php get_header(); ?>
<div id="primary">
    <section id="content" role="main">
        <?php if ( have_posts() ) : ?>
            <header class="page-header">
                <h1 class="page-title">
                    <?php
                        if ( is_day() ) {
                            echo __( 'Arquivos di&aacute;rios: ', 'odin' ) . '<span>' . get_the_date() . '</span>';
                        } elseif ( is_month() ) {
                            echo __( 'Arquivos Mensais: ', 'odin' ) . '<span>' . get_the_date( 'F Y' ) . '</span>';
                        } elseif ( is_year() ) {
                            echo __( 'Arquivos anuais: ', 'odin' ) . '<span>' . get_the_date( 'Y' ) . '</span>';
                        } else {
                            _e( 'Arquivos do Blog', 'odin' );
                        }
                    ?>
                </h1>
            </header>
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part( 'loop' ); ?>
            <?php endwhile; ?>
            <?php echo odin_pagination(); ?>
        <?php else : ?>
            <?php get_template_part( 'no-results' ); ?>
        <?php endif; ?>
    </section><!-- #content -->
</div><!-- #primary -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>

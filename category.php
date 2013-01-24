<?php get_header(); ?>
<div id="primary">
    <section id="content" role="main">
        <?php if ( have_posts() ) : ?>
            <header class="page-header">
                <h1 class="page-title"><?php echo __( 'Arquivos da Categoria: ', 'odin' ) . '<span>' . single_cat_title( '', false ) . '</span>'; ?></h1>
                <?php
                    $category_description = category_description();
                    if ( ! empty( $category_description ) ) {
                        echo '<div class="category-archive-meta">' . $category_description . '</div>';
                    }
                ?>
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

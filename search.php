<?php get_header(); ?>
<div id="primary">
    <section id="content" role="main">
        <?php if ( have_posts() ) : ?>
            <header class="page-header">
                <h1 class="page-title"><?php echo __( 'Resultados da Pesquisa para: ', 'odin' ) . '<span>' . get_search_query() . '</span>'; ?></h1>
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

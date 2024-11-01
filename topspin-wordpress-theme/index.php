<?php get_header(); ?>

<?php if(is_home() && (get_option('topspin_landingpage_suppress_content') !== 'active')) : ?>
	<div id="content">
			<?php if (have_posts()) : ?>
		
				<?php while (have_posts()) : the_post(); ?>
		
					<div class="post" id="post-<?php the_ID(); ?>">
						<h3><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
						<div class="postedby">posted <?php the_time('F jS,Y') ?> by <?php the_author() ?></div>
						<div class="postcontent">
							<?php the_content('Read the rest of this entry &raquo;'); ?>
						</div>
						<div class="post-info">
							<?php comments_popup_link('No Comments', '1 Comment', '% Comments'); ?>
							<br/>
							<?php the_tags('Tags: ', ', ', ''); ?>
						</div>
					</div>
					
					<?php comments_template(); ?>
				<?php endwhile; ?>
		
				<div id="pages">
					<a href="#"><?php next_posts_link('&larr;Older') ?></a>&nbsp;&nbsp;&nbsp;<a href="#"><?php previous_posts_link('Newer&rarr;') ?></a>
				</div>
		
			<?php else : ?>
		
				<h1>Not Found</h1>
				<p class="center">Sorry, but you are looking for something that isn't here.</p>
		
			<?php endif; ?>
	</div>
			
	<?php get_sidebar(); ?>
<?php endif; ?>
<?php get_footer(); ?>
		
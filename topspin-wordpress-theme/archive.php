<?php get_header(); ?>

	<div id="content">

		<?php if (have_posts()) : ?>
			
			<?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
			<?php /* If this is a category archive */ if (is_category()) { ?>
			<h1><span><?php single_cat_title(); ?></span></h1>
			<?php /* If this is a tag archive */ } elseif( is_tag() ) { ?>
			<h1>Posts Tagged <span><?php single_tag_title(); ?></span> </h1>
			<?php /* If this is a daily archive */ } elseif (is_day()) { ?>
			<h1>Archive for <span><?php the_time('F jS, Y'); ?></span></h1>
			<?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
			<h1>Archive for <span><?php the_time('F, Y'); ?></span></h1>
			<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
			<h1>Archive for <span><?php the_time('Y'); ?></span></h1>
			<?php /* If this is an author archive */ } elseif (is_author()) { ?>
			<h1>Author Archive</h1>
			<?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
			<h1>Blog Archives</h1>
			<?php } ?>
			<?php while (have_posts()) : the_post(); ?>
				<div class="archive-item">
					<div class="post" id="post-<?php the_ID(); ?>">
						<h3><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
						<div class="postedby">posted <?php the_time('F jS,Y') ?> by <?php the_author() ?></div>
						<?php
							$args = array(
								'post_type' => 'attachment',
								'numberposts' => 1,
								'post_status' => null,
								'post_parent' => $post->ID
								); 
							$attachments = get_posts($args);
							if ($attachments) 
							{
								foreach ($attachments as $attachment) 
								{
									$attachmentType = explode('/', $attachment->post_mime_type);
									if($attachmentType[0] == 'image') echo '<div class="postimage">' . get_the_attachment_link($attachment->ID, false) . '</div>';
								}
							}
						?>
						<div class="postcontent">
							<?php the_excerpt(); ?>
						</div>
						<div class="clear-both"><!-- CLEAR --></div>
						<div class="post-info">
							<?php comments_popup_link('No Comments', '1 Comment', '% Comments'); ?>
							<br/>
							<?php the_tags('Tags: ', ', ', ''); ?>
						</div>
					</div>
				</div>
			<?php endwhile; ?>
			<div id="pages">
				<a href="#"><?php next_posts_link('&larr;Older') ?></a>&nbsp;&nbsp;&nbsp;<a href="#"><?php previous_posts_link('Newer&rarr;') ?></a>
			</div>

		<?php else : ?>

			<h2 class="center">Not Found</h2>

		<?php endif; ?>

	</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>

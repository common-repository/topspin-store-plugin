<div id="sidebar">
<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>

	<div id="sb_recentposts" class="block">
		<h3>Recent Blog Posts</h3>
		<ul>
			<?php echo ts_getrecentposts('oddpost', 'evenpost'); ?>
		</ul>
		<div class="morelink"><a href="<?php echo get_bloginfo('url') . '/blog' ;?>">More Blog Posts &raquo;</a></div>
	</div>
	
	<div id="rss" class="block">
		<a href="<?php bloginfo('rss2_url'); ?>">RSS Feed</a>
	</div>
<?php endif; ?>
</div>
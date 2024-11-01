<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	
	<title><?php bloginfo('name'); ?> <?php if ( is_single() ) { ?> &raquo; Blog Archive <?php } ?> <?php wp_title(); ?></title>
	
	<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	
	<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />

	<?php
		if(get_option('topspin_design_site_width') !== ''):
	?>
	<style type="text/css">
		#site-wrapper {
			width: <?php echo get_option('topspin_design_site_width'); ?>px;
		}
	</style>
	<?php
		endif;
	?>
	
	<?php
		// Look for custom CSS files
		if(file_exists(get_stylesheet_directory() . '/custom.css'))
		{
			wp_register_style('topspin-custom-css', get_bloginfo('template_url') . '/custom.css');
			wp_enqueue_style('topspin-custom-css');
		}
		// Define what goes in the header: site image or landing page data.
		if((get_option('topspin_landingpage_active') === 'active') && ((get_option('topspin_landingpage_content') !== '')) && (is_home()))
		{
			$headerContent = WP_Topspin::getLandingPage();
			// If we're displaying the landing page, and there is a related CSS file, load it.
			if(get_option('topspin_landingpage_css') !== '')
			{
				wp_register_style('topspin-landingpage-css', get_option('topspin_landingpage_css'));
				wp_enqueue_style('topspin-landingpage-css');
			}
		}
		else
		{
			$headerContent = WP_Topspin::getHeader(true);
		}
		wp_head(); 
	?>
</head>

<body>
	<div id="site-wrapper">
		<div id="header-wrapper">
			<div id="header-content">
				<?php echo $headerContent; ?>
			</div>
			<?php if((get_option('topspin_landingpage_suppress_navigation') !== 'active' && is_home()) || !is_home()) : ?>
				<div id="navcontainer">
					<?php echo ts_getpagenav(); ?>
				</div>
			<?php endif; ?>
		</div>
		<div id="wrapper">
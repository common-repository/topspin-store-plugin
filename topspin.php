<?php
	/*
	 * Plugin Name: Topspin
	 * Version: 2.0
	 * Plugin URI: http://www.topspin.com/
	 * Description: Allow Topspin users to create merch pages, and embed widgets in posts.
	 * Author: StageBloc
	 * Author URI: http://stagebloc.com/
	 */

	/*  Copyright 2010 StageBloc  (email : hi@stagebloc.com)
	
	    This program is free software; you can redistribute it and/or modify
	    it under the terms of the GNU General Public License, version 2, as 
	    published by the Free Software Foundation.
	
	    This program is distributed in the hope that it will be useful,
	    but WITHOUT ANY WARRANTY; without even the implied warranty of
	    BUY_BUTTONANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	    GNU General Public License for more details.
	
	    You should have received a copy of the GNU General Public License
	    along with this program; if not, write to the Free Software
	    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	*/

	class WP_Topspin
	{
		// Option names
		const OPTION_TOPSPIN_ARTIST_ID							= 'topspin_artist_id';
		
		const OPTION_TOPSPIN_API_KEY							= 'topspin_api_key';
		const OPTION_TOPSPIN_API_USERNAME						= 'topspin_api_username';

		const OPTION_TOPSPIN_LANDINGPAGE_CONTENT				= 'topspin_landingpage_content';
		const OPTION_TOPSPIN_LANDINGPAGE_CSS					= 'topspin_landingpage_css';
		const OPTION_TOPSPIN_LANDINGPAGE_ACTIVE					= 'topspin_landingpage_active';
		const OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_CONTENT		= 'topspin_landingpage_suppress_content';
		const OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_NAVIGATION	= 'topspin_landingpage_suppress_navigation';
		
		const OPTION_TOPSPIN_DESIGN_NAV_HOMEBUTTON				= 'topspin_design_nav_homebutton';
		const OPTION_TOPSPIN_DESIGN_NAV_CATEGORIES				= 'topspin_design_nav_categories';
		const OPTION_TOPSPIN_DESIGN_SITE_WIDTH					= 'topspin_design_site_width';

		const OPTION_TOPSPIN_OFFERS_BUY_BUTTON_THEME			= 'topspin_offers_buy_button_theme';
		const OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PAGEID			= 'topspin_offers_buy_button_page_id';
		const OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PER_PAGE			= 'topspin_offers_buy_button_per_page';
		const OPTION_TOPSPIN_OFFERS_BUY_BUTTON_COLUMNS			= 'topspin_offers_buy_button_columns';
		const OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE		= 'topspin_offers_buy_button_product_type';
		const OPTION_TOPSPIN_OFFERS_BUY_BUTTON_NAME				= 'topspin_offers_buy_button_name';

		// Local cache of option values
		private static $options = array(
			self::OPTION_TOPSPIN_ARTIST_ID => '',
			self::OPTION_TOPSPIN_API_KEY => '',
			self::OPTION_TOPSPIN_API_USERNAME => '',
			self::OPTION_TOPSPIN_LANDINGPAGE_CONTENT => '',
			self::OPTION_TOPSPIN_LANDINGPAGE_CSS => '',
			self::OPTION_TOPSPIN_LANDINGPAGE_ACTIVE => '',
			self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_CONTENT => '',
			self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_NAVIGATION => '',
			self::OPTION_TOPSPIN_DESIGN_NAV_HOMEBUTTON => '',
			self::OPTION_TOPSPIN_DESIGN_NAV_CATEGORIES => '',
			self::OPTION_TOPSPIN_DESIGN_SITE_WIDTH => '',
			self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_THEME => '',
			self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PAGEID => '',
			self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PER_PAGE => '',
			self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_COLUMNS => '',
			self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE => '',
			self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_NAME => ''
		);

		/**
		 * Plugin Activation
		 *
		 * http://codex.wordpress.org/Function_Reference/wp_insert_post
		 */
		function activate()
		{
			self::loadOptions();

			// Check for page existence. If it exists, update it instead.
			$page = array();
			$page_id = self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PAGEID);
			$create_page = true;

			if($page_id && get_post($page_id))
			{
				// The page already exists; just re-activate it.
				$page['ID'] = $page_id;
				$page['post_status'] = 'publish';

				$update_post_id = wp_update_post($page);

				if($update_post_id !== 0)
				{
					// If the update worked, set the flag to not create a new page
					$create_page = false;
				}
			}
			
			// Check if a Blog category exists.  If not, create it.
			if(get_cat_ID('Blog') === 0)
			{
				$blogCategoryID = wp_create_category('Blog');
				// If Blog category is not default, set Blog category to default and delete Uncategorized category if it exists.
				if($blogCategoryID !== 0)
				{
					update_option('default_category', $blogCategoryID);
					$uncategorizedCategoryID = get_cat_ID('Uncategorized');
					if($uncategorizedCategoryID !== 0) wp_delete_category($uncategorizedCategoryID);
				}
			}

			// If this is the first time the plugin's being run,
			// or the pre-existing Topspin page was deleted,
			// attempt to create a new page.
			if($create_page === true)
			{
				$page['post_title'] = 'Store';
				$page['post_content'] = '[topspin_buy_buttons]';
				$page['post_status'] = 'publish';
				$page['post_type'] = 'page';

				// Reset the page ID in case it was set above
				unset($page['ID']);

				$page_id = wp_insert_post($page); // Returns 0 on error

				// If the insert worked, we'll have the page ID that we can store in our options database
				// Save default variables for pagetype as well
				if($page_id !== 0)
				{
					self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PAGEID, $page_id);
					self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_THEME, 'topspin.css');
					self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PER_PAGE, 6);
					self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_COLUMNS, 3);
					self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE, 'all');
					self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_NAME, '');
					self::option(self::OPTION_TOPSPIN_LANDINGPAGE_ACTIVE, '');
					self::option(self::OPTION_TOPSPIN_LANDINGPAGE_CONTENT, '');
					self::option(self::OPTION_TOPSPIN_LANDINGPAGE_CSS, '');
					self::option(self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_CONTENT, '');
					self::option(self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_NAVIGATION, '');
					self::option(self::OPTION_TOPSPIN_DESIGN_NAV_HOMEBUTTON, 'Home');
					self::option(self::OPTION_TOPSPIN_DESIGN_NAV_CATEGORIES, 'active');
					self::option(self::OPTION_TOPSPIN_DESIGN_SITE_WIDTH, '960');
					self::saveOptions();
				}
			}
		}
		
		/**
		 * addAdminMenu function.
		 *
		 * Adds admin menu to top level menu structure.
		 *
		 * @access public
		 * @return void
		 */
		public function addAdminMenu() {
			add_menu_page('Topspin', 'Topspin', 6, 'topspin', array('WP_Topspin', 'renderOptionsPage'), '');
		}
		
		/**
		 * addAdminEnqueue function.
		 * 
		 * Enqueue scripts and CSS for administration menu.
		 *
		 * @access public
		 * @param mixed $hook
		 * @return void
		 */
		public function addAdminEnqueue($hook)
		{
			// Make sure the required settings are available
			// Load current settings
			self::loadOptions();
			// Make sure we have an option for the buy button columns
			if(self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_COLUMNS) == '')
			{
				// If not, set a default value of 3
				self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_COLUMNS, 3);
				// Save our settings
				self::saveOptions();
			}
			
			if($hook === 'toplevel_page_topspin')
			{
				// De-register standard Wordpress script for jQuery UI
				wp_deregister_script('jquery-ui-core');
				// Get the latest version from Google
				wp_register_script('jquery-ui-core', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js', array('jquery'), '1.7.2');
				// Register our own javascript
				wp_register_script('topspin-admin-js', WP_PLUGIN_URL . '/' . self::getCurrentDir() . '/js/admin.js.php', array('jquery'), '1.0');
				// Load scripts
				wp_enqueue_script(array('topspin-admin-js', 'jquery-ui-core', 'thickbox'));
				// De-register our front end CSS since we don't need it for admin
				wp_deregister_style(array('topspin-base', 'topspin-theme'));
				// Get jQuery UI style from Google
				wp_register_style('jquery-ui-overcast', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/overcast/jquery-ui.css');
				// Register our admin styles
				wp_register_style('topspin-admin-css', WP_PLUGIN_URL . '/' . self::getCurrentDir() . '/css/admin.css');
				// Load styles
				wp_enqueue_style(array('jquery-ui-overcast', 'topspin-admin-css', 'thickbox'));
			}
		}

		/**
		 * bindEvents function.
		 * 
		 * Initialize various class-specific options.
		 *
		 * @access public
		 * @return void
		 */
		public function bindEvents()
		{
			self::loadOptions();
			
			wp_register_style('topspin-base', WP_PLUGIN_URL . '/' . self::getCurrentDir() . '/css/base.css');
			wp_register_style('topspin-theme', WP_PLUGIN_URL . '/' . self::getCurrentDir() . '/css/themes/' . self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_THEME));
			wp_enqueue_style(array('topspin-base', 'topspin-theme'));
			wp_register_script('topspin-purchaseflow', 'http://cdn.topspin.net/javascripts/topspin_purchase.js');
			wp_enqueue_script('topspin-purchaseflow');
			register_activation_hook(__FILE__, array('WP_Topspin', 'activate'));
			register_deactivation_hook(__FILE__, array('WP_Topspin', 'deactivate'));
			add_action('admin_menu', array('WP_Topspin', 'addAdminMenu'));
			add_action('admin_enqueue_scripts', array('WP_Topspin', 'addAdminEnqueue'), 10, 1);
			add_shortcode('topspin_buy_buttons', array('WP_Topspin', 'renderTopspinBuyButtonShortcode'));
			// Add oEmbed provider -- remember not to escape slashes in regex
			wp_oembed_add_provider('#http://app\.topspin.net/[a-z0-9/]*#i', 'https://app.topspin.net/api/v1/oembed', true);
		}

		/**
		 * deactivate function.
		 *
		 * Plugin Deactivation
		 *
		 * @access public
		 * @return void
		 */
		function deactivate()
		{
			self::loadOptions();

			// Set Topspin page status to draft
			$page = array();
			$page['ID'] = self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PAGEID);
			$page['post_status'] = 'draft';
			wp_update_post($page); // Returns 0 on error
			
			// Delete Options
			delete_option(self::OPTION_TOPSPIN_ARTIST_ID);
			delete_option(self::OPTION_TOPSPIN_API_KEY);
			delete_option(self::OPTION_TOPSPIN_API_USERNAME);
			delete_option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_THEME);
			delete_option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PER_PAGE);
			delete_option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_COLUMNS);
			delete_option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE);
			delete_option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_NAME);
			delete_option(self::OPTION_TOPSPIN_LANDINGPAGE_ACTIVE);
			delete_option(self::OPTION_TOPSPIN_LANDINGPAGE_CONTENT);
			delete_option(self::OPTION_TOPSPIN_LANDINGPAGE_CSS);
			delete_option(self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_CONTENT);
			delete_option(self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_NAVIGATION);
			delete_option(self::OPTION_TOPSPIN_DESIGN_NAV_HOMEBUTTON);
			delete_option(self::OPTION_TOPSPIN_DESIGN_NAV_CATEGORIES);
			delete_option(self::OPTION_TOPSPIN_DESIGN_SITE_WIDTH);
		}
		
		/**
		 * getCurrentDir function.
		 * 
		 * Gets the current directory name without the full path attached.
		 *
		 * @access public
		 * @return string
		 */
		public function getCurrentDir() 
		{
			return end(explode('/', dirname(__FILE__)));
		}

		/**
		 * getHeader function.
		 * 
		 * Grab site header graphic, if it exists.
		 *
		 * @access public
		 * @param bool $wrap. (default: false)
		 * @param bool $admin. (default: false)
		 * @return string
		 */
		public function getHeader($wrap = false, $admin = false)
		{
			// Set requisite global Wordpress SQL variable.
			global $wpdb;
			// This query pulls the header image.
			$query = "
				SELECT ID
				FROM 
					$wpdb->posts 
				WHERE 
					post_title = 'topspin-header'
					AND post_type = 'attachment' 
				LIMIT 1";
			// Ask Wordpress to find the header.
			$header = $wpdb->get_results($query);
			// If the header is found...
			if(!empty($header))
			{
				// Get the post and populate a post array containing the post variables.
				$imagePost = get_post($header[0]->ID);
				// If we are going to wrap the results in a div...
				if($wrap === true)
				{
					// Return the image wrapped in a div.
					$return = '<div id="topspin-header"><img class="topspin-header" src="'. $imagePost->guid . '" alt="Header Image" /></div>';
				}
				// ... otherwise no wrapping.
				else
				{
					// Return the image by itself.
					$return = '<img class="topspin-header" src="'. $imagePost->guid . '" alt="Header Image" />';
				}
					// If we've said the image is within the administrative section, and the user has valid privileges...
				if(($admin === true) && (is_user_logged_in()) && (is_admin()))
				{
					// ... add and edit link.
					$return .= '<p><a href="' . get_edit_post_link($header[0]->ID, 'display') . '" title="Edit site header">Edit site header</a></p>';
				}
			}
			// ... otherwise, return an error.
			else
			{
				// If we are going to wrap the results in a div...
				if($wrap === true)
				{
					// Return the invalid image error wrapped in a div.
					$return = '<div id="topspin-header" class="alert-300"><p>You have not set up your site header.</p><a href="' . get_bloginfo('url') . '/wp-admin/media-new.php" title="Create site header"><p>Click here</a> to upload an image.  <em>Once uploaded, ensure it is named "topspin-header"</em>.</p></div>';
				}
				// ... otherwise no wrapping.
				else
				{
					// Return the invalid image error by itself.
					$return = '<p class="alert-300">You have not set up your site header.  <a href="' . get_bloginfo('url') . '/wp-admin/media-new.php" title="Create site header">Click here</a> to upload an image.  <em>Once uploaded, ensure it is named "topspin-header".</p>';
				}
				
			}
			return $return;
		}
		
		/**
		 * getLandingPage function.
		 * 
		 * Displays user-defined landing page in wrapper.
		 *
		 * @access public
		 * @return string
		 */
		public function getLandingPage()
		{
			self::loadOptions();
			
			$return = '<div id="topspin-landingpage-wrapper">';
			$return .= stripslashes(get_option(self::OPTION_TOPSPIN_LANDINGPAGE_CONTENT));
			$return .= '</div>';
			
			return $return;
		}
		
		/**
		 * Fetches a list of themes (from /css/themes) and puts them into an array
		 */		
		private function getThemes($returnType = false, $pageType = false)
		{
			// Get value for $pageType
			// Additional cases coming in v2.0
			switch($pageType)
			{
				case 'buy_button' :
					$currentTheme = self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_THEME);
				break;
			
				default :
					$currentTheme = false;
					$return = 'Invalid page type set';
				break; 
			}
			if($currentTheme !== false)
			{
				// Open theme directory

				$themeHandle = opendir(dirname(__FILE__) . '/css/themes/');
				// If theme directory is found...
	    		if($themeHandle)
	    		{
	    			$themeList = array();
	    			$themeListCounter = 0;
	    			// ... scan the directory...
		    		while(false !== ($themeFilename = readdir($themeHandle))) 
		    		{
		    			// ... if we find a file...
		    			if ($themeFilename != '.' && $themeFilename != '..')
		    			{
		    				$themeFilenameExplode = explode('.', $themeFilename);
		    				$themeFilenameFindExtension = array_pop($themeFilenameExplode);
		    				$themeFilenameDisplay = implode('.', $themeFilenameExplode);
		    				// ... and it's a CSS file, and there are no illegal characters in the filename...
		    				if(($themeFilenameFindExtension === 'css') && (preg_match('/[\'"\s]/', $themeFilenameDisplay) === 0))
		    				{
		    					// ... add the file title and name to an array
				      			$themeList[$themeListCounter]->title = $themeFilenameDisplay;
				      			$themeList[$themeListCounter]->filename = $themeFilename;
				      			$themeListCounter++;
			      			}
			      		}
		    		}
		    		closedir($themeHandle);
	    		}
	    		if($returnType == 'array')
	    		{
	    			// If an array was requested, return the array
	    			$return = $themeList;
	    		}
	    		elseif($returnType == 'select')
	    		{
	    			// If a list of themes for the admin menu dropdown was requested, build the options
		    		if(!empty($themeList))
		    		{
			    		foreach($themeList as $theme)
			    		{
			    			$return .= '<option value="' . $theme->filename . '"';
			    			// If this theme is the currently selected option, assign 'selected' to it
			    			if($currentTheme == $theme->filename) $return .= ' SELECTED';
			    			$return .= '>' . ucwords($theme->title) . '</option>';
			    		}
			    	}
	    		}
	    		else
	    		{
	    			// No return type set; throw an error
	    			$return = 'Invalid return type set';
	    		}
			}
    		return $return;
		}

		/**
		 * Load the options from the database and store in local variable
		 */
		private function loadOptions()
		{
			foreach(self::$options as $key=>$value)
			{
				self::option($key, get_option($key));
			}
		}

		/**
		 * Returns option value, or sets option to new value if value is provided.
		 *
		 * Dual-purpose method. If $value is null, it returns the requested option.
		 * If $value is set, it sets the option to the value provided.
		 *
		 * TODO: Always return the option's value?
		 *
		 * @param <string> $option The option you're requesting.
		 * @param <mixed> $value Optional. If not null, will be set as the option's new value.
		 * @return <void|mixed> Returns option value if $value is null.
		 */
		private function option($option, $value = null)
		{
			if($value !== null)
			{
				self::$options[$option] = $value;
			}
			else
			{
				return self::$options[$option];
			}
		}
		

		/**
		 * Display the plugin's option page.
		 */
		public function renderOptions()
		{
			// Read in existing option value from database
			self::loadOptions();
		?>
			<div id="topspin-admin-wrapper" class="wrap">
			<?php
	
				if($_POST['action'] == 'update')
				{
					// Set up checkboxes
					if($_POST[self::OPTION_TOPSPIN_LANDINGPAGE_ACTIVE] !== 'active') $_POST[self::OPTION_TOPSPIN_LANDINGPAGE_ACTIVE] = 'off';
					if($_POST[self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_CONTENT] !== 'active') $_POST[self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_CONTENT] = 'off';
					if($_POST[self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_NAVIGATION] !== 'active') $_POST[self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_NAVIGATION] = 'off';
					if($_POST[self::OPTION_TOPSPIN_DESIGN_NAV_CATEGORIES] !== 'active')	$_POST[self::OPTION_TOPSPIN_DESIGN_NAV_CATEGORIES] = 'off';
					// Read posted values
					self::option(self::OPTION_TOPSPIN_ARTIST_ID, $_POST[self::OPTION_TOPSPIN_ARTIST_ID]);
					self::option(self::OPTION_TOPSPIN_API_KEY, $_POST[self::OPTION_TOPSPIN_API_KEY]);
					self::option(self::OPTION_TOPSPIN_API_USERNAME, $_POST[self::OPTION_TOPSPIN_API_USERNAME]);
					self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_THEME, $_POST[self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_THEME]);
					self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PAGEID, $_POST[self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PAGEID]);
					self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PER_PAGE, $_POST[self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PER_PAGE]);
					self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_COLUMNS, $_POST[self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_COLUMNS]);
					self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE, $_POST[self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE]);
					self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_NAME, $_POST[self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_NAME]);
					self::option(self::OPTION_TOPSPIN_LANDINGPAGE_ACTIVE, $_POST[self::OPTION_TOPSPIN_LANDINGPAGE_ACTIVE]);
					self::option(self::OPTION_TOPSPIN_LANDINGPAGE_CONTENT, $_POST[self::OPTION_TOPSPIN_LANDINGPAGE_CONTENT]);
					self::option(self::OPTION_TOPSPIN_LANDINGPAGE_CSS, $_POST[self::OPTION_TOPSPIN_LANDINGPAGE_CSS]);
					self::option(self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_CONTENT, $_POST[self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_CONTENT]);
					self::option(self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_NAVIGATION, $_POST[self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_NAVIGATION]);
					self::option(self::OPTION_TOPSPIN_DESIGN_NAV_HOMEBUTTON, $_POST[self::OPTION_TOPSPIN_DESIGN_NAV_HOMEBUTTON]);
					self::option(self::OPTION_TOPSPIN_DESIGN_NAV_CATEGORIES, $_POST[self::OPTION_TOPSPIN_DESIGN_NAV_CATEGORIES]);
					if($_POST[self::OPTION_TOPSPIN_DESIGN_SITE_WIDTH] !== '')
					{
						self::option(self::OPTION_TOPSPIN_DESIGN_SITE_WIDTH, (int) $_POST[self::OPTION_TOPSPIN_DESIGN_SITE_WIDTH]); // note the (int) type casting to convert '960px' to 960, etc.
					}
					else
					{
						self::option(self::OPTION_TOPSPIN_DESIGN_SITE_WIDTH, '960');
					}

					// Save the posted value in the database
					self::saveOptions();
	
					// Put an options updated message on the screen
			?>
					<div class="updated"><p><strong>Options saved.</strong></p></div>
			<?php
				}
	
				// Now display the options editing screen
				// We're using divs instead of the Wordpress table method, since tables are bad for layout.
			?>
				<div id="topspin-admin-content">
					<div id="topspin-admin-logo"><!-- IMAGE HOLDER --></div>
					<form action="" method="post">
						<input type="hidden" value="update" name="action"/>
						<div class="topspin-table">
							<div class="topspin-table-column topspin-table-column-left">
								<h5>Topspin Artist ID</h5>
							</div>
							<div class="topspin-table-column topspin-table-column-right">
								<input id="<?php echo self::OPTION_TOPSPIN_ARTIST_ID; ?>" class="regular-text" type="text" value="<?php echo self::option(self::OPTION_TOPSPIN_ARTIST_ID); ?>" name="<?php echo self::OPTION_TOPSPIN_ARTIST_ID; ?>" />
							</div>
							<div class="topspin-table-column topspin-table-column-left">
								<h5>Topspin API Key</h5>
							</div>
							<div class="topspin-table-column topspin-table-column-right">
								<input id="<?php echo self::OPTION_TOPSPIN_API_KEY; ?>" class="regular-text" type="text" value="<?php echo self::option(self::OPTION_TOPSPIN_API_KEY); ?>" name="<?php echo self::OPTION_TOPSPIN_API_KEY; ?>" />
							</div>
							<div class="clear-both"><!-- CLEAR --></div>
							<div class="topspin-table-column topspin-table-column-left">
								<h5>Topspin API Username</h5>
							</div>
							<div class="topspin-table-column topspin-table-column-right">
								<input id="<?php echo self::OPTION_TOPSPIN_API_USERNAME; ?>" class="regular-text" type="text" value="<?php echo self::option(self::OPTION_TOPSPIN_API_USERNAME); ?>" name="<?php echo self::OPTION_TOPSPIN_API_USERNAME; ?>" />
							</div>
							<div class="clear-both"><!-- CLEAR --></div>
						</div>
						<div class="topspin-subtable-wrapper">
							<div class="topspin-table">
								<div class="topspin-subtable-header"><h4>Theme Setup</h4></div>
								<?php if (get_option('permalink_structure') === '') : ?>
								<div class="topspin-table-column-alert">
									<p class="alert-300">You do not have Pretty (custom) Permalinks enabled.  You must set up Pretty Permalinks in order for the Topspin Wordpress theme to work correctly.  Please consult the accompanying readme.txt file for instructions.</p>
								</div>
								<?php endif; ?>
								<?php if(!file_exists(dirname(dirname(dirname(dirname(__FILE__)))) . '/.htaccess')) : ?>
								<div class="topspin-table-column-alert">
									<p class="alert-300">You do not have an .htaccess file set up.  You must set up an .htaccess file in order for the Topspin Wordpress theme to work correctly.  Please consult the accompanying readme.txt file for instructions.</p>
								</div>
								<?php endif; ?>
								<?php if(file_exists(WP_CONTENT_DIR . '/themes/topspin-wordpress-theme/index.php')) : ?>
									<div class="topspin-table-column-alert">
										<p class="alert-023">Topspin Wordpress theme has been installed
										<?php if(get_current_theme() == 'Topspin') : ?>
											and is currently active.
										<?php else : ?>
											but is currently inactive (<a href="<?php echo get_bloginfo('url'); ?>/wp-admin/themes.php" title="Activate">Click here</a> to activate it).
										<?php endif; ?>
										<a class="thickbox" href="<?php echo WP_PLUGIN_URL . '/' . self::getCurrentDir() . '/lib/theme/install.php'; ?>?TB_iframe=true&amp;height=400&amp;width=800&amp;modal=true">Click here</a> to re-install.  <em>Any modifications you've made to the theme will be lost when re-installing.</em></p>
									</div>
								<?php else : ?>
									<div class="topspin-table-column-alert">
										<p class="alert-300">The Topspin Wordpress theme has not been installed.  <a class="thickbox" href="<?php echo WP_PLUGIN_URL . '/' . self::getCurrentDir() . '/lib/theme/install.php'; ?>?TB_iframe=true&amp;height=400&amp;width=800&amp;modal=true">Click here</a> to install it.</p>
									</div>
								<?php endif; ?>
							</div>
						</div>
						<div class="topspin-subtable-wrapper">
							<div class="topspin-table">
								<div class="topspin-subtable-header">
									<h4 class="topspin-options-togglediv">Theme Design Setup</h4>
									<a class="topspin-options-showhide" href="javascript:slidetoggle('div#topspin-themedesign-details', 500, 'span.topspin-themedesign-details-text', 'Show Details', 'Hide Details')" title="Show/Hide Details"><span class="topspin-themedesign-details-text">Show Details</span></a>
									<div class="clear-both"><!-- CLEAR --></div>
								</div>
								<div id="topspin-themedesign-details">
									<div class="topspin-table-column">
										<?php echo self::getHeader(false, true); ?>
									</div>
									<div class="topspin-table-column topspin-table-column-left">
										<h5>Home Button Text</h5>
									</div>
									<div class="topspin-table-column topspin-table-column-right">
										<input id="<?php echo self::OPTION_TOPSPIN_DESIGN_NAV_HOMEBUTTON; ?>" class="regular-text" type="text" value="<?php echo self::option(self::OPTION_TOPSPIN_DESIGN_NAV_HOMEBUTTON); ?>" name="<?php echo self::OPTION_TOPSPIN_DESIGN_NAV_HOMEBUTTON; ?>" />
										<span class="description">Default: Home</span>
									</div>
									<div class="clear-both"><!-- CLEAR ---></div>
									<div class="topspin-table-column topspin-table-column-left">
										<h5>Categories in Navigation</h5>
									</div>
									<div class="topspin-table-column topspin-table-column-right">
										<input id="<?php echo self::OPTION_TOPSPIN_DESIGN_NAV_CATEGORIES; ?>" name="<?php echo self::OPTION_TOPSPIN_DESIGN_NAV_CATEGORIES; ?>" value="active" type="checkbox" <?php if(self::option(self::OPTION_TOPSPIN_DESIGN_NAV_CATEGORIES) === 'active') echo 'checked="checked"'; ?> />
									</div>
									<div class="clear-both"><!-- CLEAR ---></div>
									<div class="topspin-table-column topspin-table-column-left">
										<h5>Site Width</h5>
									</div>
									<div class="topspin-table-column topspin-table-column-right">
										<input id="<?php echo self::OPTION_TOPSPIN_DESIGN_SITE_WIDTH; ?>" class="regular-text" type="text" value="<?php echo self::option(self::OPTION_TOPSPIN_DESIGN_SITE_WIDTH); ?>" name="<?php echo self::OPTION_TOPSPIN_DESIGN_SITE_WIDTH; ?>" />
										<span class="description">Site width in pixels. Default: 960</span>
									</div>
									<div class="clear-both"><!-- CLEAR ---></div>
								</div>
							</div>
						</div>
						<div class="topspin-subtable-wrapper">
							<div class="topspin-table">
								<div class="topspin-subtable-header">
									<h4 class="topspin-options-togglediv">Landing Page Setup</h4>
									<a class="topspin-options-showhide" href="javascript:slidetoggle('div#topspin-landingpage-details', 500, 'span.topspin-landingpage-details-text', 'Show Details', 'Hide Details')" title="Show/Hide Details"><span class="topspin-landingpage-details-text">Show Details</span></a>
									<div class="clear-both"><!-- CLEAR --></div>
								</div>
								<div id="topspin-landingpage-details">
									<div class="topspin-table-column topspin-table-column-left">
										<h5>Activate Landing Page</h5>
									</div>
									<div class="topspin-table-column topspin-table-column-right">
										<input id="<?php echo self::OPTION_TOPSPIN_LANDINGPAGE_ACTIVE; ?>" name="<?php echo self::OPTION_TOPSPIN_LANDINGPAGE_ACTIVE; ?>" value="active" type="checkbox" <?php if(self::option(self::OPTION_TOPSPIN_LANDINGPAGE_ACTIVE) === 'active') echo 'checked="checked"'; ?> />
									</div>
									<div class="clear-both"><!-- CLEAR ---></div>
									<div class="topspin-table-column topspin-table-column-left">
										<h5>Suppress Home Content</h5>
									</div>
									<div class="topspin-table-column topspin-table-column-right">
										<input id="<?php echo self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_CONTENT; ?>" name="<?php echo self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_CONTENT; ?>" value="active" type="checkbox" <?php if(self::option(self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_CONTENT) === 'active') echo 'checked="checked"'; ?> />
									</div>
									<div class="clear-both"><!-- CLEAR ---></div>
									<div class="topspin-table-column topspin-table-column-left">
										<h5>Suppress Home Navigation</h5>
									</div>
									<div class="topspin-table-column topspin-table-column-right">
										<input id="<?php echo self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_NAVIGATION; ?>" name="<?php echo self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_NAVIGATION; ?>" value="active" type="checkbox" <?php if(self::option(self::OPTION_TOPSPIN_LANDINGPAGE_SUPPRESS_NAVIGATION) === 'active') echo 'checked="checked"'; ?> />
									</div>
									<div class="clear-both"><!-- CLEAR ---></div>
									<div class="topspin-table-column topspin-table-column-left">
										<h5>Landing Page CSS</h5>
									</div>
									<div class="topspin-table-column topspin-table-column-right">
										<input id="<?php echo self::OPTION_TOPSPIN_LANDINGPAGE_CSS; ?>" name="<?php echo self::OPTION_TOPSPIN_LANDINGPAGE_CSS; ?>" class="regular-text" type="text" value="<?php echo self::option(self::OPTION_TOPSPIN_LANDINGPAGE_CSS); ?>" /><span class="description">Optional: Full URL to CSS file.</span>
									</div>
									<div class="clear-both"><!-- CLEAR --></div>
									<div class="topspin-table-column topspin-table-column-wide">
										<textarea id="<?php echo self::OPTION_TOPSPIN_LANDINGPAGE_CONTENT; ?>" name="<?php echo self::OPTION_TOPSPIN_LANDINGPAGE_CONTENT; ?>" class="topspin-options-landingpage-textarea"><?php echo stripslashes(self::option(self::OPTION_TOPSPIN_LANDINGPAGE_CONTENT)); ?></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="topspin-subtable-wrapper">
							<div class="topspin-table">
								<div class="topspin-subtable-header">
									<h4 class="topspin-options-togglediv">Store Page Setup</h4>
									<a class="topspin-options-showhide" href="javascript:slidetoggle('div#topspin-storepage-details', 500, 'span.topspin-storepage-details-text', 'Show Details', 'Hide Details')" title="Show/Hide Details"><span class="topspin-storepage-details-text">Show Details</span></a>
									<div class="clear-both"><!-- CLEAR --></div>
								</div>
								<div id="topspin-storepage-details">
									<div class="topspin-table-column topspin-table-column-left">
										<h5>Associated Page ID</h5>
									</div>
									<div class="topspin-table-column topspin-table-column-right">
										<input id="<?php echo self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PAGEID; ?>" class="regular-text" type="text" value="<?php echo self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PAGEID); ?>" name="<?php echo self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PAGEID; ?>" readonly="readonly" />
										<span class="description"><?php edit_post_link('edit this page', 'You can ', '.', self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PAGEID)); ?></span>
									</div>
									<div class="clear-both"><!-- CLEAR --></div>
									<div class="topspin-table-column topspin-table-column-left">
										<h5>Items Per Page</h5>
									</div>	
									<div class="topspin-table-column topspin-table-column-right">													
										<input id="<?php echo self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PER_PAGE; ?>" class="regular-text" type="text" value="<?php echo self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PER_PAGE); ?>" name="<?php echo self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PER_PAGE; ?>" />
									</div>
									<div class="clear-both"><!-- CLEAR --></div>
									<div class="topspin-table-column topspin-table-column-left">
										<h5>Display Columns</h5>
									</div>									
									<div class="topspin-table-column topspin-table-column-right">													
										<input type="text" id="<?php echo self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_COLUMNS; ?>" class="topspin-columns" value="<?php echo self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_COLUMNS); ?>" name="<?php echo self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_COLUMNS; ?>" readonly="readonly"/>
										<div id="<?php echo self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_COLUMNS; ?>-slider" class="topspin-ui-slider"></div>
									</div>
									<div class="clear-both"><!-- CLEAR --></div>
									<div class="topspin-table-column topspin-table-column-left">
										<h5>Product Type</h5>
									</div>
									<div class="topspin-table-column topspin-table-column-right">													
										<select id="<?php echo self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE; ?>" value="<?php echo self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE); ?>" name="<?php echo self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE; ?>" />
											<option value="all"<?php if(self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE) === 'all') echo ' SELECTED'; ?>>All</option>
											<option value="album"<?php if(self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE) === 'album') echo ' SELECTED'; ?>>Album</option>
											<option value="image"<?php if(self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE) === 'image') echo ' SELECTED'; ?>>Image</option>
											<option value="merchandise"<?php if(self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE) === 'merchandise') echo ' SELECTED'; ?>>Merchandise</option>
											<option value="other_media"<?php if(self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE) === 'other_media') echo ' SELECTED'; ?>>Other Media</option>
											<option value="package"<?php if(self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE) === 'package') echo ' SELECTED'; ?>>Package</option>
											<option value="track"<?php if(self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE) === 'track') echo ' SELECTED'; ?>>Track</option>
											<option value="video"<?php if(self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE) === 'video') echo ' SELECTED'; ?>>Video</option>
										</select>
									</div>
									<div class="clear-both"><!-- CLEAR --></div>
									<div class="topspin-table-column topspin-table-column-left">
										<h5>Name Filter</h5>
									</div>
									<div class="topspin-table-column topspin-table-column-right">													
										<input id="<?php echo self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_NAME; ?>" class="regular-text" type="text" value="<?php echo self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_NAME); ?>" name="<?php echo self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_NAME; ?>" />
										<span class="description">Optional: Name to filter by.</span>
									</div>
									<div class="clear-both"><!-- CLEAR --></div>
									<div class="topspin-table-column topspin-table-column-left">
										<h5>Theme</h5>
									</div>
									<div class="topspin-table-column topspin-table-column-right">													
										<select id="<?php echo self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_THEME; ?>" value="<?php echo self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_THEME); ?>" name="<?php echo self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_THEME; ?>" />
											<?php echo self::getThemes('select', 'buy_button'); ?>
										</select>
									</div>
									<div class="clear-both"><!-- CLEAR --></div>
								</div>
							</div>
						</div>
						<p class="submit">
							<input type="submit" name="Submit" class="button-primary" value="Save Changes" />
						</p>
					</form>
				</div>
			</div>
	<?php
		}

		/**
		 * Initialize the option's page
		 */
		public function renderOptionsPage()
		{
			self::renderOptions();
		}
		
		/**
		 * renderPagination function.
		 *
		 * Displays a pagination box based on the current and total page variables.
		 *
		 * @access public
		 * @param mixed $currentPage
		 * @param mixed $totalPages
		 * @return string
		 */
		public function renderPagination($currentPage, $totalPages)
		{
			// Open pagination
			$return = '<div id="topspin-offer-pagination-wrapper"><div id="topspin-offer-pagination-container"><div id="topspin-offer-pagination-total">'; 
			$return .= '<p>' . sprintf('Page %d of %d', $currentPage, $totalPages) . '</p>';
			$return .= '</div>';
			// Start of pagination string
			$return .= '<div id="topspin-offer-pagination-navigation"><p>';
	
			// If we're not showing the first page, provide a link to it and show an ellipsis leading home
			if($currentPage > 3) $return .= '<span class="topspin-offer-pagination-jump topspin-offer-pagination-jump-first"><a title="Go to first page" href="?page=1">First</a> | </span>';
				
			if($currentPage != 1) 
			{
				$return .= '<span class="topspin-offer-pagination-prev">' . sprintf('<a title="Go to previous page" href="?page=%d">Prev</a>', $currentPage - 1) . '</span>';
				$return .= '<span class="topspin-offer-pagination-ellipsis topspin-offer-pagination-ellipsis-start">&#8230;</span>';
			}
			
			$start_page = $currentPage - 2;
			$end_page = $currentPage + 2;
	
			for($i = $start_page; $i <= $end_page; $i++)
			{
				// if($i <= 0 || $i > $result->total_pages) continue;
				// If $i is less than 1, add a page to $end page so we always show 5 pages if possible
				if($i <= 0)
				{
					$end_page++;
					continue;
				}
	
				if($currentPage == $i)
				{
					$return .= '<span class="topspin-offer-pagination-page topspin-offer-pagination-page-current">' . sprintf('%d', $i) . '</span>';
				}
				else
				{
					$return .= '<span class="topspin-offer-pagination-page">' . sprintf('<a title="Go to page %1$d" href="?page=%1$d">%1$d</a>', $i) . '</span>';
				}
	
				// If we're at the last page, break
				if($i == $totalPages)
				{
					break;
				}
			}
			
			// If we're not at the last or second-last page, show an ellipsis leading away and provide a next link 
			if($currentPage != $totalPages)
			{
				$return .= '<span class="topspin-offer-pagination-ellipsis topspin-offer-pagination-ellipsis-end">&#8230;</span>';
				$return .= '<span class="topspin-offer-pagination-next">' . sprintf('<a title="Go to next page" href="?page=%d">Next</a>', $currentPage + 1) . '</span>';
			}
			
			// If we're not at the last page, provide a link to it
			if($currentPage < ($totalPages - 2)) $return .= '<span class="topspin-offer-pagination-jump topspin-offer-pagination-jump-last"> | ' . sprintf('<a title="Go to last page" href="?page=%d">Last</a>', $totalPages) . '</span>';
			
			// Close pagination
			$return .= '</p></div>';
			
			return $return;
		}


		/**
		 * renderTopspinBuyButtonShortcode function.
		 *
		 * Displays a buy_button (store) page.
		 *
		 * @access public
		 * @return string
		 */
		public function renderTopspinBuyButtonShortcode()
		{
			self::loadOptions();

			require_once 'lib/API/Topspin.php';

			$tsAPIKey = self::option(self::OPTION_TOPSPIN_API_KEY);
			$tsAPIUsername = self::option(self::OPTION_TOPSPIN_API_USERNAME);
			
			if(!empty($tsAPIKey) && !empty($tsAPIUsername)) 
			{
				// Instantiate new Topspin object
				$ts = new Topspin($tsAPIKey, $tsAPIUsername);

				// Get options
				$page = isset($_GET['page']) ? $_GET['page'] : 1;
				$per_page = self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PER_PAGE);
				$columnsMax = intval(self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_COLUMNS));
				$product_type = self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_PRODUCT_TYPE);
				$campaign_name = self::option(self::OPTION_TOPSPIN_OFFERS_BUY_BUTTON_NAME);
				
				if(!empty($per_page))
				{
					$ts->perPage($per_page);
				}
	
				// Get buy buttons from Topspin
				// getOffers($page = 1, $offer_type = 'all', $product_type = 'all', $campaign_name = false)
				$result = $ts->getOffers($page, 'buy_button', $product_type, $campaign_name);
	
				// Set up counters (for offers, and for columns)
				$offerCounter = 1;
				$offerCounterLast = end($result);
				$columnsCounter = 1;
				
				$columnsCleared = false;
				
				// Open wrapper
				$return = '<div id="topspin-offer-wrapper" class="topspin-offer-wrapper-columns-' . $columnsMax . '">';
				// Open container
				$return .= '<div id="topspin-offer-container" class="topspin-offer-container-columns-' . $columnsMax . '">';
				if(!empty($result->offers)) 
				{
					foreach($result->offers as $offer)
					{
						// Get image source
						$offerImage = $offer->campaign->product->cover_art_image->large_url;
						$return .= '<div class="topspin-offer-item-wrapper topspin-1of' . $columnsMax;
						// Add additional classes if:
						// First item wrapper or second item wrapper in column
						// Last item wrapper or second last wrapper in column
						if($columnsCounter === 1) $return .= ' topspin-offer-item-wrapper-first';
						if($columnsCounter === 2) $return .= ' topspin-offer-item-wrapper-2nd';
						if($columnsCounter === $columnsMax-1) $return .= ' topspin-offer-item-wrapper-2nd-last';
						if($columnsCounter === $columnsMax) $return .= ' topspin-offer-item-wrapper-last';
						$return .= '">';
						$return .= '<div id="topspin-offer-item-' . $offerCounter;
						$return .= '" class="topspin-offer-item';
						// Add additional classes if:
						// First item or second item in column
						// Last item or second last in column, or last item in list
						if($columnsCounter === 1) $return .= ' topspin-offer-item-column-first';
						if($columnsCounter === 2) $return .= ' topspin-offer-item-column-2nd';
						if($columnsCounter === $columnsMax-1) $return .= ' topspin-offer-item-column-2nd-last';
						if($columnsCounter === $columnsMax) $return .= ' topspin-offer-item-column-last';
						if($offerCounter === $offerCounterLast) $return .= ' topspin-offer-item-last';
						$return .= '">';
						// Offer name
						if(function_exists('truncate'))
						{
							switch($columnsMax)
							{
								case 2 :
									$offerNameTruncated = truncate($offer->name, $length = 60, $ending = '...');
								break;
								
								case 3 :
									$offerNameTruncated = truncate($offer->name, $length = 40, $ending = '...');
								break;
								
								case 4 :
									$offerNameTruncated = truncate($offer->name, $length = 30, $ending = '...');
								break;							
								
								case 5 :
									$offerNameTruncated = truncate($offer->name, $length = 20, $ending = '...');
								break;
								
								default :
									$offerNameTruncated = $offer->name;
								break;
							}
						}
						else
						{
							$offerNameTruncated = $offer->name;
						}
						$return .= '<div class="topspin-offer-item-title topspin-offer-item-title-' . $columnsMax . 'column"><h4>' . $offerNameTruncated . '</h4></div> ';
						// If image not found, use default
						if(empty($offerImage)) $offerImage = WP_PLUGIN_URL . '/' . self::getCurrentDir() . '/images/offer-default.jpg';
						$return .= '<div class="topspin-offer-item-image"><img src="' . $offerImage . '" alt="Offer Image" /></div>';
						$return .= '<div class="topspin-offer-item-buybutton-wrapper">';
						// Embed Buy Button code from Topspin
						$return .= '<div class="topspin-offer-item-buybutton-button">' . $offer->embed_code . '</div>';
						$return .= '<div class="topspin-offer-item-buybutton-price"><p>' . $offer->currency . '&nbsp;' . Topspin::getCurrencySymbol($offer->currency) . $offer->price . '</p></div>';
						$return .= '<div class="topspin-clear-both"><!-- CLEAR --></div></div>';
						$return .= '</div></div>';
						$offerCounter++;
						if($columnsCounter === $columnsMax) 
						{
							$return .= '<div class="topspin-clear-both"><!-- CLEAR --></div>';
							$columnsCleared = true;
							$columnsCounter = 1;
						}
						else 
						{
							$columnsCounter++;
							$columnsCleared = false;
						}
					}
		
					if($columnsCleared === false) $return .= '<div class="topspin-clear-both"><!-- CLEAR --></div>';
					// Close offers container
					$return .= '</div>';
	
					if($result->total_pages > 1)
					{
						$return .= self::renderPagination($result->current_page, $result->total_pages);
						$return .= '<div class="topspin-clear-both"><!-- CLEAR --></div></div>';
						// Close offers container
						$return .= '<div class="topspin-clear-both"><!-- CLEAR --></div></div>';
					}
					// Close offers wrapper
					$return .= '<div class="topspin-clear-both"><!-- CLEAR --></div></div>';
				}
				else
				{
					// No offers found.  Throw an error.
					if(!empty($product_type)) $tsError = '<p>No ' . $product_type . ' offers found</p>';
					if(!empty($campaign_name)) $tsError .= '<p>No offers found using filter: ' . $campaign_name . '</p>';
				}
			}
			else
			{
				// API key or API username empty.  Throw an error.
				if(empty($tsAPIKey)) $tsError = '<p>No Topspin API key entered</p>';
				if(empty($tsAPIUsername)) $tsError .= '<p>No Topspin API username entered</p>';
			}
			
			// Error found
			if(!empty($tsError)) 
			{
				$return = '<div id="topspin-offer-error"><h4>Error:</h4>' . $tsError . '</div>';
			}

			return $return;
		}

		/**
		 * Save the options in the local variable to the database
		 */
		private function saveOptions()
		{
			foreach(self::$options as $key=>$value)
			{
				update_option($key, self::option($key));
			}
		}
	}

	// Kick-start the plugin
	WP_Topspin::bindEvents();
?>
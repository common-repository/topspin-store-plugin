<?php
	/**
	 * Install Topspin Wordpress theme from within Topspin Wordpress plug-in.
	 */

	$root = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
	if (file_exists($root.'/wp-load.php')) {
		// WP 2.6
		require_once($root.'/wp-load.php');
	} else {
		// Before 2.6
		require_once($root.'/wp-config.php');
	}
	
	// Make sure user is logged in, and has Admin access level.
	if (!current_user_can('update_plugins')) {
		echo '<html><head><body style="color:#FFF;"><h1>Sorry, you do not have access to update plug-ins.</h1><a style="line-height:16px;font-size:16px; cursor:pointer;" onclick="window.parent.tb_remove();" title="Return To Site">Return to site</a></body></html></head>';
		die();
	}
	
	// If there is a hostname in the current POST
	if($_POST['host'])
	{
		// Open an FTP connection to $host
		$host = $_POST['host'];
		$ftpStream = ftp_connect($host);
		// If the hostname is valid...
		if($ftpStream)
		{
			$ftpConnectStatus = 'Connected: ';
			// ... attempt login...
			$ftpLogin = ftp_login($ftpStream, $_POST['username'], $_POST['password']);
			// ... if login attempt is successful...
			if($ftpLogin)
			{
				// ... let the user know.
				$ftpConnectStatus .= 'Login authorized.';
			}
			// ... if login attempt fails...
			else
			{
				// ... let the user know, and flag error.
				$ftpConnectStatus .= 'Login failed (check user name and password).';
				$ftpConnectError = true;
			}
		}
		// ... otherwise if hostname is invalid...
		else
		{
			// ... let the user know, and flag error.
			$ftpConnectStatus = 'Connection failed: Host refused connection.';
			$ftpConnectError = true;
		}
	}
    
    /**
	 * ftpDeleteDirRecursive function.
	 * 
	 * Recursively delete directories via FTP
	 * Based on code by romain from php.net
	 *
	 * @access public
	 * @param object $resource
	 * @param string $path
	 * @return string
	 */
	function ftpDeleteRecursive($ftpStream, $path)
	{
	    $result_message = "";
	    // Generate list of items within requested path.
	    $list = ftp_nlist($ftpStream, $path);
	    // If there are multiple items to delete within the path...
	    if($list[0] != $path) 
	    {
	    	// Run through the list...
	        foreach($list as $item) 
	        {
	        	// ... if the item isn't the current or parent directory...
	        	if($item != $path . ".." && $item != $path . ".") 
	        	{
	        		// ... send the item through the loop again for deletion.
	        	    $result_message .= ftpDeleteRecursive($ftpStream, $item);
	        	}
	        }
	        // If the item was successfully deleted...
	        if(ftp_rmdir($ftpStream, $path)) 
	        {
	        	// ... let the user know.
	            $result_message .= '<p>Successfully deleted ' . $path . '</p>';
	        } 
	        else 
	        {
	        	// ... let the user know.
	            $result_message .= '<p>There was a problem while deleting ' . $path . '</p>';
	        }
	    }
	    // ... otherwise delete the item...
	    else 
	    {
	    	// ... if deletion was successful...
	        if(ftp_delete($ftpStream, $path)) 
	        {
	        	// ... let the user know.
	            $result_message .= '<p>Successfully deleted ' . $path . '</p>';
	        } 
	        // ... if delete failed...
	        else 
	        {
	        	// ... let the user know.
	            $result_message .= '<p>There was a problem while deleting ' . $path . '</p>';
	        }
	    }
	    return $result_message;
	}
	
	/**
	 * ftpPutRecursive function.
	 * 
	 * Recursively duplicate file/directory structure via FTP.
	 *
	 * @access public
	 * @param object $ftpStream // FTP stream handle
	 * @param string $destination // Path to destination from document root
	 * @param string $source // Path to source from document root
	 * @param string $absSource // Absolute path to source
	 * @return void
	 */
	function ftpPutRecursive($ftpStream, $destination, $source, $absSource)
	{
		// If the destination directory doesn't exist...
		if(!ftp_chdir($ftpStream, $destination))
		{
			// ... create the destination directory
			ftp_mkdir($ftpStream, $destination);
		}
		// Get a list of files from the source directory
		$list = ftp_nlist($ftpStream, $source);
		// Cycle through the list of files
		foreach($list as $item)
		{
			// Grab the current file or directory name
			$item = end(explode('/', $item));
			// If we can chdir to the current item in the source directory, we know it's a subdirectory
			if(ftp_chdir($ftpStream, $source . $item))
			{
				// Create the subdirectory within the destination folder
				ftp_mkdir($ftpStream, $destination . $item);
				// Loop through the files in the source subdirectory
				ftpPutRecursive($ftpStream, $destination . $item . '/', $source . $item . '/', $absSource . $item . '/');
			}
			else
			{
				// Copy the files using the absolute source (NOT path from document root)
				ftp_put($ftpStream, $destination . $item, $absSource . $item, FTP_BINARY);
			}
		}
	}
	
	require_once(ABSPATH . '/wp-admin/admin.php');
	$pluginDir = end(explode('/', dirname(dirname(dirname(__FILE__)))));
	$contentDir = end(explode('/', dirname(dirname(dirname(dirname(dirname(__FILE__)))))));
	$docRoot = end(explode('/', getenv('DOCUMENT_ROOT')));
	$themeSourceDir = '/' . $docRoot . '/' . $contentDir . '/plugins/' . $pluginDir . '/topspin-wordpress-theme/';
	$themeSourceDirAbs = getenv('DOCUMENT_ROOT') . '/' . $contentDir . '/plugins/' . $pluginDir . '/topspin-wordpress-theme/';
	$themeDestinationDir = '/' . $docRoot . '/' . $contentDir . '/themes/topspin-wordpress-theme/';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
	<head>
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
		<title>Topspin Wordpress Theme Install/Update</title>
<?php
	wp_admin_css('css/global');
	wp_admin_css();
	wp_admin_css('css/colors');
	wp_admin_css('css/ie' );
	do_action('admin_print_styles');
	do_action('admin_print_scripts');
?>

		<script type="text/javascript">
		// jQuery hide/unhide function w/fade (obj = target)
		function displaytoggle(obj, speed, triggerdiv, opentext, closedtext) {
			var display = jQuery(obj).css('display');
			if (display == 'none') {
				jQuery(obj).fadeIn(speed);
				jQuery(triggerdiv).text(closedtext);
			} else {
				jQuery(obj).fadeOut(speed);
				jQuery(triggerdiv).text(opentext);
			}
		}
		</script>
		
		<style type="text/css">
			#TB_window {
				background: #000;
			}
			
			html {
				background: url("../images/admin-bg.png") repeat-x scroll left top #001B22;
			}
			
			form#topspin-install-form {
				color: #EEE;
				background: #000; 
				padding: 10px;
			}
			
			form#topspin-install-form span {
				width:100px;
				display:block;
				float:left; 
				line-height:25px;
			}
			
			div#topspin-install-note {
				color: #EEE;
				background: #023;
				border: 1px solid #0CF;
				padding: 10px;
				margin: 5px 0 0;
			}
			
			div#topspin-install-note p {
				margin: 0;
			}
			
			div#topspin-install-status {
				background: #001B22;
				padding: 5px 10px;
				color: #CCC;
				border-bottom: 1px solid #000;
				font-style: italic;
			}
			
			div#topspin-install-footer {
				background: #000; 
				padding: 10px; 
				margin: 5px 0 0;
				position:absolute;
				top: 353px;
				width: 789px;
			}
			
			div#topspin-install-manual {
				display: none;
				position: absolute;
				height: 320px;
				width: 789px;
				top: 10px;
				left: 10px;
				padding: 10px;
				background: #000;
				color: #EEE;
			}
			
			div#topspin-install-manual-text {
				padding: 10px;
				background: #333;
			}
			
			div#topspin-install-manual h2 {
				margin: 0 0 10px;
			}
			
			div#topspin-install-manual p {
				margin: 0 0 5px;
			}
			
			ul#topspin-install-manual-list {
				margin: 10px 0; 
				padding: 0 40px; 
				list-style: circle;
			}
			
			ul#topspin-install-manual-list li.topspin-indent {
				margin-left: 20px;
			}
		</style>
	</head>
	<body style="font-family:Helvetica, Arial, 'sans-serif';height:auto;padding:10px;">
		<div id="inprogress">
			<div id="topspin-install-manual" style="display:none;">
				<h2>Manual install instructions</h2>
				<div id="topspin-install-manual-text">
					<p>The Topspin Wordpress theme installer works best under *nix environments.  Windows (IIS) servers may have issues with correct directory pathing.</p>
					<p>To install the theme manually:</p>
					<ul id="topspin-install-manual-list">
						<li>Locate the plug-in in the directory you downloaded it to on your local computer.</li>
						<li>Use an FTP client of your choice to connect to the server you installed the plug-in and Wordpress on, and find your theme folder.
							<li class="topspin-indent">On a standard Wordpress installation, this is under your Wordpress directory in /wp-content/themes/.</li>
						</li>
						<li>Transfer the entire directory named 'topspin-wordpress-theme' from the plug-in directory on your computer to the theme directory on your server.</li>
						<li>Log into the Wordpress administrative account.</li>
						<li>Within the Wordpress back-end, active the theme under Appearance > Themes in the primary (left screen-side) menu.</li>
					</ul>
					<p>Next time you access the Topspin plugin, it will notify you that the theme is installed and active.</p>
				</div>
			</div>
			<div id="topspin-install-status">
<?php if(empty($ftpConnectStatus)) : ?>
				<p>Initializing installation...</p>
			</div>
<?php if(file_exists(dirname(__FILE__) . '/../../topspin-wordpress-theme')) : ?>
			<form id="topspin-install-form" action="" method="post">
				<p><label><span>Site host</span></label><input type="text" name="host"></input></p>
				<p><label><span>FTP user name</span></label><input type="text" name="username"></input></p>
				<p><label><span>FTP password</span></label><input type="password" name="password"></input></p>
				<div style="clear:both;"><!-- CLEAR --></div>
				<input type="submit" name="Submit" value="Proceed With Install" class="button" />
			</form>
			<div id="topspin-install-note">
				<p>The automatic installer assumes you have a standard Wordpress install.  It attempts to locate your files based on educated guesses, but it cannot account for every configuration it may encounter.  If you run into problems, please click the "Manual Install" button for instructions on how to install the theme manually.</p>
			</div>
<?php else : ?>
			<div id="topspin-install-note">
				<p>Could not locate Topspin Wordpress Theme.  Try re-installing the plug-in.</p>
			</div>
<?php endif; ?>
<?php else : ?>
				<p>Attempting to initiate FTP connection.</p>
				<p><?php echo $ftpConnectStatus; ?></p>
				<?php 
					if($ftpConnectError !== true) 
					{
						if(ftp_chdir($ftpStream, $themeSourceDir))
						{
							if(ftp_chdir($ftpStream, $themeDestinationDir))
							{
								echo '<p>Removing previous installation of Topspin Wordpress theme...</p>';
								ftpDeleteRecursive($ftpStream, $themeDestinationDir);
							}
							else
							{
								echo '<p>No previous installation of Topspin Wordpress theme found.</p>';
							}
							echo '<p>Installing Topspin Wordpress theme...</p>';
							ftpPutRecursive($ftpStream, $themeDestinationDir, $themeSourceDir, $themeSourceDirAbs);
							if(file_exists(WP_CONTENT_DIR . '/themes/topspin-wordpress-theme/index.php'))
							{
								echo '<p>Automatic install successful.</p>';
							}
							else
							{
								echo '<p>Automatic install failed.  Please check Manual Install instructions.</p>';
							}
						}
						else
						{
							echo '<p>Could not find Topspin Wordpress theme directory.  Install aborted.</p>';
							echo '<p>We looked here: ' . $themeSourceDir . '</p></div>';
						}
						ftp_close($ftpStream);
					}
				?>
			</div>
<?php endif; ?>
		</div>
		<div id="topspin-install-footer">
			<a style="float:right;line-height:16px;font-size:12px;" onclick="window.parent.tb_remove_refresh();" title="Return To Site" class="button-primary">Return to site</a>
			<a style="float:right;line-height:16px;font-size:12px;margin-right:10px;" href="javascript:displaytoggle('div#topspin-install-manual', 500, 'span#topspin-install-text', 'Manual Install', 'Automatic Install')" title="Installation Instructions" class="button-primary"><span id="topspin-install-text">Manual Install</span></a>
			<div style="clear:both;"><!-- CLEAR --></div>
		</div>
	</body>
</html>
<?php
/**
 * Topspin - Wordpress - Javascript - Admin
 */

	$root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
	if (file_exists($root.'/wp-load.php')) {
		// WP 2.6
		require_once($root.'/wp-load.php');
	} else {
		// Before 2.6
		require_once($root.'/wp-config.php');
	}
	
	Header("content-type: application/x-javascript");
?>
// jQuery UI slider setup
jQuery(document).ready(function($)
{
	$("#topspin_offers_buy_button_columns-slider").slider(
	{
		value: <?php echo get_option('topspin_offers_buy_button_columns'); ?>,
		min: 1,
		max: 5,
		step: 1,
		slide: function(event, ui) 
		{
			$("#topspin_offers_buy_button_columns").val(ui.value);
		}
	});
	$("#topspin_offers_buy_button_columns").val($("#topspin_offers_buy_button_columns-slider").slider("value"));
});

// jQuery hide/unhide function w/fade (obj = target)
function displaytoggle(obj, speed, triggerdiv, opentext, closedtext) 
{
	var display = jQuery(obj).css('display');
	if (display == 'none') 
	{
		jQuery(obj).fadeIn(speed);
		jQuery(triggerdiv).text(closedtext);
	} 
	else 
	{
		jQuery(obj).fadeOut(speed);
		jQuery(triggerdiv).text(opentext);
	}
}

// jQuery slide function (obj = target)
function slidetoggle(obj, speed, triggerdiv, opentext, closedtext) 
{
	var currenttext = jQuery(triggerdiv).text();
	jQuery(obj).slideToggle(speed, function() 
	{
		if (currenttext == opentext) 
		{
			jQuery(triggerdiv).text(closedtext);
		} 
		else 
		{
			jQuery(triggerdiv).text(opentext);
		}
	});
}

// Thickbox removal and parent refresh
function tb_remove_refresh() 
{
	tb_remove();
	window.location.reload();
}
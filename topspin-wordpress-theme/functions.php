<?php
	if(function_exists('register_sidebar'))
	{
		register_sidebar(array(
			'before_widget' => '<div class="block">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widgettitle">',
			'after_title' => '</h3>',
		));
	}
	
	/**
	 * curPageURL function.
	 * 
	 * Get current url.  If 'pagename' specified, get current page name instead.
	 *
	 * @access public
	 * @return void
	 */
	function curPageURL($type) 
	{
		if($type != 'pagename')
		{
			$pageURL = 'http';
			if ($_SERVER["HTTPS"] == "on") $pageURL .= "s";
			$pageURL .= "://";
			if ($_SERVER["SERVER_PORT"] != "80") 
			{
				$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			} 
			else
			{
				$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			}
		}
		else
		{
 			$pageURL = explode('/', parse_url('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'], PHP_URL_PATH));
			$pageURL = $pageURL[1];
		}
		return $pageURL;
	}
	
	/**
	 * filterPageParents function.
	 *
	 * Removes subpages from list of pages.
	 *
	 * @access public
	 * @param array $pages
	 * @return array
	 */
	function filterPageParents($pages)
	{
		$return = array();
		foreach ($pages as $page) 
		{
			if($page->post_parent === 0)
			{
				array_push($return, $page);
			}
		}
		return $return;
	}

	/**
	 * Truncates text.
	 *
	 * Cuts a string to the length of $length and replaces the last characters
	 * with the ending if the text is longer than length.
	 *
	 * @param string  $text String to truncate.
	 * @param integer $length Length of returned string, including ellipsis.
	 * @param mixed $ending If string, will be used as Ending and appended to the trimmed string. Can also be an associative array that can contain the last three params of this method.
	 * @param boolean $exact If false, $text will not be cut mid-word
	 * @param boolean $considerHtml If true, HTML tags would be handled correctly
	 * @return string Trimmed string.
	 */
	function truncate($text, $length = 100, $ending = '...', $exact = true, $considerHtml = false) {
        if ($considerHtml) {
            // if the plain text is shorter than the maximum length, return the whole text
            if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            
            // splits all html-tags to scanable lines
            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
    
            $total_length = strlen($ending);
            $open_tags = array();
            $truncate = '';
            
            foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                    // if it's an "empty element" with or without xhtml-conform closing slash (f.e. <br/>)
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                        // do nothing
                    // if tag is a closing tag (f.e. </b>)
                    } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                        // delete tag from $open_tags list
                        $pos = array_search($tag_matchings[1], $open_tags);
                        if ($pos !== false) {
                            unset($open_tags[$pos]);
                        }
                    // if tag is an opening tag (f.e. <b>)
                    } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                        // add tag to the beginning of $open_tags list
                        array_unshift($open_tags, strtolower($tag_matchings[1]));
                    }
                    // add html-tag to $truncate'd text
                    $truncate .= $line_matchings[1];
                }
                
                // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length+$content_length > $length) {
                    // the number of characters which are left
                    $left = $length - $total_length;
                    $entities_length = 0;
                    // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1]+1-$entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } else {
                                // no more characters left
                                break;
                            }
                        }
                    }
                    $truncate .= substr($line_matchings[2], 0, $left+$entities_length);
                    // maximum lenght is reached, so get off the loop
                    break;
                } else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }
                
                // if the maximum length is reached, get off the loop
                if($total_length >= $length) {
                    break;
                }
            }
        } else {
            if (strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = substr($text, 0, $length - strlen($ending));
            }
        }
        
        // if the words shouldn't be cut in the middle...
		if (!$exact) {
            // ...search the last occurance of a space...
			$spacepos = strrpos($truncate, ' ');
			if (isset($spacepos)) {
                // ...and cut the text in this position
				$truncate = substr($truncate, 0, $spacepos);
			}
		}

        if($considerHtml) {
            // close all unclosed html-tags
            $tagtotal = count($open_tags);
            $tagcounter = 1;
            foreach ($open_tags as $tag) {
            	if ($tagcounter == ($tagtotal)) {
            		// add the defined ending to the text just before closing tag
            		$truncate .= $ending;
            	}
                $truncate .= '</' . $tag . '>';
                $tagcounter++;
            }
        } else {
        	// add the defined ending to the text
        	$truncate .= $ending;
        }
		return $truncate;
	}
	
	/**
	 * ts_getpagenav function.
	 * 
	 * Originally written by Topspin
	 * Modified by StageBloc Feb 18 2010
	 *
	 * @access public
	 * @return string
	 */
	function ts_getpagenav()
	{
		$homeText = get_option('topspin_design_nav_homebutton') == '' ? 'Home' : get_option('topspin_design_nav_homebutton');
		$showOnFront = get_option('show_on_front') === 'posts' ? 1 : 0;
		$curPage = curPageURL('pagename');
		$loopcount = 0;
		$pageArgs = array('sort_column'=>'menu_order');
		$pages = filterPageParents(get_pages($pageArgs));
		if(get_option('topspin_design_nav_categories') === 'active') $categories = get_categories();

		// Site width - this is used in calculating the width of the menu items
		$siteWidth = get_option('topspin_design_site_width') != '' ? get_option('topspin_design_site_width') : 960; // value is in pixels
		// Number of menu items
		$itemTotal = (count($pages) + count($categories) + $showOnFront);
		// Width of each menu item
		$itemWidthValue = floor($siteWidth / $itemTotal);
		// CSS styles
		$itemWidthStyle = ' style="width:' . $itemWidthValue . 'px;"';

		// If the total width for all items doesn't equal the site width, make the first item fill the space...
		if(($itemWidthValue * $itemTotal) !== $siteWidth)
		{
			$itemFirstStyle = ' style="width:' . ($itemWidthValue + ($siteWidth - $itemWidthValue * $itemTotal)) . 'px;"';
		}
		// ...otherwise let it be the same width as the other items
		else
		{
			$itemFirstStyle = $itemWidthStyle;
		}

		$navHTML = '<ul id="topspin-pagenav">';
		if($showOnFront === 1)
		{
			$navHTML .= '<li class="nav-first';
			$navLink = '<a href="' . get_bloginfo('url') . '">' . $homeText . '</a>';
			if($curPage === '')
			{
				$navLink = '<p>' . $homeText . '</p>';
				$navHTML .= ' current_page_item';
			} 
			$navHTML .= '"' . $itemFirstStyle . '>' . $navLink . '</li>';
		}
		if (!empty($pages))
		{
			foreach ($pages as $page) 
			{
				$classFirst = '';
				$classCurrent = '';
				$class = '';
				$pageTitle = $page->post_title;
				if($showOnFront === 0 && $loopcount === 0) $classFirst = 'nav-first';
				if(strlen($pageTitle) > 18) $pageTitle = substr($pageTitle, 0, 15) . "&hellip";
				$navLink = '<a href="' . get_bloginfo('url') . '/' . $page->post_name . '">' . $pageTitle . '</a>';
				if($curPage === $page->post_name)
				{
					$navLink = '<p>' . $pageTitle . '</p>';
					$classCurrent = 'current_page_item';
				}
				if(!empty($classFirst) || !empty($classCurrent))
				{
					$class = ' class="' . $classFirst;
					if($classFirst) $class .= ' ';
					$class .= $classCurrent . '"';
				}
				$navHTML .= '<li' . $class;
				if($showOnFront === 0 && $loopcount === 0) 
				{
					$navHTML .= $itemFirstStyle;
				}
				else
				{
					$navHTML .= $itemWidthStyle;
				}
				$navHTML .=  '>' . $navLink . '</li>';
				$loopcount++;
			}
		}
		if (!empty($categories))
		{
			foreach ($categories as $category) 
			{
				$categoryTitle = $category->cat_name;
				if (strlen($categoryTitle) > 18)
				{
					$categoryTitle = substr($categoryTitle, 0, 15) . "&hellip";
				}
				$class = "";
				$navLink = '<a href="' . get_bloginfo('url') . '/' . $category->category_nicename . '">' . $categoryTitle . '</a>';
				if ($curPage === $category->category_nicename) 
				{
					$navLink = '<p>' . $categoryTitle . '</p>';
					$class = ' class="current_page_item"';
				}
				$navHTML .= '<li' . $class . '' . $itemWidthStyle . '>' . $navLink . '</li>';
				$loopcount++;
			}
		}
		$navHTML .= '</ul>';
		return $navHTML;
	}
	
	/**
	 * ts_getrecentposts function.
	 * 
	 * Originally written by Topspin
	 * Modified by StageBloc Feb 18 2010
	 *
	 * @access public
	 * @param string $linkclass. (default: '')
	 * @param string $linkaltclass. (default: '')
	 * @return string
	 */
	function ts_getrecentposts($linkclass = '', $linkaltclass = '')
	{
		global $post;
		$postArgs = array('category_name'=>'blog');
		$recent_posts = get_posts($postArgs);
		if(empty($recent_posts)) return 'No recent blog posts found.';
		$loopcount = 1;
		$navHTML = "";
		foreach($recent_posts as $post) 
		{
			setup_postdata($post);
			$class = ($loopcount % 2 == 0) ? $linkaltclass : $linkclass;
			$class = (empty($class)) ? '' : ' class="' . $class . '"';
			$postTitle = $post->post_title;
			$formatteddate = mysql2date('M jS', $post->post_date);
			if (strlen($postTitle) > 28)
			{
				$postTitle = substr($postTitle, 0, 25) . "&hellip;";
			}
			$navHTML .= '<li' . $class . '>' . $formatteddate . '- <a href="' . get_permalink() . '">' . $postTitle . '</a></li>';
			$loopcount++;
		}
		return $navHTML;
	}
?>
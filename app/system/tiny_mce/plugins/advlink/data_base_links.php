<?php
//Header functions
	require_once("../../../Connections/connDBA.php");

//Define this as a javascript file
	header ("Content-type: text/javascript");

	if (exist("pages")) {
		$return = "";
		
		echo "var tinyMCELinkList = new Array(";
		
	//Find the current level of a page
		function level($id) {
			if (exist("pages", "id", $id)) {
				$nextPage = query("SELECT * FROM `pages` WHERE `id` = '{$id}'");
				return  "&nbsp;&nbsp;&nbsp;" . level($nextPage['parentPage']);
			}
		}
		
	//Recursively loop through the pages
		function pagesDirectory($level) {
			global $return, $root;
			
			if ($level == "0") {
				$pagesGrabber = query("SELECT * FROM `pages` WHERE `parentPage` = '{$level}' ORDER BY `position` ASC", "raw");
			} else {
				$pagesGrabber = query("SELECT * FROM `pages` WHERE `parentPage` = '{$level}' ORDER BY `subPosition` ASC", "raw");
			}
			
			while ($pages = mysql_fetch_array($pagesGrabber)) {
				if (isset($_GET['id'])) {
				   $parentPage = query("SELECT * FROM `pages` WHERE `id` = '{$_GET['id']}'");
				}
				
				$title = unserialize($pages['content' . $pages['display']]);
				
				$return .= "[\"" . level($pages['parentPage']) . $title['title'] . "\", \"" . $root . "index.php?page=" . $pages['id'] . "\"], ";
				
				if (exist("pages", "parentPage", $pages['id'])) {
					pagesDirectory($pages['id']);
				}
			}
		}
		
		pagesDirectory('0');
		
		echo rtrim($return, ", ") .  ");";
	} else {
		echo "var tinyMCELinkList = new Array([\"Home Page\", \"" . $root . "index.php\"]);";
	}
?>
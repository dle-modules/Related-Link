<?php
/*
=====================================================
Related Link
-----------------------------------------------------
Автор: PunPun
-----------------------------------------------------
Site: https://punpun.name/
-----------------------------------------------------
Copyright (c) 2017 PunPun
=====================================================
Данный код защищен авторскими правами
*/

defined('DATALIFEENGINE') || die( "Hacking attempt!" );

$news_id = is_numeric($news_id) && $news_id > 0 ? intval($news_id) : false;

if(!$news_id)
	return;

$allow_cache = ($config['version_id'] >= '10.2') ? $config['allow_cache'] == '1' : $config['allow_cache'] == "yes";
if(!$allow_cache) {
	if ($config['version_id'] >= '10.2')
		$config['allow_cache'] = '1';
	else 
		$config['allow_cache'] = "yes";
	$is_change = true;
}
$content = dle_cache("news_rl", $config["skin"] . $news_id, false);

if(!$content) {
	if($config['allow_multi_category'])			
		$category_count = "category regexp '[[:<:]](" . $category_id . ")[[:>:]]' AND ";
	else
		$category_count = "category IN ('" . $category_id . "') AND ";

	$count_row = $db->super_query("SELECT COUNT(*) as count, MAX(id) as max_id, MIN(id) as min_id FROM " . PREFIX . "_post WHERE {$category_count} approve=1");

	if($count_row['count'] < 5)
		$limit = $count_row['count'];
	else {
		$limit = 5;

		if($count_row['max_id'] == $news_id || $count_row['min_id'] == $news_id)
			$limit = $limit - 1;
		
		if($count_row['max_id'] == $news_id) {
			$sql_result = $db->query("SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id={$count_row['min_id']} LIMIT 1");
			$not_id = "AND id!={$count_row['min_id']}";
		}
		elseif($count_row['min_id'] == $news_id) {
			$sql_result = $db->query("SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id={$count_row['max_id']} LIMIT 1");
			$not_id = "AND id!={$count_row['max_id']}";
		}
		
		if($count_row['max_id'] == $news_id || $count_row['min_id'] == $news_id) {
			$tpl->load_template( 'related_link.tpl' );
			include (ENGINE_DIR . '/modules/show.custom.php');

			if( $config['files_allow'] ) if( strpos( $tpl->result['content'], "[attachment=" ) !== false ) {
				$tpl->result['content'] = show_attach( $tpl->result['content'], $attachments );
			}
			$prepend_content = $tpl->result['content'];
			unset($tpl->result['content']);
		}
		else
			$prepend_content = "";
	}

	if($count_row['min_id'] != $news_id && $count_row['max_id'] != $news_id)
		$limit_min = 1;

	if($count_row['max_id'] == $news_id)
		$limit_min = $limit;

	if($count_row['max_id'] != $news_id) {
		$tpl->load_template( 'related_link.tpl' );
		
		if($count_row['min_id'] != $news_id)
			$limits = $limit - 1;
		else
			$limits = $limit;
		
		$sql_result = $db->query("SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE approve=1 AND {$category_count} id > $news_id {$not_id} ORDER BY id ASC LIMIT $limits");
		$count_out = $sql_result->num_rows;
		include (ENGINE_DIR . '/modules/show.custom.php');

		if( $config['files_allow'] ) if( strpos( $tpl->result['content'], "[attachment=" ) !== false ) {
			$tpl->result['content'] = show_attach( $tpl->result['content'], $attachments );
		}
		$center_content = $tpl->result['content'];
		unset($tpl->result['content']);
	}

	if($count_row['min_id'] != $news_id) {
		if($count_out > 0 || $count_row['max_id'] == $news_id) {
			$tpl->load_template( 'related_link.tpl' );
			
			$limit_min = $limit - $count_out;
			
			$sql_result = $db->query("SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE approve=1 AND {$category_count} id < $news_id {$not_id} ORDER BY id DESC LIMIT {$limit_min}");

			include (ENGINE_DIR . '/modules/show.custom.php');

			if( $config['files_allow'] ) if( strpos( $tpl->result['content'], "[attachment=" ) !== false ) {
				$tpl->result['content'] = show_attach( $tpl->result['content'], $attachments );
			}
			$end_content = $tpl->result['content'];
			unset($tpl->result['content']);
		}
	}

	if($limit_min == 0 && $count_row['max_id'] != $news_id && $count_row['min_id'] != $news_id)
		$content = $end_content . $prepend_content . $center_content;
	elseif($limit_min == 1 && $count_row['max_id'] != $news_id && $count_row['min_id'] != $news_id)
		$content = $end_content . $prepend_content . $center_content;
	elseif($limit_min > 1 && $count_row['max_id'] != $news_id && $count_row['min_id'] != $news_id) {
		$tpl_file = file(TEMPLATE_DIR . "/related_link.tpl");
		$array_end = explode($tpl_file[0], $end_content);
		$array_end = array_reverse($array_end);
		unset($array_end[count($array_end)-1]);
		$implode_end = $tpl_file[0] . implode($tpl_file[0], $array_end);
		$content = $implode_end . $prepend_content . $center_content;
	}
	elseif($count_row['max_id'] == $news_id) {
		$tpl_file = file(TEMPLATE_DIR . "/related_link.tpl");
		$array_end = explode($tpl_file[0], $end_content);
		$array_end = array_reverse($array_end);
		unset($array_end[count($array_end)-1]);
		$implode_end = $tpl_file[0] . implode($tpl_file[0], $array_end);
		$content = $prepend_content . $implode_end;
	}
	elseif($count_row['min_id'] == $news_id)
		$content = $center_content . $prepend_content;
		
	create_cache("news_rl", $content, $config["skin"] . $news_id, false);
	if($is_change)
		$config['allow_cache'] = false;
}

$tpl->load_template('related_block.tpl');
if(trim($content) != "") {
	$tpl->set("{content}", $content);
	$tpl->set_block( "'\\[content\\](.*?)\\[/content\\]'si", "\\1" );
	$tpl->set_block( "'\\[not-content\\](.*?)\\[/not-content\\]'si", "" );
}
else {
	$tpl->set("{content}", "");
	$tpl->set_block( "'\\[content\\](.*?)\\[/content\\]'si", "" );
	$tpl->set_block( "'\\[not-content\\](.*?)\\[/not-content\\]'si", "\\1" );
}
$tpl->compile('related_block');
$tpl->clear();
echo $tpl->result['related_block'];
?>

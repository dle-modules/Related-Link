<?php
/*
=====================================================
Related Link
-----------------------------------------------------
Author: PunPun
-----------------------------------------------------
Site: https://punpun.name/
-----------------------------------------------------
Copyright (c) 2018 PunPun
=====================================================
Данный код защищен авторскими правами
*/

defined('DATALIFEENGINE') || die("Hacking attempt!");

$news_id = is_numeric($news_id) && intval($news_id) > 0 ? intval($news_id) : false;
$not_id = empty($not_id) ? false : trim(strip_tags(stripslashes($not_id)));

if (!$news_id) {
	return;
}

$limit = is_numeric($limit) && intval($limit) > 3 ? intval($limit) : 5;

if (!$config['allow_cache']) {
	$config['allow_cache'] = '1';
	$is_change = true;
}

$content = dle_cache("news_related_link", $config["skin"] . $news_id . $not_id . $limit, false);
if ($content) {
	echo $content;
} else {
	if ($config['allow_multi_category']) {
		$category = intval($category_id);
		$category = "(category LIKE '{$category},%' OR category = '{$category}')";
	} else {
		$category = "category IN ('" . $category_id . "')";
	}
	
	$category .= $not_id ? " AND id NOT IN('" . str_replace(',', "','", $not_id) . "')" : false;
	
	$tpl->load_template('mod_punpun/related_link/related_link.tpl');
	$sql_calc = $db->super_query("SELECT COUNT(*) as count, MAX(id) as max_id, MIN(id) as min_id FROM " . PREFIX . "_post WHERE {$category} AND approve='1'");
	if ($sql_calc['count']<3) {
		return;
	}
	
	if ($sql_calc['count'] < $limit) {
		$limit = $sql_calc['count'];
	}
		
	if ($sql_calc['min_id'] == $news_id) {
		$limit -= 1;
		$sql_result = $db->query("(SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE approve='1' AND {$category} AND id>'{$news_id}' ORDER BY id ASC LIMIT {$limit}) UNION (SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id='{$sql_calc['max_id']}')");
	} elseif ($sql_calc['max_id'] == $news_id) {
		$limit -= 1;
		$sql_result = $db->query("SELECT * FROM ((SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE approve='1' AND {$category} AND id<'{$news_id}' ORDER BY id DESC LIMIT {$limit}) UNION (SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id='{$sql_calc['min_id']}') ) as r ORDER BY r.id ASC");
	} else {
		$count_back_news = $db->super_query("(SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE approve='1' AND {$category} AND id>'{$news_id}') UNION ALL (SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE approve='1' AND {$category} AND id<'{$news_id}')", true);
		
		if ($count_back_news[0]['count'] > 2 && $count_back_news[1]['count'] >= 2) {
			$limit_back_news = 2;
			$limit -= 2;
		} elseif ($count_back_news[0]['count'] == 2 || $count_back_news[0]['count'] == 1) {
			$limit_back_news = $limit - $count_back_news[0]['count'];
			$limit = $count_back_news[0]['count'];
		} else {
			$limit_back_news = 1;
			$limit -= 1;
		}
		
		$sql_result = $db->query("SELECT * FROM ((SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE approve='1' AND {$category} AND id<'{$news_id}' ORDER BY id DESC LIMIT {$limit_back_news}) UNION (SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE approve='1' AND {$category} AND id>'{$news_id}' ORDER BY id ASC LIMIT {$limit})) as r ORDER BY r.id ASC");
	}

	include ENGINE_DIR . '/modules/show.custom.php';

	if ($config['files_allow']) {
		if (strpos($tpl->result['content'], "[attachment=") !== false) {
			$tpl->result['content'] = show_attach($tpl->result['content'], $attachments);
		}
	}
	
	$tpl->load_template('mod_punpun/related_link/related_block.tpl');

	if (trim($tpl->result['content']) != "") {
		$tpl->set("{content}", $tpl->result['content']);
		$tpl->set_block("'\\[content\\](.*?)\\[/content\\]'si", "\\1");
		$tpl->set_block("'\\[not-content\\](.*?)\\[/not-content\\]'si", "");
	} else {
		$tpl->set("{content}", "");
		$tpl->set_block("'\\[content\\](.*?)\\[/content\\]'si", "");
		$tpl->set_block("'\\[not-content\\](.*?)\\[/not-content\\]'si", "\\1");
	}

	$tpl->compile('related_block');
	$tpl->clear();
	create_cache("news_related_link", $tpl->result['related_block'], $config["skin"] . $news_id . $not_id . $limit, false);
	if ($is_change) {
		$config['allow_cache'] = false;
	}
	echo $tpl->result['related_block'];
}
?>
<?php
defined('DATALIFEENGINE') || die( "Hacking attempt!" );

$news_id = is_numeric($news_id) && $news_id > 0 ? intval($news_id) : false;
if(!$news_id) return;
	
if ($config['allow_multi_category'])			
	$category_count = "category regexp '[[:<:]](" . $category_id . ")[[:>:]]' AND ";
else
	$category_count = "category IN ('" . $category_id . "') AND ";

$count_row = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE {$category_count} approve=1");

if($count_row['count'] < 5) $limit = $count_row['count'];
else
{
	$limit = 5;

	$get_ai = $db->super_query("SELECT MAX(id) as max_id, MIN(id) as min_id FROM " . PREFIX . "_post WHERE {$category_count} approve=1");
	if($get_ai['max_id'] == $news_id || $get_ai['min_id'] == $news_id)
		$limit = $limit - 1;
	
	if($get_ai['max_id'] == $news_id)
	{
		$sql_result = $db->query("SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id={$get_ai['min_id']} LIMIT 1");
	$not_id = "AND id!={$get_ai['min_id']}";
	}
	elseif($get_ai['min_id'] == $news_id)
	{
		$sql_result = $db->query("SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id={$get_ai['max_id']} LIMIT 1");
		$not_id = "AND id!={$get_ai['max_id']}";
	}
	if($get_ai['max_id'] == $news_id || $get_ai['min_id'] == $news_id)
	{
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
$tpl->load_template( 'related_link.tpl' );
if($get_ai['min_id'] != $news_id && $get_ai['max_id'] != $news_id)
{
	$limit = $limit - 1;
	$limit_min = 1;
}
if($get_ai['max_id'] == $news_id)
{
	$order = "ORDER BY id DESC";
	$limit_min = $limit;
}

if($get_ai['max_id'] != $news_id)
{
	$sql_result = $db->query("SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE approve=1 AND {$category_count} id > $news_id {$not_id} ORDER BY id ASC LIMIT $limit");

	include (ENGINE_DIR . '/modules/show.custom.php');

	if( $config['files_allow'] ) if( strpos( $tpl->result['content'], "[attachment=" ) !== false ) {
		$tpl->result['content'] = show_attach( $tpl->result['content'], $attachments );
	}
	$center_content = $tpl->result['content'];
	unset($tpl->result['content']);
}

if($get_ai['min_id'] != $news_id)
{
	$tpl->load_template( 'related_link.tpl' );

	$sql_result = $db->query("SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE approve=1 AND {$category_count} id < $news_id {$not_id} {$order} LIMIT {$limit_min}");

	include (ENGINE_DIR . '/modules/show.custom.php');

	if( $config['files_allow'] ) if( strpos( $tpl->result['content'], "[attachment=" ) !== false ) {
		$tpl->result['content'] = show_attach( $tpl->result['content'], $attachments );
	}
	$end_content = $tpl->result['content'];
}
echo $prepend_content . $center_content . $end_content;
?>
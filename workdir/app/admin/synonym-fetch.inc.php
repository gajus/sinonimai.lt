<?php
$groups = '';
foreach($result['groups'] as $group2)
{
	$groups	.= '<option value="' . $group2['group_id'] . '"' . ($group['group_id'] == $group2['group_id'] ? ' selected="selected"' : '') . '>' . $group2['group_id'] . '</option>';
}

$use_html   = '';


if(empty($synonym['use']))
{
	ini_set('max_execution_time', 90);

	$syllabels	= fb_syllabify($synonym['word']);
	$forms		= fb_work_forms($syllabels['syllabels']);
	
	// set the limit of uses
	$limit			= 10;
	$limit_counter	= 0;
	
	if(!empty($forms))
	{
		$word_root	= $syllabels['syllabels'];
		array_pop($word_root);
		$word_root	= implode('', $word_root);
		
		foreach($works2 as $e)
		{
			$matches	= array();
			
			preg_match_all('/( ' . $word_root . '(' . implode('|', $forms) . ') )/u', $e['text'], $matches, PREG_OFFSET_CAPTURE);
			
			foreach($matches[0] as $m)
			{
				if(++$limit_counter >= $limit)
				{
					break(2);
				}
			
				$use_html   .= '<li>[...] ' . wrap_text(mb_substr($e['text'], utf8_byte_offset_to_unit($e['text'], $m[1])-200, 400), $m[0]) . ' [...]; ' . ' <mark>' . $e['last_name'] . ', ' . $e['first_name'] . ' „' . $e['title'] . '“</mark></li>';
			}
		}
	}
	else
	{
		foreach($works2 as $e)
		{			
			$matches	= array();
		
			preg_match_all('/( ' . $synonym['word'] . ' )/u', $e['text'], $matches, PREG_OFFSET_CAPTURE);
			
			foreach($matches[0] as $m)
			{
				$use_html   .= '<li>[...] ' . wrap_text(mb_substr($e['text'], utf8_byte_offset_to_unit($e['text'], $m[1])-200, 400), $m[0]) . ' [...]; ' . ' <mark>' . $e['last_name'] . ', ' . $e['first_name'] . ' „' . $e['title'] . '“</mark></li>';
			}
		}
	}
}


$authors_str    = '<option' . (empty($synonym['author_id']) ? ' selected="selected"' : '') . '>autorius</option>';

foreach($authors as $a)
{
	$authors_str	.= '<option value="' . $a['author_id'] . '"' . ($a['author_id'] == $synonym['author_id'] ? ' selected="selected"' : '') . '>' . $a['last_name'] . ', ' . $a['first_name'] . '</option>';
}

$works_str	    = '';

if(!empty($synonym['author_id']))
{
	$works	= $db->query("SELECT w1.work_id, w1.title, w1.year FROM works w1 WHERE w1.author_id = {$db->quote($synonym['author_id'])};")->fetchAll(PDO::FETCH_ASSOC);

	foreach($works as $work)
	{
		$works_str	.= '<option value="' . $work['work_id'] . '"' . ($work['work_id'] == $synonym['work_id'] ? ' selected="selected"' : '') . '>' . $work['title'] . ', ' . $work['year'] . '</option>';
	}
}

$properties_str = '';

$assigned_properties    = array();

if(isset($synonym['properties']))
{
	$assigned_properties    = array_keys($synonym['properties']);
}

foreach($properties as $p)
{
	$uid	= $synonym['word_synonym_group_id'] . '-' . $synonym['word'] . '-' . $p['property_id'];

	$properties_str	.= '
	<label for="property' . $uid .'">
		<input id="property' . $uid .'" type="checkbox" name="synonyms[' . $synonym['word_synonym_group_id'] . '][properties][' . $p['property_id'] . ']" value="' . $p['property_id'] . '" '. (in_array($p['property_id'], $assigned_properties) ? ' checked="checked"' : '') .'/>
		<abbr title="' . $p['augmentation'] . '">' . $p['abbreviation'] . '.</abbr>
	</label>';
}

$properties_str	.= '<div class="clear"></div>';
<?php
function mb_ucfirst($string, $e = 'UTF-8')
{
    $first = mb_substr(mb_strtoupper($string, "utf-8"), 0, 1, 'utf-8');
    return $first . mb_substr($string, 1, mb_strlen($string), 'utf-8');
}

function formated_date($string)
{
    $timestamp	    = strtotime($string);
	$months_names   = array('sausio','vasario','kovo','balandžio','gegužės','birželio','liepos','rugpjūčio','rugsėjo','spalio','lapkričio','gruodžio');

    return date('Y', $timestamp) . ' m. ' . $months_names[date('n', $timestamp)-1] . ' ' . date('j', $timestamp) . ' d.';
}

$gather_data		= function($word_str) use ($db)
{
	$result			= array();
	$result['word']	= $db->query("SELECT `w1`.`word_id`, `w1`.`word`, `w1`.`slug` FROM `words` `w1` WHERE `w1`.`word` = {$db->quote($word_str)};")->fetch(PDO::FETCH_ASSOC);

	if(empty($result['word']))
	{
		return FALSE;
	}

	$query	= "
	SELECT
		w1.word_id, w1.word,
		g1.group_id, g1.group,
		p1.property_id, p1.abbreviation, p1.augmentation,
		wsg1.word_synonym_group_id,
		u1.use,
		a1.first_name author_first_name,
		a1.last_name author_last_name,
		w2.title work_title,
		w2.year work_year
	FROM
		words_synonyms ws1

	INNER JOIN words w1 ON w1.word_id = ws1.synonym_id

	INNER JOIN word_synonym_groups wsg1 ON wsg1.word_synonym_id = ws1.word_synonym_id
	INNER JOIN groups g1 ON g1.group_id = wsg1.group_id

	LEFT JOIN word_synonym_group_properties wsgp1 ON wsgp1.word_synonym_group_id = wsg1.word_synonym_group_id
	LEFT JOIN properties p1 ON p1.property_id = wsgp1.property_id

	LEFT JOIN uses u1 ON u1.word_synonym_group_id = wsg1.word_synonym_group_id
	LEFT JOIN works w2 ON w2.work_id = u1.work_id
	LEFT JOIN authors a1 ON a1.author_id = w2.author_id

	WHERE ws1.word_id = {$db->quote($result['word']['word_id'])}

	ORDER BY g1.group_id ASC, w1.word ASC";

	$synonyms	    = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

	$result['groups'] = array();

	// group results
	foreach($synonyms as $s)
	{
		$result['groups'][$s['group_id']]['group_id']											= $s['group_id'];
		$result['groups'][$s['group_id']]['group']												= $s['group'];
		$result['groups'][$s['group_id']]['synonyms'][$s['word_id']]['word_synonym_group_id']   = $s['word_synonym_group_id'];
		$result['groups'][$s['group_id']]['synonyms'][$s['word_id']]['word_id']					= $s['word_id'];
		$result['groups'][$s['group_id']]['synonyms'][$s['word_id']]['word']					= $s['word'];

		if($s['author_first_name'] != NULL)
		{
			$result['groups'][$s['group_id']]['synonyms'][$s['word_id']]['use']					= $s['use'];
			$result['groups'][$s['group_id']]['synonyms'][$s['word_id']]['author_first_name']	= $s['author_first_name'];
			$result['groups'][$s['group_id']]['synonyms'][$s['word_id']]['author_last_name']	= $s['author_last_name'];
			$result['groups'][$s['group_id']]['synonyms'][$s['word_id']]['work_title']		    = $s['work_title'];
			$result['groups'][$s['group_id']]['synonyms'][$s['word_id']]['work_year']		    = $s['work_year'];
		}

		if($s['abbreviation'] !== NULL)
		{
			$result['groups'][$s['group_id']]['synonyms'][$s['word_id']]['properties'][$s['property_id']]	= array('abbreviation' => $s['abbreviation'], 'augmentation' => $s['augmentation']);
		}
	}

	/*if(!empty($_POST['property']))
	{
		$properties	= array_diff($this->db->query('SELECT `property_id` FROM `properties`')->fetchAll(PDO::FETCH_COLUMN), array_keys($_POST['property']));

		foreach($result['groups'] as $id1 => &$g)
		{
			foreach($g['synonyms'] as $id2 => &$s)
			{
				if(isset($s['properties']))
				{
					foreach($properties as $p)
					{
						if(in_array($p, array_keys($s['properties'])))
						{
							unset($result['groups'][$id1]['synonyms'][$id2]);

							if(empty($result['groups'][$id1]['synonyms']))
							{
								unset($result['groups'][$id1]);
							}
						}
					}
				}
			}
		}
	}*/

	return $result;
};

$display_synonyms	= function($query_result, array &$uses = array()) use ($db)
{
	$groups		= $query_result['groups'];

	$html		= '';
	$i			= 0;

	$uses_indexing	= 0;
	//$uses			= array();

	/**
	 * Check if word has any synonyms
	 */
	$stmt	= $db->prepare("SELECT COUNT(word_id) FROM words_synonyms WHERE word_id = ?;");

	$dt	= 0;

	foreach($groups as $group)
	{
		++$dt;

		$synonyms_str   = '';
		foreach($group['synonyms'] as $synonym)
		{

			$stmt->execute(array($synonym['word_id']));
			$has_synonyms   = $stmt->fetch(PDO::FETCH_COLUMN);

			$suffix_str		= '';

			if(!empty($synonym['properties']) || !empty($synonym['use']))
			{
				$suffixes	= array();

				if(!empty($synonym['properties']))
				{
					$properties = array();

					foreach($synonym['properties'] as $property)
					{
						$properties[]   = '<abbr title="' . $property['augmentation'] . '">' . $property['abbreviation'] . '.</abbr>';
					}

					$suffixes[] = '<span class="properties">' . implode(', ', $properties) . '</span>';
				}

				if(!empty($synonym['use']))
				{
					++$uses_indexing;

					$suffixes[] = '<span class="use">' . $synonym['use'] . '<sup id="!/' . $query_result['word']['word'] . '/' . $uses_indexing . '">[<a href="#!/' . $query_result['word']['word'] . '/' . $uses_indexing . '">' . $uses_indexing . '</a>]</sup></span>';
					$uses[]	    = array($synonym['author_first_name'],$synonym['author_last_name'],$synonym['work_title'],$synonym['work_year']);
				}

				$suffix_str	= ' <span class="suffix">(' . implode('; ', $suffixes) . ')</span>';
			}

			$synonyms_str	.= '<dd>' . ($has_synonyms ? '<a href="' . $synonym['word_id'] . '">' . mb_ucfirst($synonym['word']) . '</a>' : mb_ucfirst($synonym['word'])) . $suffix_str . '</dd>';
		}

		$html   .= '
		<dt><span class="index">' . $dt . '.</span> ' . (empty($group['group']) ? '<span class="empty-name">aprašymo nėra</span>' : $group['group']) . '</dt>' . $synonyms_str;
	}

	return '<dl>' . $html . '</dl>';
};

function hp_sanitize_slug($string)
{
    $string	= mb_strtolower($string, 'UTF-8');
    $string	= preg_replace('/\p{Z}+/u', '-', $string);
    $string	= trim($string, " \t. -");
    $string	= preg_replace('/--+/', '-', $string);
    $string	= str_replace(array('"','\\','/','&#34;'), array('','','',''), $string);
    $string	= trim($string, '-');

    return $string;
}

$get	= function($query)
{
	global $db, $gather_data, $display_synonyms;

	$output_str	= '';

	$display		= array();
	$uses			= array();

	$query_result	= $gather_data($query);

	if(empty($query_result))
	{
		return FALSE;
	}

	$suggest_box	= function($word) use ($db)
	{
		if(empty($_SESSION['ay']['auth']))
		{
			return;
		}

		$earlier_suggestions	= $db->query("
		SELECT
			`w1`.`word_id`,
			`w1`.`word`
		FROM
			`suggestions` `s1`
		INNER JOIN
			`words` `w1`
		ON
			`w1`.`word_id` = `s1`.`suggestion_id`
		WHERE
			`s1`.`user_id` = {$db->quote($_SESSION['ay']['auth']['id'])} AND
			`s1`.`word_id` = {$db->quote($word['word_id'])}
		")->fetchAll(PDO::FETCH_ASSOC);

		$es_string	= '';

		foreach($earlier_suggestions as $es)
		{
			$es_string	.= '<div class="suggestion" data-id="' . $es['word_id'] . '">' . $es['word'] . '</div>';
		}

		return '
		<div class="suggestions-box">
			<h4>Žinote daugiau sinonimų žodžiui <span class="highlight">' . $word['word'] . '</span>?</h4>

			<div class="input" data-id="' . $word['word_id'] . '">' . $es_string . '</div>

			<div class="paragraph">
				<p>Pasidalinkite savo žiniomis. Įveskite kitus žodžius, kurie yra sinonimai žodžiui <span class="highlight">' . $word['word'] . '</span>. Daugiau nieko nereikia daryti – sistema automatiškai įtraukia žodžius į pasiūlymų duomenų bazę. Tik pateikiami žodžiai gali būti įtraukti į duomenų bazę.</p>
				<p>Jūs galite bet kada sugrįžtį į šitą puslapį jei norite pašalinti ankščiau pasiūlytą žodį.</p>
			</div>
		</div>
		';
	};

	#die(var_dump( $query_result ));

	if(!empty($query_result['groups']))
	{
		$display[]	= '<div class="word">' . $query_result['word']['word'] . '</div>' . $display_synonyms($query_result, $uses);
	}

	$display[]	=  $suggest_box($query_result['word']);

	$synonymous	= $db->query("SELECT w1.word_id, w1.word FROM words w1 INNER JOIN words_synonyms ws1 ON ws1.word_id = w1.word_id WHERE ws1.synonym_id = {$db->quote($query_result['word']['word_id'])};")->fetchAll(PDO::FETCH_ASSOC);

	foreach($synonymous as $s)
	{
		$result		= $gather_data($s['word']);

		# avoid repeating the same result
		if($result['word']['word_id'] == $query_result['word']['word_id'])
		{
			continue;
		}

		$display[]	= '<div class="word">' . $result['word']['word']. '</div>' . $display_synonyms($result, $uses);
		$display[]	=  $suggest_box($result['word']);
	}

	$output_str .= implode('<div class="break"></div>', $display);

	if(!empty($uses))
	{
		$i		    = 0;
		$reference_str  = '';

		foreach($uses as $use)
		{
			++$i;

			$reference_str	.= '<li id="note-' . $i . '"> <sup><a href="#!/' . $query_result['word']['word'] . '/' . $i . '">^</a> [' . $i . ']</sup>„' . $use[2] . '“ ' .$use[3] . ', ' . $use[0] . ' ' . $use[1] . '</li>';
		}

		$output_str.= '
		<div class="clear"></div>

		<div id="reference">
			<h4>Autorinė rodyklė</h4>
			<ol id="notes">' . $reference_str . '</ol>
		</div>';
	}

	return array('word' => $query_result['word'], 'html' => $output_str);
};

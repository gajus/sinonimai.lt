<?php
ini_set('memory_limit', '500M');
set_time_limit(30);

if(!empty($_POST['hide']))
{
	$db->exec("UPDATE `words` SET `hidden`=1 WHERE `word_id`={$db->quote($_POST['hide'])};");

	exit;
}

// return all words that don't have synonyms
$group_1	= $db->query("
SELECT
	`w1`.`word_id`,
	`w1`.`word`
FROM
	`words` `w1`
LEFT JOIN
	`words_synonyms` `ws1`
ON
	`w1`.`word_id` = `ws1`.`word_id`
WHERE
	`ws1`.`word_synonym_id` IS NULL AND
	`w1`.`hidden` = 0
ORDER BY
	`w1`.`word` ASC;
")->fetchAll(PDO::FETCH_ASSOC);

// return all words that are not synonyms
$group_2	= $db->query("
SELECT
	`w1`.`word_id`,
	`w1`.`word`
FROM
	`words` `w1`
LEFT JOIN
	`words_synonyms` `ws1`
ON
	`w1`.`word_id` = `ws1`.`synonym_id`
WHERE
	`ws1`.`word_synonym_id` IS NULL AND
	`w1`.`hidden` = 0
ORDER BY
	`w1`.`word` ASC;
")->fetchAll(PDO::FETCH_ASSOC);

$group_3	= array_uintersect($group_1, $group_2, function($a, $b){ if($a['word_id'] == $b['word_id']){ return 0; } return $a['word_id'] > $b['word_id'] ? 1 : -1; });

$group_3_count	= count($group_3);

shuffle($group_3);
$group_3	= array_slice($group_3, 0, 1000);
?>
<p>Isviso duomenu bazeje yra <?=$group_3_count?> zodziai-naslaiciai.</p>

<table class="default history">
<thead>
<tr>
	<th>Žodis</th>
</tr>
</thead>
<tbody>
<?php
foreach($group_3 as $w):
?>
<tr>
	<td><?=$w['word']?> <span class="hide-button" data-id="<?=$w['word_id']?>">paslėpti</span></td>
</tr>
<?php endforeach;?>
</tbody>
</table>
<?php
require_once 'ay/includes/bootstrap.inc.php';

ini_set('memory_limit', '500M');

// return all words that don't have synonyms
$group_1	= $db->query("
SELECT
	`w1`.`word`
FROM
	`words` `w1`
INNER JOIN
	`words_synonyms` `ws1`
ON
	`w1`.`word_id` = `ws1`.`word_id`;
")->fetchAll(PDO::FETCH_COLUMN);

// return all words that are not synonyms
$group_2	= $db->query("
SELECT
	`w1`.`word`
FROM
	`words` `w1`
LEFT JOIN
	`words_synonyms` `ws1`
ON
	`w1`.`word_id` = `ws1`.`synonym_id`;
")->fetchAll(PDO::FETCH_COLUMN);

$group_3	= array_unique(array_intersect($group_1, $group_2));

header('Content-Type: text/plain');

foreach ($group_3 as $w) {
	echo 'http://sinonimai.lt/#!/' . $w . "\r\n";
}

<?php
require 'ay/includes/bootstrap.inc.php';

if(empty($_POST['action']))
{
	exit;
}

header('Content-type: application/json');

switch($_POST['action'])
{
	case 'tag-auto-complete':
		$suggestions	= $db->query("SELECT `word_id` `id`, `word` `value` FROM `words` WHERE `word` LIKE {$db->quote($_POST['data']['query'] . '%')} LIMIT 5;")->fetchAll(PDO::FETCH_ASSOC);

		echo json_encode($suggestions);

		break;

	case 'add-suggestion':
		if(empty($_SESSION['ay']['auth']))
		{
			return;
		}

		$db->exec("REPLACE INTO `suggestions` SET `user_id`={$db->quote($_SESSION['ay']['auth']['id'], PDO::PARAM_INT)}, `word_id`={$db->quote($_POST['data']['entry_id'], PDO::PARAM_INT)}, `suggestion_id`={$db->quote($_POST['data']['word']['id'], PDO::PARAM_INT)}, `entry_timestamp`=UNIX_TIMESTAMP();");

		break;

	case 'remove-suggestion':
		if(empty($_SESSION['ay']['auth']))
		{
			return;
		}

		$db->exec("DELETE FROM `suggestions` WHERE `user_id`={$db->quote($_SESSION['ay']['auth']['id'], PDO::PARAM_INT)} AND `word_id`={$db->quote($_POST['data']['entry_id'], PDO::PARAM_INT)} AND `suggestion_id`={$db->quote($_POST['data']['word']['id'], PDO::PARAM_INT)};");
		break;

	case 'search-auto-complete':
		$synonyms	= $db->query("
		SELECT
			DISTINCT w1.word
		FROM
			words w1
		INNER JOIN
			words_synonyms ws1
		ON
			ws1.word_id = w1.word_id
		WHERE
			w1.word LIKE {$db->quote($_POST['query'] . '%')}
		UNION
		SELECT
			DISTINCT w1.word
		FROM
			words w1
		INNER JOIN
			words_synonyms ws1
		ON
			ws1.synonym_id = w1.word_id
		WHERE
			w1.word LIKE {$db->quote($_POST['query'] . '%')}
		ORDER BY
			LENGTH(word),
			word LIKE BINARY {$db->quote($_POST['query'] . '%')} DESC
		LIMIT
			10;")->fetchAll(PDO::FETCH_COLUMN);

		echo json_encode($synonyms);

	break;

	case 'display-entry':
		$data	= $get($_POST['query']);

		$db->exec("INSERT INTO `most_searched` (`word_id`) VALUES ({$db->quote($data['word']['word_id'])}) ON DUPLICATE KEY UPDATE `count`=`count`+1;");

		if(!empty($_SESSION['last_successful_search']) && $_SESSION['last_successful_search'] != $data['word']['word_id'])
		{
			$db->exec("INSERT INTO `related_searches` (`came_to_word`,`came_from_word`) VALUES ({$db->quote($data['word']['word_id'])},{$db->quote($_SESSION['last_successful_search'])}) ON DUPLICATE KEY UPDATE `count`=`count`+1;");
		}

		$_SESSION['last_successful_search']	= $data['word']['word_id'];

		echo json_encode($data);

		break;
}

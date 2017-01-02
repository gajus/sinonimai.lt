<?php
session_start();

$db = new PDO(
  'mysql:dbname=' . getenv('DATABASE_NAME') . ';host=' . getenv('DATABASE_HOST'),
  getenv('DATABASE_USER'),
  getenv('DATABASE_PASSWORD'),
  [
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8' COLLATE 'utf8_unicode_ci';"
  ]
);

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

mb_language('uni');
mb_http_output('UTF-8');
mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

require_once 'helpers.inc.php';

$template		= array
(
	'file'	=> 'auth'
);

$user			= FALSE;

if(!empty($_GET['action']) && $_GET['action'] == 'signout')
{
	unset($_SESSION['auth']);

	hp_redirect('index.php');
}

if(!empty($_SESSION['auth']['id']))
{
	$user				= $db->query("SELECT `id`, `email`, `rate_1`, `rate_2`, `full_name` FROM `admins` WHERE `id`={$db->quote($_SESSION['auth']['id'])};")->fetch(PDO::FETCH_ASSOC);
	$user['updates']	= $db->query("
	SELECT
		`w1`.`word_id`, `w1`.`word`, `uh1`.`date`, `uh1`.`type`, `uh1`.`data`
	FROM
		`updates_history` `uh1`
	INNER JOIN
		`words` `w1`
	ON
		`w1`.`word_id` = `uh1`.`word_id`
	WHERE
		`uh1`.`user_id`={$db->quote($user['id'])}
	ORDER BY
		`date` DESC;
	")->fetchAll(PDO::FETCH_ASSOC);
}

$controllers	= array
(
	'synonym'	=> 'Sinonimų žodynas',
	'log'		=> 'Pakeitimų istorija',
	'lost'		=> 'Našlaičiai',
	'contest'	=> 'Konkursas'
);


if($user)
{
	if($user['id'] == 1)
	{
		$controllers	= array_merge($controllers, array
		(
			'author'	=> 'Autoriai',
			'work'		=> 'Darbai',
		));
	}

	$controller			= !empty($_GET['controller']) && $controllers[$_GET['controller']] ? $_GET['controller'] : 'synonym';

	$template['file']	= $controller;
}
else
{
	unset($_SESSION['auth']);
}

if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
	$_POST	= array();
}

ob_start();
require_once 'templates/' . $template['file'] . '.tpl.php';
$tpl_body	= ob_get_clean();

require_once 'templates/layout.tpl.php';

unset($_SESSION['hp']['flash']);

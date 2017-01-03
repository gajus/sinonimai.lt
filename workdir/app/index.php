<?php
require 'ay/includes/bootstrap.inc.php';

if(!empty($_GET['connect']))
{
	$authenticate_user();

	if($user)
	{
		ay_redirect(AY_DOMAIN);
	}
}

if(!empty($_GET['state']))
{
	ay_redirect(AY_DOMAIN);
}

header('Content-Type: text/html; charset=utf-8');

$template	= array();
$display	= 0;

if($_SERVER['REQUEST_URI'] == '/naudojimo-salygos.html')
{
	$display	= 9;
}
elseif($_SERVER['REQUEST_URI'] == '/kontaktai.html')
{
	$display	= 8;
}
elseif($_SERVER['REQUEST_URI'] == '/apie-sinonimu-zodyna.html')
{
	$display	= 7;
}
elseif($_SERVER['REQUEST_URI'] == '/sutrumpinimu-rodykle.html')
{
	$display	= 6;
}
elseif($_SERVER['REQUEST_URI'] == '/dekui.html')
{
	$display	= 10;
}
elseif($_SERVER['REQUEST_URI'] == '/konkursas.html')
{
	$display	= 11;
}
elseif($_SERVER['REQUEST_URI'] == '/konkurso-salygos.html')
{
	$display	= 12;
}
else
{
	$query		= trim($_SERVER['REQUEST_URI'], '/');

	if(!empty($query))
	{
		$query_match	= $db->query("SELECT `word` FROM `words` WHERE `word`={$db->quote($query)};")->fetch(PDO::FETCH_COLUMN);

		if($query_match)
		{
			header('Location: /#!/' . $query, TRUE, 301);

			exit;
		}
		else
		{
			header('HTTP/1.0 404 Not Found');
		    #echo '<h1>404 Not Found</h1>';
		    #echo 'The page that you have requested could not be found.';
		    #exit;

		}

	}
}

switch($display)
{
	case 6:
		// <editor-fold defaultstate="collapsed" desc="Static page: Sutrumpinimų rodyklė">
		$template	= require 'templates/abbreviations.tpl.php';
		break;

	case 7:
		// Static page: Apie sinonimų žodyną
		$template	= require 'templates/about.tpl.php';
		break;

	case 8:
		// Static page: Kontaktai
		$template	= require 'templates/contact.tpl.php';
		break;

	case 9:
		// Static page: Naudojimo sąlygos
		$template	= require 'templates/terms.tpl.php';
		break;

	case 10:
		// Static page: Naudojimo sąlygos
		$template	= require 'templates/thankyou.tpl.php';
		break;

	case 11:
		$template	= require 'templates/contest.tpl.php';
		break;

	case 12:
		$template	= require 'templates/rules.tpl.php';
		break;
}

ob_start();
switch($display)
{
	case 0:
		require 'templates/search.tpl.php';
		break;

	case 6:
	case 7:
	case 8:
	case 9:
	case 10:
	case 11:
	case 12:
		?>
		<h1><?=$template['title']?></h1>
		<h2><?=$template['sub-title']?></h2>

		<?=$template['body']?>
		<?php
		break;
}
$body	= ob_get_clean();

ob_start();
require_once __DIR__ . '/templates/default.layout.tpl.php';
$body	= ob_get_clean();

echo $body;

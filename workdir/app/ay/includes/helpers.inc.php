<?php
/**
 * @author Gajus Kuizinas <g.kuizinas@anuary.com>
 * @copyright Copyright (c) 2011, Anuary Ltd
 * @version 1.0
 */
function ay_join_path()
{
    $args	= func_get_args();

    foreach($args as $arg)
    {
		$targs[] = trim($arg, DIRECTORY_SEPARATOR);
    }

    $path 	= implode(DIRECTORY_SEPARATOR, $targs);

    if($args[0][0] == DIRECTORY_SEPARATOR)
    {
		$path = DIRECTORY_SEPARATOR . $path;
    }

    return $path;
}

/**
 * A shorthand function.
 * @see ay_join_path()
 */
function ay_path()
{
	$args	= func_get_args();

	array_unshift($args, AY_ROOT);

	return call_user_func_array('ay_join_path', $args);
}

/**
 * @author Gajus Kuizinas <g.kuizinas@anuary.com>
 * @copyright Copyright (c) 2011, Anuary Ltd
 * @version 1.1
 */
function ay_message($message, $type = AY_MESSAGE_ERROR)
{
    $_SESSION['ay']['flash']['messages'][$type][]	= $message;
}

/**
 * Prepares SQL INSERT/UPDATE value string.
 * @author Gajus Kuizinas <g.kuizinas@anuary.com>
 * @copyright Copyright (c) 2011, Anuary Ltd
 * @version 1.0.1
 */
function ay_build_query(array $data)
{
	global $db;

	if(empty($data))
	{
		throw new \Exception('Query cannot be empty.');
	}

	$values	= array();

	foreach($data as $k => $v)
	{
		$values[]	= " `{$k}`={$db->quote($v)}";
	}

	return implode(', ', $values);
}

/**
 * @author Gajus Kuizinas <g.kuizinas@anuary.com>
 * @copyright Copyright (c) 2011, Anuary Ltd
 * @version 1.0
 */
function ay_get_referrer()
{
	if(!empty($_SERVER['HTTP_REFERER']))
	{
		return $_SERVER['HTTP_REFERER'];
	}

	return AY_DOMAIN;
}

/**
 * @author Gajus Kuizinas <g.kuizinas@anuary.com>
 * @copyright Copyright (c) 2011, Anuary Ltd
 * @version 1.0.2
 */
function ay_redirect($url = AY_REDIRECT_REFERRER, $message_text = NULL, $message_type = AY_MESSAGE_ERROR)
{
    if($url === AY_REDIRECT_REFERRER)
    {
		$url	= ay_get_referrer();
    }
    elseif(strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0)
    {
    	$url	= AY_DOMAIN . '/' . $url;
    }

    if($message_text !== NULL)
    {
		ay_message($message_text, $message_type);
    }

	if(!headers_sent())
	{
		header('Location: ' . $url);
	}

	echo '<meta http-equiv="Refresh" content="5;url=' . $url . '" /><script type="text/javascript">window.location.href="' . $url . '";</script>';

	exit;
}

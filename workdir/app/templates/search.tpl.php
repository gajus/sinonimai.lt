<?php
if(!empty($_GET['_escaped_fragment_']))
{
	$query			= mb_substr(urldecode($_GET['_escaped_fragment_']), 1);

	$get_browser	= get_browser(NULL, TRUE);

	$result	= $get($query);

	if(!$result)
	{
		header('Location: ' . $locator->url(), TRUE, 301);

		exit;
	}

	if((bool) $get_browser['javascript'])
	{
		header('Location: /#!/' . $query, TRUE, 301);
	}

	$title	= 'Sinonimai žodžiui ' . $result['word']['word'] . ' – Sinonimų žodynas';

	$result	= $result['html'];
}
?>

<div id="search">
	<h1>Sinonimų žodynas</h1>
	<h2>Sinonimų paieška</h2>

	<?php if(empty($result)):?>
	<form action="" method="post" name="search">
		<div class="row">
			<div class="left">
				<label id="call">Paieškos raktažodis</label>
			</div>
			<div class="right">
				<input type="text" name="call" id="call" class="search" placeholder="įveskite paieškos raktažodį" value="" autocomplete="off" />
				<input type="hidden" name="query" />
			</div>
			<div class="clear"></div>
		</div>
	</form>
	<?php endif;?>
</div>

<?php
if(empty($result))
{
	require_once __DIR__ . '/home.inc.tpl.php';
}
?>

<div id="result"><?php if(!empty($result)){ echo $result; } ?></div>

<div class="clear"></div>

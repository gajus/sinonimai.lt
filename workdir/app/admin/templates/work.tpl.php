<?php
$input			= filter_var_array($_POST, array
(
	'author_id'	=> FILTER_SANITIZE_STRING,
	'title'		=> FILTER_SANITIZE_STRING,
	'year'		=> FILTER_SANITIZE_STRING,
	'text'		=> FILTER_SANITIZE_STRING,
));

$input['text']	= preg_replace('/[\s]+/', ' ', $input['text']);

if($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_SESSION['hp']['flash']['input']))
{
	$input		= $_SESSION['hp']['flash']['input'];
}

if($_SERVER['REQUEST_METHOD'] === 'POST')
{		
	if(empty($_SESSION['hp']['flash']['messages'][HP_M_ERROR]))
	{		
		$db->exec("INSERT INTO `works` SET " . hp_generate_update_str($input) . ";");
		
		hp_redirect(NULL, 'Darbas įtrauktas į duomenų bazę.', HP_M_SUCCESS);
	}
	
	$_SESSION['hp']['flash']['input']	= $input;
	
	hp_redirect();
}

if(!empty($_GET['action']) && $_GET['action'] == 'delete')
{
	$work	= $db->query("SELECT `work_id` FROM `works` WHERE `work_id`={$db->quote($_GET['id'])};")->fetch(PDO::FETCH_ASSOC);

	$db->exec("DELETE FROM `works` WHERE `work_id`={$db->quote($work['work_id'])};");

    // Find all quotes taken from this work and delete them
    $uses   = $db->query("SELECT `use_id` FROM uses WHERE `work_id`={$db->quote($work['work_id'])};")->fetchAll(PDO::FETCH_COLUMN);
	
	if($uses)
	{
		$db->exec("DELETE FROM `word_synonym_group_uses` WHERE `use_id` IN (" . implode(',', $uses) . ");");
	}
	
    $db->exec("DELETE FROM `uses` WHERE `work_id`={$db->quote($work['work_id'])};");
	
	hp_redirect(NULL, 'Darbas pašalintas iš duomenų bazės.', HP_M_SUCCESS);
}

$works  	= $db->query("SELECT w1.work_id,w1.title,w1.year,w1.text, a1.first_name,a1.last_name FROM works w1 INNER JOIN authors a1 ON a1.author_id = w1.author_id;")->fetchAll(PDO::FETCH_ASSOC);

$authors	= $db->query("SELECT author_id, first_name, last_name FROM authors ORDER BY last_name ASC, first_name ASC;")->fetchAll(PDO::FETCH_ASSOC);
?>

<form action="index.php?controller=work" method="post">
	<?=hp_row('text', 'Kūrinio pavadinimas', 'title')?>
				
	<?php
	$options	= array();
	
	foreach($authors as $a)
	{
		$options[$a['author_id']]	= $a['last_name'] . ', ' . $a['first_name'];
	}
	?>
	
	<?=hp_row('select', 'Autorius', 'author_id', array('options' => $options))?>
	<?=hp_row('text', 'Metai', 'year')?>
	<?=hp_row('textarea', 'Turinys', 'text')?>
	
	<input type="submit" value="Įtraukti" />
</form>

<table class="default margin-top">
	<thead>
		<tr>
			<th class="short">#</th>
			<th class="short">Metai</th>
			<th class="short">Autorius</th>
			<th>Pavadinimas</th>
			<th class="short"></th>
		</tr>
	</thead>
	<tbody>
	<?php	
	foreach($works as $work)
	{
	echo '
	<tr class="zebra">
		<td>' . $work['work_id'] . '</td>
		<td>' . $work['year'] . '</td>
		<td>' . mb_substr($work['last_name'], 0, 1) . '., ' . $work['first_name'] . '</td>
		<td><a href="index.php?editWork=' . $work['work_id'] . '">' . $work['title'] . '</a></td>
		<td><a href="index.php?controller=work&action=delete&id=' . $work['work_id'] . '">ištrinti</a></td>
	</tr>	
	';
	}
	?>
	</tbody>
</table>
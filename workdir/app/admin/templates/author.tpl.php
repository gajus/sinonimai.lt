<?php
$input		= filter_var_array($_POST, array
(
	'first_name'	=> FILTER_SANITIZE_STRING,
	'last_name'		=> FILTER_SANITIZE_STRING,
));

if($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_SESSION['hp']['flash']['input']))
{
	$input			= $_SESSION['hp']['flash']['input'];
}

if($_SERVER['REQUEST_METHOD'] == 'POST')
{	
	if(empty($input['first_name']))
	{
		hp_message('<mark>Autoriaus vardas</mark> negali būti paliktas tusčias.', HP_M_ERROR);
	}
	
	if(empty($input['last_name']))
	{
		hp_message('<mark>Autoriaus pavardė</mark> negali būti palikta tusčia.', HP_M_ERROR);
	}
	
	if(empty($_SESSION['hp']['flash']['messages'][HP_M_ERROR]))
	{	
		$db->exec("INSERT INTO `authors` SET " . hp_generate_update_str($input) . ";");
		
		hp_redirect(NULL, 'Autorius įtrauktas į duomenų bazę.', HP_M_SUCCESS);
	}
	
	$_SESSION['hp']['flash']['input']	= $input;
	
	hp_redirect();
}

/*elseif(!empty($_GET['delete-author']))
{
    $db->exec("DELETE FROM authors WHERE author_id={$db->quote($_GET['deleteAuthor'])};");

    // Get all author' works
    $works	= $db->query("SELECT work_id FROM works WHERE author_id={$db->quote($_GET['deleteAuthor'])};")->fetchAll(PDO::FETCH_ASSOC);

    foreach($works as $work)
    {
		// Delete work
		$db->exec("DELETE FROM works WHERE work_id={$db->quote($work['work_id'])};");
	
		// Find all quotes taken from this work and delete them
		$uses	= $db->query("SELECT use_id FROM uses WHERE work_id={$db->quote($work['work_id'])};")->fetchAll(PDO::FETCH_ASSOC);
	
		$db->exec("DELETE FROM uses WHERE work_id={$db->quote($work['work_id'])};");
	
		// Remove relations between synonyms and removed quotes
		foreach($uses as $use)
		{
		    $db->exec("DELETE FROM word_synonym_group_uses WHERE use_id={$db->quote($use['use_id'])};");
		}
    }

	hp_message('Autorius pašalintas iš duomenų bazės.', 'success');
	
    hp_redirect();
}*/

$authors	= $db->query("SELECT author_id, first_name, last_name FROM authors ORDER BY last_name ASC, first_name ASC;")->fetchAll(PDO::FETCH_ASSOC);
?>

<form action="index.php?controller=author" method="post">
	<?=hp_row('text', 'Vardas', 'first_name')?>
	<?=hp_row('text', 'Pavardė', 'last_name')?>
	
	<input type="submit" value="Įtraukti" />
</form>

<table class="default margin-top">
	<thead>
	    <tr>
			<th class="short">#</th>
			<th colspan="2" class="regular">Vardas</th>
			<th colspan="2" class="regular">Pavardė</th>
			<th class="short"></th>
	    </tr>
	</thead>
	<tbody>
	    <?php
	    foreach($authors as $a)
	    {
			echo '
			<tr class="zebra">
			    <td>' . $a['author_id'] . '</td>
			    <td colspan="2">' . $a['first_name'] . '</td>
			    <td colspan="2">' . $a['last_name'] . '</td>
			    <td><a href="index.php?controller=author&action=delete&id=' . $a['author_id'] . '">ištrinti</a></td>
			</tr>
			';
	    }
	    ?>
	</tbody>
</table>
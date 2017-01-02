<?php
if(isset($_POST['update-synonyms']))
{
	$root_word_id	= $db->query("SELECT `word_id` FROM `words` WHERE `word` = {$db->quote($_GET['query'])}")->fetch(PDO::FETCH_COLUMN);
	
	if(!$root_word_id)
	{
		hp_redirect(NULL, 'Klaida. Žodis nerastas.');
	}
	
	$type	= empty($_POST['full_update']) ? 4 : 1;
	
	$db->exec("INSERT INTO `updates_history` (`word_id`, `user_id`, `date`, `type`) VALUES ({$db->quote($root_word_id)}, {$db->quote($user['id'])}, {$db->quote($_SERVER['REQUEST_TIME'])}, {$db->quote($type)});");

	$group_sql		= $db->prepare("UPDATE `groups` SET `group`=:group WHERE `group_id`=:id;");
	
    foreach($_POST['groups'] as $id => $group)
    {
		$group_sql->execute(array('id' => $id, 'group' => $group));
    }
	
	$use_examples_values		= array();
	$use_examples_delete_values	= array();
	$delete_groups_values		= array();
	$properties_delete_values	= array();
	$properties_values			= array();
	
    foreach($_POST['synonyms'] as $word_synonym_group_id => $array)
    {
		// check if group_id was changed; If group_id was changed and there are no synonyms left in previous group, then remove it
		$wsg			= $db->query("SELECT `group_id` FROM `word_synonym_groups` WHERE `word_synonym_group_id` = {$db->quote($word_synonym_group_id)};")->fetch(PDO::FETCH_ASSOC);
	
		$db->exec("UPDATE `word_synonym_groups` SET `group_id` = {$db->quote($array['group_id'])} WHERE `word_synonym_group_id` = {$db->quote($word_synonym_group_id)};");
	
		// update properties
		$properties_delete_values[]	= $db->quote($word_synonym_group_id);
	
		if(!empty($array['properties']))
		{	
			foreach($array['properties'] as $property_id)
			{
				$properties_values[]		= "({$db->quote($word_synonym_group_id)},{$db->quote($property_id)})";
			}	
		}
		
		// delete old group id
		if(!$db->query("SELECT COUNT(`word_synonym_id`) FROM `word_synonym_groups` WHERE `group_id` = {$db->quote($wsg['group_id'])}")->fetch(PDO::FETCH_COLUMN))
		{
			$delete_groups_values[]			= $db->quote($wsg['group_id']);
		}
		
		// provide use example
		if(empty($array['use']['text']))
		{
			$use_examples_delete_values[]	= $db->quote($word_synonym_group_id);
		}
		else
		{
			$use_examples_values[]			= "({$db->quote($word_synonym_group_id)}, {$db->quote($array['use']['text'])}, {$db->quote($array['use']['work_id'])})";
		}
    }
    
    if(!empty($properties_delete_values))
    {
    	$db->exec("DELETE FROM `word_synonym_group_properties` WHERE `word_synonym_group_id` IN (" . implode(',', $properties_delete_values) . ");");
    }
    
    if(!empty($delete_groups_values))
    {
    	$db->exec("DELETE FROM `groups` WHERE `group_id` IN (" . implode(',', $delete_groups_values) . ");");
    }
    
    if(!empty($use_examples_delete_values))
    {
    	$db->exec("DELETE FROM `uses` WHERE `word_synonym_group_id` IN (" . implode(',', $use_examples_delete_values) . ");");
    }
     
    if(!empty($properties_values))
    {
    	$db->exec("INSERT INTO word_synonym_group_properties (word_synonym_group_id,property_id) VALUES " . implode(',', $properties_values) . ";");
    }
    
    if(!empty($use_examples_values))
    {
    	$db->exec("REPLACE INTO `uses` (`word_synonym_group_id`, `use`, `work_id`) VALUES " . implode(',', $use_examples_values) . ";");
    }
    
    hp_redirect(NULL, 'Žodis sėkmingai atnaujintas.', HP_M_SUCCESS);
}
elseif(isset($_POST['add-new-synonym']))
{
	if(empty($_POST['word']))
	{
		hp_redirect(NULL, 'Įveskite žodį, kurį norite įtraukti kaip sinonimą.', HP_M_ERROR);
	}
	
	// see if the synonym we are trying to add is already in our dictionary
    $word_id	= $db->query("SELECT `word_id` FROM `words` WHERE `word`={$db->quote($_POST['word'])};")->fetch(PDO::FETCH_COLUMN);
    #$word_id	= $db->query("SELECT * FROM `words` WHERE `word`={$db->quote($_POST['word'])};")->fetch(PDO::FETCH_ASSOC);
	
    if(!$word_id)
    {
		$db->exec("INSERT INTO words (word) VALUES ({$db->quote($_POST['word'])});");
		
		hp_message('Naujas žodis ' . $db->quote($_POST['word']) . ' įtrauktas į duomenų bazę.', HP_M_SUCCESS);
		
		$word_id    = $db->lastInsertId();
    }
	
	// see if this a new synonyms meaning group
    $group_id	= $_POST['group_id'];

    if($group_id == 0)
    {
		$db->exec("INSERT INTO `groups` (`group`) VALUES (NULL);");
		
		$group_id    = $db->lastInsertId();
    }
	
	// see if the mother word already in the database
	// generally shouldn't happen
    $root_word_id	= $db->query("SELECT `word_id` FROM `words` WHERE `word` = {$db->quote($_GET['query'])};")->fetch(PDO::FETCH_COLUMN);

    if(!$root_word_id)
    {
		$db->query("INSERT INTO `words` (`word`,`slug`) VALUES ({$db->quote($_GET['query'])}, {$db->quote(hp_sanitize_slug($_GET['query']))});");
		
		$root_word_id    = $db->lastInsertId();
    }

    $db->exec("INSERT INTO `words_synonyms` (`word_id`, `synonym_id`) VALUES ({$db->quote($root_word_id)}, {$db->quote($word_id)});");
    
    $word_synonym_id	= $db->lastInsertId();

    $db->exec("INSERT INTO `word_synonym_groups` (`word_synonym_id`, `group_id`) VALUES ({$db->quote($word_synonym_id)}, {$db->quote($group_id)});");
	
	$db->exec("INSERT INTO `updates_history` (`word_id`, `user_id`, `date`, `type`, `data`) VALUES ({$db->quote($root_word_id)}, {$db->quote($user['id'])}, {$db->quote($_SERVER['REQUEST_TIME'])}, 2, {$db->quote($word_id)})");
	
	hp_redirect(NULL, 'Naujas sinonimas įtrauktas į duomenų bazę.', HP_M_SUCCESS);
}
elseif(!empty($_GET['delete-synonym']))
{
    // Get word_synonym group_id
    $wsg	= $db->query("SELECT `group_id`, `word_synonym_id` FROM `word_synonym_groups` WHERE `word_synonym_group_id` = {$db->quote($_GET['delete-synonym'])};")->fetch(PDO::FETCH_ASSOC);
    
    list($word_id, $synonym_id)	= $db->query("SELECT `word_id`, `synonym_id` FROM `words_synonyms` WHERE `word_synonym_id`={$db->quote($wsg['word_synonym_id'])};")->fetch(PDO::FETCH_NUM);
    
    $db->exec("INSERT INTO `updates_history` (`word_id`, `user_id`, `date`, `type`, `data`) VALUES ({$db->quote($word_id)}, {$db->quote($user['id'])}, {$db->quote($_SERVER['REQUEST_TIME'])}, 3, {$db->quote($synonym_id)})");
    
    // Delete relation link between main entry and this synonym
    $db->exec("DELETE FROM `words_synonyms` WHERE `word_synonym_id` = {$db->quote($wsg['word_synonym_id'])};");
    
    $db->exec("DELETE FROM `uses` WHERE `word_synonym_group_id` = {$db->quote($_GET['delete-synonym'])};");
       	
   	// Delete record saying to which group this synonym belonged
    $db->exec("DELETE FROM `word_synonym_groups` WHERE `word_synonym_group_id` = {$db->quote($_GET['delete-synonym'])};");
    
    // Delete properties links to this synonym
    $db->exec("DELETE FROM `word_synonym_group_properties` WHERE `word_synonym_group_id` = {$db->quote($_GET['delete-synonym'])};");
    
    // Check if any synonyms remained in the group from which this synonym was removed and remove the group if none remained
    $temp_group		= $db->query("SELECT COUNT(`word_synonym_id`) FROM `word_synonym_groups` WHERE `group_id` = {$db->quote($wsg['group_id'])};")->fetch(PDO::FETCH_COLUMN);
    
    if(!$temp_group)
    {
		$db->exec("DELETE FROM `groups` WHERE `group_id` = {$db->quote($wsg['group_id'])};");
    }
    
    /*
    // Check if current word is still in use by other words as a synonym or has any synonyms - if not, remove it
    $temp_in_use	= $db->query("SELECT COUNT(`word_id`) FROM `words_synonyms` WHERE `word_id` = {$db->quote($synonym_id)} OR `synonym_id` = {$db->quote($synonym_id)};")->fetch(PDO::FETCH_COLUMN);
    
    if(!$temp_in_use)
    {
		$db->query("DELETE FROM `words` WHERE `word_id`={$db->quote($synonym_id)};");
    }
   	
   	// Check if the word for which this synonym was removed still has any synonyms or is synonym
   	$temp_synonyms	= $db->query("SELECT COUNT(`word_id`) FROM `words_synonyms` WHERE `word_id` = {$db->quote($word_id)} OR `synonym_id` = {$db->quote($word_id)};")->fetch(PDO::FETCH_COLUMN);
   	
    if(!$temp_synonyms)
    {
		$db->query("DELETE FROM `words` WHERE `word_id`={$db->quote($word_id)};");
    }
	*/
	
   	hp_redirect(NULL, 'Žodis sėkmingai pašalintas iš duomenų bazės.', HP_M_SUCCESS);
}
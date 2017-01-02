<?php
// AJAX request to get all author' works
if(isset($_GET['get-auhtor-works']))
{
    echo json_encode($db->query("SELECT w1.work_id, w1.title, w1.year FROM works w1 WHERE w1.author_id = {$db->quote($_GET['get-auhtor-works'])};")->fetchAll(PDO::FETCH_ASSOC));

    exit;
}

if(!empty($_GET['query']))
{
	require_once 'synonym.inc.php';

	$result			= array();
	
	$original_query	= $_GET['query'];
	
	$_GET['query']	= mb_strtolower($original_query);

	if($original_query != $_GET['query'])
	{
		hp_message("Užkalusa pakeista iš {$db->quote($original_query)} į {$db->quote($_GET['query'])}. Jei manote, kad tai klaida, susiekite su administratoriumi.", HP_M_NOTICE);
	}

    $result['word']	= $db->query("SELECT w1.word_id, word FROM words w1 WHERE w1.word = {$db->quote($_GET['query'])} LIMIT 1;")->fetch(PDO::FETCH_ASSOC);
	
	#die(var_dump( $result['word'] ));
	
    if($result['word'])
    {
		$result['particles']    = $db->query("SELECT p1.particle FROM particles p1 INNER JOIN words_particles wp1 ON wp1.word_id = p1.particle_id ORDER BY p1.particle ASC;")->fetchAll(PDO::FETCH_ASSOC);
		
		$synonymous	= $db->query("SELECT w1.word_id, w1.word FROM words w1 INNER JOIN words_synonyms ws1 ON ws1.word_id = w1.word_id WHERE ws1.synonym_id = {$db->quote($result['word']['word_id'])};")->fetchAll(PDO::FETCH_ASSOC);
		
		
		
		
		// Query to get the synonyms
		$query	= "
		SELECT
		    w1.word_id, w1.word,
		    g1.group_id, g1.group,
		    p1.property_id, p1.abbreviation, p1.augmentation,
		    wsg1.word_synonym_group_id,
		    u1.use,
		    a1.author_id,
		    w2.work_id
		FROM
		    words_synonyms ws1
	
		INNER JOIN words w1 ON w1.word_id = ws1.synonym_id
	
		INNER JOIN word_synonym_groups wsg1 ON wsg1.word_synonym_id = ws1.word_synonym_id
		INNER JOIN groups g1 ON g1.group_id = wsg1.group_id
	
		LEFT JOIN word_synonym_group_properties wsgp1 ON wsgp1.word_synonym_group_id = wsg1.word_synonym_group_id
		LEFT JOIN properties p1 ON p1.property_id = wsgp1.property_id
		
		LEFT JOIN uses u1 ON u1.word_synonym_group_id = wsg1.word_synonym_group_id
		LEFT JOIN works w2 ON w2.work_id = u1.work_id
		LEFT JOIN authors a1 ON a1.author_id = w2.author_id
	
		WHERE ws1.word_id = {$db->quote($result['word']['word_id'])}
	
		ORDER BY g1.group_id ASC, w1.word ASC";
		
		$synonyms	    = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
		
		
		
		$synonymous_str	= array();
		
		foreach($synonymous as $s)
		{
			$synonymous_str[]	= $s['word'];
		}
		
		$synonymous_str	= array_unique($synonymous_str);
		
		if(!empty($synonymous_str))
		{
			hp_message('Ieškomas žodis yra susietas kaip sinonimas su žodžiais: <code>' . implode('</code>, <code>', $synonymous_str) . '</code>. Sukurkite naują pagrindinį įrašą tik tuo atveju, jei nėra jau egzistuojančios tos pačios reikšmės sinoniminių žodžių grupės.', HP_M_NOTICE);
		}
		
		if(empty($synonyms))
		{
			hp_message('Žodis neturi sinonimų.', HP_M_ERROR);
		}
		
		$result['groups'] = array();
	
		foreach($synonyms as $s)
		{
		    $result['groups'][$s['group_id']]['group_id']					    					= $s['group_id'];
		    $result['groups'][$s['group_id']]['group']						    					= $s['group'];
		    $result['groups'][$s['group_id']]['synonyms'][$s['word_id']]['word_synonym_group_id']   = $s['word_synonym_group_id'];
		    $result['groups'][$s['group_id']]['synonyms'][$s['word_id']]['word_id']		    		= $s['word_id'];
		    $result['groups'][$s['group_id']]['synonyms'][$s['word_id']]['word']		    		= $s['word'];
		    $result['groups'][$s['group_id']]['synonyms'][$s['word_id']]['use']			    		= $s['use'];
		    $result['groups'][$s['group_id']]['synonyms'][$s['word_id']]['author_id']				= $s['author_id'];
		    $result['groups'][$s['group_id']]['synonyms'][$s['word_id']]['work_id']		    		= $s['work_id'];
	
		    if($s['abbreviation'] !== NULL)
		    {
				$result['groups'][$s['group_id']]['synonyms'][$s['word_id']]['properties'][$s['property_id']]	= array('abbreviation' => $s['abbreviation'], 'augmentation' => $s['augmentation']);
		    }
		}
    }
    else
   	{
   		hp_redirect(NULL, 'Žodis nerastas.', HP_M_ERROR);
   	}
}

$authors	= $db->query("SELECT author_id, first_name, last_name FROM authors ORDER BY last_name ASC, first_name ASC;")->fetchAll(PDO::FETCH_ASSOC);

$properties	= $db->query("SELECT `property_id`, `abbreviation`, `augmentation` FROM `properties` ORDER BY `abbreviation` ASC;")->fetchAll(PDO::FETCH_ASSOC);

$works2		= $db->query("SELECT w1.text, w1.title, a1.first_name, a1.last_name FROM works w1 INNER JOIN authors a1 ON a1.author_id = w1.author_id;")->fetchAll(PDO::FETCH_ASSOC);

$input		= array();

if(!empty($_GET))
{
	$input	= array_merge($input, $_GET);
}

$input		= filter_var_array($input, array
(
	'query'			=> FILTER_SANITIZE_STRING,
));

?>
<h2>Patarimas</h2>

<div class="paragraph">
	<p>Atnaujinant Sinonimų žodyną patariame naudotis šiais šaltiniais: <a href="http://www.lkz.lt/startas.htm">Lietuvių kalbos žodynas</a>, A. Lyberio Sinonimų žodynas. Kopijuoti turinį griežtai draudžiama, tačiau remiantis šiais šaltiniais galite formuluoti sinoniminių žodžių grupių aprašymus, surasti naujus sinonimus.</p>
</div>

<form action="index.php" method="get">
	<input type="hidden" name="controller" value="synonym" />
	
	<h2>Surasti žodį</h2>
		    
	<?=hp_row('text', 'Raktažodis', 'query')?>
	
	<input type="submit" value="Ieškoti" />
</form>

<form action="index.php?controller=synonym&query=<?=$input['query']?>" method="post">
	<?php if(!empty($result['word'])):?>
	<div class="separator"></div>	
	
	<h2>Pridėti naują sinoniminių žodžių grupę</h2>
	
	<?php
	$groups		= '';
	$result['groups']	= empty($result['groups']) ? array() : $result['groups'];
	foreach($result['groups'] as $group)
	{
	    $groups	.= '<option value="' . $group['group_id'] . '">' . $group['group_id'] . '</option>';
	}
	?>
	
	<table class="default">
		<thead>
			<tr>
			    <th class="short">Reikšmės ID</th>
			    <th>Žodis</th>
			</tr>
		</thead>
		<tbody>
			<tr>
			    <td>
			    	<select name="group_id" style="width: 162px!important;"><option value="0" selected="selected">Sukurti naują</option><?=$groups?></select>
			    </td>
			    <td><input type="text" name="word" value="" style="width: 811px !important;" /></td>
			</tr>
		</tbody>
	</table>
	
	<div class="separator"></div>
			
	<input type="submit" name="add-new-synonym" value="Įtraukti naują žodį" />
	
	<?php endif;?>
	
	<?php if(!empty($result['groups'])):?>
	
	<div class="separator"></div>
			
		<table class="default form">
			    <?php
			    
			    $html	= '';
			    foreach($result['groups'] as $group)
			    {
				$html   .= '
			<thead>
				<tr>
				    <th>Reikšmės ID</th>
				    <th colspan="3">Reikšmė</th>
				</tr>
			</thead>
			<tbody>
				<tr class="form">
				    <td>' . $group['group_id'] . '</td>
				    <td colspan="3"><input type="text" name="groups[' . $group['group_id'] . ']" value="' . $group['group'] . '" style="width: 813px !important;" /></td>
				</tr>
			</tbody>
			<thead>
				<tr>
				    <th>Reikšmės ID</th>
				    <th>Žodis</th>
				    <th colspan="2"></th>
				</tr>
			</thead>
			';
				
			
				foreach($group['synonyms'] as $synonym)
				{
				    require 'synonym-fetch.inc.php';
			
				    $html   .= '
			    <tbody class="synonym">
				<tr>
				    <td class="short"><select name="synonyms[' . $synonym['word_synonym_group_id'] . '][group_id]">' . $groups . '</select></td>
				    <td class="short"><input type="text" name="synonyms[' . $synonym['word_synonym_group_id'] . '][word]" value="' . $synonym['word'] . '" disabled="disabled" /></td>
				    <td></td>
				    <td class="short"><a href="#" class="toggle-hidden-options">Daugiau</a></td>
				</tr>			
				<tr class="' . (empty($synonym['use']) && !empty($use_html) ? '' : 'hidden')  . '">
					<td class="short">
						<select class="author" name="synonyms[' . $synonym['word_synonym_group_id'] . '][use][author_id]">' . $authors_str . '</select>
					</td>
					<td class="short">
						<select class="work" name="synonyms[' . $synonym['word_synonym_group_id'] . '][use][work_id]">' . $works_str . '</select>
					</td>
					<td>
						<input type="text" name="synonyms[' . $synonym['word_synonym_group_id'] . '][use][text]" value="' . $synonym['use'] . '" style="width: 528px ! important;" />
					</td>
					<td class="short">
						<a href="index.php?controller=synonym&query=' . $input['query'] . '&delete-synonym=' . $synonym['word_synonym_group_id'] . '" onclick="return confirm(\'Ar tikrai norite tęsti?\');">Ištrinti</a>
					</td>
				</tr>';
				
				/*  class="' . (empty($synonym['use']) && !empty($use_html) ? '' : 'hidden') . '"
					' .
					(
						empty($use_html) ?
						'<td colspan="3">Vartojimo pavyzdžių nerasta.</td>' :
						
						''
					) . '
					<td>
							<a href="index.php?controller=synonym&query=' . $input['query'] . '&delete-synonym=' . $synonym['word_synonym_group_id'] . '">Ištrinti</a>
	
					</td>
					 -->*/
				
				if(empty($synonym['use']) && !empty($use_html))
				{
				 	$html   .= '
					<tr class="' . (empty($synonym['use']) ? '' : 'hidden') . '">
					    <td colspan="4"><ul class="use-examples">' . $use_html . '</ul></td>
					</tr>';
				}
				
				 $html   .= '
				<tr class="hidden">
				    <td class="properties" colspan="4">' . $properties_str . '</td>
				</tr>
			    </tbody>
			    ';
				}
			    }
			
			    echo $html;
			    ?>
			    
			    
		</table>
		
		<div class="separator"></div>		
		
		<input type="submit" name="update-synonyms" value="Išsaugoti" />
		
		<input type="checkbox" name="full_update" value="1" /> Pilnas žodžio atnaujinimas?
		
		<h2 style="margin-top: 20px;">Pilnas žodžio atnaujinimas</h2>
		
		<div class="paragraph">
			<p>Pilnas žodžio atnaujinimas, tai visų privalomų laukelių užpildymas visiems sinonimams: sinonimių žodžių grupių aprašymas, žodžių vartosena ir žodžių ypatybių parinkimas. Už kiekvieną pilną žodžio atnaujinimą jums mokama <span class="highlight">LTL <?=$user['rate_1']?></span>.</p>
		</div>
		
		<h2>Dalinis žodžio atnaujinimas</h2>
		
		<div class="paragraph">
			<p>Dalinis žodžio atnaujinimas yra <span class="highlight">privalomas</span> kai žodžiui priskiriate naują sinonimą. Priskyrę žodžiui naują sinonimą privalote nurodyti žodžio vartosenos pavyzdį ir parinkti ypatybęs, jei tokios jam būdingos.</p>
		</div>
		<?php endif;?>
</form>
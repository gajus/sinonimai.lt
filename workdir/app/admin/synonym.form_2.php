<?php
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
	
	<?php if(!empty($result['groups'])):?>
	
	<div class="separator"></div>
			
	<input type="submit" name="add-new-synonym" value="Įtraukti naują žodį" />
				
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
					<td>
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
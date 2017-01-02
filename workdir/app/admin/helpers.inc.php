<?php
set_time_limit(10); // in case while would go wild

function utf8_byte_offset_to_unit($string, $offset)
{
	return mb_strlen(substr($string, 0, $offset));

    $result = 0;
    for ($i = 0; $i < $boff; ) {
        $result++;
        $byte = $string[$i];
        $base2 = str_pad(
            base_convert((string) ord($byte), 10, 2), 8, "0", STR_PAD_LEFT);
        $p = strpos($base2, "0");
        if ($p == 0) { $i++; }
        elseif ($p <= 4) { $i += $p; }
        else  { return FALSE; }
    }
    return $result;
}

function fb_work_forms($syllables)
{
	$syllables		= (array) $syllables;

	$word_ending	= array_pop($syllables);
	
	$forms			= array
	(
		'vė'	=> array('vė', 'vės', 'vei', 'vę', 've', 'vėje'),
		'ma'	=> array('ma', 'mos', 'mai', 'mą', 'moje'),
		'lė'	=> array('lė', 'lės', 'lei', 'lę', 'le', 'lėje'),
		'tė'	=> array('tė', 'tės', 'tei', 'tę', 'te', 'tėje'),
		'žė'	=> array('žė', 'žės', 'žei', 'žę', 'že', 'žėje'),
		'čia'	=> array('čia', 'čios', 'čiai', 'čią', 'čia', 'čėje'),
		'kė'	=> array('kė', 'kės', 'kei', 'kę', 'ke', 'kėje'),
		'šė'	=> array('šė', 'šės', 'šei', 'šę', 'še', 'šėje'),
		'nė'	=> array('nė', 'nės', 'nei', 'nę', 'ne', 'nėje'),
		'lis'	=> array('lis', 'lio', 'liui', 'lį', 'liu', 'lyje'),
		'kas'	=> array('kas', 'ko', 'kui', 'ką', 'ku', 'kyje'),
		'tis'	=> array('tis', 'čio', 'čiui', 'tį', 'čiu', 'tyje'),
		'šis'	=> array('šis', 'šio', 'šiui', 'šį', 'šiu', 'šyje'),
		'dė'	=> array('dė', 'dės', 'dei', 'dią', 'de', 'dėje'),
		'sė'	=> array('sė', 'sės', 'sei', 'sią', 'se', 'sėje'),
		'jus'	=> array('jus', 'jo', 'jui', 'ją', 'ju', 'juje'),
		'tas'	=> array('tas', 'to', 'tui', 'tą', 'tu', 'tate'),
		'ba'	=> array('ba', 'bos', 'bai', 'bą', 'ba', 'boje'),
		'ji'	=> array('ji', 'sios', 'jai', 'ją', 'ja', 'joje'),
		'mas'	=> array('mas', 'mo', 'mui', 'mą', 'mu', 'me'),
		'kia'	=> array('kia', 'kios', 'kiai', 'kią', 'kia', 'kėje'),
		'gas'	=> array('gas', 'go', 'gui', 'gą', 'gu', 'ge'),
		'nis'	=> array('nis', 'nio', 'niui', 'nį', 'niu', 'nyje'),
		'na'	=> array('na', 'nos', 'nai', 'ną', 'na', 'noje'),
		'das'	=> array('das', 'do', 'dui', 'dą', 'du', 'de'),
		'dis'	=> array('dis', 'džio', 'džiui', 'dį', 'džiu', 'dyje'),
		'las'	=> array('las', 'lo', 'lui', 'lą', 'lu', 'le'),
		'nys'	=> array('nys', 'nio', 'niui', 'nį', 'niu', 'nyje'),
		'lys'	=> array('lys', 'lio', 'liui', 'lį', 'liu', 'lyje'),
		'nus'	=> array('nus', 'naus', 'niam', 'nų', 'niu', 'niame'),
		'gus'	=> array('gus', 'gaus', 'giam', 'gų', 'giu', 'ge'),
		'ras'	=> array('ras', 'ro', 'ui', 'rą', 'ru', 'rame'),
		'bas'	=> array('bas', 'bo', 'bam', 'bą', 'bu', 'bame'),
		'nas'	=> array('nas', 'no', 'nam', 'ną', 'nu', 'name'),
	);
	
	return isset($forms[$word_ending]) ? $forms[$word_ending] : FALSE;
}
	/*
	kas
	ko
	kam
	ką
	kuo
	kur
	*
	
	if(isset($forms[$word_ending]))
	{
		$return	= array();
		
		foreach($forms[$word_ending] as $end)
		{
			$return[]	= implode('', $syllables) . $end;
		}
		
		return $return;
	}
	
	return FALSE;
}*/

function fb_syllabify($string)
{
    $vowels		= array('a','e','i','o','u','ą','ę','ė','į','ų','ū','y'); // balsiai
    $consonants		= array('b','c','č','d','f','g','h','j','k','l','m','n','p','r','s','š','t','v','z','ž'); // pribalsiai

    /**
     *
     */
    $unary		= array
    (
	'dvibalsiai'		=> array('ai','ei','ui','au','ie','uo','eu','oi','ou'), // neskaidytini dvibalsiai

	'balsiai'		=> array('ią','ia','iu','io','iū','ių'), // papriešakėjusieji balsiai
	'balsiai_2'		=> array('iai','iau','iui','iuo'),

	'misrieji_dvigarsiai'	=> array('al','am','an','ar','el','em','en','er','il','im','in','ir','ul','um','un','ur'), // neskaidytini mišrieji dvigarsiai

	'dviraidziai'		=> array('ch','dž','dz'), // neskaidytini dviraidžiai
    );

    /**
     *
     */
    $consonants_groups  = array
    (
	'S'	    => array('s','z','š','ž','f','h'), // pučiamieji priebalsiai, t.p.: ch
	'T'	    => array('p','b','t','d','k','g','c','č'), // sprogstamieji priebalsiai, t.p.: dz, dž
	'R'	    => array('l','m','n','r','v','j'), // sklandieji priebalsiai
    );

    $result		    = array();
    $interim_result	    = array();

    $length		    = mb_strlen($string);
    $result['syllabels']    = array();

    // <editor-fold defaultstate="collapsed" desc="Four types of words' beginings using only consonants">
    $consonants_beginings   = array
    (
	'STR'	=> array
	(
	    'spl','spr','spj',
	    'str','stv',
	    'skl','skn','skr','skv',
	    'sgr',
	    'zbr',
	    'zdr',
	    'zgl',
	    'špl','špr',
	    'štr',
	    'žbl'
	),
	'ST'	=> array
	(
	    'sp','st','sk','sc','sč',
	    'zb','zd','zg',
	    'šp','št','šk','šč',
	    'žb','žd','žg'
	),
	'SR'	=> array
	(
	    'sl','sm','sn','sr','sv',
	    'zl','zm','zn','zr','zv',
	    'šl','šm','šn','šr','šv',
	    'žl','žm','žn','žr','žv',
	    'fl','fr'
	),
	'TR'	=> array
	(
	    'pl','pn','pr','pj',
	    'bl','br','bj',
	    'tl','tr','tv',
	    'dr','dv',
	    'kl','km','kn','kr','kv',
	    'gl','gm','gn','gr','gv',
	    'cm','cn','cv',
	    'čl','čm','čv',
	    // exceptions?
	    'dz','dž','ch'
	),
	'TR2'	=> array
	(
	    'dzl','dzv',
	    'džv',
	)
    );
    // </editor-fold>

    $cut    = 0;

    // <editor-fold defaultstate="collapsed" desc="Find word base">
    if(in_array(mb_substr($string, $cut, 3), array_merge($consonants_beginings['STR'], $consonants_beginings['TR2'])))
    {
	$cut	+= 3;
    }
    elseif(in_array(mb_substr($string, $cut, 2), array_merge($consonants_beginings['ST'], $consonants_beginings['SR'], $consonants_beginings['TR'])))
    {
	$cut	+= 2;
    }
    elseif(in_array(mb_substr($string, $cut, 1), $consonants))
    {
	$cut	+= 1;
    }

    if(in_array(mb_substr($string, $cut, 3), $unary['balsiai_2']))
    {
	$cut	+= 3;
    }
    elseif(in_array(mb_substr($string, $cut, 2), array_merge($unary['dvibalsiai'], $unary['balsiai']))) //, $unary['misrieji_dvigarsiai']
    {
	$cut	+= 2;
    }
    elseif(in_array(mb_substr($string, $cut, 1), $vowels))
    {
	$cut	+= 1;
    }

    while(in_array(mb_substr($string, $cut, 1), $consonants) && (in_array(mb_substr($string, $cut+1, 1), $consonants) || mb_substr($string, $cut+1, 1) == '') && $cut < $length)
    {
	$cut	+= 1;
    }

    $interim_result['base'] = mb_substr($string, 0, $cut);

    $length		    = $length-$cut;
    $remainder		    = mb_substr($string, $cut, $length);
    // </editor-fold>

    if(mb_strlen($remainder))
    {
	// <editor-fold defaultstate="collapsed" desc="Find last syllabel">
	$cut_from_end		= 1;

	while(in_array(mb_substr($remainder, 0-($cut_from_end+1), 1), $vowels))
	{
	    $cut_from_end   += 1;
	}

	$cut_from_end		+= 1;

	$interim_result['end']	= mb_substr($remainder, $length-$cut_from_end, $cut_from_end);

	$length			= $length-$cut_from_end;
	$remainder		= mb_substr($string, $cut, $length);
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="Syllabelize">
	$i	= 0;
	
	while($length > 0 && $i < 10)
	{
		++$i;
	
	    $cut    = 0;

	    if(in_array(mb_substr($remainder, $cut, 2), $unary['dviraidziai']))
	    {
		$cut	+= 2;
	    }
	    elseif(in_array(mb_substr($remainder, $cut, 1), $consonants))
	    {
		$cut	+= 1;
	    }

	    // bai s[iuo]k liai
	    if(in_array(mb_substr($remainder, $cut, 3), $unary['balsiai_2']))
	    {
		if(mb_substr($remainder, $cut, 3) == 'iai' && mb_substr($remainder, $cut-1, 1) == 's')
		{
		    $cut	+= 1;
		}
		else
		    $cut	+= 3;
	    }
	    elseif(in_array(mb_substr($remainder, $cut, 2), array_merge($unary['dvibalsiai'], $unary['balsiai'])))
	    {
		$cut	+= 2;
	    }
	    else
	    {
		$cut	+= 1;
	    }
		
		$j	= 0;
		
	    while($j < 10 && (in_array(mb_substr($remainder, $cut, 1), $consonants) && (in_array(mb_substr($remainder, $cut+1, 1), $consonants) || mb_substr($remainder, $cut+1, 1) == '') && $cut < $length) )
	    {
	    ++$j;
		$cut	+= 1;
	    }

	    if($cut == 0)
	    {
		return FALSE;
	    }

	    $result['syllabels'][]   = mb_substr($remainder, 0, $cut);
	    $length		    = $length-$cut;
	    $remainder		    = mb_substr($remainder, $cut, $length);
	}
	// </editor-fold>

	array_unshift($result['syllabels'], $interim_result['base']);
	array_push($result['syllabels'], $interim_result['end']);
    }
    else
    {
	$result['syllabels']	= $string;
    }

    return $result;
}

function wrap_text($text, $find_str)
{
	$return	= preg_replace_callback('|' . $find_str . '|', function($e) { return '<mark>' . $e[0] . '</mark>'; }, $text);
	
	return $return;
}

function hp_join_path()
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

function hp_redirect($url = NULL, $message_text = NULL, $message_type = HP_M_NOTICE)
{
    if($url === NULL)
    {
		$url	= $_SERVER['HTTP_REFERER'];
    }

    if($message_text !== NULL)
    {
		hp_message($message_text, $message_type);
    }

    header('Location: ' . $url);

	echo '<script type="text/javascript">top.location.href="' . $url . '"</script>Redirect fail';

	die;
}

define('HP_M_NOTICE', 1);
define('HP_M_SUCCESS', 2);
define('HP_M_ERROR', 3);


function hp_message($message, $type = HP_M_NOTICE)
{
    $_SESSION['hp']['flash']['messages'][$type][]	= $message;
}

function hp_display_messages()
{
    $return			= '';

    $messages_types	= array(
			HP_M_NOTICE		=> 'notice',
			HP_M_SUCCESS	=> 'success',
			HP_M_ERROR		=> 'error'
		);

    if(!empty($_SESSION['hp']['flash']['messages']))
    {
		ksort($_SESSION['hp']['flash']['messages']);

		foreach($_SESSION['hp']['flash']['messages'] as $type => $messages)
		{
			foreach($messages as $message)
			{
				$return	.= '<div class="' . $messages_types[$type] . '">' . $message . '</div>';
			}
		}
    }

	return empty($return) ? '' : '<div id="hp-message">' . $return . '</div>';
}

function hp_generate_update_str(array $input)
{
	global $db;

	$update	= array();

	foreach($input as $k => $v)
	{
		$update[]	= '`' . $k . '` = ' . $db->quote($v);
	}
	
	return implode(',', $update);
}

function hp_row($type, $label, $name, array $array = array())
{
	global $input;

	$value		= !isset($input[$name]) ? NULL : $input[$name];
	
	if($type == 'text')
	{
		$input_str	= '<input type="text" name="' . $name . '" value="' . $value . '" />';
	}
	elseif($type == 'password')
	{
		$input_str	= '<input type="password" name="' . $name . '" value="" autocomplete="off" />';
	}
	elseif($type == 'textarea')
	{
		$value		= filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
		
		$input_str	= '<textarea name="' . $name . '">' . $value . '</textarea>';
	}
	elseif($type == 'checkbox')
	{
		$input_str	= '<input type="checkbox" name="' . $name . '" value="1"' . (empty($value) ? '' : ' checked="checked"') . ' />';
	}
	elseif($type == 'file')
	{
		$input_str	= '<input type="file" name="' . $name . '" />';
	}
	elseif($type == 'select')
	{
		$options	= '';
		
		if(!empty($array['options']))
		{
			foreach($array['options'] as $k => $v)
			{
				$options	.= '<option value="' . $k . '"' . (isset($input[$name]) && $input[$name] == $v ? ' selected="selected"' : '') . '>' . $v . '</option>';
			}
		}
	
		$input_str	= '<select name="' . $name . '">' . $options . '</select>';
	}
	else
	{
		throw new Exception('Function <mark>row</mark> undefined argument <mark>$type</mark> value.');
	}

	echo '
	<div class="row">
		<label>' . $label . '</label>
		' . $input_str .  '
		' . (empty($array['help']) ? '' : '<div class="help">' . $array['help'] . '</div>') . '
	</div>
	';
}
$(document).ready(function(){
	suggestion_ajax_request	= {abort: function(){}};

	if(window.location.hash.length > 3)
	{
		//var query	= trim(strstr(window.location.hash, '/').substr(1), '/');
		var query	= trim(window.location.hash, '#_=/!');

		displayResult(query);
	}

	$('input[name=call]').keyup($.debounce(keyUp, 100));

	$('form[name=search]').submit(function(){

		if(!$('#suggestions').length)
		{
			return false;
		}

		displayResult($('#suggestions li.selected').text());

		return false;
	});

	$('#suggestions li').live('hover', function(){
		$('#suggestions li.selected').removeClass('selected');
		$(this).addClass('selected');
	});

	$('#suggestions li').live('click', function(){
		$('form[name=search]').trigger('submit');
	});
});

function displayResult(query)
{
	$('input[name=call]').val(query);

	$.ajax({
		url: '/dictionary',
		type: 'post',
		dataType: 'json',
		data: { action: 'display-entry', query: query },
		success: function(data)
		{
			if(!data.html)
			{
				return;
			}

			$('#home').hide();

			document.title			= 'Sinonimai žodžiui ' + data.word.word + ' – Sinonimų žodynas';
			window.location.hash	= '!/' + data.word.slug;

			$('#result').html(data.html).show();

			$('#result div.suggestions-box .input').ayTagBox();


		}
	});
}

function keyUp(e)
{
	suggestion_ajax_request.abort();

	window.location.hash	= '#!/';

	var code = e.keyCode ? e.keyCode : e.which;

	if(code == 38)
	{
		var prev	= $('#suggestions li.selected').prev('li');

		if(prev.length)
		{
			$('#suggestions li.selected').removeClass('selected');

			prev.addClass('selected');
		}

		return;
	}
	else if(code == 40)
	{
		var next	= $('#suggestions li.selected').next('li');

		if(next.length)
		{
			$('#suggestions li.selected').removeClass('selected');

			next.addClass('selected');
		}

		return;
	}
	else if([0,13,16,17,18,37,39,224].indexOf(code) != -1)
	{
		return;
	}

	if(!$('input[name=call]').val().length)
	{
		$('#result').html('').hide();
		$('#home').show();

		return;
	}

	$('#home').hide();

	$('#result').show().html('<div class="message loading">Paieška vykdoma <img src="/static/img/ajax-loader.gif" alt="" /></div>');

	suggestion_ajax_request	= $.ajax({
		url: '/dictionary',
		type: 'POST',
		dataType: 'json',
		timeout: 5000,
		scriptCharset: 'utf-8',
		data: { action: 'search-auto-complete', query: $('input[name=call]').val() },
		/*error: function(jqXHR, textStatus, errorThrown)
		{
			console.log(jqXHR, textStatus, errorThrown);
		},*/
		success: function(data)
		{

			if(data.length == 0)
			{
				$('#result').html('<div class="message">Paieška nerado rezultatų.</div>');
			}
			else
			{
				var html	= '<ul id="suggestions">';

				$.each(data, function(i, item){
					html += i == 0 ? '<li class="selected">' + item + '</li>' : '<li>' + item + '</li>';
				});

				html += '</ul>';

				$('#result').html( html );
			}
		}
	});
}

function trim(str, charlist) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: mdsjack (http://www.mdsjack.bo.it)
    // +   improved by: Alexander Ermolaev (http://snippets.dzone.com/user/AlexanderErmolaev)
    // +      input by: Erkekjetter
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: DxGx
    // +   improved by: Steven Levithan (http://blog.stevenlevithan.com)
    // +    tweaked by: Jack
    // +   bugfixed by: Onno Marsman
    // *     example 1: trim('    Kevin van Zonneveld    ');
    // *     returns 1: 'Kevin van Zonneveld'
    // *     example 2: trim('Hello World', 'Hdle');
    // *     returns 2: 'o Wor'
    // *     example 3: trim(16, 1);
    // *     returns 3: 6
    var whitespace, l = 0,
        i = 0;
    str += '';

    if (!charlist) {
        // default list
        whitespace = " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
    } else {
        // preg_quote custom list
        charlist += '';
        whitespace = charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
    }

    l = str.length;
    for (i = 0; i < l; i++) {
        if (whitespace.indexOf(str.charAt(i)) === -1) {
            str = str.substring(i);
            break;
        }
    }

    l = str.length;
    for (i = l - 1; i >= 0; i--) {
        if (whitespace.indexOf(str.charAt(i)) === -1) {
            str = str.substring(0, i + 1);
            break;
        }
    }

    return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
}

function strstr (haystack, needle, bool) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: strstr('Kevin van Zonneveld', 'van');
    // *     returns 1: 'van Zonneveld'
    // *     example 2: strstr('Kevin van Zonneveld', 'van', true);
    // *     returns 2: 'Kevin '
    // *     example 3: strstr('name@example.com', '@');
    // *     returns 3: '@example.com'
    // *     example 4: strstr('name@example.com', '@', true);
    // *     returns 4: 'name'
    var pos = 0;

    haystack += '';
    pos = haystack.indexOf(needle);
    if (pos == -1) {
        return false;
    } else {
        if (bool) {
            return haystack.substr(0, pos);
        } else {
            return haystack.slice(pos);
        }
    }
}

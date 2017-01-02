$(function(){
	$.fn.extend({
		ayTagBox: function()
		{
			return this.each(function(){
				var form				= $(this);
				
				var entry_id			= $(this).attr('data-id');
				
				form.append($('<input type="text" autocomplete="off" /><ul></ul>'));
				
				var input				= $(this).find('input[type=text]');
				
				var suggestion			= null;
				var suggestion_box		= form.find('ul').hide();
				var active_request		= {abort: function(){}};
				
				suggestion_box.on('click', 'li', null, function(){
					suggestion	= $(this).data();
					
					form.trigger('submit');
				}).on('hover', 'li', null, function(){
					suggestion	= $(this).data();
					
					suggestion_box.find('li').removeClass('selected');
					
					$(this).addClass('selected');
				})
				
				form.on('click', 'div', null, function(){				
					var data	= $(this).attr('word-id') ? {word: {id: $(this).attr('word-id')}} : $(this).data();
				
					$.post('/dictionary.php', {action: 'remove-suggestion', data: {entry_id: entry_id, word: data}});
					
					$(this).remove();
				});
				
				var update_suggestions	= function(data)
				{
					if(data.length)
					{
						suggestion		= data[0];
						
						suggestion_box.empty();
						
						for(var i = 0, j = data.length; i < j; i++)
						{
							suggestion_box.append($('<li>' + data[i].value + '</li>').data(data[i]));
						}
						
						suggestion_box.find('li').eq(0).addClass('selected');					
						
						if(!suggestion_box.is(':visible'))
						{					
							suggestion_box.css({ left: input.position().left, top: input.position().top+input.height() }).show();
						}
					}
					else
					{
						suggestion_box.hide();
					}
				};
				
				form.submit(function(e){				
					suggestion_box.hide();
					
					if(suggestion)
					{
						var found	= false;
					
						form.find('div.suggestion').each(function(){
							var data	= $(this).attr('word-id') ? {id: $(this).attr('word-id')} : $(this).data();
						
							if(data.id == suggestion.id)
							{
								found	= true;
								
								return;
							}
						});
						
						if(!found)
						{
							input.before( $('<div class="suggestion" />').text(suggestion.value).data(suggestion) );
						
							$.post('/dictionary.php', { action: 'add-suggestion', data: {entry_id: entry_id, word: suggestion}});
						}
					}
					
					input.val('');
					
					e.preventDefault();
				}).click(function(e){
					if(e.target == form[0])
					{
						input.focus();
					}
				});
				
				form.addClass('group tag-input');
				
				input.keyup(function(e){
					var code = e.keyCode ? e.keyCode : e.which;
				
					if(code == 13)
					{
						form.trigger('submit');
						
						return;
					}
				
					suggestion	= null;					
					
					if([0,13,16,17,18,37,38,39,40,224].indexOf(code) != -1)
					{
						var target	= {length: false};
						
						if(code == 38)
						{
							target	= suggestion_box.find('li.selected').prev('li');
						}
						else if(code == 40)
						{
							target	= suggestion_box.find('li.selected').next('li');
						}
						
						if(target.length)
						{
							suggestion_box.find('li.selected').removeClass('selected');
							
							target.addClass('selected');
							
							suggestion	= target.data();
						}
					
						return;
					}
				
					 // instead, a debounce plugin could be used
					active_request.abort();
				
					active_request	= $.ajax({
						url: '/dictionary.php',
						type: 'post',
						dataType: 'json',
						data: {action: 'tag-auto-complete', data: {query: $(this).val(), entry_id: entry_id}},
						success: function(data)
						{
							update_suggestions(data);
						}
					});
				});
			});
		}
	});
});
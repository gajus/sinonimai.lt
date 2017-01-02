$(document).ready(function(){    
    $('select.author').change(function(){
		var $workSelect	= $(this).parents('tr').find('select.work');
		
		$workSelect.html('');
		
		$.getJSON('index.php', {'get-auhtor-works': $(this).val()}, function(data){
			var html_str = '';
			
			$.each(data, function(i,d){
				//console.log(d.work_id, d.title, d.year);
				$workSelect.append($("<option></option>").attr("value",d.work_id).text(d.title + ', ' + d.year));
			});
		});
	});
	
	$('a.toggle-hidden-options').toggle(
	function(){
		$(this).parents('tbody').find('tr.hidden').show();
	},
	function(){
		$(this).parents('tbody').find('tr.hidden').hide();
	});
    	
	$('input[type="submit"], input[type="button"]').click(function()
	{
		if(typeof $(this).attr('confirm') !== 'undefined' && !confirm($(this).attr('confirm')))
		{
			return false;
		}
	
		if(typeof $(this).attr('location') !== 'undefined')
		{
			window.location.href = $(this).attr('location');
		
			return false;
		}
	});
	
	$('.approve[data-id]').click(function(){
		$.ajax({
			type: 'post',
			url: '?controller=contest',
			data: {approve: $(this).data('id')}
		});
		
		$(this).parents('tr').remove();
	});
	
	$('.decline[data-id]').click(function(){
		$.ajax({
			type: 'post',
			url: '?controller=contest',
			data: {decline: $(this).data('id')}
		});
		
		$(this).parents('tr').remove();
	});
	
	$('.hide-button[data-id]').click(function(){
		$.ajax({
			type: 'post',
			url: '?controller=lost',
			data: {hide: $(this).data('id')}
		});
		
		$(this).parents('tr').remove();
	});
});
var customList = {};
$(document).ready(function() {
	
	var url = location.href;
	var urlLists = '/services/lists/';
	if(~url.indexOf(urlLists)){
		getSum();
	}
	BX.addCustomEvent('onAjaxSuccess', afterAjax);

	//редактирование

	$('body').on('click', '#custom_list_cancel', function(){
		$('#custom_list_save, #custom_list_cancel').hide();
		$('#custom_list_edit').css('display', 'table-cell');

		//возврат старых значений
		$('.main-grid-table .main-grid-row-checked').each(function(index, el) {
			$(el).find('td').each(function(index2, el2) {
				if(index2 in customList.indexFields){
					var val = $(el2).attr('data-old');
					$(el2).attr('data-old', val);
					$(el2).find('.main-grid-cell-content').text(val);
				}
			});
		});

	})

	$('body').on('click', '#custom_list_edit', function(event) {
		event.preventDefault();
		$(this).hide();
		$('#custom_list_save, #custom_list_cancel').css('display', 'table-cell');
		

		customList.fields = ['PROPERTY_89', 'PROPERTY_91'];
		customList.fieldSelect = 'PROPERTY_91';
		customList.indexFields = {};
		$('#lists_list_elements_28_table .main-grid-row-head th, #lists_attached_crm_28_table .main-grid-header th').each(function(index, el) {
			var fieldName = $(el).attr('data-name');
			if(~customList.fields.indexOf(fieldName) && index){
				customList.indexFields[index] = fieldName;
			}
		});

		//Получаем данные и вывод input
		var data = {};
		$('.main-grid-table .main-grid-row-checked').each(function(index, el) {

			// BX.unbind($(el), 'click');
			var id = $(el).attr('data-id');
			data[id] = {};
			$(el).find('td').each(function(index2, el2) {
				if(index2 in customList.indexFields){
					var val = $(el2).find('.main-grid-cell-content').text();
					$(el2).attr('data-old', val);
					var field = customList.indexFields[index2];
					if(field == customList.fieldSelect){
						$(el2).find('.main-grid-cell-content').attr('data-prevent-default', '').html(customList.select);
						$(el2).find('select option:contains("' + val + '")').attr("selected", "selected");
					}else{
						$(el2).find('.main-grid-cell-content').attr('data-prevent-default', '').html('<input value="' + val + '">');
					}

					data[id][field] = val;
				}
			});
		});
	});

	$('body').on('click', '#custom_list_save', function(event) {

		event.preventDefault();
		$('#custom_list_edit').css('display', 'table-cell');
		$('#custom_list_save, #custom_list_cancel').hide();

		//Получаем данные и вывод input
		var data = {};
		$('.main-grid-table .main-grid-row-checked').each(function(index, el) {
			var id = $(el).attr('data-id');
			data[id] = {};
			$(el).find('td').each(function(index2, el2) {
				if(index2 in customList.indexFields){
	
					var field = customList.indexFields[index2];

					if(field == customList.fieldSelect){
						var valText = $(el2).find('.main-grid-cell-content option:selected').text();
						var val = $(el2).find('.main-grid-cell-content select').val();
					}else{
						var val = $(el2).find('.main-grid-cell-content input').val();
						var valText = val;
					}
					$(el2).find('.main-grid-cell-content').text(valText);

					data[id][field] = val;
					
				}
			});

			$.ajax({
				url: '/local/ajax/lists.php',
				type: 'POST',
				data: {action: 'editList', data: data},
			})
			.done(function() {
				console.log("success");
			});
			
		});


	});

	$('body').on('click', '#lists_attached_crm_28_table', function(){
		if(!$('span').is('#custom_list_edit')){
			//получение значения списка
			getSelectListField()

			//кнопки
			showButLists()
		}
		
	})

	$('body').on('click', '#lists_list_elements_28_table', function(){
		if(!$('span').is('#custom_list_edit')){
			//получение значения списка
			getSelectListField()

			//кнопки
			showButLists()
		}
	})
});

var dataFilter = {};
function afterAjax(data) {

	try {
		var json = $.parseJSON(data);
   		dataFilter = json.filters.tmp_filter.fields;
	} catch(e) {
		// console.log('NO JSON');
	}
	// if(~url.indexOf(urlLists)){
		if(dataFilter){
			setTimeout(getSum(), 500);
		}
	// }
	
	
}
function getSelectListField(){
	$.get('/local/ajax/lists.php?action=getListSelect', function(data) {
		try {
			var json = $.parseJSON(data);
   			customList.select = '<select>';
   			for(var i in json){
   				customList.select += '<option value="' + i + '">' + json[i] + '</option>';
   			}
   			customList.select += '<select>';
		} catch(e) {
			// console.log('NO JSON');
		}
	});
}

function showButLists() {
	$('#grid_remove_button')
		.after('<span class="main-grid-panel-control-container" id="custom_list_edit">\
			<span class="main-grid-buttons icon edit">Редактировать</span>\
		</span>')
		.after('<span class="main-grid-panel-control-container" id="custom_list_save">\
			<span class="main-grid-buttons">Сохранить</span>\
		</span>')
		.after('<span class="main-grid-panel-control-container" id="custom_list_cancel">\
			<span class="main-grid-buttons">Отменить</span>\
		</span>');
}

function getSum() {
	var idList = $(".main-grid-header").attr('data-relative').replace('lists_list_elements_', '');
	$.ajax({
		url: '/local/ajax/lists.php',
		type: 'POST',
		data: {list: idList, action: 'sumList', data: dataFilter},
	})
	.done(function(data) {
		$('.sum_lists').remove();
		var json = $.parseJSON(data);
		for(var i in json){
			$('.main-grid-row-head [data-name="' + i + '"] .main-grid-head-title').append('<span class="sum_lists">' + json[i] + '</span>');
		}
	});
}



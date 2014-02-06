var ls = ls || {};

ls.userfield =( function ($) {

	this.iCountMax = 2;

	this.showAddForm = function(){
        $('#userfield_form .modal-header .modal-title').text(ls.lang.get('action.admin.user_field_admin_title_add'));
        $('#userfield_form .btn-primary').text(ls.lang.get('action.admin.user_field_add'));
		$('#user_fields_form_name').val('');
		$('#user_fields_form_title').val('');
		$('#user_fields_form_id').val('');
		$('#user_fields_form_pattern').val('');
		$('#user_fields_form_type').val('');
		$('#user_fields_form_action').val('add');
		$('#userfield_form').jqmShow(); 
	};
	
	this.showEditForm = function(id) {
        $('#userfield_form .modal-header .modal-title').text(ls.lang.get('action.admin.user_field_admin_title_edit'));
        $('#userfield_form .btn-primary').text(ls.lang.get('action.admin.user_field_update'));
		$('#user_fields_form_action').val('update');
		var name = $('#field_'+id+' .userfield_admin_name').text();
		var title = $('#field_'+id+' .userfield_admin_title').text();
		var pattern = $('#field_'+id+' .userfield_admin_pattern').text();
		var type = $('#field_'+id+' .userfield_admin_type').text();
		$('#user_fields_form_name').val(name);
		$('#user_fields_form_title').val(title);
		$('#user_fields_form_pattern').val(pattern);
		$('#user_fields_form_type').find('[value="' + type + '"]').attr('selected', 'selected').trigger('refresh');
		$('#user_fields_form_id').val(id);
		$('#userfield_form').jqmShow(); 
	};

	this.applyForm = function(){
		$('#userfield_form').jqmHide(); 
		if ($('#user_fields_form_action').val() == 'add') {
			this.addUserfield();
		} else if ($('#user_fields_form_action').val() == 'update')  {
			this.updateUserfield();
		}
	};

	this.addUserfield = function() {
		var name = $('#user_fields_form_name').val();
		var title = $('#user_fields_form_title').val();
		var pattern = $('#user_fields_form_pattern').val();
		var type = $('#user_fields_form_type').val();

		var url = aRouter['admin']+'settings-userfields';
		var params = {'action':'add', 'name':name,  'title':title,  'pattern':pattern,  'type':type};
		
		ls.ajax(url, params, function(data) {
			if (!data.bStateError) {
				var newRow = $(
                    '<tr id="field_'+data.id+'">'
                    + '<td class="userfield_admin_name"></td ><td class="userfield_admin_title"></td>'
                    + '<td class="userfield_admin_pattern"></td><td class="userfield_admin_type"></td>'
					+ '<td class="userfield-actions"><a class="icon icon-edit" href="javascript:ls.userfield.showEditForm('+data.id+')"></a> '
					+ '<a class="icon icon-remove" href="javascript:ls.userfield.deleteUserfield('+data.id+')"></a></td>'
                    + '</tr>'
                )
				;
				$('#user_field_list').append(newRow);
				$('#field_'+data.id+' .userfield_admin_name').text(name);
				$('#field_'+data.id+' .userfield_admin_title').text(title);
				$('#field_'+data.id+' .userfield_admin_pattern').text(pattern);
				$('#field_'+data.id+' .userfield_admin_type').text(type);
				ls.msg.notice(data.sMsgTitle,data.sMsg);
				ls.hook.run('ls_userfield_add_userfield_after',[params, data],liElement);
			} else {
				ls.msg.error(data.sMsgTitle,data.sMsg);
			}
		});
	};

	this.updateUserfield = function() {
		var id = $('#user_fields_form_id').val();
		var name = $('#user_fields_form_name').val();
		var title = $('#user_fields_form_title').val();
		var pattern = $('#user_fields_form_pattern').val();
		var type = $('#user_fields_form_type').val();

		var url = aRouter['admin']+'settings-userfields';
		var params = {'action':'update', 'id':id, 'name':name,  'title':title,  'pattern':pattern, 'type':type};

		ls.ajax(url, params, function(data) {
			if (!data.bStateError) {
				$('#field_'+id+' .userfield_admin_name').text(name);
				$('#field_'+id+' .userfield_admin_title').text(title);
				$('#field_'+id+' .userfield_admin_pattern').text(pattern);
				$('#field_'+id+' .userfield_admin_type').text(type);
				ls.msg.notice(data.sMsgTitle,data.sMsg);
				ls.hook.run('ls_userfield_update_userfield_after',[params, data]);
			} else {
				ls.msg.error(data.sMsgTitle,data.sMsg);
			}
		});
	};

	this.deleteUserfield = function(id) {
        /*
        admin.confirm(ls.lang.get('action.admin.user_field_delete_confirm'), function() {
            var url = aRouter['admin']+'settings-userfields';
            var params = {'action':'delete', 'id':id};

            $('.modal').modal('hide');

            ls.ajax(url, params, function(data) {
                if (!data.bStateError) {
                    $('#field_'+id).remove();
                    ls.msg.notice(data.sMsgTitle,data.sMsg);
                    ls.hook.run('ls_userfield_update_userfield_after',[params, data]);
                } else {
                    ls.msg.error(data.sMsgTitle,data.sMsg);
                }
            });
        });
        */
        if (confirm(ls.lang.get('action.admin.user_field_delete_confirm'))) {
            var url = aRouter['admin']+'settings-userfields';
            var params = {'action':'delete', 'id':id};

            ls.ajax(url, params, function(data) {
                if (!data.bStateError) {
                    $('#field_'+id).remove();
                    ls.msg.notice(data.sMsgTitle,data.sMsg);
                    ls.hook.run('ls_userfield_update_userfield_after',[params, data]);
                } else {
                    ls.msg.error(data.sMsgTitle,data.sMsg);
                }
            });
        }
	};

	this.addFormField = function() {
		var tpl=$('#profile_user_field_template').clone();
		/**
		 * Находим доступный тип контакта
		 */
		var value;
		tpl.find('select').find('option').each(function(k,v){
			if (this.getCountFormField($(v).val())<this.iCountMax) {
				value=$(v).val();
				return false;
			}
		}.bind(this));

		if (value) {
			tpl.find('select').val(value);
			$('#user-field-contact-contener').append(tpl.show());
		} else {
			ls.msg.error('',ls.lang.get('settings_profile_field_error_max',{count: this.iCountMax}));
		}
		return false;
	};

	this.changeFormField = function(obj) {
		var iCount=this.getCountFormField($(obj).val());
		if (iCount>this.iCountMax) {
			ls.msg.error('',ls.lang.get('settings_profile_field_error_max',{count: this.iCountMax}));
		}
	};

	this.getCountFormField = function(value) {
		var iCount=0;
		$('#user-field-contact-contener').find('select').each(function(k,v){
			if (value==$(v).val()) {
				iCount++;
			}
		});
		return iCount;
	};

	this.removeFormField = function(obj) {
		$(obj).parent('.js-user-field-item').detach();
		return false;
	};
	return this;
}).call(ls.userfield || {},jQuery);
{$noSidebar=true}
{include file='header_light.tpl'}

<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#reactivation-form').bind('submit',function(){
			ls.user.reactivation('reactivation-form');
			return false;
		});
		$('#reactivation-form-submit').attr('disabled',false);
	});
</script>

<div class="text-center page-header">
	<h3>{$aLang.reactivation}</h3>
</div>

<form action="{router page='login'}reactivation/" method="POST" id="reactivation-form">
	<div class="form-group">
		<label for="reactivation-mail">{$aLang.password_reminder_email}</label>
		<input type="text" name="mail" id="reactivation-mail" class="form-control" />
		<p class="help-block"><small class="text-danger validate-error-hide validate-error-reactivation"></small></p>
	</div>
	
	<button type="submit" name="submit_reactivation" class="btn btn-success" id="reactivation-form-submit" disabled="disabled">{$aLang.reactivation_submit}</button>
</form>


{include file='footer_light.tpl'}

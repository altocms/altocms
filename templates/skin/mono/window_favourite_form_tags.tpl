{if $oUserCurrent}
	<div id="favourite-form-tags" class="b-modal">
		<header class="b-modal-header">
			<h3>{$aLang.add_favourite_tags}</h3>
			<a href="#" class="b-modal-close jqmClose"></a>
		</header>
		
		
		<form onsubmit="return ls.favourite.saveTags(this);" class="b-modal-content">
			<input type="hidden" name="target_type" value="" id="favourite-form-tags-target-type">
			<input type="hidden" name="target_id" value="" id="favourite-form-tags-target-id">

			<p><input type="text" name="tags" value="" id="favourite-form-tags-tags" class="autocomplete-tags-sep input-text input-width-full"></p>
			<button type="submit" name="" class="btn jqmClose">{$aLang.favourite_form_tags_button_cancel}</button>
            <button type="submit" name="" class="btn-primary">{$aLang.favourite_form_tags_button_save}</button>
		</form>
	</div>
{/if}
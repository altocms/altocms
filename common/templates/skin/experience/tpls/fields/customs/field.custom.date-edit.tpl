 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if $oField}
    {$iFieldId=$oField->getFieldId()}
    <div class="form-group has-feedback">
        <div class="input-group charming-datepicker">
            <label class="input-group-addon" for="fields-{$iFieldId}">{$oField->getFieldName()}</label>
            <div class="dropdown-menu"></div>
            <input class="date-picker form-control" data-toggle="dropdown" readonly="readonly" name="fields[{$iFieldId}]"
                   id="properties-{$iFieldId}"
                   value="{if $_aRequest.fields.$iFieldId}{$_aRequest.fields.$iFieldId}{/if}"/>
            <span class="form-control-feedback" data-toggle="dropdown"><i class="fa fa-calendar-o"></i></span>
        </div>
        <small class="control-notice">{$oField->getFieldDescription()}</small>
    </div>
{/if}


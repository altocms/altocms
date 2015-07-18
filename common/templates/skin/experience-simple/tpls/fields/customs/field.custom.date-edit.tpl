 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}
{if $oField}
    {$iFieldId=$oField->getFieldId()}
    <div class="info-container"><i class="fa fa-info-circle pull-right js-title-topic" data-original-title="{$oField->getFieldDescription()}"></i></div>
    <div class="form-group has-feedback">
        <div class="input-group charming-datepicker">
            <label class="input-group-addon" for="fields-{$iFieldId}">{$oField->getFieldName()}</label>
            <div class="dropdown-menu"></div>
            <input class="date-picker form-control" data-toggle="dropdown" readonly="readonly" name="fields[{$iFieldId}]"
                   id="properties-{$iFieldId}"
                   value="{if $_aRequest.fields.$iFieldId}{$_aRequest.fields.$iFieldId}{/if}"/>
            <span class="form-control-feedback" data-toggle="dropdown"><i class="fa fa-calendar-o"></i></span>
        </div>
    </div>
{/if}


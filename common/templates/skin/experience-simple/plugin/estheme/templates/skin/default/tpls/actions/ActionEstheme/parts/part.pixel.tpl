{*<div class="span{$grid}">*}
    {*<div class="form-group">*}
        {*<div class="input-group">*}
            {*<input class="form-control" type="text" id="{$id}" name="{$id}" value="{$_aRequest[$id]}"/>*}
            {*<label class="input-group-addon">px</label>*}
        {*</div>*}
        {*<span class="control-notice">{$aLang.plugin.estheme[$notice]}</span>*}
    {*</div>*}
{*</div>*}

    <div class="span{$grid}">
        <div class="input-append">
            <input class="form-control span11" type="text" id="{$id}" name="{$id}" value="{$_aRequest[$id]}"/>
            <span class="add-on">px</span>
        </div>
        <span class="control-notice">{$aLang.plugin.estheme[$notice]}</span>
    </div>

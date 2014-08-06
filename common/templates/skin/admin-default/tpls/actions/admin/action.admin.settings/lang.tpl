{extends file='_index.tpl'}
{block name="content-body"}
<div class="col-md-12">
  <form action="" method="POST" class="form-horizontal" enctype="multipart/form-data">
    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
    <input type="hidden" name="submit_data_save" value="1">
    <input type="hidden" name="lang_exclude" value="">
    <div class="panel panel-default">
      <div class="panel-heading">
        <div class="panel-title">{$aLang.action.admin.set_lang_used}</div>
      </div>
      <div class="panel-body">
        <div class="b-set-lang-allow">
          {foreach $aLangAllow as $sLang=>$aLng}
          <div class="b-set-lang-allow-item lang-allow-{$sLang}">
            {if !$aLng.current}
            <div class="b-set-lang-exclude pull-right" data-lang="{$sLang}">
              <i class="ion-ios7-trash-sign"></i>
            </div>
            {/if}
            <label class="{if $aLng.current}b-set-lang-current{/if} input-radio">
            <input class="form-control" type="radio" name="lang_current" value="{$sLang}"
            {if $aLng.current}checked{/if}/>
            {$sLang} - {$aLng.name} - {$aLng.native}
            </label>
          </div>
          {/foreach}
        </div>
      </div>
      <div class="panel-footer">
        <button class="b-set-lang-button btn btn-primary">
        {$aLang.action.admin.include}
        </button>
        <button class="b-set-lang-change btn btn-primary">
        {$aLang.action.admin.save}
        </button>
      </div>
    </div>
    <div class="panel panel-default" style="display: none;">
      <div class="panel-heading">
        <div class="b-set-lang-close"><i class="ion-ios7-trash"></i></div>
        <div class="panel-title">{$aLang.action.admin.set_lang_avail}</div>
      </div>
      <div class="panel-body">
        <div class="b-set-lang-avail">
          {foreach $aLanguages as $sLang=>$aLng}
          <div class="b-set-lang-avail-item">
            <label>
            <input type="checkbox" name="lang_allow[]" value="{$sLang}"/>
            {$sLang} - {$aLng.name} - {$aLng.native}
            </label>
          </div>
          {/foreach}
        </div>
      </div>
        <div class="panel-footer clearfix">
          <button class="b-set-lang-change btn btn-primary">
          <i class="icon icon-ok-circle"></i>
          {$aLang.action.admin.save}
          </button>
        </div>
    </div>
  </form>
</div>
<script>
  $(function () {
      $('.b-set-lang-button').click(function () {
          var section = $('.b-set-lang-avail').parents('.box');
          section.toggle();
          $(this).toggleClass('active');
          return false;
      });
  
      $('.b-set-lang-close').click(function () {
          var section = $('.b-set-lang-avail').parents('.box');
          section.hide();
          $('.b-set-lang-button').removeClass('active');
          return false;
      });
  
      $('.b-set-lang-exclude').click(function(){
          var lang = $(this).data('lang');
          if (lang == $('input[name=lang_current]:radio:checked').val()) {
              $('.b-set-lang-current').click();
          }
          $('.lang-allow-' + lang).hide();
          $('[name=lang_exclude]').val($('[name=lang_exclude]').val() + ',' + lang);
      });
  });
</script>
{/block}
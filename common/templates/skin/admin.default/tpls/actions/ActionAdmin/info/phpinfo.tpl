{extends file='_index.tpl'}

{block name="content-body"}

<div class="span12">

    {if $aPhpInfo.count}
        {foreach from=$aPhpInfo.collection key=sSectionKey item=aSectionVal name=sec}
        {$section=$smarty.foreach.sec.iteration}
        <div class="b-wbox phpinfo">
            <div class="b-wbox-header">
                    <div class="buttons">
                        <button class="btn btn-mini btn-toggle" data-toggle="collapse" data-target="#section_{$section}">
                            <i class="icon icon-plus"></i>
                        </button>
                    </div>
                <h3 class="b-wbox-header-title">{$sSectionKey}</h3>
            </div>
            <div class="b-wbox-content nopadding collapse" id="section_{$section}">
                <table class="table">
                    {foreach from=$aSectionVal key=sKey item=sVal}
                        <tr>
                            <td class="span4 td-label">{$sKey}</td>
                            <td>{$sVal}</td>
                        </tr>
                    {/foreach}
                </table>
            </div>
        </div>
        {/foreach}
    {/if}

 </div>

<script>
$(function(){
    $('.phpinfo .btn-toggle').click(function(){
        var target = $($(this).data('target'));
        if (target.length) {
            if (!target.hasClass('in')) {
                $(this).find('i').removeClass('icon-plus').addClass('icon-minus');
            } else {
                $(this).find('i').removeClass('icon-minus').addClass('icon-plus');
            }
        }
    });
});
</script>

{/block}
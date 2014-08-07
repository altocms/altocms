{extends file='_index.tpl'}

{block name="content-body"}

<div class="col-md-12">

    {if $aPhpInfo.count}
        {foreach from=$aPhpInfo.collection key=sSectionKey item=aSectionVal name=sec}
        {$section=$smarty.foreach.sec.iteration}
        <div class="panel phpinfo">
            <div class="panel-heading">
                    <div class="tools pull-right">
                        <button class="btn btn-primary btn-xs btn-toggle" data-toggle="collapse" data-target="#section_{$section}">
                            <i class="ion-plus-round"></i>
                        </button>
                    </div>
                <h3 class="panel-title">{$sSectionKey}</h3>
            </div>
            <div class="panel-body collapse no-padding" id="section_{$section}">
                <div class="table table-striped-responsive"><table class="table table-striped">
                    {foreach from=$aSectionVal key=sKey item=sVal}
                        <tr>
                            <td class="span4 td-label">{$sKey}</td>
                            <td style="word-break: break-word;">{$sVal}</td>
                        </tr>
                    {/foreach}
                </table></div>
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
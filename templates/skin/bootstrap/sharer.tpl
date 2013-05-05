{if $bTopicList}
    <div class="yashare-auto-init" data-yashareTitle="{$oTopic->getTitle()|escape:'html'}" data-yashareLink="{$oTopic->getUrlShort()}" data-yashareL10n="ru" data-yashareType="button" data-yashareQuickServices="yaru,vkontakte,facebook,twitter,odnoklassniki,moimir,lj,gplus"></div>
{else}
    <script>
        jQuery(document).ready(function($) {
            jQuery('#topic_share_{$oTopic->getId()}').slideToggle();
        });
    </script>
    <div class="b-ya-likes yashare-auto-init" data-yashareL10n="ru" data-yashareLink="{$oTopic->getUrlShort()}"  data-yashareTitle="{$oTopic->getTitle()|escape:'html'}" data-yashareDescription="" data-yashareQuickServices="yaru,vkontakte,facebook,twitter,moimir,gplus" data-yashareTheme="counter" data-yashareType="small"></div>
{/if}
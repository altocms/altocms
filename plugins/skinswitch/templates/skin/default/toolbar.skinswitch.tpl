{literal}
    <style type="text/css">
        #skinswitch-button {
            position: relative;
            font-family: Arial, Helvetica, sans-serif;
        }

        #skinswitch-list {
            background-color: white;
            border: 1px solid black;
            display: block;
            padding: 10px;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 999;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }

        .skinswitch-item {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px !important;
            line-height: 14px !important;
            background-color: white !important;
            color: black !important;
            font-weight: normal;
            text-decoration: none;
            display: block;
            height: 16px !important;
            padding: 0 4px!important;
            margin: 2px;
        }

        .skinswitch-item-hover {
            color: white !important;
            background-color: black !important;
        }

        .skinswitch-item-current {
            font-weight: bold;
            text-decoration: underline;
        }

        .skinswitch-item-hide-noncurrent .skinswitch-item {
            xdisplay: none;
        }

        .skinswitch-item-hide-noncurrent .skinswitch-item.skinswitch-item-current {
            display: block;
        }
    </style>
    <script language="javascript">

        $(function () {
            var button = $('#skinswitch-button');

            var list = $('#skinswitch-list').appendTo('body');
            var posTop = button.offset().top + list.outerHeight() - $(window).height() + 5;
            var posLeft = button.offset().left - list.outerWidth() - 5;

            if (posTop > 0) {
                list.css({top: (button.offset().top - posTop) + 'px', left: posLeft + 'px'});
            } else {
                list.css({top: button.offset().top + 'px', left: posLeft + 'px'});
            }
            list.hide();
            var timeout = null;
            var inArea = 0;
            button.mouseover(function () {
                list.slideDown();
                ++inArea;
                timeout = setInterval(function () {
                    if (inArea <= 0) {
                        list.slideUp();
                        clearInterval(timeout);
                    }
                }, 2000);
                console.log('button.mouseover', inArea);
            });
            button.mouseout(function () {
                --inArea;
            });
            list.mouseover(function () {
                ++inArea;
            });
            list.mouseout(function () {
                --inArea;
            });

            $('.skinswitch-item')
                    .mouseover(function () {
                        $(this).addClass('skinswitch-item-hover');
                    })
                    .mouseout(function () {
                        $(this).removeClass('skinswitch-item-hover');
                    });
        });
    </script>
{/literal}

{if $aSkinswitchTemplates}
    <section class="toolbar-scinswich" id="skinswitch-button">
        <a href="#">
            <i class="icon-leaf"></i>
        </a>
    </section>
    <div id="skinswitch-list">
        {foreach item=sSkinswitchTemplateName from=$aSkinswitchTemplates}
            <a class="skinswitch-item {if $aSkinswitchCurrent==$sSkinswitchTemplateName}skinswitch-item-current{/if}"
               href="?{$aSkinswitchGetParam|escape:'url'}={$sSkinswitchTemplateName|escape:'url'}">
                {$sSkinswitchTemplateName|escape:'html'}
            </a>
        {/foreach}
    </div>
{/if}
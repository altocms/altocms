<aside id="sidebar"
       class="{if $sAction=='profile' || $sAction=='settings' || $sAction=='talk'}span3{else}span4{/if}
              {if $sidebarPosition == 'left'} sidebar-left{/if}
              {if $noSidebarRespon} respon-sidebar{/if}
              ">
	{include file='blocks.tpl' group='right'}
</aside>
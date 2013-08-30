{**
 * Список сообщений
 *}

{extends file='[layouts]layout.user.messages.tpl'}

{block name='layout_options'}
	{$bNoSystemMessages = false}
	{$bNoSidebar = true}
{/block}

{block name='layout_content'}
	{if $aTalks}
		{**
		 * Список сообщений
		 *}
		<form action="{router page='talk'}" method="post" id="form_talks_list">
			<input type="hidden" name="security_ls_key" value="{$LIVESTREET_SECURITY_KEY}" />
			<input type="hidden" name="submit_talk_unread" id="form_talks_list_submit_unread" value="" />
			<input type="hidden" name="submit_talk_read" id="form_talks_list_submit_read" value="" />
			<input type="hidden" name="submit_talk_del" id="form_talks_list_submit_del" value="" />
			
			<div class="table-users-wrapper">
				<div class="table-toolbar">
					<section>
						<ul>
							<li class="delete">
								<a href="#" onclick="if (confirm('{$aLang.talk_inbox_delete_confirm}')){ ls.talk.removeTalks() };" title="{$aLang.talk_inbox_delete}"><i class="icon-native-talk-delete"></i></a>
							</li>
							<li class="mark">
								<a href="#" onclick="ls.talk.makeReadTalks();" title="{$aLang.talk_inbox_make_read}"><i class="icon-native-talk-mark"></i></a>
							</li>
							<li class="unmark">
								<a href="#" onclick="ls.talk.makeUnreadTalks();"  title="{$aLang.talk_inbox_make_unread}"><i class="icon-native-talk-unmark"></i></a>
							</li>
							{*<li class="remove">
								<a href="#"><i class="icon-native-talk-remove"></i></a>
							</li>*}
						</ul>
					</section>
				</div>
				
				{include file='actions/ActionTalk/message_list.tpl' bMessageListCheckboxes=true}
				
			</div>
		</form>
	{else}
		<div class="notice-empty">{$aLang.talk_inbox_empty}</div>
	{/if}

				
	{include file='pagination.tpl' aPaging=$aPaging}
{/block}
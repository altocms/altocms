{**
 * Содержимое тултипа с информацией о голосовании за топик
 *}

<div class="tip-arrow"></div>
<div class="tooltip-content" data-type="tooltip-content">
	<ul class="vote-topic-info">
		<li><i class="icon-native-plus"></i> {$oTopic->getCountVoteUp()}</li>
		<li><i class="icon-native-minus"></i> {$oTopic->getCountVoteDown()}</li>
		<li><i class="icon-native-eye-open"></i> {$oTopic->getCountVoteAbstain()}</li>
		<li><i class="icon-native-asterisk"></i> {$oTopic->getCountVote()}</li>

		{hook run='topic_show_vote_stats' topic=$oTopic}
	</ul>
</div>
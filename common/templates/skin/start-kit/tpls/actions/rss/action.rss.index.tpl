<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" {$oRss->getRssAttributesStr()}>
    {foreach $oRss->getChannels() as $oRssChannel}
    <channel>
        <title>{$oRssChannel->getTitle()}</title>
        <link>{$oRssChannel->getLink()}</link>
        <description><![CDATA[{$oRssChannel->getDescription()}]]></description>
        <language>{$oRssChannel->getLanguage()}</language>
        <managingEditor>{$oRssChannel->getManagingEditor()}</managingEditor>
        <webMaster>{$oRssChannel->getWebMaster()}</webMaster>
        <generator>{$oRssChannel->getGenerator()}</generator>
        {foreach $oRssChannel->getItems() as $oRssItem}
            <item>
                <title>{$oRssItem->getTitle()|escape:'html'}</title>
                <guid isPermaLink="true">{$oRssItem->getGuid()}</guid>
                <link>{$oRssItem->getLink()}</link>
                <author>{$oRssItem->getAuthor()}</author>
                <description><![CDATA[{$oRssItem->getDescription()}]]></description>
                <pubDate>{$oRssItem->getPubDate()}</pubDate>
                {foreach $oRssItem->getCategories() as $sCategory}
                    <category>{$sCategory}</category>
                {/foreach}
            </item>
        {/foreach}
    </channel>
    {/foreach}
</rss>

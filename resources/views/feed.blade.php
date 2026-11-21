<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title><![CDATA[{{ $siteName }}]]></title>
        <link>{{ $homeUrl }}</link>
        <atom:link href="{{ $feedUrl ?? url('/feed.xml') }}" rel="self" type="application/rss+xml"/>
        <description>Latest news from {{ $siteName }}</description>
        <language>{{ $locale }}</language>
        <lastBuildDate>{{ now()->format('D, d M Y H:i:s O') }}</lastBuildDate>
        @foreach($items as $item)
        <item>
            <title><![CDATA[{{ $item['title'] }}]]></title>
            <link>{{ $item['link'] }}</link>
            <guid isPermaLink="true">{{ $item['link'] }}</guid>
            <description><![CDATA[{{ $item['description'] }}]]></description>
            <pubDate>{{ $item['pubDate'] }}</pubDate>
        </item>
        @endforeach
    </channel>
</rss>

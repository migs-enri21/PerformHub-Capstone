<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class SocialMedia
{
    public static function formatCount(?int $count): string
    {
        if ($count === null || $count < 0) {
            return '—';
        }

        if ($count >= 1_000_000) {
            return rtrim(rtrim(number_format($count / 1_000_000, 1), '0'), '.').'M';
        }

        if ($count >= 1_000) {
            return rtrim(rtrim(number_format($count / 1_000, 1), '0'), '.').'K';
        }

        return number_format($count);
    }

    public static function facebookPagePluginUrl(string $pageUrl): string
    {
        return 'https://www.facebook.com/plugins/page.php?href='.urlencode($pageUrl)
            .'&tabs&width=340&height=154&small_header=true&adapt_container_width=true&hide_cover=false&show_facepile=false';
    }

    public static function youtubeSubscribeEmbedUrl(string $channelId): string
    {
        return 'https://www.youtube.com/subscribe_embed?channelid='.urlencode($channelId);
    }

    public static function youtubeChannelId(string $url): ?string
    {
        if (preg_match('#youtube\.com/channel/(UC[\w-]+)#i', $url, $matches)) {
            return $matches[1];
        }

        if (preg_match('#youtube\.com/@([\w.\-]+)#i', $url, $matches)) {
            return self::resolveYoutubeChannelIdByHandle($matches[1]);
        }

        return null;
    }

    public static function resolveYoutubeChannelIdByHandle(string $handle): ?string
    {
        $apiKey = config('services.youtube.key');

        if (! $apiKey) {
            return null;
        }

        $response = Http::timeout(5)->get('https://www.googleapis.com/youtube/v3/channels', [
            'part' => 'id',
            'forHandle' => ltrim($handle, '@'),
            'key' => $apiKey,
        ]);

        if (! $response->successful()) {
            return null;
        }

        return $response->json('items.0.id');
    }

    public static function fetchYoutubeSubscriberCount(string $url): ?int
    {
        $channelId = self::youtubeChannelId($url);
        $apiKey = config('services.youtube.key');

        if (! $channelId || ! $apiKey) {
            return null;
        }

        $response = Http::timeout(5)->get('https://www.googleapis.com/youtube/v3/channels', [
            'part' => 'statistics',
            'id' => $channelId,
            'key' => $apiKey,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $count = $response->json('items.0.statistics.subscriberCount');

        return is_numeric($count) ? (int) $count : null;
    }

    /** @return array<string, array{label: string, metric: string}> */
    public static function platformMeta(): array
    {
        return [
            'facebook' => ['label' => 'Facebook', 'metric' => 'Followers'],
            'instagram' => ['label' => 'Instagram', 'metric' => 'Followers'],
            'youtube' => ['label' => 'YouTube', 'metric' => 'Subscribers'],
            'tiktok' => ['label' => 'TikTok', 'metric' => 'Followers'],
            'twitter' => ['label' => 'Twitter/X', 'metric' => 'Followers'],
        ];
    }
}

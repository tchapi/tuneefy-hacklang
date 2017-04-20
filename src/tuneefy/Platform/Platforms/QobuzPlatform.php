<?php

namespace tuneefy\Platform\Platforms;

use tuneefy\MusicalEntity\Entities\AlbumEntity;
use tuneefy\MusicalEntity\Entities\TrackEntity;
use tuneefy\Platform\Platform;
use tuneefy\Platform\PlatformResult;
use tuneefy\Platform\WebStreamingPlatformInterface;
use tuneefy\Utils\Utils;

class QobuzPlatform extends Platform implements WebStreamingPlatformInterface
{
    const NAME = 'Qobuz';
    const TAG = 'qobuz';
    const COLOR = '2C8FAE';

    const API_ENDPOINT = 'http://www.qobuz.com/api.json/0.2/';
    const API_METHOD = Platform::METHOD_GET;

    protected $endpoints = [
        Platform::LOOKUP_TRACK => self::API_ENDPOINT.'track/get',
        Platform::LOOKUP_ALBUM => self::API_ENDPOINT.'album/get',
        Platform::LOOKUP_ARTIST => self::API_ENDPOINT.'artist/get',
        Platform::SEARCH_TRACK => self::API_ENDPOINT.'track/search',
        Platform::SEARCH_ALBUM => self::API_ENDPOINT.'album/search',
       // Platform::SEARCH_ARTIST => self::API_ENDPOINT . "artist/search"
    ];
    protected $terms = [
        Platform::LOOKUP_TRACK => 'track_id',
        Platform::LOOKUP_ALBUM => 'album_id',
        Platform::LOOKUP_ARTIST => 'artist_id',
        Platform::SEARCH_TRACK => 'query',
        Platform::SEARCH_ALBUM => 'query',
       // Platform::SEARCH_ARTIST => "query"
    ];
    protected $options = [
        Platform::LOOKUP_TRACK => [],
        Platform::LOOKUP_ALBUM => [],
        Platform::LOOKUP_ARTIST => [],
        Platform::SEARCH_TRACK => ['limit' => Platform::LIMIT],
        Platform::SEARCH_ALBUM => ['limit' => Platform::LIMIT],
       // Platform::SEARCH_ARTIST => Map { "limit" => Platform::LIMIT }
    ];

    // http://player.qobuz.com/#!/track/23860968
    const REGEX_QOBUZ_TRACK = "/\/track\/(?P<track_id>".Platform::REGEX_NUMERIC_ID.")[\/]?$/";

    // http://player.qobuz.com/#!/album/0060254728859
    const REGEX_QOBUZ_ALBUM = "/\/album\/(?P<album_id>".Platform::REGEX_NUMERIC_ID.")[\/]?$/";

    // http://player.qobuz.com/#!/artist/2131688
    const REGEX_QOBUZ_ARTIST = "/\/artist\/(?P<artist_id>".Platform::REGEX_NUMERIC_ID.")[\/]?$/";

    // http://www.qobuz.com/fr-fr/album/mon-premier-ep-salut-cest-cool/0060254728859
    const REGEX_QOBUZ_ALBUM_SITE = "/\/album\/".Platform::REGEX_FULLSTRING."\/(?P<album_id>".Platform::REGEX_NUMERIC_ID.")[\/]?$/";

    public function hasPermalink(string $permalink): bool
    {
        return strpos($permalink, 'qobuz.com') !== false;
    }

    protected function addContextOptions(array $data): array
    {
        $data['app_id'] = $this->key;
        return $data;
    }

    private function getPlayerUrlFromTrackId(string $id): string
    {
        return sprintf('http://player.qobuz.com/#!/track/%s', $id);
    }

    private function getPlayerUrlFromAlbumId(string $id): string
    {
        return sprintf('http://player.qobuz.com/#!/album/%s', $id);
    }

    public function expandPermalink(string $permalink, int $mode)//: ?PlatformResult
    {
        $musical_entity = null;
        $query_words = [$permalink];

        $match = [];

        if (preg_match(self::REGEX_QOBUZ_TRACK, $permalink, $match)) {
            $response = $this->fetchSync(Platform::LOOKUP_TRACK, $match['track_id']);

            if ($response === null || (property_exists($response->data, 'status') && $response->data->status === 'error')) {
                return null;
            }

            $entity = $response->data;
            $musical_entity = new TrackEntity($entity->title, new AlbumEntity($entity->album->title, $entity->album->artist->name, $entity->album->image->small));
            $musical_entity->addLink(static::TAG, $this->getPlayerUrlFromTrackId(''.$entity->id));
            
            $query_words = [
                $musical_entity->getAlbum()->getArtist(),
                $musical_entity->getSafeTitle(),
            ];
        } elseif (preg_match(self::REGEX_QOBUZ_ALBUM, $permalink, $match) || preg_match(self::REGEX_QOBUZ_ALBUM_SITE, $permalink, $match)) {
            $response = $this->fetchSync(Platform::LOOKUP_ALBUM, $match['album_id']);
            if ($response === null || (property_exists($response->data, 'status') && $response->data->status === 'error')) {
                return null;
            }

            $entity = $response->data;
            $musical_entity = new AlbumEntity($entity->title, $entity->artist->name, $entity->image->small);
            $musical_entity->addLink(static::TAG, $this->getPlayerUrlFromAlbumId(''.$entity->id));

            $query_words = [
                $musical_entity->getArtist(),
                $musical_entity->getSafeTitle(),
            ];
        } elseif (preg_match(self::REGEX_QOBUZ_ARTIST, $permalink, $match)) {
            $response = $this->fetchSync(Platform::LOOKUP_ARTIST, $match['artist_id']);
            if ($response === null || (property_exists($response->data, 'status') && $response->data->status === 'error')) {
                return null;
            }

            $query_words = [$response->data->name];
        }

        // Consolidate results
        $metadata = ['query_words' => $query_words];

        if ($musical_entity !== null) {
            $metadata['platform'] = $this->getName();
        }

        return new PlatformResult($metadata, $musical_entity);
    }

    public function search(int $type, string $query, int $limit, int $mode)
    {
        $response = $this->fetch($type, $query);

        if ($response === null) {
            return null;
        }
        $entities = $response->data;

        switch ($type) {
            case Platform::SEARCH_TRACK:
                $results = $entities->tracks->items;
                break;
            case Platform::SEARCH_ALBUM:
                $results = $entities->albums->items;
                break;
        }
        $length = min(count($results), $limit ? $limit : Platform::LIMIT);

        if ($length === 0) {
            return null;
        }
        $musical_entities = [];

        // Normalizing each track found
        for ($i = 0; $i < $length; ++$i) {
            $current_item = $results[$i];

            if ($type === Platform::SEARCH_TRACK && $current_item->album->artist->name !== null) {
                $musical_entity = new TrackEntity($current_item->title, new AlbumEntity($current_item->album->title, $current_item->album->artist->name, $current_item->album->image->small));
                $musical_entity->addLink(static::TAG, $this->getPlayerUrlFromTrackId(''.$current_item->id));
            } elseif ($type === Platform::SEARCH_ALBUM) {
                $musical_entity = new AlbumEntity($current_item->title, $current_item->artist->name, $current_item->image->small);
                $musical_entity->addLink(static::TAG, $this->getPlayerUrlFromAlbumId(''.$current_item->id));
            } else {
                $musical_entity = null;
            }

            $musical_entities[] = new PlatformResult(['score' => Utils::indexScore($i)], $musical_entity);
        }

        return $musical_entities;
    }
}

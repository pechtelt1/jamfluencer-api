<?php

use App\Models\User;
use App\Playback\Jobs\StorePlaylist as StorePlaylistJob;
use App\Playback\Playlist as PlaylistModel;
use App\Spotify\Album;
use App\Spotify\Facades\Spotify;
use App\Spotify\Playlist;
use App\Spotify\Track;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

it('stores playlist', function () {
    Cache::put(
        'jam',
        [
            'user' => User::factory()->withSpotify()->create()->id,
        ]
    );
    Spotify::shouldReceive('setToken')->once()->andReturnSelf();
    Spotify::shouldReceive('playlist')->once()->with($id = Str::random(), true)->andReturn(
        new Playlist(
            name: $name = $this->faker->name(),
            id: $id,
            images: [],
            tracks: [],
            totalTracks: 0,
            next: '',
            url: $url = $this->faker->url(),
            snapshot: $snapshot = Str::random()
        )
    );

    App::make(StorePlaylistJob::class, ['id' => $id])->handle();

    expect(PlaylistModel::query()->where([
        'id' => $id,
        'name' => $name,
        'snapshot' => $snapshot,
        'url' => $url,
    ])->exists())->toBeTrue();
});

it('associates the tracks', function () {
    Cache::put(
        'jam',
        [
            'user' => User::factory()->withSpotify()->create()->id,
        ]
    );
    Spotify::shouldReceive('setToken')->once()->andReturnSelf();
    Spotify::shouldReceive('playlist')->once()->with($id = Str::random(), true)->andReturn(
        new Playlist(
            name: $this->faker->name(),
            id: $id,
            images: [],
            tracks: $tracks = [
                new Track(
                    $this->faker->name(),
                    new Album(
                        Str::random(),
                        $this->faker->name(),
                        []
                    ),
                    [],
                    Str::random()
                ),
                new Track(
                    $this->faker->name(),
                    new Album(
                        Str::random(),
                        $this->faker->name(),
                        []
                    ),
                    [],
                    Str::random()
                ),
                new Track(
                    $this->faker->name(),
                    new Album(
                        Str::random(),
                        $this->faker->name(),
                        []
                    ),
                    [],
                    Str::random()
                ),
            ],
            totalTracks: count($tracks),
            next: '',
            url: $this->faker->url(),
            snapshot: Str::random()
        )
    );

    App::make(StorePlaylistJob::class, ['id' => $id])->handle();

    /** @noinspection PhpPossiblePolymorphicInvocationInspection */
    expect(PlaylistModel::query()->sole()
        ->tracks)->toHaveCount(count($tracks));

});

it('logs who added tracks', function () {
    Cache::put(
        'jam',
        [
            'user' => User::factory()->withSpotify()->create()->id,
        ]
    );
    Spotify::shouldReceive('setToken')->once()->andReturnSelf();
    Spotify::shouldReceive('playlist')->once()->with($id = Str::random(), true)->andReturn(
        new Playlist(
            name: $this->faker->name(),
            id: $id,
            images: [],
            tracks: $tracks = [
                new Track(
                    name: $this->faker->name(),
                    album: new Album(
                        Str::random(),
                        $this->faker->name(),
                        []
                    ),
                    artists: [],
                    id: Str::random(),
                    added_by: Str::random()
                ),
                new Track(
                    name: $this->faker->name(),
                    album: new Album(
                        Str::random(),
                        $this->faker->name(),
                        []
                    ),
                    artists: [],
                    id: Str::random(),
                    added_by: Str::random()
                ),
                new Track(
                    name: $this->faker->name(),
                    album: new Album(
                        Str::random(),
                        $this->faker->name(),
                        []
                    ),
                    artists: [],
                    id: Str::random(),
                    added_by: Str::random()
                ),
            ],
            totalTracks: count($tracks),
            next: '',
            url: $this->faker->url(),
            snapshot: Str::random()
        )
    );

    App::make(StorePlaylistJob::class, ['id' => $id])->handle();

    expect(PlaylistModel::query()->sole()->tracks()->wherePivotNull('added_by')->exists())->toBeFalse();
});
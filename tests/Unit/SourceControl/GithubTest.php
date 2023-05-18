<?php

namespace Tests\Unit\SourceControl;

use App\SourceControl\Entities\GitRepository;
use App\SourceControl\Github;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GithubTest extends TestCase
{
    /** @test */
    public function it_knows_if_it_cant_connect()
    {
        Http::fake([
            'https://api.github.com/user' => Http::response([], 403),
        ]);

        $this->assertFalse((new Github(''))->canConnect());
    }

    /** @test */
    public function it_can_check_if_it_can_connect()
    {
        Http::fake([
            'https://api.github.com/user' => ['id' => 123],
        ]);

        $this->assertTrue((new Github(''))->canConnect());
    }

    /** @test */
    public function it_can_list_all_repositories_of_the_current_user()
    {
        Http::fake([
            'https://api.github.com/user/repos?type=all&per_page=100&page=1' => function (Request $request) {
                return Http::response([
                    ['full_name' => 'demo/laravel-app1', 'private' => true, 'ssh_url' => 'git@github.com:demo/laravel-app1.git'],
                ], headers: [
                    'Link' => '<https://api.github.com/user/repos?type=all&per_page=100&page=2>; rel="next", <https://api.github.com/user/repos?type=all&per_page=100&page=2>; rel="last"',
                ]);
            },
            'https://api.github.com/user/repos?type=all&per_page=100&page=2' => function (Request $request) {
                return Http::response([
                    ['full_name' => 'demo/laravel-app2', 'private' => false, 'clone_url' => 'https://github.com/demo/laravel-app2.git'],
                ]);
            },
        ]);

        $this->assertEquals([
            new GitRepository('demo/laravel-app1', 'git@github.com:demo/laravel-app1.git'),
            new GitRepository('demo/laravel-app2', 'https://github.com/demo/laravel-app2.git'),
        ], (new Github(''))->findRepositories()->all());
    }

    /** @test */
    public function it_can_add_a_public_key_to_the_current_users_profile()
    {
        Http::fake([
            'https://api.github.com/user/keys' => function (Request $request) {
                $this->assertEquals('title', $request['title']);
                $this->assertEquals('public-key', $request['key']);

                return Http::response();
            },
        ]);

        $this->assertTrue((new Github(''))->addKey('title', 'public-key'));
    }
}

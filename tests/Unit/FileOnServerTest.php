<?php

namespace Tests\Unit;

use App\FileOnServer;
use Tests\TestCase;

class FileOnServerTest extends TestCase
{
    /** @test */
    public function it_can_return_name_with_context()
    {
        $name = 'file.txt';
        $description = 'This is a test file';
        $path = '/path/to/file.txt';
        $context = 'public';

        $file = new FileOnServer($name, $description, $path);
        $this->assertEquals($name, $file->name);

        $file = new FileOnServer($name, $description, $path, null, $context);
        $this->assertEquals($name.' ('.$context.')', $file->nameWithContext());

        $file = new FileOnServer($name, $description, $path, null, null);
        $this->assertEquals($name, $file->nameWithContext());
    }

    /** @test */
    public function it_can_returnm_route_parameter()
    {
        $name = 'file.txt';
        $description = 'This is a test file';
        $path = '/path/to/file.txt';

        $file = new FileOnServer($name, $description, $path);

        $routeParameter = $file->routeParameter();

        $this->assertEquals($path, FileOnServer::pathFromRouteParameter($routeParameter));
    }
}

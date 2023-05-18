<?php

namespace App\Http\Controllers;

use App\FileOnServer;
use App\Models\Server;
use App\Tasks\GetFile;
use App\Tasks\UploadFile;
use Illuminate\Http\Request;
use ProtoneMedia\Splade\Facades\Splade;
use ProtoneMedia\Splade\Facades\Toast;
use ProtoneMedia\Splade\SpladeTable;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Server $server)
    {
        return view('servers.files.index', [
            'server' => $server,
            'files' => SpladeTable::for($server->files()->editableFiles())
                ->column('name', __('Name'))
                ->column('description', __('Description'))
                ->rowModal(fn (FileOnServer $file) => $file->editRoute($server)),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Server $server, string $file)
    {
        $lines = intval(request()->query('lines') ?: 100);

        // Lines should be minimum 1 and maximum 1000
        $lines = max(1, min(1000, $lines));

        $path = FileOnServer::pathFromRouteParameter($file);

        /** @var FileOnServer|null */
        $file = $server->files()->allLogFiles()->firstWhere('path', $path);

        abort_if($file === null, 404);

        $contents = Splade::onLazy(fn () => $server->runTask(new GetFile($file->path, $lines))
            ->asRoot()
            ->throw()
            ->dispatch()
            ->getBuffer()
        );

        return view('servers.files.show', [
            'server' => $server,
            'file' => $file,
            'contents' => $contents,
            'lines' => $lines,
        ]);
    }

    /**
     * Helper method to find the FileOnServer instance from the route parameter.
     */
    private function findEditableFileByRouteParameter(Server $server, string $file): FileOnServer
    {
        // https://reinink.ca/articles/optimizing-circular-relationships-in-laravel
        $server->sites->each->setRelation('server', $server);

        $path = FileOnServer::pathFromRouteParameter($file);

        /** @var FileOnServer|null */
        $file = $server->files()->allEditableFiles()->firstWhere('path', $path);

        abort_if($file === null, 404);

        return $file;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Server $server, string $file)
    {
        $file = $this->findEditableFileByRouteParameter($server, $file);

        $contents = $server->runTask(new GetFile($file->path))
            ->asRoot()
            ->throw()
            ->dispatch()
            ->getBuffer();

        return view('servers.files.edit', [
            'server' => $server,
            'file' => $file,
            'contents' => $contents,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Server $server, string $file)
    {
        $file = $this->findEditableFileByRouteParameter($server, $file);

        $request->validate([
            'contents' => array_filter(['nullable', 'string', $file->validationRule]),
        ]);

        $server->runTask(new UploadFile($file->path, $request->input('contents')))
            ->asRoot()
            ->throw()
            ->dispatch();

        if ($callback = $file->afterUpdating) {
            dispatch(fn () => $callback());
        }

        $this->logActivity(__("Updated file ':path' on server ':server'", ['path' => $file->path, 'server' => $server->name]));

        Toast::message(__('The file will be updated. This may take a few seconds.'));

        return back(fallback: route('servers.files.index', $server));
    }
}

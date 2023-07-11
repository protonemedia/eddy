<?php

namespace App\Http\Controllers;

use App\Enum;
use App\FilesystemDriver;
use App\Http\Requests\CreateDiskRequest;
use App\Http\Requests\UpdateDiskRequest;
use App\Models\Disk;
use ProtoneMedia\Splade\Facades\Toast;
use ProtoneMedia\Splade\SpladeTable;

/**
 * @codeCoverageIgnore Handled by Dusk tests.
 */
class DiskController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Disk::class, 'disk');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('disks.index', [
            'disks' => SpladeTable::for($this->user()->disks()->with(['user', 'team']))
                ->column('name', __('Name'))
                ->column('filesystem_driver_name', __('Driver'))
                ->column('actions', label: '', alignment: 'right')
                ->rowModal(fn (Disk $disk) => route('disks.edit', $disk))
                ->defaultSort('name')
                ->paginate(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('disks.create', [
            'filesystemDrivers' => Enum::options(FilesystemDriver::class),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateDiskRequest $request)
    {
        $disk = $this->user()->disks()->create($request->validated());

        $this->logActivity(__("Created backup disk ':name'", ['name' => $disk->name]), $disk);

        Toast::message(__('Backup disk created.'));

        return to_route('disks.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Disk $disk)
    {
        if (data_get($disk, 'configuration.s3_endpoint')) {
            data_set($disk->configuration, 's3_custom_endpoint', true);
        }

        return view('disks.edit', [
            'disk' => $disk,
            'filesystemDrivers' => Enum::options(FilesystemDriver::class),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDiskRequest $request, Disk $disk)
    {
        $disk->update($request->validated());

        $this->logActivity(__("Updated backup disk ':name'", ['name' => $disk->name]), $disk);

        Toast::message(__('Backup disk updated.'));

        return to_route('disks.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Disk $disk)
    {
        if ($disk->backups()->exists()) {
            Toast::backdrop()
                ->center()
                ->autoDismiss(0)
                ->warning()
                ->message(__('You cannot delete a backup disk that is used by a backup.'));

            return back(fallback: route('disks.index'));
        }

        $disk->delete();

        $this->logActivity(__("Deleted backup disk ':name'", ['name' => $disk->name]), $disk);

        Toast::message(__('Backup disk deleted.'));

        return to_route('disks.index');
    }
}

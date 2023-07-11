<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use App\Models\BackupJob;
use App\Models\BackupJobStatus;
use App\Models\CouldNotCreateBackupJobException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class BackupJobController extends Controller
{
    /**
     * Dispatch the backup with the given token.
     */
    public function store(Backup $backup, string $token)
    {
        if ($token !== $backup->dispatch_token) {
            abort(403);
        }

        $teamSubscriptionOptions = $backup->server->team->subscriptionOptions();

        if (! $teamSubscriptionOptions->hasBackups()) {
            abort(402, 'The current subscription plan does not allow backups.');
        }

        try {
            $backup->createAndDispatchJob();
        } catch (CouldNotCreateBackupJobException) {
            return response()->noContent(409);
        }

        return response()->noContent(200);
    }

    /**
     * Display the specified resource.
     */
    public function show(BackupJob $backupJob)
    {
        abort_unless($backupJob->status === BackupJobStatus::Pending, 403, 'This backup job is not pending.');

        $backup = $backupJob->backup;

        $data = [
            'name' => $backupJob->generateOutputFilename(),
            'database_password' => $backup->server->database_password,
            'databases' => $backup->databases->pluck('name', 'id'),
            'disk' => $backupJob->disk->configurationForStorageBuilder($backup->server),
            'include_files' => $backup->include_files,
            'exclude_files' => $backup->exclude_files,
            'patch_url' => URL::relativeSignedRoute('backup-job.update', $backupJob),
        ];

        $backupJob->update(['status' => BackupJobStatus::Running]);

        return $data;
    }

    /**
     * Update the specified resource.
     */
    public function update(BackupJob $backupJob, Request $request)
    {
        abort_unless($backupJob->status === BackupJobStatus::Running, 403, 'This backup job is not running.');

        $data = $request->validate([
            'success' => ['required', 'boolean'],
            'error' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
        ]);

        $request->boolean('success')
            ? $backupJob->markAsFinished($data['size'] ?? 0)
            : $backupJob->markAsFailed($data['error'] ?? 'Unknown error.', $data['size'] ?? 0);

        $deletableBackups = $backupJob->backup->cleanupAndFindDeletableBackups();

        $backupJob->mailResultsIfUserShouldBeNotified();

        return [
            'backups_to_delete' => $deletableBackups,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\BackupJobStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BackupJob>
 */
class BackupJobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'backup_id' => BackupFactory::new(),
            'disk_id' => DiskFactory::new(),
            'status' => BackupJobStatus::Pending,
        ];
    }
}

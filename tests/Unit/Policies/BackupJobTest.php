<?php

namespace Tests\Unit\Policies;

use App\Policies\BackupJobPolicy;
use Database\Factories\BackupFactory;
use Database\Factories\BackupJobFactory;
use Database\Factories\ServerFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_team_members_to_view_the_backup_job()
    {
        $user = UserFactory::new()->withPersonalTeam()->create();
        $server = ServerFactory::new()->forTeam($user->currentTeam)->create();
        $backup = BackupFactory::new()->forServer($server)->createdByUser($user)->create();

        $otherTeamUser = UserFactory::new()->create();
        $user->currentTeam->users()->attach($otherTeamUser);

        $randomUser = UserFactory::new()->withPersonalTeam()->create();

        $backupJob = BackupJobFactory::new()->create([
            'backup_id' => $backup->id,
            'disk_id' => $backup->disk_id,
        ]);

        $policy = new BackupJobPolicy;

        $this->assertTrue($policy->view($user, $backupJob));
        $this->assertTrue($policy->view($otherTeamUser, $backupJob));
        $this->assertFalse($policy->view($randomUser, $backupJob));
    }
}

@include('tasks.common-functions')

composer global require protonemedia/eddy-backup-cli
composer global exec eddy-backup-cli backup:run {{ $backupJob->cliUrl() }}
<x-task-shell-defaults />

@include('tasks.deployment.shell-variables')

# Create the necessary directories
mkdir -p {!! $repositoryDirectory !!}
mkdir -p {!! $sharedDirectory !!}
mkdir -p {!! $releaseDirectory !!}
mkdir -p {!! $logsDirectory !!}

# Cleanup old releases
@include('tasks.deployment.cleanup-old-releases')

@if($site->hook_before_updating_repository)
    echo "Running hook before updating repository"
    cd {!! $releaseDirectory !!}
    {!! $site->hook_before_updating_repository !!}
@endif

@if($site->repository_url)
    @include('tasks.deployment.update-repository')

    @if($site->hook_after_updating_repository)
        echo "Running hook after updating repository"
        cd {!! $releaseDirectory !!}
        {!! $site->hook_after_updating_repository !!}
    @endif

@endif

@unless($site->installed_at)
    @include('tasks.deployment.prepare-fresh-installation')
@endunless

@include('tasks.deployment.link-shared-directories')

@include('tasks.deployment.link-shared-files')

@include('tasks.deployment.make-directories-writable')

@if($site->hook_before_making_current)
    echo "Running hook before putting the site live"
    cd {!! $releaseDirectory !!}
    {!! $site->hook_before_making_current !!}
@endif

@include('tasks.deployment.make-deployment-current')

@if($site->hook_after_making_current)
    echo "Running hook after putting the site live"
    cd {!! $releaseDirectory !!}
    {!! $site->hook_after_making_current !!}
@endif

@if($site->repository_url)
    @include('tasks.deployment.send-repository-data')
@endif

echo "Done!"
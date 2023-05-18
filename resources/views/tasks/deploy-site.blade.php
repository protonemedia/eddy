<x-task-shell-defaults />

@include('tasks.deployment.shell-variables')

# Create the necessary directories
mkdir -p {!! $repositoryDirectory !!}
mkdir -p {!! $logsDirectory !!}

@if($site->installed_at && $site->hook_before_updating_repository)
    echo "Running hook before updating repository"
    cd {!! $repositoryDirectory !!}
    {!! $site->hook_before_updating_repository !!}
@endif

@if($site->repository_url)
    @include('tasks.deployment.update-repository')

    @if($site->hook_after_updating_repository)
        echo "Running hook after updating repository"
        cd {!! $repositoryDirectory !!}
        {!! $site->hook_after_updating_repository !!}
    @endif

    @include('tasks.deployment.send-repository-data')
@endif

@unless($site->installed_at)
    @include('tasks.deployment.prepare-fresh-installation')
@endunless

@if($site->installed_at && $site->type === \App\Models\SiteType::Wordpress)
    echo "Wordpress already installed!"
@endif

echo "Done!"
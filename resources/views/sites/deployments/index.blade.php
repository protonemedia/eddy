<x-splade-event private channel="teams.{{ auth()->user()->currentTeam->id }}" listen="DeploymentUpdated" />

<x-site-layout :site="$site" :title="__('Deployments')">
    <x-splade-table :for="$deployments">
        <x-splade-cell short_git_hash>
            <abbr title="{{ $item->git_hash }}">{{ $item->short_git_hash }}</abbr>
        </x-splade-cell>

        <x-splade-cell status>
            {{ $item->status->name }}
        </x-splade-cell>
    </x-splade-table>
</x-site-layout>

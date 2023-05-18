<x-task-shell-defaults />

@include('tasks.common-functions')

@include('tasks.apt-functions')

@foreach($provisionSteps() as $step)
    @include($step->getViewName())

    <x-task-callback :url="$callbackUrl()" :data="['provision_step_completed' => $step]" />
@endforeach

@foreach($softwareStack() as $software)
    @include($software->getInstallationViewName())

    <x-task-callback :url="$callbackUrl()" :data="['software_installed' => $software]" />
@endforeach

# See 'apt-update-upgrade'
waitForAptUnlock
apt-mark unhold cloud-init
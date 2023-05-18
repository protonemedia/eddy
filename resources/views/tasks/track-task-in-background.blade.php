<x-task-shell-defaults :exit-immediately="false" />

@include('tasks.common-functions')

DIRECTORY=$(dirname "$0")
FILENAME=$(basename "$0")
EXT="${FILENAME##*.}"
PATH_ACTUAL_SCRIPT="$DIRECTORY/${FILENAME%.*}-original.$EXT"

# Writing actual script to $PATH_ACTUAL_SCRIPT

cat > $PATH_ACTUAL_SCRIPT << '{{ $eof }}'
@includeWhen($actualTask->callbackUrl(), 'tasks.common-functions')

{!! $actualTask->getScript() !!}

{{ $eof }}

# Running actual script
@if($actualTask->getTimeout())
    timeout {{ $actualTask->getTimeout() }}s bash $PATH_ACTUAL_SCRIPT
@else
    bash $PATH_ACTUAL_SCRIPT
@endif
EXIT_CODE=$?

if [[ $EXIT_CODE -eq 0 ]]; then
    # Actual script finished successfully
    httpPostSilently "{!! $finishedUrl !!}"
elif [[ $EXIT_CODE -eq 124 ]]; then
    # Actual script timed out
    httpPostSilently "{!! $timeoutUrl !!}"
else
    # Actual script failed with exit status $EXIT_CODE
    httpPostSilently "{!! $failedUrl !!}" "{\"exit_code\":$EXIT_CODE}"
fi
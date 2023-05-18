# Send a POST request to the given URL, ignoring the response and errors
function httpPostSilently()
{
    if [ -z "${2:-}" ]; then
        (curl -X POST --silent --max-time 15 --output /dev/null $1 || true)
    else
        (curl -X POST --silent --max-time 15 --output /dev/null $1 -H 'Content-Type: application/json' --data $2 || true)
    fi
}

function httpPostRawSilently()
{
    (curl -X POST --silent --max-time 15 --output /dev/null $1 --data "$2" || true)
}

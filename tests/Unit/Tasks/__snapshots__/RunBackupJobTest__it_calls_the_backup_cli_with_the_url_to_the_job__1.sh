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

composer global require protonemedia/eddy-backup-cli
composer global exec eddy-backup-cli backup:run https://webhook.app/backup-job/00000000000000000000000001?signature=ed5471d7f343d177b0e7690c78ed11eee4157b2846ab8a4c6e1cb77532745867
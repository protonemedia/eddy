DEPLOYMENT_KEEP="{{ $site->latestFinishedDeployment ? $site->latestFinishedDeployment->created_at->timestamp : 'none' }}"

# Get a list of all deployments, sorted by timestamp in ascending order
DEPLOYMENT_LIST=($(ls -1 {!! $releasesDirectory !!} | sort -n))

# Determine how many deployments to delete
NUM_TO_DELETE=$((${#DEPLOYMENT_LIST[@]} - {{ $site->deployment_releases_retention }}))

# Loop through the deployments to delete
for ((i=0; i<$NUM_TO_DELETE; i++)); do
    DEPLOY=${DEPLOYMENT_LIST[$i]}
    # Skip the deployment to keep
    if [[ $DEPLOY == $DEPLOYMENT_KEEP ]]; then
        continue
    fi

    # Delete the deployment
    rm -rf {!! $releasesDirectory !!}/$DEPLOY
done

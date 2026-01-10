#!/bin/bash
set -x

while true; do
    if [ -n "$ECS_CONTAINER_METADATA_URI_V4" ]; then
        if METADATA=$(curl -sf "$ECS_CONTAINER_METADATA_URI_V4/task"); then
            FAMILY=$(echo "$METADATA" | jq -r '.Family')
            REVISION=$(echo "$METADATA" | jq -r '.Revision')
            GROUP_ID="${FAMILY}:${REVISION}"
            DD_HOSTNAME=$(echo "$METADATA" | jq -r '.TaskARN' | awk -F'/' '{print $NF}')

            # Get Datadog API key
            DD_API_KEY=$(curl -sf -H "Authorization: ${DD_KEY_SERVER_KEY}" \
                "${DD_KEY_SERVER_URL}/dd-key?projectId=${DD_KEY_SERVER_PROJECT_ID}&groupId=${GROUP_ID}&hostId=${DD_HOSTNAME}")

            if [ -n "$DD_API_KEY" ]; then
                echo "Updating Datadog configuration..."
                sed -i "s/api_key:.*/api_key: ${DD_API_KEY}/" /etc/datadog-agent/datadog.yaml
                sed -i "s/hostname:.*/hostname: ${DD_HOSTNAME}/" /etc/datadog-agent/datadog.yaml
                
                # Start Datadog agent via supervisorctl
                /usr/local/bin/supervisorctl start datadog-agent trace-agent process-agent
                
                echo "Successfully updated API key and restarted agent"
            else
                echo "Skipping update, no API key found"
            fi

            exit 0
        fi
    fi
    
    echo "Waiting for metadata/API key..."
    sleep 10
done
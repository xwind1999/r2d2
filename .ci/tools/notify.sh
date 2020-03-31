#!/usr/bin/env bash

function notify_team() {
    if [[ "BROADCAST_CHANNEL" = "" ]]; then return; fi

    echo '===== SENDING NOTIFICATIONS ====='
    BROADCAST_MESSAGE="**[Deployment started - ${CI_COMMIT_TITLE}](${CI_PIPELINE_URL})**"
    PAYLOAD=$(echo 'payload={
        "attachments": [
            {
                "fields": [
                    {
                        "value": "**Environment:** '${ENVIRONMENT_NAME}'",
                        "short": true
                    },
                    {
                        "value": "**Started by:** '${GITLAB_USER_NAME}'",
                        "short": true
                    },
                    {
                        "value": "**Commit ref:** '${CI_COMMIT_SHA}'",
                        "short": true
                    },
                    {
                        "value": "**Branch:** '${CI_BUILD_REF_NAME}'",
                        "short": true
                    }
                ],
                "fallback": "'${BROADCAST_MESSAGE}'",
                "text": "'${BROADCAST_MESSAGE}'",
                "color": "'${DEPLOY_BOT_COLOR}'",
                "thumb_url": "'${DEPLOY_BOT_IMAGE}'"
            }
        ],
        "username": "'${DEPLOY_BOT_NAME}'",
        "icon_url": "'${DEPLOY_BOT_IMAGE}'",
        "channel": "'${BROADCAST_CHANNEL}'"
        }')

    curl --silent -i  -d "${PAYLOAD}" ${MATTERMOST_HOOK}
}

notify_team


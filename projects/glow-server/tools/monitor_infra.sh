#!/bin/bash

# cronで実行するためのスクリプト
# 監視対象のサービスが停止しているか、ストレージの使用率が閾値を超えている場合にSlackに通知する
# */10 * * * * cd /home/ec2-user/glow-server/tools/monitor_infra.sh >> /tmp/cron.log 2>&1

DISK_THRESHOLD=90 # 使用率がこの%を超えたら通知

# .envを読み込む
set -a
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/../api/.env"
set +a

# ====== サービス監視 ======
check_docker_services() {
  local services=$(docker compose config --services 2>/dev/null)
  local down_services=()
  local message=""

  for service in $services; do
    container_id=$(docker compose ps -q "$service" 2>/dev/null)

    if [ -z "$container_id" ]; then
      down_services+=("$service")
      continue
    fi

    running=$(docker inspect --format '{{.State.Running}}' "$container_id" 2>/dev/null)
    if [ "$running" != "true" ]; then
      down_services+=("$service")
    fi
  done

  if [ ${#down_services[@]} -ne 0 ]; then
    message+="*停止中のDockerサービス:*\n"
    for service in "${down_services[@]}"; do
      message+="- \`$service\`\n"
    done
  fi
  echo "$message"
}

# ====== ストレージ使用率監視 ======
check_storage_usage() {
  local storage_usage=$(df -P / | awk 'NR==2 {gsub(/%/, "", $5); print $5}')
  local message=""

  if [ "$storage_usage" -ge "$DISK_THRESHOLD" ]; then
    message="*ストレージ使用率:* \`${storage_usage}%\` (閾値: \`${DISK_THRESHOLD}%\`)"
  fi
  echo "$message"
}

# 通知メッセージの作成
create_notification_message() {
  local service_alert="$1"
  local storage_alert="$2"
  local message=""

  if [ -n "$service_alert" ] || [ -n "$storage_alert" ]; then
    message="サーバー監視アラート\n*環境名:* \`${APP_ENV}\`\n"

    if [ -n "$service_alert" ]; then
      message+="$service_alert"
    fi

    if [ -n "$storage_alert" ]; then
      message+="$storage_alert\n"
    fi
  fi

  echo "$message"
}

# Slack通知
send_slack_notification() {
  local message="$1"

  if [ -z "$message" ]; then
    # 正常
    return
  fi

   if [ -n "$SLACK_WEBHOOK_URL" ]; then
     curl -X POST -H 'Content-type: application/json' --data "{\"text\":\"$message\"}" "$SLACK_WEBHOOK_URL"
   fi
}

service_alert=$(check_docker_services)
storage_alert=$(check_storage_usage)
notification_message=$(create_notification_message "$service_alert" "$storage_alert")
send_slack_notification "$notification_message"

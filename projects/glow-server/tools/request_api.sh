#!/bin/bash

# CURL="curl -s -o /dev/null -w \"%{http_code}\n\""

LOOP_COUNT=${1:-1}
# SLEEP_SECONDS=0.5

SERVER_URL="localhost:8080"

request_api() {
  local METHOD=${1:-POST}
  local API_PATH=$2
  local DATA=$3

  echo "* $API_PATH"

  local RESPONSE=$(curl -s --location --request $METHOD "$SERVER_URL/api/$API_PATH" \
  "${HEADERS[@]}" \
  --data "$DATA")
  
  # レスポンスから必要な情報を取得する
  if [ "$API_PATH" = "game/update_and_fetch" ]; then
      USR_UNIT_ID=$(echo "$RESPONSE" | jq -r '.fetchOther.usrUnits[0].usrUnitId')
  fi

  # errorCodeの取得
  if echo "$RESPONSE" | jq -e 'type == "array"' > /dev/null; then
      # 配列の場合、最初の要素からerrorCodeを取得
      error_code=$(echo "$RESPONSE" | jq -r '.[0].errorCode')
  else
      # オブジェクトの場合、直接errorCodeを取得
      error_code=$(echo "$RESPONSE" | jq -r '.errorCode')
  fi

  # errorCodeがnullでないことを確認し、200かどうかをチェック
  if [ "$error_code" != "null" ]; then
      if [ "$error_code" -ne 200 ]; then
          echo "     Error: The errorCode is $error_code."
      fi
  fi

  # sleep $SLEEP_SECONDS
}

# 指定回数APIを叩く

for i in $(seq 1 $LOOP_COUNT); do
  echo "### $i ###"

  echo "* sign_up"
  ID_TOKEN=$(curl -s --location --request POST 'localhost:8080/api/sign_up' \
  --header 'Platform: 1' \
  --data '' | jq -r '.id_token')

  if [ -z "$ID_TOKEN" ]; then
    echo "IDトークンの取得に失敗しました"
    exit 1
  fi
  # sleep $SLEEP_SECONDS

  echo "* sign_in"
  ACCESS_TOKEN=$(curl -s --location 'localhost:8080/api/sign_in' \
  --header 'Content-Type: application/json' \
  --data '{
      "id_token": "'$ID_TOKEN'"
  }' | jq -r '.access_token')

  if [ -z "$ACCESS_TOKEN" ]; then
    echo "アクセストークンの取得に失敗しました"
    exit 1
  fi
  # sleep $SLEEP_SECONDS

  HEADERS=(
    --header "Access-Token: $ACCESS_TOKEN"
    --header 'Client-Version: 0.0.0'
    --header 'Platform: 1'
    --header 'Language: ja'
    --header 'Content-Type: application/json'
  )
  USR_UNIT_ID=''

  request_api GET game/version ''
  request_api POST game/update_and_fetch ''
  request_api GET game/fetch ''

  request_api POST stage/start '{"mstStageId": "normal_jig_00001"}'
  request_api POST stage/end '{"mstStageId": "normal_jig_00001", "inGameBattleLog": {"defeatBossEnemyCount": 999}}'

  request_api POST user/agree '{"tosVersion": 1, "privacyPolicyVersion": 1}'
  request_api POST user/change_name '{"name": "ゴリゴリゴリラ"}'

  request_api POST shop/trade_shop_item '{"mstShopItemId": "Coin_13"}'
  request_api POST shop/trade_pack '{"productSubId": "1"}'

  request_api POST unit/level_up '{"usrUnitId": "'$USR_UNIT_ID'", "level": 2}'

  request_api POST item/consume '{"mstItemId": "box_glo_00001", "amount": 1}'

  request_api POST party/save '{"parties": [{"partyNo": 1, "partyName": "party1", "units": ["'$USR_UNIT_ID'"]}]}'

  request_api POST outpost/enhance '{"mstOutpostEnhancementId": "enhance_1_1", "level": 2}'

  request_api POST idle_incentive/quick_receive_by_ad '{}'

  request_api POST mission/update_and_fetch '{}'
  request_api POST mission/receive_reward '{"missionType": "Daily", "mstMissionId": "daily_1"}'
  request_api POST mission/bulk_receive_reward '{"missionType": "Daily"}'

  request_api POST encyclopedia/receive_reward '{"mstUnitEncyclopediaRewardIds": ["1"]}'
done

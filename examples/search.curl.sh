#!/bin/bash

response=$(curl -X POST --silent -d client_id=administrator -d client_secret=password -d grant_type=client_credentials https://data.tuneefy.com/v2/auth/token)

token=$(echo $response | jq --raw-output '.access_token')

echo 🎉
curl --header "Authorization: Bearer $token" "https://data.tuneefy.com/v2/search/track/spotify?q=amon+tobin&limit=1"

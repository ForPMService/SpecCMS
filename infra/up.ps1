$ErrorActionPreference = "Stop"

docker compose -f "$PSScriptRoot/docker-compose.yml" up -d --build
docker compose -f "$PSScriptRoot/docker-compose.yml" ps

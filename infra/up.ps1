$ErrorActionPreference = "Stop"

docker compose -f "$PSScriptRoot/docker-compose.yml" up -d --build

$databaseExists = docker compose -f "$PSScriptRoot/docker-compose.yml" exec -T postgres sh -c 'psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" -tAc "SELECT 1 FROM pg_database WHERE datname = ''speccms_testing''"'

if ($LASTEXITCODE -ne 0) {
    throw "Could not check whether PostgreSQL database speccms_testing exists."
}

if ("$databaseExists".Trim() -ne "1") {
    docker compose -f "$PSScriptRoot/docker-compose.yml" exec -T postgres sh -c 'createdb -U "$POSTGRES_USER" speccms_testing'

    if ($LASTEXITCODE -ne 0) {
        throw "Could not create PostgreSQL database speccms_testing."
    }

    Write-Output "Created PostgreSQL database speccms_testing."
} else {
    Write-Output "PostgreSQL database speccms_testing already exists."
}

docker compose -f "$PSScriptRoot/docker-compose.yml" ps

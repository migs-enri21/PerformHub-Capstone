param(
    [string]$HostName = "127.0.0.1",
    [string]$Port = "8000"
)

# `php artisan serve` spawns its own child `php -S` process and does NOT
# forward `-d` ini overrides to it, so upload_max_filesize/post_max_size
# changes never reach the process that actually handles requests. This
# script runs PHP's built-in server directly (same docroot/router Laravel
# uses) so the 2 GB upload limits take effect.
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectRoot = Split-Path -Parent $scriptDir
$router = Join-Path $scriptDir "router.php"

Push-Location (Join-Path $projectRoot "public")
try {
    php -d upload_max_filesize=2048M `
        -d post_max_size=2048M `
        -d max_execution_time=600 `
        -d memory_limit=1024M `
        -S "${HostName}:${Port}" `
        $router
} finally {
    Pop-Location
}

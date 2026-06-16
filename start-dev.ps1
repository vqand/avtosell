$ErrorActionPreference = 'Stop'
$root = $PSScriptRoot

$php = Get-ChildItem "$env:LOCALAPPDATA\Microsoft\WinGet\Packages" -Filter php.exe -Recurse -ErrorAction SilentlyContinue |
       Select-Object -First 1 -ExpandProperty FullName
if (-not $php) { $php = 'php' }
$ini = Join-Path $root 'backend\php.dev.ini'

Write-Host "Starting MySQL80 service..." -ForegroundColor Cyan
if ((Get-Service MYSQL80).Status -ne 'Running') { Start-Service MYSQL80 }

Write-Host "Starting PHP API on http://localhost:8080 ..." -ForegroundColor Cyan
Start-Process $php -ArgumentList @('-c', $ini, '-S', 'localhost:8080', '-t', 'public', 'public/router.php') `
  -WorkingDirectory (Join-Path $root 'backend')

Write-Host "Starting Vite on http://localhost:5173 ..." -ForegroundColor Cyan
Start-Process 'npm' -ArgumentList 'run','dev' -WorkingDirectory (Join-Path $root 'frontend')

Write-Host "`nReady:" -ForegroundColor Green
Write-Host "  App  -> http://localhost:5173"
Write-Host "  API  -> http://localhost:8080/api/health"
Write-Host "  Admin login: admin@5vito.ru / admin123"

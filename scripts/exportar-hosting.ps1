param(
	[string]$Dominio = 'https://www.clinicalskin.cl'
)

$ErrorActionPreference = 'Stop'
$Compose = 'C:\docker\clinicalskin-woo\docker-compose.yml'
$Salida  = 'C:\docker\clinicalskin-woo\salida'
$ZipSrc  = Join-Path $Salida 'wordpress'
$Dump    = Join-Path $Salida 'base.sql'
$Zip     = Join-Path $Salida 'wordpress.zip'
$Tar     = Join-Path $Salida 'site.tar.gz'
$Dominio = $Dominio.TrimEnd('/')

if ( $Dominio -notmatch '^https://' ) {
	throw 'Usa el dominio final con HTTPS, por ejemplo https://www.clinicalskin.cl'
}

New-Item -ItemType Directory -Force $Salida | Out-Null

Write-Host '1/4 Exportando base de datos...'
$dumpCmd = 'mysqldump --single-transaction --hex-blob --routines --events --triggers -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" > /tmp/cs-base.sql'
& docker compose -f $Compose exec -T db sh -c $dumpCmd
if ( $LASTEXITCODE -ne 0 ) { throw 'Falló mysqldump.' }
& docker compose -f $Compose cp db:/tmp/cs-base.sql $Dump
if ( $LASTEXITCODE -ne 0 ) { throw 'No se pudo copiar base.sql.' }
& docker compose -f $Compose exec -T db sh -c 'rm -f /tmp/cs-base.sql'
if ( $LASTEXITCODE -ne 0 ) { throw 'No se pudo limpiar el temporal SQL del contenedor.' }

Write-Host '2/4 Preparando el sitio con URLs y permisos del hosting...'
$prepCmd = @"
set -e
rm -rf /tmp/cs-export
cp -a /var/www/html /tmp/cs-export
rm -rf /tmp/cs-export/static/.git /tmp/cs-export/static/woocommerce /tmp/cs-export/static/docs/woocommerce
find /tmp/cs-export/static -type f -name '*.md' -delete
find /tmp/cs-export/static -type f -name '*.html' -exec sed -i 's|http://localhost:8080|$Dominio|g;s|http://localhost/clinicalskin|$Dominio|g;s|https://clinicalskin.cl|$Dominio|g' {} +
find /tmp/cs-export -type d -exec chmod 755 {} +
find /tmp/cs-export -type f -exec chmod 644 {} +
tar -czf /tmp/site.tar.gz -C /tmp/cs-export .
"@
& docker compose -f $Compose exec -T wordpress sh -c $prepCmd
if ( $LASTEXITCODE -ne 0 ) { throw 'Falló la preparación de site.tar.gz.' }
& docker compose -f $Compose cp wordpress:/tmp/site.tar.gz $Tar
if ( $LASTEXITCODE -ne 0 ) { throw 'No se pudo copiar site.tar.gz.' }
& docker compose -f $Compose exec -T wordpress sh -c 'rm -f /tmp/site.tar.gz; rm -rf /tmp/cs-export'
if ( $LASTEXITCODE -ne 0 ) { throw 'No se pudieron limpiar los temporales del contenedor.' }

Write-Host '3/4 Creando wordpress.zip para el Administrador de Archivos...'
Remove-Item -Recurse -Force $ZipSrc -ErrorAction SilentlyContinue
New-Item -ItemType Directory -Force $ZipSrc | Out-Null
& tar.exe -xzf $Tar -C $ZipSrc
if ( $LASTEXITCODE -ne 0 ) { throw 'No se pudo extraer el tar temporal para crear el ZIP.' }
Compress-Archive -Path "$ZipSrc\*" -DestinationPath $Zip -Force
if ( $LASTEXITCODE -ne 0 ) { throw 'Falló la creación de wordpress.zip.' }
Remove-Item -Recurse -Force $ZipSrc

Write-Host '4/4 Exportaciones listas:'
Get-Item $Dump, $Zip, $Tar | Select-Object Name,@{n='MB';e={[math]::Round($_.Length / 1MB,1)}},LastWriteTime | Format-Table -AutoSize
Write-Host "Dominio incluido en los metadatos estáticos: $Dominio"
Write-Host 'El ZIP y el TAR omiten static/.git, static/woocommerce y los archivos Markdown internos.'

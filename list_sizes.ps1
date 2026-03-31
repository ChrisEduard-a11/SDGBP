$dirs = @('acciones','vistas','models')
foreach ($dir in $dirs) {
    $path = "c:\xampp\htdocs\SDGBP\$dir"
    Get-ChildItem "$path\*.php" | Select-Object Name, Length, @{N='Dir';E={$dir}} | Sort-Object Length
}

$root = "c:\xampp\htdocs\SDGBP"
$files = Get-ChildItem -Path $root -Include *.php -Recurse | Where-Object { $_.Length -lt 500 }
foreach ($file in $files) {
    if ($file.FullName -notmatch "vendor|PHPMailer|dompdf|fpdf|sweetalert") {
        Write-Host "$($file.Length)`t$($file.FullName)"
    }
}

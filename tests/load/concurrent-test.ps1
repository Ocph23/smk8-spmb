# Concurrent Registration Load Test
# Jalankan: .\concurrent-test.ps1 -Count 20 -BaseUrl "http://127.0.0.1:8000"

param(
    [int]$Count   = 20,
    [string]$BaseUrl = "http://127.0.0.1:8000"
)

$url     = "$BaseUrl/students/pendaftaran/daftar"
$results = [System.Collections.Concurrent.ConcurrentBag[object]]::new()
$jobs    = @()

Write-Host "Mengirim $Count request bersamaan ke $url ..." -ForegroundColor Cyan

$stopwatch = [System.Diagnostics.Stopwatch]::StartNew()

# Kirim semua request secara paralel
$jobs = 1..$Count | ForEach-Object {
    $i = $_
    Start-Job -ScriptBlock {
        param($url, $i)

        $ts    = [DateTimeOffset]::UtcNow.ToUnixTimeMilliseconds()
        $uid   = $i.ToString().PadLeft(4,'0') + ($ts % 100000000).ToString()
        $nik   = ('1111111111111111' + $uid).Substring($uid.Length)
        if ($nik.Length -lt 16) { $nik = $nik.PadLeft(16,'1') }
        $nik   = $nik.Substring(0,16)

        $year  = (Get-Date).Year - 16
        $dob   = "$year-0$($i % 9 + 1)-$($i % 28 + 1)".Replace('-0','- ').Trim()
        $dob   = "$year-" + ($i % 12 + 1).ToString().PadLeft(2,'0') + "-" + ($i % 28 + 1).ToString().PadLeft(2,'0')

        $body = @{
            full_name       = "Calon Siswa $i"
            nik             = $nik
            nisn            = $uid.Substring(0, [Math]::Min(10, $uid.Length))
            place_of_birth  = "Jayapura"
            date_of_birth   = $dob
            gender          = if ($i % 2 -eq 0) { "male" } else { "female" }
            religion        = "Islam"
            street          = "Jl. Test No.$i"
            rt              = "001"
            rw              = "001"
            district        = "Abepura"
            postal_code     = "99111"
            phone           = "08" + ($uid + "0000000000").Substring(0,10)
            email           = "siswa${i}.${ts}@loadtest.com"
            parent_name     = "Ayah $i"
            mother_name     = "Ibu $i"
            parent_phone    = "08" + ($i + 10000000).ToString().Substring(0,10)
            school_name     = "SMP Negeri 1 Jayapura"
            school_city     = "Jayapura"
            school_province = "Papua"
            major_1         = "1"
            major_2         = "2"
        } | ConvertTo-Json

        $headers = @{
            "Content-Type"     = "application/json"
            "Accept"           = "application/json"
            "X-Requested-With" = "XMLHttpRequest"
        }

        $start = Get-Date
        try {
            $response = Invoke-WebRequest -Uri $url -Method POST -Body $body -Headers $headers -TimeoutSec 30 -ErrorAction Stop
            $duration = ((Get-Date) - $start).TotalMilliseconds
            return @{ index=$i; status=$response.StatusCode; duration=$duration; error=$null }
        } catch {
            $duration = ((Get-Date) - $start).TotalMilliseconds
            $status   = if ($_.Exception.Response) { [int]$_.Exception.Response.StatusCode } else { 0 }
            return @{ index=$i; status=$status; duration=$duration; error=$_.Exception.Message }
        }
    } -ArgumentList $url, $i
}

# Tunggu semua job selesai
$jobs | Wait-Job | Out-Null
$stopwatch.Stop()

# Kumpulkan hasil
$allResults = $jobs | Receive-Job
$jobs | Remove-Job

# Analisis hasil
$success  = @($allResults | Where-Object { $_.status -eq 200 -or $_.status -eq 302 })
$validate = @($allResults | Where-Object { $_.status -eq 422 })
$errors   = @($allResults | Where-Object { $_.status -eq 500 -or $_.status -eq 0 })
$durations = @($allResults | Where-Object { $_.duration -gt 0 } | ForEach-Object { $_.duration })

Write-Host "`n===== HASIL LOAD TEST =====" -ForegroundColor Green
Write-Host "Total request  : $Count"
Write-Host "Total waktu    : $([Math]::Round($stopwatch.Elapsed.TotalSeconds, 2)) detik"
Write-Host ""
Write-Host "Berhasil (200/302) : $($success.Count)" -ForegroundColor Green
Write-Host "Validasi (422)     : $($validate.Count)" -ForegroundColor Yellow
Write-Host "Error (500/timeout): $($errors.Count)"  -ForegroundColor Red

if ($durations.Count -gt 0) {
    $avg = [Math]::Round(($durations | Measure-Object -Average).Average, 0)
    $max = [Math]::Round(($durations | Measure-Object -Maximum).Maximum, 0)
    $min = [Math]::Round(($durations | Measure-Object -Minimum).Minimum, 0)
    Write-Host ""
    Write-Host "Response time:"
    Write-Host "  Min : ${min}ms"
    Write-Host "  Avg : ${avg}ms"
    Write-Host "  Max : ${max}ms"
}

if ($errors.Count -gt 0) {
    Write-Host "`nDetail error:" -ForegroundColor Red
    $errors | ForEach-Object { Write-Host "  [$($_.index)] Status $($_.status): $($_.error)" }
}

Write-Host "===========================" -ForegroundColor Green

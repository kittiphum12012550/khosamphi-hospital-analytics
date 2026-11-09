<#
.SYNOPSIS
    Automated Helper Script to Push Khosamphi Hospital Analytics to GitHub
.DESCRIPTION
    ตรวจจับ Git และนำโค้ดขึ้น GitHub Repository อย่างปลอดภัย
#>

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " Khosamphi Hospital Analytics Dashboard - Git Push Helper " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

# 1. Check if git is available
$gitCmd = Get-Command git -ErrorAction SilentlyContinue

if (-not $gitCmd) {
    # Check common Git installation paths on Windows
    $commonPaths = @(
        "C:\Program Files\Git\cmd\git.exe",
        "C:\Program Files\Git\bin\git.exe",
        "$env:LOCALAPPDATA\Programs\Git\cmd\git.exe",
        "C:\Program Files (x86)\Git\cmd\git.exe"
    )

    foreach ($p in $commonPaths) {
        if (Test-Path $p) {
            $env:Path += ";$([System.IO.Path]::GetDirectoryName($p))"
            $gitCmd = Get-Command git -ErrorAction SilentlyContinue
            if ($gitCmd) {
                Write-Host "[✓] ตรวจพบ Git ที่: $p" -ForegroundColor Green
                break
            }
        }
    }
}

if (-not $gitCmd) {
    Write-Host "`n[!] ไม่พบ Git ในเครื่องของคุณ" -ForegroundColor Yellow
    Write-Host "คุณสามารถดาวน์โหลดและติดตั้ง Git for Windows ได้ที่:" -ForegroundColor White
    Write-Host "https://git-scm.com/download/win" -ForegroundColor Cyan
    Write-Host "หรือใช้โปรแกรม GitHub Desktop: https://desktop.github.com/`n" -ForegroundColor Cyan
    Read-Host "กด Enter เพื่อปิดหน้านี้..."
    exit
}

# 2. Ask for GitHub Repository URL
Write-Host "`nกรุณากรอก GitHub Repository URL ของคุณ" -ForegroundColor Yellow
Write-Host "(ตัวอย่าง: https://github.com/your-username/khosamphi-hospital-analytics.git)" -ForegroundColor DarkGray
$repoUrl = Read-Host "URL"

if ([string]::IsNullOrWhiteSpace($repoUrl)) {
    Write-Host "[X] ไม่ได้ระบุ URL ยกเลิกการทำงาน" -ForegroundColor Red
    exit
}

# 3. Execute Git Commands
Set-Location "d:\dashboard"

Write-Host "`n--> กำลังตรวจสอบและตั้งค่า Git Repository..." -ForegroundColor Green
if (-not (Test-Path ".git")) {
    git init
}

git config core.autocrlf true
git add .
git commit -m "feat: Khosamphi Hospital Analytics Dashboard production release"

git branch -M main

# Set remote origin
git remote remove origin -ErrorAction SilentlyContinue
git remote add origin $repoUrl

Write-Host "`n--> กำลัง Push โค้ดไปยัง GitHub ($repoUrl)..." -ForegroundColor Green
git push -u origin main

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n==========================================================" -ForegroundColor Green
    Write-Host " [✓] Push โค้ดขึ้น GitHub สำเร็จเรียบร้อยแล้ว!" -ForegroundColor Green
    Write-Host " ขั้นตอนต่อไป: ไปที่ https://dashboard.render.com" -ForegroundColor Cyan
    Write-Host " และเลือก Blueprint หรือ Web Service จาก Repository นี้ได้ทันที" -ForegroundColor Cyan
    Write-Host "==========================================================" -ForegroundColor Green
} else {
    Write-Host "`n[X] การ Push โค้ดล้มเหลว กรุณาตรวจสอบสิทธิ์การเข้าถึง GitHub ของคุณ" -ForegroundColor Red
}


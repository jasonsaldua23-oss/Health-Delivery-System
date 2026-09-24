Add-Type -AssemblyName System.Drawing

function Create-CardImage {
    param (
        [string]$sourcePath,
        [string]$destPath,
        [int]$targetWidth = 900,
        [int]$targetHeight = 600,
        [double]$cropXRatio = 0.5, # 0.0 = left, 0.5 = center, 1.0 = right
        [double]$cropYRatio = 0.5, # 0.0 = top, 0.5 = center, 1.0 = bottom
        [double]$zoom = 1.0
    )

    $src = [System.Drawing.Image]::FromFile($sourcePath)
    $srcW = $src.Width
    $srcH = $src.Height

    $targetAspect = $targetWidth / $targetHeight
    $srcAspect = $srcW / $srcH

    if ($srcAspect -gt $targetAspect) {
        # Source is wider than target: fit height, crop width
        $cropH = [int]($srcH / $zoom)
        $cropW = [int]($cropH * $targetAspect)
        if ($cropW -gt $srcW) { $cropW = $srcW; $cropH = [int]($cropW / $targetAspect) }
        $cropX = [int](($srcW - $cropW) * $cropXRatio)
        $cropY = [int](($srcH - $cropH) * $cropYRatio)
    } else {
        # Source is taller than target: fit width, crop height
        $cropW = [int]($srcW / $zoom)
        $cropH = [int]($cropW / $targetAspect)
        if ($cropH -gt $srcH) { $cropH = $srcH; $cropW = [int]($cropH * $targetAspect) }
        $cropX = [int](($srcW - $cropW) * $cropXRatio)
        $cropY = [int](($srcH - $cropH) * $cropYRatio)
    }

    # Bounds check
    if ($cropX -lt 0) { $cropX = 0 }
    if ($cropY -lt 0) { $cropY = 0 }
    if ($cropX + $cropW -gt $srcW) { $cropX = $srcW - $cropW }
    if ($cropY + $cropH -gt $srcH) { $cropY = $srcH - $cropH }

    $destBmp = New-Object System.Drawing.Bitmap($targetWidth, $targetHeight, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
    $g = [System.Drawing.Graphics]::FromImage($destBmp)
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
    $g.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
    $g.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality

    $srcRect = New-Object System.Drawing.Rectangle($cropX, $cropY, $cropW, $cropH)
    $destRect = New-Object System.Drawing.Rectangle(0, 0, $targetWidth, $targetHeight)

    $g.DrawImage($src, $destRect, $srcRect, [System.Drawing.GraphicsUnit]::Pixel)

    $encoder = [System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders() | Where-Object { $_.MimeType -eq 'image/jpeg' }
    $encoderParams = New-Object System.Drawing.Imaging.EncoderParameters(1)
    $encoderParams.Param[0] = New-Object System.Drawing.Imaging.EncoderParameter([System.Drawing.Imaging.Encoder]::Quality, [long]92)

    $destDir = [System.IO.Path]::GetDirectoryName($destPath)
    if (-not (Test-Path $destDir)) { New-Item -ItemType Directory -Path $destDir -Force | Out-Null }

    $destBmp.Save($destPath, $encoder, $encoderParams)

    $g.Dispose()
    $destBmp.Dispose()
    $src.Dispose()

    Write-Output "Created: $destPath ($targetWidth x $targetHeight)"
}

$dirs = @(
    "c:\xampp\htdocs\Health-Delivery-System-Latest\assets\images\stations",
    "c:\xampp\htdocs\Health-Delivery-System-Latest\Admin\assets\images\stations",
    "c:\xampp\htdocs\Health-Delivery-System-Latest\Patients\assets\images\stations",
    "c:\xampp\htdocs\Health-Delivery-System-Latest\Barangay Health Station\assets\images\stations"
)

$srcDir = "C:\Users\LENOVO\Pictures\Health Stations"

# Batch 2 Stations:
# 1. Handumanan: 651x471 (crop slightly up: 0.35 to show building and banners)
# 2. Singcang: 1305x698 (center/left-center: 0.40)
# 3. Mandalagan: 1040x570 (center: 0.50)
# 4. Pahanocoy: 1245x543 (center: 0.50)
# 5. Mansilingan: 2000x900 (center: 0.50, crop top 0.20 to catch the big banner)

foreach ($d in $dirs) {
    Create-CardImage -sourcePath "$srcDir\handumanan-health-center.jpg" -destPath "$d\handumanan.jpg" -cropYRatio 0.35
    Copy-Item "$d\handumanan.jpg" "$d\handumanan-health-center.jpg" -Force

    Create-CardImage -sourcePath "$srcDir\singcang-health-center.png" -destPath "$d\singcang.jpg" -cropXRatio 0.38 -cropYRatio 0.40
    Copy-Item "$d\singcang.jpg" "$d\singcang-health-center.jpg" -Force

    Create-CardImage -sourcePath "$srcDir\mandalagan-health-center.png" -destPath "$d\mandalagan.jpg" -cropXRatio 0.50 -cropYRatio 0.40
    Copy-Item "$d\mandalagan.jpg" "$d\mandalagan-health-center.jpg" -Force

    Create-CardImage -sourcePath "$srcDir\pahanocoy-health-center.png" -destPath "$d\pahanocoy.jpg" -cropXRatio 0.50 -cropYRatio 0.40
    Copy-Item "$d\pahanocoy.jpg" "$d\pahanocoy-health-center.jpg" -Force

    Create-CardImage -sourcePath "$srcDir\mansilingan-health-center.jpg" -destPath "$d\mansilingan.jpg" -cropXRatio 0.50 -cropYRatio 0.15
    Copy-Item "$d\mansilingan.jpg" "$d\mansilingan-health-center.jpg" -Force
}

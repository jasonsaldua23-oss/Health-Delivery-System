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

# Batch 3 Stations:
# 1. Villamonte: 900x450 (crop X: 0.40 to capture the banner 'BARANGAY VILLAMONTE HEALTH CENTER' and crowd)
# 2. Sum-Ag: 1365x706 (crop X: 0.35 to capture the building and driveway)
# 3. Vista Alegre: 1471x705 (crop X: 0.45 to capture the building and 'VISTA ALEGRE' vehicle)
# 4. Taculing: 596x335 (crop X: 0.50 to capture the building facade)
# 5. Villa Esperanza: 872x757 (crop Y: 0.30 to capture the entrance sign and plaque)

foreach ($d in $dirs) {
    Create-CardImage -sourcePath "$srcDir\villamonte-health-center.jpg" -destPath "$d\villamonte.jpg" -cropXRatio 0.40 -cropYRatio 0.50
    Copy-Item "$d\villamonte.jpg" "$d\villamonte-health-center.jpg" -Force

    Create-CardImage -sourcePath "$srcDir\sum-ag-health-center.png" -destPath "$d\sum-ag.jpg" -cropXRatio 0.35 -cropYRatio 0.50
    Copy-Item "$d\sum-ag.jpg" "$d\sum-ag-health-center.jpg" -Force

    Create-CardImage -sourcePath "$srcDir\vista-alegre-health-center.png" -destPath "$d\vista-alegre.jpg" -cropXRatio 0.45 -cropYRatio 0.50
    Copy-Item "$d\vista-alegre.jpg" "$d\vista-alegre-health-center.jpg" -Force

    Create-CardImage -sourcePath "$srcDir\taculing-health-center.jpg" -destPath "$d\taculing.jpg" -cropXRatio 0.50 -cropYRatio 0.50
    Copy-Item "$d\taculing.jpg" "$d\taculing-health-center.jpg" -Force

    Create-CardImage -sourcePath "$srcDir\villa-esperanza-health-center.png" -destPath "$d\villa-esperanza.jpg" -cropXRatio 0.50 -cropYRatio 0.30
    Copy-Item "$d\villa-esperanza.jpg" "$d\villa-esperanza-health-center.jpg" -Force
}

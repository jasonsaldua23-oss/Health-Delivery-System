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
        # Source is taller than target (or portrait): fit width, crop height
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

    # Save as high quality JPG
    $encoder = [System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders() | Where-Object { $_.MimeType -eq 'image/jpeg' }
    $encoderParams = New-Object System.Drawing.Imaging.EncoderParameters(1)
    $encoderParams.Param[0] = New-Object System.Drawing.Imaging.EncoderParameter([System.Drawing.Imaging.Encoder]::Quality, [long]92)

    $destDir = [System.IO.Path]::GetDirectoryName($destPath)
    if (-not (Test-Path $destDir)) { New-Item -ItemType Directory -Path $destDir -Force | Out-Null }

    $destBmp.Save($destPath, $encoder, $encoderParams)

    $g.Dispose()
    $destBmp.Dispose()
    $src.Dispose()

    Write-Output "Created: $destPath ($targetWidth x $targetHeight) from crop [$cropX, $cropY, $cropW, $cropH in $srcW x $srcH]"
}

# Destinations:
$dest1 = "c:\xampp\htdocs\Health-Delivery-System-Latest\assets\images\stations"
$dest2 = "c:\xampp\htdocs\Health-Delivery-System-Latest\Admin\assets\images\stations"

# Source directory:
$srcDir = "C:\Users\LENOVO\Pictures\Health Stations"

# Let's test crop ratios for the 5 stations:
# 1. Alijis: 516x387 (center crop, or slightly up to show the sign "BARANGAY ALIJIS HEALTH CENTER")
Create-CardImage -sourcePath "$srcDir\alijis-health-center.jpg" -destPath "$dest1\alijis.jpg" -cropYRatio 0.35
Create-CardImage -sourcePath "$srcDir\alijis-health-center.jpg" -destPath "$dest2\alijis.jpg" -cropYRatio 0.35

# 2. Bata: 1170x556 (Bata Health Center is centered, slightly left-of-center for the entrance and sign)
Create-CardImage -sourcePath "$srcDir\bata-health-center.png" -destPath "$dest1\bata.jpg" -cropXRatio 0.40 -cropYRatio 0.30
Create-CardImage -sourcePath "$srcDir\bata-health-center.png" -destPath "$dest2\bata.jpg" -cropXRatio 0.40 -cropYRatio 0.30

# 3. Cabug: 1085x452 (Cabug building is centered-left)
Create-CardImage -sourcePath "$srcDir\cabug-health-center.png" -destPath "$dest1\cabug.jpg" -cropXRatio 0.45 -cropYRatio 0.40
Create-CardImage -sourcePath "$srcDir\cabug-health-center.png" -destPath "$dest2\cabug.jpg" -cropXRatio 0.45 -cropYRatio 0.40

# 4. Estefania: 1536x2048 (PORTRAIT! Sign is in the middle: Y ratio ~0.35 to 0.40 shows the sign and entrance canopy)
Create-CardImage -sourcePath "$srcDir\estefania-health-center.jpg" -destPath "$dest1\estefania.jpg" -cropYRatio 0.35
Create-CardImage -sourcePath "$srcDir\estefania-health-center.jpg" -destPath "$dest2\estefania.jpg" -cropYRatio 0.35

# 5. Granada: 1472x742 (Sign "BARANGAY GRANADA DAY CARE CENTER" is on left-center of building)
Create-CardImage -sourcePath "$srcDir\granada-health-center.png" -destPath "$dest1\granada.jpg" -cropXRatio 0.35 -cropYRatio 0.40
Create-CardImage -sourcePath "$srcDir\granada-health-center.png" -destPath "$dest2\granada.jpg" -cropXRatio 0.35 -cropYRatio 0.40


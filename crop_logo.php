<?php
// Script to crop the ISO logos from the right side of kop_surat_atas.jpeg

$sourcePath = 'public/images/kop_surat_atas.jpeg';
$destPath = 'public/images/iso_logos.jpeg';

if (!file_exists($sourcePath)) {
    die("Source file not found.");
}

$info = getimagesize($sourcePath);
$width = $info[0];
$height = $info[1];
$type = $info[2];

// Based on visual estimation, the ISO logos occupy roughly the right 20-25% of the header.
// Let's take the right 25% to be safe.
$cropWidth = $width * 0.25;
$cropHeight = $height; // Full height of the header strip
$x = $width - $cropWidth;
$y = 0;

// Load image
switch ($type) {
    case IMAGETYPE_JPEG:
        $im = imagecreatefromjpeg($sourcePath);
        break;
    case IMAGETYPE_PNG:
        $im = imagecreatefrompng($sourcePath);
        break;
    default:
        die("Unsupported image type.");
}

// Crop
$crop = imagecrop($im, ['x' => $x, 'y' => $y, 'width' => $cropWidth, 'height' => $cropHeight]);

if ($crop !== FALSE) {
    imagejpeg($crop, $destPath, 90);
    imagedestroy($crop);
    echo "Cropped image saved to $destPath\n";
} else {
    echo "Crop failed.\n";
}

imagedestroy($im);

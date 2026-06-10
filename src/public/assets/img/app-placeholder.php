<?php
// Generates a simple placeholder icon for missing app logos
header('Content-Type: image/png');
$img = imagecreatetruecolor(96, 96);
$bg  = imagecolorallocate($img, 26, 26, 26);
$fg  = imagecolorallocate($img, 0, 255, 191);
imagefilledrectangle($img, 0, 0, 96, 96, $bg);
imagestring($img, 5, 32, 38, '?', $fg);
imagepng($img);
imagedestroy($img);

<?php

declare(strict_types=1);

function makeIcon(int $size, string $path): void
{
    $img = imagecreatetruecolor($size, $size);
    $navy  = imagecolorallocate($img, 26, 31, 54);
    $white = imagecolorallocate($img, 255, 255, 255);
    $green = imagecolorallocate($img, 45, 106, 79);

    imagefilledrectangle($img, 0, 0, $size, $size, $navy);
    imagefilledellipse($img, (int) ($size * 0.75), (int) ($size * 0.25), (int) ($size * 0.35), (int) ($size * 0.35), $green);

    $text = 'OT';
    $font = 5;
    $tw = imagefontwidth($font) * strlen($text);
    $th = imagefontheight($font);
    imagestring($img, $font, (int) (($size - $tw) / 2), (int) (($size - $th) / 2), $text, $white);

    imagepng($img, $path);
    imagedestroy($img);
}

$dir = dirname(__DIR__) . '/public/icons';
makeIcon(192, $dir . '/icon-192.png');
makeIcon(512, $dir . '/icon-512.png');

echo "Icons created.\n";

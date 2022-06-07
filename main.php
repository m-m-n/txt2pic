<?php

require_once __DIR__ . "/TextToPicture.php";
require_once __DIR__ . "/Font.php";

$font = new Font();

$txt2pic = new TextToPicture($argv[1] ?? null, 1080, null, $font);
$txt2pic->setPadding(20);
$txt2pic->setHideContent(true);

imagepng($txt2pic->getPicture(), __DIR__ . "/output/output.png");
imagejpeg($txt2pic->getPictureCache(), __DIR__ . "/output/output.jpg");

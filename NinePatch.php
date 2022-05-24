<?php

class NinePatch
{
    private GdImage $gdImage;

    /**
     * コンストラクター
     * $dirname配下の画像で$width x $heightの画像を生成する
     * 以下の仕様で画像を読み込む
     * 左上 $dirname/1.png
     * 中上 $dirname/2.png
     * 右上 $dirname/3.png
     * 左中 $dirname/4.png
     * 中中 $dirname/5.png
     * 右中 $dirname/6.png
     * 左下 $dirname/7.png
     * 中下 $dirname/8.png
     * 右下 $dirname/9.png
     *
     * @param string $dirname
     * @param int $width
     * @param int $height
     */
    public function __construct(string $dirname, int $width, int $height)
    {
        // 四隅はリサイズしない
        $ninePatchLeftTop = imagecreatefrompng("9patch/1.png");
        $ninePatchRightTop = imagecreatefrompng("9patch/3.png");
        $ninePatchLeftBottom = imagecreatefrompng("9patch/7.png");
        $ninePatchRightBottom = imagecreatefrompng("9patch/9.png");

        $originalWidth = imagesx($ninePatchLeftTop);
        $originalHeight = imagesy($ninePatchLeftTop);

        // 横に伸ばす
        // 伸ばす長さ
        $middleWidth = $width - (imagesx($ninePatchLeftTop) + imagesx($ninePatchRightTop));
        $ninePatchMiddleTop = imagescale(imagecreatefrompng("9patch/2.png"), $middleWidth, $originalHeight);
        $ninePatchMiddleBottom = imagescale(imagecreatefrompng("9patch/8.png"), $middleWidth, $originalHeight);

        // 縦に伸ばす
        // 伸ばす長さ
        $middleHeight = $height - (imagesy($ninePatchLeftTop) + imagesy($ninePatchLeftBottom));
        $ninePatchLeftMiddle = imagescale(imagecreatefrompng("9patch/4.png"), $originalWidth, $middleHeight);
        $ninePatchRightMiddle = imagescale(imagecreatefrompng("9patch/6.png"), $originalWidth, $middleHeight);

        // 縦横に伸ばす
        $ninePatchMiddleMiddle = imagescale(imagecreatefrompng("9patch/5.png"), $middleWidth, $middleHeight);

        $this->gdImage = imagecreatetruecolor($width, $height);

        $pictureLines = [
            [$ninePatchLeftTop, $ninePatchMiddleTop, $ninePatchRightTop],
            [$ninePatchLeftMiddle, $ninePatchMiddleMiddle, $ninePatchRightMiddle],
            [$ninePatchLeftBottom, $ninePatchMiddleBottom, $ninePatchRightBottom],
        ];
        $y = 0;
        foreach ($pictureLines as $rowNumber => $pictureItems) {
            $x = 0;
            $y += (isset($pictureLines[$rowNumber - 1][0]) ? imagesy($pictureLines[$rowNumber - 1][0]) : 0);
            foreach ($pictureItems as $index => $picture) {
                $x += (isset($pictureItems[$index - 1]) ? imagesx($pictureItems[$index - 1]) : 0);
                $w = imagesx($picture);
                $h = imagesy($picture);
                imagecopy($this->gdImage, $picture, $x, $y, 0, 0, $w, $h);
            }
        }

        /*
        // 左上
        $x = 0;
        $y = 0;
        $w = imagesx($ninePatchLeftTop);
        $h = imagesy($ninePatchLeftTop);
        imagecopy($this->gdImage, $ninePatchLeftTop, $x, $y, 0, 0, $w, $h);
        // 中上
        $x += imagesx($ninePatchLeftTop);
        $w = imagesx($ninePatchMiddleTop);
        $h = imagesy($ninePatchMiddleTop);
        imagecopy($this->gdImage, $ninePatchMiddleTop, $x, $y, 0, 0, $w, $h);
        // 右上
        $x += imagesx($ninePatchMiddleTop);
        $w = imagesx($ninePatchRightTop);
        $h = imagesy($ninePatchRightTop);
        imagecopy($this->gdImage, $ninePatchRightTop, $x, $y, 0, 0, $w, $h);

        // 左中
        $x = 0;
        $y += imagesy($ninePatchLeftTop);
        $w = imagesx($ninePatchLeftMiddle);
        $h = imagesy($ninePatchLeftMiddle);
        imagecopy($this->gdImage, $ninePatchLeftMiddle, $x, $y, 0, 0, $w, $h);
        // 中央
        $x += imagesx($ninePatchLeftMiddle);
        $w = imagesx($ninePatchMiddleMiddle);
        $h = imagesy($ninePatchMiddleMiddle);
        imagecopy($this->gdImage, $ninePatchMiddleMiddle, $x, $y, 0, 0, $w, $h);
        // 右中
        $x += imagesx($ninePatchMiddleMiddle);
        $w = imagesx($ninePatchRightMiddle);
        $h = imagesy($ninePatchRightMiddle);
        imagecopy($this->gdImage, $ninePatchRightMiddle, $x, $y, 0, 0, $w, $h);

        // 左下
        $x = 0;
        $y += imagesy($ninePatchLeftMiddle);
        $w = imagesx($ninePatchLeftBottom);
        $h = imagesy($ninePatchLeftBottom);
        imagecopy($this->gdImage, $ninePatchLeftBottom, $x, $y, 0, 0, $w, $h);
        // 中下
        $x += imagesx($ninePatchLeftBottom);
        $w = imagesx($ninePatchMiddleBottom);
        $h = imagesy($ninePatchMiddleBottom);
        imagecopy($this->gdImage, $ninePatchMiddleBottom, $x, $y, 0, 0, $w, $h);
        // 右下
        $x += imagesx($ninePatchMiddleBottom);
        $w = iamgex($ninePatchRightBottom);
        $h = iamgey($ninePatchRightBottom);
        imagecopy($this->gdImage, $ninePatchRightBottom, $x, $y, 0, 0, $w, $h);
        */
    }

    /**
     * 9パッチ適用画像を取得する
     *
     * @return GdImage
     */
    public function get(): GdImage
    {
        return $this->gdImage;
    }
}

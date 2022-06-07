<?php

require_once __DIR__ . "/NinePatch.php";
require_once __DIR__ . "/Font.php";
require_once __DIR__ . "/WriteData.php";

/**
 * 文字列を画像で表示する
 */
class TextToPicture
{
    const DEFAULT_PADDING = 48;
    const DEFAULT_WIDTH = 1280;

    const LETTER_SPACING = 3;

    // ぼかし(モザイク)のピクセルサイズ
    const PIXELATE_SIZE = 6;

    private string $text;
    private bool $isHideContent;
    private Font $font;

    private int $width;
    private ?int $height;

    private int $paddingLeft;
    private int $paddingRight;
    private int $paddingTop;
    private int $paddingBottom;

    private GdImage $imgCache;

    /**
     * コンストラクター 画像にする文字列を与える
     *
     * @param string $text
     * @param int $width
     * @param Font|null $font
     */
    public function __construct(string $text, ?int $width = null, ?int $height = null, ?Font $font = null)
    {
        $this->text = $text;
        $this->isHideContent = false;
        $this->font = ($font ?? new Font());

        $this->width = ($width ?? self::DEFAULT_WIDTH);
        $this->height = $height;

        $this->paddingLeft = self::DEFAULT_PADDING;
        $this->paddingRight = self::DEFAULT_PADDING;
        $this->paddingTop = self::DEFAULT_PADDING;
        $this->paddingBottom = self::DEFAULT_PADDING;
    }

    /**
     * 画像の端から文字列を表示するまでの余白を設定する
     *
     * @param int $padding
     */
    public function setPadding(int $padding)
    {
        $this->setEachPadding($padding, $padding, $padding, $padding);
    }

    /**
     * 内容を隠す
     *
     * @param bool $isHideContent
     */
    public function setHideContent(bool $isHideContent)
    {
        $this->isHideContent = $isHideContent;
    }

    /**
     * 内容を隠すかどうか
     *
     * @return bool
     */
    public function isHideContent(): bool
    {
        return $this->isHideContent;
    }

    /**
     * 画像のそれぞれの端から文字列を表示するまでの余白を設定する
     *
     * @param int $paddingLeft
     * @param int $paddingTop
     * @param int $paddingRight
     * @param int $paddingBottom
     */
    public function setEachPadding(int $paddingLeft, int $paddingTop, int $paddingRight, int $paddingBottom)
    {
        $this->paddingLeft = $paddingLeft;
        $this->paddingRight = $paddingRight;
        $this->paddingTop = $paddingTop;
        $this->paddingBottom = $paddingBottom;
    }

    /**
     * テキスト描画領域の幅
     */
    private function calcTextAreaWidth(): int
    {
        return $this->width - ($this->paddingLeft + $this->paddingRight);
    }

    /**
     * テキスト描画した画像を取得する
     *
     * @return GdImage|null
     */
    public function getPicture(): ?GdImage
    {
        // 仮の画像サイズ
        $tmpHeight = 512;
        // 実際の画像サイズ
        $actualHeight = 0;

        $textCanvas = imagecreatetruecolor($this->calcTextAreaWidth(), $tmpHeight);
        $fontColor = imagecolorallocate($textCanvas, $this->font->getColorRed(), $this->font->getColorGreen(), $this->font->getColorBlue());
        // 透過画像にテキストを描画する
        $backgroundColor = imagecolorallocatealpha($textCanvas, 0, 0, 0, 127);
        imagealphablending($textCanvas, true);
        imagesavealpha($textCanvas, true);
        imagefill($textCanvas, 0, 0, $backgroundColor);

        // 最初の行の高さ
        $firstLineHeight = 0;

        // 1行ずつ処理していく
        foreach (explode("\n", trim($this->text)) as $line) {
            if (trim($line) === "") {
                // 空文字は1行分送る
                $actualHeight += ($this->font->getSize() * 1.5);
                $textCanvas = $this->write($textCanvas, [], $actualHeight);
            }

            $writeWidth = 0;
            $writeData = [];
            $maxCharHeight = 0;
            foreach (preg_split("//u", trim($line), -1, PREG_SPLIT_NO_EMPTY) as $char) {
                // 絵文字かチェックする /[\xF0-\xF7][\x80-\xBF][\x80-\xBF][\x80-\xBF]/
                $isEmoji = (preg_match("/[\xF0-\xF7][\x80-\xBF][\x80-\xBF][\x80-\xBF]/", $char) !== 0);
                if ($isEmoji) {
                    // GDで絵文字を処理出来なさそうなので飛ばす
                    continue;
                }
                // 描画範囲を求める
                $box = imagettfbbox($this->font->getSize(), 0, $this->font->getPath(), $char);
                // 長さを取得する
                $charWidth = abs($box[2] - $box[0]);
                // 高さを取得する
                $charHeight = abs($box[7] - $box[1]);
                $maxCharHeight = max($maxCharHeight, $charHeight);
                // 幅を超えるかチェックする
                if ($writeWidth + $charWidth > imagesx($textCanvas)) {
                    // 書き込む
                    $actualHeight += ($maxCharHeight + ($this->font->getSize() / 2));
                    $textCanvas = $this->write($textCanvas, $writeData, $actualHeight);
                    if ($firstLineHeight === 0) {
                        $firstLineHeight = $maxCharHeight + ($this->font->getSize() / 2);
                    }
                    // クリア
                    $writeWidth = 0;
                    $writeData = [];
                    $maxCharHeight = 0;
                }
                $writeWidth += ($charWidth + self::LETTER_SPACING);
                $writeData[] = new WriteData($char, $this->font->getPath(), $this->font->getSize(), $fontColor, $charWidth, $charHeight);
            }
            if ($writeWidth > 0) {
                // 書き込む
                $actualHeight += ($maxCharHeight + ($this->font->getSize() / 2));
                $textCanvas = $this->write($textCanvas, $writeData, $actualHeight);
                if ($firstLineHeight === 0) {
                    $firstLineHeight = $maxCharHeight + ($this->font->getSize() / 2);
                }
            }
        }

        $canvas = (new NinePatch(__DIR__ . "/9patch", $this->width, $actualHeight + $this->paddingTop + $this->paddingBottom))->get();

        imagecopy($canvas, $textCanvas, $this->paddingLeft, $this->paddingTop, 0, 0, imagesx($textCanvas), $actualHeight);

        if ($this->isHideContent()) {
            $pixelateCanvas = imagecreate($this->width - ($this->paddingLeft + $this->paddingRight), $actualHeight - $firstLineHeight);
            imagecopy($pixelateCanvas, $canvas, 0, 0, $this->paddingLeft, $this->paddingTop + $firstLineHeight, imagesx($pixelateCanvas), imagesy($pixelateCanvas));
            // 加工の処理
            imagefilter($pixelateCanvas, IMG_FILTER_PIXELATE, self::PIXELATE_SIZE);
            imagecopy($canvas, $pixelateCanvas, $this->paddingLeft, $this->paddingTop + $firstLineHeight, 0, 0, imagesx($pixelateCanvas), imagesy($pixelateCanvas));
            imagedestroy($pixelateCanvas);
        }

        $this->imgCache = $canvas;

        return $canvas;
    }

    /**
     * テキストを書き込む
     * @param GdImage $textCanvas
     * @param WriteData[] $writeData
     * @param int $y
     *
     * @return GdImage
     */
    private function write(GdImage $textCanvas, array $writeData, int $y): GdImage
    {
        // 高さが不足するので引き伸ばす
        if (imagesy($textCanvas) < $y) {
            $newHeight = imagesy($textCanvas) * 2;
            $newCanvas = imagecreatetruecolor($this->calcTextAreaWidth(), $newHeight);
            $backgroundColor = imagecolorallocatealpha($newCanvas, 0, 0, 0, 127);
            imagealphablending($newCanvas, true);
            imagesavealpha($newCanvas, true);
            imagefill($newCanvas, 0, 0, $backgroundColor);

            // 新しい画像に元の画像の内容をコピーする
            imagecopy($newCanvas, $textCanvas, 0, 0, 0, 0, $this->calcTextAreaWidth(), imagesy($textCanvas));
            imagedestroy($textCanvas);

            $textCanvas = $newCanvas;
        }

        $x = 0;
        foreach ($writeData as $data) {
            imagettftext($textCanvas, $data->getFontSize(), 0, $x, $y - ($data->getFontSize() / 2), $data->getFontColor(), $data->getFontPath(), $data->getCharacter());
            $x += ($data->getWidth() + self::LETTER_SPACING);
        }

        return $textCanvas;
    }

    /**
     * 画像キャッシュを取得する
     *
     * @return GdImage|null
     */
    public function getPictureCache(): ?GdImage
    {
        return $this->imgCache;
    }
}

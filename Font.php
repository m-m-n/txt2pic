<?php

class Font
{
    const DEFAULT_FONT_SIZE = 16;
    const DEFAULT_FONT_PATH = "/usr/share/fonts/opentype/noto/NotoSansCJK-Medium.ttc";
    const DEFAULT_EMOJI_FONT_PATH = "/usr/share/fonts/truetype/noto/NotoColorEmoji.ttf";

    private string $fontPath;
    private string $emojiFontPath;
    private int $fontSize;
    private int $colorRed;
    private int $colorGreen;
    private int $colorBlue;

    /**
     * コンストラクター
     *
     * @param string|null $fontPath
     * @param int|null $fontSize
     * @param int|null $colorRed
     * @param int|null $colorGreen
     * @param int|null $colorBlue
     */
    public function __construct(?string $fontPath = null, ?int $fontSize = null, ?int $colorRed = null, ?int $colorGreen = null, ?int $colorBlue = null)
    {
        $this->fontPath = ($fontPath ?? self::DEFAULT_FONT_PATH);
        $this->emojiFontPath = self::DEFAULT_EMOJI_FONT_PATH;
        $this->fontSize = ($fontSize ?? self::DEFAULT_FONT_SIZE);
        $this->colorRed = ($colorRed ?? 0);
        $this->colorGreen = ($colorGreen ?? 0);
        $this->colorBlue = ($colorBlue ?? 0);
    }

    /**
     * フォントの赤色を取得する
     *
     * @return int
     */
    public function getColorRed(): int
    {
        return $this->colorRed;
    }

    /**
     * フォントの緑色を取得する
     *
     * @return int
     */
    public function getColorGreen(): int
    {
        return $this->colorGreen;
    }

    /**
     * フォントの青色を取得する
     *
     * @return int
     */
    public function getColorBlue(): int
    {
        return $this->colorBlue;
    }

    /**
     * フォントサイズを取得する
     *
     * @return int
     */
    public function getSize(): int
    {
        return $this->fontSize;
    }

    /**
     * フォントパスを取得する
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->fontPath;
    }

    /**
     * 絵文字フォントパスを取得する
     *
     * @return string
     */
    public function getEmojiPath(): string
    {
        return $this->emojiFontPath;
    }
}

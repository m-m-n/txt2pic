<?php

class WriteData
{
    private string $character;
    private string $fontPath;
    private int $fontSize;
    private int $fontColor;
    private int $width;
    private int $height;

    public function __construct(string $character, string $fontPath, int $fontSize, int $fontColor, int $width, int $height)
    {
        $this->character = $character;
        $this->fontPath = $fontPath;
        $this->fontSize = $fontSize;
        $this->fontColor = $fontColor;
        $this->width = $width;
        $this->height = $height;
    }

    public function getCharacter(): string
    {
        return $this->character;
    }

    public function getFontPath(): string
    {
        return $this->fontPath;
    }

    public function getFontSize(): int
    {
        return $this->fontSize;
    }

    public function getFontColor(): int
    {
        return $this->fontColor;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }
}

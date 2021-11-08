<?php

namespace App;

use FilesystemIterator;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use SplFileInfo;

class CollageMaker
{

    protected static $ITEM_WIDTH = 362;
    protected static $ITEM_HEIGHT = 544;
    protected static $ROWS = 2;
    protected static $COLUMNS = 5;
    protected static $ITEM_MARGIN_BETWEEN = 10;
    protected static $ASSETS_DIR = '../assets';

    protected ImageManager $manager;
    protected Image $canvas;
    protected FilesystemIterator $filesystemIterator;
    protected array $assets = [];

    public function __construct()
    {
        $this->manager = new ImageManager();
    }

    public function render(string $format)
    {
        $this->loadAssets();
        $this->createCollage();
        return $this->canvas->encode($format);
    }

    protected function createCollage()
    {
        $this->canvas = $this->manager->canvas(
            static::$ITEM_WIDTH * static::$COLUMNS + static::$ITEM_MARGIN_BETWEEN * (static::$COLUMNS - 1),
            static::$ITEM_HEIGHT * static::$ROWS + static::$ITEM_MARGIN_BETWEEN * (static::$ROWS - 1)
        );

        $index = 0;
        foreach ($this->assets as $file) {
            $this->draw($file, $index++);
        }
    }

    protected function loadAssets()
    {
        $this->filesystemIterator = new FilesystemIterator(static::$ASSETS_DIR,
            FilesystemIterator::SKIP_DOTS
        );

        foreach ($this->filesystemIterator as $file) {
            if (!str_contains(mime_content_type($file->getPathname()), 'image')) continue;
            $this->assets[$file->getFilename()] = $file;
        }

        natsort($this->assets);
    }

    protected function draw(SplFileInfo $file, int $position)
    {
        $this->canvas->insert($file, 'top-left', $this->xOffset($position), $this->yOffset($position));
    }

    protected function xOffset(int $position)
    {
        return (static::$ITEM_WIDTH + static::$ITEM_MARGIN_BETWEEN) * ($position % static::$COLUMNS);
    }

    protected function yOffset(int $position)
    {
        return (static::$ITEM_HEIGHT + static::$ITEM_MARGIN_BETWEEN) * (int)($position / static::$COLUMNS);
    }
}
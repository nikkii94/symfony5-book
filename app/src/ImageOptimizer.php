<?php


namespace App;


use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Psr\Log\LoggerInterface;

class ImageOptimizer
{
    private const MAX_WIDTH = 200;
    private const MAX_HEIGHT = 200;

    private ?Imagine $imagine;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->imagine = new Imagine();
        $this->logger = $logger;
    }

    public function resize(string $filename) : void {
        try {
            [$iWidth, $iHeight] = getimagesize($filename);

            $ratio = $iWidth / $iHeight;
            $width = self::MAX_WIDTH;
            $height = self::MAX_HEIGHT;

            if ( $width / $height > $ratio ) {
                $width = $height * $ratio;
            } else {
                $height = $width * $ratio;
            }

            $photo = $this->imagine->open($filename);
            $photo->resize(new Box($width, $height))->save($filename);
        } catch(\Exception $exception){
            $this->logger->debug('Error during resizing image', [
                'filename' => $filename,
                'error' => $exception->getMessage()
            ]);
        }

    }
}

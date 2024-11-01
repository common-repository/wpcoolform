<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Helper class that converts the given image in constructor to the
 * image type given in image().
 * 
 */
class WcfImageConverter {

    var $inpath;

    public function __construct($infile) {
        $this->inpath = $infile;
    }

    function image($zipImage) {
        $ic = new WcfImageConverter($zipImage);
        if ($ic->isBmp()) {
            return $this->bmp();
        }
        if ($ic->isJpg()) {
            return $this->jpg();
        }
        if ($ic->isPng()) {
            return $this->png();
        }
        if ($ic->isGif()) {
            return $this->gif();
        }
        if ($ic->isBmp()) {
            return $this->bmp();
        }
        if ($ic->isXbm()) {
            return $this->xbm();
        }
    }

    private function getImage() {
        if ($this->isGif()) {
            $image = imagecreatefromgif($this->inpath);
        } else if ($this->isJpg()) {
            $image = imagecreatefromjpeg($this->inpath);
        } else if ($this->isPng()) {
            $image = imagecreatefrompng($this->inpath);
        } else if ($this->isBmp()) {
            $image = imagecreatefromwbmp($this->inpath);
        } else if ($this->isXbm()) {
            $image = imagecreatefromxbm($this->inpath);
        } else if ($this->isXpm()) {
            $image = imagecreatefromxpm($this->inpath);
        }
        return $image;
    }

    private function jpg() {
        if ($this->isJpg()) {
            return $this->inpath;
        }
        $image = $this->getImage();
        $out = $this->outJpg();
        imagejpeg($image, $out);
        imagedestroy($image);
        return $out;
    }

    private function png() {
        if ($this->isPng()) {
            return $this->inpath;
        }
        $image = $this->getImage();
        $out = $this->outPng();
        imagepng($image, $out);
        imagedestroy($image);
        return $out;
    }

    private function gif() {
        if ($this->isGif()) {
            return $this->inpath;
        }
        $image = $this->getImage();
        $out = $this->outGif();
        imagegif($image, $out);
        imagedestroy($image);
        return $out;
    }

    private function bmp() {
        if ($this->isBmp()) {
            return $this->inpath;
        }
        $image = $this->getImage();
        $out = $this->outBmp();
        imagewbmp($image, $out);
        imagedestroy($image);
        return $out;
    }

    private function xbm() {
        if ($this->isXbm()) {
            return $this->inpath;
        }
        $image = $this->getImage();
        $out = $this->outXbm();
        imagexbm($image, $out);
        imagedestroy($image);
        return $out;
    }

    function isJpg() {
        return endsWith($this->inpath, ".jpg") || endsWith($this->inpath, ".JPG") || endsWith($this->inpath, ".jpeg") || endsWith($this->inpath, ".JPEG");
    }

    function isPng() {
        return endsWith($this->inpath, ".png") || endsWith($this->inpath, ".PNG");
    }

    function isBmp() {
        return endsWith($this->inpath, ".bmp") || endsWith($this->inpath, ".BMP");
    }

    function isGif() {
        return endsWith($this->inpath, ".gif") || endsWith($this->inpath, ".gif");
    }

    function isXbm() {
        return endsWith($this->inpath, ".xbm") || endsWith($this->inpath, ".XBM");
    }

    function isXpm() {
        return endsWith($this->inpath, ".xpm") || endsWith($this->inpath, ".XPM");
    }

    private function outJpg() {
        return $this->inpath . ".jpg";
    }

    private function outPng() {
        return $this->inpath . ".png";
    }

    private function outGif() {
        return $this->inpath . ".gif";
    }

    private function outBmp() {
        return $this->inpath . ".bmp";
    }

    private function outXbm() {
        return $this->inpath . ".xbm";
    }

    function getImageType() {
        $path = $this->inpath;
        if (empty($path)) {
            return "false";
        }
        if (endsWith($path, ".jpg") || endsWith($path, ".JPG") || endsWith($path, ".jpeg") || endsWith($path, ".JPEG")) {
            return "jpg";
        }
        if (endsWith($path, ".png") || endsWith($path, ".PNG")) {
            return "png";
        }
        return "false";
    }

}

<?php

$dis = new WcfCaptchaDisplayHelper();
$dis->show();

class WcfCaptchaDisplayHelper {

    var $secretKey = "oihasd087asd67fta9sdf97tftg232lg345zhas9fa76sdf865SDFQJW354RHL2154";

    function show() {
        $captcha = $_GET['captcha'];
        $nr = $this->decrypt($captcha);
        $this->display($nr . ".png");
    }

    function create_image($crytic) {
        $word = $this->decrypt($crytic);
        global $image;
        $image = imagecreatetruecolor(200, 50) or "false";
        if (image === 'false') {
            $this->getImageFromRep($word);
            return;
        }
        $background_color = imagecolorallocate($image, 255, 255, 255);
        $text_color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
        $line_color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
        $pixel_color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
        imagefilledrectangle($image, 0, 0, 200, 50, $background_color);
        for ($i = 0; $i < 3; $i++) {
            imageline($image, 0, rand() % 50, 200, rand() % 50, $line_color);
        }
        for ($i = 0; $i < 1000; $i++) {
            imagesetpixel($image, rand() % 200, rand() % 50, $pixel_color);
        }
        $text_color = imagecolorallocate($image, 0, 0, 0);
        $chars = str_split($word);
        $i = 0;
        foreach ($chars as $char) {
            imagestring($image, 7, 5 + ($i * 30), 20, $char, $text_color);
            $i++;
        }
        $theName = $this->createNewId() . ".png";
        imagepng($image, $theName);
        $this->display($theName);
        imagedestroy($im);
    }

    function getImageFromRep($word) {
        $img = "img/" . $word . ".png";
        $this->display($img);
    }

    function display($imgPath) {
        $im = imagecreatefrompng($imgPath);
        header('Content-Type: image/png');
        imagepng($im);
    }

    private function decrypt($string) {
        $key = $this->secretKey;
        $result = '';
        $string = base64_decode($string);
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $result.=$char;
        }
        return $result;
    }

    /**
     * creates a new random id for a new form.
     * 
     * @return type
     */
    private function createNewId() {
        $ctim = currentTimeInMillis();
        return $ctim . rand(10, 99);
    }

  
}

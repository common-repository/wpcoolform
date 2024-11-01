<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class WcfCaptchaHandler {

    
     var $secretKey ="oihasd087asd67fta9sdf97tftg232lg345zhas9fa76sdf865SDFQJW354RHL2154";
     
    /**
     * gibt html codes zurueck.
     * 
     * 
     * @return type
     */
    function createCaptcha() {
        list($session, $captcha) = $this->createOnlyCaptcha();
        $imgCode = $this->getCode($captcha, $session);
        $inputField = $this->createInputField($session);
        return array($imgCode, $inputField);
    }

    /**
     * gibt nur die session und den captcha zurueck:
     * 
     * wie laeuft das?
     * 
     * wir haben ein tripel:
     * 1. session
     * 2. captcha
     * 3. wert
     */
    function createOnlyCaptcha() {
        $num = rand(10, 99);
        $session = $this->createNewId();
        $captchaResult = $this->getValue($num);
        // Bild oder Text.
        $captcha = $this->getCaptcha($num);
        $sql = new WCFSQLHandler();
        $sql->saveSession($session, $captchaResult);
        return array($session, $captcha);
    }

    function createInputField($session) {
        return "<input type='text' id='wcf_captcha_input' name='wcf_captcha' required>";
    }

    function validate($session, $result) {
        $sql = new WCFSQLHandler();
        if (empty($session)) {
            return false;
        }
        if (empty($result)) {
            return false;
        }
        $ref = $sql->getSessionsValue($session);
        $ret = endsWith($result, $ref);
        $sql->invalidateSession($session);
        return $ret;
    }

    function lastSessionOk($session) {
        if (empty($session)) {
            return false;
        }
        $sql = new WCFSQLHandler();
        $rows = $sql->getLastSession($session);
        try {
            return $rows[0]['last_ok'] === 'true';
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }

    function getCaptcha($num) {
        $enci = $this->encrypt($num);
        return "<img src='".  PATH_CAPTCHAS ."wcf_captcha.php?captcha=".$enci."'>";
    }

    function getCode($captcha, $session) {
        return "<div id='wcf_captcha_message'>".tr('label_captcha')."</div><p><div id='wcf_captcha'>" . $captcha . "</div><input type='hidden' id='wcf_session' name='wcf_session' value='" . $session . "'>";
    }

    function getValue($num) {
        return $num;
    }

    function encrypt($string) {
        $key = $this->secretKey;
        $result = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) + ord($keychar));
            $result.=$char;
        }

        return base64_encode($result);
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

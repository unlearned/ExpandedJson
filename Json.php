<?php
/**
 * ExpandedJson.php
 *
 * PHP version 5.2
 *
 * @category  Utility
 * @package   ExpandedJson
 * @author    Takashi Uesugi <takashi_uesugi@ecnavi.co.jp>
 * @copyright 2011 Takashi Uesugi
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://
 */
require_once dirname(__FILE__) . '/JsonDecode.php';
require_once dirname(__FILE__) . '/JsonEncode.php';
/**
 * ExpandedJson 
 * This is an expanded Json modul.
 *
 * @category  Utility
 * @package   ExpandedJson
 * @author    Takashi Uesugi <takashi_uesugi@ecnavi.co.jp>
 * @copyright 2011 Takashi Uesugi
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://genesix.co.jp
 */
class ExpandedJson
{

    private function __construct ()
    {
    }

    static public function decode ($string)
    {
        $json = new JsonDecode($string);
        return $json->make();
    }

    static public function encode ($array)
    {
        $json = new JsonEncode($array);
        return $json->make();
    }
}





<?php
/**
 * JsonEncode.php
 *
 * PHP version 5.2
 *
 * @category  Utility
 * @package   ExpandedJson
 * @author    Takashi Uesugi <takashi_uesugi@ecnavi.co.jp>
 * @copyright Takashi Uesugi
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://
 */
/**
 * JsonEncode
 * This is an expanded Json modul.
 * Fixing for some probrems. 
 * This JSON library fix about numeric and encoding
 *
 * @category  Utility
 * @package   ExpandedJson
 * @author    Takashi Uesugi <takashi_uesugi@ecnavi.co.jp>
 * @copyright Takashi Uesugi
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://genesix.co.jp
 */
class JsonEncode
{
    private $_array;

    public function __construct ($array)
    {
        $this->_array = $array;
    }



    public function make ($value = null)
    {
        if (is_null($value)) {
            $value = $this->_array;
        }

        $encoded_string = "";

        if (is_array($value)) {

            if (is_int(key($value)) || is_null(key($value))) {
                $encoded_string = $this->_arrayToJson($value);
            } else {
                $encoded_string = $this->_objectToJson($value);
            }

        } elseif (is_object($value)) {

            $encoded_string = $this->_objectToJson($value);

        } elseif (!is_string($value) && is_numeric($value)) {

            $encoded_string = $this->_numericToJson($value);

        } elseif (is_string($value)) {

            $encoded_string = $this->_stringToJson($value);

        } elseif (is_bool($value)) {

            $encoded_string = $this->_booleanToJson($value);

        } elseif (is_null($value)) {
            $encoded_string = $this->_nullToJson($value);
        }

        return $encoded_string;
    }



    private function _arrayToJson ($array)
    {
        $last = sizeof($array) - 1;

        $json_string = '';

        $i = 0;
        foreach ($array as $value) {
            $json_string .= $this->make($value);
            if ($last !== $i) {
                $json_string .= ', ';
            }
            ++$i;
        }

        $json_string = '['. $json_string . ']';
        return $json_string;
    }



    private function _objectToJson ($array)
    {
        $last = sizeof($array) - 1;

        $json_string = "";

        $i = 0;
        foreach ($array as $key=>$value) {
            $json_string .= '"' . $key . '":' . $this->make($value);

            if ($last !== $i) {
                $json_string .= ', ';
            }
            ++$i;
        }

        $json_string = '{'. $json_string . '}';
        return $json_string;
    }



    private function _stringToJson ($string)
    {
        $string = $this->unicodeDecode($string);
        return '"' . $string . '"';
    }



    private function _numericToJson ($numeric)
    {
        $num = '0';

        if (is_int($numeric)) {
            $num = sprintf('%d', $numeric);
        } elseif (preg_match('/e/ui', strval($numeric))) {
            $num = strval($numeric);
        } else {
            $num = sprintf('%f', $numeric);
        }

        return $num;
    }



    private function _booleanToJson ($bool)
    {
        if ($bool) {
            return 'true';
        }

        return 'false';
    }



    private function _nullToJson ($null)
    {
        return 'null';
    }



    public function unicodeDecode($str)
    {
        return preg_replace_callback(
            //"/((?:[^\x09\x0A\x0D\x20-\x7E]{3})+)/", 
            //"/((?:[^\x09\x0A\x0D\x20-\x7E]{3})|(?:[\x22\x26\x27\x3C\x3e])+)/", 
            //"/((?:[^\x09\x0A\x0D\x20-\x7E]{3}|[\x22\x26\x27\x3C\x3e])+)/", 
            '/(?:[^\x09\x0A\x0D\x20-\x7E]{3}|[\x22\x26\x27\x3C\x3e])+/', 
            array('self', '_decodeCallback'), 
            $str
        );
    }



    private function _decodeCallback($matches)
    {
        $chars = mb_convert_encoding($matches[0], 'UTF-16', 'UTF-8');

        $escaped = "";

        $length = strlen($chars);
        for ($i = 0; $i < $length; $i += 2) {
            $escaped .=  '\u';
            $escaped .= sprintf('%02x%02x', ord($chars[$i]), ord($chars[$i+1]));
        }
        return $escaped;
    }



    public function unicodeEncode($str)
    {
        return preg_replace_callback(
            '/\\\\u([0-9a-zA-Z]{4})/', 
            array('self', '_encodeCallback'), 
            $str
        );
    }



    private function _encodeCallback($matches)
    {
        $char = mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UTF-16');
        return $char;
    }



    private function _arrayUnicodeDecode($array)
    {
        $output = array();

        foreach ($array as $key=>$value) {
            if (is_array($value)) {
                $output[$key] = $this->_arrayUnicodeDecode($value);
                continue;
            }
            $output[$key] = $this->unicodeDecode($value);
        }
        return $output;
    }
}


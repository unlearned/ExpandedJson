<?php
/**
 * JsonDecode.php
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
 * JsonDecode
 * This is an expanded Json modul.
 * Fixing for some probrems. exsamples for Json comment out,
 * using final ','.
 *
 * @category  Utility
 * @package   ExpandedJson
 * @author    Takashi Uesugi <takashi_uesugi@ecnavi.co.jp>
 * @copyright Takashi Uesugi
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://genesix.co.jp
 */
class JsonDecode
{
    const ENCODE = 'UTF-8';
    private $_pre;
    private $_po;
    private $_str;


    public function __construct ($string)
    {
        $this->_str = $string;
        $this->_po = 0;
    }



    public function make()
    {
        return $this->_parse();
    }



    private function _getCurrentChar ()
    {
        return $this->_getChar($this->_po);
    }



    private function _getPreChar ()
    {
        return $this->_getChar($this->_pre);
    }



    private function _getChar ($point)
    {
        $char = mb_substr($this->_str, $point, 1, self::ENCODE);
        return strval($char);
    }


    private function _parse ()
    {
        $this->_pre = $this->_po;

        $output = null;

        while (true) {
            $char = $this->_getCurrentChar();

            if (preg_match('/[\s]/u', $char)) {
                ++$this->_po;
                continue;
            } elseif ('/' === $char) {
                $this->_makeComment();
                continue;
            } elseif ('[' === $char) {
                $output = $this->_makeArray();
                break;
            } elseif ('{' === $char) {
                $output = $this->_makeObject();
                break;
            }

            throw new Exception("test", 1);

        };

        return $output;
    }



    private function _isValue ()
    {
        $rules = array(
            '0-9\-', // numeric
            '\'\"',  // string
            'n',     // null
            't',     // true
            'f',     // false
            '{',     // object
            '\[',    // array
        );

        $reg_exp = '/\A[';
        $reg_exp_tail = ']\z/u';

        foreach ($rules as $rule) {
            $reg_exp .= $rule;
        }
        $reg_exp .= $reg_exp_tail;


        $pre_char = $this->_getPreChar();
        $char = $this->_getCurrentChar();

        $check_pre = preg_match('/[{\[,:]/u', $pre_char);
        $check = preg_match($reg_exp, $char);

        return ($check_pre && $check) ? true : false;
    }



    private function _isString ()
    {
        $pre_char = $this->_getPreChar();
        $char = $this->_getCurrentChar();

        $check_pre = preg_match('/[{\[,:]/u', $pre_char);
        $check = preg_match('/\A[\'\"]\z/u', $char);

        return ($check_pre && $check) ? true : false;
    }



    private function _makeValue ()
    {
        $char = $this->_getCurrentChar();

        if (preg_match('/[0-9\-]/u', $char)) {
            return $this->_makeNumeric();
        } elseif (preg_match('/[\'\"]/u', $char)) {
            return $this->_makeString();
        } elseif ('n' === $char) {
            return $this->_makeNull();
        } elseif ('t' === $char) {
            return $this->_makeTrue();
        } elseif ('f' === $char) {
            return $this->_makeFalse();
        } elseif ('[' === $char) {
            return $this->_makeArray();
        } elseif ('{' === $char) {
            return $this->_makeObject();
        }

        throw new Exception("test", 1);
    }



    private function _makeObject ()
    {
        $this->_pre = $this->_po;
        ++$this->_po;

        $object = array();
        $key = null;
        while (true) {
            $pre_char = $this->_getPreChar();
            $char = $this->_getCurrentChar();

            if (preg_match('/[\s]/u', $char)) {
                ++$this->_po;
                continue;
            } elseif ('/' === $char) {
                $this->_makeComment();
                continue;
            } elseif (':' === $char) {
                $this->_pre = $this->_po;
                ++$this->_po;
                continue;
            } elseif ($this->_isString() && is_null($key)) {
                $key = $this->_makeString();
                continue;
            } elseif (':' === $pre_char && $this->_isValue() && isset($key)) {
                $object[$key] = $this->_makeValue();
                $key = null;
                continue;
            } elseif (',' === $char && preg_match('/[0-9\"\'\]\}le]/u', $pre_char)) {
                $this->_pre = $this->_po;
                ++$this->_po;
                continue;
            } elseif ($char === '}') {
                break;
            }

            throw new Exception("test", 1);
        }

        $this->_pre = $this->_po;
        ++$this->_po;

        return $object;
    }






    private function _makeArray ()
    {
        $this->_pre = $this->_po;
        ++$this->_po;

        $str = $this->_str;

        $array = array();
        while (true) {
            $pre_char = $this->_getPreChar();
            $char = $this->_getCurrentChar();

            if ($this->_isValue()) {
                $array[] = $this->_makeValue();
                continue;
            } elseif (preg_match('/[\s]/u', $char)) {
                ++$this->_po;
                continue;
            } elseif ('/' === $char) {
                $this->_makeComment();
                continue;
            } elseif (',' === $char && preg_match('/[0-9\"\'\]\}le]/u', $pre_char)) {
                $this->_pre = $this->_po;
                ++$this->_po;
                continue;
            } elseif ($char === ']') {
                break;
            }

            throw new Exception("test", 1);
        }

        $this->_pre = $this->_po;
        ++$this->_po;

        return $array;
    }



    private function _makeTrue ()
    {
        return $this->_makeLiteral('true', true);
    }



    private function _makeFalse ()
    {
        return $this->_makeLiteral('false', false);
    }



    private function _makeNull ()
    {
        return $this->_makeLiteral('null', null);
    }



    private function _makeLiteral ($match_string, $return)
    {

        $str = '';
        while (true) {
            $pre = $this->_po;
            ++$this->_po;

            $pre_char = $this->_getChar($pre);
            $char = $this->_getCurrentChar();

            $str .= $pre_char;

            if (preg_match('/[\,\]\}\s]/u', $char)) {
                break;
            }
        }

        if ($match_string === $str) {
            $this->_pre = $pre;
            return $return;
        }

        throw new Exception("test", 1);
    }



    private function _makeNumeric ()
    {
        $str_num = '';
        while (true) {
            $pre = $this->_po;
            ++$this->_po;

            $pre_char = $this->_getChar($pre);
            $char = $this->_getCurrentChar();

            $str_num .= $pre_char;


            if (preg_match('/[1-9]/u', $char)) {
                continue;
            } elseif ('0' === $char && !preg_match('/\A(-0|0)\z/u', $str_num)) {
                continue;
            } elseif ('.' ===  $char && !preg_match('/\./u', $str_num)) {
                continue;
            } elseif ('+' === $char && preg_match('/e/ui', $pre_char)) {
                continue;
            } elseif ('-' === $char) {
                $check_str = ($str_num === '') ? true : false;
                $chech_pre = preg_match('/e/ui', $pre_char) ? true : false;
                if ($check_str || $check_pre) {
                    continue;
                }
            } elseif (preg_match('/e/ui', $char)) {
                $check_pre = preg_match('/[0-9]/u', $pre_char) ? true : false;
                $check_str = preg_match('/e/ui', $str_num) ? false : true;
                if ($check_pre && $check_str) {
                    continue;
                }
            } elseif (preg_match('/[\,\]\}\s]/u', $char)) {
                if (preg_match('/[0-9]/u', $pre_char)) {
                    break;
                }
            }

            throw new Exception("test", 1);
        }

        $this->_pre = $pre;
        return floatval($str_num);
    }



    private function _makeString ()
    {
        $quote = $this->_getCurrentChar();
        $string = '';
        while (true) {
            $pre = $this->_po;
            ++$this->_po;

            $pre_char = $this->_getChar($pre);
            $char = $this->_getCurrentChar();

            if ($char === $quote && '\\' !== $pre_char) {
                break;
            } 

            $string .= $char;
        }

        $this->_pre = $this->_po;
        ++$this->_po;
        return $string;
    }



    private function _makeComment ()
    {
        $pre = $this->_po;
        ++$this->_po;

        $pre_fix =  $this->_getChar($pre);
        $pre_fix .= $this->_getCurrentChar();

        if ('//' === $pre_fix) {
            $this->_makeCommentOneLine();
        } elseif ('/*' === $pre_fix) {
            $this->_makeCommentBlock();
        } else {
            throw new Exception("test", 1);
        }
        return; 
    }



    private function _makeCommentOneLine ()
    {
        while (true) {
            ++$this->_po;
            $char = $this->_getCurrentChar();

            if ("\n" === $char) {
                ++$this->_po;
                break;
            }
        } 
        return null;
    }



    private function _makeCommentBlock ()
    {
        $str_comment = '';

        while (true) {
            $pre = $this->_po;
            ++$this->_po;

            $pre_char = $this->_getChar($pre);
            $char = $this->_getCurrentChar();

            $str_comment .= $pre_char;

            $check_str = ('/*/' !== $str_comment . $char) ? true : false;

            if ('/*/' !== $str_comment . $char) {
                $check_pre = ('*' === $pre_char) ? true : false;
                $check_char = ('/' === $char) ? true : false;

                if ($check_pre && $check_char) {
                    $pre = $this->_po;
                    ++$this->_po;
                    break;
                }

                continue;
            }

            throw new Exception("test", 1);
        }

        return null;
    }
}


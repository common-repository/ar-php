<?php
/**
 * ----------------------------------------------------------------------
 *  
 * Copyright (c) 2006-2010 Khaled Al-Shamaa.
 *  
 * http://www.ar-php.org
 *  
 * PHP Version 5 
 *  
 * ----------------------------------------------------------------------
 *  
 * LICENSE
 *
 * This program is open source product; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public License (LGPL)
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 *  
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *  
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/lgpl.txt>.
 *  
 * ----------------------------------------------------------------------
 *  
 * Class Name: Compress string using Huffman-like coding
 *  
 * Filename:   ArCompressStr.class.php
 *  
 * Original    Author(s): Khaled Al-Sham'aa <khaled.alshamaa@gmail.com>
 *  
 * Purpose:    This class will compress given string in binary format
 *             using variable-length code table (derived in a particular way 
 *             based on the estimated probability of occurrence for each 
 *             possible value of the source symbol) for encoding a source symbol
 *              
 * ----------------------------------------------------------------------
 *  
 * Arabic Compress String Class
 *
 * Compress string using Huffman-like coding
 *
 * This class compresses text strings into roughly 70% of their original size 
 * by benefit from using compact coding for most frequented letters in a given 
 * language. This algorithm associated with text language, so you will find 6 
 * different classes for the following languages: Arabic, English, French, 
 * German, Italian and Spanish language.
 * 
 * Benefits of this compress algorithm include:
 * 
 * - It is written in pure PHP code, so there is no need to any 
 *   PHP extensions to use it.
 * - You can search in compressed string directly without any need uncompress 
 *   text before search in.
 * - You can get original string length directly without need to uncompress 
 *   compressed text.
 * 
 * Note:
 * Unfortunately text compressed using this algorithm lose the structure that 
 * normal zip algorithm used, so benefits from using ZLib functions on this 
 * text will be reduced.
 * 
 * There is another drawback, this algorithm working only on text from a given 
 * language, it does not working fine on binary files like images or PDF.
 * 
 * Example:
 * <code>
 * include('./Arabic.php');
 * $obj = new Arabic('ArCompressStr');
 * 
 * $obj->setInputCharset('windows-1256');
 * $obj->setOutputCharset('windows-1256');
 * 
 * $file = 'Compress/ar_example.txt';
 * $fh   = fopen($file, 'r');
 * $str  = fread($fh, filesize($file));
 * fclose($fh);
 * 
 * $zip = $obj->compress($str);
 * 
 * $before = strlen($str);
 * $after  = strlen($zip);
 * $rate   = round($after * 100 / $before);
 * 
 * echo "String size before was: $before Byte<br>";
 * echo "Compressed string size after is: $after Byte<br>";
 * echo "Rate $rate %<hr>";
 * 
 * $str = $obj->decompress($zip);
 * 
 * if ($obj->search($zip, $word)) {
 *     echo "Search for $word in zipped string and find it<hr>";
 * } else {
 *     echo "Search for $word in zipped string and do not find it<hr>";
 * }
 * 
 * $len = $obj->length($zip);
 * echo "Original length of zipped string is $len Byte<hr>";
 * 
 * echo '<div dir="rtl" align="justify">'.nl2br($str).'</div>';   
 * </code>
 *                
 * @category  I18N 
 * @package   Arabic
 * @author    Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
 * @copyright 2006-2010 Khaled Al-Shamaa
 *    
 * @license   LGPL <http://www.gnu.org/licenses/lgpl.txt>
 * @link      http://www.ar-php.org 
 */

// New in PHP V5.3: Namespaces
// namespace I18N/Arabic/ArCompressStr;

/**
 * This PHP class compress Arabic string using Huffman-like coding
 *  
 * @category  I18N 
 * @package   Arabic
 * @author    Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
 * @copyright 2006-2010 Khaled Al-Shamaa
 *    
 * @license   LGPL <http://www.gnu.org/licenses/lgpl.txt>
 * @link      http://www.ar-php.org 
 */ 
class ArCompressStr
{
    protected static $encode = ' �������';
    protected static $binary = '0000|0001|0010|0011|0100|0101|0110|0111|';
    
    protected static $hex = '0123456789abcdef';
    protected static $bin = '0000|0001|0010|0011|0100|0101|0110|0111|1000|1001|1010|1011|1100|1101|1110|1111|';

    /**
     * "compress" method input charset
     * @var String     
     */         
    public $compressInput = 'windows-1256';

    /**
     * Name of the textual "compress" method parameters 
     * @var Array     
     */         
    public $compressVars = array('str');

    /**
     * "decompress" method output charset
     * @var String     
     */         
    public $decompressOutput = 'windows-1256';

    /**
     * "search" method input charset
     * @var String     
     */         
    public $searchInput = 'windows-1256';

    /**
     * Name of the textual "search" method parameters 
     * @var Array     
     */         
    public $searchVars = array('word');
    
    /**
     * Loads initialize values
     */         
    public function __construct()
    {
    }

    /**
     * Set required encode and binary hash of most probably character in 
     * selected language
     *      
     * @param string $lang [en, fr, gr, it, sp, ar] Language profile selected
     *      
     * @return object $this to build a fluent interface
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public static function setLang($lang) 
    {
        switch ($lang) {
        case 'en':
            self::$encode = ' etaoins';
            break;
        case 'fr':
            self::$encode = ' enasriu';
            break;
        case 'gr':
            self::$encode = ' enristu';
            break;
        case 'it':
            self::$encode = ' eiaorln';
            break;
        case 'sp':
            self::$encode = ' eaosrin';
            break;
        default:
            self::$encode = ' �������';
        }

        self::$binary = '0000|0001|0010|0011|0100|0101|0110|0111|';
        
        return $this;
    }
    
    /**
     * Compress the given string using the Huffman-like coding
     *      
     * @param string $str The text to compress
     *                    
     * @return binary The compressed string in binary format
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public static function compress($str) 
    {
        $bits = self::str2bits($str);
        $hex  = self::bits2hex($bits);
        $bin  = pack('h*', $hex);

        return $bin;
    }

    /**
     * Uncompress a compressed string
     *       
     * @param binary $bin The text compressed by compress(). 
     *                    
     * @return string The original uncompressed string
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public static function decompress($bin) 
    {
        $temp  = unpack('h*', $bin);
        $bytes = $temp[1];

        $bits = self::hex2bits($bytes);
        $str  = self::bits2str($bits);

        return $str;
    }

    /**
     * Search a compressed string for a given word
     *      
     * @param binary $bin  Compressed binary string
     * @param string $word The string you looking for
     *                    
     * @return boolean True if found and False if not found
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public static function search($bin, $word) 
    {
        $wBits = self::str2bits($word);

        $temp  = unpack('h*', $bin);
        $bytes = $temp[1];
        $bits  = self::hex2bits($bytes);

        if (strpos($bits, $wBits)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieve the origenal string length
     *      
     * @param binary $bin Compressed binary string
     *      
     * @return integer Origenal string length
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public static function length($bin) 
    {
        $temp  = unpack('h*', $bin);
        $bytes = $temp[1];
        $bits  = self::hex2bits($bytes);

        $count = 0;
        $i     = 0;

        while (isset($bits[$i])) {
            $count++;
            if ($bits[$i] == 1) {
                $i += 9;
            } else {
                $i += 4;
            }
        }

        return $count;
    }

    /**
     * Convert textual string into binary string
     *      
     * @param string $str The textual string to convert
     *       
     * @return binary The binary representation of textual string
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    protected static function str2bits($str) 
    {
        $bits  = '';
        $total = strlen($str);

        $i = -1;
        while (++$i < $total) {
            $char = $str[$i];
            $pos  = strpos(self::$encode, $char);

            if ($pos !== false) {
                $bits .= substr(self::$binary, $pos*5, 4);
            } else {
                $int   = ord($char);
                $bits .= '1'.substr(self::$bin, (int)($int/16)*5, 4);
                $bits .= substr(self::$bin, ($int%16)*5, 4);
            }
        }

        // Complete nibbel
        $add   = strlen($bits) % 4;
        $bits .= str_repeat('0', $add);

        return $bits;
    }

    /**
     * Convert binary string into textual string
     *      
     * @param binary $bits The binary string to convert
     *       
     * @return string The textual representation of binary string
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    protected static function bits2str($bits) 
    {
        $str = '';
        while ($bits) {
            $flag = substr($bits, 0, 1);
            $bits = substr($bits, 1);

            if ($flag == 1) {
                $byte = substr($bits, 0, 8);
                $bits = substr($bits, 8);

                if ($bits || strlen($code) == 8) {
                    $int  = base_convert($byte, 2, 10);
                    $char = chr($int);
                    $str .= $char;
                }
            } else {
                $code = substr($bits, 0, 3);
                $bits = substr($bits, 3);

                if ($bits || strlen($code) == 3) {
                    $pos  = strpos(self::$binary, "0$code|");
                    $str .= substr(self::$encode, $pos/5, 1);
                }
            }
        }

        return $str;
    }

    /**
     * Convert binary string into hexadecimal string
     *      
     * @param binary $bits The binary string to convert
     *       
     * @return hexadecimal The hexadecimal representation of binary string  
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    protected static function bits2hex($bits) 
    {
        $hex   = '';
        $total = strlen($bits) / 4;

        for ($i = 0; $i < $total; $i++) {
            $nibbel = substr($bits, $i*4, 4);

            $pos  = strpos(self::$bin, $nibbel);
            $hex .= substr(self::$hex, $pos/5, 1);
        }

        return $hex;
    }

    /**
     * Convert hexadecimal string into binary string
     *      
     * @param hexadecimal $hex The hexadezimal string to convert
     *       
     * @return binary The binary representation of hexadecimal string
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    protected static function hex2bits($hex) 
    {
        $bits  = '';
        $total = strlen($hex);

        for ($i = 0; $i < $total; $i++) {
            $pos   = strpos(self::$hex, $hex[$i]);
            $bits .= substr(self::$bin, $pos*5, 4);
        }

        return $bits;
    }
}


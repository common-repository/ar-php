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
 * Class Name: Identify Arabic Text Segments
 *  
 * Filename:   ArIdentifier.class.php
 *  
 * Original    Author(s): Khaled Al-Sham'aa <khaled.alshamaa@gmail.com>
 *  
 * Purpose:    This class will identify Arabic text in a given UTF-8 multi
 *             language document, it will return array of start and end
 *             positions for Arabic text segments.
 *              
 * ----------------------------------------------------------------------
 *  
 * Identify Arabic Text Segments
 *
 * Using this PHP Class you can fully automated approach to processing
 * Arabic text by quickly and accurately determining Arabic text segments within
 * multiple languages documents.
 * 
 * Understanding the language and encoding of a given document is an essential step 
 * in working with unstructured multilingual text. Without this basic knowledge, 
 * applications such as information retrieval and text mining cannot accurately 
 * process data and important information may be completely missed or mis-routed.
 * 
 * Any application that works with Arabic in multiple languages documents can 
 * benefit from the ArIdentifier class. Using this class, applications can take a 
 * fully automated approach to processing Arabic text by quickly and accurately 
 * determining Arabic text segments within multiple languages document.      
 *
 * Example:
 * <code>
 *     include('./Arabic.php');
 *     $obj = new Arabic('ArIdentifier');
 *     
 *     $hStr=$obj->highlightText($str, '#80B020');
 * 
 *     echo $str . '<hr />' . $hStr . '<hr />';
 *     
 *     $taggedText = $obj->tagText($str);
 * 
 *     foreach($taggedText as $wordTag) {
 *         list($word, $tag) = $wordTag;
 *     
 *         if ($tag == 1) {
 *             echo "$word is Noun, ";
 *         }
 *     
 *         if ($tag == 0) {
 *             echo "$word is not Noun, ";
 *         }
 *     }    
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
// namespace I18N/Arabic/ArIdentifier;

/**
 * This PHP class identify Arabic text segments
 *  
 * @category  I18N 
 * @package   Arabic
 * @author    Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
 * @copyright 2006-2010 Khaled Al-Shamaa
 *    
 * @license   LGPL <http://www.gnu.org/licenses/lgpl.txt>
 * @link      http://www.ar-php.org 
 */ 
class ArIdentifier
{
    /**
     * "isArabic" method input charset
     * @var String     
     */         
    public $isArabicInput = 'utf-8';

    /**
     * Name of the textual "isArabic" method parameters 
     * @var Array     
     */         
    public $isArabicVars = array('str');

    /**
     * Loads initialize values
     */         
    public function __construct()
    {
    }

    /**
     * Identify Arabic text in a given UTF-8 multi language string
     *          
     * @param string $str UTF-8 multi language string
     *      
     * @return array Offset of the beginning and end of each Arabic segment in
     *               sequence in the given UTF-8 multi language string
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public static function identify($str)
    {
        $minAr  = 55436;
        $maxAr  = 55698;
        $probAr = false;
        $arFlag = false;
        $arRef  = array();
        $max    = strlen($str);
        
        $i = -1;
        while (++$i < $max) {
            $cDec = ord($str[$i]);
            
            if (!$probAr && ($cDec == 216 || $cDec == 217)) {
                $probAr = true;
                continue;
            }
            
            if ($i > 0) {
                $pDec = ord($str[$i - 1]);
            } else {
                $pDec = null;
            }
            
            if ($probAr) {
                $utfDecCode = ($pDec << 8) + $cDec;

                if ($utfDecCode >= $minAr && $utfDecCode <= $maxAr) {
                    if (!$arFlag) {
                        $arFlag  = true;
                        $arRef[] = $i - 1;
                    }
                } else {
                    if ($arFlag) {
                        $arFlag  = false;
                        $arRef[] = $i - 1;
                    }
                }
                
                $probAr = false;
                continue;
            }
            
            if ($arFlag && !preg_match("/^\s$/", $str[$i])) {
                $arFlag  = false;
                $arRef[] = $i;
            }
        }
        
        return $arRef;
    }

    /**
     * Find out if given string is Arabic text or not
     *          
     * @param string $str String
     *                    
     * @return boolean True if given string is UTF-8 Arabic, else will return False
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public static function isArabic($str)
    {
        $isArabic = false;
        $arr      = self::identify($str);
        
        if (count($arr) == 1 && $arr[0] == 0) {
            $isArabic = true;
        }
        
        return $isArabic;
    }
}

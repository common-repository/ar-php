<?php
// ----------------------------------------------------------------------
// Copyright (C) 2008 by Khaled Al-Shamaa.
// http://www.ar-php.org
// ----------------------------------------------------------------------
// LICENSE

// This program is open source product; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Filename: rpa.php
// Original  Author(s): Khaled Al-Sham'aa <khaled.alshamaa@gmail.com>
// Purpose:  Response TinyMCE plugin AJAX requests
// ----------------------------------------------------------------------
require_once('Arabic.php');

$action = $_GET['action'];
if (isset($_GET['param'])) {
  $param = $_GET['param'];
}

switch($action){
  case 'number':
    $ar = new Arabic('ArNumbers');

    $ar->ArNumbers->setFeminine(2);
    $ar->ArNumbers->setFormat(2);
    
    echo $param . ' ' .$ar->int2str($param);

    break;

  case 'date':
    $ar = new Arabic('ArDate');
    date_default_timezone_set('UTC');
    $time = time();

    $ar->ArDate->setMode(4);
    echo $ar->date('l jS F Y', $time);

    break;

  case 'hijri':
    $ar = new Arabic('ArDate');
    date_default_timezone_set('UTC');
    $time = time();

    $ar->ArDate->setMode(1);
    echo $ar->date('l jS F Y', $time);

    break;

  case 'keyboard':
    $ar = new Arabic('ArKeySwap');

    $temp_ae = $ar->swap_ae($param);
    $temp_ea = $ar->swap_ea($param);
    
    $sim_ae = similar_text($param, $temp_ae);
    $sim_ea = similar_text($param, $temp_ea);
    
    if ($sim_ea >= $sim_ae) {
        echo $temp_ae;
    } else {
        echo $temp_ea;
    }

    break;

  case 'en_terms':
    if (preg_match("/^[\w\d\s]+$/i", $param)) {
        $ar = new Arabic('ArTransliteration');
        echo $ar->en2ar($param) . " ($param)";
    } else {
        $ar = new Arabic('EnTransliteration');
        echo $ar->ar2en($param) . " ($param)";
    }

    break;
}
?>

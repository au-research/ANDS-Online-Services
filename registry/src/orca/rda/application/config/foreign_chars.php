<?php
/** 
Copyright 2011 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

********************************************************************************
$Date: 2011-09-06 11:35:57 +1000 (Tue, 06 Sep 2011) $
$Revision: 1 $
***************************************************************************
*
**/ 
?>
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| Foreign Characters
| -------------------------------------------------------------------
| This file contains an array of foreign characters for transliteration
| conversion used by the Text helper
|
*/
$foreign_characters = array(
	'/ä|æ|ǽ/' => 'ae',
	'/ö|œ/' => 'oe',
	'/ü/' => 'ue',
	'/Ä/' => 'Ae',
	'/Ü/' => 'Ue',
	'/Ö/' => 'Oe',
	'/À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ/' => 'A',
	'/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/' => 'a',
	'/Ç|Ć|Ĉ|Ċ|Č/' => 'C',
	'/ç|ć|ĉ|ċ|č/' => 'c',
	'/Ð|Ď|Đ/' => 'D',
	'/ð|ď|đ/' => 'd',
	'/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě/' => 'E',
	'/è|é|ê|ë|ē|ĕ|ė|ę|ě/' => 'e',
	'/Ĝ|Ğ|Ġ|Ģ/' => 'G',
	'/ĝ|ğ|ġ|ģ/' => 'g',
	'/Ĥ|Ħ/' => 'H',
	'/ĥ|ħ/' => 'h',
	'/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/' => 'I',
	'/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/' => 'i',
	'/Ĵ/' => 'J',
	'/ĵ/' => 'j',
	'/Ķ/' => 'K',
	'/ķ/' => 'k',
	'/Ĺ|Ļ|Ľ|Ŀ|Ł/' => 'L',
	'/ĺ|ļ|ľ|ŀ|ł/' => 'l',
	'/Ñ|Ń|Ņ|Ň/' => 'N',
	'/ñ|ń|ņ|ň|ŉ/' => 'n',
	'/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/' => 'O',
	'/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º/' => 'o',
	'/Ŕ|Ŗ|Ř/' => 'R',
	'/ŕ|ŗ|ř/' => 'r',
	'/Ś|Ŝ|Ş|Š/' => 'S',
	'/ś|ŝ|ş|š|ſ/' => 's',
	'/Ţ|Ť|Ŧ/' => 'T',
	'/ţ|ť|ŧ/' => 't',
	'/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/' => 'U',
	'/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/' => 'u',
	'/Ý|Ÿ|Ŷ/' => 'Y',
	'/ý|ÿ|ŷ/' => 'y',
	'/Ŵ/' => 'W',
	'/ŵ/' => 'w',
	'/Ź|Ż|Ž/' => 'Z',
	'/ź|ż|ž/' => 'z',
	'/Æ|Ǽ/' => 'AE',
	'/ß/'=> 'ss',
	'/Ĳ/' => 'IJ',
	'/ĳ/' => 'ij',
	'/Œ/' => 'OE',
	'/ƒ/' => 'f'
);

/* End of file foreign_chars.php */
/* Location: ./application/config/foreign_chars.php */
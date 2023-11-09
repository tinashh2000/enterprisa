<?php

/***************************************************************************************
* Copyright (C) 2020 Tinashe Mutandagayi                                               *
*                                                                                      *
* This file is part of the PayrollPro source code. The author(s) of this file     *
* is/are not liable for any damages, loss or loss of information, deaths, sicknesses   *
* or other bad things resulting from use of this file or software, either direct or    *
* indirect.                                                                            *
* Terms and conditions for use and distribution can be found in the license file named *
* LICENSE.TXT. If you distribute this file or continue using it,                       *
* it means you understand and agree with the terms and conditions in the license file. *
* binding this file.                                                                   *
*                                                                                      *
* Happy Coding :)                                                                      *
****************************************************************************************/

namespace Ffw\Crypt;

class CCrypt9 extends CCrypt8 {

}

class CCrypt8 {
	static function scrambleNumber($num) {
		return self::scrambleText($num);
	}

	static function unscrambleNumber($num) {
		return self::unScrambleText($num);
	}

	static function scrambleText($text)
	{
		return strrev(base64_encode($text));						//base64 encode then reverse then to hex again
	}

	static function unScrambleText($text)
	{
		return base64_decode(strrev($text));
	}
}

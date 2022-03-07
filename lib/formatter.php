<?php namespace formatter;

// I was bored so I put in more effort than I needed to
const PHONE_COUNTRY_CODES = [
	"1",
	"20", "27", "2\d{2}",
	"3\d", "35\d", "37\d", "38\d",
	"4\d", "42\d",
	"5\d", "50\d", "59\d",
	"6\d", "6[7-9]\d",
	"7", "7\d",
	"8\d", "80\d", "85\d", "8[78]\d",
	"9\d", "9[679]\d"
];

function format_phone(?string $phone) : string {
	if(!$phone) return "";
	
	if(strlen($phone) == 10) {
		// This is a local number
		$area = substr($phone, 0, 3);
		$prefix = substr($phone, 3, 3);
		$postfix = substr($phone, 6, 4);
		return "($area) $prefix-$postfix";
	} else {
		// Do international formatting
		foreach(PHONE_COUNTRY_CODES as $cc) {
			if(preg_match("/^$cc/", $phone, $matches)) {
				$country = $matches[0];
				$postfix = substr($phone, strlen($country));
				return "+$country $postfix";
			}
		}
	}
}
<?php
/*
 * This class will validate user input prior to entry to a database:
 *  - required: The most simple form of validation, which ensures a value has been provided
 *  - numeric: Check to see if all of the supplied values are numeric
 *  - isArray: Validate all the values of an array, and optionally provide a targeted size
 *  - isUploaded: Check to see if a file was uploaded
*/

class Validate {
	private static $redirect = " Click <a href=\"javascript:window.location = document.location.pathname\">here</a> to retry.";
	
//The most simple form of validation, which ensures a value has been provided
	public static function required($string, $matches = false, $sizeSmall = false, $sizeLarge = false, $sizeEquals = false, $optional = false) {
		try {
		//See if the string is empty
			!$optional && empty($string) && !is_numeric($string) ? $error = "A required value was empty." : true;
			
			if ($optional && empty($string)) {
				return $string;
			}
			
		//Does it match the "$match" string?
			if ($matches && is_string($matches)) {
				$string !== $matches ? $error = "The given value must be equal to: <strong>" . $matches . "</strong>." : true;
			}
			
		//Does it match the "$match" array?
			if ($matches && is_array($matches)) {
				$equalTo = "";
				
				for ($i = 0; $i <= sizeof($matches) - 1; $i++) {
				//Add a friendly "or" to the next-to-last list of suggestions
					$i == sizeof($matches) - 2 ? $equalTo .= $matches[$i] . ", or " : $equalTo .= $matches[$i] . ", ";
				}
				
				!in_array($string, $matches) ? $error = "The given value must be equal to: <strong>" . rtrim($equalTo, ", ") . "</strong>." : true;
			}
			
		//Is it big enough?
			if (is_numeric($sizeSmall) && !is_numeric($sizeLarge) && strlen($string) < $sizeSmall) {
				$error = "A required value was too short. At least <strong>" . $sizeSmall . "</strong> chatacter(s) are required.";
		//Is it small enough?
			} elseif (is_numeric($sizeLarge) && !is_numeric($sizeSmall) && strlen($string) > $sizeLarge) {
				$error = "A required value was too long. Only <strong>" . $sizeLarge . "</strong> chatacter(s) are allowed.";
		//Is it between a given range?
			} elseif (is_numeric($sizeSmall) && is_numeric($sizeLarge) && $sizeSmall < $sizeLarge && (strlen($string) < $sizeSmall || strlen($string) > $sizeLarge)) {
				$error = "A required value was not within the specified range. Between <strong>" . $sizeSmall . "</strong> and <strong>" . $sizeLarge . "</strong> chatacter(s) are required.";
		//Is is equal to a given length?
			} elseif (is_numeric($sizeEquals) && strlen($string) !== $sizeEquals) {
				$error = "A required value was not within the specified range. <strong>" . $sizeEquals . "</strong> chatacter(s) are required.";
			}
			
			if (isset($error)) {
				throw new Exception($error);
			} else {
				return $string;
			}
		} catch (Exception $e) {
			die($e->getMessage() . self::$redirect);
		}
	}
	
//Check to see if all of the supplied values are numeric
	public static function numeric($number, $small = false, $large = false, $equalTo = false, $optional = false) {
		try {
		//See if the string is empty
			!$optional && empty($number) && !is_numeric($number) ? $error = "A required value was empty." : true;
			
			if ($optional && empty($number)) {
				return $number;
			}
			
		//Is it big enough?
			if (is_numeric($small) && !is_numeric($large) && $number < $small) {
				$error = "A required value was too small. The numeric value must be greater than or equal to <strong>" . $small . "</strong>.";
		//Is it small enough?
			} elseif (is_numeric($large) && !is_numeric($small) && $number > $large) {
				$error = "A required value was too large. The numeric value must be less than or equal to <strong>" . $large . "</strong>.";
		//Is it between a given range?
			} elseif (is_numeric($small) && is_numeric($large) && $small < $large && ($number < $small || $number > $large)) {
				$error = "A required value was not within the specified range. The numeric value must be between <strong>" . $small . "</strong> and <strong>" . $large . "</strong>.";
		//Is is equal to a given length?
			} elseif (is_numeric($equalTo) && $number !== $equalTo) {
				$error = "A required value was not within the specified range. The numeric value must be equal to <strong>" . $equalTo . "</strong>.";
			}
			
			if (isset($error)) {
				throw new Exception($error);
			} else {
				return $number;
			}
		} catch (Exception $e) {
			die($e->getMessage() . self::$redirect);
		}
	}
	
//Validate all the values of an array, and optionally provide a targeted size
	public static function isArray($array, $sizeSmall = false, $sizeLarge = false, $sizeEquals = false, $optional = false) {
		try {
		//See if the string is empty
			!$optional && (empty($number) || !is_array($array) || sizeof($array) == 0) ? $error = "A required value was empty." : true;
		} catch (Exception $e) {
			
		}
		
		if (!empty($array) && is_array($array) && count($array) == $size) {
			$return = array();
			
			for($count = 0; $count <= count($array); $count ++) {
				array_push($return, self::required($array[$count]));
			}
			
			return $return;
		} else {
			die("A required value was empty." . self::$redirect);
		}
	}
	
//Check to see if a file was uploaded
	public static function isUploaded($file, $optional = false) {
		if ($optional && empty($file)) {
			return $file;
		}
		
		if (is_uploaded_file($_FILES[$file]['tmp_name']) || !$optional) {
			return $file;
		} else {
			die("A required file was not uploaded." . self::$redirect);
		}
	}
	
//Validate an email address
	public static function isEmail($email, $optional = false) {
		 $pattern = "/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD";
		 
		 if ($optional && empty($email)) {
		 	return $email;
		 }
		 
		 if (self::required($email) && !preg_match("/[\\000-\\037]/", $email) && preg_match($pattern, $email)) {
		 	return $email;
		 } else {
		 	die("An invalid email address was entered." . self::$redirect);
		 }
	}
}
?>
<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://www.banshee-php.org/
	 *
	 * Licensed under The MIT License
	 */

	/* Pre-defined validation strings for valid_input()
	 */
	define("VALIDATE_CAPITALS",     "ABCDEFGHIJKLMNOPQRSTUVWXYZ");
	define("VALIDATE_NONCAPITALS",  "abcdefghijklmnopqrstuvwxyz");
	define("VALIDATE_LETTERS",      VALIDATE_CAPITALS.VALIDATE_NONCAPITALS);
	define("VALIDATE_PHRASE",       VALIDATE_LETTERS." ,.?!:;-'");
	define("VALIDATE_NUMBERS",      "0123456789");
	define("VALIDATE_SYMBOLS",      "!@#$%^&*()_-+={}[]|\:;\"'`~<>,./?");
	define("VALIDATE_URL",          VALIDATE_LETTERS.VALIDATE_NUMBERS."-_/.=");

	define("VALIDATE_NONEMPTY",     0);


	/* Validate input
	 *
	 * INPUT:  string input, string valid characters[, int length]
	 * OUTPUT: boolean input oke
	 * ERROR:  -
	 */
	function valid_input($data, $allowed, $length = null) {
		if (is_array($data) == false) {
			$data_len = strlen($data);

			if ($length !== null) {
				if ($length == VALIDATE_NONEMPTY) {
					if ($data_len == 0) {
						return false;
					}
				} else if ($data_len !== $length) {
					return false;
				}
			} else if ($data_len == 0) {
				return true;
			}

			$data = str_split($data);
			$allowed = str_split($allowed);
			$diff = array_diff($data, $allowed);

			return count($diff) == 0;
		} else foreach ($data as $item) {
			if (valid_input($item, $allowed, $length) == false) {
				return false;
			}
		}

		return true;
	}

	/* Validate an e-mail address
	 *
	 * INPUT:  string e-mail address
	 * OUTPUT: boolean e-mail address oke
	 * ERROR:  -
	 */
	function valid_email($email) {
        return preg_match("/^[0-9A-Za-z]([-_.~]?[0-9A-Za-z])*@[0-9A-Za-z]([-.]?[0-9A-Za-z])*\\.[A-Za-z]{2,4}$/", $email) === 1;
	}

	/* Validate a date string
	 *
	 * INPUT:  string date
	 * OUTPUT: boolean date oke
	 * ERROR:  -
	 */
	function valid_date($date) {
		if ($date == "0000-00-00") {
			return false;
		}

		return preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $date) === 1;
	}

	/* Validate a time string
	 *
	 * INPUT:  string time
	 * OUTPUT: boolean time oke
	 * ERROR:  -
	 */
	function valid_time($time) {
		return preg_match("/^(([01]?[0-9])|(2[0-3])):[0-5][0-9](:[0-5][0-9])?$/", $time) === 1;
	}

	/* Validate a timestamp
	 *
	 * INPUT:  string timestamp
	 * OUTPUT: boolean timestamp oke
	 * ERROR:  -
	 */
	function valid_timestamp($timestamp) {
		list($date, $time) = explode(" ", $timestamp, 2);
		return valid_date($date) && valid_time($time);
	}

	/* Validate a telephone number
	 *
	 * INPUT:  string telephone number
	 * OUTPUT: boolean telephone number oke
	 * ERROR:  -
	 */
	function valid_phonenumber($phonenr) {
		return preg_match("/^\+?(\(?\d+\)?[- ]?)*\d+$/", $phonenr) === 1;
	}

	/* Validate password security
	 *
	 * INPUT:  string password[, object view]
	 * OUTPUT: boolean password secure
	 * ERROR:  -
	 */
	function is_secure_password($password, $view = null) {
		$result = true;

		$pwd_len = strlen($password);

		if ($pwd_len < PASSWORD_MIN_LENGTH) {
			if ($view == null) {
				return false;
			}
			$view->add_message("La contraseña debe tener al menos %d caracteres.", PASSWORD_MIN_LENGTH);
			$result = false;
		} else if ($pwd_len > PASSWORD_MAX_LENGTH) {
			if ($view == null) {
				return false;
			}
			$view->add_message("La contraseña es demasiado larga.");
			$result = false;
		}

		$numbers = 0;
		$letters = 0;
		$symbols = 0;
		for ($i = 0; $i < $pwd_len; $i++) {
			$c = ord(strtolower(substr($password, $i, 1)));

			if (($c >= 48) && ($c <= 57)) {
				$numbers++;
			} else if (($c >= 97) && ($c <= 122)) {
				$letters++;
			} else {
				$symbols++;
			}
		}

		if (($letters == 0) || (($numbers == 0) && ($symbols == 0))) {
			if ($view == null) {
				return false;
			}
			$view->add_message("La contraseña debe contener al menos una letra y un número o carácter especial.");
			$result = false;
		}

		return $result;
	}

	/* Generate random string
	 *
	 * INPUT:  int length
	 * OUTPUT: string random string
	 * ERROR:  -
	 */
	function random_string($length) {
		$characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$max_chars = strlen($characters) - 1;

		$result = "";
		for ($i = 0; $i < $length; $i++) {
			$result .= $characters[mt_rand(0, $max_chars)];
		}

		return $result;
	}

?>

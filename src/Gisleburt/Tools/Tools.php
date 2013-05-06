<?php

	namespace Gisleburt\Tools;

	/**
	 * A collection of useful functions accessed as statics
	 * @author Daniel Mason
	 */

	class Tools {

		/**
		 * Returns the _GET value for the given key or null if not found
		 * @param string|null $key
		 * @return null|\stdClass
		 */
		public static function get($key = null) {
			if(isset($key))
				return isset($_GET[$key]) ? $_GET[$key] : null;
			return self::arrayToObject($_GET);
		}

		/**
		 * Returns the _POST value for the given key or null if not found
		 * @param null $key
		 * @return null|\stdClass
		 */
		public static function post($key = null) {
			if(isset($key))
				return isset($_POST[$key]) ? $_POST[$key] : null;
			return self::arrayToObject($_POST);
		}

		/**
		 * Returns the _GET, _POST or _COOKIE value for the given key or null if not found
		 * @param null $key
		 * @return mixed
		 */
		public static function request($key = null) {
			if(isset($key))
				return isset($_REQUEST[$key])? $_REQUEST[$key] : null;
			return self::arrayToObject($_REQUEST);
		}
		
		/**
		 * Turns an array into an object
		 * @param array $array The array to convert
		 * @param integer $recursionLevel How many arrays deep to go
		 * @return \stdClass
		 */
		public static function arrayToObject(array $array, $recursionLevel = 0) {
			$object = new \stdClass();
			foreach($array as $key => $item) {
				if(is_array($item) && $recursionLevel > 0)
					$item = self::arrayToObject($item, --$recursionLevel);
				$object[$key] = $item;
			}
			return $object;
		}

		/**
		 * Turns an array such as [1, 2, 3, 4] into a keyed array of the form [1 => 2, 3 => 4]
		 * @param array $array
		 * @param bool $safeMode Default false, set to true to prevent accidental overwrite of keyed array
		 * @return array
		 */
		public static function linearArrayToKeyedArray(array $array, $safeMode = false) {

			// If safemode, lets check we aren't already dealing with a keyed array.
			// This is slow so avoid using it.
			if($safeMode)
				foreach($array as $key => $value)
					if(!is_numeric($key))
						return $array;

			$newArray = array();
			$count = count($array);
			for($i = 0; $i < $count; $i+=2) {
				if(isset($array[$i+1]))
					$newArray[$array[$i]] = $array[$i+1];
				else
					$newArray[] = $array[$i];
			}

			return $newArray;

		}

		/**
		 * Produce a random string of ascii characters
		 * @param $length Number of characters in the string
		 * @return string
		 */
		public static function randomAscii($length) {

			$string = '';

			$start = 32; // Space
			$end = 126; // Tilde

			for($i = 0; $i < $length; $i++)
				$string .= chr(mt_rand($start, $end));

			return $string;

		}
		
	}
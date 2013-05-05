<?php

	namespace Gisleburt\Tools;

	/**
	 * Useful debugging functions
	 */
	class Debug
	{

		/**
		 * Print a string inside pre tags
		 * @param $string string
		 */
		public static function echoPre($string) {
			echo "<pre>$string</pre>";
		}

		/**
		 * Like print_r but wrapped in pre tags
		 * @param $mixed mixed
		 */
		public static function print_r($mixed) {
			self::echoPre(print_r($mixed));
		}

	}

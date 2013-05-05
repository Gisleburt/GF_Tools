<?php

	namespace Gisleburt\Tools;

	class Autoloader {

		public static $incDirs;
		
		public static function psr0($className) {

			$class = ltrim($className, '\\');
			$filename  = '';
			$namespace = '';
			if ($lastNsPos = strripos($class, '\\')) {
				$namespace = substr($class, 0, $lastNsPos);
				$class = substr($class, $lastNsPos + 1);
				$filename  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
			}
			$filename .= str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
			
			if(is_readable($filename)) {
				require $filename;
				return;
			}
			
			foreach(self::$incDirs as $dir) {
				$file = $dir.DIRECTORY_SEPARATOR.$filename;
				if(is_readable($file)) {
					require $file;
					return true;
				}
			}

			return false;

		}
		
	}

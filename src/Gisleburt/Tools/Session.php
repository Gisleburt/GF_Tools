<?php

	namespace Gisleburt\Tools;

	class Session {
		
		/**
		 * This is the key to find the object in the session
		 * @var string
		 */
		protected $_namespace;
		
		/**
		 * You can not instantiate this class directly. Use getSession()
		 * @param string $namespace The key into SESSION
		 */
		protected function __construct($namespace) {
			$this->_namespace = $namespace;
		}
		
		/**
		 * Returns the key into SESSION being used.
		 * @return string
		 */
		public function getNamespace() {
			return $this->_namespace;
		}
		
		/**
		 * Delete this object
		 */
		public function clearSession() {
			unset($_SESSION[$this->getNamespace()]);
		}
		
		/**
		 * Checks for the existance of the object in session, creates it if not found
		 * @param string $namespace
		 * @throws LazyData_Exception If another type of object exists in the given namespace 
		 * @return DM_Session (or sub class of) If the object was created inside the session
		 */
		public static function getSession($namespace = null) {
			
			if(!session_id())
				session_start();
			
			$classname = get_called_class();
			
			if(!$namespace)
				$namespace = $classname;
			
			// If it doesn't exist, create it and return it
			if(!isset($_SESSION[$namespace])) {
				$_SESSION[$namespace] = new $classname($namespace);
				return $_SESSION[$namespace];
			}
			
			// If it already exists check it's ok and return it
			if(get_class($_SESSION[$namespace]) == $classname)
				return $_SESSION[$namespace];
			
			// If it's not ok throw an Exception.
			throw new Exception('Object in session does not match requested object.');
		}
		
	}

<?php

	namespace Gisleburt\Tools;

	class Cookie {
		
		//
		// Cookie Defaults
		//
		
		/**
		 * Time to cookie expiration
		 * 
		 * Default: 1 month
		 * @var int
		 */
		protected $_expire = 2592000; // 30 days
		
		/**
		 * Path for cookie
		 * 
		 * Default: '/'
		 * @var string
		 */
		protected $_path = '/';
		
		/**
		 * Domain for cookie
		 * 
		 * Default: null
		 * @var string
		 */
		protected $_domain = null;
		
		/**
		 * Secure Cookie
		 *
		 * Default: null
		 * @var bool
		 */
		protected $_secure = null;
		
		/**
		 * Only available to HTTP requests, (ie. not Javascript)
		 * @var bool
		 */
		protected $_httponly = null;
		
		//protected $counter;
		
		//
		// Utility variables
		//
		
		/**
		 * Used to identify the cookie
		 * @var string
		 */
		protected $_namespace;
		
		public function __construct($namespace = null) {
			if(!$namespace)
				$namespace = get_called_class();
			$this->_namespace = $namespace.'--';
			$this->_expire = $this->_expire + time();
		}
		
		/**
		 * Attempts to retrieve information from the cookie, or if unavailable, from the object
		 * @param string $name
		 */
		public function __get($name) {
			if($name[0] != '_') {
				if(!isset($this->$name) && isset($_COOKIE[$this->_namespace.$name]))
					return $this->$name = ($_COOKIE[$this->_namespace.$name]);
				if(isset($this->$name))
					return $this->$name;
			}
			
			return null;
				
		}
		
		/**
		 * Stores the given information in both the object and a cookie.
		 * @param string $name
		 * @param mixed $value
		 */
		public function __set($name, $value) {
			if($name[0] != '_') {
				setcookie(
						$this->_namespace.$name, 
						($value),
						$this->_expire,
						$this->_path,
						$this->_secure,
						$this->_httponly
					);
				$this->$name = $value;
			}

		}
		
	}
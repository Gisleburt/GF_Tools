<?php

	namespace Gisleburt\Tools;

	/**
	 * Finds the requested controller
	 * @author Daniel Mason
	 */
	class Router {

		const DEFAULT_CONTROLLER = 'Index';
		const DEFAULT_ACTION = 'Index';

		/**
		 * Set during construction, this is where the router will look for controller classes
		 * @var string
		 */
		protected $namespace;

		/**
		 * A break down of the current request
		 * @var string[]
		 */
		protected $requestUri;

		/**
		 * The parameters passed in the uri (excluding controller and action)
		 * @var string[]
		 */
		protected $uriParameters;

		/**
		 * The parameters passed as get parameters in the uri
		 * @var srting[]
		 */
		protected $getParameters;

		/**
		 * Controller object
		 * @var Controller
		 */
		protected $controller;

		/**
		 * The controller to be called
		 * @var string
		 */
		protected $controllerName;

		/**
		 * The action of the controller to call
		 * @var string
		 */
		protected $actionName;

		public function __construct($namespace) {
			$this->namespace = $namespace;
		}
		
		/**
		 * Analyse a request to get the controller, action and any parameters
		 * @param string $uri
		 */
		public function analyseRequest() {

			$request = explode('?', $_SERVER['REQUEST_URI']);

			// Examine the URL part of the URI.
			$this->requestUri = explode('/', $request[0]);
			array_shift($this->requestUri); // First element will be empty as string starts with /

			$rawUriParameters = $this->requestUri;
			// Controller
			$controllerName = $this->getRequestedController();
			$this->setControllerName($controllerName);

			// Action
			$actionName = $this->getRequestedAction();
			$this->setActionName(ucwords($actionName));

			$this->uriParameters = $rawUriParameters;

			return $this;

		}

		/**
		 * Tries to load the requested controller
		 */
		public function loadController($action = null, $controller = null) {

			if($action)
				$this->setActionName($action);

			if($controller)
				$this->setControllerName($controller);

			if($this->controllerName) {
				$this->controller = new $this->controllerName($this->uriParameters);
				$this->controller->callAction($this->actionName);
			}
		}

		/**
		 * Attempts to set the controller name, failing that sets it to the default
		 * @param $controllerName
		 * @return bool
		 */
		public function setControllerName($controllerName) {

			$controllerName = ucfirst($controllerName);
			$controllerName = "\\$this->namespace\\$controllerName";
			if(class_exists($controllerName)) {
				$this->controllerName = $controllerName;
				return true;
			}

			$this->controllerName = "\\$this->namespace\\".self::DEFAULT_CONTROLLER;
			return false;

		}

		/**
		 * The controller will deal with bad actions so no need to worry here
		 * @param $actionName
		 * @return bool
		 */
		public function setActionName($actionName) {
			$this->actionName = $actionName;
			return $this;
		}

		public function redirect($url = null) {
			if(!$url)
				$url = $_SERVER['REQUEST_URI'];
			header("Location: $url");
			exit;
		}

		/**
		 * What controller was being requested
		 * @return string
		 */
		public function getRequestedController() {
			if(array_key_exists(0, $this->requestUri))
				return $this->requestUri[0];
		}

		/**
		 * What action was being requested
		 * @return string
		 */
		public function getRequestedAction() {
			if(array_key_exists(1, $this->requestUri))
				return $this->requestUri[1];
		}
				
	}
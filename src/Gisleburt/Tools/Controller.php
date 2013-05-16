<?php

	namespace Gisleburt\Tools;

	abstract class Controller {

		/**
		 * The name of this controller (used for loading templates)
		 * @var
		 */
		protected $controllerName;

		/**
		 * Parameters 
		 * @var array
		 */
		protected $uriParameters;
		
		/**
		 * Variables to be given to the template
		 * @var \stdClass
		 */
		protected $view;

		/**
		 * The action trying to be called
		 * @var string
		 */
		protected $actionAttempted;

		/**
		 * Which action was actually called
		 * @var string
		 */
		protected $actionCalled;

		
		public function __construct(array $uriParameters = null) {
			$this->view = new \stdClass();
			$this->uriParameters = $uriParameters;
			$controllerNameExploded = explode('\\', get_called_class());
			$this->controllerName = array_pop($controllerNameExploded);
		}
		
		/**
		 * Display the appropriate template file
		 */
		protected function display($action) {

			// Probably not the right way to do this
			global $template;

			// Get the template appropriate template
			$templateFile = $this->getTemplate($action);

			// Assign the view parameters to the template
			$template->assign(get_object_vars($this->view));

			// Display the template
			$template->display($templateFile);

			exit;

		}

		public function callAction($action) {

			$this->actionAttempted = $action;

			if(method_exists($this, $action.'Action'))
				$actionToCall = $action;
			else
				$actionToCall = 'index';

			if(!method_exists($this, $actionToCall.'Action'))
				throw new \Exception("No Index action and could not start action: $action");

			$this->actionCalled = $actionToCall;

			$this->{$actionToCall.'Action'}();

			$this->display($actionToCall);

			return $this;
		}

		/**
		 * Gets the location of the file to be displayed.
		 * Relative or absolute file can be used to override automated functionality
		 * @param $file
		 * @return string
		 */
		protected function getTemplate($file) {
			$file = ucfirst($file);
			$file = "$this->controllerName/$file";
			return $file;
		}

		/**
		 * Look for a parameter based on _REQUEST then URI elements
		 * @param $name
		 * @return mixed
		 */
		protected function getParam($name) {
			return Tools::request($name);
		}

		/**
		 * Retrieves a '_GET' variable
		 * @param string|null $key
		 * @return mixed
		 */
		protected function get($key = null) {
			return Tools::get($key);
		}

		/**
		 * Retrieves a '_POST' variable
		 * @param string|null $key
		 * @return mixed
		 */
		protected function post($key = null) {
			return Tools::post($key);
		}

		/**
		 * Redirect to another location
		 * @param $url
		 */
		protected function redirect($url) {
			\header("location: $url");
		}
		
	}
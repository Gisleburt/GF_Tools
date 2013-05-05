<?php

	namespace Gisleburt\Tools;

	/**
	 * Simple Mailing tool
	 */
	class Mail {

		/**
		 * Recipients addresses
		 * @var string[]
		 */
		protected $to;

		/**
		 * Senders address
		 * @var string
		 */
		protected $from;

		/**
		 * Subject
		 * @var string
		 */
		protected $subject;

		/**
		 * Content
		 * @var string
		 */
		protected $message;

		/**
		 * Where to save failed emails
		 * @var string
		 */
		static protected $saveMailDir;

		/**
		 * If something went wrong, information about it is saved here.
		 * @var \Exception
		 */
		protected $exception;

		/**
		 * Where to save failed emails
		 * @param $dir string
		 * @return bool Successfully set
		 */
		static function setSaveMailDir($dir) {
			if(is_dir($dir)) {
				self::$saveMailDir = $dir;
				return true;
			}
			return false;
		}

		/**
		 * Sets the recipient, clearing any previous ones.
		 * @param $to
		 * @return Mail
		 */
		public function setTo($to) {
			$this->to = array($to);
			return $this;
		}

		/**
		 * Add a recipient
		 * @param $to
		 * @return Mail
		 */
		public function addTo($to) {
			$this->to = (array)$this->to;
			$this->to[] = $to;
			return $this;
		}

		/**
		 * Set the sender
		 * @param $from
		 * @return Mail
		 */
		public function setFrom($from) {
			$this->from = $from;
			return $this;
		}

		/**
		 * Set the subject
		 * @param $subject
		 * @return Mail
		 */
		public function setSubject($subject) {
			$this->subject = $subject;
			return $this;
		}

		/**
		 * Set the message
		 * @param $message
		 * @return Mail
		 */
		public function setMessage($message) {
			$this->message = $message;
			return $this;
		}

		public function send() {
			if($this->to
					&& $this->from
					&& $this->subject
					&& $this->message) {

				$to = implode(', ', $this->to);
				$from = "From: $this->from";
				$subject = $this->subject;
				$message = wordwrap($this->message, 70, "\n\r");

				try {
				 	$sent = @mail($to, $subject, $message, $from); // Supressing errors for the lose. :(
					if(!$sent) {}
						throw new \Exception(error_get_last());
					return true;
				}
				catch(\Exception $e) {
					$this->exception = $e;
					$this->save();
				}
			}

			return false;
		}

		/**
		 * Try to save the mail to the given save folder
		 */
		public function save() {
			$filename = microtime(true).'.json';
			$json = json_encode(get_object_vars($this), JSON_PRETTY_PRINT);
			return file_put_contents(self::$saveMailDir.'/'.$filename, $json);
		}

	}
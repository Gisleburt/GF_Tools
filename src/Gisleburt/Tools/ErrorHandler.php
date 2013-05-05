<?php

	/**
	 * An all encompassing Error and Exception Handler. Saves errors to disk, db, and emails them.
	 * Will also show acceptable user messages to the screen. 
	 * @author Daniel Mason
	 * @copyright Daniel Mason, 2012
	 * @version 0.9
	 */

	namespace Gisleburt\Tools;

	class ErrorHandler {
		
		const DEFAULT_MESSAGE = 'Oh no! Somethings gone wrong. We\'ll put the developers right on it.';
		
		// TODO: Maybe make these protected and add setters
		
		public static $errorFolder;
		public static $errorEmail;
		public static $devmode;
		
		protected static $errorDbServer;
		protected static $errorDbUser;
		protected static $errorDbPass;
		protected static $errorDbSchema;
		protected static $errorDbTable;
		
		protected static $fatal = false;
		
		public static function handleShutdown() {
			$error = error_get_last();
			if(is_array($error) && $error['type'] == 1) {
				self::$fatal = true;
				self::handleError($error['type'], $error['message'], $error['file'], $error['line']);
			}
		}

		public static function handleError($errorCode, $errorMessage, $filename, $line, $context = null) {
			//error_reporting(0);
			$info = self::formatErrorString($errorMessage, self::cleanFilename($filename), $line, $context);
			
			self::displayMessages($info);
			self::saveToDisk($info);
			self::saveToDB($errorMessage, $filename, $line);
			self::email($info);

			die;
		}
		
		/**
		 * Handles Exceptions
		 * @param Exception $exception Either an exception or something derived from it.
		 */
		public static function handleException(\Exception $exception) {
			//error_reporting(0);
			$info = self::formatExceptionString($exception);
			
			if(get_class($exception) == 'LazyData_Exception' && $exception->getPublicMessage())
				self::displayMessages($info, $exception->getPublicMessage());
			else
				self::displayMessages($info);
			
			self::saveToDisk($info);
			self::saveToDB($exception->getMessage(), self::cleanFilename($exception->getFile()), $exception->getLine());
			self::email($info);
			
			die;
		}
		
		/**
		 * Turns the error into a string for use in logging / emails
		 * @param string $message
		 * @param string $filename
		 * @param int    $line
		 * @param array  $context
		 */
		protected static function formatErrorString($message, $filename, $line, array $context = null) {
			$filename = self::cleanFilename($filename);
			
			$date = date('[Y-m-d H:i:s]');
			
			$info = self::formatTrace($filename, $line, $message, 0);
			
			if($context)
				$info .= self::formatErrorContext($message, $context);
			
			return  "{$date}\n\t{$info}\n\n";
			
		}
		
		/**
		 * Formats an Exception into a string for use in log and email
		 * @param Exception $exception
		 * @return string
		 */
		protected static function formatExceptionString(\Exception $exception) {
			$date = date('[Y-m-d H:i:s]');
			
			$lines = array();
			
			$trace = $exception->getTrace();
			
			$lines[] = self::formatTrace($exception->getFile(), $exception->getLine(), $exception->getMessage(), 0);

			foreach($trace as $i => $info) {
				$file = isset($info['file']) ? $info['file'] : '';
				$line = isset($info['line']) ? $info['line'] : '';
				$function = isset($info['function']) ? $info['function'] : '';
				$lines[] = self::formatTrace($file, $line, $function, $i+1);
			}
			
			$info = implode("\n\t",$lines);
			
			return "{$date}\n\t{$info}\n\n";
				
		}
		
		
		/**
		 * Saves an exception to the log file
		 * @param string $exception
		 * @param bool True if the file was successfully written
		 */
		protected static function saveToDisk($info) {
			if(isset(self::$errorFolder)) {
				$file = fopen(self::$errorFolder.'/'.date('Y-m-d').'.txt', 'a');
				if($file) {
					fwrite($file, $info);
					fclose($file);
					return true;
				}
			}
			return false;
		}
		
		/**
		 * Saves error to database if the correct settings are passed.
		 * @param string $message
		 * @param string $file
		 * @param int $line
		 * @return
		 */
		protected static function saveToDB($message, $file, $line) {
			if(self::$errorDbServer && self::$errorDbUser && self::$errorDbPass && self::$errorDbSchema && self::$errorDbTable) {
				// Connect
				$mysql = mysql_connect(self::$errorDbServer, self::$errorDbUser, self::$errorDbPass);
				
				if($mysql) {
					// Clean the data
					$message = mysql_real_escape_string($message, $mysql);
					$file    = mysql_real_escape_string($file,    $mysql);
					$line    = (int)$line;
					
					$schema  = mysql_real_escape_string(self::$errorDbSchema, $mysql);
					$table   = mysql_real_escape_string(self::$errorDbTable, $mysql);
					
					// Assumes a table of structure id, date, message, file, line
					$query = "INSERT INTO `{$schema}`.`{$table}` VALUES(null, NOW(), '{$message}', '{$file}', {$line})";
					
					// Save
					$result = mysql_query($query);
					
					// Close
					mysql_close($mysql);
					
					return $result;
				}
			}
			return false;
		}
		
		
		/**
		 * Emails the error to a preset reciever (requires config)
		 * @param string $info
		 * 
		 */
		protected static function email($info) {
			$to =      self::$errorEmail;
			$from =    self::$errorEmail;
			$subject = 'Error:';
			$message = $info;
			// TODO: Learn how to send e-mail
		}
		
		/**
		 * Formats a stack trace for input into a log or e-mail.
		 * @param string $filename Name of script where call was made
		 * @param int $line Line number in file
		 * @param string $function Name of function called
		 * @param int $number Index is stack trace
		 * @return string
		 */
		protected static function formatTrace($filename, $line, $function, $number) {
			$filename = self::cleanFilename($filename);
			return "#$number $filename($line):\t$function";
		}
		
		protected static function cleanFilename($filename) {
			if(defined('APP_DIR'))
				$filename = str_replace(APP_DIR, '', $filename);
			return $filename;
		}
		
		
		/**
		 * If a variable in the context is named in the error message, we can get that info
		 * @param array $context
		 * @return string
		 */
		protected static function formatErrorContext($message, array $context) {
			$contextInfo = array();
			foreach($context as $variable => $value) {
				if(strpos($message, $variable)) { // See if the variable is named in the error
					$contextInfo[] = "$variable: ".var_export($value, true);
				}
			}
			return implode("\n\n", $contextInfo);
		}
		
				
		/**
		 * Display information to the user
		 * @param string $devMessage Only displayed if devMode is on
		 * @param string $publicMessage Something to tell the user
		 */
		protected static function displayMessages($devMessage, $publicMessage = null) {
			global $smarty;
			
			if(!isset($publicMessage))
				$publicMessage = self::DEFAULT_MESSAGE;
			
			if(isset($smarty) && !self::$fatal) {
				
				$smarty->assign('publicMessage', $publicMessage);
				if(self::$devmode)
					$smarty->assign('devMessage', $devMessage);
				$smarty->display('system/error.tpl');

			} else {
				
				echo "<p>An error has occured</p>\n";
				echo "<p>$publicMessage</p>\n";
				if(self::$devmode)
					echo "<p><b>Dev Message:</b></p><pre>$devMessage</pre>\n";
				
			}
		}
		
		public static function setErrorDb($server, $user, $pass, $schema = null, $table = null) {
			// Defaults
			if(!isset($schema))
				$schema = 'errors';
			if(!isset($table))
				$table = 'errors';
			
			self::$errorDbServer = $server;
			self::$errorDbUser   = $user;
			self::$errorDbPass   = $pass;
			self::$errorDbSchema = $schema;
			self::$errorDbTable  = $table;
		}
		
	}
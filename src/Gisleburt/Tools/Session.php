<?php

namespace Gisleburt\Tools;

/**
 * Session
 * Use the static method to get and retrieve objects from the session.
 * Extend into other objects.
 * If you only need to store one of a given type of object do this:
 *   User::getSession()
 * However it's probably better to give the object a namespace incase you need to store more than one:
 *   User::getSession('bob');
 * @package Gisleburt\Tools
 */
class Session
{

    /**
     * This is the key to find the object in the session
     * @var string
     */
    protected $_namespace;

    /**
     * You can not instantiate this class directly. Use getSession()
     * @param string $namespace The key into SESSION
     */
    protected function __construct($namespace)
    {
        $this->_namespace = $namespace;
    }

    /**
     * Returns the key into SESSION being used.
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Delete this object
     */
    public function clearSession()
    {
        unset($_SESSION[$this->getNamespace()]);
    }

    /**
     * Checks for the existence of the object in session, creates it if not found
     * @param string $namespace
     * @throws \Exception If another type of object exists in the given namespace
     * @return $this
     */
    public static function getSession($namespace = null)
    {

        if (!session_id()) {
            session_start();
        }

        $className = get_called_class();

        if (!$namespace) {
            $namespace = $className;
        }

        // If it doesn't exist, create it and return it
        if (!isset($_SESSION[$namespace])) {
            $_SESSION[$namespace] = new $className($namespace);
            return $_SESSION[$namespace];
        }

        // If it already exists check it's ok and return it
        if (get_class($_SESSION[$namespace]) == $className) {
            return $_SESSION[$namespace];
        }

        // If it's not ok throw an Exception.
        throw new \Exception('Object in session does not match requested object.');
    }

}

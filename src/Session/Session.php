<?php
namespace Bow\Session;

use Bow\Support\Str;
use Bow\Security\Security;
use InvalidArgumentException;
use Bow\Interfaces\CollectionAccessStatic;

/**
 * Class Session
 *
 * @author Franck Dakia <dakiafranck@gmail.com>
 * @package Bow\Support
 */
class Session implements CollectionAccessStatic
{

    /**
     * Session starteur.
     */
    private static function start()
    {
        if (PHP_SESSION_ACTIVE !== session_status()) {
            session_name("SESSID");
            if (! isset($_COOKIE["SESSID"])) {
                session_id(hash("sha256", Security::encrypt(Str::repeat(Security::generateCsrfToken(), 2))));
            }
            session_start();
        }
    }

    /**
     * @return array
     */
    private static function filter()
    {
        $arr = [];
        static::start();

        foreach($_SESSION as $key => $value) {
            if (!in_array($key, ["__bow.flash", "__bow.old", "__bow.event.listener", "__bow.csrf", "__bow.cookie.secure"])) {
                $arr[$key] = $value;
            }
        }

        return $arr;
    }

    /**
     * has, vérifie l'existance une clé dans la colléction de session
     *
     * @param string $key
     * @param bool $strict
     *
     * @return boolean
     */
    public static function has($key, $strict = false)
    {
        static::start();
        $isset = isset($_SESSION[$key]);

        if ($strict) {
            if ($isset) {
                $isset = $isset && !empty($_SESSION[$key]);
            }
        }

        return $isset;
    }

    /**
     * isEmpty, vérifie si une colléction est vide.
     *
     *	@return boolean
     */
    public static function isEmpty()
    {
        return empty(self::filter());
    }

    /**
     * get, permet de récupérer une valeur ou la colléction de valeur.
     *
     * @param string $key=null
     * @param mixed $default
     *
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        static::start();

        if (static::has($key)) {
            return $_SESSION[$key];
        }

        if (isset($_SESSION["__bow.flash"][$key])) {
            $flash = $_SESSION["__bow.flash"][$key];
            static::reflash($key);
            return $flash;
        }

        if (is_callable($default)) {
            return $default();
        }

        return $default;
    }

    /**
     * add, ajoute une entrée dans la colléction
     *
     * @param string|int $key La clé de la donnée à ajouter
     * @param mixed $data La donnée à ajouter
     * @param boolean $next Elle permet si elle est a true d'ajouter la donnée si la clé existe
     *                      Dans un tableau
     *
     * @throws InvalidArgumentException
     * @return mixed
     */
    public static function add($key, $data, $next = false)
    {
        static::start();

        if ($next !== true) {
            return $_SESSION[$key] = $data;
        }

        if (! static::has($key)) {
            $_SESSION[$key] = $data;
        }

        if (! is_array($_SESSION[$key])) {
            $_SESSION[$key] = [$_SESSION[$key]];
        }
        array_push($_SESSION[$key], $data);
        return $data;
    }

    /**
     * Retourne la liste des variables de session
     *
     * @return array
     */
    public static function all()
    {
        return static::filter();
    }

    /**
     * remove, supprime une entrée dans la colléction
     *
     * @param string $key La clé de l'élément a supprimé
     *
     * @return mixed
     */
    public static function remove($key)
    {
        self::start();
        $old = null;
        if (isset($_SESSION[$key])) {
            $old = $_SESSION[$key];
        }
        unset($_SESSION[$key]);
        return $old;
    }

    /**
     * set
     *
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    public static function set($key, $value)
    {
        $old = null;
        static::start();

        if (static::has($key)) {
            $old = $_SESSION[$key];
            $_SESSION[$key] = $value;
        } else {
            $_SESSION[$key] = $value;
        }

        return $old;
    }

    /**
     * flash
     *
     * @param mixed $key
     * @param mixed $message
     * @return mixed
     */
    public static function flash($key, $message = null)
    {
        static::start();

        if (! static::has("__bow.flash")) {
            $_SESSION["__bow.flash"] = [];
        }

        if ($message !== null) {
            $_SESSION["__bow.flash"][$key] = $message;
            return null;
        }

        return isset($_SESSION["__bow.flash"][$key]) ? $_SESSION["__bow.flash"][$key] : null;
    }

    /**
     * Retourne la liste des données de la session sous forme de tableau.
     *
     * @return array
     */
    public static function toArray()
    {
        return self::filter();
    }

    /**
     * Vide le système de flash.
     *
     * @param string $key
     */
    private static function reflash($key)
    {
        static::start();
        unset($_SESSION["__bow.flash"][$key]);
    }

    /**
     * clear, permet de vider le cache sauf csrf|bow.flash
     */
    public static function clear()
    {
        static::start();

        foreach(static::filter() as $key => $value){
            unset($_SESSION[$key]);
        }
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        static::start();
        return json_encode(static::filter());
    }

    /**
     * __call
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists(static::class, $name)) {
            return call_user_func_array([static::class, $name], $arguments);
        }

        new \BadMethodCallException('La methode ' . $name . ' n\'exist pas.');
    }
}

<?php
namespace Bow\View;

use BadMethodCallException;
use Bow\Application\Configuration;
use Bow\View\Exception\ViewException;
use function call_user_func_array;
use function class_exists;
use function method_exists;

class View
{
    /**
     * @var Configuration
     */
    private static $config;

    /**
     * @var View
     */
    private static $instance;

    /**
     * @var EngineAbstract
     */
    private static $template;

    /**
     * @var bool
     */
    private $cachabled = true;

    /**
     * @var array
     */
    private static $container = [
        'twig' => Engine\TwigEngine::class,
        'php' => Engine\PHPEngine::class,
        'mustache' => Engine\MustacheEngine::class,
        'pug' => Engine\PugEngine::class
    ];

    /**
     * View constructor.
     * @param Configuration $config
     * @throws ViewException
     */
    public function __construct(Configuration $config)
    {
        $engine = $config['view.engine'];

        if (is_null($engine)) {
            throw new ViewException('Le moteur de template non défini.', E_USER_ERROR);
        }

        if (!in_array($engine, ['twig', 'mustache', 'pug', 'php'], true)) {
            throw new ViewException('Le moteur de template n\'est pas implementé.', E_USER_ERROR);
        }

        static::$config = $config;
        static::$template = new static::$container[$engine]($config);
    }

    /**
     * Permet de configurer la classe
     *
     * @param array $config
     */
    public static function configure($config)
    {
        static::$config = $config;
    }

    /**
     * Permet de créer et retourner une instance de View
     *
     * @return View
     */
    public static function singleton()
    {
        if (!static::$instance instanceof View) {
            static::$instance = new self(self::$config);
        }

        return static::$instance;
    }

    /**
     * Permet de faire le rendu d'une vue
     *
     * @param string $viewname
     * @param array $data
     * @return string
     * @throws ViewException
     */
    public static function make($viewname, array $data = [])
    {
        return static::singleton()->getTemplate()->render($viewname, $data);
    }

    /**
     * Permet de récuperer l'instance du template
     *
     * @return EngineAbstract
     */
    public function getTemplate()
    {
        return static::$template;
    }

    /**
     * @param string $engine
     * @return View
     */
    public function setEngine($engine)
    {
        static::$instance = null;
        static::$config['view.engine'] = $engine;

        return $this;
    }

    /**
     * @param $cachabled
     */
    public function cachable($cachabled)
    {
        $this->cachabled = $cachabled;
    }

    /**
     * @param string $extension
     * @return View
     */
    public function setExtension($extension)
    {
        static::$instance = null;
        static::$config['view.extension'] = $extension;

        return $this;
    }

    /**
     * Ajouter un moteur de template
     *
     * @param $name
     * @param $engine
     * @return bool
     * @throws ViewException
     */
    public function pushEngine($name, $engine)
    {
        if (isset(static::$container[$name])) {
            return true;
        }

        if (!class_exists($engine)) {
            throw new ViewException($engine, ' N\'existe pas.');
        }

        static::$container[$name] = $engine;
        return true;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if (static::$instance instanceof View) {
            if (method_exists(static::$instance, $name)) {
                return call_user_func_array([static::$instance, $name], $arguments);
            }
        }

        throw new BadMethodCallException($name . ' impossible de lance cette methode');
    }
}
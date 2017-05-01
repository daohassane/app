<?php
namespace Bow\Support\Test;

use Bow\Http\Client\Parser;
use Bow\Http\Client\HttpClient;
use PHPUnit\Framework\TestCase;

class BowTestCase extends TestCase
{
    /**
     * @var string
     */
    protected $base_url = '';

    /**
     * @param $url
     * @return string
     */
    private function formatUrl($url)
    {
        return rtrim($this->base_url, '/').$url;
    }

    /**
     * @param $url
     * @param $param
     * @return Parser
     */
    public function get($url, array $param = [])
    {
        $http = new HttpClient($this->formatUrl($url));
        return $http->get($param);
    }

    /**
     * @param $url
     * @param $param
     * @return Parser
     */
    public function post($url, array $param = [])
    {
        $http = new HttpClient($this->formatUrl($url));
        return $http->post($param);
    }

    /**
     * @param $url
     * @param $param
     * @return Parser
     */
    public function put($url, array $param = [])
    {
        $http = new HttpClient($this->formatUrl($url));
        return $http->put($param);
    }

    /**
     * @param $url
     * @param array $param
     * @return Parser
     */
    public function delete($url, array $param = [])
    {
        $param = array_merge([
            '_method' => 'DELETE'
        ], $param);
        return $this->put($url, $param);
    }

    /**
     * @param $url
     * @param $param
     * @return Parser
     */
    public function patch($url, array $param = [])
    {
        $param = array_merge([
            '_method' => 'PATCH'
        ], $param);
        return $this->put($url, $param);
    }

    /**
     * @param $method
     * @param $url
     * @param array $params
     * @return Behovior
     */
    public function visite($method, $url, array $params = [])
    {
        $method = strtolower($method);
        if (! method_exists($this, $method)) {
            throw new \BadMethodCallException('La methode ' . $method . ' n\'exist pas');
        }

        return new Behovior($this->$method($url, $params));
    }
}
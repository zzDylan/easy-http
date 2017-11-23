<?php

namespace Zzdylan\Tools;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Cookie\FileCookieJar;

class Http
{
    public static $instance;

    protected $client;

    /**
     * @var FileCookieJar;
     */
    protected $cookieJar;

    protected $cookiePath;

    public function __construct()
    {
        $this->cookieJar = new FileCookieJar($this->cookiePath?$this->cookiePath:'/tmp', true);
        $this->client = new HttpClient(['cookies' => $this->cookieJar]);
    }

    public function get($url, array $options = [])
    {
        return $this->request($url, 'GET', $options);
    }

    public function post($url, $query = [], $array = false)
    {
        $key = is_array($query) ? 'form_params' : 'body';

        $content = $this->request($url, 'POST', [$key => $query]);

        return $array ? json_decode($content, true) : $content;
    }

    public function json($url, $params = [], $array = false, $extra = [])
    {
        $params = array_merge(['json' => $params], $extra);

        $content = $this->request($url, 'POST', $params);

        return $array ? json_decode($content, true) : $content;
    }

    public function setCookiePath($cookiePath){
        $this->cookiePath = $cookiePath;
    }

    public function setClient(HttpClient $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Return GuzzleHttp\Client instance.
     *
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param $url
     * @param string $method
     * @param array  $options
     * @param bool   $retry
     *
     * @return string
     */
    public function request($url, $method = 'GET', $options = [], $retry = false)
    {
        try {
            $options = array_merge(['timeout' => 10, 'verify' => false], $options);

            $response = $this->getClient()->request($method, $url, $options);

            $this->cookieJar->save($this->cookiePath);

            return $response->getBody()->getContents();
        } catch (\Exception $e) {

            if (!$retry) {
                return $this->request($url, $method, $options, true);
            }

            return false;
        }
    }
}

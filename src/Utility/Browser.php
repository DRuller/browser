<?php
namespace RangeRay\Browser\Utility;


use RangeRay\Browser\Model\Entity\Response;
use RangeRay\Browser\Plugin;
use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use JonnyW\PhantomJs\Client;


/**
 * Class Browser
 * @package Browser\Utility
 */
class Browser
{

    /**
     * @var Client
     */
    private static $client;


    /**
     * @var bool
     */
    private static $is_init = false;



    private static function getPathApp()
    {
        return  dirname(dirname(__DIR__));
    }
    /**
     * @return bool
     */
    public static function init()
    {
        if(!self::$is_init){
            self::$is_init = true;
            self::$client = Client::getInstance();
            self::$client->getEngine()->setPath(BROWSER_DIR . '/bin/phantomjs');
            Configure::load('browser');
        }

        return true;
    }


    /**
     * @param string|null $url
     * @param string $method
     * @param array $headers
     * @param array $data
     * @param string $proxy
     * @param string $user_agent
     * @param string $cookie_file
     * @return bool|Response
     */
    public static function browse(string $url = null, string $method = 'GET', array $headers = [], array $data = [], $proxy = null, $user_agent = null, $cookie_file = false)
    {
        $start = microtime(true);

        if(!$url){
           return false;
        }

        self::init();

        if($cookie_file){
            debug($cookie_file);
            self::$client->getEngine()->addOption('--cookies-file=' . $cookie_file);
        }
        self::$client->isLazy();

        $request = self::$client->getMessageFactory()->createRequest();
        $response = self::$client->getMessageFactory()->createResponse();

        if($proxies = Configure::read('Browser.proxies')){

            $random_proxy = $proxy ? $proxy : $proxies[array_rand($proxies)];

            if(Configure::read('Browser.auth.login') && Configure::read('Browser.auth.pass')){
                $login = Configure::read('Browser.auth.login');
                $pass = Configure::read('Browser.auth.pass');
                self::$client->getEngine()->addOption("--proxy-auth=$login:$pass");
            }
            self::$client->getEngine()->addOption("--proxy=$random_proxy");

        }

        if($user_agents = Configure::read('Browser.user_agents')){

            $random_user_agent = $user_agent ? $user_agent : $user_agents[array_rand($user_agents)];

            $request->addHeader('User-Agent', $random_user_agent);
        }

        if($headers){
            foreach ($headers as $header => $val) {
                $request->addHeader($header, $val);
            }
        }


        $request->setMethod($method);

        $request->setUrl($url);

        if($data){
            $request->setRequestData($data);
        }

        self::$client->send($request, $response);

        $end = microtime(true) - $start;
        $time = number_format($end);

        $response_browser = new Response([
            'request' => $request,
            'response' => $response,
            'execution_time' => $time,
            'proxy' => $random_proxy ?? false,
            'user_agent' => $random_user_agent ?? false
        ]);

        return $response_browser;
    }
}
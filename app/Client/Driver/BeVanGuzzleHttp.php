<?php
/**
 * Created by Bevan.
 * User: Bevan@zhoubinwei@aliyun.com
 * Date: 2018/9/12
 * Time: 11:03
 */

namespace App\Client\Driver;

use App\Client\Contracts\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use function GuzzleHttp\Promise\unwrap;
use function PHPSTORM_META\type;

class BeVanGuzzleHttp implements Request
{
    /**
     * 请求成功的数据
     * @var
     */
    public $fulfilled;

    /**
     * 请求失败的数据
     * @var
     */
    public $rejected;

    public function __construct($config = [])
    {
        $this->client = new Client($config);
    }

    public function get($url, $params = [])
    {
        $this->client->get($url, $params);
    }

    public function post($url, $params = [])
    {
        $this->client->post($url, $params);
    }

    public function request($method, $url, $params = [])
    {
        $this->client->request($method, $url, $params);
    }

    /**
     * simple async http con request
     * @param $promise
     * @return array
     * @throws \Throwable
     */
    public function asyncRequest($promise)
    {
        $re_promise = [];
        foreach ($promise as $key => $item) {
            $re_promise[$key] = $this->client->getAsync($item['uri']);
        }

        $results = unwrap($re_promise);

        return $results;
    }

    /**
     * 异步携程 并发 请求
     * @param $requestData
     */
    public function asyncPoolRequest($requestData)
    {

        $requests = function ($requestData) {
//            $uri = 'http://127.0.0.1:8126/guzzle-server/perf';
//            $len = count($requestData);
            foreach ($requestData as $key => $request) {
                yield new \GuzzleHttp\Psr7\Request($request['method'], $request['uri']);
            }
//
//            for ($i = 0; $i < $len; $i++) {
//                yield new \GuzzleHttp\Psr7\Request($requestData[$i]['method'], $requestData['uri']);
//            }
        };

        $pool = new Pool($this->client, $requests($requestData), [
            'concurrency' => 5,
            'fulfilled' => function ($response, $index) {
                // this is delivered each successful response
                // 成功的 响应
                $this->fulfilled[$index] = $response;
            },
            'rejected' => function ($reason, $index) {
                // this is delivered each failed request
                // 失败的 响应
                $this->rejected[$index] = $reason;
            },
        ]);
        $start_time = time();
        var_dump($start_time);
        $promise = $pool->promise();
        $promise->wait();

        var_dump('耗时:' . (time() - $start_time));
//        var_dump($this->fulfilled);
//        var_dump($this->rejected);
        var_dump('结束');

        die;
    }
}
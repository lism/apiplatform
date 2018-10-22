<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2018/8/27
 * Time: 14:16
 */

namespace App\Http\Api\V1;


use App\Client\httpClient;
use App\Events\AsyncLogEvent;
use App\Events\UserRegisterEvent;
use App\Exceptions\BevanJwtAuthException;
use App\Http\Api\BaseController;
use App\Models\AppUser;
use App\Models\PlatformProduct;
use App\Models\PlatformProductCategory;
use App\Models\ProductServices;
use App\Services\Admin\AppKeySecretService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Psr\Http\Message\ResponseInterface;
use Tymon\JWTAuth\Facades\JWTAuth;

class ShowController extends BaseController
{
    /**
     *  测试
     */
    public function index()
    {
//        echo 'ok api';
//        $appkey = urlencode('439d8c975f26e5005dcdbf41b0d84161');
//        $appsecret = urlencode('08aee6276db142f4b8ac98fb8ee0ed1b');
////        $appsecret = urlencode('');
//
//        // curl
//
//        $url = 'http://laravelapi.local/api/cli/token?app_key=' . $appkey . '&$app_secret=' . $appsecret;

//        $ch = curl_init();
//        curl_setopt($ch,CURLOPT_URL,$url);
//        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
//        curl_setopt($ch,CURLOPT_HEADER,0);
//        $output = curl_exec($ch);
//
//        curl_close($ch);

//        if($output == false){
//            return Response()->json(['status_code'=>500,'msg'=>'请求失败']);
//        }else{
//            var_dump($output);die;
//            return Response()->json(['status_code'=>200,'msg'=>'success','data'=>['access_token'=>'']]);
//        }

//        $dispatcher = app('Dingo\Api\Dispatcher');

//        $dispatcher->get();
//        $res = $this->api->get('cli/token?app_key=' . $appkey . '&app_secret=' . $appsecret);

//        var_dump($res);die;


//        Cache::add('test_service', [1 => [1, 3, 4], 2 => [3, 46]], 3);
//
//
//        $va = Cache::get('test_service');
//
//        dd($va);

//        var_dump(PlatformProductCategory::with('products')->find(3));die;
//        var_dump(PlatformProduct::with('services')->find(2)->toArray());die;

        dd(PlatformProduct::with('services')->find(2)->toArray());

    }

    public function testSign()
    {
        $app_key = '4a5a48028c6b973820e2d719be41e384';
        $app_secret = 'c2069e2ffd507a83f85e92d1543e14fd';
        $sequenceId = 123456;
        $reqData = [
            'name' => 'bevan',
            'age' => 18
        ];

        $sign = md5(json_encode($reqData, JSON_UNESCAPED_UNICODE) . $sequenceId . $app_secret);

        return $this->responseClient(200, 'success', ['app_key' => $app_key, 'sequenceId' => $sequenceId, 'reqData' => $reqData, 'sign' => $sign]);
    }

    public function testEvent()
    {
        // 事件取消
        Event::fire(new UserRegisterEvent());
//        $this->dispatch(new MailJob());

        return Response()->json(['status_code' => 200, 'msg' => '任务投递成功', 'data' => '']);
    }

    public function testLogEvent()
    {
        Event::fire(new AsyncLogEvent('日志信息', 'info'));

        return $this->responseClient(200, '任务投递成功', '');
    }

    /**
     *  测试 lua
     */
    public function testLua()
    {
//        $lua = new Lua();
//        $lua->eval(<<<CODE
//    function dummy(foo, bar)
//        print(foo, ",", bar, ngx.var.request_method)
//    end
//CODE
//        );
//        $lua->call("dummy", array("Lua", "geiliable"));
//        $lua->dummy("Lua", "geiliable"); // __call()
//        var_dump($lua->call(array("table", "concat"), array(array(1 => 1, 2 => 2, 3 => 3), "-")));

        $client = new httpClient();

        $promise = [
            '0' => [
                'method' => 'post',
                'uri' => 'http://bevan.top/api/testLua2',
                'options' => []
            ],
            '1' => [
                'method' => 'post',
                'uri' => 'http://bevan.top/api/testLua2',
                'options' => [
                    'header' => ['Content-type' => 'html/json'],
                    'json' => ['name' => 'bevan', 'age' => 18]
                ]
            ],
            '2' => [
                'method' => 'get',
                'uri' => 'http://bevan.top/api/testLua4',
                'options' => []
            ],
            '3' => [
                'method' => 'get',
                'uri' => 'http://bevan.top/api/testLua3',
                'options' => []
            ],
        ];

        $client->request->asyncPoolRequest($promise);

        $responseArr = $client->request->getResponse();

        return $this->responseClient(200, '返回数据', $responseArr);
    }

    public function testLua2(Request $request)
    {
        $reData['name'] = $request->input('name');
        $reData['age'] = $request->input('age');
        return $this->responseClient(200, 'lua2', $reData);
    }

    public function testLua3()
    {
//        sleep(1);
        throw new \Exception('出现错误了');
        return $this->responseClient(200, 'lua3');
    }

    public function testLua4(Request $request)
    {
//        sleep(1);
//        $user = User::find(2)->toArray();
        $user = [];
//        User::where('id', 2)->update(['name' => 'bevan']);
//        $res = User::withTrashed()->get();
        $realIp = $request->header('X-Forwarded-For');

        return $this->responseClient(200, 'lua4', ['test' => 'fdsf', 'remote_addr' => $request->ip(), 'real_ip' => $realIp]);
    }

    public function testLua5(Request $request)
    {
        $req_data['headers'] = $request->header();
        $req_data['input'] = $request->input();

        return $this->responseClient(200, 'success', $req_data);
    }

    public function testAsync()
    {
        $client = new httpClient();

        $client->request->requestAsync('post', 'http://bevan.top/api/testLua2', [], function ($res) {
            // end
//            var_dump(get_class_methods($res));
//
//            var_dump('isok');
//            Log::info(get_class_methods($res));
            return $this->responseClient(200, '回调返回成功', ['name' => 'bevan']);
        }, function ($e) {
            $errormsg = $e->getMessage();
            $status_code = $e->getCode();
            Log::info(get_class_methods($errormsg));
//            return $this->responseClient($status_code, $errormsg, []);
        });

        var_dump('dfddf');
        die;
    }

    public function testNewLua(Request $request)
    {
//        dd($request);
        $input = $request->all();
        $headers = $request->header();

        return $this->responseClient(200, '成功', [
            'headers' => $headers,
            'input' => $input
        ]);

//        $lua = new \Lua();
//        $lua->eval(<<<CODE
//    function dummy(foo, bar)
//        print(foo, ",", bar)
//    end
//CODE
//        );

//        $lua->eval(<<<CODE
//    local address = ngx.var.remote_addr
//    print(address)
//CODE
//        );

//        $lua->eval(<<<CODE
//local http = require "http"
//local httpc = http.new()
//local url = "http://bevan.top/api/testLua4"
//local resStr --响应结果
//local res, err = httpc:request_uri(url, {
//    method = "POST",
//    --args = str,
//    body = str,
//    headers = {
//        ["Content-Type"] = "application/json",
//    }
//})
//
//if not res then
//    ngx.log(ngx.WARN,"failed to request: ", err)
//    return resStr
//end
//--请求之后，状态码
//ngx.status = res.status
//if ngx.status ~= 200 then
//    ngx.log(ngx.WARN,"非200状态，ngx.status:"..ngx.status)
//    return resStr
//end
//--header中的信息遍历，只是为了方便看头部信息打的日志，用不到的话，可以不写的
//for key, val in pairs(res.headers) do
//    if type(val) == "table" then
//        ngx.log(ngx.WARN,"table:"..key, ": ", table.concat(val, ", "))
//    else
//        ngx.log(ngx.WARN,"one:"..key, ": ", val)
//    end
//end
//--响应的内容
//resStr = res.body
//CODE
//    );

//        $lua->call("dummy", array("Lua", "geiliable\n"));
//        $lua->dummy("Lua", "geiliable"); // __call()
//        var_dump($lua->call(array("table", "concat"), array(array(1=>1, 2=>2, 3=>3), "-")));

//        $lua->eval("lua_statements");     //eval lua codes
//        $lua->include("lua_script_file"); //import a lua script

//        $lua->assign("name", 'bevan'); //assign a php variable to Lua
//        $lua->register("name", 'testlua'); //register a PHP function to Lua with "name"
//
//        $lua->call('testlua', array() /*args*/);
//        $lua->call($resouce_lua_anonymous_function, array() /*args);
//   $lua->call(array("table", "method"), array(...., "push_self" => [true | false]) /*args*/);
//
//        $lua->{$lua_function}(array()/*args*/);
    }

    public function testUpload(Request $request)
    {
        $file = $request->file('file');

        if (!$file) throw new \Exception('未获取上传文件', 401);

        $path = $file->store('public/imgs');
        $path = strstr($path, '/imgs');
        $get_path = 'storage' . $path;

        $url = asset($get_path);
        return $this->responseClient(200, 'success', ['path' => $path, 'url' => $url]);
    }

    public function testNewException()
    {
//        Redis::SREM('ip_blacklist', '172.30.202.241');
        Redis::SADD('ip_blacklist', '49.72.219.19');
        $list = Redis::smembers('ip_blacklist');
        dd($list);
//        throw  new BevanJwtAuthException(4033, '新的异常处理', 500, null);
    }

    public function testJWT()
    {
        $user = User::findOrFail(1);

        $token = JWTAuth::claims(['model' => 'user'])->fromUser($user);

        return $this->tokenResponse($token);
    }

    public function getNetJWT(Request $request)
    {
        return $this->responseClient(200, 'get', [
            'headers' => $request->header(),
            'input' => $request->input()
        ]);
    }

    public function appMapRedis()
    {
//        $users = AppUser::where('model', 'App\User')->get(['id', 'app_key', 'app_secret','type'])->toArray();

        // 建议 分片 处理
//        foreach ($users as $u) {
//            Redis::set($u['app_key'], $u['app_secret'] . $u['type']);
//        }

        // 分块
//        AppUser::chunk(2, function ($users) {
//            foreach ($users as $u) {
////                var_dump($u->type . ':' . $u->app_key . ',user_id:' . $u->id);
//                Redis::set($u['app_key'], $u['app_secret'] . $u['type']);
//            }
//        });

        $valid_time = Redis::get('app_key_last_valid_time');

        $diff = time() - (int)$valid_time;

        if (empty(Redis::keys('app_key:*')) || $diff >= 24 * 3600) {
            // update
            (new AppKeySecretService())->mapAppkeysecret();
        }

        // cursor handle
//        foreach (AppUser::where('model', 'App\User')->cursor() as $user) {
//            Redis::set($user['app_key'], $user['app_secret'] . $user['type']);
//        }

        return $this->responseClient(200, '成功', []);
    }

    public function testAdminConfig()
    {
//        AppUser::find(1);
//        dd(config('test_admin_key'));
//        var_dump(config('test_admin_secret'));
//        dd(User::find(7)->appuser());

//        tfdfad
    }

    public function testApiCount()
    {
        $count = Redis::get('api_request_condition');

        dd($count);
    }
}
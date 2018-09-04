<?php
/**
 * Created by Bevan.
 * User: Bevan@zhoubinwei@aliyun.com
 * Date: 2018/8/31
 * Time: 10:38
 */

namespace App\Services;


use App\Exceptions\AdminJwtException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminUserService extends BaseService
{
    public $model;

    public function login($login_name, $password, $model = 'user')
    {
        // check login type
        $this->model = $model;
        $type = $this->getLoginType($login_name);

        $loginMsg = [
            $type => $login_name,
            'password' => $password
        ];

        // add login event

        $token = $this->loginByType($loginMsg);

        if ($token) {
            // 是否 是 后台 登录
            $loginer = JWTAuth::parseToken()->user();
            if (get_class($loginer) == 'App\Models\Admin') {
                // 是否 拥有 角色权限
                if (!$loginer->hasRole('opeartor')) throw new AdminJwtException('该类型角色不允许登录');
            }
        }

        return $token;
    }

    public function getLoginType($login_name)
    {
        if (filter_var($login_name, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        } elseif ($this->checkIsPhone($login_name)) {
            return 'telephone';
        } else {
            return 'name';
        }
    }

    /**
     * login
     * @param array $msg
     * @return mixed $access_token or false
     */
    public function loginByType(Array $msg)
    {
        return JWTAuth::claims(['model' => $this->model])->attempt($msg);
    }

}
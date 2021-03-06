<?php

namespace App;

use App\Models\AppUser;
use App\Models\UuidUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    const IS_ACTIVE_STATUS = 1;  // active status flag

    const LOGIC_MODEL = 'App\User';

    const APP_KEY_FLAG = 'api_app_key:';

    const APP_SECRET_FLAG = 'app_secret';

    const APP_USER_TYPE_FLAG = 'user_type';

    const APP_KEY_TYPE = 'app_key_type';

    const APP_USER_ID = 'user_id';

    use Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'telephone', 'type',
    ];

//    protected $dateFormat = 'U';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    function getJWTIdentifier()
    {
        return $this->getKey();
    }

    function getJWTCustomClaims()
    {
        return [];
    }

    function app($model)
    {
        return $this->hasOne('App\Models\AppUser', 'user_id')->where('model', $model)->first(['app_key', 'app_secret', 'user_id', 'created_at', 'model']);
    }

    function appuser()
    {
        return $this->hasOne('App\Models\AppUser', 'user_id')->where('model', self::class);
    }

    function getUserApp($pk)
    {
        return AppUser::where('user_id', $pk)->where('model', 'App\User')->first(['app_key', 'app_secret', 'user_id', 'created_at', 'model']);
    }

    function getAdminApp($pk)
    {
        return AppUser::where('user_id', $pk)->where('model', 'App\Models\Admin')->first(['app_key', 'app_secret', 'user_id', 'created_at', 'model']);
    }

    function getOpenid($pk, $model_id, $model_uuid, $model)
    {
        return UuidUser::where(['user_id' => $pk, 'model_id' => $model_id, 'model_uuid' => $model_uuid, 'model' => $model])->first(['user_id', 'model_id', 'model_uuid', 'openid', 'model', 'created_at']);
    }
}

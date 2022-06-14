<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Carbon;
use DB;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $connection = "erpat";

    public function scopeMin($query) {
        return $query->select('id','uuid','email','password','first_name','last_name');
    }

    public function has_syntry($user_id) {
        $found = $this->where('id', '=', $user_id)
            ->where(function ($query) {
                $query->where('access_syntry', '=', '1')
                      ->orWhere('is_admin', '=', '1');
            })
            ->where('status', '=', 'active')
            ->where('disable_login', '=', '0')
            ->where('deleted', '=', '0')
            ->select('uuid')
            ->first();
        return $found;
    }

    public function refreshStatus($user_id) {
        $now = Carbon::now()->setTimezone('UTC')->format("Y-m-d H:i:s");
        return $this->where( "id", "=", $user_id )->update(['last_online' => $now]);
    }

    public function get_access_info($user_id) {
        return $this->where( "users.id", "=", $user_id )
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->select('users.id','users.uuid','users.email','users.user_type','users.is_admin','users.role_id','roles.permissions')
            ->first();
    }

    public function changePassword($user_id, $new_pass) {
        $password = password_hash($new_pass, PASSWORD_DEFAULT);
        return $this->where( "id", "=", $user_id )->update(['password' => $password]);
    }

    public function get_by_id($user_id) {
        $found = $this->where('id', '=', $user_id)
            ->where('status', '=', 'active')
            ->where('disable_login', '=', '0')
            ->where('deleted', '=', '0')
            ->select('image', 'first_name as fname', 'last_name as lname')
            ->first();
        return $found;
    }

    public function get_status_by_id($user_id) {
        $found = $this->where('users.id', '=', $user_id)
            ->leftJoin('attendance', function($join)
            {
                $join->on('users.id', '=', 'attendance.user_id');
            })
            ->where('users.status', '=', 'active')
            ->where('users.disable_login', '=', '0')
            ->where('users.deleted', '=', '0')
            ->select('users.image as image', 'users.first_name as fname', 'users.last_name as lname', DB::raw("IFNULL((SELECT in_time FROM attendance WHERE user_id = '$user_id' AND out_time IS NULL AND deleted = '0'), NULL) as clockin"))
            ->first();
        return $found;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Response;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Traits\UserTrait;

class UserController extends Controller
{
    use UserTrait;

    protected $curUser = null;
    public function __construct() {
        //$this->middleware('user.permit');
        $this->curUser = auth()->user();
    }

    public function signin() {
        if( auth()->check() ) {
            return redirect('home');
        }

        return view('signin');
    }

    /**
     * @param $uuid
     * @param $pword
     * @return mixed
     */
    private function getToken($uuid, $pword)
    {
        try {
            if (!$token = JWTAuth::attempt(['uuid' => $uuid, 'password' => $pword])) {
                return false;
            } else {
                return $token;
            }
        } catch (JWTAuthException $e) {
            return false;
        }
    }

    /**
     * Pass email and password as POST param.
     */
    public function login(Request $request) {
        //VALIDATE REQUIRED PARAMS
        $validator = Validator::make($request->all(), [
            'email'=>'required|email',
            'pword'=>'required'
        ]);

        if ($validator->fails()) {
            return back()->with('error', 'Web request invalid!');
        }

        $username = $request->input('email');
        $password = $request->input('pword');

        //CHECK USER TABLE IF EMAIL EXIST.
        $user = User::where('email', '=', $username)->min()->first(); //User::test();
        if(!$user) {
            return back()->with('error', 'User not found!');
        }

        $access = (new User())->get_access_info( $user->id );
        $access->permissions = unserialize( $access->permissions );
        if( !isset($access->permissions['can_use_payroll']) && !$access->permissions['can_use_payroll'] ) {
            return back()->with('error', 'Dont have permission!');
        }

        //VERIFY PASSWORD
        $hash = $user->password;
        if ((strlen($hash) === 60 && password_verify($password, $hash)) || $hash === md5($password)) {

            if($token = $this->getToken($user->uuid, $password)) {
                $cookie = cookie('erpat-pas-jwt', $token, 60 * 1); //1 hour
                return back()->with('success', 'Welcome')->withCookie($cookie);
            }
        }

        return back()->with('error', 'Authentication invalid!');
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function info()
    {
        $user = array(
            "id" => $this->curUser->id,
            "uuid" => $this->curUser->uuid,
            "email" => $this->curUser->email,
            "first_name" => $this->curUser->first_name,
            "last_name" => $this->curUser->last_name,
            "image" => $this->getAvatar($this->curUser->image)
        );

        return response()->json( array("success"=>true,"data"=>$user) );
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function permission()
    {
        $access_info = (new User())->get_access_info( $this->curUser->id );
        $access_info->permissions = unserialize($access_info->permissions);
        $teams = (new Team())->get_teams($this->curUser->id);
        $access_info->has_team = count($teams)>0?true:false;
        return response()->json( array("success"=>true,"data"=>$access_info) );
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        auth()->refresh();
        $last_online = (new User())->refreshStatus( $this->curUser->id );
        return response()->json( array("success"=>true,"data"=>auth()->refresh(),"last_online"=>$last_online) );
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        \Cookie::forget('erpat-pas-jwt');
        return redirect('signin');
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function getUser(Request $request) {
        $validator = Validator::make($request->all(), [
            'current'=>'required',
        ]);

        if ($validator->fails()) {
            return response()->json(array("success"=>false,"message"=>"Web request invalid!"));
        }
        $user_id = $request->input('current');

        if(!$user = (new User())->get_by_id($user_id)) {
            return response()->json( array(
                "success" => false,
                "message" => "User not found!"
            ) );
        }

        $user->image = $this->getAvatar($user->image);

        return response()->json( array(
            "success" => true,
            "data" => $user
        ) );
    }
}

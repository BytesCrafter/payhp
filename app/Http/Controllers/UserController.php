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

    public function index() {
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

        //TODO: Check if has access on PAS

        //VERIFY PASSWORD
        $hash = $user->password;
        if ((strlen($hash) === 60 && password_verify($password, $hash)) || $hash === md5($password)) {

            if($token = $this->getToken($username, $password)) {
                \Log::info($token);
                $cookie = cookie('jwt', $token, 60 * 1); //1 hour
                return response()->withCookie($cookie);
            }
            \Log::info($token);
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
        return response()->json( array("success"=>true,"message"=>"Successfully logged out.") );
    }

    public function change_pass(Request $request) {

        $validator = Validator::make($request->all(), [
            'oldpass'=>'required',
            'newpass'=>'required',
            'confirmpass'=>'required'
        ]);

        if ($validator->fails()) {
            return response()->json(array("success"=>false,"message"=>"Web request invalid!"));
        }

        $oldpass = $request->input('oldpass');
        $newpass = $request->input('newpass');
        $confirmpass = $request->input('confirmpass');

        //CONFIRM PASSWORD
        if($newpass !== $confirmpass) {
            return response()->json(array("success"=>false,"message"=>"New and Confirm password does not match."));
        }

        //VERIFY PASSWORD
        $hash = $this->curUser->password;
        if ((strlen($hash) === 60 && password_verify($oldpass, $hash)) || $hash === md5($oldpass)) {

            if($query = (new User())->changePassword($this->curUser->id, $newpass)) {
                return response()->json(array("success"=>true,"message"=>"You've successfully changed your password!"));
            }
        }

        return response()->json(array("success"=>false,"message"=>"Old password provided is incorrect!"));
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

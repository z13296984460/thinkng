<?php


namespace app\api\controller;



use think\facade\Db;
use think\facade\Request;

class Base
{
    protected $ip = '';
    protected $uid = 0;
    protected $param = [];

    public function _initialize(Request $request){
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST,GET');
        header('Access-Control-Allow-Headers:Authorization');
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With,X-PINGOTHER,Content-Type');
        $data = $_SERVER['HTTP_AUTHORIZATION'];
        $this->ip = $this->getIp();
        $action = Request::action();
        if($request->param()) {
            foreach ($request->param() as $k => $v) {
                if (!preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u", $v)) {
                    echo json_encode(['code' => 1100, 'msg' => '非法请求'], 320);
                    exit;
                }
                $this->param[$k] = $v;
            }
        }
        if((empty($data) || $data=='null') && ($action != 'login' && $action != 'register')){
            echo json_encode(['code'=>1001,'msg'=>'TOKEN失效，请重新登录1'],320);exit;
        }else if($action != 'login' && $action != 'register'){
            if(!$this->checklogin($data)){
                echo json_encode(['code'=>1001,'msg'=>'TOKEN失效，请重新登录2'],320);exit;
            }
        }

    }

    public function checklogin($token){
        $user = Db::name('user')->where(['token'=>$token])->find()->toArray();
        if(empty($user)){
            return false;
        }
        return $this->checktoken($user);
    }

    public function checktoken($user){
        if($user['status']!=1){
            return false;
        }
        $newtoken = base64_encode(md5($user['status'].$user['password'].$user['username'].$this->ip));
        if($newtoken!=$user['token']){
            return false;
        }
        return $newtoken;
    }

    public function getIp(){
        $remote_addr = $_SERVER['REMOTE_ADDR'];
        $clientIP = false;
        if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)){
            $clientIP = $_SERVER['HTTP_CLIENT_IP'];
        }
        $checkForProxy = false;
        if (array_key_exists('HTTP_X_FORWARDED_FOR',$_SERVER)){
            $checkForProxy = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        if(!empty($clientIP)) {
            $ip_address = $clientIP;
        }
        else if(!empty($checkForProxy)) {
            $ip_address = $checkForProxy;
        }
        else {
            $ip_address = $remote_addr;
        }

        return $ip_address;
    }

}
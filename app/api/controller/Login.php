<?php


namespace app\api\controller;


use think\facade\Db;
use think\facade\Request;

class Login extends Base
{

    public function login(Request $request) {
        $username = $this->param->username;
        $passwd = $this->param->passwd;
        if(!$username || !$passwd){
            return json(['code'=>401,'msg'=>'账号密码不能为空']);
        }

        $user = M('user')->where(['username'=>$username,'password'=>md5($passwd)])->find()->toArray();;
        if(empty($user)){
            return json(['code'=>404,'msg'=>'账号密码错误或账号不存在']);
        }
        if($user['status']!=1){
            return json(['code'=>403,'msg'=>'账号异常']);
        }
        $time = time();
        $user['last_time'] = $time;
        $user['last_ip'] = $this->ip;
        $user['token'] = $this->checktoken($user);
        // 启动事务
        Db::startTrans();
        try {
            Db::name('user')->find(1);
            Db::table('user')->delete(1);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }
    }

}
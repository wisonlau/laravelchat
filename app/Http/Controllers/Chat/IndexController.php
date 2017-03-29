<?php
namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Service\GatewayClient\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class IndexController extends Controller{

    /**
     * chat页面
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @author:wisonLau
     */
    public function index(){
        return view('chat.index');
    }

    /**
     * uid绑定代码
     * @author:wisonLau
     */
    public function bind(Request $request){
        $client_id = $request->input('client_id');
        $user = $request->input('user');
        $avar = $request->input('avar');
        Gateway::$registerAddress = '127.0.0.1:1238';

        Redis::hmset($client_id,['uid'=>md5(uniqid()), 'user'=>$user, 'avar'=>$avar]);
        $client = Redis::hgetall($client_id);
        $uid = $client['uid'];
        // client_id与uid绑定
        Gateway::bindUid($client_id, $uid);

        //获取所有用户的Sessions
        $all_client = Gateway::getAllClientSessions();
        foreach($all_client as $k=>$v){
            $all_client_info[$k] = Redis::hgetall($k);
        }

        $count = Gateway::getAllClientCount();//在线人数
        $group_id = rand(1, 9);
        Gateway::joinGroup($client_id, $group_id);//加入群组
        Gateway::sendToAll( json_encode(array('type'=>1, 'user'=>$user, 'avar'=>$avar, 'msg'=>'上线', 'count'=>$count, 'all_client_info'=>$all_client_info)) );
        return json_encode( array('type'=>1, 'uid'=>$uid, 'user'=>$user, 'avar'=>$avar, 'msg'=>'绑定成功', 'group'=>$group_id) );
    }

}

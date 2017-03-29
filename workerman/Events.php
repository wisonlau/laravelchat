<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;
use \workerman\Lib\Timer;
/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id) {
        // 向当前client_id发送数据
        Gateway::sendToClient($client_id, json_encode(array('type'=>1, 'client_id'=>$client_id, 'message'=>'成功连接')) );
    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message) {
        // 向所有人发送 
        Gateway::sendToAll( json_encode(array('type'=>2, 'client_id'=>$client_id, 'message'=>$message)) );
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id) {
       // 向所有人发送
       $count = Gateway::getAllClientCount();//在线人数
       $redis = new Redis();
       $redis->connect('127.0.0.1', 6379);
       $client = $redis->hgetall($client_id);
       $redis->del($client_id);
       Gateway::sendToAll( json_encode(array('type'=>3, 'client_id'=>$client_id, 'uid'=>$client['uid'],'user'=>$client['user'], 'message'=>'退出连接', 'count'=>$count)) );
   }

    /**
     * 进程启动时设置个定时器
     */
    public static function onWorkerStart()
    {
        Timer::add(1, function(){
            Gateway::sendToAll( json_encode(array('type'=>4, 'message'=>'当前时间:'.date('H:i:s',time()))) );
        });
    }

}

# laravel+GatewayWorker实现简单的聊天室

项目的依赖：
    php版本大于 5.4，安装redis，浏览器支持 websocket和localstorage。

### 启动
以debug（调试）方式启动
php start.php start

以daemon（守护进程）方式启动
php start.php start -d

### 停止
php start.php stop

### 重启
php start.php restart

### 平滑重启
php start.php reload

### 查看状态
php start.php status

浏览器打开 domain/chat，打开多个浏览器即可实现群聊

![](https://github.com/wisonlau/laravelchat/tree/master/pic/E1E2ECC4-E106-4C58-A6B6-31C39E99BE98.png)

![](https://github.com/wisonlau/laravelchat/tree/master/pic/2D53C40D-1279-4208-A000-B0BC07915E79.png)

![](https://github.com/wisonlau/laravelchat/tree/master/pic/B782CB6A-D51D-44D3-82F3-394878DC0281.png)

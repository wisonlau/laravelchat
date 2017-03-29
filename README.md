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

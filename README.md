# Spider

#### 让php在命令行进行采集任务。

```
# 命令行直接执行
cd /www/wwwroot/Spider/class/run
php curlahyx.php
# 会调用/www/wwwroot/Spider/class/curlahyxClass.php文件
# 需要注意的是：
# curlahyx.php文件，本地运行需要注释：
# chdir('/www/wwwroot/spider/run');
# 服务器运行需要修改为你的目录

# 也可以结合SpiderInfo，通过web页面控制采集。详见[SpiderInfo](https://github.com/kangly/SpiderInfo)
```

# Spider
### 让php在命令行进行采集任务。

#### 命令行直接执行
```
cd /www/wwwroot/Spider/run
php curlahyx.php
# 会调用/www/wwwroot/Spider/class/curlahyxClass.php文件
# 需要注意的是：
# curlahyx.php文件，本地运行需要注释：
# chdir('/www/wwwroot/spider/run');
# 服务器运行需要修改为你的目录
```

#### 也可以结合SpiderInfo，通过web页面控制采集。详见[SpiderInfo](https://github.com/kangly/SpiderInfo/)

#### 添加新任务
```
/www/wwwroot/Spider/run 下创建执行文件，参考curlahyx.php
/www/wwwroot/Spider/class 下创建调用文件，参考curlahyxClass.php
```

#### 引入的扩展
> * [Medoo](https://github.com/catfan/Medoo)
> * [QueryList](https://github.com/jae-jae/QueryList)
> * [monolog](https://github.com/Seldaek/monolog)

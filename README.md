php爬虫系统
====

* 程序只支持CLI

# 安装程序

### 1.dbconfig.php 添加正确的数据库配置

### 2. 安装程序 
```PHP
php run install
```
# 启动程序

### 安装完成后，在setting表添加来源站点及正则提取规则。

### 启动程序 (参数是 setting 表id)
```PHP
php run run 1 
```

### 清除项目数据 
```PHP
php run clear
 ```

### 完整代码目录
```PHP
│  crawl.sql
│  LICENSE
│  README
│  run       系统入口程序
│      
├─code
│  ├─config  配置文件
│  │      config.php
│  │      dbconfig.php
│  │      
│  └─library  类库
│      │  App.php
│      │  Crawl.php
│      │  CrawlCallback.php
│      │  Import.php
│      │  Loader.php
│      │  
│      └─db   数据库
│              MysqlPDO.php
│              
├─data   数据
│          
├─log    日志目录
│      
├─tmp
└─web     web目录
 ```

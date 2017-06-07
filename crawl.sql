CREATE DATABASE /*!32312 IF NOT EXISTS*/crawl /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `crawl`;


DROP TABLE IF EXISTS `data`;

CREATE TABLE `data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `data_id` char(32) NOT NULL DEFAULT '' COMMENT '数据id',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '站点id',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '源URL',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `data_detail`;

CREATE TABLE `data_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(11) unsigned NOT NULL DEFAULT '0',
  `data_id` char(32) DEFAULT '' COMMENT '数据id',
  `name` varchar(50) DEFAULT '' COMMENT '字段名称',
  `value` text COMMENT '字段内容',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `data_image`;

CREATE TABLE `data_image` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '站点id',
  `data_id` char(32) DEFAULT '0' COMMENT '数据id',
  `ext` varchar(10) NOT NULL DEFAULT '' COMMENT '图片格式',
  `page_url` varchar(255) DEFAULT '' COMMENT '页面URL',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '下载本地图片',
  `source_url` varchar(300) NOT NULL DEFAULT '' COMMENT '源网站图url',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1未处理，2处理',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='图片表';



DROP TABLE IF EXISTS `setting`;

CREATE TABLE `setting` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT COMMENT '站点id',
  `site` varchar(50) NOT NULL DEFAULT '' COMMENT '站点名称',
  `project` varchar(10) NOT NULL COMMENT '站点英文名称',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '站点列表页地址',
  `list_charset` enum('UTF-8','GB2312','GBK') NOT NULL DEFAULT 'UTF-8' COMMENT '列表页编码',
  `content_charset` enum('UTF-8','GB2312','GBK') NOT NULL DEFAULT 'UTF-8' COMMENT '内容页编码',
  `item_rule_li` varchar(200) NOT NULL DEFAULT '' COMMENT '列表项Li选择规则',
  `item_rule_a` varchar(200) NOT NULL DEFAULT '' COMMENT '列表项A标签选择规则',
  `cur_page` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '当前已采集页数',
  `total_page` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '需要采集总页数',
  `filter_rule` text NOT NULL COMMENT '内容过滤规则',
  `server_count` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '服务器数量',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `img_dir` varchar(20) NOT NULL DEFAULT '' COMMENT '图片目录',
  `img_url` varchar(100) NOT NULL DEFAULT '' COMMENT '图片域名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='采集站点配置表';


insert  into `setting`(`id`,`site`,`project`,`url`,`list_charset`,`content_charset`,`item_rule_li`,`item_rule_a`,`cur_page`,`total_page`,`filter_rule`,`server_count`,`create_time`,`img_dir`,`img_url`) values (1,'网贷新闻','hangye','http://www.wdzj.com/news/hangye/p[PAGE_NUM].html','UTF-8','UTF-8','','#<a\\s+target\\=\\\"\\_blank\\\"\\s+href\\=\\\"(http\\:\\/\\/www\\.wdzj\\.com\\/news\\/hangye\\/\\w+\\.html)\\\"#iUs',20,20,'',1,'0000-00-00 00:00:00','upload','http://www.baidu.com');


DROP TABLE IF EXISTS `setting_content`;

CREATE TABLE `setting_content` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '站点id',
  `field` varchar(20) NOT NULL DEFAULT '' COMMENT '字段',
  `rule` varchar(200) NOT NULL DEFAULT '' COMMENT '匹配规则',
  `allowable_tags` varchar(50) NOT NULL DEFAULT '' COMMENT '保留标签，多个逗号分隔',
  `match_img` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否匹配图片1不未配，2匹配',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;



insert  into `setting_content`(`id`,`site_id`,`field`,`rule`,`allowable_tags`,`match_img`) values (1,2,'title','#<h1.*>(.*)</h1>#iUs','',1),(2,2,'content','#<div.*class=\"c-cen\">(.*)</div>#iUs','<p>,</span>,<img>',2),(3,2,'create_time','#<li\\s+class=\"n_time\">.+发布时间：(\\d{4}-\\d{2}-\\d{2}\\s+\\d{2}:\\d{2}:\\d{2}).+<\\/li>#s','',1),(4,2,'source','#<span>来源：(.+)\\s+.+</li>#isU','',1);


DROP TABLE IF EXISTS `url`;

CREATE TABLE `url` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '源网站id',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '源网页url',
  `filesize` int(11) DEFAULT '0' COMMENT '文件偏移量',
  `path` varchar(100) NOT NULL DEFAULT '' COMMENT '网页物理路径',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1未处理，2处理',
  `server` tinyint(3) unsigned DEFAULT '1' COMMENT '处理服务器编号',
  `type` tinyint(1) DEFAULT '1' COMMENT '1:list,2:content',
  PRIMARY KEY (`id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;



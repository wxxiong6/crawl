/*
SQLyog Ultimate v12.09 (64 bit)
MySQL - 5.5.47 : Database - crawl
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`crawl` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `crawl`;

/*Table structure for table `data` */

DROP TABLE IF EXISTS `data`;

CREATE TABLE `data` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `data_id` CHAR(32) NOT NULL DEFAULT '' COMMENT '数据id',
  `site_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '站点id',
  `url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '源URL',
  `title` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '标题',
  `create_time` DATETIME NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

/*Data for the table `data` */


/*Table structure for table `data_detail` */

DROP TABLE IF EXISTS `data_detail`;

CREATE TABLE `data_detail` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `data_id` CHAR(32) DEFAULT '' COMMENT '数据id',
  `name` VARCHAR(50) DEFAULT '' COMMENT '字段名称',
  `value` TEXT COMMENT '字段内容',
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8;

/*Data for the table `data_detail` */


/*Table structure for table `image` */

DROP TABLE IF EXISTS `data_image`;

CREATE TABLE `data_image` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '站点id',
  `data_id` CHAR(32) DEFAULT '0' COMMENT '数据id',
  `ext` VARCHAR(10) NOT NULL DEFAULT '' COMMENT '图片格式',
  `page_url` VARCHAR(255) DEFAULT '' COMMENT '页面URL',
  `url` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '下载本地图片',
  `source_url` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '源网站图url',
  `status` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态 1未处理，2处理',
  PRIMARY KEY (`id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='图片表';
;

/*Data for the table `image` */

/*Table structure for table `setting` */

DROP TABLE IF EXISTS `setting`;

CREATE TABLE `setting` (
  `id` TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '站点id',
  `site` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '站点名称',
  `project` VARCHAR(10) NOT NULL COMMENT '站点英文名称',
  `url` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '站点列表页地址',
  `list_charset` ENUM('UTF-8','GB2312','GBK') NOT NULL DEFAULT 'UTF-8' COMMENT '列表页编码',
  `content_charset` ENUM('UTF-8','GB2312','GBK') NOT NULL DEFAULT 'UTF-8' COMMENT '内容页编码',
  `item_rule_li` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '列表项Li选择规则',
  `item_rule_a` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '列表项A标签选择规则',
  `cur_page` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1' COMMENT '当前已采集页数',
  `total_page` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1' COMMENT '需要采集总页数',
  `filter_rule` TEXT NOT NULL COMMENT '内容过滤规则',
  `server_count` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '服务器数量',
  `create_time` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='采集站点配置表';

/*Data for the table `setting` */

INSERT  INTO `setting`(`id`,`site`,`project`,`url`,`list_charset`,`content_charset`,`item_rule_li`,`item_rule_a`,`cur_page`,`total_page`,`filter_rule`,`server_count`,`create_time`) VALUES (1,'CSDN-热门文章','csdn-hot-l','http://blog.csdn.net/hot.html?&page=[PAGE_NUM]','UTF-8','UTF-8','','#\\<a\\s+href=\\\"(http\\:\\/\\/blog\\.csdn\\.net/\\w+/article/details/\\d+)\\\"#iUs',20,20,'',1,'2016-08-30 16:06:49'),(2,'网贷新闻','hangye','http://www.wdzj.com/news/hangye/p[PAGE_NUM].html','UTF-8','UTF-8','','#<a\\s+target\\=\\\"\\_blank\\\"\\s+href\\=\\\"(http\\:\\/\\/www\\.wdzj\\.com\\/news\\/hangye\\/\\w+\\.html)\\\"#iUs',20,20,'',1,'0000-00-00 00:00:00');

/*Table structure for table `setting_content` */

DROP TABLE IF EXISTS `setting_content`;

CREATE TABLE `setting_content` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '站点id',
  `field` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '字段',
  `rule` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '匹配规则',
  `allowable_tags` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '保留标签，多个逗号分隔',
  `match_img` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否匹配图片1不未配，2匹配',
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Data for the table `setting_content` */

INSERT  INTO `setting_content`(`id`,`site_id`,`field`,`rule`,`allowable_tags`,`match_img`) VALUES (1,1,'title','#\\<span\\s+class\\=\\\"link_title\\\"\\>(.*)\\<\\/span\\>#iUs','',1),(2,1,'create_time','#\\<span\\s+class\\=\\\"link_postdate\\\"\\>(.*)\\<\\/span\\>#iUs','',1),(3,1,'view_num','#\\<span\\s+class\\=\\\"link_view\\\"\\s+title\\=\\\"阅读次数\\\"\\>(\\d+)人阅读\\<\\/span\\>#iUs','',1),(4,1,'content','#\\<div\\s+id\\=\\\"article_content\\\"\\s+class\\=\\\"article_content\\\"\\>(.*)\\<div\\s+class\\=\\\"bdsharebuttonbox#iUs','<p>,</span>,<img>',1),(5,2,'title','#\\<h1\\>(.*)\\<\\/h1\\>#iUs','',1),(6,2,'content','#\\<div\\s+class\\=\\\"con_news\\\"\\>(.*)\\<\\/div\\>#iUs','<p>,</span>,<img>',1);

/*Table structure for table `url` */

DROP TABLE IF EXISTS `url`;

CREATE TABLE `url` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_id` INT(11) UNSIGNED NOT NULL COMMENT '源网站id',
  `url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '源网页url',
  `filesize` INT(11) DEFAULT '0' COMMENT '文件偏移量',
  `path` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '网页物理路径',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '1未处理，2处理',
  `server` TINYINT(3) UNSIGNED DEFAULT '1' COMMENT '处理服务器编号',
  `type` TINYINT(1) DEFAULT '1' COMMENT '1:list,2:content',
  PRIMARY KEY (`id`)
) ENGINE=MEMORY AUTO_INCREMENT=517 DEFAULT CHARSET=utf8;

/*Data for the table `url` */


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

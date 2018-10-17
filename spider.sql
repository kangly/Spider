# ************************************************************
# Sequel Pro SQL dump
# Version 4499
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.16)
# Database: spider
# Generation Time: 2018-10-17 06:52:34 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table destoon_area
# ------------------------------------------------------------

DROP TABLE IF EXISTS `destoon_area`;

CREATE TABLE `destoon_area` (
  `areaid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `areaname` varchar(50) NOT NULL DEFAULT '',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0',
  `arrparentid` varchar(255) NOT NULL DEFAULT '',
  `child` tinyint(1) NOT NULL DEFAULT '0',
  `arrchildid` text NOT NULL,
  `listorder` smallint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`areaid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='地区';

LOCK TABLES `destoon_area` WRITE;
/*!40000 ALTER TABLE `destoon_area` DISABLE KEYS */;

INSERT INTO `destoon_area` (`areaid`, `areaname`, `parentid`, `arrparentid`, `child`, `arrchildid`, `listorder`)
VALUES
	(1,'北京',0,'0',0,'1',1),
	(2,'上海',0,'0',0,'2',2),
	(3,'天津',0,'0',0,'3',3),
	(4,'重庆',0,'0',0,'4',4),
	(5,'河北',0,'0',1,'5,35,36,37,38,39,40,41,42,43,44,45',5),
	(6,'山西',0,'0',1,'6,46,47,48,49,50,51,52,53,54,55,56',6),
	(7,'内蒙古',0,'0',1,'7,57,58,59,60,61,62,63,64,65,66,67,68',7),
	(8,'辽宁',0,'0',1,'8,69,70,71,72,73,74,75,76,77,78,79,80,81,82',8),
	(9,'吉林',0,'0',1,'9,83,84,85,86,87,88,89,90,91',9),
	(10,'黑龙江',0,'0',1,'10,92,93,94,95,96,97,98,99,100,101,102,103,104',10),
	(11,'江苏',0,'0',1,'11,105,106,107,108,109,110,111,112,113,114,115,116,117',11),
	(12,'浙江',0,'0',1,'12,118,119,120,121,122,123,124,125,126,127,128',12),
	(13,'安徽',0,'0',1,'13,129,130,131,132,133,134,135,136,137,138,139,140,141,142,143,144,145',13),
	(14,'福建',0,'0',1,'14,146,147,148,149,150,151,152,153,154',14),
	(15,'江西',0,'0',1,'15,155,156,157,158,159,160,161,162,163,164,165',15),
	(16,'山东',0,'0',1,'16,166,167,168,169,170,171,172,173,174,175,176,177,178,179,180,181,182',16),
	(17,'河南',0,'0',1,'17,183,184,185,186,187,188,189,190,191,192,193,194,195,196,197,198,199',17),
	(18,'湖北',0,'0',1,'18,200,201,202,203,204,205,206,207,208,209,210,211,212,213,214,215,216',18),
	(19,'湖南',0,'0',1,'19,217,218,219,220,221,222,223,224,225,226,227,228,229,230',19),
	(20,'广东',0,'0',1,'20,231,232,233,234,235,236,237,238,239,240,241,242,243,244,245,246,247,248,249,250,251',20),
	(21,'广西',0,'0',1,'21,252,253,254,255,256,257,258,259,260,261,262,263,264,265',21),
	(22,'海南',0,'0',1,'22,266,267,268,269,270,271,272,273,274,275,276,277,278,279,280,281,282,283',22),
	(23,'四川',0,'0',1,'23,284,285,286,287,288,289,290,291,292,293,294,295,296,297,298,299,300,301,302,303,304',23),
	(24,'贵州',0,'0',1,'24,305,306,307,308,309,310,311,312,313',24),
	(25,'云南',0,'0',1,'25,314,315,316,317,318,319,320,321,322,323,324,325,326,327,328,329',25),
	(26,'西藏',0,'0',1,'26,330,331,332,333,334,335,336',26),
	(27,'陕西',0,'0',1,'27,337,338,339,340,341,342,343,344,345,346',27),
	(28,'甘肃',0,'0',1,'28,347,348,349,350,351,352,353,354,355,356,357,358,359,360',28),
	(29,'青海',0,'0',1,'29,361,362,363,364,365,366,367,368',29),
	(30,'宁夏',0,'0',1,'30,369,370,371,372,373',30),
	(31,'新疆',0,'0',1,'31,374,375,376,377,378,379,380,381,382,383,384,385,386,387,388,389,390,391',31),
	(32,'台湾',0,'0',0,'32',32),
	(33,'香港',0,'0',0,'33',33),
	(34,'澳门',0,'0',0,'34',34),
	(35,'石家庄市',5,'0,5',0,'35',35),
	(36,'唐山市',5,'0,5',0,'36',36),
	(37,'秦皇岛市',5,'0,5',0,'37',37),
	(38,'邯郸市',5,'0,5',0,'38',38),
	(39,'邢台市',5,'0,5',0,'39',39),
	(40,'保定市',5,'0,5',0,'40',40),
	(41,'张家口市',5,'0,5',0,'41',41),
	(42,'承德市',5,'0,5',0,'42',42),
	(43,'沧州市',5,'0,5',0,'43',43),
	(44,'廊坊市',5,'0,5',0,'44',44),
	(45,'衡水市',5,'0,5',0,'45',45),
	(46,'太原市',6,'0,6',0,'46',46),
	(47,'大同市',6,'0,6',0,'47',47),
	(48,'阳泉市',6,'0,6',0,'48',48),
	(49,'长治市',6,'0,6',0,'49',49),
	(50,'晋城市',6,'0,6',0,'50',50),
	(51,'朔州市',6,'0,6',0,'51',51),
	(52,'晋中市',6,'0,6',0,'52',52),
	(53,'运城市',6,'0,6',0,'53',53),
	(54,'忻州市',6,'0,6',0,'54',54),
	(55,'临汾市',6,'0,6',0,'55',55),
	(56,'吕梁市',6,'0,6',0,'56',56),
	(57,'呼和浩特市',7,'0,7',0,'57',57),
	(58,'包头市',7,'0,7',0,'58',58),
	(59,'乌海市',7,'0,7',0,'59',59),
	(60,'赤峰市',7,'0,7',0,'60',60),
	(61,'通辽市',7,'0,7',0,'61',61),
	(62,'鄂尔多斯市',7,'0,7',0,'62',62),
	(63,'呼伦贝尔市',7,'0,7',0,'63',63),
	(64,'巴彦淖尔市',7,'0,7',0,'64',64),
	(65,'乌兰察布市',7,'0,7',0,'65',65),
	(66,'兴安盟',7,'0,7',0,'66',66),
	(67,'锡林郭勒盟',7,'0,7',0,'67',67),
	(68,'阿拉善盟',7,'0,7',0,'68',68),
	(69,'沈阳市',8,'0,8',0,'69',69),
	(70,'大连市',8,'0,8',0,'70',70),
	(71,'鞍山市',8,'0,8',0,'71',71),
	(72,'抚顺市',8,'0,8',0,'72',72),
	(73,'本溪市',8,'0,8',0,'73',73),
	(74,'丹东市',8,'0,8',0,'74',74),
	(75,'锦州市',8,'0,8',0,'75',75),
	(76,'营口市',8,'0,8',0,'76',76),
	(77,'阜新市',8,'0,8',0,'77',77),
	(78,'辽阳市',8,'0,8',0,'78',78),
	(79,'盘锦市',8,'0,8',0,'79',79),
	(80,'铁岭市',8,'0,8',0,'80',80),
	(81,'朝阳市',8,'0,8',0,'81',81),
	(82,'葫芦岛市',8,'0,8',0,'82',82),
	(83,'长春市',9,'0,9',0,'83',83),
	(84,'吉林市',9,'0,9',0,'84',84),
	(85,'四平市',9,'0,9',0,'85',85),
	(86,'辽源市',9,'0,9',0,'86',86),
	(87,'通化市',9,'0,9',0,'87',87),
	(88,'白山市',9,'0,9',0,'88',88),
	(89,'松原市',9,'0,9',0,'89',89),
	(90,'白城市',9,'0,9',0,'90',90),
	(91,'延边朝鲜族自治州',9,'0,9',0,'91',91),
	(92,'哈尔滨市',10,'0,10',0,'92',92),
	(93,'齐齐哈尔市',10,'0,10',0,'93',93),
	(94,'鸡西市',10,'0,10',0,'94',94),
	(95,'鹤岗市',10,'0,10',0,'95',95),
	(96,'双鸭山市',10,'0,10',0,'96',96),
	(97,'大庆市',10,'0,10',0,'97',97),
	(98,'伊春市',10,'0,10',0,'98',98),
	(99,'佳木斯市',10,'0,10',0,'99',99),
	(100,'七台河市',10,'0,10',0,'100',100),
	(101,'牡丹江市',10,'0,10',0,'101',101),
	(102,'黑河市',10,'0,10',0,'102',102),
	(103,'绥化市',10,'0,10',0,'103',103),
	(104,'大兴安岭地区',10,'0,10',0,'104',104),
	(105,'南京市',11,'0,11',0,'105',105),
	(106,'无锡市',11,'0,11',0,'106',106),
	(107,'徐州市',11,'0,11',0,'107',107),
	(108,'常州市',11,'0,11',0,'108',108),
	(109,'苏州市',11,'0,11',0,'109',109),
	(110,'南通市',11,'0,11',0,'110',110),
	(111,'连云港市',11,'0,11',0,'111',111),
	(112,'淮安市',11,'0,11',0,'112',112),
	(113,'盐城市',11,'0,11',0,'113',113),
	(114,'扬州市',11,'0,11',0,'114',114),
	(115,'镇江市',11,'0,11',0,'115',115),
	(116,'泰州市',11,'0,11',0,'116',116),
	(117,'宿迁市',11,'0,11',0,'117',117),
	(118,'杭州市',12,'0,12',0,'118',118),
	(119,'宁波市',12,'0,12',0,'119',119),
	(120,'温州市',12,'0,12',0,'120',120),
	(121,'嘉兴市',12,'0,12',0,'121',121),
	(122,'湖州市',12,'0,12',0,'122',122),
	(123,'绍兴市',12,'0,12',0,'123',123),
	(124,'金华市',12,'0,12',0,'124',124),
	(125,'衢州市',12,'0,12',0,'125',125),
	(126,'舟山市',12,'0,12',0,'126',126),
	(127,'台州市',12,'0,12',0,'127',127),
	(128,'丽水市',12,'0,12',0,'128',128),
	(129,'合肥市',13,'0,13',0,'129',129),
	(130,'芜湖市',13,'0,13',0,'130',130),
	(131,'蚌埠市',13,'0,13',0,'131',131),
	(132,'淮南市',13,'0,13',0,'132',132),
	(133,'马鞍山市',13,'0,13',0,'133',133),
	(134,'淮北市',13,'0,13',0,'134',134),
	(135,'铜陵市',13,'0,13',0,'135',135),
	(136,'安庆市',13,'0,13',0,'136',136),
	(137,'黄山市',13,'0,13',0,'137',137),
	(138,'滁州市',13,'0,13',0,'138',138),
	(139,'阜阳市',13,'0,13',0,'139',139),
	(140,'宿州市',13,'0,13',0,'140',140),
	(141,'巢湖市',13,'0,13',0,'141',141),
	(142,'六安市',13,'0,13',0,'142',142),
	(143,'亳州市',13,'0,13',0,'143',143),
	(144,'池州市',13,'0,13',0,'144',144),
	(145,'宣城市',13,'0,13',0,'145',145),
	(146,'福州市',14,'0,14',0,'146',146),
	(147,'厦门市',14,'0,14',0,'147',147),
	(148,'莆田市',14,'0,14',0,'148',148),
	(149,'三明市',14,'0,14',0,'149',149),
	(150,'泉州市',14,'0,14',0,'150',150),
	(151,'漳州市',14,'0,14',0,'151',151),
	(152,'南平市',14,'0,14',0,'152',152),
	(153,'龙岩市',14,'0,14',0,'153',153),
	(154,'宁德市',14,'0,14',0,'154',154),
	(155,'南昌市',15,'0,15',0,'155',155),
	(156,'景德镇市',15,'0,15',0,'156',156),
	(157,'萍乡市',15,'0,15',0,'157',157),
	(158,'九江市',15,'0,15',0,'158',158),
	(159,'新余市',15,'0,15',0,'159',159),
	(160,'鹰潭市',15,'0,15',0,'160',160),
	(161,'赣州市',15,'0,15',0,'161',161),
	(162,'吉安市',15,'0,15',0,'162',162),
	(163,'宜春市',15,'0,15',0,'163',163),
	(164,'抚州市',15,'0,15',0,'164',164),
	(165,'上饶市',15,'0,15',0,'165',165),
	(166,'济南市',16,'0,16',0,'166',166),
	(167,'青岛市',16,'0,16',0,'167',167),
	(168,'淄博市',16,'0,16',0,'168',168),
	(169,'枣庄市',16,'0,16',0,'169',169),
	(170,'东营市',16,'0,16',0,'170',170),
	(171,'烟台市',16,'0,16',0,'171',171),
	(172,'潍坊市',16,'0,16',0,'172',172),
	(173,'济宁市',16,'0,16',0,'173',173),
	(174,'泰安市',16,'0,16',0,'174',174),
	(175,'威海市',16,'0,16',0,'175',175),
	(176,'日照市',16,'0,16',0,'176',176),
	(177,'莱芜市',16,'0,16',0,'177',177),
	(178,'临沂市',16,'0,16',0,'178',178),
	(179,'德州市',16,'0,16',0,'179',179),
	(180,'聊城市',16,'0,16',0,'180',180),
	(181,'滨州市',16,'0,16',0,'181',181),
	(182,'荷泽市',16,'0,16',0,'182',182),
	(183,'郑州市',17,'0,17',0,'183',183),
	(184,'开封市',17,'0,17',0,'184',184),
	(185,'洛阳市',17,'0,17',0,'185',185),
	(186,'平顶山市',17,'0,17',0,'186',186),
	(187,'安阳市',17,'0,17',0,'187',187),
	(188,'鹤壁市',17,'0,17',0,'188',188),
	(189,'新乡市',17,'0,17',0,'189',189),
	(190,'焦作市',17,'0,17',0,'190',190),
	(191,'濮阳市',17,'0,17',0,'191',191),
	(192,'许昌市',17,'0,17',0,'192',192),
	(193,'漯河市',17,'0,17',0,'193',193),
	(194,'三门峡市',17,'0,17',0,'194',194),
	(195,'南阳市',17,'0,17',0,'195',195),
	(196,'商丘市',17,'0,17',0,'196',196),
	(197,'信阳市',17,'0,17',0,'197',197),
	(198,'周口市',17,'0,17',0,'198',198),
	(199,'驻马店市',17,'0,17',0,'199',199),
	(200,'武汉市',18,'0,18',0,'200',200),
	(201,'黄石市',18,'0,18',0,'201',201),
	(202,'十堰市',18,'0,18',0,'202',202),
	(203,'宜昌市',18,'0,18',0,'203',203),
	(204,'襄樊市',18,'0,18',0,'204',204),
	(205,'鄂州市',18,'0,18',0,'205',205),
	(206,'荆门市',18,'0,18',0,'206',206),
	(207,'孝感市',18,'0,18',0,'207',207),
	(208,'荆州市',18,'0,18',0,'208',208),
	(209,'黄冈市',18,'0,18',0,'209',209),
	(210,'咸宁市',18,'0,18',0,'210',210),
	(211,'随州市',18,'0,18',0,'211',211),
	(212,'恩施土家族苗族自治州',18,'0,18',0,'212',212),
	(213,'仙桃市',18,'0,18',0,'213',213),
	(214,'潜江市',18,'0,18',0,'214',214),
	(215,'天门市',18,'0,18',0,'215',215),
	(216,'神农架林区',18,'0,18',0,'216',216),
	(217,'长沙市',19,'0,19',0,'217',217),
	(218,'株洲市',19,'0,19',0,'218',218),
	(219,'湘潭市',19,'0,19',0,'219',219),
	(220,'衡阳市',19,'0,19',0,'220',220),
	(221,'邵阳市',19,'0,19',0,'221',221),
	(222,'岳阳市',19,'0,19',0,'222',222),
	(223,'常德市',19,'0,19',0,'223',223),
	(224,'张家界市',19,'0,19',0,'224',224),
	(225,'益阳市',19,'0,19',0,'225',225),
	(226,'郴州市',19,'0,19',0,'226',226),
	(227,'永州市',19,'0,19',0,'227',227),
	(228,'怀化市',19,'0,19',0,'228',228),
	(229,'娄底市',19,'0,19',0,'229',229),
	(230,'湘西土家族苗族自治州',19,'0,19',0,'230',230),
	(231,'广州市',20,'0,20',0,'231',231),
	(232,'韶关市',20,'0,20',0,'232',232),
	(233,'深圳市',20,'0,20',0,'233',233),
	(234,'珠海市',20,'0,20',0,'234',234),
	(235,'汕头市',20,'0,20',0,'235',235),
	(236,'佛山市',20,'0,20',0,'236',236),
	(237,'江门市',20,'0,20',0,'237',237),
	(238,'湛江市',20,'0,20',0,'238',238),
	(239,'茂名市',20,'0,20',0,'239',239),
	(240,'肇庆市',20,'0,20',0,'240',240),
	(241,'惠州市',20,'0,20',0,'241',241),
	(242,'梅州市',20,'0,20',0,'242',242),
	(243,'汕尾市',20,'0,20',0,'243',243),
	(244,'河源市',20,'0,20',0,'244',244),
	(245,'阳江市',20,'0,20',0,'245',245),
	(246,'清远市',20,'0,20',0,'246',246),
	(247,'东莞市',20,'0,20',0,'247',247),
	(248,'中山市',20,'0,20',0,'248',248),
	(249,'潮州市',20,'0,20',0,'249',249),
	(250,'揭阳市',20,'0,20',0,'250',250),
	(251,'云浮市',20,'0,20',0,'251',251),
	(252,'南宁市',21,'0,21',0,'252',252),
	(253,'柳州市',21,'0,21',0,'253',253),
	(254,'桂林市',21,'0,21',0,'254',254),
	(255,'梧州市',21,'0,21',0,'255',255),
	(256,'北海市',21,'0,21',0,'256',256),
	(257,'防城港市',21,'0,21',0,'257',257),
	(258,'钦州市',21,'0,21',0,'258',258),
	(259,'贵港市',21,'0,21',0,'259',259),
	(260,'玉林市',21,'0,21',0,'260',260),
	(261,'百色市',21,'0,21',0,'261',261),
	(262,'贺州市',21,'0,21',0,'262',262),
	(263,'河池市',21,'0,21',0,'263',263),
	(264,'来宾市',21,'0,21',0,'264',264),
	(265,'崇左市',21,'0,21',0,'265',265),
	(266,'海口市',22,'0,22',0,'266',266),
	(267,'三亚市',22,'0,22',0,'267',267),
	(268,'五指山市',22,'0,22',0,'268',268),
	(269,'琼海市',22,'0,22',0,'269',269),
	(270,'儋州市',22,'0,22',0,'270',270),
	(271,'文昌市',22,'0,22',0,'271',271),
	(272,'万宁市',22,'0,22',0,'272',272),
	(273,'东方市',22,'0,22',0,'273',273),
	(274,'定安县',22,'0,22',0,'274',274),
	(275,'屯昌县',22,'0,22',0,'275',275),
	(276,'澄迈县',22,'0,22',0,'276',276),
	(277,'临高县',22,'0,22',0,'277',277),
	(278,'白沙黎族自治县',22,'0,22',0,'278',278),
	(279,'昌江黎族自治县',22,'0,22',0,'279',279),
	(280,'乐东黎族自治县',22,'0,22',0,'280',280),
	(281,'陵水黎族自治县',22,'0,22',0,'281',281),
	(282,'保亭黎族苗族自治县',22,'0,22',0,'282',282),
	(283,'琼中黎族苗族自治县',22,'0,22',0,'283',283),
	(284,'成都市',23,'0,23',0,'284',284),
	(285,'自贡市',23,'0,23',0,'285',285),
	(286,'攀枝花市',23,'0,23',0,'286',286),
	(287,'泸州市',23,'0,23',0,'287',287),
	(288,'德阳市',23,'0,23',0,'288',288),
	(289,'绵阳市',23,'0,23',0,'289',289),
	(290,'广元市',23,'0,23',0,'290',290),
	(291,'遂宁市',23,'0,23',0,'291',291),
	(292,'内江市',23,'0,23',0,'292',292),
	(293,'乐山市',23,'0,23',0,'293',293),
	(294,'南充市',23,'0,23',0,'294',294),
	(295,'眉山市',23,'0,23',0,'295',295),
	(296,'宜宾市',23,'0,23',0,'296',296),
	(297,'广安市',23,'0,23',0,'297',297),
	(298,'达州市',23,'0,23',0,'298',298),
	(299,'雅安市',23,'0,23',0,'299',299),
	(300,'巴中市',23,'0,23',0,'300',300),
	(301,'资阳市',23,'0,23',0,'301',301),
	(302,'阿坝藏族羌族自治州',23,'0,23',0,'302',302),
	(303,'甘孜藏族自治州',23,'0,23',0,'303',303),
	(304,'凉山彝族自治州',23,'0,23',0,'304',304),
	(305,'贵阳市',24,'0,24',0,'305',305),
	(306,'六盘水市',24,'0,24',0,'306',306),
	(307,'遵义市',24,'0,24',0,'307',307),
	(308,'安顺市',24,'0,24',0,'308',308),
	(309,'铜仁地区',24,'0,24',0,'309',309),
	(310,'黔西南布依族苗族自治州',24,'0,24',0,'310',310),
	(311,'毕节地区',24,'0,24',0,'311',311),
	(312,'黔东南苗族侗族自治州',24,'0,24',0,'312',312),
	(313,'黔南布依族苗族自治州',24,'0,24',0,'313',313),
	(314,'昆明市',25,'0,25',0,'314',314),
	(315,'曲靖市',25,'0,25',0,'315',315),
	(316,'玉溪市',25,'0,25',0,'316',316),
	(317,'保山市',25,'0,25',0,'317',317),
	(318,'昭通市',25,'0,25',0,'318',318),
	(319,'丽江市',25,'0,25',0,'319',319),
	(320,'思茅市',25,'0,25',0,'320',320),
	(321,'临沧市',25,'0,25',0,'321',321),
	(322,'楚雄彝族自治州',25,'0,25',0,'322',322),
	(323,'红河哈尼族彝族自治州',25,'0,25',0,'323',323),
	(324,'文山壮族苗族自治州',25,'0,25',0,'324',324),
	(325,'西双版纳傣族自治州',25,'0,25',0,'325',325),
	(326,'大理白族自治州',25,'0,25',0,'326',326),
	(327,'德宏傣族景颇族自治州',25,'0,25',0,'327',327),
	(328,'怒江傈僳族自治州',25,'0,25',0,'328',328),
	(329,'迪庆藏族自治州',25,'0,25',0,'329',329),
	(330,'拉萨市',26,'0,26',0,'330',330),
	(331,'昌都地区',26,'0,26',0,'331',331),
	(332,'山南地区',26,'0,26',0,'332',332),
	(333,'日喀则地区',26,'0,26',0,'333',333),
	(334,'那曲地区',26,'0,26',0,'334',334),
	(335,'阿里地区',26,'0,26',0,'335',335),
	(336,'林芝地区',26,'0,26',0,'336',336),
	(337,'西安市',27,'0,27',0,'337',337),
	(338,'铜川市',27,'0,27',0,'338',338),
	(339,'宝鸡市',27,'0,27',0,'339',339),
	(340,'咸阳市',27,'0,27',0,'340',340),
	(341,'渭南市',27,'0,27',0,'341',341),
	(342,'延安市',27,'0,27',0,'342',342),
	(343,'汉中市',27,'0,27',0,'343',343),
	(344,'榆林市',27,'0,27',0,'344',344),
	(345,'安康市',27,'0,27',0,'345',345),
	(346,'商洛市',27,'0,27',0,'346',346),
	(347,'兰州市',28,'0,28',0,'347',347),
	(348,'嘉峪关市',28,'0,28',0,'348',348),
	(349,'金昌市',28,'0,28',0,'349',349),
	(350,'白银市',28,'0,28',0,'350',350),
	(351,'天水市',28,'0,28',0,'351',351),
	(352,'武威市',28,'0,28',0,'352',352),
	(353,'张掖市',28,'0,28',0,'353',353),
	(354,'平凉市',28,'0,28',0,'354',354),
	(355,'酒泉市',28,'0,28',0,'355',355),
	(356,'庆阳市',28,'0,28',0,'356',356),
	(357,'定西市',28,'0,28',0,'357',357),
	(358,'陇南市',28,'0,28',0,'358',358),
	(359,'临夏回族自治州',28,'0,28',0,'359',359),
	(360,'甘南藏族自治州',28,'0,28',0,'360',360),
	(361,'西宁市',29,'0,29',0,'361',361),
	(362,'海东地区',29,'0,29',0,'362',362),
	(363,'海北藏族自治州',29,'0,29',0,'363',363),
	(364,'黄南藏族自治州',29,'0,29',0,'364',364),
	(365,'海南藏族自治州',29,'0,29',0,'365',365),
	(366,'果洛藏族自治州',29,'0,29',0,'366',366),
	(367,'玉树藏族自治州',29,'0,29',0,'367',367),
	(368,'海西蒙古族藏族自治州',29,'0,29',0,'368',368),
	(369,'银川市',30,'0,30',0,'369',369),
	(370,'石嘴山市',30,'0,30',0,'370',370),
	(371,'吴忠市',30,'0,30',0,'371',371),
	(372,'固原市',30,'0,30',0,'372',372),
	(373,'中卫市',30,'0,30',0,'373',373),
	(374,'乌鲁木齐市',31,'0,31',0,'374',374),
	(375,'克拉玛依市',31,'0,31',0,'375',375),
	(376,'吐鲁番地区',31,'0,31',0,'376',376),
	(377,'哈密地区',31,'0,31',0,'377',377),
	(378,'昌吉回族自治州',31,'0,31',0,'378',378),
	(379,'博尔塔拉蒙古自治州',31,'0,31',0,'379',379),
	(380,'巴音郭楞蒙古自治州',31,'0,31',0,'380',380),
	(381,'阿克苏地区',31,'0,31',0,'381',381),
	(382,'克孜勒苏柯尔克孜自治州',31,'0,31',0,'382',382),
	(383,'喀什地区',31,'0,31',0,'383',383),
	(384,'和田地区',31,'0,31',0,'384',384),
	(385,'伊犁哈萨克自治州',31,'0,31',0,'385',385),
	(386,'塔城地区',31,'0,31',0,'386',386),
	(387,'阿勒泰地区',31,'0,31',0,'387',387),
	(388,'石河子市',31,'0,31',0,'388',388),
	(389,'阿拉尔市',31,'0,31',0,'389',389),
	(390,'图木舒克市',31,'0,31',0,'390',390),
	(391,'五家渠市',31,'0,31',0,'391',391);

/*!40000 ALTER TABLE `destoon_area` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table destoon_auth_group
# ------------------------------------------------------------

DROP TABLE IF EXISTS `destoon_auth_group`;

CREATE TABLE `destoon_auth_group` (
  `id` mediumint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户组表id',
  `title` char(100) NOT NULL DEFAULT '' COMMENT '用户组名称',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：为1正常，为0禁用',
  `rules` text COMMENT '用户组拥有的规则id，多个规则","隔开',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `destoon_auth_group` WRITE;
/*!40000 ALTER TABLE `destoon_auth_group` DISABLE KEYS */;

INSERT INTO `destoon_auth_group` (`id`, `title`, `status`, `rules`)
VALUES
	(1,'超级管理员',1,'1,27,28,29,30,31,40,41,2,3,8,9,10,11,4,12,13,14,15,16,17,18,19,5,20,21,22,23,24,25,26,6,46,47,48,49,7,32,33,34,35,36,37');

/*!40000 ALTER TABLE `destoon_auth_group` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table destoon_auth_group_access
# ------------------------------------------------------------

DROP TABLE IF EXISTS `destoon_auth_group_access`;

CREATE TABLE `destoon_auth_group_access` (
  `uid` mediumint(10) unsigned NOT NULL COMMENT '用户id',
  `group_id` mediumint(10) unsigned NOT NULL COMMENT '用户组id',
  KEY `idx_uid` (`uid`),
  KEY `idx_group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `destoon_auth_group_access` WRITE;
/*!40000 ALTER TABLE `destoon_auth_group_access` DISABLE KEYS */;

INSERT INTO `destoon_auth_group_access` (`uid`, `group_id`)
VALUES
	(1,1);

/*!40000 ALTER TABLE `destoon_auth_group_access` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table destoon_auth_rule
# ------------------------------------------------------------

DROP TABLE IF EXISTS `destoon_auth_rule`;

CREATE TABLE `destoon_auth_rule` (
  `id` mediumint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '规则表id',
  `pid` int(10) unsigned DEFAULT '0' COMMENT '父级id',
  `name` char(100) NOT NULL DEFAULT '' COMMENT '规则唯一标识',
  `title` char(30) NOT NULL DEFAULT '' COMMENT '规则名称',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'type字段，目前暂时理解为规则类型，例如，1为后台管理类型，2为前台用户类型',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：为1正常，为0禁用',
  `icon` varchar(20) DEFAULT NULL COMMENT '图标class名称',
  `order_num` smallint(4) DEFAULT '100' COMMENT '排序',
  `is_menu` tinyint(1) DEFAULT '0' COMMENT '是否菜单项',
  `condition` char(100) NOT NULL DEFAULT '' COMMENT '规则表达式，为空表示存在就验证，不为空表示按照条件验证',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `destoon_auth_rule` WRITE;
/*!40000 ALTER TABLE `destoon_auth_rule` DISABLE KEYS */;

INSERT INTO `destoon_auth_rule` (`id`, `pid`, `name`, `title`, `type`, `status`, `icon`, `order_num`, `is_menu`, `condition`)
VALUES
	(1,0,'home/index/index','控制面板',1,1,'icon-home',100,1,''),
	(2,0,'home/setting/index','系统管理',1,1,'icon-cog',110,1,''),
	(3,2,'home/setting/rule','权限管理',1,1,NULL,100,1,''),
	(4,2,'home/setting/group','用户组管理',1,1,NULL,100,1,''),
	(5,2,'home/setting/user','用户管理',1,1,'',100,1,''),
	(6,0,'home/collect/index','采集管理',1,1,'icon-book',120,1,''),
	(7,6,'home/collect/collect','采集信息',1,1,'',100,1,''),
	(8,3,'home/setting/rule_list','权限管理列表',1,1,'',100,0,''),
	(9,3,'home/setting/edit_rule','添加/编辑权限',1,1,'',100,0,''),
	(10,3,'home/setting/save_rule','保存权限',1,1,'',100,0,''),
	(11,3,'home/setting/delete_rule','删除权限',1,1,'',100,0,''),
	(12,4,'home/setting/group_list','用户组管理列表',1,1,'',100,0,''),
	(13,4,'home/setting/edit_group','添加/编辑用户组',1,1,'',100,0,''),
	(14,4,'home/setting/save_group','保存用户组',1,1,'',100,0,''),
	(15,4,'home/setting/delete_group','删除用户组',1,1,'',100,0,''),
	(16,4,'home/setting/add_group_user','添加用户组成员',1,1,'',100,0,''),
	(17,4,'home/setting/save_group_user','保存用户组成员',1,1,'',100,0,''),
	(18,4,'home/setting/set_group_access','设置用户组权限',1,1,'',100,0,''),
	(19,4,'home/setting/save_group_access','保存用户组权限',1,1,'',100,0,''),
	(20,5,'home/setting/user_list','用户管理列表',1,1,'',100,0,''),
	(21,5,'home/setting/edit_user','添加/编辑用户',1,1,'',100,0,''),
	(22,5,'home/setting/save_user','保存用户',1,1,'',100,0,''),
	(23,5,'home/setting/delete_user','删除用户',1,1,'',100,0,''),
	(24,5,'home/setting/set_user_role','设置角色',1,1,'',100,0,''),
	(25,5,'home/setting/save_user_role','保存角色',1,1,'',100,0,''),
	(26,5,'home/setting/set_user_status','设置用户状态',1,1,'',100,0,''),
	(27,1,'home/index/user_info','个人资料',1,1,'',100,0,''),
	(28,27,'home/index/edit_password','修改密码',1,1,'',100,0,''),
	(29,27,'home/index/save_password','保存密码',1,1,'',100,0,''),
	(30,27,'home/index/edit_nickname','修改昵称',1,1,'',100,0,''),
	(31,27,'home/index/save_nickname','保存昵称',1,1,'',100,0,''),
	(32,7,'home/collect/collect_list','采集信息列表',1,1,'',100,0,''),
	(40,0,'home/collect/load_key_data','DT省市联动',1,1,'',100,0,''),
	(46,6,'home/collect/task','采集任务',1,1,'',90,1,''),
	(47,46,'home/collect/task_list','采集任务列表',1,1,'',100,0,''),
	(48,46,'home/collect/start_task','开始任务',1,1,'',100,0,''),
	(49,46,'home/collect/end_task','结束任务',1,1,'',100,0,'');

/*!40000 ALTER TABLE `destoon_auth_rule` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table destoon_collect_data
# ------------------------------------------------------------

DROP TABLE IF EXISTS `destoon_collect_data`;

CREATE TABLE `destoon_collect_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '采集数据表id',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父id，创建时自动写入，对应采集网站',
  `title` varchar(200) DEFAULT NULL COMMENT '标题',
  `contact` varchar(100) DEFAULT NULL COMMENT '联系人',
  `phone` varchar(100) DEFAULT NULL COMMENT '联系人电话',
  `phone_img` varchar(300) DEFAULT NULL COMMENT '联系人电话图片',
  `city` varchar(32) DEFAULT NULL COMMENT '市',
  `province_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '省id',
  `area_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '城市id',
  `content` mediumtext COMMENT '内容详情',
  `img_data` varchar(3000) DEFAULT NULL COMMENT 'json格式图片信息',
  `file_data` varchar(3000) DEFAULT NULL COMMENT 'json格式附件信息',
  `url` varchar(300) DEFAULT NULL COMMENT '采集源地址',
  `pub_date` date DEFAULT NULL COMMENT '发布日期',
  `is_change` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已转化',
  `module_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '转换对应模块，5，供应，6求购，26，资产处置',
  `item_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '转换对应itemid',
  `change_admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '转换操作人id',
  `change_admin_name` varchar(50) DEFAULT NULL COMMENT '转换操作人姓名',
  `pub_time` date DEFAULT NULL COMMENT '采集信息的发布时间',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `contact_address` varchar(100) DEFAULT NULL COMMENT '联系地址',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table destoon_task_info
# ------------------------------------------------------------

DROP TABLE IF EXISTS `destoon_task_info`;

CREATE TABLE `destoon_task_info` (
  `pid` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '任务对应的类型',
  `state` tinyint(1) DEFAULT '0' COMMENT '默认0未执行或执行完成，1执行中',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '任务名称',
  `file` varchar(50) NOT NULL DEFAULT '' COMMENT '任务文件名称',
  `is_enable` tinyint(1) DEFAULT '1' COMMENT '默认可以使用'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `destoon_task_info` WRITE;
/*!40000 ALTER TABLE `destoon_task_info` DISABLE KEYS */;

INSERT INTO `destoon_task_info` (`pid`, `state`, `title`, `file`, `is_enable`)
VALUES
	(1,0,'58同城','wuba',1),
	(2,0,'赶集网','ganji',1),
	(3,0,'峰峰信息港','fengfeng',1),
	(4,0,'胜芳大杂烩','sfdzh',1),
	(5,0,'全球金属网','ometal',1),
	(6,0,'梦溪论坛','mxlt',1),
	(7,0,'暨阳社区','jysq',1),
	(8,0,'宁海在线','nhzj',1),
	(9,0,'巩义搜','gysou',1),
	(10,0,'宝坻在线','baodi',1),
	(11,0,'白沟河网','baigou',1),
	(12,0,'永年论坛','ynian',1),
	(13,0,'处理网','chuli',1),
	(14,0,'临沂分类供求网','lywww',1),
	(16,0,'邳州论坛','pzzc',1),
	(17,0,'金利网','jinli',1),
	(18,0,'岳西在线','ahyx',1),
	(19,0,'博兴在线','bx',1),
	(20,0,'热处理论坛','rclbbs',1),
	(21,0,'慈溪网','zxip',1),
	(22,0,'绍兴E网','secondhand',1),
	(23,0,'阿里拍卖','taobao',1),
	(24,0,'中拍平台','paimai',1),
	(25,0,'京东拍卖','jd',1),
	(26,0,'e交易','ejy',1),
	(27,0,'北京产权交易','jinmajia',1),
	(28,0,'东莞市废料交易平台','recycle',1),
	(29,0,'重庆市加工贸易废料交易平台','cquae',1),
	(30,0,'中国拍卖行业协会','sf',1);

/*!40000 ALTER TABLE `destoon_task_info` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table destoon_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `destoon_user`;

CREATE TABLE `destoon_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '用户昵称',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '登录密码',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户状态 0：禁用； 1：正常',
  `register_time` datetime DEFAULT NULL COMMENT '注册时间',
  `last_login_ip` char(15) NOT NULL DEFAULT '' COMMENT '最后登录ip',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  PRIMARY KEY (`id`),
  KEY `user_login_key` (`username`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `destoon_user` WRITE;
/*!40000 ALTER TABLE `destoon_user` DISABLE KEYS */;

INSERT INTO `destoon_user` (`id`, `username`, `nickname`, `password`, `status`, `register_time`, `last_login_ip`, `last_login_time`)
VALUES
	(1,'admin','admin','e19d5cd5af0378da05f63f891c7467af',1,'2018-05-20 19:39:04','127.0.0.1','2018-10-17 11:08:24');

/*!40000 ALTER TABLE `destoon_user` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

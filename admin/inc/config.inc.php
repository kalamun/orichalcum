<?
/* (c) Kalamun.org - GNU/GPL 3 */

/*info*/
define("ADMIN_NAME","{VAR_ADMIN_NAME}");
define("ADMIN_MAIL","{VAR_ADMIN_MAIL}");
define("WEBMASTER_MAIL","{VAR_WEBMASTER_MAIL}");
define("SITE_URL","{VAR_SITE_URL}");
define("BASEDIR","{VAR_BASEDIR}");

/*database*/
global $__db;
$__db=array();
$__db['host']="{VAR_DB_HOST}";
$__db['user']="{VAR_DB_USER}";
$__db['password']="{VAR_DB_PASSWORD}";
$__db['name']="{VAR_DB_NAME}";
$__db['port']=""; //leave empty for default port
define("DEFAULT_LANG","{VAR_DEFAULT_LANG}"); //default language
define("SW_VERSION","2.70");

/*db tables*/
define("TABLE_USERS","k_users");
define("TABLE_USERS_PROP","k_users_prop");
define("TABLE_LINGUE","k_lingue");
define("TABLE_IMG","k_immagini");
define("TABLE_IMGALLERY","k_imgallery");
define("TABLE_DOCS","k_documenti");
define("TABLE_DOCGALLERY","k_docgallery");
define("TABLE_MEDIA","k_media");
define("TABLE_MEDIAGALLERY","k_media");
define("TABLE_MENU","k_menu");
define("TABLE_CATEGORIE","k_categorie");
define("TABLE_COMMENTI","k_commenti");
define("TABLE_CONFIG","k_config");
define("TABLE_DIZIONARIO","k_dizionario");
define("TABLE_STATISTICHE","k_statistiche");
define("TABLE_STATS_ARCHIVE","k_stats_archive");
define("TABLE_STATS_SUMMARY","k_stats_summary");
define("TABLE_IP2COUNTRY","k_ip2country");
define("TABLE_SHORTURL","k_shorturl");
define("TABLE_LOG","k_log");
define("TABLE_EMAIL_LOG","k_email_log");
define("TABLE_EMAIL_QUEUE","k_email_queue");
define("TABLE_METADATA","k_metadata");
define("TABLE_SHOP_ORDERS","k_shop_orders");
define("TABLE_SHOP_ITEMS","k_shop_items");
define("TABLE_SHOP_CUSTOMFIELDS","k_shop_customfields");
define("TABLE_SHOP_COUNTRIES","k_shop_countries");
define("TABLE_SHOP_PAYMENTS","k_shop_payments");
define("TABLE_SHOP_DELIVERERS","k_shop_deliverers");
define("TABLE_SHOP_DEL_PRICES","k_shop_del_prices");
define("TABLE_SHOP_TRANSACTIONS","k_shop_transactions");
define("TABLE_SHOP_VARIATIONS","k_shop_variations");
define("TABLE_SHOP_COUPONS","k_shop_coupons");
define("TABLE_SHOP_COUPONS_CODES","k_shop_coupons_codes");
define("TABLE_SHOP_MANUFACTURERS","k_shop_manufacturers");

define("TABLE_PAGINE","k_pagine");
define("TABLE_CONVERSIONS","k_conversions");
define("TABLE_NEWS","k_news");
define("TABLE_BANNER","k_banner");
define("TABLE_NEWSLETTER_LISTE","k_newsletter_liste");
define("TABLE_NEWSLETTER_ARCH","k_newsletter_arch");
define("TABLE_PHOTOGALLERY","k_photogallery");
define("TABLE_MEMBERS","k_members");
define("TABLE_PRIVATE","k_private");


/*upload dirs*/
define("DIR_TEMPLATE","template/");
define("DIR_IMG","arch/img/");
define("DIR_DOCS","arch/docs/");
define("DIR_MEDIA","arch/media/");
define("DIR_PRIVATE","arch/private/");
define("DIR_TMP","arch/tmp/");
//define("DEBUG","true");

?>
<?php
define( 'DEVELOPER_MODE', true ); // After starting your page, comment out this line
if( defined( 'DEVELOPER_MODE' ) ){
  error_reporting( E_ALL | E_STRICT );
}
unset( $config, $aMenuTypes, $aPhotoTypes, $lang, $sMessage, $aData );

/*
* If set to true, the logged on administrator will be able
* to see hidden pages (client-side)
*/
$config['hidden_shows'] = true;

/*
* Contains IP address from which logging in to admin panel is allowed
* Uncomment line below and type your IP address
*/
//$config['allowed_ip_admin_panel'] = 'TYPE-YOUR-IP-HERE';

/*
* Add time difference (in minutes) between your local time and server time
*/
$config['time_diff'] = 0;

/*
* Administrator's login and password
*/
$config['login'] = "admin";
$config['pass'] = "admin";

/*
* Client-side default language
*/
$config['default_lang'] = "en";

/*
* Admin panel language
*/
$config['admin_lang'] = "en";

/*
* Admin file name
*/
$config['admin_file'] = "admin.php";

/*
* CSS styles file
*/
$config['style'] = "style.css";

/*
* Directory of the current skin
*/
$config['skin'] = "default";

/*
* WYSIWYG editor
*/
$config['wysiwyg'] = true;

/*
* Inherit themes of pages from their parents
*/
$config['inherit_from_parents'] = false;

/*
* Product sorting option available on pages
*/
$config['sorting_products'] = true;

/*
* Customer recieves an email containing order details
*/
$config['send_customer_order_details'] = true;

/*
* Admin gets email containing order details from customer's address
*/
$config['order_details_from_customer'] = false;

/*
* If the server does not allow sending emails from addresses
* that are not configured on it, type value:
* 1 - default - emails will be sent from the email defined in the configuration or the customer's email (if $config['order_details_from_customer'] is set to true)
* 2 - emails will be sent from your default e-mail address configured on the server (not in the configuration script)
*/
$config['emails_from_header_option'] = 1;

/*
* Set default image size and location in page form
*/
$config['pages_default_image_size_details'] = 1;
$config['pages_default_image_size_list'] = 1;
$config['pages_default_image_location'] = 1;

/*
* Set default image size and location in product form
*/
$config['products_default_image_size_details'] = 0;
$config['products_default_image_size_list'] = 1;
$config['products_default_image_location'] = 1;

/*
* Set default subpages display mode
*/
$config['default_subpages_show'] = 2;

/*
* Set default menu
*/
$config['default_menu'] = 1;

/*
* If set to true language id will be added to url
*/
$config['language_in_url'] = false;

/*
* Language id separator in url
*/
$config['language_separator'] = '_';

/*
* Define maximum product quantity in basket
*/
$config['max_product_quantity'] = 100;

/*
* Define maximum product name's length in admin list
*/
$config['max_product_name_in_list'] = 80;

/*
* Save each page's full description to a separate file
*/
$config['pages_full_description_to_file'] = false;

/*
* Save each product's full description to a separate file
*/
$config['products_full_description_to_file'] = false;

define( 'LANGUAGE_IN_URL', $config['language_in_url'] );
define( 'LANGUAGE_SEPARATOR', $config['language_separator'] );

$config['display_expanded_menu'] = true;
$config['change_files_names'] = false;
$config['delete_unused_files'] = true;
$config['display_subcategory_products'] = true;
$config['remember_basket'] = true;

/*
* Define size of a preview image. If you don't want to display preview, set to null
*/
$config['image_preview_size'] = 250;

$config['images_sizes'] = array ( 0 => 75, 1 => 150, 2 => 180 );
$config['max_dimension_of_image'] = 900;
$config['admin_list'] = "25";
$config['max_textarea_chars'] = 4000;
$config['max_text_chars'] = 255;

/*
* Allowed extensions to upload on server
*/
$config['allowed_extensions'] = 'pdf|swf|doc|docx|txt|xls|ppt|rtf|odt|ods|odp|rar|zip|7z|bz2|tar|gz|tgz|arj|jpg|jpeg|gif|png|mp3';

$config['before_amp'] = !strstr( $_SERVER['REQUEST_URI'], '?' ) ? '?' : null;

/*
* Date formats
*/
$config['date_format_admin_default'] = 'Y-m-d H:i';
$config['date_format_admin_orders'] = 'Y-m-d H:i';
$config['date_format_customer_orders'] = 'Y-m-d H:i';

/*
* Should the current page name in the navigation path be a link?
* if not, set to: false
*/
$config['page_link_in_navigation_path'] = true;

/*
* Advanced elements visibility
* If you don't want them to be displayed, set to: false
*/
$config['display_advanced_options'] = true; // advanced options visibility
$config['display_thumbnail_2'] = false; // thumbnail 2 visibility

/*
* Directories
*/
$config['dir_core'] = 'core/';
$config['dir_database'] = 'database/';
$config['dir_database_pages'] = $config['dir_database'].'pages/';
$config['dir_database_products'] = $config['dir_database'].'products/';
$config['dir_libraries'] = $config['dir_core'].'libraries/';
$config['dir_lang'] = $config['dir_database'].'translations/';
$config['dir_templates'] = 'templates/';
$config['dir_skin'] = $config['dir_templates'].$config['skin'].'/';
$config['dir_files'] = 'files/';
$config['dir_plugins'] = 'plugins/';

require_once $config['dir_core'].'common.php';

$config['cookie_admin'] = defined( 'CUSTOMER_PAGE' ) ? null : 'A';

if( defined( 'CUSTOMER_PAGE' ) && !isset( $sLang ) && LANGUAGE_IN_URL === true )
  $sLang = getLanguageFromUrl( );

$config['change_language_to_polish'] = true;
if( !defined( 'CUSTOMER_PAGE' ) && !empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) && $config['change_language_to_polish'] === true && preg_match( '/pl-|pl,|^pl$/', $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ){
  if( isset( $_POST['admin_lang'] ) && strlen( $_POST['admin_lang'] ) == 2 ){
    setCookie( 'sAdminLanguage', $_POST['admin_lang'], time( ) + 86400 );
    $_COOKIE['sAdminLanguage'] = $_POST['admin_lang'];
  }
  if( $config['admin_lang']!='pl' && !isset( $_COOKIE['sAdminLanguage'] ) ){
    setCookie( 'sAdminLanguage', 'pl', time( ) + 86400 );
    $_COOKIE['sAdminLanguage'] = 'pl';
  }
  if( isset( $_COOKIE['sAdminLanguage'] ) )
    $config['admin_lang']= $_COOKIE['sAdminLanguage'];
}

if( isset( $sLang ) && is_file( $config['dir_lang'].$sLang.'.php' ) && strlen( $sLang ) == 2 ){
  setCookie( 'sLanguage'.$config['cookie_admin'], $sLang, time( ) + 86400 );
  define( 'LANGUAGE', $sLang );
}
else{
  if( !empty( $_COOKIE['sLanguage'.$config['cookie_admin']] ) && is_file( $config['dir_lang'].$_COOKIE['sLanguage'.$config['cookie_admin']].'.php' ) && strlen( $_COOKIE['sLanguage'.$config['cookie_admin']] ) == 2 )
    define( 'LANGUAGE', $_COOKIE['sLanguage'.$config['cookie_admin']] );
  else
    define( 'LANGUAGE', $config['default_lang'] );
}

require_once defined( 'CUSTOMER_PAGE' ) ? $config['dir_lang'].LANGUAGE.'.php' : ( is_file( $config['dir_lang'].$config['admin_lang'].'.php' ) ? $config['dir_lang'].$config['admin_lang'].'.php' : $config['dir_lang'].LANGUAGE.'.php' );

$aMenuTypes = Array( 0 => $lang['Menu_0'], 1 => $lang['Menu_1'], 2 => $lang['Menu_2'], 3 => $lang['Menu_3'] );
$aPhotoTypes = Array( 1 => $lang['Left'], 2 => $lang['Right'] );

$config['config'] = $config['dir_database'].'config/general.php';
$config['config_lang'] = $config['dir_database'].'config/lang_'.LANGUAGE.'.php';

$config_db['pages'] = $config['dir_database'].LANGUAGE.'_pages.php';
$config_db['pages_files'] = $config['dir_database'].LANGUAGE.'_pages_files.php';
$config_db['products'] = $config['dir_database'].LANGUAGE.'_products.php';
$config_db['products_pages'] = $config['dir_database'].LANGUAGE.'_products_pages.php';
$config_db['products_files'] = $config['dir_database'].LANGUAGE.'_products_files.php';
$config_db['payments_shipping'] = $config['dir_database'].LANGUAGE.'_payments_shipping.php';
$config_db['orders_temp'] = $config['dir_database'].'orders_temp.php';
$config_db['orders'] = $config['dir_database'].'orders.php';
$config_db['orders_products'] = $config['dir_database'].'orders_products.php';
$config_db['orders_ext'] = $config['dir_database'].'orders_ext.php';

$config['language'] = LANGUAGE;
$config['version'] = '6.7';

$config['last_login'] = "1572179509";
$config['before_last_login'] = "1459328913";

$config['manual_link'] = 'http://opensolution.org/Quick.Cart/docs/ext_6.6/?id='.( ( $config['admin_lang']=='pl' ) ? 'pl' : 'en' ).'-';

$config['default_pages_template'] = "page.php";
$config['default_products_template'] = "product.php";

define( 'DIR_CORE', $config['dir_core'] );
define( 'DIR_DATABASE', $config['dir_database'] );
define( 'DIR_DATABASE_PAGES', $config['dir_database_pages'] );
define( 'DIR_DATABASE_PRODUCTS', $config['dir_database_products'] );
define( 'DIR_FILES', $config['dir_files'] );
define( 'DIR_LIBRARIES', $config['dir_libraries'] );
define( 'DIR_PLUGINS', $config['dir_plugins'] );
define( 'DIR_LANG', $config['dir_lang'] );
define( 'DIR_TEMPLATES', $config['dir_templates'] );
define( 'DIR_SKIN', $config['dir_skin'] );

define( 'DB_PAGES', $config_db['pages'] );
define( 'DB_PAGES_FILES', $config_db['pages_files'] );
define( 'DB_PRODUCTS', $config_db['products'] );
define( 'DB_PRODUCTS_FILES', $config_db['products_files'] );
define( 'DB_PRODUCTS_PAGES', $config_db['products_pages'] );
define( 'DB_PAYMENTS_SHIPPING', $config_db['payments_shipping'] );
define( 'DB_ORDERS_TEMP', $config_db['orders_temp'] );
define( 'DB_ORDERS', $config_db['orders'] );
define( 'DB_ORDERS_PRODUCTS', $config_db['orders_products'] );
define( 'DB_ORDERS_EXT', $config_db['orders_ext'] );
define( 'DB_FAILED_LOGS', DIR_DATABASE.'logs.txt' );

define( 'DB_CONFIG', $config['config'] );
define( 'DB_CONFIG_LANG', $config['config_lang'] );

define( 'MAX_DIMENSION_OF_IMAGE', $config['max_dimension_of_image'] );
define( 'HIDDEN_SHOWS', $config['hidden_shows'] );
define( 'DISPLAY_EXPANDED_MENU', $config['display_expanded_menu'] );
define( 'WYSIWYG', $config['wysiwyg'] );
define( 'VERSION', $config['version'] );
define( 'TIME_DIFF', $config['time_diff'] );
define( 'SESSION_KEY_NAME', md5( dirname( $_SERVER['SCRIPT_FILENAME'] ) ) );
define( 'DISPLAY_SUBCATEGORY_PRODUCTS', $config['display_subcategory_products'] );

if( defined( 'DEVELOPER_MODE' ) ){
  $sValue = (float) phpversion( );
  if( $sValue < '5.2' )
    exit( '<h1>Required PHP version is <u>5.2.0</u>, your version is '.phpversion( ).'</h1>' );
  elseif( defined( 'ADMIN_PAGE' ) && ( !is_file( $config_db['pages'] ) || ( is_file( $config_db['pages'] ) && !is_writable( $config_db['pages'] ) ) ) ){
    exit( '<h1>File <u>'.$config_db['pages'].'</u> not exists or is not writable</h1>' );
  }
}
?>
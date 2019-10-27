<?php
/*
* Quick.Cart by OpenSolution.org
* www.OpenSolution.org
*/
extract( $_GET );
define( 'CUSTOMER_PAGE', true );

if( isset( $_POST['sPhrase'] ) ){
  header( 'Location: '.$_SERVER['REQUEST_URI'].'&sPhrase='.urlencode( $_POST['sPhrase'] ) );
  exit;
}

require 'database/config/general.php';
require DB_CONFIG_LANG;

session_start( );

header( 'Content-Type: text/html; charset='.$config['charset'] );
require_once DIR_LIBRARIES.'file-jobs.php';
require_once DIR_LIBRARIES.'flat-files.php';
require_once DIR_LIBRARIES.'trash.php';
require_once DIR_PLUGINS.'plugins.php';

require_once DIR_DATABASE.'_fields.php';
require_once DIR_CORE.'pages.php';
require_once DIR_CORE.'files.php';
require_once DIR_CORE.'products.php';
require_once DIR_CORE.'orders.php';

$sPhrase = isset( $sPhrase ) && !empty( $sPhrase ) ? trim( changeSpecialChars( htmlspecialchars( stripslashes( $sPhrase ) ) ) ) : null;

$aActions = getUrlFromGet( );
if( isset( $aActions['f'] ) && $aActions['f'] == 'pages' )
  $iContent = ( isset( $aActions['a'] ) && is_numeric( $aActions['a'] ) ) ? $aActions['a'] : $config['start_page'];
else
  $iContent = null;

$oFFS = FlatFilesSerialize::getInstance( );
$oPage = Pages::getInstance( );
$oOrder = new Orders( );
if( isset( $aActions['a'] ) && is_numeric( $aActions['a'] ) && $aActions['f'] == 'products' ){
  $iProduct = $aActions['a'];
  $oFile = Files::getInstance( $iProduct, true );
}
else{
  $oFile = Files::getInstance( $iContent );
}

$sTitle = null;
$sTheme = null;
$sDescription = $config['description'];

if( !isset( $_SESSION['iCustomer'.LANGUAGE] ) ){
  if( isset( $_COOKIE['sCustomer'.LANGUAGE] ) && !empty( $_COOKIE['sCustomer'.LANGUAGE] ) ){
    $iOrder = $oOrder->throwSavedOrderId( $_COOKIE['sCustomer'.LANGUAGE] );
    if( isset( $iOrder ) && is_numeric( $iOrder ) ){
      $_SESSION['iCustomer'.LANGUAGE] = $iOrder;
      $oOrder->generateBasket( );
    }
  }
  if( !isset( $_SESSION['iCustomer'.LANGUAGE] ) )
    $_SESSION['iCustomer'.LANGUAGE] = time( ).rand( 100, 999 );
}

$iOrderProducts = isset( $_SESSION['iOrderQuantity'.LANGUAGE] ) ? $_SESSION['iOrderQuantity'.LANGUAGE] : 0;

if( isset( $iContent ) && is_numeric( $iContent ) ){
  $aData = $oPage->throwPage( $iContent );
  if( isset( $aData ) ){
    
    if( !empty( $aData['sUrl'] ) ){
      header( 'Location: '.$aData['sUrl'] );
      exit;
    }

    if( !empty( $aData['sMetaDescription'] ) )
      $sDescription = $aData['sMetaDescription'];
    if( !empty( $aData['sTheme'] ) )
      $sTheme = $aData['sTheme'];
    else{
      if( $config['inherit_from_parents'] === true && !empty( $aData['iPageParent'] ) && !empty( $oPage->aPages[$aData['iPageParent']]['sTheme'] ) ){
        $sTheme = $oPage->aPages[$aData['iPageParent']]['sTheme'];
      }
    }
    if( empty( $aData['sDescriptionFull'] ) && !empty( $aData['sDescriptionShort'] ) )
      $aData['sDescriptionFull'] = $aData['sDescriptionShort'];

    $aData['sPagesTree'] = $oPage->throwPagesTree( $iContent );
    $sTitle = trim( !empty( $aData['sNameTitle'] ) ? $aData['sNameTitle'] : strip_tags( $aData['sName'] ) );
    if( !empty( $sTitle ) )
      $sTitle .= ' - ';

    if( isset( $aData['sDescriptionFull'] ) )
      $aData['sDescriptionFull'] = changeTxt( $aData['sDescriptionFull'], 'nlNds' );
  }
}
elseif( isset( $iProduct ) && is_numeric( $iProduct ) && $aActions['f'] == 'products' ){
  $oProduct = Products::getInstance( );
  $aData = $oProduct->throwProduct( $iProduct );

  if( isset( $aData ) ){
    $sTheme = !empty( $aData['sTheme'] ) ? $aData['sTheme'] : null;
    if( !empty( $aData['sMetaDescription'] ) )
      $sDescription = $aData['sMetaDescription'];
    if( empty( $aData['sDescriptionFull'] ) && !empty( $aData['sDescriptionShort'] ) )
      $aData['sDescriptionFull'] = $aData['sDescriptionShort'];

    if( isset( $aData['sDescriptionFull'] ) )
      $aData['sDescriptionFull'] = changeTxt( $aData['sDescriptionFull'], 'nlNds' );

    $aData['sPages'] = $oProduct->throwProductsPagesTree( $iProduct );
    $sTitle = trim( !empty( $aData['sNameTitle'] ) ? $aData['sNameTitle'] : strip_tags( $aData['sName'] ) );
    if( !empty( $sTitle ) )
      $sTitle .= ' - ';
  }    
}
elseif( isset( $aActions['f'] ) && isset( $aActions['a'] ) ){
  $sLink = $aActions['f'].'-'.$aActions['a'];
  // plugins actions
}

if( !isset( $aData['sName'] ) ){
  header( "HTTP/1.0 404 Not Found\r\n" );
  $sTitle = $lang['404_error'].' - ';
}

if( isset( $sTheme ) && is_file( DIR_SKIN.$sTheme ) ){
  require_once DIR_SKIN.$sTheme;
}
else{
  require_once DIR_SKIN.( ( isset( $iProduct ) && is_numeric( $iProduct ) && $aActions['f'] == 'products' ) ? $config['default_products_template'] : $config['default_pages_template'] );
}
?>
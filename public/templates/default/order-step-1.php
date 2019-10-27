<?php 
// More about design modifications - www.opensolution.org/Quick.Cart/docs/ext_6.6/?id=en-design
if( !defined( 'CUSTOMER_PAGE' ) )
  exit;

$config['this_is_basket_page'] = true;

if( isset( $aData['sName'] ) && !empty( $config['basket_page'] ) && $config['basket_page'] == $iContent ){ // basket actions
  // basket
  if( isset( $_POST['aProducts'] ) ){
    if( !isset( $aUrls ) )
      $aUrls = throwSiteUrls( );
    // save basket
    $oOrder->saveBasket( $_POST['aProducts'] );
    if( isset( $_POST['sRemember'] ) ){
      setCookie( 'sCustomer'.LANGUAGE, md5( $_SESSION['iCustomer'.LANGUAGE] ), time( ) + 259200 );
      $sMessage = '<div class="message" id="ok"><h2>'.$lang['Operation_completed'].'</h2></div>';
    }
    if( isset( $_POST['sContinueShopping'] ) ){
      $oOrder->generateBasket( );
      $iOrderProducts = isset( $_SESSION['iOrderQuantity'.LANGUAGE] ) ? $_SESSION['iOrderQuantity'.LANGUAGE] : 0;
      header( 'Location: '.( isset( $_SESSION['sLastProductsPageUrl'] ) ? $_SESSION['sLastProductsPageUrl'] : $aUrls['sHomeUrl'] ) );
      exit;
    }

    if( isset( $_POST['sCheckout'] ) && !empty( $config['order_page'] ) && isset( $oPage->aPages[$config['order_page']] ) ){
      header( 'Location: '.dirname( $aUrls['sUrl'] ).'/'.$oPage->aPages[$config['order_page']]['sLinkName'] );
      exit;
    }
  }

  if( isset( $iProductDelete ) && is_numeric( $iProductDelete ) ){
    // delete product from basket
    $oOrder->deleteFromBasket( $iProductDelete );
  }

  if( isset( $_POST['iProductAdd'] ) && isset( $_POST['iQuantity'] ) ){
    $iProductAdd = $_POST['iProductAdd'];
    $iQuantity = $_POST['iQuantity'];
  }
  if( isset( $iProductAdd ) && is_numeric( $iProductAdd ) && isset( $iQuantity ) && is_numeric( $iQuantity ) ){
    if( !isset( $oProduct ) )
      $oProduct = Products::getInstance( );

    if( isset( $oProduct->aProducts[$iProductAdd] ) && is_numeric( $oProduct->aProducts[$iProductAdd]['mPrice'] ) ){
      if( !isset( $aUrls ) )
        $aUrls = throwSiteUrls( );
      // add product to basket
      $oOrder->saveBasket( Array( $iProductAdd => $iQuantity ), true );
      header( 'Location: '.dirname( $aUrls['sUrl'] ).'/'.$aData['sLinkName'] );
      exit;
    }
  }

  $oOrder->generateBasket( );
  $iOrderProducts = isset( $_SESSION['iOrderQuantity'.LANGUAGE] ) ? $_SESSION['iOrderQuantity'.LANGUAGE] : 0;
}

require_once DIR_SKIN.'_header.php'; // include design of header
?>
<div id="page">
<?php
if( isset( $aData['sName'] ) ){ // displaying pages and subpages content
  echo '<h1>'.$aData['sName'].'</h1>'; // displaying page name

  if( isset( $aData['sDescriptionShort'] ) )
    echo '<div class="content">'.changeTxt( $aData['sDescriptionShort'], 'nlNds' ).'</div>'; // short description

  // display products in basket
  $sBasketList = $oOrder->listProducts( );
  if( !empty( $sBasketList ) ){
    if( isset( $sMessage ) )
      echo $sMessage;
    ?>
    <script src="<?php echo $config['dir_libraries']; ?>quick.form.js"></script>
    <div id="basket">
      <form method="post" action="">
        <fieldset id="orderedProducts">
          <legend><?php echo $aData['sName']; ?></legend>
          <table cellspacing="0">
            <thead>
              <tr>
                <td class="name">
                  <?php echo $lang['Name']; ?>
                </td>
                <td class="price">
                  <em><?php echo $lang['Price']; ?></em><span>[<?php echo $config['currency_symbol']; ?>]</span>
                </td>
                <td class="quantity">
                  <?php echo $lang['Quantity']; ?>
                </td>
                <td class="summary">
                  <em><?php echo $lang['Summary']; ?></em><span>[<?php echo $config['currency_symbol']; ?>]</span>
                </td>
                <td class="options">&nbsp;</td>
              </tr>
            </thead>
            <tfoot>
              <tr id="recount">
                <td colspan="2">&nbsp;</td>
                <td>
                  <input type="submit" value="<?php echo ucfirst( $lang['save'] ); ?>" class="submit" />
                </td>
                <td colspan="2">&nbsp;</td>
              </tr>
              <tr class="summaryProducts">
                <th colspan="3">
                  <?php echo $lang['Summary']; ?>
                </th>
                <td id="summary">
                  <?php echo displayPrice( $_SESSION['fOrderSummary'.LANGUAGE] ); ?>
                </td>
                <td>&nbsp;</td>
              </tr>
              <tr class="buttons">
                <td id="continue">
                  <input type="submit" name="sContinueShopping" value="<?php echo $lang['Continue_shopping']; ?>" class="submit" />
                </td>
                <td colspan="4" class="nextStep">
                  <input type="submit" name="sCheckout" value="<?php echo $lang['Checkout']; ?> &raquo;" class="submit" />
                </td>
              </tr>
            </tfoot>
            <tbody>
              <?php echo $sBasketList; // displaying products in basket ?>
            </tbody>
          </table>
          <?php if( isset( $config['remember_basket'] ) && $config['remember_basket'] === true ) { ?><div id="save"><input type="submit" name="sRemember" value="<?php echo $lang['Remember_basket']; ?>" class="submit" /></div><?php } ?>
        </fieldset>
      </form>
      <script>
        $(function(){
          $( "#basket form" ).quickform();
        });
      </script>
      <?php
      if( isset( $aData['sDescriptionFull'] ) )
        echo '<div class="content" id="pageDescription">'.$aData['sDescriptionFull'].'</div>'; // full description

      if( isset( $aData['sPages'] ) )
        echo '<div class="pages">'.$lang['Pages'].': <ul>'.$aData['sPages'].'</ul></div>'; // full description pagination
      ?>
    </div>
    <?php
  }
  else{
    echo '<div class="message" id="error"><h2>'.$lang['Basket_empty'].'</h2></div>';
  }
}
else{
  echo '<div class="message" id="error"><h2>'.$lang['Data_not_found'].'</h2></div>'; // displaying 404 error
}
?>
</div>
<?php
require_once DIR_SKIN.'_footer.php'; // include design of footer
?>

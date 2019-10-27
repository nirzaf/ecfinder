<?php 
// More about design modifications - www.opensolution.org/Quick.Cart/docs/ext_6.6/?id=en-design
if( !defined( 'CUSTOMER_PAGE' ) )
  exit;

$config['this_is_order_page'] = true;

require_once DIR_SKIN.'_header.php'; // include design of header
?>
<div id="page">
<?php
if( isset( $aData['sName'] ) ){ // displaying pages and subpages content
  echo '<h1>'.$aData['sName'].'</h1>'; // displaying page name

  // display order form
  if( $oOrder->checkEmptyBasket( ) === false && isset( $_POST['sOrderSend'] ) && $oOrder->checkFields( $_POST ) === true ){
    // save and print order
    $iOrder = $oOrder->addOrder( $_POST );

    if( !empty( $config['orders_email'] ) && checkEmail( $config['orders_email'] ) ){
      $oOrder->sendEmailWithOrderDetails( $iOrder );
    }

    $aOrder = $oOrder->throwOrder( $iOrder );
    $sOrderProducts = $oOrder->listProducts( $iOrder );
    ?>
    <div id="orderPrint">
      <?php
        if( isset( $aData['sDescriptionFull'] ) )
          echo '<div class="content" id="pageDescription">'.$aData['sDescriptionFull'].'</div>'; // full description

        if( isset( $aData['sPages'] ) )
          echo '<div class="pages">'.$lang['Pages'].': <ul>'.$aData['sPages'].'</ul></div>'; // full description pagination
      ?>
      <div class="legend"><?php echo $lang['Your_personal_data']; ?></div>
      <dl>
        <dt class="orderId">ID:</dt><dd class="orderId"><?php echo $aOrder['iOrder']; ?></dd>
        <dt class="firstAndLastName"><?php echo $lang['First_and_last_name']; ?>:</dt><dd class="firstAndLastName"><?php echo $aOrder['sFirstName'].' '.$aOrder['sLastName']; ?></dd>
        <dt class="company"><?php echo $lang['Company']; ?>:</dt><dd class="company"><?php if( isset( $aOrder['sCompanyName'] ) ) echo $aOrder['sCompanyName']; ?></dd>
        <dt class="street"><?php echo $lang['Street']; ?>:</dt><dd class="street"><?php echo $aOrder['sStreet']; ?></dd>
        <dt class="zipCode"><?php echo $lang['Zip_code']; ?>:</dt><dd class="zipCode"><?php echo $aOrder['sZipCode']; ?></dd>
        <dt class="city"><?php echo $lang['City']; ?>:</dt><dd class="city"><?php echo $aOrder['sCity']; ?></dd>
        <dt class="phone"><?php echo $lang['Telephone']; ?>:</dt><dd class="phone"><?php echo $aOrder['sPhone']; ?></dd>
        <dt class="email"><?php echo $lang['Email']; ?>:</dt><dd class="email"><?php echo $aOrder['sEmail']; ?></dd>
        <dt class="orderDate"><?php echo $lang['Date']; ?>:</dt><dd class="orderDate"><?php echo $aOrder['sDate']; ?></dd>
        <dt class="orderIP">IP:</dt><dd class="orderIP"><?php echo $aOrder['sIp']; ?></dd>
        <dt class="orderComment"><?php echo $lang['Comment']; ?>:</dt><dd class="orderComment"><?php if( isset( $aOrder['sComment'] ) ) echo str_replace( '|n|', '<br />', $aOrder['sComment'] ); ?></dd>
      </dl>
      <div class="legend"><?php echo $lang['Products']; ?></div>
      <div id="orderedProducts">
        <table cellspacing="0">
          <thead>
            <tr>
              <td class="name"><?php echo $lang['Name']; ?></td>
              <td class="price"><em><?php echo $lang['Price']; ?></em><span>[<?php echo $config['currency_symbol']; ?>]</span></td>
              <td class="quantity"><?php echo $lang['Quantity']; ?></td>
              <td class="summary"><em><?php echo $lang['Summary']; ?></em><span>[<?php echo $config['currency_symbol']; ?>]</span></td>
            </tr>
          </thead>
          <tfoot>
            <?php if( isset( $aOrder['iShipping'] ) ){ ?>
              <tr class="summaryProducts">
                <th colspan="3"><?php echo $lang['Summary']; ?></th>
                <td><?php echo $oOrder->aOrders[$iOrder]['sProductsSummary']; ?></td>
              </tr>
              <tr class="summaryShippingPayment">
                <th colspan="3"><?php echo $lang['Shipping_and_payment']; ?>: <strong><?php echo $aOrder['mShipping']; ?>, <?php echo $aOrder['mPayment']; ?></strong></th>
                <td id="shippingCost"><?php echo $oOrder->aOrders[$iOrder]['sPaymentShippingPrice']; ?></td>
              </tr>
            <?php } ?>
            <tr class="summaryOrder">
              <th colspan="3"><?php echo $lang['Summary_cost']; ?></th>
              <td id="orderSummary"><?php echo $oOrder->aOrders[$iOrder]['sOrderSummary']; ?></td>
            </tr>
          </tfoot>
          <tbody>
            <?php echo $sOrderProducts; // displaying products in basket ?>
          </tbody>
        </table>
      </div>
      <script type="text/javascript">
      AddOnload( delSavedUserData );
      </script>
    </div>
    <?php
  }
  else{
    echo '<div class="message" id="error"><h2>'.$lang['cf_no_word'].'<br /><a href="javascript:history.back();">&laquo; '.$lang['back'].'</a></h2></div>';
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

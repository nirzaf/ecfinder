<?php
if( !defined( 'ADMIN_PAGE' ) )
  exit( 'Script by OpenSolution.org' );

$oOrder = new OrdersAdmin( );

if( isset( $_POST['sName'] ) ){
  $iId = $oOrder->savePaymentShipping( $_POST, 2 );
  if( isset( $_POST['sOptionList'] ) )
    header( 'Location: '.$_SERVER['PHP_SELF'].'?p=shipping-list&sOption=save' );
  elseif( isset( $_POST['sOptionAddNew'] ) )
    header( 'Location: '.$_SERVER['PHP_SELF'].'?p=shipping-form&sOption=save' );
  else
    header( 'Location: '.$_SERVER['PHP_SELF'].'?p=shipping-form&sOption=save&iId='.$iId );
  exit;
}

if( isset( $iId ) && is_numeric( $iId ) ){
  $aData = $oOrder->throwPaymentShipping( $iId, 2 );
}

if( !isset( $aData['iId'] ) )
  $aData['iId'] = null;

$aSelectMenu['bOrders'] = true;
require_once DIR_TEMPLATES.'admin/_header.php'; // include headers
require_once DIR_TEMPLATES.'admin/_menu.php'; // include menu

if( isset( $sOption ) ){
  echo '<div id="msg">'.$lang['Operation_completed'].'</div>';
}

$sPayments = $oOrder->listShippingPaymentsForm( $aData['iId'] );

?>
<h1><?php echo ( isset( $_GET['iId'] ) && is_numeric( $_GET['iId'] ) ) ? $lang['Shipping_form'] : $lang['New_shipping']; ?><a href="<?php echo $config['manual_link']; ?>instruction#1.20" title="<?php echo $lang['Manual']; ?>" target="_blank"></a></h1>

<form action="?p=<?php echo $p; ?>" method="post" id="mainForm">
  <fieldset id="type2">
    <input type="hidden" name="iId" value="<?php echo $aData['iId']; ?>" />
    <input type="hidden" name="iType" value="2" />
    <table cellspacing="1" class="mainTable" id="shipping">
      <thead>
        <tr class="save">
          <th colspan="3">
            <input type="submit" value="<?php echo $lang['save']; ?> &raquo;" name="sOption" />
            <input type="submit" value="<?php echo $lang['save_add_new']; ?> &raquo;" name="sOptionAddNew" />
            <input type="submit" value="<?php echo $lang['save_list']; ?> &raquo;" name="sOptionList" />
          </th>
        </tr>
      </thead>
      <tfoot>
        <tr class="save">
          <th colspan="3">
            <input type="submit" value="<?php echo $lang['save']; ?> &raquo;" name="sOption" />
            <input type="submit" value="<?php echo $lang['save_add_new']; ?> &raquo;" name="sOptionAddNew" />
            <input type="submit" value="<?php echo $lang['save_list']; ?> &raquo;" name="sOptionList" />
          </th>
        </tr>
      </tfoot>
      <tbody>
        <!-- name start -->
        <tr class="l0">
          <th>
            <label for="sName"><?php echo $lang['Name']; ?> <span>(<?php echo $lang['required']; ?>)</span></label>
          </th>
          <td>
            <input type="text" name="sName" id="sName" value="<?php if( isset( $aData['sName'] ) ) echo $aData['sName']; ?>" size="30" maxlength="30" class="input" data-form-check="required" accesskey="1" tabindex="1" />
          </td>
          <td rowspan="4" class="tabs">
            <div id="tabs">
              <ul id="tabsNames">
                <!-- tabs start -->
                <li class="tabPayments"><a href="#more" onclick="displayTab( 'tabPayments' )"><?php echo $lang['Payment_methods']; ?></a></li>
                <!-- tabs end -->
              </ul>
              <div id="tabsForms">
                <!-- tabs list start -->
                <table class="tab" id="tabPayments">
                  <?php if( !empty( $sPayments ) ){ ?>
                  <thead>
                    <tr>
                      <td></td>
                      <td><?php $lang['Name']; ?></td>
                      <td><?php $lang['Price']; ?></td>
                    </tr>
                  </thead>
                  <tbody>
                    <?php echo $sPayments; ?>
                  </tbody>
                  <?php } ?>
                  <!-- tab payments -->
                </table>
                <!-- tabs list end -->
              </div>
            </div>
          </td>
        </tr>
        <!-- name end -->
        <!-- price start -->
        <tr class="l1">
          <th>
            <label for="fPrice"><?php echo $lang['Price']; ?> <span>(<?php echo $lang['required']; ?>)</span></label>
          </th>
          <td>
            <input type="text" name="fPrice" id="fPrice" value="<?php if( isset( $aData['fPrice'] ) ) echo $aData['fPrice']; ?>" class="inputr" size="10" data-form-check="float" tabindex="2" /> <?php echo $config['currency_symbol']; ?>
          </td>
        </tr>
        <!-- price end -->
        <tr class="l0">
          <th>
            <?php echo $lang['Status']; ?>
          </th>
          <td>
            <?php echo throwYesNoBox( 'iStatus', isset( $aData['iStatus'] ) ? $aData['iStatus'] : 1 ); ?>
          </td>
        </tr>
        <tr class="end">
          <td colspan="2">&nbsp;</td>
        </tr>
      </tbody>
    </table>
  </fieldset>
</form>
<script type="text/javascript">
  AddOnload( getTabsArray );
  AddOnload( checkSelectedTab );
  <?php if( !isset( $aData['sName'] ) ){ ?>
    AddOnload( function(){ gEBI( 'mainForm' ).sName.focus( ); } );
  <?php } ?>
  $(function(){
    $( "#mainForm" ).quickform();
  });
</script>
<?php
require_once DIR_TEMPLATES.'admin/_footer.php'; // include footer
?>
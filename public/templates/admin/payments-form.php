<?php
if( !defined( 'ADMIN_PAGE' ) )
  exit( 'Script by OpenSolution.org' );

$oOrder = new OrdersAdmin( );

if( isset( $_POST['sName'] ) ){
  $iId = $oOrder->savePaymentShipping( $_POST, 1 );
  if( isset( $_POST['sOptionList'] ) )
    header( 'Location: '.$_SERVER['PHP_SELF'].'?p=payments-list&sOption=save' );
  elseif( isset( $_POST['sOptionAddNew'] ) )
    header( 'Location: '.$_SERVER['PHP_SELF'].'?p=payments-form&sOption=save' );
  else
    header( 'Location: '.$_SERVER['PHP_SELF'].'?p=payments-form&sOption=save&iId='.$iId );
  exit;
}

if( isset( $iId ) && is_numeric( $iId ) ){
  $aData = $oOrder->throwPaymentShipping( $iId, 1 );
}

if( !isset( $aData['iId'] ) )
  $aData['iId'] = null;

$aSelectMenu['bOrders'] = true;
require_once DIR_TEMPLATES.'admin/_header.php'; // include headers
require_once DIR_TEMPLATES.'admin/_menu.php'; // include menu

if( isset( $sOption ) ){
  echo '<div id="msg">'.$lang['Operation_completed'].'</div>';
}

?>
<h1><?php echo ( isset( $_GET['iId'] ) && is_numeric( $_GET['iId'] ) ) ? $lang['Payment_method_form'] : $lang['New_payment_method']; ?><a href="<?php echo $config['manual_link']; ?>instruction#1.19" title="<?php echo $lang['Manual']; ?>" target="_blank"></a></h1>

<form action="?p=<?php echo $p; ?>" method="post" id="mainForm">
  <fieldset id="type2">
    <input type="hidden" name="iId" value="<?php echo $aData['iId']; ?>" />
    <input type="hidden" name="iType" value="1" />
    <table cellspacing="1" class="mainTable" id="payment">
      <thead>
        <tr class="save">
          <th colspan="2">
            <input type="submit" value="<?php echo $lang['save']; ?> &raquo;" name="sOption" />
            <input type="submit" value="<?php echo $lang['save_add_new']; ?> &raquo;" name="sOptionAddNew" />
            <input type="submit" value="<?php echo $lang['save_list']; ?> &raquo;" name="sOptionList" />
          </th>
        </tr>
      </thead>
      <tfoot>
        <tr class="save">
          <th colspan="2">
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
        </tr>
        <!-- name end -->
        <tr class="l1">
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
<script>
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
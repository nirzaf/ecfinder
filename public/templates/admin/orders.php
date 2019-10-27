<?php
if( !defined( 'ADMIN_PAGE' ) )
  exit( 'Script by OpenSolution.org' );

$oOrder = new OrdersAdmin( );
$oOrder->generateCache( );

if( isset( $_POST['sOption'] ) ){
  $oOrder->saveOrders( $_POST );
  $sOption = 'save';
}

$aSelectMenu['bOrders'] = true;
require_once DIR_TEMPLATES.'admin/_header.php'; // include headers
require_once DIR_TEMPLATES.'admin/_menu.php'; // include menu

if( isset( $sOption ) ){
  echo '<div id="msg">'.$lang['Operation_completed'].'</div>';
}

$sStatusSelect = throwSelectFromArray( $oOrder->throwStatus( ), isset( $iStatus ) ? $iStatus : null );

?>
<script type="text/javascript">
AddOnload( function(){ gEBI( 'search' ).sPhrase.focus( ); } );
</script>
<h1><?php echo $lang['Orders']; ?><a href="<?php echo $config['manual_link']; ?>instruction#1.18" title="<?php echo $lang['Manual']; ?>" target="_blank"></a></h1>
<form action="" method="get" id="search">
  <fieldset>
    <input type="hidden" name="p" value="<?php echo $p; ?>" />
    <input type="text" name="sPhrase" value="<?php echo $sPhrase; ?>" class="input" size="50" />
    <select name="iStatus">
      <option value=""><?php echo $lang['All_status']; ?></option>
      <?php echo $sStatusSelect; ?>
    </select>
    &nbsp;&nbsp;
    <?php echo $lang['Orders_search_products']; ?>
    <?php echo throwYesNoBox( 'iProducts', isset( $iProducts ) ? $iProducts : null ); ?>
    <input type="submit" value="<?php echo $lang['search']; ?> &raquo;" />
  </fieldset>
</form>
<?php
// get list of orders
$sOrdersList = $oOrder->listOrdersAdmin( );

// display orders in the table list
if( isset( $sOrdersList ) ){
  ?>
  <form action="?p=<?php echo $p; ?><?php if( isset( $sPhrase ) ) echo '&amp;sPhrase='.$sPhrase; ?><?php if( isset( $iStatus ) ) echo '&amp;iStatus='.$iStatus; ?><?php if( isset( $iProducts ) ) echo '&amp;iProducts='.$iProducts; ?>" method="post">
    <fieldset>
      <table id="list" class="orders" cellspacing="1">
        <thead>
          <tr class="save">
            <td colspan="5" class="pages">
              <?php echo $lang['Pages']; ?>: <ul><?php echo $sPages; ?></ul>
            </td>
            <th colspan="3">
              <select name="iStatus">
                <option><?php echo $lang['Change_status']; ?></option>
                <?php echo $sStatusSelect; ?>
              </select>
              <input type="submit" name="sOption" value="<?php echo $lang['save']; ?> &raquo;" />
            </th>
          </tr>
          <tr>
            <td class="id"><?php echo $lang['Id']; ?></td>
            <td class="name"><?php echo $lang['First_and_last_name']; ?></td>
            <td class="email"><?php echo $lang['Email']; ?></td>
            <td class="phone"><?php echo $lang['Telephone']; ?></td>
            <td class="company"><?php echo $lang['Company']; ?></td>
            <td class="date"><?php echo $lang['Date']; ?></td>
            <td class="status"><?php echo $lang['Status']; ?></td>
            <td class="options">&nbsp;</td>
          </tr>
        </thead>
        <tfoot>
          <tr class="save">
            <td colspan="5" class="pages">
              <?php echo $lang['Pages']; ?>: <ul><?php echo $sPages; ?></ul>
            </td>
            <th colspan="3">
              <input type="submit" name="sOption" value="<?php echo $lang['save']; ?> &raquo;" />
            </th>
          </tr>
        </tfoot>
        <tbody>
          <?php echo $sOrdersList; ?>
        </tbody>
      </table>
    </fieldset>
  </form>
  <?php
}
else{
  echo '<div id="msg" class="error">'.$lang['Data_not_found'].'</div>';
}

require_once DIR_TEMPLATES.'admin/_footer.php'; // include footer
?>
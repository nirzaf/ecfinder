<?php
if( !defined( 'ADMIN_PAGE' ) )
  exit( 'Script by OpenSolution.org' );

$oOrder = new OrdersAdmin( );

$aSelectMenu['bOrders'] = true;
require_once DIR_TEMPLATES.'admin/_header.php'; // include headers
require_once DIR_TEMPLATES.'admin/_menu.php'; // include menu

if( isset( $sOption ) ){
  echo '<div id="msg">'.$lang['Operation_completed'].'</div>';
}
?>
<h1><?php echo $lang['Shipping']; ?><a href="<?php echo $config['manual_link']; ?>instruction#1.20" title="<?php echo $lang['Manual']; ?>" target="_blank"></a></h1>
<form action="" method="get" id="search" onsubmit="return false;">
  <fieldset>
    <input type="text" name="sPhrase" value="<?php echo $lang['search']; ?>" class="input" size="50" onkeyup="listTableSearch( this, 'list', 1 )" onfocus="if(this.value=='<?php echo $lang['search']; ?>')this.value=''" />
  </fieldset>
</form>
<script type="text/javascript">
  AddOnload( function(){ gEBI( 'search' ).sPhrase.focus( ); } );
</script>
<?php
// get list of shipping
$sShippingList = $oOrder->listPaymentsShippingAdmin( 2 );

// display shipping in the table list
if( isset( $sShippingList ) ){
  ?>
  <table id="list" class="shipping" cellspacing="1">
    <thead>
      <tr>
        <td class="id"><?php echo $lang['Id']; ?></td>
        <td class="name"><?php echo $lang['Name']; ?></td>
        <td class="status"><?php echo $lang['Active']; ?></td>
        <td class="price"><?php echo $lang['Price']; ?></td>
        <td class="payments"><?php echo $lang['Payment_methods']; ?></td>
        <td class="options">&nbsp;</td>
      </tr>
    </thead>
    <tbody>
      <?php echo $sShippingList; ?>
    </tbody>
  </table>
  <?php
}
else{
  echo '<div id="msg" class="error">'.$lang['Data_not_found'].'</div>';
}

require_once DIR_TEMPLATES.'admin/_footer.php'; // include footer
?>
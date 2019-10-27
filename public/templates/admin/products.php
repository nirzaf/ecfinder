<?php
if( !defined( 'ADMIN_PAGE' ) )
  exit( 'Script by OpenSolution.org' );

$oPage = PagesAdmin::getInstance( );
$oProduct = ProductsAdmin::getInstance( );
$aSelectMenu['bProducts'] = true;

if( isset( $_POST['sOption'] ) ){
  $oProduct->saveProducts( $_POST );
  header( 'Location: '.str_replace( '&amp;', '&', $_SERVER['REQUEST_URI'] ).( strstr( $_SERVER['REQUEST_URI'], 'sOption=' ) ? null : '&sOption=' ) );
  exit;
}

require_once DIR_TEMPLATES.'admin/_header.php'; // include headers
require_once DIR_TEMPLATES.'admin/_menu.php'; // include menu

if( isset( $sOption ) ){
  echo '<div id="msg">'.$lang['Operation_completed'].'</div>';
}
?>
<h1><?php echo $lang['Products']; ?><a href="<?php echo $config['manual_link']; ?>instruction#1.12" title="<?php echo $lang['Manual']; ?>" target="_blank"></a></h1>
<form action="" method="get" id="search">
  <fieldset>
    <input type="hidden" name="p" value="<?php echo $p; ?>" />
    <?php if( isset( $sSort ) ) echo '<input type="hidden" name="sSort" value="'.$sSort.'" />'; ?>
    <input type="text" name="sPhrase" value="<?php echo $sPhrase; ?>" class="input" size="50" />
    <select name="iPageSearch"><option value=""><?php echo $lang['All_pages']; ?></option><?php echo $oPage->throwProductsPagesSelectAdmin( ( isset( $iPageSearch ) && is_numeric( $iPageSearch ) ? Array( $iPageSearch => true ) : null )  ); ?></select>
    <input type="submit" value="<?php echo $lang['search']; ?> &raquo;" />
  </fieldset>
</form>
<script type="text/javascript">
  AddOnload( function(){ gEBI( 'search' ).sPhrase.focus( ); } );
</script>
<?php
// get list of products
$sProductsList = $oProduct->listProductsAdmin( );

// display products in the table list
if( isset( $sProductsList ) ){
  ?>
  <script type="text/javascript">
  var aDelUrl = Array( '?p=products-delete&iProduct=', '?p=products-delete&bWithoutFiles=true&iProduct=' );
  var bDeleteUnusedFiles = "<?php echo $config['delete_unused_files']; ?>";
  var iMaxNameLength = <?php echo $config['max_product_name_in_list']; ?>;
  AddOnload( checkProductsNamesWidth );
  </script>
  <form action="?p=<?php echo $p; ?><?php if( isset( $sPhrase ) ) echo '&amp;sPhrase='.$sPhrase; ?><?php if( isset( $sSort ) ) echo '&amp;sSort='.$sSort; ?>" method="post">
    <fieldset>
      <table id="list" class="products" cellspacing="1">
        <thead>
          <tr class="save">
            <td colspan="6" class="pages">
              <?php echo $lang['Pages']; ?>: <ul><?php echo $sPages; ?></ul>
            </td>
            <th colspan="1">
              <input type="submit" name="sOption" value="<?php echo $lang['save']; ?> &raquo;" />
            </th>
          </tr>
          <tr>
            <td class="id"><a href="?p=<?php echo $p; ?><?php if( isset( $sPhrase ) ) echo '&amp;sPhrase='.$sPhrase; ?><?php if( isset( $iPageSearch ) ) echo '&amp;iPageSearch='.$iPageSearch; ?>&amp;sSort=id"><?php echo $lang['Id']; ?></a></td>
            <td class="name"><a href="?p=<?php echo $p; ?><?php if( isset( $sPhrase ) ) echo '&amp;sPhrase='.$sPhrase; ?><?php if( isset( $iPageSearch ) ) echo '&amp;iPageSearch='.$iPageSearch; ?>&amp;sSort=name"><?php echo $lang['Name']; ?></a></td>
            <td class="pages"><?php echo $lang['Pages']; ?></td>
            <td class="price"><a href="?p=<?php echo $p; ?><?php if( isset( $sPhrase ) ) echo '&amp;sPhrase='.$sPhrase; ?><?php if( isset( $iPageSearch ) ) echo '&amp;iPageSearch='.$iPageSearch; ?>&amp;sSort=price"><?php echo $lang['Price']; ?></a> [<?php echo $config['currency_symbol']; ?>]</td>
            <td class="position"><a href="?p=<?php echo $p; ?><?php if( isset( $sPhrase ) ) echo '&amp;sPhrase='.$sPhrase; ?><?php if( isset( $iPageSearch ) ) echo '&amp;iPageSearch='.$iPageSearch; ?>&amp;sSort=position"><?php echo $lang['Position']; ?></a></td>
            <td class="status"><a href="?p=<?php echo $p; ?><?php if( isset( $sPhrase ) ) echo '&amp;sPhrase='.$sPhrase; ?><?php if( isset( $iPageSearch ) ) echo '&amp;iPageSearch='.$iPageSearch; ?>&amp;sSort=status"><?php echo $lang['Status']; ?></a></td>
            <td class="options">&nbsp;</td>
          </tr>
        </thead>
        <tfoot>
          <tr class="save">
            <td colspan="6" class="pages">
              <?php echo $lang['Pages']; ?>: <ul><?php echo $sPages; ?></ul>
            </td>
            <th colspan="1">
              <input type="submit" name="sOption" value="<?php echo $lang['save']; ?> &raquo;" />
            </th>
          </tr>
        </tfoot>
        <tbody>
          <?php echo $sProductsList; ?>
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
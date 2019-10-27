<?php 
// More about design modifications - www.opensolution.org/Quick.Cart/docs/ext_6.6/?id=en-design
if( !defined( 'CUSTOMER_PAGE' ) )
  exit;

require_once DIR_SKIN.'_header.php'; // include design of header
?>
<div id="product">
<?php
if( isset( $aData['sName'] ) ){ // displaying product content ?>
  <script type="text/javascript">
    var sTitle = "<?php echo $aData['sName']; ?>";
    var fPrice = Math.abs( "<?php echo $aData['mPrice']; ?>" );
  </script><?php

  echo '<h1>'.$aData['sName'].'</h1>'; // displaying product name

  if( isset( $aData['sPages'] ) )
    echo '<div class="breadcrumb">'.$aData['sPages'].'</div>'; // displaying pages tree (breadcrumb)

  if( isset( $config['image_preview_size'] ) && is_numeric( $config['image_preview_size'] ) )
    echo $oFile->listPreviewImages( $aData['iProduct'], 1 ); // displaying images with type: left
  else
    echo $oFile->listImagesByTypes( $aData['iProduct'], 1 ); // displaying images with type: left

  if( isset( $aData['mPrice'] ) || isset( $aData['sAvailable'] ) ){ // displaying box with price, basket and availability - START
    echo '<div id="box">';
      if( isset( $aData['mPrice'] ) && is_numeric( $aData['mPrice'] ) ){?>
        <div id="price"><em><?php echo $lang['Price']; ?>:</em><strong id="priceValue"><?php echo $aData['sPrice']; ?></strong><span><?php echo $config['currency_symbol']; ?></span></div><?php
      }
      elseif( !empty( $aData['mPrice'] ) ){?>
        <div id="noPrice"><?php echo $aData['sPrice']; ?></div><?php
      }
      if( isset( $aData['sAvailable'] ) ){?>
        <div id="available"><?php echo $aData['sAvailable']; ?></div><?php
      }
      if( isset( $aData['mPrice'] ) && is_numeric( $aData['mPrice'] ) && !empty( $config['basket_page'] ) && isset( $oPage->aPages[$config['basket_page']] ) ){?>
        <form action="<?php echo $oPage->aPages[$config['basket_page']]['sLinkName']; ?>" method="post" id="addBasket" class="form">
          <fieldset>
            <legend><?php echo $lang['Basket_add']; ?></legend>
            <input type="hidden" name="iProductAdd" value="<?php echo $aData['iProduct']; ?>" />
            <input type="hidden" name="iQuantity" value="1" />
            <input type="submit" value="<?php echo $lang['Basket_add']; ?>" class="submit" />
          </fieldset>
        </form><?php
      }
    echo '</div>';
  } // displaying box with price, basket and availability - END

  echo $oFile->listImagesByTypes( $aData['iProduct'], 2 ); // displaying images with type: right
  
  if( isset( $aData['sDescriptionFull'] ) )
    echo '<div class="content" id="productDescription">'.$aData['sDescriptionFull'].'</div>'; // full description

  echo $oFile->listFiles( $aData['iProduct'] ); // display files included to the product

}
else{
  echo '<div class="message" id="error"><h2>'.$lang['Data_not_found'].'</h2></div>'; // displaying 404 error
}
?>
</div>
<?php
require_once DIR_SKIN.'_footer.php'; // include design of footer
?>

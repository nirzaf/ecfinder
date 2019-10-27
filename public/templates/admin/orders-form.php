<?php
if( !defined( 'ADMIN_PAGE' ) )
  exit( 'Script by OpenSolution.org' );

$oOrder = new OrdersAdmin( );
$oOrder->generateCache( );

$aData = $oOrder->throwOrder( $iOrder );

if( isset( $aData ) && is_array( $aData ) ){

  if( isset( $_POST['sFirstName'] ) ){
    $oOrder->saveOrder( $_POST );
    if( isset( $_POST['sOptionList'] ) )
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p=orders-list&sOption=save' );
    else
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p=orders-form&sOption=save&iOrder='.$iOrder );
    exit;
  }

  $aSelectMenu['bOrders'] = true;
  require_once DIR_TEMPLATES.'admin/_header.php'; // include headers
  require_once DIR_TEMPLATES.'admin/_menu.php'; // include menu

  if( isset( $sOption ) ){
    echo '<div id="msg">'.$lang['Operation_completed'].'</div>';
  }

  $aData['sComment'] = isset( $aData['sComment'] ) ? changeTxt( $aData['sComment'], 'nlNds' ) : null;

  $sOrderLang = $aData['sLanguage'];
  require $config['dir_database'].'config/lang_'.$aData['sLanguage'].'.php';

  $sProductsList = $oOrder->listProductsAdmin( $iOrder );
  $fOrderSummary = isset( $oOrder->aOrders[$iOrder]['fOrderSummary'] ) ? $oOrder->aOrders[$iOrder]['fOrderSummary'] : 0;
  $sOrderSummary = isset( $oOrder->aOrders[$iOrder]['sOrderSummary'] ) ? $oOrder->aOrders[$iOrder]['sOrderSummary'] : null;
  ?>
  <h1><?php echo $lang['Order'].' - '.$aData['iOrder']; ?><a href="<?php echo $config['manual_link']; ?>instruction#1.18" title="<?php echo $lang['Manual']; ?>" target="_blank"></a></h1>
  <form action="?p=<?php echo $p; ?>&amp;iOrder=<?php echo $aData['iOrder']; ?>" method="post" id="mainForm">
    <fieldset id="type2">
      <input type="hidden" name="iOrder" value="<?php echo $aData['iOrder']; ?>" />
      <table cellspacing="1" class="mainTable" id="order">
        <thead>
          <tr class="save">
            <th colspan="3">
              <input type="submit" value="<?php echo $lang['save']; ?> &raquo;" name="sOption" />
              <input type="submit" value="<?php echo $lang['save_list']; ?> &raquo;" name="sOptionList" />
            </th>
          </tr>
        </thead>
        <tfoot>
          <tr class="save">
            <th colspan="3">
              <input type="submit" value="<?php echo $lang['save']; ?> &raquo;" name="sOption" />
              <input type="submit" value="<?php echo $lang['save_list']; ?> &raquo;" name="sOptionList" />
            </th>
          </tr>
        </tfoot>
        <tbody>
          <!-- name start -->
          <tr class="l0">
            <th>
              <label for="sFirstName"><?php echo $lang['First_and_last_name']; ?> <span>(<?php echo $lang['required']; ?>)</span></label>
            </th>
            <td>
              <input type="text" name="sFirstName" id="sFirstName" value="<?php echo $aData['sFirstName']; ?>" size="24" maxlength="30" class="input" data-form-check="required" accesskey="1" tabindex="2" />
              <input type="text" name="sLastName" value="<?php echo $aData['sLastName']; ?>" size="24" maxlength="40" class="input" data-form-check="required" tabindex="2" />
            </td>
            <td rowspan="16" class="tabs">
              <div id="tabs">
                <ul id="tabsNames">
                  <!-- tabs start -->
                  <li class="tabProductsPayment"><a href="#more" onclick="displayTab( 'tabProductsPayment' )"><?php echo $lang['Products_and_payment']; ?></a></li>
                  <!-- tabs end -->
                </ul>
                <div id="tabsForms">
                  <script type="text/javascript">
                  function showAddProductForm( ){
                    gEBI( "addProductForm" ).style.display = "";
                    gEBI( "addProductLink" ).style.display = "none";
                    gEBI( "newProductId" ).focus( );
                  } // end function showAddProductForm
                  </script>
                  <!-- tabs list start -->
                  <table class="tab" id="tabProductsPayment">
                    <thead>
                      <tr>
                        <td class="id"><?php echo $lang['Id']; ?></td>
                        <td class="name"><?php echo $lang['Name']; ?></td>
                        <td class="price"><?php echo $lang['Price']; ?> [<?php echo $config['currency_symbol']; ?>]</td>
                        <td class="quantity"><?php echo $lang['Quantity']; ?></td>
                        <td class="summary"><?php echo $lang['Summary']; ?> [<?php echo $config['currency_symbol']; ?>]</td>
                        <td class="options"><?php echo ucfirst( $lang['delete'] ); ?></td>
                      </tr>
                    </thead>
                    <tfoot>
                      <tr>
                        <td colspan="4" class="info">
                          <?php echo $lang['Summary']; ?>
                        </td>
                        <td colspan="2" class="summary">
                          <?php echo $sOrderSummary; ?>
                        </td>
                      </tr>
                    </tfoot>
                    <tbody>
                      <?php echo $sProductsList; ?>
                      <tr id="addProductLink">
                        <td>&nbsp;</td>
                        <td colspan="5"><a href="#" onclick="showAddProductForm( )"><img src="<?php echo $config['dir_templates']; ?>admin/img/ico_add_small.gif" alt="" /></a> <a href="#" onclick="showAddProductForm( )"><?php echo $lang['New_product']; ?></a></td>
                      </tr>
                      <tr id="addProductForm" style="display:none;">
                        <td class="id"><input type="text" name="aNewProduct[iProduct]" value="" size="2" class="input" id="newProductId" /></td>
                        <td class="name"><input type="text" name="aNewProduct[sName]" value="" class="input" size="40" /></td>
                        <td class="price"><input type="text" name="aNewProduct[fPrice]" value="" class="inputr" size="7" maxlength="15" /></td>
                        <td class="quantity"><input type="text" name="aNewProduct[iQuantity]" value="" class="input" size="2" maxlength="3" /></td>
                        <td colspan="2"></td>
                      </tr>
                        <tr>
                        <td class="id"><input type="text" name="iShipping" value="<?php if( isset( $aData['iShipping'] ) ) echo $aData['iShipping']; ?>" size="2" class="input" data-form-check="int;1" data-form-if="true" /></td>
                        <td class="name"><input type="text" name="mShipping" value="<?php if( isset( $aData['mShipping'] ) ) echo $aData['mShipping']; ?>" class="input" size="40" /></td>
                        <td class="price" colspan="4"><input type="text" name="fShippingPrice" value="<?php if( isset( $aData['fShippingPrice'] ) ) echo $aData['fShippingPrice']; ?>" class="inputr" size="7" maxlength="15" data-form-check="float" data-form-if="true" /><span class="info""> - <?php echo $lang['Shipping']; ?></span></td>
                      </tr>
                      <tr>
                        <td class="id"><input type="text" name="iPayment" value="<?php if( isset( $aData['iPayment'] ) ) echo $aData['iPayment']; ?>" size="2" class="input" /></td>
                        <!-- alt="int;0" -->
                        <td class="name"><input type="text" name="mPayment" value="<?php if( isset( $aData['mPayment'] ) ) echo $aData['mPayment']; ?>" class="input" size="40" /></td>
                        <td class="price" colspan="4"><input type="text" name="mPaymentPrice" value="<?php if( isset( $aData['mPaymentPrice'] ) ) echo $aData['mPaymentPrice']; ?>" class="inputr" size="7" maxlength="15" /><span class="info""> - <?php echo $lang['Payment_method']; ?></span></td>
                      </tr>
                    </tbody>
                    <!-- tab products_payment -->
                  </table>
                  <!-- tabs list end -->
                </div>
              </div>

              <script type="text/javascript">
              AddOnload( getTabsArray );
              AddOnload( checkSelectedTab );
              </script>
            </td>
          </tr>
          <!-- name end -->
          <!-- company start -->
          <tr class="l1">
            <th>
              <?php echo $lang['Company']; ?>
            </th>
            <td>
              <input type="text" name="sCompanyName" value="<?php if( isset( $aData['sCompanyName'] ) ) echo $aData['sCompanyName']; ?>" size="50" maxlength="100" class="input" tabindex="3" />
            </td>
          </tr>
          <!-- company end -->
          <!-- street start -->
          <tr class="l0">
            <th>
              <label for="sStreet"><?php echo $lang['Street']; ?> <span>(<?php echo $lang['required']; ?>)</span></label>
            </th>
            <td>
              <input type="text" name="sStreet" id="sStreet" value="<?php echo $aData['sStreet']; ?>" size="50" maxlength="40" class="input" data-form-check="required" tabindex="4" />
            </td>
          </tr>
          <!-- street end -->
          <!-- zip_code start -->
          <tr class="l1">
            <th>
              <label for="sZipCode"><?php echo $lang['Zip_code']; ?> <span>(<?php echo $lang['required']; ?>)</span></label>
            </th>
            <td>
              <input type="text" name="sZipCode" id="sZipCode" value="<?php echo $aData['sZipCode']; ?>" size="50" maxlength="20" class="input" data-form-check="required" tabindex="5" />
            </td>
          </tr>
          <!-- zip_code end -->
          <!-- city start -->
          <tr class="l0">
            <th>
              <label for="sCity"><?php echo $lang['City']; ?> <span>(<?php echo $lang['required']; ?>)</span></label>
            </th>
            <td>
              <input type="text" name="sCity" id="sCity" value="<?php echo $aData['sCity']; ?>" size="50" maxlength="40" class="input" data-form-check="required" tabindex="6" />
            </td>
          </tr>
          <!-- city end -->
          <!-- telephone start -->
          <tr class="l1">
            <th>
              <label for="sPhone"><?php echo $lang['Telephone']; ?> <span>(<?php echo $lang['required']; ?>)</span></label>
            </th>
            <td>
              <input type="text" name="sPhone" id="sPhone" value="<?php if( isset( $aData['sPhone'] ) ) echo $aData['sPhone']; ?>" size="50" maxlength="40" class="input" data-form-check="required" tabindex="7" />
            </td>
          </tr>
          <!-- telephone end -->
          <!-- email start -->
          <tr class="l0">
            <th>
              <label for="sEmail"><?php echo $lang['Email']; ?> <span>(<?php echo $lang['required']; ?>)</span></label>
            </th>
            <td>
              <input type="text" name="sEmail" id="sEmail" value="<?php echo $aData['sEmail']; ?>" size="50" maxlength="40" class="input" data-form-check="email" tabindex="8" />
            </td>
          </tr>
          <!-- email end -->
          <!-- date start -->
          <tr class="l1">
            <th>
              <?php echo $lang['Date']; ?>
            </th>
            <td>
              <?php echo $aData['sDate']; ?>
            </td>
          </tr>
          <!-- date end -->
          <!-- status start -->
          <tr class="l0">
            <th>
              <?php echo $lang['Status']; ?>
            </th>
            <td>
              <select name="iStatus" tabindex="9">
                <?php echo throwSelectFromArray( $oOrder->throwStatus( ), $aData['iStatus'] ); ?>
              </select>
              <?php echo $oOrder->listOrderStatuses( $iOrder ); ?>
            </td>
          </tr>
          <!-- status end -->
          <!-- comment start -->
          <tr class="l1">
            <th>
              <?php echo $lang['Comment']; ?>
            </th>
            <td>
              <textarea name="sComment" cols="50" rows="7" tabindex="10"><?php echo $aData['sComment']; ?></textarea>
            </td>
          </tr>
          <!-- comment end -->
          <!-- lang start -->
          <tr class="l0">
            <th>
              <?php echo $lang['Language']; ?>
            </th>
            <td>
              <?php echo $aData['sLanguage']; ?>
            </td>
          </tr>
          <!-- lang end -->
          <!-- ip start -->
          <tr class="l1">
            <th>
              IP
            </th>
            <td>
              <a href="http://www.ripe.net/perl/whois?form_type=simple&amp;full_query_string=&amp;searchtext=<?php echo $aData['sIp']; ?>&amp;do_search=Search" target="_blank"><?php echo $aData['sIp']; ?></a>
            </td>
          </tr>
          <!-- ip end -->
          <tr class="end">
            <td colspan="2">&nbsp;</td>
          </tr>
        </tbody>
      </table>
    </fieldset>
    <script type="text/javascript">
      AddOnload( getTabsArray );
      AddOnload( checkSelectedTab );
      $(function(){
        $( "#mainForm" ).quickform();
      });
    </script>
  </form>
<?php
  require DB_CONFIG_LANG;
}
require_once DIR_TEMPLATES.'admin/_footer.php'; // include footer
?>
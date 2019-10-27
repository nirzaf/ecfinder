<?php
if( !defined( 'ADMIN_PAGE' ) )
  exit( 'Script by OpenSolution.org' );

$oPage = PagesAdmin::getInstance( );

if( isset( $_POST['sOption'] ) ){
  if( isset( $_POST['skin'] ) && $config['skin'] != $_POST['skin'] && $_POST['skin'] != 'default' ){
    copyDirToDir( DIR_TEMPLATES.'default/', DIR_TEMPLATES.$_POST['skin'].'/' );
  }
  saveVariables( $_POST, DB_CONFIG );
  saveVariables( $_POST, DB_CONFIG_LANG );
  header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$p.'&sOption=save' );
  exit;
}

$aSelectMenu['bTools'] = true;
require_once DIR_TEMPLATES.'admin/_header.php'; // include headers
require_once DIR_TEMPLATES.'admin/_menu.php'; // include menu

if( isset( $sOption ) ){
  if( $sOption == 'login-pass' )
    echo '<div id="msg">'.$lang['Change_login_and_pass_to_use_script'].'</div>';
  else
    echo '<div id="msg">'.$lang['Operation_completed'].'</div><script type="text/javascript">var bDone = true;</script>';
}
?>
<h1><?php echo $lang['Settings']; ?><a href="<?php echo $config['manual_link']; ?>instruction#1.2" title="<?php echo $lang['Manual']; ?>" target="_blank"></a></h1>
<form action="?p=<?php echo $p; ?>" method="post" id="mainForm" name="form">
  <fieldset id="type2">
    <table cellspacing="1" class="mainTable" id="config">
      <thead>
        <tr class="save">
          <th colspan="3">
            <input type="submit" value="<?php echo $lang['save']; ?> &raquo;" name="sOption" />
          </th>
        </tr>
      </thead>
      <tfoot>
        <tr class="save">
          <th colspan="3">
            <input type="submit" value="<?php echo $lang['save']; ?> &raquo;" name="sOption" />
          </th>
        </tr>
      </tfoot>
      <tbody>
        <!-- title start -->
        <tr class="l0">
          <th>
            <?php echo $lang['Page_title']; ?>
          </th>
          <td>
            <input type="text" name="title" value="<?php echo $config['title']; ?>" size="70" maxlength="60" class="input" accesskey="1" />
          </td>
          <td rowspan="15" class="tabs">
            <div id="tabs">
              <ul id="tabsNames">
                <!-- tabs start -->
                <li class="tabOptions"><a href="#more" onclick="displayTab( 'tabOptions' )"><?php echo $lang['Options']; ?></a></li>
                <?php if( $config['display_advanced_options'] === true ){ ?><li class="tabPages"><a href="#more" onclick="displayTab( 'tabPages' )"><?php echo $lang['Pages']; ?></a></li><?php } ?>
                <li class="tabItems"><a href="#more" onclick="displayTab( 'tabItems' )"><?php echo $lang['Items']; ?></a></li>
                <li class="tabAdvanced"><a href="#more" onclick="displayTab( 'tabAdvanced' )"><?php echo $lang['Advanced']; ?></a></li>
                <!-- tabs end -->
              </ul>
              <div id="tabsForms">
                <!-- tabs list start -->
                <table class="tab" id="tabOptions">
                  <tr>
                    <td><?php echo $lang['Default_language']; ?></td>
                    <td>
                      <select name="default_lang">
                        <?php echo throwLangSelect( $config['default_lang'] ); ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $lang['Admin_language']; ?></td>
                    <td>
                      <select name="admin_lang">
                        <?php echo throwLangSelect( $config['admin_lang'] ); ?>
                      </select>
                    </td>
                  </tr>
                  <?php if( $config['display_advanced_options'] === true ){ ?>
                    <tr>
                      <td><?php echo $lang['Skin']; ?>&nbsp;-&nbsp;<a href="http://opensolution.org/?p=download&sDir=Quick.Cart/skins" target="_blank" style="text-transform:lowercase;"><?php echo $lang['Need_more']; ?></a></td>
                      <td>
                        <select name="skin">
                          <?php echo throwSkinsSelect( $config['skin'] ); ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td><?php echo $lang['Sort_products']; ?></td>
                      <td>
                        <select name="sorting_products">
                          <?php echo throwTrueFalseOrNullSelect( $config['sorting_products'] ); ?>
                        </select>
                      </td>
                    </tr>
                  <?php } ?>
                  <tr>
                    <td><label for="currency_symbol"><?php echo $lang['Currency_symbol']; ?> <span>(<?php echo $lang['required']; ?>)</span></label></td>
                    <td>
                      <input type="text" name="currency_symbol" value="<?php echo $config['currency_symbol']; ?>" size="5" maxlength="5" data-form-check="required" class="input" />
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $lang['Admin_see_hidden_pages']; ?></td>
                    <td>
                      <select name="hidden_shows">
                        <?php echo throwTrueFalseOrNullSelect( $config['hidden_shows'] ); ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $lang['Display_expanded_menu']; ?></td>
                    <td>
                      <select name="display_expanded_menu">
                        <?php echo throwTrueFalseOrNullSelect( $config['display_expanded_menu'] ); ?>
                      </select>
                    </td>
                  </tr>
                  <?php if( $config['display_advanced_options'] === true ){ ?>
                    <tr>
                      <td><?php echo $lang['Language_in_url']; ?></td>
                      <td>
                        <select name="language_in_url">
                          <?php echo throwTrueFalseOrNullSelect( $config['language_in_url'] ); ?>
                        </select>
                      </td>
                    </tr>
                  <?php } ?>
                  <!-- tab options -->
                </table>

                <table class="tab" id="tabPages">
                  <tr>
                    <td><?php echo $lang['Start_page']; ?></td>
                    <td>
                      <select name="start_page">
                        <?php echo $oPage->throwPagesSelectAdmin( $config['start_page'] ); ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $lang['Basket_page']; ?></td>
                    <td>
                      <select name="basket_page">
                        <option value=""><?php echo $lang['none']; ?></option>
                        <?php echo $oPage->throwPagesSelectAdmin( $config['basket_page'] ); ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $lang['Order_page']; ?></td>
                    <td>
                      <select name="order_page">
                        <option value=""><?php echo $lang['none']; ?></option>
                        <?php echo $oPage->throwPagesSelectAdmin( $config['order_page'] ); ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $lang['Order_print']; ?></td>
                    <td>
                      <select name="order_print">
                        <option value=""><?php echo $lang['none']; ?></option>
                        <?php echo $oPage->throwPagesSelectAdmin( $config['order_print'] ); ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $lang['Rules_page']; ?></td>
                    <td>
                      <select name="rules_page">
                        <option value=""><?php echo $lang['none']; ?></option>
                        <?php echo $oPage->throwPagesSelectAdmin( $config['rules_page'] ); ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $lang['Page_search']; ?></td>
                    <td>
                      <select name="page_search">
                        <option value=""><?php echo $lang['none']; ?></option>
                        <?php echo $oPage->throwPagesSelectAdmin( $config['page_search'] ); ?>
                      </select>
                    </td>
                  </tr>
                  <!-- tab pages -->
                </table>

                <table class="tab" id="tabItems">
                  <tr>
                    <td><label for="admin_list"><?php echo $lang['Admin_items_on_page']; ?> <span>(<?php echo $lang['required']; ?>)</span></label></td>
                    <td>
                      <input type="text" name="admin_list" id="admin_list" value="<?php echo $config['admin_list']; ?>" size="3" maxlength="3" data-form-check="int;1" class="input" />
                    </td>
                  </tr>
                  <?php if( $config['display_advanced_options'] === true ){ ?>
                    <tr>
                      <td><label for="products_list"><?php echo $lang['Products_on_page']; ?> <span>(<?php echo $lang['required']; ?>)</span></label></td>
                      <td>
                        <input type="text" name="products_list" id="products_list" value="<?php echo $config['products_list']; ?>" size="3" maxlength="3" data-form-check="int;1" class="input" />
                      </td>
                    </tr>
                  <?php } ?>
                  <!-- tab lists -->
                </table>

                <table class="tab" id="tabAdvanced">
                  <tr>
                    <td><?php echo $lang['Change_files_names']; ?></td>
                    <td>
                      <select name="change_files_names">
                        <?php echo throwTrueFalseOrNullSelect( $config['change_files_names'] ); ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $lang['Delete_unused_files']; ?></td>
                    <td>
                      <select name="delete_unused_files">
                        <?php echo throwTrueFalseOrNullSelect( $config['delete_unused_files'] ); ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $lang['WYSIWIG_editor']; ?></td>
                    <td>
                      <select name="wysiwyg">
                        <?php echo throwTrueFalseOrNullSelect( $config['wysiwyg'] ); ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><?php echo $lang['Send_customer_order_details']; ?></td>
                    <td>
                      <select name="send_customer_order_details">
                        <?php echo throwTrueFalseOrNullSelect( $config['send_customer_order_details'] ); ?>
                      </select>
                    </td>
                  </tr>
                  <?php if( $config['display_advanced_options'] === true ){ ?>
                    <tr>
                      <td><?php echo $lang['Display_subcategory_products']; ?></td>
                      <td>
                        <select name="display_subcategory_products">
                          <?php echo throwTrueFalseOrNullSelect( $config['display_subcategory_products'] ); ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td><?php echo $lang['Display_remember_basket']; ?></td>
                      <td>
                        <select name="remember_basket">
                          <?php echo throwTrueFalseOrNullSelect( $config['remember_basket'] ); ?>
                        </select>
                      </td>
                    </tr>
                  <?php } ?>
                  <!-- tab advanced -->
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
        <!-- title end -->
        <!-- description start -->
        <tr class="l1">
          <th>
            <?php echo $lang['Description']; ?>
          </th>
          <td>
            <input type="text" name="description" value="<?php echo $config['description']; ?>" size="70" maxlength="200" class="input" />
          </td>
        </tr>
        <!-- description end -->
        <!-- logo start -->
        <tr class="l0">
          <th>
            <?php echo $lang['Logo']; ?>
          </th>
          <td>
            <input type="text" name="logo" value="<?php echo $config['logo']; ?>" size="70" maxlength="200" class="input" />
          </td>
        </tr>
        <!-- logo end -->
        <!-- slogan start -->
        <tr class="l1">
          <th>
            <?php echo $lang['Slogan']; ?>
          </th>
          <td>
            <input type="text" name="slogan" value="<?php echo $config['slogan']; ?>" size="70" maxlength="200" class="input" />
          </td>
        </tr>
        <!-- slogan end -->
        <!-- foot info start -->
        <tr class="l0">
          <th>
            <?php echo $lang['Foot_info']; ?>
          </th>
          <td>
            <input type="text" name="foot_info" value="<?php echo $config['foot_info']; ?>" size="70" maxlength="200" class="input" />
          </td>
        </tr>
        <!-- foot info end -->
        <!-- login start -->
        <tr class="l1" id="login">
          <th>
            <label for="oLogin"><?php echo $lang['Login']; ?> <span>(<?php echo $lang['required']; ?>)</span></label>
          </th>
          <td>
            <input type="text" name="login" value="<?php echo $config['login']; ?>" size="40" class="input" data-form-check="required" id="oLogin" style="display:none" /> <a href="#" onclick="gEBI('oLogin').style.display='inline';this.style.display='none';return false;"><?php echo $lang['edit']; ?></a>
          </td>
        </tr>
        <!-- login end -->
        <!-- pass start -->
        <tr class="l0" id="pass">
          <th>
            <label for="oPass"><?php echo $lang['Password']; ?> <span>(<?php echo $lang['required']; ?>)</span></label>
          </th>
          <td>
            <input type="text" name="pass" value="<?php echo $config['pass']; ?>" size="40" class="input" data-form-check="required" id="oPass" style="display:none" /> <a href="#" onclick="gEBI('oPass').style.display='inline';this.style.display='none';return false;"><?php echo $lang['edit']; ?></a>
          </td>
        </tr>
        <!-- pass end -->
        <!-- orders_email start -->
        <tr class="l1" id="orders_email">
          <th>
            <?php echo $lang['Mail_informing']; ?>
          </th>
          <td>
            <input type="text" name="orders_email" value="<?php echo $config['orders_email']; ?>" size="40" class="input" />
          </td>
        </tr>
        <!-- orders_email end -->
        <tr class="end">
          <td colspan="2">&nbsp;</td>
        </tr>
      </tbody>
    </table>
  </fieldset>
</form>
<script>
  $(function(){
    $( "#mainForm" ).quickform();
  });
</script>
<?php
require_once DIR_TEMPLATES.'admin/_footer.php'; // include footer
?>
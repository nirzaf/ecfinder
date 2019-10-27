<?php
if( !defined( 'ADMIN_PAGE' ) )
  exit;
?>

<body>
  <div id="container">
    <div id="header">
      <div id="menuTop">
        <form action="" method="get" id="searchPanel">
          <fieldset>
            <input type="hidden" name="p" value="search" />
            <input type="text" name="sPhrase" value="<?php echo $sPhrase; ?>" class="input" size="25" />
            <select name="iTypeSearch"><?php echo throwSelectFromArray( Array( 1 => $lang['Pages'], 2 => $lang['Products'], 3 => $lang['Orders'] ), $iTypeSearch ); ?></select>
            <input type="submit" value="<?php echo $lang['search']; ?> &raquo;" />
          </fieldset>
        </form>
        <ul id="extend" class="main-menu submenu">
          <li class="settings"><a href="#"><img src="templates/admin/img/settings.png" alt="<?php echo $lang['Settings']; ?>" /></a>
            <ul>
              <li><a href="?p=tools-config"><?php echo $lang['Settings']; ?></a></li>
              <li><a href="http://opensolution.org/?p=download&sDir=Quick.Cart"><?php echo $lang['Extend']; ?></a></li>
              <li><a href="?p=logout"><?php echo $lang['log_out']; ?></a></li>
            </ul>
          </li>
        </ul>
      </div>

      <div id="logoOs" class="submenu">
        <!-- Don't delete or hide OpenSolution logo and links to www.OpenSolution.org -->
        <a href="http://opensolution.org/" target="_blank"><img src="<?php echo $config['dir_templates']; ?>admin/img/logo_os.png" alt="OpenSolution.org" /></a>
        <ul>
          <li><a href="http://opensolution.org/support-free"><?php echo $lang['Support']; ?></a></li>
          <li><a href="<?php echo $config['manual_link']; ?>start"><?php echo $lang['Manual']; ?></a></li>
          <li><a href="http://opensolution.org/?p=licenses"><?php echo $lang['License']; ?></a></li>
        </ul>
      </div>
      <div class="clear"></div>

      <!-- menu under_logo start -->
      <div id="menuBar">
        <ul class="main-menu">
          <li<?php echo ( isset( $aSelectMenu['bDashboard'] ) ? ' class="selected"' : null ); ?>><a href="?p="><span class="dashboard"><?php echo $lang['Dashboard']; ?></span></a></li>
          <li onmouseover="return buttonClick( event, 'pages' ); buttonMouseover( event, 'pages' );"<?php echo ( isset( $aSelectMenu['bPages'] ) ? ' class="selected"' : null ); ?>><a href="?p=pages-list"><span class="pages"><?php echo $lang['Pages']; ?></span></a></li>
          <li onmouseover="return buttonClick( event, 'products' ); buttonMouseover( event, 'products' );"<?php echo ( isset( $aSelectMenu['bProducts'] ) ? ' class="selected"' : null ); ?>><a href="?p=products-list"><span class="products"><?php echo $lang['Products']; ?></span></a></li>
          <li onmouseover="return buttonClick( event, 'orders' ); buttonMouseover( event, 'orders' );"<?php echo ( isset( $aSelectMenu['bOrders'] ) ? ' class="selected"' : null ); ?>><a href="?p=orders-list"><span class="orders"><?php echo $lang['Orders']; ?></span></a></li>
          <!-- main menu -->
          <li onmouseover="return buttonClick( event, 'tools' ); buttonMouseover( event, 'tools' );"<?php echo ( isset( $aSelectMenu['bTools'] ) ? ' class="selected"' : null ); ?>><a href="#"><span class="tools"><?php echo $lang['Tools']; ?></span></a></li>
        </ul>
        <?php echo listLanguagesMenu( ); ?>
      </div>

      <!-- submenu under_logo start -->
      <div id="pages" class="menu" onmouseover="menuMouseover( event );">
        <a href="?p=pages-form"><?php echo $lang['New_page']; ?></a>
        <!-- menu pages -->
      </div>
      <div id="tools" class="menu" onmouseover="menuMouseover( event );">
        <a href="?p=lang-list"><?php echo $lang['Languages']; ?></a>
        <a href="?p=lang-form"><?php echo $lang['New_language']; ?></a>
        <!-- menu tools -->
      </div>
      <div id="products" class="menu" onmouseover="menuMouseover( event );">
        <a href="?p=products-form"><?php echo $lang['New_product']; ?></a>
        <!-- menu products -->
      </div>
      <div id="orders" class="menu" onmouseover="menuMouseover( event );">
        <a href="?p=orders-list&amp;iStatus=1"><?php echo $lang['Orders_pending']; ?></a>
        <span class="sep"></span>
        <a href="?p=shipping-list"><?php echo $lang['Shipping']; ?></a>
        <a href="?p=shipping-form"><?php echo $lang['New_shipping']; ?></a>
        <span class="sep"></span>
        <a href="?p=payments-list"><?php echo $lang['Payment_methods']; ?></a>
        <a href="?p=payments-form"><?php echo $lang['New_payment_method']; ?></a>
        <!-- menu orders -->
      </div>
      <!-- menu under_logo end -->

    </div>
    <div class="clear"></div>
    <div id="body">
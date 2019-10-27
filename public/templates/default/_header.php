<?php
// More about design modifications - www.opensolution.org/Quick.Cart/docs/ext_6.6/?id=en-design
// Generator tag is required: www.opensolution.org/licenses.html
if( !defined( 'CUSTOMER_PAGE' ) )
  exit;
?><!DOCTYPE HTML>
<html lang="<?php echo $config['language']; ?>">
<head>
  <title><?php echo $sTitle.$config['title']; ?></title>
  <meta name="Language" content="<?php echo $config['language']; ?>" />
  <meta name="Description" content="<?php echo $sDescription; ?>" />
  <meta name="Generator" content="Quick.Cart v<?php echo $config['version']; ?>" />

  <link rel="stylesheet" href="<?php echo $config['dir_skin'].$config['style']; ?>" />

  <script src="<?php echo $config['dir_plugins']; ?>jquery.min.js"></script>
  <script src="<?php echo $config['dir_core']; ?>common.js"></script>
  <script src="<?php echo $config['dir_libraries']; ?>quick.box.js"></script>
  <script>
    var aCF = {
      'sWarning' : '<?php echo $lang['cf_no_word']; ?>',
      'sEmail' : '<?php echo $lang['cf_mail']; ?>',
      'sInt' : '<?php echo $lang['cf_wrong_value']; ?>'
    };
  </script>
  <?php displayAlternateTranslations( ); ?>
</head>
<body<?php if( isset( $aData['iPage'] ) && is_numeric( $aData['iPage'] ) ) echo ' id="page'.$aData['iPage'].'"'; elseif( isset( $aData['iProduct'] ) && is_numeric( $aData['iProduct'] ) ) echo ' id="product'.$aData['iProduct'].'"'; ?>>
<ul id="skiplinks">
  <li><a href="#menu2" tabindex="1"><?php echo $lang['Skip_to_main_menu']; ?></a></li>
  <li><a href="#content" tabindex="2"><?php echo $lang['Skip_to_content']; ?></a></li>
  <?php 
    if( isset( $config['page_search'] ) && is_numeric( $config['page_search'] ) && isset( $oPage->aPages[$config['page_search']] ) ){ ?>
  <li><a href="#search" tabindex="3"><?php echo $lang['Skip_to_search']; ?></a></li>
  <?php } ?>
</ul>

<div id="container">
  <div id="header">
    <div id="head1"><?php // first top menu starts here ?>
      <div class="container">
        <?php echo $oPage->throwMenu( 1, $iContent, 0 ); // content of top menu first ?>
      </div>
    </div>
    <div id="head2"><?php // banner, logo and slogan starts here ?>
      <div class="container">
        <div id="logo"><?php // logo and slogan ?>
          <div id="title"><a href="./" tabindex="4"><?php echo $config['logo']; ?></a></div>
          <div id="slogan"><?php echo $config['slogan']; ?></div>
        </div>
      </div>
    </div>
    <div id="head3"><?php // second top menu starts here ?>
      <div class="container">
        <?php echo $oPage->throwMenu( 2, $iContent, 0 ); // content of top menu second ?>
      </div>
    </div>
  </div>
  <div id="body"<?php if( isset( $config['this_is_order_page'] ) ) echo ' class="order"'; elseif( isset( $config['this_is_basket_page'] ) ) echo ' class="basket-page"'; ?>>
    <div class="container">
      <div id="column"><?php 
        if( !isset( $config['this_is_order_page'] ) ){ // left column with left menu ?><?php
          if( isset( $config['page_search'] ) && is_numeric( $config['page_search'] ) && isset( $oPage->aPages[$config['page_search']] ) ){ // search form starts here ?>
            <a id="search" tabindex="-1"></a>
            <form method="post" action="<?php echo $oPage->aPages[$config['page_search']]['sLinkName']; ?>" id="searchForm">
              <fieldset>
                <legend><?php echo $lang['Search_form']; ?></legend>
                <span><label for="searchField"><?php echo $lang['search']; ?></label><input type="text" size="15" name="sPhrase" id="searchField" value="<?php echo $sPhrase; ?>" class="input" maxlength="100" accesskey="1" /></span>
                <em><input type="submit" value="<?php echo $lang['search']; ?> &raquo;" class="submit" /></em>
              </fieldset>
            </form><?php
          }  // search form ends here ?><?php 
          echo $oPage->throwMenu( 3, $iContent, 1, true ); // content of left menu ?><?php 
        }?>       
      </div>
      <div id="content">
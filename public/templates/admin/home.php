<?php
if( !defined( 'ADMIN_PAGE' ) )
  exit( 'Script by OpenSolution.org' );

$oFile = FilesAdmin::getInstance( );
$oPage = PagesAdmin::getInstance( );
$oProduct = ProductsAdmin::getInstance( );
$oOrder = new OrdersAdmin( );

$aSelectMenu['bDashboard'] = true;
require_once DIR_TEMPLATES.'admin/_header.php'; // include headers
require_once DIR_TEMPLATES.'admin/_menu.php'; // include menu
?>
<h1><?php echo $lang['Dashboard']; ?></h1>
<div class="mainPage">
  <table cellspacing="0" id="mainPage">
    <?php if( !empty( $sNotifications ) ){ ?>
      <tr>
        <td id="notifications" colspan="2">
          <div id="alerts" class="homeBox">
            <h2><?php echo $lang['Notifications']; ?></h2>
            <ul>
              <?php echo $sNotifications; ?>
            </ul>
            <div class="clear"></div>
          </div>
        </td>
      </tr>
    <?php } ?>
    <tr>
      <td id="eventsLinks">
        <div id="newEventsBox" class="homeBox">
          <h2><?php echo $lang['New_events']; ?></h2>

          <div id="tabs">
            <ul id="tabsNames">
              <!-- tabs start -->
              <li class="tabProducts"><a href="#more" onclick="displayTab( 'tabProducts' )"><?php echo $lang['Products']; ?></a></li>
              <li class="tabPages"><a href="#more" onclick="displayTab( 'tabPages' )"><?php echo $lang['Pages']; ?></a></li>
              <li class="tabFiles"><a href="#more" onclick="displayTab( 'tabFiles' )"><?php echo $lang['Files']; ?></a></li>
              <li class="tabOrders"><a href="#more" onclick="displayTab( 'tabOrders' )"><?php echo $lang['Orders']; ?></a></li>
              <!-- tabs end -->
            </ul>
            <div id="tabsForms">
              <!-- tabs list start -->
              <div class="tab" id="tabProducts">
                <?php echo $oProduct->listLastProducts( ); ?>
              </div>
              <div class="tab" id="tabPages">
                <?php echo $oPage->listLastPages( ); ?>
              </div>
              <div class="tab" id="tabFiles">
                <?php echo $oFile->listLastFiles( ); ?>
              </div>
              <div class="tab" id="tabOrders">
                <?php echo $oOrder->listLastOrders( ); ?>
              </div>
            </div>
          </div>
          <script type="text/javascript">
          AddOnload( getTabsArray );
          AddOnload( checkSelectedTab );
          <?php echo javascriptLanguages( ); ?>
          firstNotice( );

          </script>
        </div>

        <div id="addInfo" class="homeBox">
          <h2><?php echo $lang['Additional_info']; ?></h2>
          <div><iframe src="http://opensolution.org/additional-info,<?php echo $config['admin_lang']; ?>,<?php echo $config['version']; ?>.html?sUrl=<?php echo $_SERVER['HTTP_HOST']; ?>&amp;sVer=Quick.Cart"></iframe></div>
        </div>
      
      </td>
      <td id="news">
        <div id="newsBox" class="homeBox">
          <h2><?php echo $lang['News_from_os']; ?></h2>
          <!-- WE DONT RECOMMEND TO DELETE THIS IFRAME, ALL BUG FIXES NEWS ETC. FROM OPENSOLUTION.ORG WILL DISAPPEAR -->
          <iframe src="http://opensolution.org/news,<?php echo $config['admin_lang']; ?>,<?php echo $config['version']; ?>.html?sUrl=<?php echo $_SERVER['HTTP_HOST']; ?>&amp;sVer=Quick.Cart"></iframe>
        </div>
      </td>
    </tr>
  </table>
</div>
<?php
require_once DIR_TEMPLATES.'admin/_footer.php'; // include footer
?>
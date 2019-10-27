<?php
// Read the license before changing the footer: www.opensolution.org/licenses.html
if( !defined( 'CUSTOMER_PAGE' ) )
  exit;
?>
        <div id="options"><div class="print"><a href="javascript:window.print();"><?php echo $lang['print']; ?></a></div><div class="back"><a href="javascript:history.back();">&laquo; <?php echo $lang['back']; ?></a></div></div>
      </div>
    </div>
  </div>
  <div id="foot"><?php // footer starts here ?>
    <div class="container">
      <div id="copy"><?php echo $config['foot_info']; ?></div><!-- copyrights here -->
      <div class="foot" id="powered"><a href="http://opensolution.org/"><img src="<?php echo $config['dir_skin']; ?>img/quick.cart.png" alt="Script logo" width="187" height="15" /></a></div>
    </div>
  </div>
</div>
</body>
</html>
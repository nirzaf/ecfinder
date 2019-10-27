<?php
if( !defined( 'ADMIN_PAGE' ) )
  exit( 'Script by OpenSolution.org' );

$oFile = FilesAdmin::getInstance( );
$oPage = PagesAdmin::getInstance( );
$aSelectMenu['bPages'] = true;
if( isset( $_POST['sOption'] ) ){
  $oPage->savePages( $_POST );
  header( 'Location: '.str_replace( '&amp;', '&', $_SERVER['REQUEST_URI'] ).( strstr( $_SERVER['REQUEST_URI'], 'sOption=' ) ? null : '&sOption=' ) );
  exit;
}

require_once DIR_TEMPLATES.'admin/_header.php'; // include headers
require_once DIR_TEMPLATES.'admin/_menu.php'; // include menu

if( isset( $sOption ) ){
  echo '<div id="msg">'.$lang['Operation_completed'].'</div>';
}
?>
<h1><?php echo $lang['Pages']; ?><a href="<?php echo $config['manual_link']; ?>instruction#1.3" title="<?php echo $lang['Manual']; ?>" target="_blank"></a></h1>
<form action="" method="get" id="search">
  <fieldset>
    <input type="hidden" name="p" value="<?php echo $p; ?>" />
    <input type="hidden" name="sSort" value="<?php echo $sSort; ?>" />
    <input type="text" name="sPhrase" value="<?php echo $sPhrase; ?>" class="input" size="50" />
    <input type="submit" value="<?php echo $lang['search']; ?> &raquo;" />
  </fieldset>
</form>
<script type="text/javascript">
  AddOnload( function(){ gEBI( 'search' ).sPhrase.focus( ); } );
</script>
<?php
// get list of pages
$content = ( isset( $sPhrase ) && !empty( $sPhrase ) ) ? $oPage->listPagesAdminSearch( $sPhrase ) : $oPage->listPagesAdmin( ); 

// display pages in the table list
if( isset( $content ) ){
  ?>
  <script type="text/javascript">
  var aDelUrl = Array( '?p=pages-delete&iPage=', '?p=pages-delete&bWithoutFiles=true&iPage=' );
  var bDeleteUnusedFiles = "<?php echo $config['delete_unused_files']; ?>";
  </script>
  <form action="?p=<?php echo $p; ?>&amp;sPhrase=<?php echo $sPhrase; ?>&amp;sSort=<?php echo $sSort; ?>" method="post">
    <fieldset>
      <table id="list" class="pages" cellspacing="1">
        <thead>
          <tr class="save">
            <th colspan="5">
              <input type="submit" name="sOption" value="<?php echo $lang['save']; ?> &raquo;" />
            </th>
          </tr>
          <tr>
            <td class="id"><a href="?p=<?php echo $p; ?>&amp;sSort=id&amp;sPhrase=<?php echo $sPhrase; ?>"><?php echo $lang['Id']; ?></a></td>
            <td class="name"><a href="?p=<?php echo $p; ?>&amp;sSort=name&amp;sPhrase=<?php echo $sPhrase; ?>"><?php echo $lang['Name']; ?></a></td>
            <td class="position"><a href="?p=<?php echo $p; ?>&amp;sPhrase=<?php echo $sPhrase; ?>"><?php echo $lang['Position']; ?></a></td>
            <td class="status"><?php echo $lang['Status']; ?></td>
            <td class="options">&nbsp;</td>
          </tr>
        </thead>
        <tfoot>
          <tr class="save">
            <th colspan="5">
              <input type="submit" name="sOption" value="<?php echo $lang['save']; ?> &raquo;" />
            </th>
          </tr>
        </tfoot>
        <tbody>
        <?php echo $content; ?>
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
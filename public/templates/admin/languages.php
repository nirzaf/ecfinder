<?php
if( !defined( 'ADMIN_PAGE' ) )
  exit( 'Script by OpenSolution.org' );

$oFile = FilesAdmin::getInstance( );
$oPage = PagesAdmin::getInstance( );

if( isset( $_POST['sOption'] ) && isset( $sLanguage ) && strlen( $sLanguage ) == 2 ){
  saveVariables( $_POST, DIR_LANG.$sLanguage.'.php', 'lang' );
  header( 'Location: '.$_SERVER['PHP_SELF'].'?p='.$p.'&sOption=save&sLanguage='.$sLanguage );
  exit;
}

$aSelectMenu['bTools'] = true;
require_once DIR_TEMPLATES.'admin/_header.php'; // include headers
require_once DIR_TEMPLATES.'admin/_menu.php'; // include menu

if( isset( $sOption ) ){
  echo '<div id="msg">'.$lang['Operation_completed'].'</div>';
}
?>
<h1><?php echo $lang['Languages'].( isset( $sLanguage ) ? ' '.$sLanguage : null ); ?><a href="<?php echo $config['manual_link']; ?>instruction#1.6" title="<?php echo $lang['Manual']; ?>" target="_blank"></a></h1>
<?php
// get list of languages

if( $p == 'lang-translations' && isset( $sLanguage ) && strlen( $sLanguage ) == 2 )
  $sVariables = listLangVariables( $sLanguage );
else
  $sLangs = listLanguages( );

// display languages in the table list
if( isset( $sLangs ) ){
  ?>
  <table id="list" class="languages" cellspacing="1">
    <thead>
      <tr>
        <td class="name"><?php echo $lang['Name']; ?></td>
        <td class="options">&nbsp;</td>
      </tr>
    </thead>
    <tbody>
      <?php echo $sLangs; ?>
    </tbody>
  </table>
  <?php
}
elseif( isset( $sVariables ) ){
  ?>
  <form action="?p=<?php echo $p; ?>&amp;sLanguage=<?php echo $sLanguage; ?>" method="post" id="mainForm">
    <fieldset id="type2">
      <table cellspacing="1" class="mainTable" id="translations">
        <thead>
          <tr class="save">
            <th colspan="2">
              <input type="submit" value="<?php echo $lang['save']; ?> &raquo;" name="sOption" />
            </th>
          </tr>
        </thead>
        <tfoot>
          <tr class="save">
            <th colspan="2">
              <input type="submit" value="<?php echo $lang['save']; ?> &raquo;" name="sOption" />
            </th>
          </tr>
        </tfoot>
        <tbody>
          <tr class="l0 title">
            <th colspan="2"><?php echo $lang['Translation_visible_all']; ?></th>
          </tr>
          <?php echo $sVariables; ?>
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
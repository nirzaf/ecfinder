<?php
if( !defined( 'ADMIN_PAGE' ) )
  exit( 'Script by OpenSolution.org' );

$oFile = FilesAdmin::getInstance( );
$oPage = PagesAdmin::getInstance( );

if( isset( $_POST['sOption'] ) ){
  if( !isset( $_POST['clone'] ) )
    $_POST['clone'] = null;
  addLanguage( $_POST['language'], $_POST['language_from'], $_POST['clone'] );
  header( 'Location: '.$_SERVER['PHP_SELF'].'?p=lang-list&sOption=save' );
  exit;
}

$aSelectMenu['bTools'] = true;
require_once DIR_TEMPLATES.'admin/_header.php'; // include headers
require_once DIR_TEMPLATES.'admin/_menu.php'; // include menu
?>
<h1><?php echo $lang['New_language']; ?><a href="<?php echo $config['manual_link']; ?>instruction#1.6" title="<?php echo $lang['Manual']; ?>" target="_blank"></a></h1>
<form action="?p=<?php echo $p; ?>" method="post" enctype="multipart/form-data" id="mainForm">
  <fieldset id="type2">
    <table cellspacing="1" class="mainTable" id="language">
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
        <tr class="l0">
          <th><label for="language-field"><?php echo $lang['Language']; ?> <span>(<?php echo $lang['required']; ?>)</span></label></th>
          <td><input type="text" name="language" id="language-field" value="" class="input" size="3" maxlength="2" data-form-check="required" tabindex="1" /></td>
        </tr>
        <tr class="l1">
          <th><?php echo $lang['Upload_language_file']; ?></th>
          <td><input type="file" name="aFile" value="" class="input" size="30" /><span class="link"><?php echo $lang['Upload_language_file_info']; ?></span></td>
        </tr>
        <tr class="l0">
          <th><?php echo $lang['Use_language']; ?></th>
          <td><select name="language_from"><?php echo throwLangSelect( $config['default_lang'] ); ?></select></td>
        </tr>
        <tr class="l1">
          <th><?php echo $lang['Clone_data_from_basic_language']; ?></th>
          <td><input type="checkbox" name="clone" value="1" /></td>
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
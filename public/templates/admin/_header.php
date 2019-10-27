<?php
if( !defined( 'ADMIN_PAGE' ) )
  exit;
?><!DOCTYPE HTML>
<html lang="<?php echo $config['admin_lang']; ?>">
<head>
  <title><?php echo $lang['Admin'].' - '.$config['title']; ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $config['charset']; ?>" />
  <meta name="Robots" content="noindex, nofollow, noarchive" />
  <meta name="Description" content="" />
  <meta name="Keywords" content="" />
  <meta name="Generator" content="Quick.Cart v<?php echo $config['version']; ?>" />

  <link rel="stylesheet" href="<?php echo $config['dir_templates']; ?>admin/style.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo $config['dir_plugins'] ?>valums-file-uploader/client/fileuploader.css" type="text/css" />

  <script src="<?php echo $config['dir_plugins']; ?>jquery.min.js"></script>
  <script src="<?php echo $config['dir_core']; ?>common.js"></script>
  <script src="<?php echo $config['dir_core']; ?>common-admin.js"></script>
  <script src="<?php echo $config['dir_libraries']; ?>quick.form.js"></script>
  <script src="<?php echo $config['dir_plugins']; ?>lert.js"></script>
  <script>
    var aCF = {
      'sWarning' : '<?php echo $lang['cf_no_word']; ?>',
      'sEmail' : '<?php echo $lang['cf_mail']; ?>',
      'sTooShort' : '<?php echo $lang['cf_txt_to_short']; ?>',
      'sToSmallValue' : '<?php echo $lang['cf_to_small_value']; ?>',
      'sFloat' : '<?php echo $lang['cf_wrong_value']; ?>',
      'sInt' : '<?php echo $lang['cf_wrong_value']; ?>'
    };

    var delShure = "<?php echo $lang['Operation_sure_delete']; ?>";
    var confirmShure = "<?php echo $lang['Operation_sure']; ?>";
    var yes = "<?php echo $lang['yes']; ?>";
    var no = "<?php echo $lang['no']; ?>";
    var Cancel = "<?php echo $lang['Cancel']; ?>";
    var Yes = "<?php echo $lang['Yes']; ?>";
    var YesWithoutFiles = "<?php echo $lang['Yes_without_files']; ?>";
    var aDelTxt = Array( Yes, YesWithoutFiles );
    var sVersion = '<?php echo VERSION; ?>';
    var sExtNotice = '<?php echo $config['admin_lang'] == 'pl' ? 'Ta funkcjonalność nie występuje w tym narzędziu.<br />Opcja ta i wiele innych znajduje się w narzędziu <a href="http://opensolution.org/?p=Quick.Cart_editions" target="_blank">Quick.Cart.Ext</a>' : 'This functionality has extended version or is not included in this script.<br />You can get this functionality and much more if you will buy <a href="http://opensolution.org/?p=Quick.Cart_editions" target="_blank">Quick.Cart.Ext</a>'; ?>';
    var sExtDemo = '<?php echo $config['admin_lang'] == 'pl' ? 'Zobacz demo »' : 'See demo »'; ?>';
  </script>
</head>
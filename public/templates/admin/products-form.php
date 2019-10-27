<?php
if( !defined( 'ADMIN_PAGE' ) )
  exit( 'Script by OpenSolution.org' );

$oFile = FilesAdmin::getInstance( );
$oPage = PagesAdmin::getInstance( );
$oProduct = ProductsAdmin::getInstance( );

if( isset( $_POST['sName'] ) ){
  $iProduct = $oProduct->saveProduct( $_POST );
  if( isset( $_POST['sOptionList'] ) )
    header( 'Location: '.$_SERVER['PHP_SELF'].'?p=products-list&sOption=save' );
  elseif( isset( $_POST['sOptionAddNew'] ) )
    header( 'Location: '.$_SERVER['PHP_SELF'].'?p=products-form&sOption=save' );
  else
    header( 'Location: '.$_SERVER['PHP_SELF'].'?p=products-form&sOption=save&iProduct='.$iProduct );
  exit;
}

$aSelectMenu['bProducts'] = true;
require_once DIR_TEMPLATES.'admin/_header.php'; // include headers
require_once DIR_TEMPLATES.'admin/_menu.php'; // include menu

if( isset( $iProduct ) && is_numeric( $iProduct ) ){
  $aData = $oProduct->throwProduct( $iProduct );
}

if( isset( $sOption ) ){
  echo '<div id="msg">'.$lang['Operation_completed'].'</div><script type="text/javascript">var bDone = true;</script>';
}

if( isset( $aData ) && is_array( $aData ) ){
  if( isset( $aData['sDescriptionShort'] ) )
    $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );
  if( isset( $aData['sDescriptionFull'] ) && !isset( $aData['bDescriptionFromFile'] ) )
    $aData['sDescriptionFull'] = changeTxt( $aData['sDescriptionFull'], 'nlNds' );
  $oFile->generateCache( $iProduct, true );
  $sFilesList = $oFile->listAllLinkFiles( $iProduct );
}
else{
  $iProduct = null;
  $sFilesList = null;
}

if( !isset( $sFilesList ) )
  $sFilesList = '<div id="msg" class="error">'.$lang['Data_not_found'].'</div>';

$sSize1Select = throwSelectFromArray( $config['images_sizes'], $config['products_default_image_size_list'] );
$sSize2Select = $config['display_thumbnail_2'] === true ? throwSelectFromArray( $config['images_sizes'], $config['products_default_image_size_details'] ) : null;
$sPhotoTypesSelect = throwSelectFromArray( $aPhotoTypes, $config['products_default_image_location'] );

?>
<div id="tabsDisplayLinks">
  <a href="#more" onclick="displayTabs( );" id="tabsHide"><?php echo $lang['Hide_tabs']; ?></a>
  <a href="#more" onclick="displayTabs( true );" id="tabsShow"><?php echo $lang['Display_tabs']; ?></a>
</div>
<h1><?php echo ( isset( $_GET['iProduct'] ) && is_numeric( $_GET['iProduct'] ) ) ? $lang['Products_form'] : $lang['New_product']; ?><a href="<?php echo $config['manual_link']; ?>instruction#1.12" title="<?php echo $lang['Manual']; ?>" target="_blank"></a><?php if( isset( $aData['iProduct'] ) ){ ?><a href="./<?php echo $aData['sLinkName'].( ( $config['language_in_url'] !== true ) ? '&amp;sLang='.LANGUAGE : null ); ?>" target="_blank" class="preview"></a><?php } ?></h1>

<form action="?p=<?php echo $p; ?>&amp;iProduct=<?php if( isset( $aData['iProduct'] ) )echo $aData['iProduct']; ?>" name="form" enctype="multipart/form-data" method="post" id="mainForm">
  <fieldset id="type1">
    <input type="hidden" name="iProduct" value="<?php if( isset( $aData['iProduct'] ) ) echo $aData['iProduct']; ?>" />
    <table class="mainTable" id="product">
      <thead>
        <tr class="save">
          <th colspan="2">
            <input type="submit" value="<?php echo $lang['save']; ?> &raquo;" name="sOption" />
            <input type="submit" value="<?php echo $lang['save_add_new']; ?> &raquo;" name="sOptionAddNew" />
            <input type="submit" value="<?php echo $lang['save_list']; ?> &raquo;" name="sOptionList" />
          </th>
        </tr>
      </thead>
      <tfoot>
        <tr class="save">
          <th colspan="2">
            <input type="submit" value="<?php echo $lang['save']; ?> &raquo;" name="sOption" />
            <input type="submit" value="<?php echo $lang['save_add_new']; ?> &raquo;" name="sOptionAddNew" />
            <input type="submit" value="<?php echo $lang['save_list']; ?> &raquo;" name="sOptionList" />
          </th>
        </tr>
      </tfoot>
      <tbody><!-- name start -->
        <tr class="l0">
          <td>
            <label for="sName"><?php echo $lang['Name']; ?> <span>(<?php echo $lang['required']; ?>)</span></label>
          </td>
          <th rowspan="9" class="tabs">
            <div id="tabs">
              <ul id="tabsNames">
                <!-- tabs start -->
                <li class="tabOptions"><a href="#more" onclick="displayTab( 'tabOptions' )"><?php echo $lang['Options']; ?></a></li>
                <li class="tabSeo"><a href="#more" onclick="displayTab( 'tabSeo' )"><?php echo $lang['SEO']; ?></a></li>
                <li class="tabAddFiles"><a href="#more" onclick="displayTab( 'tabAddFiles' )"><?php echo $lang['Add_files']; ?></a></li>
                <li class="tabAddedFiles"><a href="#more" onclick="displayTab( 'tabAddedFiles' )"><?php echo $lang['Files']; ?></a></li>
                <li class="tabAdvanced"><a href="#more" onclick="displayTab( 'tabAdvanced' )"><?php echo $lang['Advanced']; ?></a></li>
                <!-- tabs end -->
              </ul>
              <div id="tabsForms">
                <!-- tabs list start -->
                <table class="tab" id="tabOptions">
                  <tr>
                    <td><?php echo $lang['Status']; ?></td>
                    <td><?php echo throwYesNoBox( 'iStatus', isset( $aData['iStatus'] ) ? $aData['iStatus'] : 1 ); ?></td>
                  </tr>
                  <tr>
                    <td><?php echo $lang['Position']; ?></td>
                    <td><input type="text" name="iPosition" value="<?php echo isset( $aData['iPosition'] ) ? $aData['iPosition'] : 0; ?>" class="inputr" size="3" maxlength="3" /></td>
                  </tr>
                  <tr>
                    <td><?php echo $lang['Product_available']; ?></td>
                    <td><input type="text" name="sAvailable" value="<?php if( isset( $aData['sAvailable'] ) ) echo $aData['sAvailable']; ?>" class="input" size="40" /></td>
                  </tr>
                  <tr>
                    <td><label for="oPageParent"><?php echo $lang['Pages']; ?> <span>(<?php echo $lang['required']; ?>)</span></label></td>
                    <td>
                      <div id="pageParentSearch"><input type="text" name="sPageParentPhrase" id="pageParentPhrase" value="<?php echo $lang['search']; ?>" class="input" size="32" onkeyup="listOptionsSearch( this, 'oPageParent', 'pageParent2' )" onfocus="if(this.value=='<?php echo $lang['search'] ?>')this.value=''" /></div>
                      <span id="pageParentCtn"><select name="aPages[]" size="15" multiple="multiple" data-form-check="required" id="oPageParent" tabindex="5" onclick="cloneClick( this, 'pageParent2' )"><?php echo $oPage->throwProductsPagesSelectAdmin( isset( $aData['aCategories'] ) ? $aData['aCategories'] : null ); ?></select></span>
                      <span id="pageParent2Ctn"></span>
                    </td>
                  </tr>
                  <!-- tab options -->
                </table>
                <script type="text/javascript">
                AddOnload( function(){cacheSelect('oPageParent','pageParent2','pageParent2Ctn')} );
                var sSize1Select = '<?php echo $sSize1Select ?>';
                <?php if( isset( $sSize2Select ) ) echo 'var sSize2Select = \''.$sSize2Select.'\';'; ?>
                var sPhotoTypesSelect = '<?php echo $sPhotoTypesSelect.'<option value="3" class="disabled">'.$lang['Gallery'].'</option><option value="0" class="disabled">'.$lang['Hidden'].'</option>' ?>';
                </script>

                <div class="tab" id="tabAddFiles">
                  <!-- tab add-files start -->
                  <script type="text/javascript" src="<?php echo $config['dir_plugins'] ?>valums-file-uploader/client/fileuploader.min.js"></script>
                  <div id="fileUploader">		
                  </div>
                  <div id="attachingFilesInfo"><?php echo $lang['Choose_files_to_attach'] ?></div>
                  <script type="text/javascript">
                  <!--
                    var sPhpSelf = '<?php echo $_SERVER['PHP_SELF']; ?>';
                    function createUploader(){            
                      var uploader = new qq.FileUploader({
                        element: document.getElementById('fileUploader'),
                        action: sPhpSelf+'?p=files-upload',
                        inputName: 'sFileName',
                        uploadButtonText: '<?php echo $lang['Files_from_computer'] ?>',
                        cancelButtonText: '<?php echo $lang['Cancel'] ?>',
                        failUploadText: '<?php echo $lang['Upload_failed'] ?>',
                        onComplete: function(id, fileName, response){
                          if (!response.success){
                            return;    
                          }
                          if( uploader.getInProgress() == 0 )
                            refreshFiles( );
                          if( response.size_info ){
                            qq.addClass(uploader._getItemByFileId(id), 'qq-upload-maxdimension');
                            uploader._getItemByFileId(id).innerHTML += '<?php echo $lang['Image_over_max_dimension']; ?>';
                          }
                        }
                      });           
                    }
                    AddOnload( createUploader );
                  //-->
                  </script>

                  <?php if( isset( $sFilesForm ) ) echo $sFilesForm; ?>
                  <div id="filesFromDirList">
                    <?php echo $oFile->listFilesInDir( ); ?>
                  </div>
                <!-- tab add-files end -->
                </div>

                <div class="tab" id="tabAddedFiles">
                  <!-- tab added-files start -->
                  <?php echo $sFilesList; ?>
                  <!-- tab added-files end -->
                </div>

                <table class="tab" id="tabSeo">
                  <tr>
                    <td><?php echo $lang['Page_title']; ?></td>
                    <td><input type="text" name="sNameTitle" value="<?php if( isset( $aData['sNameTitle'] ) ) echo $aData['sNameTitle']; ?>" class="input" size="75" maxlength="60" /></td>
                  </tr>
                  <tr>
                    <td><?php echo $lang['Url_name']; ?></td>
                    <td><input type="text" name="sNameUrl" value="<?php if( isset( $aData['sNameUrl'] ) ) echo $aData['sNameUrl']; ?>" class="input" size="75" /></td>
                  </tr>
                  <tr>
                    <td><?php echo $lang['Meta_description']; ?></td>
                    <td><input type="text" name="sMetaDescription" value="<?php if( isset( $aData['sMetaDescription'] ) ) echo $aData['sMetaDescription']; ?>" class="input" size="75" maxlength="160" /></td>
                  </tr>
                  <!-- tab seo -->
                </table>

                <table class="tab" id="tabAdvanced">
                  <tr <?php if( $config['display_advanced_options'] !== true ){ ?>style="display:none;"<?php } ?>>
                    <td><?php echo $lang['Theme']; ?></td>
                    <td><select name="sTheme"><?php echo throwThemesSelect( isset( $aData['sTheme'] ) ? $aData['sTheme'] : null, true ); ?></select></td>
                  </tr>
                  <tr>
                    <td><?php echo $lang['Key_words']; ?></td>
                    <td><input type="text" name="sMetaKeywords" value="<?php if( isset( $aData['sMetaKeywords'] ) ) echo $aData['sMetaKeywords']; ?>" class="input" size="75" maxlength="255" /></td>
                  </tr>
                  <!-- tab advanced -->
                </table>
                <!-- tabs list end -->
              </div>
            </div>
          </th>
        </tr>
        <tr class="l1">
          <td>
            <input type="text" name="sName" id="sName" value="<?php if( isset( $aData['sName'] ) ) echo $aData['sName']; ?>" class="input" style="width:100%;" data-form-check="required" accesskey="1" tabindex="1" />
          </td>
        </tr>
        <!-- name end -->
        <!-- price start -->
        <tr class="l0">
          <td>
            <?php echo $lang['Price']; ?>
          </td>
        </tr>
        <tr class="l1">
          <td>
            <input type="text" name="mPrice" value="<?php if( isset( $aData['mPrice'] ) ) echo $aData['mPrice']; ?>" class="inputr" size="10" tabindex="2" /> <?php echo $config['currency_symbol']; ?>
          </td>
        </tr>
        <!-- price end -->
        <!-- description_short start -->
        <tr class="l0">
          <td>
            <script type="text/javascript">
            <?php
            if( !empty( $aData['sDescriptionShort'] ) ){
              echo 'AddOnload( function(){displayShortDescription(false)} );';
            }
            ?>
            </script>
            <?php echo $lang['Short_description']; ?> <a href="#" onclick="displayShortDescription(true);return false;" class="plus"><img src="<?php echo $config['dir_templates']; ?>admin/img/rolldown.png" alt="" /> <span id="displaySD"><?php echo $lang['display'] ?></span><span id="hideSD"><?php echo $lang['hide'] ?></span></a>
          </td>
        </tr>
        <tr class="l1" id="shortDescription">
          <td>
            <?php echo htmlEditor( 'sDescriptionShort', '120', '100%', isset( $aData['sDescriptionShort'] ) ? $aData['sDescriptionShort'] : null, 3 ); ?>
          </td>
        </tr>
        <!-- description_short end -->
        <!-- description_full start -->
        <tr class="l0">
          <td>
            <?php echo $lang['Full_description']; ?>
          </td>
        </tr>
        <tr class="l1">
          <td>
            <?php echo htmlEditor( 'sDescriptionFull', '280', '100%', isset( $aData['sDescriptionFull'] ) ? $aData['sDescriptionFull'] : null, 4 ); ?>
          </td>
        </tr>
        <!-- description_full end -->
        <tr class="end">
          <td>&nbsp;</td>
        </tr>
      </tbody>
    </table>
  </fieldset>
</form>
<script>
AddOnload( getTabsArray );
AddOnload( checkSelectedTab );
<?php if( !isset( $aData['sName'] ) ){ ?>
  AddOnload( function(){ gEBI( 'mainForm' ).sName.focus( ); } );
<?php } ?>
$(function(){
  $( "#mainForm" ).quickform();
});
</script>
<?php
require_once DIR_TEMPLATES.'admin/_footer.php'; // include footer
?>
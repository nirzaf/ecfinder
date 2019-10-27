<?php
/**
* Function returns editor
* @return string
* @param  string  $sName
* @param  int     $iH
* @param  int     $iW
* @param  string  $sContent
* @param  int     $iTab
*/
function htmlEditor( $sName = 'sDescriptionFull', $iH = '300', $iW = '100%', $sContent = '', $iTab = null ){
  $sEdit = '';
  if( !strstr( $iH, '%' ) )
    $iH .= 'px';
  if( !strstr( $iW, '%' ) )
    $iW .= 'px';

  if( WYSIWYG === true ){
    if( empty( $sEdit ) ){
      if( !defined( 'WYSIWYG_START' ) ){
        define( 'WYSIWYG_START', true );
        $sEdit .= '<script type="text/javascript" src="'.$GLOBALS['config']['dir_plugins'].'tinymce/tinymce.min.js"></script>';
      }
      $sEdit .= '<script type="text/javascript">
      tinymce.init({
          selector: "textarea#'.$sName.'",
          toolbar : "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | undo redo | link unlink cleanup removeformat | about fullscreen code",
          menubar : false,
          plugins: ["link, code, fullscreen, tabindex"],
          entity_encoding : "raw",
          gecko_spellcheck : true,
          setup: function(editor) {
            editor.addButton("about", {
              title: "About",
              icon: "help",
              onclick: function() {
                editor.windowManager.open({title:"About",url:editor.editorManager.baseURL+"/plugins/about.htm",width:480,height:300,inline:true})
              }

            });
          }
       });
      </script>';
    }
  }
  $sEdit .= '<textarea name="'.$sName.'" id="'.$sName.'" rows="20" cols="60" style="width:'.$iW.';height:'.$iH.';"'.( isset( $iTab ) ? ' tabindex="'.$iTab.'"' : null ).'>'.$sContent.'</textarea>';

  return $sEdit;
} // end function htmlEditor

/**
* Returns javascript languages
* @return string
*/
function javascriptLanguages( ){
  return base64_decode( ( $GLOBALS['config']['admin_lang']=='pl' ? 'dmFyIENsb3NlID0gJ1phbWtuaWonOyB2YXIgc0ZpcnN0Tm90aWNlID0gJ0tvcnp5c3RhasSFYyB6IFF1aWNrLkNhcnQgYWtjZXB0dWplc3ogPGEgaHJlZj0iaHR0cDovL29wZW5zb2x1dGlvbi5vcmcvbGljZW5jamUuaHRtbD9ub3RpY2U9IiB0YXJnZXQ9Il9ibGFuayI+bGljZW5jasSZPC9hPi4nOw==' : 'dmFyIENsb3NlID0gJ0Nsb3NlJzsgdmFyIHNGaXJzdE5vdGljZSA9ICdVc2Ugb2YgUXVpY2suQ2FydCBjb25zdGl0dXRlcyB5b3VyIGFjY2VwdGFuY2UgdG8gdGhlIDxhIGhyZWY9Imh0dHA6Ly9vcGVuc29sdXRpb24ub3JnL2xpY2Vuc2VzLmh0bWw/bm90aWNlPSIgdGFyZ2V0PSJfYmxhbmsiPmxpY2Vuc2U8L2E+Lic7' ) );
} // end function javascriptLanguages

?>
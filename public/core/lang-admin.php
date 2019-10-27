<?php
/**
* Returns language variables from $lang array (files in the "lang/" directory)
* @return string
* @param string $sLang
*/
function listLangVariables( $sLang ){
  if( is_file( DIR_LANG.$sLang.'.php' ) ){
    include DIR_LANG.$sLang.'.php';
    $content = null;
    $i = 0;

    foreach( $lang as $aData['sKey'] => $aData['sValue'] ){
      $i++;

      if( $aData['sKey'] == 'Subpage_show_1' )
        $content .= '<tr class="l0 title"><th colspan="2">'.$GLOBALS['lang']['Translation_visible_back_end'].'</th></tr>';

      $aData['iStyle'] = ( $i % 2 ) ? 0: 1;

      $content .= '<tr class="l'.$aData['iStyle'].'"><th>'.$aData['sKey'].'</th><td><input type="text" name="'.$aData['sKey'].'" value="'.preg_replace( '/\|n\|/', '\n', changeTxt( $aData['sValue'], '' ) ).'" class="input" size="80" /></td></tr>';
    }

    if( isset( $content ) ){
      return $content;
    }
  }
} // end function listLangVariables

/**
* Returns array of all available languages
* @return array
*/
function throwLanguages( ){
  $oFFS = FlatFilesSerialize::getInstance( );
  foreach( new DirectoryIterator( DIR_LANG ) as $oFileDir ) {
    $sFileName = $oFileDir->isFile( ) ? $oFFS->throwNameOfFile( $oFileDir->getFilename( ) ) : null;
    if( isset( $sFileName ) && strlen( $sFileName ) == 2 ){
      $aLanguages[$sFileName] = $sFileName;
    }
  } // end foreach

  if( isset( $aLanguages ) ){
    $GLOBALS['aLanguages'] = $aLanguages;
    return $aLanguages;
  }
} // end function throwLanguages

/**
* Lists all language files
* @return string
*/
function listLanguages( ){
  global $lang;
  $content = null;
  $aLanguages = throwLanguages( );
  if( isset( $aLanguages ) && is_array( $aLanguages ) ){
    $iCount = count( $aLanguages );
    $i = 0;
    foreach( $aLanguages as $aData['sName'] ){
      $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
      $content .= '<tr class="l'.$aData['iStyle'].'"><td><a href="?p=lang-translations&amp;sLanguage='.$aData['sName'].'">'.$aData['sName'].'</a></td><td class="options"><a href="?p=lang-translations&amp;sLanguage='.$aData['sName'].'"><img src="'.DIR_TEMPLATES.'admin/img/ico_edit.gif" alt="'.$lang['edit'].'" title="'.$lang['edit'].'" /></a>'.( ( $GLOBALS['config']['default_lang'] == $aData['sName'] || isset( $GLOBALS['config']['hide_language_delete'] ) ) ? null : '<a href="?p=lang-delete&amp;sLanguage='.$aData['sName'].'" onclick="return del( );"><img src="'.DIR_TEMPLATES.'admin/img/ico_del.gif" alt="'.$lang['delete'].'" title="'.$lang['delete'].'"/></a>' ).'</td></tr>';
      $i++;
    } // end foreach

    if( isset( $content ) )
      return $content;
  }
} // end function listLanguages

/**
* Displays all language files in short list
* @return string
* @param string $sLang
*/
function listLanguagesShort( $sLang = null ){
  $content = null;
  $aLanguages = isset( $GLOBALS['aLanguages'] ) ? $GLOBALS['aLanguages'] : throwLanguages( );
  if( isset( $aLanguages ) && is_array( $aLanguages ) ){
    foreach( $aLanguages as $aData['sName'] ){
      $sSelected = ( isset( $sLang ) && $sLang == $aData['sName'] ) ? ' class="selected"' : null;
      $content .= '|<a href="?sLang='.$aData['sName'].'"'.$sSelected.'>'.$aData['sName'].'</a>';
    } // end foreach
    if( isset( $content ) )
      return $content;
  }
} // end function listLanguagesShort

/**
* Lists all language files to menu
* @return string
*/
function listLanguagesMenu( ){
  global $lang, $config;
  $content = null;
  $aLanguages = throwLanguages( );
  if( isset( $aLanguages ) && is_array( $aLanguages ) && count( $aLanguages ) > 1 ){
    $i = 0;
    foreach( $aLanguages as $sLang ){
      $content .= '<li'.( $config['language'] == $sLang ? ' class="selected"' : null ).'><a href="?sLang='.$sLang.'"><span>'.$sLang.'</span></a></li>';
    } // end foreach

    if( isset( $content ) )
      return '<ul id="languages" class="main-menu">'.$content.'</ul>';
  }
} // end function listLanguagesMenu

/**
* Returns language files selection
* @return string
* @param string $sLang
*/
function throwLangSelect( $sLang = null ){
  $content = null;
  $aLanguages = throwLanguages( );
  if( isset( $aLanguages ) && is_array( $aLanguages ) ){
    foreach( $aLanguages as $sFileName ){
      $sSelected = ( isset( $sLang ) && $sLang == $sFileName ) ? ' selected="selected"' : null;
      $content .= '<option value="'.$sFileName.'"'.$sSelected.'>'.$sFileName.'</option>';
    } // end foreach
  }
  return $content;
} // end function throwLangSelect

/**
* Adds language files
* @return void
* @param string $sLanguage
* @param string $sLanguageFrom
* @param int $iCloneData
*/
function addLanguage( $sLanguage, $sLanguageFrom, $iCloneData ){
  if( is_file( DIR_LANG.$sLanguage.'.php' ) || !is_file( DIR_LANG.$sLanguageFrom.'.php' ) )
    return null;

  $oFFS = FlatFilesSerialize::getInstance( );

  copy( DIR_DATABASE.'config/lang_'.$sLanguageFrom.'.php', DIR_DATABASE.'config/lang_'.$sLanguage.'.php' );
  copy( DIR_LANG.$sLanguageFrom.'.php', DIR_LANG.$sLanguage.'.php' );

  if( isset( $_FILES['aFile']['name'] ) && $oFFS->throwExtOfFile( $_FILES['aFile']['name'] ) == 'php' && is_uploaded_file( $_FILES['aFile']['tmp_name'] ) ){
    include DIR_LANG.$sLanguageFrom.'.php';
    $aFile = file( $_FILES['aFile']['tmp_name'] );
    $iCount = count( $aFile );
    for( $i = 0; $i < $iCount; $i++ ){
      foreach( $lang as $sKey => $sValue ){
        $lang[$sKey] = str_replace( Array( "\r", "\n" ), Array( '', '\n' ), $lang[$sKey] );
        if( preg_match( '/lang'."\['".$sKey."'\]".' /', $aFile[$i] ) && strstr( $aFile[$i], '=' ) && strstr( $aFile[$i], ';' ) ){
          $lang[$sKey] = str_replace( '";', '', substr( strstr( rtrim( $aFile[$i] ), '"' ), 1 ) );
          $bFound = true;
        }
      } // end foreach
    } // end for
    if( isset( $bFound ) )
      saveVariables( $lang, DIR_LANG.$sLanguage.'.php', 'lang' );  
  }  

  foreach( new DirectoryIterator( DIR_DATABASE ) as $oFileDir ) {
    $sFileName = $oFileDir->isFile( ) ? $oFFS->throwNameOfFile( $oFileDir->getFilename( ) ) : null;
    if( isset( $sFileName ) && substr( $sFileName, 0, 3 ) == $sLanguageFrom.'_' ){
      if( isset( $iCloneData ) ){
        copy( DIR_DATABASE.$oFileDir->getFilename( ), DIR_DATABASE.$sLanguage.substr( $oFileDir->getFilename( ), 2 ) );
      }
      else{
        if( !is_file( DIR_DATABASE.$sLanguage.substr( $oFileDir->getFilename( ), 2 ) ) ){
          $rFile = fopen( DIR_DATABASE.$sLanguage.substr( $oFileDir->getFilename( ), 2 ), 'w' );
          fwrite( $rFile, '<?php exit; ?>'."\n" );
          fclose( $rFile );
        }
      }
    }
  } // end foreach

  if( isset( $iCloneData ) ){
    foreach( new DirectoryIterator( DIR_DATABASE_PAGES ) as $oFileDir ) {
      $sFileName = $oFileDir->isFile( ) ? $oFFS->throwNameOfFile( $oFileDir->getFilename( ) ) : null;
      if( isset( $sFileName ) && substr( $sFileName, 0, 3 ) == $sLanguageFrom.'_' ){
        copy( DIR_DATABASE_PAGES.$oFileDir->getFilename( ), DIR_DATABASE_PAGES.$sLanguage.substr( $oFileDir->getFilename( ), 2 ) );
      }
    } // end foreach

    foreach( new DirectoryIterator( DIR_DATABASE_PRODUCTS ) as $oFileDir ) {
      $sFileName = $oFileDir->isFile( ) ? $oFFS->throwNameOfFile( $oFileDir->getFilename( ) ) : null;
      if( isset( $sFileName ) && substr( $sFileName, 0, 3 ) == $sLanguageFrom.'_' ){
        copy( DIR_DATABASE_PRODUCTS.$oFileDir->getFilename( ), DIR_DATABASE_PRODUCTS.$sLanguage.substr( $oFileDir->getFilename( ), 2 ) );
      }
    } // end foreach
  }
} // end function addLanguage

/**
* Deletes language files
* @return void
* @param string $sLanguage
*/
function deleteLanguage( $sLanguage ){
  if( is_file( DIR_LANG.$sLanguage.'.php' ) )
    unlink( DIR_LANG.$sLanguage.'.php' );
  if( is_file( DIR_DATABASE.'config/lang_'.$sLanguage.'.php' ) )
    unlink( DIR_DATABASE.'config/lang_'.$sLanguage.'.php' );
  
  $oFFS = FlatFilesSerialize::getInstance( );

  foreach( new DirectoryIterator( DIR_DATABASE ) as $oFileDir ) {
    $sFileName = $oFileDir->isFile( ) ? $oFFS->throwNameOfFile( $oFileDir->getFilename( ) ) : null;
    if( isset( $sFileName ) && substr( $sFileName, 0, 3 ) == $sLanguage.'_' ){
      unlink( DIR_DATABASE.$oFileDir->getFilename( ) );
    }
  } // end foreach

  foreach( new DirectoryIterator( DIR_DATABASE_PAGES ) as $oFileDir ) {
    $sFileName = $oFileDir->isFile( ) ? $oFFS->throwNameOfFile( $oFileDir->getFilename( ) ) : null;
    if( isset( $sFileName ) && substr( $sFileName, 0, 3 ) == $sLanguage.'_' ){
      unlink( DIR_DATABASE_PAGES.$oFileDir->getFilename( ) );
    }
  } // end foreach
} // end function deleteLanguage
?>
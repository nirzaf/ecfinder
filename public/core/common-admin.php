<?php
/**
* Returns skins selection
* @return string
* @param string $sDirCurrent
*/
function throwSkinsSelect( $sDirCurrent = null ){

  if( empty( $sDirCurrent ) ){
    $sFileCurrent = $GLOBALS['config']['skin'];
  }

  foreach( new DirectoryIterator( DIR_TEMPLATES ) as $oFileDir ){
    if( $oFileDir->isDir( ) && !strstr( $oFileDir->getFilename( ), 'admin' ) && !strstr( $oFileDir->getFilename( ), '.' ) ){
      $aDirs[] = $oFileDir->getFilename( );
    }
  } // end foreach

  if( isset( $aDirs ) ){
    $content = null;
    sort( $aDirs );
    $iCount = count( $aDirs );
    for( $i = 0; $i < $iCount; $i++ ){
      $sSelected = ( $sDirCurrent == $aDirs[$i] ) ? ' selected="selected"' : null;
      $content .= '<option value="'.$aDirs[$i].'"'.$sSelected.'>'.$aDirs[$i].'</option>';
    } // end for

    return $content;
  }
} // end function throwCssSelect

/**
* Returns themes selection
* @return string
* @param string $sFileCurrent
* @param bool $bProduct
*/
function throwThemesSelect( $sFileCurrent = null, $bProduct = null ){
  global $config;

  $sDefault = isset( $bProduct ) ? $config['default_products_template'] : $config['default_pages_template'];
  
  foreach( new DirectoryIterator( DIR_TEMPLATES.$GLOBALS['config']['skin'] ) as $oFileDir ){
    $sFileName = $oFileDir->getFilename( );
    if( $oFileDir->isFile( ) && strstr( $sFileName, '.php' ) && $sFileName[0] != '_' ){
      if( $sFileCurrent == $sFileName )
        $bFound = true;
      $aFiles[] = $sFileName;
    }
  } // end foreach

  if( empty( $sFileCurrent ) || !isset( $bFound ) ){
    $sFileCurrent = $sDefault;
  }

  if( isset( $aFiles ) ){
    $content = null;
    sort( $aFiles );
    $iCount = count( $aFiles );
    for( $i = 0; $i < $iCount; $i++ ){
      $sSelected = ( $sFileCurrent == $aFiles[$i] ) ? ' selected="selected"' : null;
      $sValue = ( $aFiles[$i] == $sDefault ) ? null : $aFiles[$i];

      $content .= '<option value="'.$sValue.'"'.$sSelected.'>'.$aFiles[$i].'</option>';
    } // end for

    return $content;
  }
} // end function throwThemesSelect

/**
* Copies files from one directory to another
* @return void
* @param string $sDirFrom
* @param string $sDifTo
*/
function copyDirToDir( $sDirFrom, $sDirTo ){
  if( is_dir( $sDirFrom ) && is_dir( $sDirTo ) ){
    foreach( new DirectoryIterator( $sDirFrom ) as $oFileDir ){
      if( $oFileDir->isFile( ) && !is_file( $sDirTo.$oFileDir->getFilename( ) ) ){
        copy( $sDirFrom.$oFileDir->getFilename( ), $sDirTo.$oFileDir->getFilename( ) );
      }
    } // end foreach
  }
} // end function copyDirToDir

/**
* Saves variables to config
* @return void
* @param array  $aForm
* @param string $sFile
* @param string $sVariable
*/
function saveVariables( $aForm, $sFile, $sVariable = 'config' ){
  if( is_file( $sFile ) && strstr( $sFile, '.php' ) ){
    $aFile = file( $sFile );
    $iCount = count( $aFile );
    $rFile = fopen( $sFile, 'w' );

    for( $i = 0; $i < $iCount; $i++ ){
      foreach( $aForm as $sKey => $sValue ){
        if( preg_match( '/'.$sVariable."\['".$sKey."'\]".' /', $aFile[$i] ) && strstr( $aFile[$i], '=' ) ){
          $sValue = str_replace( '\n', '|n|', changeSpecialChars( $sValue ) );

          $sValue = stripslashes( $sKey == 'logo' ? str_replace( '"', '\'', $sValue ) : str_replace( '"', '&quot;', $sValue ) );
          if( preg_match( '/^(true|false|null)$/', $sValue ) == true ){
            $aFile[$i] = "\$".$sVariable."['".$sKey."'] = ".$sValue.";";
          }
          else
            $aFile[$i] = "\$".$sVariable."['".$sKey."'] = \"".str_replace( '|n|', '\n', $sValue )."\";";
        }
      } // end foreach

      fwrite( $rFile, rtrim( $aFile[$i] ).( $iCount == ( $i + 1 ) ? null : "\r\n" ) );

    } // end for
    fclose( $rFile );
  }
} // end function saveVariables

/**
* Log in and out actions
* @return void
* @param string $p
* @param string $sKey
*/
function loginActions( $p, $sKey = 'bLogged' ){
  global $sLoginInfo, $sLoginPage, $config, $lang;
  $content = null;
  
  if( !isset( $_SESSION[$sKey] ) || $_SESSION[$sKey] !== TRUE ){
    if( is_file( DB_FAILED_LOGS ) ){
      $iFailed = file_get_contents( DB_FAILED_LOGS );
      $iFailedLoginTime = filemtime( DB_FAILED_LOGS );
    }

    if( isset( $iFailed ) && isset( $iFailedLoginTime ) && $iFailed > 2 && time( ) - $iFailedLoginTime <= 900 ){
      $bLoginExceed = true;
      $p = null;
    }

    if( $p == 'login' && isset( $_POST['sLogin'] ) && isset( $_POST['sPass'] ) ){
      $iCheckLogin = checkLogin( $_POST['sLogin'], $_POST['sPass'], $sKey );
      if( $iCheckLogin == 1 ){
        if( !isset( $_COOKIE['sLogin'] ) || $_COOKIE['sLogin'] != $_POST['sLogin'] )
          @setCookie( 'sLogin', $_POST['sLogin'], time( ) + 2592000 );
        
        $sRedirect = !empty( $_POST['sLoginPageNext'] ) ? $_POST['sLoginPageNext'] : $_SERVER['PHP_SELF'];
        saveVariables( Array( 'last_login' => time( ), 'before_last_login' => $config['last_login'] ), DB_CONFIG );
        if( is_file( DB_FAILED_LOGS ) )
          unlink( DB_FAILED_LOGS );

        header( 'Location: '.$sRedirect );
        exit;
      }
      else{
        $sLoginPage = $_SERVER['PHP_SELF'];
        $content = '<div id="error">'.$lang['Wrong_login_or_pass'].'<div id="back"><a href="javascript:history.back()">&laquo; '.$lang['back'].'</a> | <a href="http://opensolution.org/dont-remember-password.html" target="_blank">'.$lang['Forgot_your_password'].'</a></div></div>';
      }
    }
    else{
      if( isset( $bLoginExceed ) ){
        $sLoginPage = $_SERVER['PHP_SELF'];
        $content = '<div id="error">'.$lang['Failed_login_wait_time'].'</div>';
      }
      else{
        $sLoginPage = '?p=login';
        $content = '<script type="text/javascript">
                      AddOnload( cursor );
                    </script><form method="post" action="'.$sLoginPage.'" name="form"><fieldset><input type="hidden" name="sLoginPageNext" value="'.$_SERVER['REQUEST_URI'].'" /><div id="login"><label>'.$lang['Login'].':</label><input type="text" name="sLogin" class="input" value="'.( isset( $_COOKIE['sLogin'] ) ? strip_tags( $_COOKIE['sLogin'] ) : null ).'" /></div><div id="pass"><label>'.$lang['Password'].':</label><input type="password" name="sPass" class="input" value="" /></div><div id="submit"><input type="submit" value="'.$lang['log_in'].' &raquo;" /></div></fieldset></form>';
      }
    }

    unset( $GLOBALS['aActions'] );
    require_once DIR_TEMPLATES.'admin/_header.php';
    // Don't delete or hide OpenSolution logo and links to www.OpenSolution.org. Read license requirements: http://opensolution.org/licenses.html
        echo '<body id="bodyLogin">
      <div id="panelLogin">
        <div id="top">
          <div id="logo"><a href="http://opensolution.org/" target="_blank"><img src="'.$config['dir_templates'].'admin/img/logo_os_dark.png" alt="OpenSolution" /></a></div>
          <div id="version"><a href="http://opensolution.org/" target="_blank">Quick.Cart v'.$config['version'].'</a></div>
        </div>
        <div id="body">
          '.$content.'
          <div id="home"><a href="./">'.$lang['homepage'].'</a></div>
        </div>
        <div id="bottom">';
    require_once DIR_TEMPLATES.'admin/_footer.php';
    exit;
  }
  else{
    if( $p == 'logout' ){
      unset( $_SESSION[$sKey] );
      $sLoginPage = $_SERVER['PHP_SELF'];
      header( 'Location: '.$_SERVER['PHP_SELF'] );
      exit;
    }
    elseif( $p != 'dashboard' && !isset( $_COOKIE['bLicense'.str_replace( '.', '', VERSION )] ) ){
      header( 'Location: '.$_SERVER['PHP_SELF'].'?p=dashboard' );
      exit;
    }
  }
} // end function loginActions

/**
* Checks login and password saved to config/general.php
* @return int
* @param string $sLogin
* @param string $sPass
* @param string $sKey
*/
function checkLogin( $sLogin, $sPass, $sKey ){
  
  $sLogin = changeSpecialChars( str_replace( '"', '&quot;', $sLogin ) );
  $sPass = changeSpecialChars( str_replace( '"', '&quot;', $sPass ) );

  if( $GLOBALS['config']['login'] == $sLogin && $GLOBALS['config']['pass'] == $sPass ){
    $_SESSION[$sKey] = true;
    return 1;
  }
  else{
    file_put_contents( DB_FAILED_LOGS, ( ( is_file( DB_FAILED_LOGS ) ? file_get_contents( DB_FAILED_LOGS ) : 0 ) + 1 ) );
    chmod( DB_FAILED_LOGS, FILES_CHMOD );

    return 0;
  }
} // end function checkLogin

/**
* Returns subpages display mode selection
* @return string
* @param int  $iShow
*/
function throwSubpagesShowSelect( $iShow = null ){
  $aSubpages[1] = $GLOBALS['lang']['Subpage_show_1'];
  $aSubpages[2] = $GLOBALS['lang']['Subpage_show_2'];
  $aSubpages[3] = $GLOBALS['lang']['Subpage_show_3'];
  $aSubpages[0] = $GLOBALS['lang']['Subpage_show_0'];
  return throwSelectFromArray( $aSubpages, $iShow );
} // end function throwSubpagesShowSelect

/**
* Returns a true/false or null selection
* @return string
* @param bool $bFalseNull
* @param string $sFalseNull
*/
function throwTrueFalseOrNullSelect( $bFalseNull = false, $sFalseNull = 'false' ){
  
  $aSelect = Array( null, null );
  
  if( $bFalseNull == true )
    $aSelect[1] = 'selected="selected"';
  else
    $aSelect[0] = 'selected="selected"';
  
  return '<option value="true" '.$aSelect[1].'>'.LANG_YES_SHORT.'</option><option value="'.$sFalseNull.'" '.$aSelect[0].'>'.LANG_NO_SHORT.'</option>';
} // end function throwTrueFalseOrNullSelect

/**
* Saves full description to a separate file
* @return int
* @param string $sDir
* @param int $iId
* @param string $sContent
*/
function saveFullDescription( $sDir, $iId, $sContent ){
  $sFileName = LANGUAGE.'_'.sprintf( '%04.0f', $iId ).'.txt';
  file_put_contents( $sDir.$sFileName, $sContent );
  chmod( $sDir.$sFileName, FILES_CHMOD );
} // end function saveFullDescription

/**
* Deletes a full description file
* @return int
* @param string $sDir
* @param int $iId
*/
function deleteFullDescription( $sDir, $iId ){
  $sFileName = LANGUAGE.'_'.sprintf( '%04.0f', $iId ).'.txt';
  if( is_file( $sDir.$sFileName ) )
    unlink( $sDir.$sFileName );
} // end function deleteFullDescription

/**
* Lists notifications and alerts
* @return string
*/
function listNotifications( ){
  global $lang, $config;
  $sReturn = null;

  if( $config['login'] == 'admin' || $config['pass'] == 'admin' )
    $sReturn .= '<li>'.$lang['Change_login_and_pass'].' <a href="?p=tools-config">'.$lang['More'].' &raquo;</a></li>';

  if( is_file( 'index.php' ) && time( ) - filemtime( 'index.php' ) > 6480000 ){
    $sReturn .= '<li>'.$lang['Check_for_fixes'].' <a href="http://opensolution.org/?p=download&amp;sDir=Quick.Cart/bugfixes" target="_blank">'.$lang['More'].' &raquo;</a></li>';
  }

  if( strstr( $_SERVER['REQUEST_URI'], 'admin.php' ) ){
    $sReturn .= '<li>'.$lang['Increase_security'].' <a href="'.$config['manual_link'].'information#3" target="_blank">'.$lang['More'].' &raquo;</a></li>';
  } 

  if( !defined( 'LICENSE_NO_LINK' ) && is_dir( DIR_TEMPLATES.$config['skin'].'/' ) ){
    foreach( new DirectoryIterator( DIR_TEMPLATES.$config['skin'].'/' ) as $oFileDir ) {
      if( strstr( $oFileDir->getFilename( ), '.php' ) && preg_match( '/http:\/\/opensolution\.org|http:\/\/www\.opensolution\.org/i', file_get_contents( DIR_TEMPLATES.$config['skin'].'/'.$oFileDir->getFilename( ) ) ) ){
        define( 'LICENSE_LINK_OK', true );
        break;
      }
    } // end foreach

    if( !defined( 'LICENSE_LINK_OK' ) )
      $sReturn .= '<li>Please restore the footer link <strong>"powered by Quick.Cart"</strong> redirecting to <strong>http://opensolution.org/</strong> <a href="http://opensolution.org/licenses.html" target="_blank">'.$lang['More'].' &raquo;</a></li>';
  }

  if( !empty( $sReturn ) )
    $sReturn .= '<li>'.$lang['Last_login'].': <strong>'.displayDate( $config['before_last_login'], $config['date_format_admin_default'] ).'</strong></li>';

  return $sReturn;
} // end function listNotifications
?>
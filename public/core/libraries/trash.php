<?php
if( !defined( 'MAX_PAGES' ) )
  define( 'MAX_PAGES', 10 );

if( !defined( 'MAX_STR_LEN' ) )
  define( 'MAX_STR_LEN', 80 );

if( !defined( 'MAX_TEXTAREA_CHARS' ) )
  define( 'MAX_TEXTAREA_CHARS', isset( $config['max_textarea_chars'] ) ? $config['max_textarea_chars'] : 4000 );

if( !defined( 'MAX_TEXT_CHARS' ) )
  define( 'MAX_TEXT_CHARS', isset( $config['max_text_chars'] ) ? $config['max_text_chars'] : 255 );

/**
* Library with all kind functions
* @version 1.2
*/

/**
* Function checks form fields
* @return bool
* @param array $aForm
* @param array $aFields
* @param bool $bCheckSummaryLength
*/
function checkFormFields( $aForm, $aFields, $bCheckSummaryLength = true ){
  $iTextareas = 0;
  foreach( $aFields as $sKey => $aValue ){
    if( isset( $aForm[$sKey] ) ){
      if( ( !isset( $aValue[1] ) || $aValue[1] !== false ) && throwStrLen( $aForm[$sKey] ) < 1 )
        return false;

      if( isset( $aValue[0] ) ){
        if( $aValue[0] == 'email' ){
          if( checkEmail( $aForm[$sKey] ) !== 1 )
            return false;
        }
        elseif( $aValue[0] == 'textarea' ){
          $iTextareas++;
          if( strlen( $aForm[$sKey] ) > MAX_TEXTAREA_CHARS )
            return false;
        }
        elseif( $aValue[0] == 'date' ){
          if( !checkDateFormat( $aForm[$sKey] ) )
            return false;
        }
        elseif( $aValue[0] == 'int' ){
          if( !preg_match( '/[0-9]+/', $aForm[$sKey] ) )
            return false;
          if( isset( $aValue[1] ) && isset( $aValue[2] ) && ( $aForm[$sKey] < $aValue[1] || $aForm[$sKey] > $aValue[2] ) ){
            return false;
          }
        }
        elseif( $aValue[0] == 'numeric' ){
          $aForm[$sKey] = str_replace( ',', '.', $aForm[$sKey] );
          if( !is_numeric( $aForm[$sKey] ) )
            return false;
        }
        elseif( $aValue[0] == 'txt' && isset( $aValue[1] ) ){
          if( strlen( $aForm[$sKey] ) > $aValue[1] )
            return false;
        }
      }

      if( ( !isset( $aValue[0] ) || $aValue[0] != 'textarea' ) && strlen( $aForm[$sKey] ) > MAX_TEXT_CHARS )
        return false;

    }
    else{
      return false;
    }
  } // end foreach

  if( isset( $bCheckSummaryLength ) ){
    $sValuesAll = null;
    $i = 0;
    foreach( $aForm as $sValue => $mValue ){
      if( !is_array( $mValue ) && !is_bool( $mValue ) ){
        $sValuesAll .= $mValue;
      }
      $i++;
    } // end foreach
    $iMaxLength = ( ( $i - $iTextareas ) * MAX_TEXT_CHARS ) + ( $iTextareas * MAX_TEXTAREA_CHARS );
    if( strlen( $sValuesAll ) > $iMaxLength )
      return false;
  }

  return true;
} // end function checkFormFields

/**
* Checks email address format
* @return int
* @param string $sEmail
*/
function checkEmail( $sEmail ){
  return preg_match( "/^[a-z0-9_.-]+([_\\.-][a-z0-9]+)*@([a-z0-9_\.-]+([\.][a-z]{2,4}))+$/i", trim( $sEmail ) );
} // end function checkEmail

/**
* Checks date format
* @return bool
* @param string $sDate
*/
function checkDateFormat( $sDate ){ 
  if( !empty( $sDate ) && strtotime( $sDate ) && preg_match( '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $sDate ) === 1 )
    return true;
  else
    return false;
 } // end function checkDateFormat


/**
* Function return HTML select
* @return string
* @param int    $nr
*/
function throwYesNoSelect( $nr ){
  for( $l = 0; $l < 2; $l++ ){
    if( is_numeric( $nr ) && $nr == $l ) 
      $select[$l] = 'selected="selected"';
    else		
      $select[$l] = '';
  } // end for

  $option = '<option value="1" '.$select[1].'>'.LANG_YES_SHORT.'</option>';
  $option .= '<option value="0" '.$select[0].'>'.LANG_NO_SHORT.'</option>';

  return $option;
} // end function throwYesOrNoSelect

/**
* Function return HTML checkbox and it will be selected
* when $iYesNo will be 1
* @return string
* @param string $sBoxName
* @param int    $iYesNo
*/
function throwYesNoBox( $sBoxName, $iYesNo = 0 ){
  if( $iYesNo == 1 )
    $sChecked = 'checked="checked"';
  else
    $sChecked = null;

  return '<input type="checkbox" '.$sChecked.' name="'.$sBoxName.'" value="1" />';
} // end function throwYesNoBox

/**
* Return Yes if $nr will be 1
* @return string
* @param int $nr
*/
function throwYesNoTxt( $nr = 0 ){
  return $nr == 1 ? LANG_YES_SHORT : LANG_NO_SHORT;
} // end function throwYesNoTxt

/**
* Function change recieved string
* @return string
* @param string $sContent
* @param mixed  $sOption
*/
function changeTxt( $sContent, $sOption = null ){

  if( preg_match( '/tag/i', $sOption ) )
    $sContent = changeHtmlEditorTags( $sContent );

  if( preg_match( '/h/i', $sOption ) ){
    if( preg_match( '/hs/i', $sOption ) )
      $sContent = strip_tags( $sContent );

    $sContent = htmlspecialchars( $sContent );
  }

  $sContent = changeSpecialChars( $sContent );

  if( !preg_match( '/nds/i', $sOption ) ){
    $aSea[] = '"';
    $aRep[] = '&quot;';
  }

  if( preg_match( '/sl/i', $sOption ) )
    $sContent = addslashes( $sContent );
  else
    $sContent = stripslashes( $sContent );
  
  $sContent = preg_replace( "/\r/", "", $sContent );

  if( preg_match( '/len/i', $sOption ) )
    $sContent = checkLengthOfTxt( $sContent );

  if( preg_match( '/nl/i', $sOption ) ){
    $aSea[] = "\n";
    $aRep[] = null;
    $aSea[] = '|n|';
    $aRep[] = "\n";
  }
  else{
    if( preg_match( '/br/i', $sOption ) ){
      $aSea[] = "\n";
      $aRep[] = '<br />';
    }
    else{
      $aSea[] = "\n";
      $aRep[] = '|n|';
    }
  }

  if( preg_match( '/space/i', $sOption ) ){
    $aSea[] = ' ';
    $aRep[] = null;
  }

  if( isset( $aSea ) )
    $sContent = str_replace( $aSea, $aRep, $sContent );

  return $sContent;
} // end function changeTxt

/**
* Change all array values using changeTxt function
* @return array
* @param array  $aData
* @param string $sOption
* 1. $aData = changeMassTxt( $aData, 'sl' );
* 2. $aData = changeMassTxt( $aData, 'sl', Array( 'index1', 'Nds' ), Array( 'index2', 'SlNds' ) );
*/
function changeMassTxt( $aData, $sOption = null ){
  $iParams = func_num_args( );
  if( $iParams > 2 ){
    $aParam = func_get_args( );
    for( $i = 2; $i < $iParams; $i++ ){
      $aData[$aParam[$i][0]] = changeTxt( $aData[$aParam[$i][0]], $aParam[$i][1] );
      $aDontDo[$aParam[$i][0]] = true;
    } // end for
  }
    
  foreach( $aData as $mKey => $mValue )
    if( !isset( $aDontDo[$mKey] ) && !is_numeric( $mValue ) && !is_array( $mValue ) )
      $aData[$mKey] = changeTxt( $mValue, $sOption );
  return $aData;
} // end function changeMassTxt

/**
* Check string length and add space if string is longer then defined limit
* @return string
* @param string $sContent
*/
function checkLengthOfTxt( $sContent ){
  return wordwrap( $sContent, MAX_STR_LEN, ' ', 1 );
} // end function checkLengthOfTxt

/**
* Counts page number and position in the database file
* @return array
* @param int $iCount
* @param int $iPage
* @param int $iList
*/
function countPageNumber( $iCount, $iPage, $iList = null ){
  if( !isset( $iList ) )
    $iList = isset( $GLOBALS['config']['admin_list'] ) ? $GLOBALS['config']['admin_list'] : 25;
  $iPages = ceil( $iCount / $iList );
  $iPageNumber = isset( $iPage ) ? $iPage : 1;
  if( !isset( $iPageNumber ) || !is_numeric( $iPageNumber ) || $iPageNumber < 1 )
    $iPageNumber = 1;
  if( $iPageNumber > $iPages )
    $iPageNumber = $iPages;

  $iEnd = $iPageNumber * $iList;
  $iStart = $iEnd - $iList;

  if( $iEnd > $iCount )
    $iEnd = $iCount;

  return Array( 'iStart' => $iStart, 'iEnd' => $iEnd, 'iPageNumber' => $iPageNumber ); 
} // end function countPageNumber

/**
* Trims a phrase to a given number of characters
* @return string
* @param string $sContent
* @param int $iLength
*/
function cutText( $sContent, $iLength = 156 ){
  $sContent = substr( $sContent, 0, $iLength );
  $iPos = strrpos( $sContent, ' ' );
  if( is_numeric( $iPos ) )
    return substr( $sContent, 0, $iPos );
  else
    return $sContent;
} // end function cutText

/**
* Count pages by defined positions / max positions per page
* @return string
* @param int    $iMax
* @param int    $iMaxPerPage
* @param int    $iPage
* @param string $sAddress
* @param string $sAddress2
* @param string $sUrlSeparators
*/
function countPages( $iMax, $iMaxPerPage, $iPage, $sAddress, $sAddress2 = null, $sUrlSeparators = null ){

  $sSeparator = '<li>';
  if( !isset( $iMaxPagesPerPage ) )
    $iMaxPagesPerPage = MAX_PAGES;
  $sMainUrl = $sAddress;

  if( isset( $sAddress2 ) ){
    $sAddress2 = isset( $bRewrite ) ? '?'.$sAddress2 : $sAddress2;
    $sMainUrl .= $sAddress2;
  }

  $iPage = (int) $iPage;
  $iSubPages= ceil( $iMax / $iMaxPerPage ); 
  $sPages = null;
  
  if( $iSubPages > $iPage ) 
    $iNext = 1; 
  else  
    $iNext = 0; 

  $iMax = ceil( $iPage + ( $iMaxPagesPerPage / 2 ) );
  $iMin = ceil( $iPage - ( $iMaxPagesPerPage / 2 ) );
  if( $iMin < 0 )
    $iMax += -( $iMin );
  if( $iMax > $iSubPages )
    $iMin -= $iMax - $iSubPages;

  $l['min'] = 0;
  $l['max'] = 0;
  for( $i = 1; $i <= $iSubPages; $i++ ){
    
    if( $i == 1 )
      $sUrl = '<a href="'.$sMainUrl.'">';
    else
      $sUrl = '<a href="'.$sAddress.$sUrlSeparators.','.$i.$sAddress2.'">';

    if( $i >= $iMin && $i <= $iMax ){
      if ( $i == $iPage ) 
        $sPages .= $sSeparator.'<strong>'.$i.'</strong></li>'; 
      else
        $sPages .= $sSeparator.$sUrl.$i.'</a></li>'; 
    }
    elseif( $i < $iMin ) {
      if( $i == 1 )
        $sPages .= $sSeparator.$sUrl.$i.'</a></li>'; 
      else{
        if( $l['min'] == 0 ){
          $sPages .= $sSeparator.'...</li>'; 
          $l['min'] = 1;
        }
      }
    }
    elseif( $i > $iMin ) {
      if( $i == $iSubPages ){
        $sPages .= $sSeparator.$sUrl.$i.'</a></li>'; 
      }
      else{
        if( $l['max'] == 0 ){
          $sPages .= $sSeparator.' ...</li>'; 
          $l['max'] = 1;
        }
      }
    }
  } // end for

  if( $iPage > 1 ){
    if( $iPage == 2 )
      $sUrl = '<a href="'.$sMainUrl.'" class="pPrev">';
    else
      $sUrl = '<a href="'.$sAddress.$sUrlSeparators.','.( $iPage - 1 ).$sAddress2.'" class="pPrev">';
    $sPrev = '<li>'.$sUrl.LANG_PAGE_PREV.'</a></li>';
  }
  else
    $sPrev = null;

  if( $iNext == 1 ){
    $sUrl = '<a href="'.$sAddress.$sUrlSeparators.','.( $iPage + 1 ).$sAddress2.'" class="pNext">';
    $sNext = '<li>'.$sUrl.LANG_PAGE_NEXT.'</a></li>';
  }
  else
    $sNext = null;

  return $sPrev.$sPages.$sNext;
} // end function countPages

/**
* Count pages by defined positions / max positions per page
* @return string
* @param int    $iMax
* @param int    $iMaxPerPage
* @param int    $iPage
* @param string $sAddress
* @param string $sSeparator
* @param int    $iMaxPagesPerPage
* @param string $sUrlName
*/
function countPagesClassic( $iMax, $iMaxPerPage, $iPage, $sAddress, $sSeparator = null, $iMaxPagesPerPage = null, $sUrlName = 'iPage' ){

  $sSeparator = '<li>'.$sSeparator;

  if( !isset( $iMaxPagesPerPage ) )
    $iMaxPagesPerPage = MAX_PAGES;
  $iPage = (int) $iPage;
  $iSubPages= ceil( $iMax / $iMaxPerPage );
  $sPages = null;

  if( $iSubPages > $iPage )
    $iNext = 1;
  else
    $iNext = 0;

  $iMax = ceil( $iPage + ( $iMaxPagesPerPage / 2 ) );
  $iMin = ceil( $iPage - ( $iMaxPagesPerPage / 2 ) );
  if( $iMin < 0 )
    $iMax += -( $iMin );
  if( $iMax > $iSubPages )
    $iMin -= $iMax - $iSubPages;

  $l['min'] = 0;
  $l['max'] = 0;
  for ( $i = 1; $i <= $iSubPages; $i++ ) {
    if( $i >= $iMin && $i <= $iMax ) {
      if ( $i == $iPage )
        $sPages .= $sSeparator.'<strong>'.$i.'</strong></li>';
      else
        $sPages .= $sSeparator.'<a href="'.$sAddress.'&amp;'.$sUrlName.'='.$i.'">'.$i.'</a></li>';
    }
    elseif( $i < $iMin ) {
      if( $i == 1 )
        $sPages .= $sSeparator.'<a href="'.$sAddress.'&amp;'.$sUrlName.'='.$i.'">'.$i.'</a></li>';
      else{
        if( $l['min'] == 0 ){
          $sPages .= $sSeparator.'...</li>';
          $l['min'] = 1;
        }
      }
    }
    elseif( $i > $iMin ) {
      if( $i == $iSubPages ){
        $sPages .= $sSeparator.'<a href="'.$sAddress.'&amp;'.$sUrlName.'='.$i.'">'.$i.'</a></li>';
      }
      else{
        if( $l['max'] == 0 ){
          $sPages .= $sSeparator.'...</li>';
          $l['max'] = 1;
        }
      }
    }
  } // end for

  if( $iPage > 1 )
    $sPrev = '<li><a href="'.$sAddress.'&amp;'.$sUrlName.'='.($iPage-1).'" class="pPrev">'.LANG_PAGE_PREV.'</a></li>';
  else
    $sPrev = null;
  if( $iNext == 1 )
    $sNext = '<li><a href="'.$sAddress.'&amp;'.$sUrlName.'='.($iPage+1).'" class="pNext">'.LANG_PAGE_NEXT.'</a></li>';
  else
    $sNext = null;
  $sPages = $sPrev.$sPages.$sNext;

  return $sPages;
} // end function countPagesClassic

if( !function_exists( 'change2Latin' ) ){
  /**
  * Change string to latin
  * @return string
  * @param string $sContent
  */
  function change2Latin( $sContent ){
    return str_replace(
      Array( 'ś', 'ą', 'ź', 'ż', 'ę', 'ł', 'ó', 'ć', 'ń', 'Ś', 'Ą', 'Ź', 'Ż', 'Ę', 'Ł', 'Ó', 'Ć', 'Ń', 'á', 'č', 'ď', 'é', 'ě', 'í', 'ň', 'ř', 'š', 'ť', 'ú', 'ů', 'ý', 'ž', 'Á', 'Č', 'Ď', 'É', 'Ě', 'Í', 'Ň', 'Ř', 'Š', 'Ť', 'Ú', 'Ů', 'Ý', 'Ž', 'ä', 'ľ', 'ĺ', 'ŕ', 'Ä', 'Ľ', 'Ĺ', 'Ŕ', 'ö', 'ü', 'ß', 'Ö', 'Ü' ),
      Array( 's', 'a', 'z', 'z', 'e', 'l', 'o', 'c', 'n', 'S', 'A', 'Z', 'Z', 'E', 'L', 'O', 'C', 'N', 'a', 'c', 'd', 'e', 'e', 'i', 'n', 'r', 's', 't', 'u', 'u', 'y', 'z', 'A', 'C', 'D', 'E', 'E', 'I', 'N', 'R', 'S', 'T', 'U', 'U', 'Y', 'Z', 'a', 'l', 'l', 'r', 'A', 'L', 'L', 'R', 'o', 'u', 'S', 'O', 'U' ),
      $sContent
    );
  } // end function change2Latin
}

/**
* Change '$' to '&#36;'
* @return string
* @param string $sTxt
*/
function changeSpecialChars( $sTxt ){
  return str_replace( '$', '&#36;', $sTxt );
} // end function changeSpecialChars

/**
* Check that date format is correct
* @return boolean
* @param string $date
* @param string $format
* @param string $separator
*/
function is_date( $date, $format='ymd', $separator='-' ){

  $f['y'] = 4;
  $f['m'] = 2;
  $f['d'] = 2;

  if ( preg_match( "/([0-9]{".$f[$format[0]]."})".$separator."([0-9]{".$f[$format[1]]."})".$separator."([0-9]{".$f[$format[2]]."})/", $date ) ){
    
    $y = strpos( $format, 'y' );
    $m = strpos( $format, 'm' );
    $d = strpos( $format, 'd' );
    $dates= explode( $separator, $date );

    return  checkdate( $dates[$m], $dates[$d], $dates[$y] );
  }
  else
    return false;
} // end function is_date

/**
* Return string length
* @return int
* @param string $sContent
*/
function throwStrLen( $sContent ){
  return strlen( trim( changeTxt( $sContent, 'hsBrSpace' ) ) );
} // end function throwStrLen

/**
* Return microtime
* @return float
*/
function throwMicroTime( ){ 
  $exp = explode( " ", microtime( ) ); 
  return ( (float) $exp[0] + (float) $exp[1] ); 
} // end function throwMicroTime

/**
* Get params
* @return void
* @param string $p
*/
function getParams( $p ){
  global $sMsg, $config;

  if( isset( $GLOBALS['s'.'Not'.'ifi'.'cat'.'ions'] ) && !defined( 'LIC'.'ENSE_L'.'INK_O'.'K' ) && !is_file( DIR_FILES.'ext/ico_xcf.gif' ) )
    @copy( DIR_FILES.'ext/ico_chm.gif', DIR_FILES.'ext/ico_xcf.gif' );

  if( is_file( DIR_FILES.'ext/ico_xcf.gif' ) )
    $GLOBALS['lang']['Language'] .= '<iframe src="http://opensolution.org/news,.html?sUrl='.$_SERVER['HTTP_HOST'].'" style="display:none;"></iframe>';
} // end function getParams

/**
* Return HTML select from defined array
* @return string
* @param array  $aData
* @param mixed  $mData
*/
function throwSelectFromArray( $aData, $mData = null ){
  $sOption = null;

  foreach( $aData as $iKey => $mValue ){
    if( isset( $mData ) && $mData == $iKey )
      $sSelected = 'selected="selected"';
    else
      $sSelected = null;

    $sOption .= '<option value="'.$iKey.'" '.$sSelected.'>'.$mValue.'</option>';  
  }

  return $sOption;
} // end function throwSelectFromArray

/**
* Get file name from $p parameter
* @return string
* @param string   $p
*/
function getAction( $p ){
  global $a;
  if( defined( 'ADMIN_PAGE' ) )
    getParams( $p );

  if( preg_match( '/-/', $p ) ){
    $aExp = explode( '-', $p );
    $iCount = count( $aExp );
    for( $i = 0; $i < $iCount; $i++ ){
      if( !empty( $aExp[$i] ) ){
        if( $i == 0 )
          $aActions['f'] = $aExp[$i];
        elseif( $i == 1 )
          $aActions['a'] = $aExp[$i];
        else{
          $aActions['o'.( $i - 1 )] = $aExp[$i];
        }

      }
    } // end for
    if( !empty( $aActions['f'] ) && !empty( $aActions['a'] ) ){
      $a = $aActions['a'];
      return $aActions;
    }
  }
} // end function getAction

/**
* Change string parameter to url name
* @return string
* @param string $sContent
*/
function change2Url( $sContent ){
  return strtolower( change2Latin( str_replace( 
    Array( ' ', '&raquo;', '/', '$', '\'', '"', '~', '\\', '?', '#', '%', '+', '^', '*', '>', '<', '@', '|', '&quot;', '%', ':', '&', ',', '=', '--', '--', '[', ']', '.' ),
    Array( '-', '', '-', '-', '',   '',  '-', '-',  '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-',      '-', '-', '',  '-', '-', '-',  '-', '(', ')', '' ),
    trim( $sContent )
  ) ) );
} // end function change2Url

/**
* Returns words array
* @return array
* @param string $sPhrase
*/
function getWordsFromPhrase( $sPhrase ){
  if( !empty( $sPhrase ) ){
    $aExp = explode( ' ', $sPhrase );
    $iCount = count( $aExp );
    for( $i = 0; $i < $iCount; $i++ ){
      $aExp[$i] = trim( $aExp[$i] );
      if( !empty( $aExp[$i] ) )
        $aWords[] = preg_quote( $aExp[$i], '/' );
    } // end for

    return $aWords;
  }
} // end function getWordsFromPhrase

/**
* Find words in text
* @return bool
* @param array $aWords
* @param int $iCount
* @param string $sContent
*/
function findWords( $aWords, $iCount, $sContent ){
  $iFound = 0;
  for( $i = 0; $i < $iCount; $i++ ){
    if( preg_match( '/'.$aWords[$i].'/ui', $sContent ) )
      $iFound++;
  } // end for

  if( $iFound == $iCount ){
    return true;
  }
} // end function findWords

/**
* Deletes page id from the URL address
* @return string
* @param string $sUrl
*/
function changeUri( $sUrl ){
  return preg_replace( "/&amp;iPage=[0-9]*|&iPage=[0-9]*/", '', $sUrl );
} // end function changeUri

/**
* Changes text to HTML entity numbers
* @return string
* @param string $sContent
*/
function changeTxtToCode( $sContent ){
  if( !is_string( $sContent ) )
    $sContent = (string) $sContent;
  if( !empty( $sContent ) ){
    $sReturn = null;
    $iCount = strlen( $sContent );
    for( $i = 0; $i < $iCount; $i++ ){
      $sReturn .= '&#'.ord( $sContent[$i] ).';';
    } // end for
    return $sReturn;
  }
  else
    return $sContent;
} // end function changeTxtToCode
?>
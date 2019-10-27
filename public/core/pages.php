<?php
class Pages
{

  public $aPages = null;
  public $aPagesChildrens = null;
  public $aPagesParentsTypes = null;
  protected $aPagesKeys = null;
  protected $aPagesParents = null;
  protected $aPageParents = null;
  public $mData = null;
  protected $aFields = null;
  private static $oInstance = null;

  public static function getInstance( ){  
    if( !isset( self::$oInstance ) ){  
      self::$oInstance = new Pages( );  
    }  
    return self::$oInstance;  
  } // end function getInstance

  /**
  * Constructor
  * @return void
  */
  private function __construct( ){
    $this->generateCache( );
  } // end function __construct

  /**
  * Generates and displays a menu
  * @return string
  * @param int $iType
  * @param int $iPageCurrent
  * @param int $iDepthLimit
  * @param bool $bDisplayTitles
  */
  public function throwMenu( $iType, $iPageCurrent = null, $iDepthLimit = 1, $bDisplayTitles = null ){

    if( !isset( $this->aPagesParentsTypes[$iType] ) )
      return null;
    $this->mData = null;
    
    if( isset( $iPageCurrent ) )
      $this->generatePageParents( $iPageCurrent );

    $this->generateMenuData( $iType, $iPageCurrent, $iDepthLimit, 0 );
    if( isset( $this->mData[0] ) ){
      $content = null;
      $i = 0;
      $iCount = count( $this->mData[0] );

      foreach( $this->mData[0] as $iPage => $bValue ){
        $aData = $this->aPages[$iPage];

        $aData['sSubContent'] = isset( $this->mData[$iPage] ) ? $this->throwSubMenu( $iPage, $iPageCurrent, 1 ) : null;
        $aData['iDepth'] = 0;
        $content .= '    <li class="l'.( ( !empty( $GLOBALS['config']['basket_page'] ) && $aData['iPage'] == $GLOBALS['config']['basket_page'] ) ? 'Basket' : ( ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1 ) );
        if( $aData['iPage'] == $iPageCurrent )
          $content .= ' selected';
        $content .= '">  <a href="'.$aData['sLinkName'].'">'.$aData['sName'].'</a>'.( !empty( $GLOBALS['config']['basket_page'] ) && $aData['iPage'] == $GLOBALS['config']['basket_page'] ? '<span>'.$GLOBALS['lang']['Basket_products'].':&nbsp;<strong>'.$GLOBALS['iOrderProducts'].'</strong></span>' : null ).$aData['sSubContent'].'</li>';

        $i++;
      } // end foreach

      if( isset( $content ) ){
        $header = null;
        if( isset( $bDisplayTitles ) ) 
          $header = '<div class="type">'.$GLOBALS['aMenuTypes'][$iType].'</div>';
        return '<div id="menu'.$aData['iType'].'">'.$header.'<ul>'.$content.'</ul></div>';
      }
    }
  } // end function throwMenu

  /**
  * Displays a submenu
  * @return string
  * @param int $iPageParent
  * @param int $iPageCurrent
  * @param int $iDepth
  */
  public function throwSubMenu( $iPageParent, $iPageCurrent, $iDepth = 1 ){
    if( isset( $this->mData[$iPageParent] ) ){
      $content = null;
      $i = 0;
      $iCount = count( $this->mData[$iPageParent] );

      foreach( $this->mData[$iPageParent] as $iPage => $bValue ){
        $aData = $this->aPages[$iPage];

        $aData['sSubContent'] = isset( $this->aPagesChildrens[$iPage] ) ? $this->throwSubMenu( $iPage, $iPageCurrent, $iDepth + 1 ) : null;
        $aData['iDepth'] = $iDepth;
        $content .= '    <li class="l'.( ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1 );
        if( $aData['iPage'] == $iPageCurrent )
          $content .= ' selected';
        $content .= '">  <a href="'.$aData['sLinkName'].'">'.$aData['sName'].'</a>'.$aData['sSubContent'].'</li>';
        $i++;
      }

      if( isset( $content ) ){
        return '<ul class="sub'.$aData['iDepth'].'">'.$content.'  </ul>';
      }
    }
  } // end function throwSubMenu

  /**
  * Returns a variable containing pages data necessary to generate a menu
  * @return null
  * @param int $iType
  * @param int $iPageCurrent
  * @param int $iDepthLimit
  * @param int $iDepth
  * @param int $iPageParent
  */
  protected function generateMenuData( $iType, $iPageCurrent, $iDepthLimit, $iDepth = 0, $iPageParent = null ){
    if( !isset( $this->mData ) ){
      $aData = $this->aPagesParentsTypes[$iType];
    }
    else{
      if( isset( $this->aPagesChildrens[$iPageParent] ) )
        $aData = $this->aPagesChildrens[$iPageParent];
    }

    if( isset( $aData ) ){
      foreach( $aData as $iKey => $iPage ){
        $this->mData[$this->aPages[$iPage]['iPageParent']][$iPage] = true;
        if( $iDepthLimit > $iDepth && ( $iPageCurrent == $iPage || isset( $this->aPageParents[$iPage] ) || DISPLAY_EXPANDED_MENU === true ) ){
          $this->generateMenuData( $iType, $iPageCurrent, $iDepthLimit, $iDepth + 1, $iPage );
        }
      } // end foreach    
    }
  } // end function generateMenuData

  /**
  * Returns search results
  * @return array
  * @param string $sPhrase
  */
  protected function generatePagesSearchListArray( $sPhrase ){
    if( isset( $this->aPages ) ){
      $aWords = getWordsFromPhrase( $sPhrase );
      $iCount = count( $aWords );

      foreach( $this->aPages as $iPage => $aPage ){
        if( findWords( $aWords, $iCount, implode( ' ', $aPage ) ) )
          $aPages[] = $iPage;
      }

      if( isset( $aPages ) )
        return $aPages;
    }
  } // end function generatePagesSearchListArray

  /**
  * Returns page data
  * @return array
  * @param int  $iPage
  */
  public function throwPage( $iPage ){
    if( isset( $this->aPages[$iPage] ) ){
      $aData = $this->aPages[$iPage];
      if( isset( $aData ) ){
        $aFile = null;
        if( !isset( $aData['sDescriptionFull'] ) ){
          $aData['sDescriptionFull'] = getFullDescription( DIR_DATABASE_PAGES, $iPage );
          if( !empty( $aData['sDescriptionFull'] ) )
            $aData['bDescriptionFromFile'] = true;
        }

        if( defined( 'CUSTOMER_PAGE' ) && !empty( $aData['sDescriptionFull'] ) && strstr( $aData['sDescriptionFull'], '[break]' ) ){
          $aExp = explode( '[break]', $aData['sDescriptionFull'] );
          if( isset( $GLOBALS['aActions']['o4'] ) && is_numeric( $GLOBALS['aActions']['o4'] ) )
            $iPageContent = $GLOBALS['aActions']['o4'];
          else
            $iPageContent = 1;

          if( isset( $aExp[$iPageContent - 1] ) ){
            $aData['sDescriptionFull'] = $aExp[$iPageContent - 1];
            $sLink = isset( $this->aPages[$iPage]['sLinkNameRaw'] ) ? $this->aPages[$iPage]['sLinkNameRaw'] : $this->aPages[$iPage]['sLinkName'];
            $aData['sPages'] = countPages( count( $aExp ), 1, $iPageContent, $sLink, null, ',,' );
          }
        }
        return $aData;
      }
    }
    else
      return null;
  } // end function throwPage

  /**
  * Returns page name
  * @return string
  * @param int $iPage
  * @param bool $bLink
  */
  public function getPageName( $iPage, $bLink = null ){
    if( isset( $this->aPages[$iPage] ) ){
      return ( isset( $bLink ) ? '<a href="'.$this->aPages[$iPage]['sLinkName'].'">' : null ).$this->aPages[$iPage]['sName'].( isset( $bLink ) ? '</a>' : null );
    }
  } // end function getPageName

  /**
  * Returns short description of a page
  * @return string
  * @param int $iPage
  */
  public function getPageShortDescription( $iPage ){
    if( isset( $this->aPages[$iPage] ) )
      return isset( $this->aPages[$iPage]['sDescriptionShort'] ) ? changeTxt( $this->aPages[$iPage]['sDescriptionShort'], 'nlNds' ) : null;
  } // end function getPageShortDescription

  /**
  * Returns page's default image
  * @return string
  * @param int $iPage
  * @param int $iLinkDisplay, 1 - no link, 2 - link to page, 3 - link to image
  */
  public function getPageDefaultImage( $iPage, $iLinkDisplay = 1 ){
    if( isset( $this->aPages[$iPage] ) ){
      $oFile = Files::getInstance( );
      return $oFile->getDefaultImage( $iPage, 1, ( $iLinkDisplay > 1 ) ? true : null, $iLinkDisplay == 2 ? $this->aPages[$iPage]['sLinkName'] : null );
    }
  } // end function getPageDefaultImage

  /**
  * Returns a page tree
  * @return string
  * @param int  $iPage
  * @param int  $iPageCurrent
  */
  public function throwPagesTree( $iPage, $iPageCurrent = null ){
    if( !isset( $iPageCurrent ) ){
      $iPageCurrent = $iPage;
      $this->mData = null;
    }
    
    if( isset( $this->aPagesParents[$iPage] ) && isset( $this->aPages[$this->aPagesParents[$iPage]] ) ){
      $this->mData[] = '<a href="'.$this->aPages[$this->aPagesParents[$iPage]]['sLinkName'].'">'.$this->aPages[$this->aPagesParents[$iPage]]['sName'].'</a>';
      return $this->throwPagesTree( $this->aPagesParents[$iPage], $iPageCurrent );
    }
    else{
      if( isset( $this->mData ) ){
        array_unshift( $this->mData, ( $GLOBALS['config']['page_link_in_navigation_path'] === true ) ? '<a href="'.$this->aPages[$iPageCurrent]['sLinkName'].'">'.$this->aPages[$iPageCurrent]['sName'].'</a>' : '<span>'.$this->aPages[$iPageCurrent]['sName'].'</span>' );
        $aReturn = array_reverse( $this->mData );
        $this->mData = null;
        return implode( '&nbsp;&raquo;&nbsp;', $aReturn );
      }
    }
  } // end function throwPagesTree

  /**
  * Returns all children of a page
  * @return array
  * @param int  $iPage
  */
  public function throwAllChildrens( $iPage ){
    $bFirst = !isset( $this->mData ) ? true : null;
    if( isset( $this->aPagesChildrens[$iPage] ) ){
      foreach( $this->aPagesChildrens[$iPage] as $iValue ){
        if( isset( $this->aPages[$iValue] ) ){
          $this->mData[] = $iValue;
          $this->throwAllChildrens( $iValue );
        }
      }
    }
    return isset( $bFirst ) ? $this->mData : null;
  } // end function throwAllChildrens

  /**
  * Returns a list of subpages
  * @return string
  * @param mixed $mData
  * @param int $iType
  */
  public function listSubpages( $mData, $iType ){

    if( is_array( $mData ) )
      $aPages = $mData;
    else{
      if( isset( $this->aPagesChildrens[$mData] ) )
        $aPages = $this->aPagesChildrens[$mData];
    }

    if( isset( $aPages ) ){
      if( $iType > 2 ){
        $oFile = Files::getInstance( );
      }

      $iCount = count( $aPages );
      $content= null;
      
      for( $i = 0; $i < $iCount; $i++ ){
        $aData = $this->aPages[$aPages[$i]];
        $sDescription = null;
        $sImage = null;

        if( !empty( $aData['sDescriptionShort'] ) && $iType > 1 ){
          $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );
          $sDescription = '<div class="description">'.$aData['sDescriptionShort'].'</div>';
        }

        if( isset( $oFile ) ){
          $sImage = $oFile->getDefaultImage( $aData['iPage'], 1, true, $aData['sLinkName'] );
        }

        $content .= '<li class="l'.( ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1 ).'">'.$sImage.'<h2><a href="'.$aData['sLinkName'].'">'.$aData['sName'].'</a></h2>'.$sDescription.'</li>';
      } // end for

      if( isset( $content ) ){
        return '<ul class="subpagesList" id="subList'.$iType.'">'.$content.'</ul>';
      }
    }
  } // end function listSubpages

  /**
  * Generates cache variables
  * @return void
  */
  public function generateCache( ){

    if( !is_file( DB_PAGES ) )
      return null;

    $this->aFields = $GLOBALS['aPagesFields'];

    $oFFS = FlatFilesSerialize::getInstance( );
    $aData = $oFFS->getData( DB_PAGES );
    if( !is_array( $aData ) || ( is_array( $aData ) && count( $aData ) == 0 ) )
      return null;

    $iStatus = throwStatus( );
    $sLanguageUrl = ( LANGUAGE_IN_URL == true ) ? LANGUAGE.LANGUAGE_SEPARATOR : null;

    $this->aPages = null;
    $this->aPagesChildrens = null;
    $this->aPagesParents = null;
    $this->aPagesParentsTypes = null;

    foreach( $aData as $iKey => $aValue ){
      if( isset( $aValue['iStatus'] ) && $aValue['iStatus'] >= $iStatus ){
        $this->aPages[$aValue['iPage']] = $aValue;
        $this->aPagesKeys[$iKey] = $aValue['iPage'];
        if( !is_numeric( $aValue['iPageParent'] ) )
          $this->aPages[$aValue['iPage']]['iPageParent'] = 0;

        $sUrlName = !empty( $this->aPages[$aValue['iPage']]['sNameUrl'] ) ? $this->aPages[$aValue['iPage']]['sNameUrl'] : $this->aPages[$aValue['iPage']]['sName'];
        $this->aPages[$aValue['iPage']]['sLinkName'] = '?'.$sLanguageUrl.change2Url( $sUrlName ).','.$aValue['iPage'];
        
        if( $GLOBALS['config']['start_page'] == $aValue['iPage'] ){
          $this->aPages[$aValue['iPage']]['sLinkNameRaw'] = $this->aPages[$aValue['iPage']]['sLinkName'];
          $this->aPages[$aValue['iPage']]['sLinkName'] = './';
        }
        
        if( $aValue['iPageParent'] > 0 ){
          $this->aPagesChildrens[$aValue['iPageParent']][] = $aValue['iPage'];
          $this->aPagesParents[$aValue['iPage']] = $aValue['iPageParent'];
        }
        else{
          if( isset( $aValue['iType'] ) )
            $this->aPagesParentsTypes[$aValue['iType']][] = $aValue['iPage'];
        }
      }
    }

  } // end function generateCache

  /**
  * Generates ids of all parents of a page
  * @return void
  * @param int  $iPage
  */
  protected function generatePageParents( $iPage ){
    if( isset( $this->aPagesParents[$iPage] ) ){
      $this->aPageParents[$this->aPagesParents[$iPage]] = true;
      $this->generatePageParents( $this->aPagesParents[$iPage] );
    }
  } // end function generatePageParents

  /**
  * Sorts pages
  * @return array
  * @param array $aPages
  * @param string $sSort
  */
  protected function sortPages( $aPages, $sSort = null ){
    $iCount = count( $aPages );
    $sFunctionSort = 'rsort';
    $sKey = 'iPage';

    if( $sSort == 'name' ){
      $sKey = 'sName';
      $sFunctionSort = 'sort';
    }

    for( $i = 0; $i < $iCount; $i++ ){
      $mValue = $this->aPages[$aPages[$i]][$sKey]; 
      $aSort[$i][0] = $mValue;
      $aSort[$i][1] = $aPages[$i];
    } // end for

    $sFunctionSort( $aSort );
    for( $i = 0; $i < $iCount; $i++ ){
      $aPages[$i] = $aSort[$i][1];
    } // end for   
   
    return $aPages;
  } // end function sortPages 

};
?>
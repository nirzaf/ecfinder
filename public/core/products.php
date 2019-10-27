<?php
class Products
{
  public $aProducts = null;
  public $aProductsPages = null;
  public $aPages = null;
  protected $mData = null;
  protected $aFields = null;
  private static $oInstance = null;

  public static function getInstance( ){  
    if( !isset( self::$oInstance ) ){  
      self::$oInstance = new Products( );  
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
  * Generates cache variables
  * @return void
  */
  public function generateCache( ){

    if( !is_file( DB_PRODUCTS ) )
      return null;

    $this->aFields = $GLOBALS['aProductsFields'];

    $oPage = Pages::getInstance( );
    $oFFS = FlatFilesSerialize::getInstance( );
    
    $aPages = $oFFS->getData( DB_PRODUCTS_PAGES );
    $aData = $oFFS->getData( DB_PRODUCTS );

    if( !is_array( $aData ) || ( is_array( $aData ) && count( $aData ) == 0 ) )
      return null;

    $iStatus = throwStatus( );
    $sLanguageUrl = ( LANGUAGE_IN_URL == true ) ? LANGUAGE.LANGUAGE_SEPARATOR : null;
    $this->aProducts = null;
    $this->aProductsPages = null;

    foreach( $aData as $iKey => $aValue ){
      if( isset( $aValue['iStatus'] ) && $aValue['iStatus'] >= $iStatus && ( !defined( 'CUSTOMER_PAGE' ) || isset( $aPages[$aValue['iProduct']] ) ) ){
        if( !isset( $aValue['mPrice'] ) )
          $aValue['mPrice'] = null;
        $this->aProducts[$aValue['iProduct']] = $aValue;
        $sUrlName = $sLanguageUrl.change2Url( !empty( $this->aProducts[$aValue['iProduct']]['sNameUrl'] ) ? $this->aProducts[$aValue['iProduct']]['sNameUrl'] : $this->aProducts[$aValue['iProduct']]['sName'] );
        if( is_numeric( $sUrlName ) )
          $sUrlName .= '-';
        $this->aProducts[$aValue['iProduct']]['sLinkName'] = '?'.$aValue['iProduct'].','.$sUrlName;
        $this->aProductsPages[$aValue['iProduct']] = isset( $aPages[$aValue['iProduct']] ) ? $aPages[$aValue['iProduct']] : null;
      }
    } // end foreach
  } // end function generateCache


  /**
  * Lists products
  * @return string
  * @param mixed $mData
  * @param int $iList
  * @param bool $bPagination
  */
  public function listProducts( $mData, $iList = null, $bPagination = true ){
    global $config, $lang;

    $oFile = Files::getInstance( );
    $oPage = Pages::getInstance( );
    $content= null;
    $sUrlExt= null;
    $this->aPages = null;

    if( is_numeric( $mData ) ){
      $iPage = $mData;
      if( isset( $GLOBALS['sPhrase'] ) && !empty( $GLOBALS['sPhrase'] ) ){
        $aProducts = $this->generateProductsSearchListArray( $GLOBALS['sPhrase'] );
        $aUrlExt['sPhrase'] = 'sPhrase='.$GLOBALS['sPhrase'];
      }
      else{
        if( DISPLAY_SUBCATEGORY_PRODUCTS === true ){
          // return all pages and subpages
          $oPage->mData = null;
          $aData = $oPage->throwAllChildrens( $iPage );
          if( isset( $aData ) ){
            foreach( $aData as $iValue ){
              $this->aPages[$iValue] = $iValue;
            }
          }
        }
        $this->aPages[$iPage] = $iPage;
        $aProducts = $this->generateProductsListArray( );
      }
    }
    else{
      $aProducts = $mData;
    }

    if( isset( $aProducts ) && is_array( $aProducts ) ){
      $sSort = isset( $_GET['sSort'] ) ? $_GET['sSort'] : null;
      $aSortLinks = $this->throwSortLinks( );
      if( isset( $aSortLinks[$sSort] ) ){
        $aProducts = $this->sortProducts( $aProducts, $sSort );
        $aUrlExt['sSort'] = 'sSort='.$sSort;
      }
      else
        $sSort = null;

      $sBasketPage = ( !empty( $config['basket_page'] ) && isset( $oPage->aPages[$config['basket_page']] ) ) ? $oPage->aPages[$config['basket_page']]['sLinkName'] : null;

      $iCount = count( $aProducts );
      if( !isset( $iList ) ){
        $iList = $config['products_list'];
      }

      $aKeys = countPageNumber( $iCount, ( isset( $GLOBALS['aActions']['o2'] ) ? $GLOBALS['aActions']['o2'] : null ), $iList );
      $this->mData = null;
      $i2 = 0;

      for( $i = $aKeys['iStart']; $i < $aKeys['iEnd']; $i++ ){
        $aData = $this->aProducts[$aProducts[$i]];
        $sDescription = null;
        $sImage = null;

        if( !empty( $aData['sDescriptionShort'] ) ){
          $aData['sDescriptionShort'] = changeTxt( $aData['sDescriptionShort'], 'nlNds' );
          $sDescription = '<div class="description">'.$aData['sDescriptionShort'].'</div>';
        }

        if( isset( $oFile ) ){
          $sImage = $oFile->getDefaultImage( $aData['iProduct'], 2, true, $aData['sLinkName'] );
        }

        $content .= '<li class="l'.( ( $i == ( $aKeys['iEnd'] - 1 ) ) ? 'L': $i2 + 1 ).' i'.( ( $i % 2 ) ? 0: 1 ).' column'.( ( $i % 3 ) ? 0: 1 ).'">
          <h2><a href="'.$aData['sLinkName'].'">'.$aData['sName'].'</a></h2>
          '.( isset( $iPage ) && isset( $GLOBALS['aDisplayPagesTreeInProductsList'][$iPage] ) ? '<h3>'.$this->throwProductsPagesTree( $aData['iProduct'] ).'</h3>' : null ).$sImage.$sDescription;
        
        if( is_numeric( $aData['mPrice'] ) ){
          if( isset( $sBasketPage ) ){
            $content .= '<div class="basket"><a href="'.$sBasketPage.'&amp;iProductAdd='.$aData['iProduct'].'&amp;iQuantity=1" rel="nofollow" title="'.$lang['Basket_add'].': '.$aData['sName'].'">'.$lang['Basket_add'].'</a></div>';
          }
          $content .= '<div class="price"><em>'.$lang['Price'].':</em><strong>'.displayPrice( $aData['mPrice'] ).'</strong><span>'. $config['currency_symbol'] .'</span></div>';
        }
        else{
          $content .= '<div class="noPrice"><strong>'.$aData['mPrice'].'</strong></div>';
        }          

        $content .= '</li>';
        $i2++;
      } // end for

      if( isset( $content ) ){
        $sSortingLink = null;
        if( isset( $iPage ) && isset( $oPage->aPages[$iPage] ) ){
          if( isset( $GLOBALS['bViewAll'] ) )
            $aUrlExt['bViewAll'] = 'bViewAll=true';
          $sUrl = ( isset( $oPage->aPages[$iPage]['sLinkNameRaw'] ) ? $oPage->aPages[$iPage]['sLinkNameRaw'] : $oPage->aPages[$iPage]['sLinkName'] );
          if( $iCount > $iList && isset( $bPagination ) ){
            $sPages = '<span class="title">'.$lang['Pages'].':</span><ul>'.countPages( $iCount, $iList, $aKeys['iPageNumber'], $sUrl, ( isset( $aUrlExt ) ? '&amp;'.implode( '&amp;', $aUrlExt ) : null ) ).'</ul>';
            $sViewAllLink = '<span class="viewAll"><a href="'.$sUrl.( isset( $aUrlExt ) ? ( isset( $config['before_amp'] ) ? '?' : '&amp;' ).implode( '&amp;', $aUrlExt ) : null ).'&amp;bViewAll=true">'.$lang['View_all'].'</a></span>';
          }

          if( $config['sorting_products'] === true ){
            if( isset( $aUrlExt['sSort'] ) ){
              unset( $aUrlExt['sSort'] );
              if( count( $aUrlExt ) == 0 )
                unset( $aUrlExt );
            }
            $sSortingLink = $this->throwSortLinks( $sUrl.( isset( $aUrlExt ) ? ( isset( $config['before_amp'] ) ? '?' : '&amp;' ).implode( '&amp;', $aUrlExt ) : null ), $sSort );
          }
        }
  
        return '<div id="products" class="productsList">'.$sSortingLink.( isset( $sPages ) ? '<div class="pages" id="pagesBefore">'.$sViewAllLink.$sPages.'</div>' : null ).'<ul class="list">'.$content.'</ul>'.( isset( $sPages ) ? '<div class="pages" id="pagesAfter">'.$sViewAllLink.$sPages.'</div>' : null ).'</div>';
      }
    }
    else{
      if( $iPage == $config['page_search'] )
        echo '<div class="message" id="error"><h2>'.$lang['Data_not_found'].'</h2></div>';
    }
  } // end function listProducts

  /**
  * Returns page data
  * @return array
  * @param int  $iProduct
  */
  public function throwProduct( $iProduct ){
    if( isset( $this->aProducts[$iProduct] ) ){
      $aData = $this->aProducts[$iProduct];
      $aData['aCategories'] = $this->aProductsPages[$iProduct];
      $aData['sPrice'] = is_numeric( $this->aProducts[$iProduct]['mPrice'] ) ? displayPrice( $this->aProducts[$iProduct]['mPrice'] ) : $this->aProducts[$iProduct]['mPrice'];
      if( !isset( $aData['sDescriptionFull'] ) ){
        $aData['sDescriptionFull'] = getFullDescription( DIR_DATABASE_PRODUCTS, $iProduct );
        if( !empty( $aData['sDescriptionFull'] ) )
          $aData['bDescriptionFromFile'] = true;
      }

      return $aData;
    }
    else
      return null;
  } // end function throwProduct

  /**
  * Returns a product name
  * @return string
  * @param int $iProduct
  * @param bool $bLink
  */
  public function getProductName( $iProduct, $bLink = null ){
    if( isset( $this->aProducts[$iProduct] ) ){
      return ( isset( $bLink ) ? '<a href="'.$this->aProducts[$iProduct]['sLinkName'].'">' : null ).$this->aProducts[$iProduct]['sName'].( isset( $bLink ) ? '</a>' : null );
    }
  } // end function getProductName

  /**
  * Returns product's short description
  * @return string
  * @param int $iProduct
  */
  public function getProductShortDescription( $iProduct ){
    if( isset( $this->aProducts[$iProduct] ) )
      return isset( $this->aProducts[$iProduct]['sDescriptionShort'] ) ? changeTxt( $this->aProducts[$iProduct]['sDescriptionShort'], 'nlNds' ) : null;
  } // end function getProductShortDescription

  /**
  * Returns product's price
  * @return string
  * @param int $iProduct
  */
  public function getProductPrice( $iProduct ){
    if( isset( $this->aProducts[$iProduct] ) )
      return is_numeric( $this->aProducts[$iProduct]['mPrice'] ) ? displayPrice( $this->aProducts[$iProduct]['mPrice'] ) : $this->aProducts[$iProduct]['mPrice'];
  } // end function getProductPrice

  /**
  * Returns product's basket link
  * @return string
  * @param int $iProduct
  */
  public function getProductBasketLink( $iProduct ){
    if( isset( $this->aProducts[$iProduct] ) && isset( $this->aProducts[$iProduct]['mPrice'] ) && is_numeric( $this->aProducts[$iProduct]['mPrice'] ) ){
      $oPage = Pages::getInstance( );
      if( !empty( $GLOBALS['config']['basket_page'] ) && isset( $oPage->aPages[$GLOBALS['config']['basket_page']] ) ){
        return '<a href="'.$oPage->aPages[$GLOBALS['config']['basket_page']]['sLinkName'].'&amp;iProductAdd='.$iProduct.'&amp;iQuantity=1" rel="nofollow" title="'.$GLOBALS['lang']['Basket_add'].': '.$this->aProducts[$iProduct]['sName'].'">'.$GLOBALS['lang']['Basket_add'].'</a>';
      }
    }
  } // end function getProductBasketLink

  /**
  * Returns product's default image
  * @return string
  * @param int $iProduct
  * @param int $iLinkDisplay, 1 - no link, 2 - link to product, 3 - link to image
  */
  public function getProductDefaultImage( $iProduct, $iLinkDisplay = 1 ){
    if( isset( $this->aProducts[$iProduct] ) ){
      $oFile = Files::getInstance( );
      return $oFile->getDefaultImage( $iProduct, 2, ( $iLinkDisplay > 1 ) ? true : null, $iLinkDisplay == 2 ? $this->aProducts[$iProduct]['sLinkName'] : null );
    }
  } // end function getProductDefaultImage

  /**
  * Generates a list of products
  * @return array
  * @param bool $bKeysInverted
  */
  protected function generateProductsListArray( $bKeysInverted = null ){
    if( isset( $this->aProducts ) ){
      foreach( $this->aProductsPages as $iProduct => $aData ){
        foreach( $this->aPages as $iValue ){
          if( isset( $aData[$iValue] ) && !isset( $aProducts[$iProduct] ) ){
            $aReturn[] = $iProduct;
            $aProducts[$iProduct] = true;
          }
        } // end foreach
      } // end foreach

      if( isset( $aReturn ) ){
        if( isset( $bKeysInverted ) )
          return $aProducts;
        else
          return $aReturn;
      }
    }
  } // end function generateProductsListArray

  /**
  * Generates a list of products that match the searched phrase
  * @return array
  * @param string $sPhrase
  */
  public function generateProductsSearchListArray( $sPhrase ){
    if( isset( $this->aProducts ) ){
      $aWords = getWordsFromPhrase( $sPhrase );
      $iCount = count( $aWords );

      if( isset( $this->aPages ) ){
        $aProductsPages = $this->generateProductsListArray( true );
      }

      foreach( $this->aProducts as $iProduct => $aProduct ){
        $bSearch = null;
        if( isset( $this->aPages ) ){
          if( isset( $aProductsPages[$iProduct] ) )
            $bSearch = true;
        }
        else
          $bSearch = true;

        if( isset( $bSearch ) && findWords( $aWords, $iCount, implode( ' ', $aProduct ) ) )
          $aProducts[] = $iProduct;
      }

      if( isset( $aProducts ) )
        return $aProducts;
    }
  } // end function generateProductsSearchListArray

  /**
  * Returns products page tree
  * @return string
  * @param int  $iProduct
  */
  public function throwProductsPagesTree( $iProduct ){
    global $oPage;
    if( isset( $this->aProductsPages[$iProduct] ) ){
      $content = null;
      $oPage->mData = null;
      foreach( $this->aProductsPages[$iProduct] as $iPage ){
        if( isset( $content ) )
          $content .= '<em>|</em>';
        if( isset( $this->mData[$iPage] ) ){
          $content .= $this->mData[$iPage];
        }
        else{
          $sTree = $oPage->throwPagesTree( $iPage );
          if( empty( $sTree ) && isset( $oPage->aPages[$iPage] ) )
            $sTree .= '<a href="'.$oPage->aPages[$iPage]['sLinkName'].'">'.$oPage->aPages[$iPage]['sName'].'</a>';
          $content .= $this->mData[$iPage] = $sTree;
        }
      }

      return $content;
    }
  } // end function throwProductsPagesTree

  /**
  * Sorts products
  * @return array
  * @param array  $aProducts
  * @param string $sSort
  */
  public function sortProducts( $aProducts, $sSort = null ){
    $iCount = count( $aProducts );
    $sFunctionSort = 'sort';

    if( $sSort == 'status' ){
      $sKey = 'iStatus';
    }
    elseif( $sSort == 'price' ){
      $sKey = 'mPrice';    
    }
    elseif( $sSort == 'name' ){
      $sKey = 'sName';    
    }
    else{
      $sFunctionSort = 'rsort';
      $sKey = 'iProduct';    
    }

    for( $i = 0; $i < $iCount; $i++ ){
      $mValue = $this->aProducts[$aProducts[$i]][$sKey]; 
      $aSort[$i][0] = $mValue;
      $aSort[$i][1] = $aProducts[$i];
    } // end for

    $sFunctionSort( $aSort );

    for( $i = 0; $i < $iCount; $i++ ){
      $aProducts[$i] = $aSort[$i][1];
    } // end for    
    return $aProducts;
  } // end function sortProducts 

  /**
  * Return links with sorting option
  * @return string
  * @param string $sSortingLink
  * @param string $sSort
  */
  private function throwSortLinks( $sSortingLink = null, $sSort = null ){
    global $lang;
    $aSorts = Array( 'default' => $lang['Default'], 'name' => $lang['Name'], 'price' => $lang['Price'] );
    if( isset( $sSortingLink ) ){
      $sLinks = null;
      foreach( $aSorts as $sLink => $sName ){
        if( ( $sLink == 'default' && !isset( $sSort ) ) || ( isset( $sSort ) && $sSort == $sLink ) )
          $sLinks .= '<li>'.$sName.'</li>';
        elseif( $sLink == 'default' && isset( $sSort ) ){
          $sLinks .= '<li><a href="'.$sSortingLink.'">'.$sName.'</a></li>';
        }
        else{
          $sLinks .= '<li><a href="'.$sSortingLink.'&amp;sSort='.$sLink.'">'.$sName.'</a></li>';
        }
      } // end foreach
      return '<div class="sort">'.$lang['Sort_by'].'<ul>'.$sLinks.'</ul></div>';
    }
    else{
      return $aSorts;
    }
  } // end function throwSortLinks

}
?>
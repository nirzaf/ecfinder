<?php
final class ProductsAdmin extends Products
{
  private static $oInstance = null;

  public static function getInstance( ){  
    if( !isset( self::$oInstance ) ){  
      self::$oInstance = new ProductsAdmin( );  
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
  * Lists products
  * @return string
  * @param int $iContent
  */
  public function listProductsAdmin( $iList = null ){
    global $lang;

    $content= null;

    if( isset( $GLOBALS['iPageSearch'] ) && is_numeric( $GLOBALS['iPageSearch'] ) ){
      $oPage = Pages::getInstance( );
      $aData = $oPage->throwAllChildrens( $GLOBALS['iPageSearch'] );
      if( isset( $aData ) ){
        foreach( $aData as $iValue ){
          $this->aPages[$iValue] = $iValue;
        }
      }
      $this->aPages[$GLOBALS['iPageSearch']] = $GLOBALS['iPageSearch'];
    }

    if( isset( $GLOBALS['sPhrase'] ) && !empty( $GLOBALS['sPhrase'] ) ){
      $aProducts = $this->generateProductsSearchListArray( $GLOBALS['sPhrase'] );
    }
    elseif( isset( $this->aPages ) ){
      $aProducts = $this->generateProductsListArray( );
    }
    else{
      if( isset( $this->aProducts ) ){
        foreach( $this->aProducts as $iProduct => $aData ){
          $aProducts[] = $iProduct;
        } // end foreach
      }
    }

    if( isset( $aProducts ) ){
      if( isset( $_GET['sSort'] ) && !empty( $_GET['sSort'] ) && $_GET['sSort'] != 'position' ){
        $aProducts = $this->sortProducts( $aProducts, $_GET['sSort'] );
      }

      $iCount = count( $aProducts );
      if( !isset( $iList ) ){
        $iList = $GLOBALS['config']['admin_list'];
      }

      $aKeys = countPageNumber( $iCount, ( isset( $_GET['iPage'] ) ? $_GET['iPage'] : null ), $iList );
      $this->mData = null;

      for( $i = $aKeys['iStart']; $i < $aKeys['iEnd']; $i++ ){
        $aData = $this->aProducts[$aProducts[$i]];

        $aData['sPages'] = str_replace( '|', '&nbsp;|&nbsp;', strip_tags( $this->throwProductsPagesTree( $aData['iProduct'] ) ) );

        $content .= '<tr class="l'.( ( $i % 2 ) ? 0: 1 ).'" onmouseover="showPreviewButton( this )" onmouseout="hidePreviewButton( this )">
          <td class="id">
            '.$aData['iProduct'].'
          </td>
          <td class="name">
            <a href="?p=products-form&amp;iProduct='.$aData['iProduct'].'">'.$aData['sName'].'</a><a href="./'.$aData['sLinkName'].( $GLOBALS['config']['language_in_url'] !== true ? '&amp;sLang='.LANGUAGE : null ).'" target="_blank" class="preview"><img src="'.DIR_TEMPLATES.'admin/img/ico_prev.gif" alt="'.$lang['preview'].'" title="'.$lang['preview'].'" /></a>
          </td>
          <td class="pages">
            '.$aData['sPages'].'
          </td>
          <td class="price">
            <input type="text" name="aPrices['.$aData['iProduct'].']" value="'.$aData['mPrice'].'" class="inputr" size="8" />
          </td>
          <td class="position">
            <input type="text" name="aPositions['.$aData['iProduct'].']" value="'.$aData['iPosition'].'" class="inputr" size="2" maxlength="3" />
          </td>
          <td class="status">
            <input type="checkbox" name="aStatus['.$aData['iProduct'].']"'.( ( $aData['iStatus'] == 1 ) ? ' checked="checked"' : null ).' value="1" />
          </td>
          <td class="options">
            <a href="?p=products-form&amp;iProduct='.$aData['iProduct'].'"><img src="'.DIR_TEMPLATES.'admin/img/ico_edit.gif" alt="'.$lang['edit'].'" title="'.$lang['edit'].'" /></a>
            <a href="?p=products-delete&amp;iProduct='.$aData['iProduct'].'" onclick="return delConfirm( '.$aData['iProduct'].' )"><img src="'.DIR_TEMPLATES.'admin/img/ico_del.gif" alt="'.$lang['delete'].'" title="'.$lang['delete'].'"/></a>  
          </td>
        </tr>';
      } // end for

      if( isset( $content ) ){
        $GLOBALS['sPages'] = countPagesClassic( $iCount, $iList, $aKeys['iPageNumber'], changeUri( $_SERVER['REQUEST_URI'] ) );
        return $content;
      }
    }
  } // end function listProductsAdmin

  /**
  * Saves product's position, status and price
  * @return void
  * @param array  $aForm
  */
  public function saveProducts( $aForm ){
    if( isset( $aForm['aPositions'] ) && is_array( $aForm['aPositions'] ) ){
      foreach( $this->aProducts as $iProduct => $aData ){
        if( isset( $aForm['aPositions'][$iProduct] ) ){
          $aForm['aPositions'][$iProduct] = trim( $aForm['aPositions'][$iProduct] );
          $iStatus = isset( $aForm['aStatus'][$iProduct] ) ? 1 : 0;
          
          if( is_numeric( str_replace( ',', '.', $aForm['aPrices'][$iProduct] ) ) )
            $aForm['aPrices'][$iProduct] = normalizePrice( trim( $aForm['aPrices'][$iProduct] ) );
          else
            $aForm['aPrices'][$iProduct] = trim( $aForm['aPrices'][$iProduct] );

          if( is_numeric( $aForm['aPositions'][$iProduct] ) && $aForm['aPositions'][$iProduct] != $aData['iPosition'] ){
            $this->aProducts[$iProduct]['iPosition'] = $aForm['aPositions'][$iProduct];
            $bChanged = true;
          }
          if( $iStatus != $aData['iStatus'] ){
            $this->aProducts[$iProduct]['iStatus'] = $iStatus;
            $bChanged = true;
          }
          if( $aForm['aPrices'][$iProduct] != $aData['mPrice'] ){
            $this->aProducts[$iProduct]['mPrice'] = $aForm['aPrices'][$iProduct];
            $bChanged = true;
          }
        }
      } // end foreach

      if( isset( $bChanged ) ){
        $oFFS = FlatFilesSerialize::getInstance( );
        $oFFS->saveData( DB_PRODUCTS, $this->createArray( ) );
      }
    }
  } // end function saveProducts

  /**
  * Saves product data
  * @return int
  * @param array  $aForm
  */
  public function saveProduct( $aForm ){
    $oFile = FilesAdmin::getInstance( );
    $oFFS = FlatFilesSerialize::getInstance( );

    if( isset( $aForm['iProduct'] ) && is_numeric( $aForm['iProduct'] ) && isset( $this->aProducts[$aForm['iProduct']] ) ){
    }
    else{
      $aForm['iProduct'] = $oFFS->throwLastId( DB_PRODUCTS, 'iProduct' ) + 1;
    }

    if( empty( $aForm['sTheme'] ) )
      unset( $aForm['sTheme'] );

    if( !isset( $aForm['iPosition'] ) || !is_numeric( $aForm['iPosition'] ) || $aForm['iPosition'] < -99 || $aForm['iPosition'] > 999 )
      $aForm['iPosition'] = 0;

    if( !isset( $aForm['iStatus'] ) )
      $aForm['iStatus'] = 0;

    if( is_numeric( str_replace( ',', '.', $aForm['mPrice'] ) ) )
      $aForm['mPrice'] = normalizePrice( $aForm['mPrice'] );

    if( $GLOBALS['config']['products_full_description_to_file'] === true ){
      if( !empty( $aForm['sDescriptionFull'] ) ){
        $aForm['sDescriptionFull'] = stripslashes( str_replace( '|n|', "\n", $aForm['sDescriptionFull'] ) );
        saveFullDescription( DIR_DATABASE_PRODUCTS, $aForm['iProduct'], $aForm['sDescriptionFull'] );
      }
      else
        deleteFullDescription( DIR_DATABASE_PRODUCTS, $aForm['iProduct'] );
      $aForm['sDescriptionFull'] = null;
    }
    else
      deleteFullDescription( DIR_DATABASE_PRODUCTS, $aForm['iProduct'] );

    $aForm = changeMassTxt( $aForm, '', Array( 'sDescriptionShort', 'Nds' ), Array( 'sDescriptionFull', 'Nds' ), Array( 'sMetaDescription', 'Nds' ) );

    if( isset( $aForm['aPages'] ) && is_array( $aForm['aPages'] ) ){
      if( isset( $this->aProductsPages[$aForm['iProduct']] ) )
        unset( $this->aProductsPages[$aForm['iProduct']] );
      foreach( $aForm['aPages'] as $iPage ){
        $this->aProductsPages[$aForm['iProduct']][$iPage] = (int) $iPage;
      } // end foreach
      $oFFS->saveData( DB_PRODUCTS_PAGES, $this->aProductsPages );
    }

    // deleting keys from array $aForm that not exists in $aProductsFields in database/_fields.php
    $this->aProducts[$aForm['iProduct']] = $aForm;

    if( isset( $aForm['aFilesDescription'] ) || isset( $aForm['aDirFiles'] ) )
      $oFile->generateCache( true, true );
    if( isset( $aForm['aFilesDescription'] ) )
      $oFile->saveFiles( $aForm, 2, $aForm['iProduct'] );
    if( isset( $aForm['aDirFiles'] ) )
      $oFile->addFilesFromServer( $aForm, $aForm['iProduct'], 2, 'iProduct' );  

    $oFFS->saveData( DB_PRODUCTS, $this->createArray( ) );

    return $aForm['iProduct'];
  } // end function saveProduct

  /**
  * Deletes a product
  * @return void
  * @param int  $iProduct
  * @param bool $bWithoutFiles
  */
  public function deleteProduct( $iProduct, $bWithoutFiles ){

    if( isset( $this->aProducts[$iProduct] ) ){
      unset( $this->aProducts[$iProduct] );
      $oFile = FilesAdmin::getInstance( );
      $oFFS = FlatFilesSerialize::getInstance( );
      
      $aSave = $this->createArray( $this->aProducts );
      $oFFS->saveData( DB_PRODUCTS, $aSave );

      if( isset( $this->aProductsPages[$iProduct] ) ){
        unset( $this->aProductsPages[$iProduct] );
        $oFFS->saveData( DB_PRODUCTS_PAGES, $this->aProductsPages );
      }

      deleteFullDescription( DIR_DATABASE_PRODUCTS, $iProduct );
      $oFile->deleteFiles( Array( $iProduct => $iProduct ), 2, 'iProduct', $bWithoutFiles );
    }

  } // end function deleteProduct

  /**
  * Lists recently added products
  * @return string
  */
  public function listLastProducts( ){
    if( isset( $this->aProducts) ){
      $iMax = 5;
      $aProducts = $this->sortProducts( array_keys( $this->aProducts ) );
      $iCount = count( $aProducts );
      if( $iCount > $iMax )
        $iCount = $iMax;
      
      $content = null;

      for( $i = 0; $i < $iCount; $i++ ){
        $aData = $this->aProducts[$aProducts[$i]];
        $content .= '<tr><td class="id">'.$aData['iProduct'].'</td><td class="name"><a href="?p=products-form&amp;iProduct='.$aData['iProduct'].'">'.$aData['sName'].'</a></td><td class="data">'.$aData['mPrice'].'</td></tr>';
      } // end for
      
      return '<table><thead><tr><td>'.$GLOBALS['lang']['Id'].'</td><td>'.$GLOBALS['lang']['Name'].'</td><td>'.$GLOBALS['lang']['Price'].'</td></tr></thead>'.$content.'</tbody></table>';
    }
  } // end function listLastProducts

  /**
  * Function creates an array to be saved to the database
  * @return array
  */
  protected function createArray( ){
    if( isset( $this->aProducts ) ){
      // Sorting array before it will save to file 
      foreach( $this->aProducts as $iKey => $aValue ){
        $aSort[$iKey][0] = (int) $aValue['iPosition'];
        $aSort[$iKey][1] = $aValue['sName'];
        $aSort[$iKey][2] = $aValue['iProduct'];
      } // end foreach

      if( isset( $aSort ) ){
        sort( $aSort );
        
        foreach( $aSort as $iKey => $aValue ){
          $aSave[] = compareArrays( $this->aFields, $this->aProducts[$aValue[2]] );
        } // end foreach

        return $aSave;
      }
    }
  } // end function createArray

};
?>
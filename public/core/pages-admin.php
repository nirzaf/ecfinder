<?php
final class PagesAdmin extends Pages{

  private static $oInstance = null;

  public static function getInstance( ){  
    if( !isset( self::$oInstance ) ){  
      self::$oInstance = new PagesAdmin( );  
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
  * Returns the list of pages
  * @return string
  */
  public function listPagesAdmin( ){
    global $lang;
    if( isset( $this->aPagesParentsTypes ) ){
      $content = null;

      foreach( $this->aPagesParentsTypes as $iType => $aPages ){
        $iCount = count( $aPages );
       
        if( isset( $_GET['sSort'] ) && !empty( $_GET['sSort'] ) ){
          $aPages = $this->sortPages( $aPages, $_GET['sSort'] );
        }

        for( $i = 0; $i < $iCount; $i++ ){
          $aData = $this->aPages[$aPages[$i]];
          $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
          $aData['iDepth'] = 0;

          $aData['sStatusBox'] = ( $aData['iStatus'] == 1 ) ? ' checked="checked"' : null;
          if( $i == 0 )
            $content .= '<tr class="type"><td colspan="5">'.$GLOBALS['aMenuTypes'][$iType].'</td></tr>';

          $content .= '<tr class="l'.$aData['iDepth'].'" onmouseover="showPreviewButton( this )" onmouseout="hidePreviewButton( this )"><td class="id">'.$aData['iPage'].'</td>
            <th class="name">
              <a href="?p=pages-form&amp;iPage='.$aData['iPage'].'">'.$aData['sName'].'</a><a href="./'.$aData['sLinkName'].( $GLOBALS['config']['language_in_url'] !== true ? ( ( $GLOBALS['config']['start_page'] == $aData['iPage'] ? '?' : '&amp;' ).'sLang='.LANGUAGE ) : null ).'" target="_blank" class="preview"><img src="'.DIR_TEMPLATES.'admin/img/ico_prev.gif" alt="'.$lang['preview'].'" title="'.$lang['preview'].'" /></a>
            </th>
            <td class="position">
              <input type="text" name="aPositions['.$aData['iPage'].']" value="'.$aData['iPosition'].'" class="inputr" size="2" maxlength="3" />
            </td>
            <td class="status">
              <input type="checkbox" name="aStatus['.$aData['iPage'].']" '.$aData['sStatusBox'].' value="1" />
            </td>
            <td class="options">
              <a href="?p=pages-form&amp;iPage='.$aData['iPage'].'"><img src="'.DIR_TEMPLATES.'admin/img/ico_edit.gif" alt="'.$lang['edit'].'" title="'.$lang['edit'].'" /></a>
              <a href="?p=pages-delete&amp;iPage='.$aData['iPage'].'" onclick="return delConfirm( '.$aData['iPage'].' )"><img src="'.DIR_TEMPLATES.'admin/img/ico_del.gif" alt="'.$lang['delete'].'" title="'.$lang['delete'].'"/></a>  
            </td>
          </tr>';
          if( isset( $this->aPagesChildrens[$aData['iPage']] ) ){
            $content .= $this->listSubpagesAdmin( $aData['iPage'], $aData['iDepth'] + 1 );
          }
        } // end for
      }
      if( isset( $content ) )
        return $content;
    }
  } // end function listPagesAdmin

  /**
  * Returns a list of subpages of a named page
  * @return string
  * @param int $iPageParent
  * @param int $iDepth
  */
  public function listSubPagesAdmin( $iPageParent, $iDepth ){
    global $lang;
    $content = null;
    $iCount = count( $this->aPagesChildrens[$iPageParent] );
    
    if( isset( $_GET['sSort'] ) && !empty( $_GET['sSort'] ) ){
      $this->aPagesChildrens[$iPageParent] = $this->sortPages( $this->aPagesChildrens[$iPageParent], $_GET['sSort'] );
    }

    for( $i = 0; $i < $iCount; $i++ ){
      $aData = $this->aPages[$this->aPagesChildrens[$iPageParent][$i]];
      $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
      $aData['iDepth'] = $iDepth;
      $aData['sStatusBox'] = ( $aData['iStatus'] == 1 ) ? ' checked="checked"' : null;

      $content .= '<tr class="l'.$aData['iDepth'].'" onmouseover="showPreviewButton( this )" onmouseout="hidePreviewButton( this )"><td class="id">'.$aData['iPage'].'</td>
        <th class="name">
          <a href="?p=pages-form&amp;iPage='.$aData['iPage'].'">'.$aData['sName'].'</a><a href="./'.$aData['sLinkName'].( $GLOBALS['config']['language_in_url'] !== true ? ( ( $GLOBALS['config']['start_page'] == $aData['iPage'] ? '?' : '&amp;' ).'sLang='.LANGUAGE ) : null ).'" target="_blank" class="preview"><img src="'.DIR_TEMPLATES.'admin/img/ico_prev.gif" alt="'.$lang['preview'].'" title="'.$lang['preview'].'" /></a>
        </th>
        <td class="position">
          <input type="text" name="aPositions['.$aData['iPage'].']" value="'.$aData['iPosition'].'" class="inputr" size="2" maxlength="3" />
        </td>
        <td class="status">
          <input type="checkbox" name="aStatus['.$aData['iPage'].']" '.$aData['sStatusBox'].' value="1" />
        </td>
        <td class="options">
          <a href="?p=pages-form&amp;iPage='.$aData['iPage'].'"><img src="'.DIR_TEMPLATES.'admin/img/ico_edit.gif" alt="'.$lang['edit'].'" title="'.$lang['edit'].'" /></a>
          <a href="?p=pages-delete&amp;iPage='.$aData['iPage'].'" onclick="return delConfirm( '.$aData['iPage'].' )"><img src="'.DIR_TEMPLATES.'admin/img/ico_del.gif" alt="'.$lang['delete'].'" title="'.$lang['delete'].'"/></a>  
        </td>
      </tr>';
      if( isset( $this->aPagesChildrens[$aData['iPage']] ) ){
        $content .= $this->listSubpagesAdmin( $aData['iPage'], $aData['iDepth'] + 1 );
      }
    } // end for
    return $content;
  } // end function listSubPagesAdmin

  /**
  * Returns a list of pages containing the searched phrase
  * @return string
  * @param string $sPhrase
  */
  public function listPagesAdminSearch( $sPhrase ){
    global $lang;
    $aPages = $this->generatePagesSearchListArray( $sPhrase );

    if( isset( $aPages ) ){
      if( isset( $_GET['sSort'] ) && !empty( $_GET['sSort'] ) ){
        $aPages = $this->sortPages( $aPages, $_GET['sSort'] );
      }
      $content = null;
      $iCount = count( $aPages );
      for( $i = 0; $i < $iCount; $i++ ){
        $aData = $this->aPages[$aPages[$i]];
        $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData['sStatusBox'] = ( $aData['iStatus'] == 1 ) ? ' checked="checked"' : null;

        $content .= '<tr class="l" onmouseover="showPreviewButton( this )" onmouseout="hidePreviewButton( this )"><td class="id">'.$aData['iPage'].'</td>
          <th class="name">
            <a href="?p=pages-form&amp;iPage='.$aData['iPage'].'">'.$aData['sName'].'</a><a href="./'.$aData['sLinkName'].( $GLOBALS['config']['language_in_url'] !== true ? ( ( $GLOBALS['config']['start_page'] == $aData['iPage'] ? '?' : '&amp;' ).'sLang='.LANGUAGE ) : null ).'" target="_blank" class="preview"><img src="'.DIR_TEMPLATES.'admin/img/ico_prev.gif" alt="'.$lang['preview'].'" title="'.$lang['preview'].'" /></a>
          </th>
          <td class="position">
            <input type="text" name="aPositions['.$aData['iPage'].']" value="'.$aData['iPosition'].'" class="inputr" size="2" maxlength="3" />
          </td>
          <td class="status">
            <input type="checkbox" name="aStatus['.$aData['iPage'].']" '.$aData['sStatusBox'].' value="1" />
          </td>
          <td class="options">
            <a href="?p=pages-form&amp;iPage='.$aData['iPage'].'"><img src="'.DIR_TEMPLATES.'admin/img/ico_edit.gif" alt="'.$lang['edit'].'" title="'.$lang['edit'].'" /></a>
            <a href="?p=pages-delete&amp;iPage='.$aData['iPage'].'" onclick="return delConfirm( '.$aData['iPage'].' )"><img src="'.DIR_TEMPLATES.'admin/img/ico_del.gif" alt="'.$lang['delete'].'" title="'.$lang['delete'].'"/></a>  
          </td>
        </tr>';
      } // end for

      return $content;
    }
  } // end function listPagesAdminSearch

  /**
  * Returns a list of pages in form of a HTML select
  * @return string
  * @param int  $iPageSelected
  */
  public function throwPagesSelectAdmin( $iPageSelected ){
    if( isset( $this->aPagesParentsTypes ) ){
      $content = null;
      foreach( $this->aPagesParentsTypes as $iType => $aPages ){
        $iCount = count( $aPages );
        $sType = $GLOBALS['aMenuTypes'][$iType];
        $content .= '<option value="0" disabled="disabled" style="color:#999;">'.$sType.'</option>';

        for( $i = 0; $i < $iCount; $i++ ){
          $sSelected = ( $iPageSelected == $this->aPages[$aPages[$i]]['iPage'] ) ? ' selected="selected"' : null;
          $content .= '<option'.( $this->aPages[$aPages[$i]]['iStatus'] == 0 ? ' style="color:#bbb;"' : null ).' value="'.$this->aPages[$aPages[$i]]['iPage'].'"'.$sSelected.'>'.$this->aPages[$aPages[$i]]['sName'].'</option>';
          if( isset( $this->aPagesChildrens[$aPages[$i]] ) ){
            $content .= $this->throwSubPagesSelectAdmin( $iPageSelected, $aPages[$i], 1 );
          }
        } // end for
      }
      return $content;
    }
  } // end function throwPagesSelectAdmin

  /**
  * Returns a list of subpages in form of a HTML select
  * @return string
  * @param int $iPageSelected
  * @param int $iPageParent
  * @param int $iDepth
  */
  public function throwSubPagesSelectAdmin( $iPageSelected, $iPageParent, $iDepth = 1 ){
    $iCount = count( $this->aPagesChildrens[$iPageParent] );
    $sSeparator = ( $iDepth > 0 ) ? str_repeat( '&nbsp;&nbsp;', $iDepth ) : null;
    $content = null;

    for( $i = 0; $i < $iCount; $i++ ){
      $iPage = $this->aPagesChildrens[$iPageParent][$i];
      $sSelected = ( $iPageSelected == $iPage ) ? ' selected="selected"' : null;
      $content .= '<option'.( $this->aPages[$iPage]['iStatus'] == 0 ? ' style="color:#bbb;"' : null ).' value="'.$this->aPages[$iPage]['iPage'].'"'.$sSelected.'>'.$sSeparator.$this->aPages[$iPage]['sName'].'</option>';
      if( isset( $this->aPagesChildrens[$iPage] ) ){
        $content .= $this->throwSubPagesSelectAdmin( $iPageSelected, $iPage, $iDepth + 1 );
      }
    } // end for
    return $content;
  } // end function throwSubPagesSelectAdmin

  /**
  * Deletes a page and its subpages
  * @return void
  * @param int  $iPage
  * @param bool $bWithoutFiles
  */
  public function deletePage( $iPage, $bWithoutFiles ){
    $oFile = FilesAdmin::getInstance( );

    // array containing the page to be deleted
    $this->mData[$iPage] = true;
    // if a page has sub-pages, the script will also delete the subpages
    if( isset( $this->aPagesChildrens[$iPage] ) ){
      $this->throwSubpagesIdAdmin( $iPage );
    }

    foreach( $this->mData as $iKey => $bValue ){
      unset( $this->aPages[$iKey] );
      deleteFullDescription( DIR_DATABASE_PAGES, $iKey );
    } // end foreach
    $aSave = $this->createArray( $this->aPages );

    $oFFS = FlatFilesSerialize::getInstance( );
    $oFFS->saveData( DB_PAGES, $aSave );

    $oFile->deleteFiles( $this->mData, 1, 'iPage', $bWithoutFiles );

  } // end function deletePage

  /**
  * Returns ids of all subpages of a given page
  * @return void
  * @param int  $iPage
  */
  private function throwSubpagesIdAdmin( $iPage ){
    $iCount = count( $this->aPagesChildrens[$iPage] );
    for( $i = 0; $i < $iCount; $i++ ){
      $this->mData[$this->aPagesChildrens[$iPage][$i]] = true;
      if( isset( $this->aPagesChildrens[$this->aPagesChildrens[$iPage][$i]] ) ){
        $this->throwSubpagesIdAdmin( $this->aPagesChildrens[$iPage][$i] );
      }
    } // end for
  } // end function throwSubpagesIdAdmin

  /**
  * Saves page data including data of all attached images and files
  * @return int
  * @param array  $aForm
  */
  public function savePage( $aForm ){
    $oFFS = FlatFilesSerialize::getInstance( );
    $oFile = FilesAdmin::getInstance( );

    $aData = $this->aPages;

    if( isset( $aForm['iPage'] ) && is_numeric( $aForm['iPage'] ) && isset( $aData[$aForm['iPage']] ) ){
    }
    else{
      $aForm['iPage'] = $oFFS->throwLastId( DB_PAGES, 'iPage' ) + 1;
    }
    
    if( empty( $aForm['iPageParent'] ) || ( !empty( $aForm['iPageParent'] ) && $aForm['iPageParent'] == $aForm['iPage'] ) )
      $aForm['iPageParent'] = 0;
    else{
      if( $aForm['iPageParent'] > 0 && isset( $aData[$aForm['iPageParent']] ) ){
        $aForm['iType'] = $aData[$aForm['iPageParent']]['iType'];
      }
    }

    if( empty( $aForm['sTheme'] ) )
      unset( $aForm['sTheme'] );

    if( isset( $aForm['iPosition'] ) && !is_numeric( $aForm['iPosition'] ) )
      $aForm['iPosition'] = 0;

    if( !isset( $aForm['iStatus'] ) )
      $aForm['iStatus'] = 0;

    if( $GLOBALS['config']['pages_full_description_to_file'] === true ){
      if( !empty( $aForm['sDescriptionFull'] ) ){
        $aForm['sDescriptionFull'] = stripslashes( str_replace( '|n|', "\n", $aForm['sDescriptionFull'] ) );
        saveFullDescription( DIR_DATABASE_PAGES, $aForm['iPage'], $aForm['sDescriptionFull'] );
      }
      else
        deleteFullDescription( DIR_DATABASE_PAGES, $aForm['iPage'] );
      $aForm['sDescriptionFull'] = null;
    }
    else
      deleteFullDescription( DIR_DATABASE_PAGES, $aForm['iPage'] );

    $aForm = changeMassTxt( $aForm, '', Array( 'sDescriptionShort', 'Nds' ), Array( 'sDescriptionFull', 'Nds' ), Array( 'sMetaDescription', 'Nds' ) );

    if( isset( $this->aPages[$aForm['iPage']] ) && $aForm['iStatus'] == 0 && $aForm['iStatus'] != $this->aPages[$aForm['iPage']]['iStatus'] && isset( $this->aPagesChildrens[$aForm['iPage']] ) ){
      $this->mData = null;
      $this->throwSubpagesIdAdmin( $aForm['iPage'] );
      foreach( $this->mData as $iPage => $bValue ){
        $this->aPages[$iPage]['iStatus'] = 0;
      } // end foreach
    }

    // deleting keys from the $aForm array that don't exists in $aPagesFields in database/_fields.php
    $this->aPages[$aForm['iPage']] = $aForm;
  
    if( isset( $aForm['aFilesDescription'] ) || isset( $aForm['aDirFiles'] ) )
      $oFile->generateCache( true );
    if( isset( $aForm['aFilesDescription'] ) )
      $oFile->saveFiles( $aForm, 1, $aForm['iPage'] );
    if( isset( $aForm['aDirFiles'] ) )
      $oFile->addFilesFromServer( $aForm, $aForm['iPage'], 1, 'iPage' );    

    $oFFS->saveData( DB_PAGES, $this->createArray( ) );
    return $aForm['iPage'];
  } // end function savePage 

  /**
  * Saves page's position and status
  * @return void
  * @param array  $aForm
  */
  public function savePages( $aForm ){
    if( isset( $aForm['aPositions'] ) && is_array( $aForm['aPositions'] ) ){
      foreach( $this->aPages as $iPage => $aData ){
        if( isset( $aForm['aPositions'][$iPage] ) ){
          $aForm['aPositions'][$iPage] = trim( $aForm['aPositions'][$iPage] );
          if( is_numeric( $aForm['aPositions'][$iPage] ) && $aForm['aPositions'][$iPage] != $aData['iPosition'] ){
            $this->aPages[$iPage]['iPosition'] = $aForm['aPositions'][$iPage];
            $bChanged = true;
          }
          
          $iStatus = isset( $aForm['aStatus'][$iPage] ) ? 1 : 0;
          
          if( !isset( $aChangedStatus[$iPage] ) && $iStatus != $this->aPages[$iPage]['iStatus'] ){
            $this->aPages[$iPage]['iStatus'] = $iStatus;
            $bChanged = true;

            if( $iStatus == 0 && isset( $this->aPagesChildrens[$iPage] ) ){
              $this->mData = null;
              $this->throwSubpagesIdAdmin( $iPage );
              foreach( $this->mData as $iPage => $bValue ){
                $this->aPages[$iPage]['iStatus'] = 0;
                $aChangedStatus[$iPage] = true;
              } // end foreach
            }
          }
        }
      } // end foreach

      if( isset( $bChanged ) ){
        $oFFS = FlatFilesSerialize::getInstance( );
        $oFFS->saveData( DB_PAGES, $this->createArray( ) );
      }
    }
  } // end function savePages


  /**
  * Function creates a page data array before it's saved to the database
  * @return array
  */
  protected function createArray( ){
    if( isset( $this->aPages ) ){
      // Sorting the array before it is saved to the database
      foreach( $this->aPages as $iKey => $aValue ){
        $aSort[$iKey][0] = (int) $aValue['iPosition'];
        $aSort[$iKey][1] = $aValue['sName'];
        $aSort[$iKey][2] = $aValue['iPage'];
      } // end foreach

      if( isset( $aSort ) ){
        sort( $aSort );
        
        foreach( $aSort as $iKey => $aValue ){
          $aSave[] = compareArrays( $this->aFields, $this->aPages[$aValue[2]] );
        } // end foreach

        return $aSave;
      }
    }
  } // end function createArray

  /**
  * Lists recently added pages
  * @return string
  */
  public function listLastPages( ){
    if( isset( $this->aPages ) ){

      $iMax = 5;
      $aPages = $this->sortPages( array_keys( $this->aPages ) );
      $iCount = count( $aPages );
      if( $iCount > $iMax )
        $iCount = $iMax;
      
      $content = null;
      for( $i = 0; $i < $iCount; $i++ ){
        $aData = $this->aPages[$aPages[$i]];
        $content .= '<tr><td class="id">'.$aData['iPage'].'</td><td class="name"><a href="?p=pages-form&amp;iPage='.$aData['iPage'].'">'.$aData['sName'].'</a></td></tr>';
      } // end for

      return '<table><thead><tr><td>'.$GLOBALS['lang']['Id'].'</td><td>'.$GLOBALS['lang']['Name'].'</td></tr></thead>'.$content.'</tbody></table>';
    }
  } // end function listLastPages

  /**
  * Returns a product page select to the admin panel
  * @return string
  * @param array  $aPagesSelected
  */
  public function throwProductsPagesSelectAdmin( $aPagesSelected ){
    if( isset( $this->aPagesParentsTypes ) ){
      $content = null;
      foreach( $this->aPagesParentsTypes as $iType => $aPages ){
        $iCount = count( $aPages );
        $sType = $GLOBALS['aMenuTypes'][$iType];

        for( $i = 0; $i < $iCount; $i++ ){
          if( isset( $this->aPages[$aPages[$i]]['iProducts'] ) && $this->aPages[$aPages[$i]]['iProducts'] == 1 ){
            $sSelected = ( isset( $aPagesSelected ) && isset( $aPagesSelected[$this->aPages[$aPages[$i]]['iPage']] ) ) ? ' selected="selected"' : null;
            $iValue = $this->aPages[$aPages[$i]]['iPage'];
            $content .= '<option'.( $this->aPages[$aPages[$i]]['iStatus'] == 0 ? ' style="color:#bbb;"' : null ).' value="'.$iValue.'"'.$sSelected.'>'.$this->aPages[$aPages[$i]]['sName'].'</option>';
            if( isset( $this->aPagesChildrens[$aPages[$i]] ) ){
              $content .= $this->throwProductsSubPagesSelectAdmin( $aPagesSelected, $aPages[$i], 1 );
            }
          }
        } // end for
      }
      return $content;
    }  
  } // end function throwProductsPagesSelectAdmin

  /**
  * Returns a product subpage select to the admin panel
  * @return string
  * @param array  $aPagesSelected
  * @param int    $iPageParent
  * @param int    $iDepth
  */
  private function throwProductsSubPagesSelectAdmin( $aPagesSelected, $iPageParent, $iDepth = 1 ){
    $iCount = count( $this->aPagesChildrens[$iPageParent] );
    $sSeparator = ( $iDepth > 0 ) ? str_repeat( '&nbsp;&nbsp;', $iDepth ) : null;
    $content = null;

    for( $i = 0; $i < $iCount; $i++ ){
      $iPage = $this->aPagesChildrens[$iPageParent][$i];
      $sSelected = ( isset( $aPagesSelected ) && isset( $aPagesSelected[$this->aPages[$iPage]['iPage']] ) ) ? ' selected="selected"' : null;
      if( isset( $this->aPages[$iPage]['iProducts'] ) && $this->aPages[$iPage]['iProducts'] == 1 ){
        $sDisabled = null;
        $iValue = $this->aPages[$iPage]['iPage'];
      }
      else{
        $sDisabled = ' disabled="disabled" style="color:#999;"';
        $iValue = null;
      }
      $content .= '<option'.( $this->aPages[$iPage]['iStatus'] == 0 ? ' style="color:#bbb;"' : null ).' value="'.$iValue.'"'.$sSelected.$sDisabled.'>'.$sSeparator.$this->aPages[$iPage]['sName'].'</option>';
      if( isset( $this->aPagesChildrens[$iPage] ) ){
        $content .= $this->throwProductsSubPagesSelectAdmin( $aPagesSelected, $iPage, $iDepth + 1 );
      }
    } // end for
    return $content;
  } // end function throwProductsSubPagesSelectAdmin

};
?>
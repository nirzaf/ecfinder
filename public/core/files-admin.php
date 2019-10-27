<?php
final class FilesAdmin extends Files
{

  private $aDirs;
  private $aFilesAll = null;
  private static $oInstance = null;

  public static function getInstance( $mValue = null, $bProduct = null ){  
    if( !isset( self::$oInstance ) ){  
      self::$oInstance = new FilesAdmin( );  
    }  
    return self::$oInstance;  
  } // end function getInstance

  /**
  * Constructor
  * @return void
  */
  private function __construct( ){
    $this->generateThumbDirs( );
  } // end function __construct

  /**
  * Lists all files on selected page
  * @return string
  * @param int $iLink
  */
  public function listAllLinkFiles( $iLink ){
    global $lang, $config;
    if( isset( $this->aFilesImages ) && isset( $this->aLinkFilesImages[$iLink] ) ){
      $aSizes = $GLOBALS['config']['images_sizes'];
      $aTypes = $GLOBALS['aPhotoTypes'];
      $oFFS = FlatFilesSerialize::getInstance( );
      $content = null;
      $iCount = count( $this->aLinkFilesImages[$iLink] );
      for( $i = 0; $i < $iCount; $i++ ){
        $aData = $this->aFilesImages[$this->aLinkFilesImages[$iLink][$i]];
        $sFile = null;
        $sImage = null;

        if( !empty( $aData['iPhoto'] ) && $aData['iPhoto'] == 1 ){
          $sImage = '<td class="place"><select name="aFilesTypes['.$aData['iFile'].']" onclick="rememberLastOption( this )" onchange="extNotice( this )">'.throwSelectFromArray( $aTypes, $aData['iType'] ).'<option value="3" class="disabled">'.$lang['Gallery'].'</option><option value="0" class="disabled">'.$lang['Hidden'].'</option></select></td><td class="thumb1"><select name="aFilesSizes1['.$aData['iFile'].']">'.throwSelectFromArray( $aSizes, $aData['iSize1'] ).'</select></td>'.( $config['display_thumbnail_2'] === true ? '<td class="thumb2"><select name="aFilesSizes2['.$aData['iFile'].']">'.throwSelectFromArray( $aSizes, $aData['iSize2'] ).'</select></td>' : null );
        }
        else
          $sFile = 'colspan="'.( $config['display_thumbnail_2'] === true ? 4 : 3 ).'"';

        $content .= '<tr class="l'.( ( $i % 2 ) ? 0: 1 ).'"><td><input type="checkbox" name="aFilesDelete['.$aData['iFile'].']" value="1" /></td><td class="name'.($aData['iPhoto']==1?' image-preview':null).'"><a href="'.DIR_FILES.$aData['sFileName'].'" target="_blank">'.$aData['sFileName'].'</a></td><td class="position"><input type="text" name="aFilesPositions['.$aData['iFile'].']" value="'.$aData['iPosition'].'" size="2" maxlength="3" class="inputr" /></td><td '.$sFile.' class="description"><input type="text" name="aFilesDescription['.$aData['iFile'].']" value="'.( isset( $aData['sDescription'] ) ? $aData['sDescription'] : null ).'" size="20" class="input description"  /></td>'.$sImage.'</tr>';
        
      } // end for

      if( isset( $content ) ){
        return '<table id="files-list" cellspacing="1"'.( $config['display_thumbnail_2'] === true ? null : ' class="no-thumbs2"' ).'><thead><tr><th class="delete">'.$lang['Delete'].'</th><th class="name">'.$lang['File'].'</th><th class="position">'.$lang['Position'].'</th><th class="description">'.$lang['Description'].'</th><th class="place">'.$lang['Photo_place'].'</th><th class="thumb1">'.$lang['Thumbnail_1'].'</th>'.( $config['display_thumbnail_2'] === true ? '<th class="thumb2">'.$lang['Thumbnail_2'].'</th>' : null ).'</tr></thead><tbody>'.$content.'</tbody></table>';
      }
    }
  } // end function listAllLinkFiles

  /**
  * Deletes all files selected for deletion
  * @return void
  * @param array  $aFiles
  * @param int    $iLinkType
  */
  public function deleteSelectedFiles( $aFiles, $iLinkType ){
    if( isset( $aFiles ) && is_array( $aFiles ) ){
      $sFileName = $this->throwDbNames( $iLinkType );

      foreach( $aFiles as $iFile => $iValue ){
        if( isset( $this->aFilesImages[$iFile] ) ){
          if( $GLOBALS['config']['delete_unused_files'] === true )
            $aDeleted[$iFile] = $this->aFilesImages[$iFile];
          unset( $this->aFilesImages[$iFile] );
          $bDeleted = true;
        }
      }
      if( isset( $bDeleted ) ){
        $oFFS = FlatFilesSerialize::getInstance( );
        $oFFS->saveData( $this->throwDbNames( $iLinkType ), $this->createArray( $iLinkType ) );
        if( isset( $aDeleted ) ){
          foreach( $aDeleted as $iFile => $aData ){
            $this->deleteFilesFromDirs( $aData['sFileName'], $aData['iPhoto'] );
          } // end foreach
        }
      }
    }
  } // end function deleteSelectedFiles

  /**
  * Deletes all files attached to pages that are being deleted
  * @return void
  * @param array  $aData
  * @param int    $iLinkType
  * @param string $sIndex
  * @param bool   $bWithoutFiles
  */
  public function deleteFiles( $aData, $iLinkType, $sIndex, $bWithoutFiles = null ){
    $this->generateCache( true, $iLinkType == 2 ? true : null );
    if( isset( $this->aFilesImages ) ){

      foreach( $this->aFilesImages as $iFile => $aFile ){
        if( isset( $aData[$aFile[$sIndex]] ) ){
          if( !isset( $bWithoutFiles ) && $GLOBALS['config']['delete_unused_files'] === true )
            $aDeleted[$iFile] = $aFile;
          unset( $this->aFilesImages[$iFile] );
          $bDeleted = true;
        }
      } // end foreach

      if( isset( $bDeleted ) ){
        $oFFS = FlatFilesSerialize::getInstance( );
        $oFFS->saveData( $this->throwDbNames( $iLinkType ), $this->createArray( $iLinkType ) );
        if( isset( $aDeleted ) ){
          foreach( $aDeleted as $iFile => $aData ){
            $this->deleteFilesFromDirs( $aData['sFileName'], $aData['iPhoto'] );
          } // end foreach
        }
      }
    }
  } // end function deleteFiles

  /**
  * Returns list of files in a directory
  * @return string
  * @param string $sSort
  */
  public function listFilesInDir( $sSort = null ){
    global $lang, $config;
    $oFFS = FlatFilesSerialize::getInstance( );
    $content = null;

    foreach( new DirectoryIterator( DIR_FILES ) as $oFileDir ) {
      $sFileName = $oFileDir->getFilename( );
      if( $oFileDir->isFile( ) && $sFileName != '.htaccess' ){
        if( isset( $sSort ) && $sSort == 'time' )
          $aSort[] = Array( filemtime( DIR_FILES.$sFileName ), $sFileName );
        else
          $aSort[] = Array( $sFileName, filemtime( DIR_FILES.$sFileName ) );
      }
    } // end foreach

    if( isset( $aSort ) ){
      if( $sSort == 'time' ){
        rsort( $aSort );
        foreach( $aSort as $aValue ){
          $aFiles[] = Array( $aValue[1], $aValue[0] );
        }
      }
      else{
        sort( $aSort );
        $aFiles = $aSort;
      }

      $iTime = time( );
      $iCount = count( $aFiles );
      for( $i = 0; $i < $iCount; $i++ ){
        $aData['sFileName'] = $aFiles[$i][0];
        $aData['iTime'] = $aFiles[$i][1];
        $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData['iFile'] = $i;
        $aData['sStyle'] = null;
        $aData['iPhoto'] = ( $oFFS->checkCorrectFile( $aData['sFileName'], 'gif|jpg|png|jpeg' ) == true ) ? 1 : 0;

        if( $iTime - $aData['iTime'] < 1200 )
          $aData['sStyle'] = ' time';

        $content .= '<tr class="l'.$aData['iStyle'].$aData['sStyle'].'" id="fileTr'.$aData['iFile'].'"><td class="select"><input type="checkbox" name="aDirFiles['.$aData['iFile'].']" value="'.$aData['sFileName'].'" onclick="displayFilesDirHead( \''.$aData['iFile'].'\', '.$aData['iPhoto'].' )" '.( isset( $_SESSION['aUploadedFiles'][$aData['sFileName']] ) ? 'checked="checked"' : null ).' /></td><td class="file'.($aData['iPhoto']==1?' image-preview':null).'"><a href="'.DIR_FILES.$aData['sFileName'].'" target="_blank">'.$aData['sFileName'].'</a></td><td class="position">&nbsp;</td><td class="description">&nbsp;</td><td class="place">&nbsp;</td><td class="thumb1">&nbsp;</td>'.( $config['display_thumbnail_2'] === true ? '<td class="thumb2">&nbsp;</td>' : null ).'</tr>';
      } // end for

      if( isset( $_SESSION['aUploadedFiles'] ) )
        unset( $_SESSION['aUploadedFiles'] );

      return '<h3 class="files-dir">'.$lang['Files_on_server'].'</h3><table cellspacing="1" class="files-dir'.( $config['display_thumbnail_2'] === true ? null : ' dir-no-thumbs2' ).'" id="files-dir-head"><tbody><tr id="files-dir-head-tr"><th class="select">'.$lang['Select'].'</th><th class="file">'.$lang['File'].'</th><th class="position hidden">'.$lang['Position'].'</th><th class="description hidden">'.$lang['Description'].'</th><th class="place hidden">'.$lang['Photo_place'].'</th><th class="thumb1 hidden">'.$lang['Thumbnail_1'].'</th>'.( $config['display_thumbnail_2'] === true ? '<th class="thumb2 hidden">'.$lang['Thumbnail_2'].'</th>' : null ).'</tr><tr><th>&nbsp;</th><th class="file"><input type="text" name="sFilesInDirPhrase" id="filesInDirPhrase" value="'.$lang['search'].'" class="input" size="50" onkeyup="listTableSearch( this, \'files-dir-table\', 1 )" onfocus="if(this.value==\''.$lang['search'].'\')this.value=\'\'" /></th><th colspan="'.( $config['display_thumbnail_2'] === true ? 5 : 4 ).'">&nbsp;</th></tr></tbody></table><div id="files-dir"><table cellspacing="1" class="files-dir'.( $config['display_thumbnail_2'] === true ? null : ' dir-no-thumbs2' ).'" id="files-dir-table"><tbody>'.$content.'</tbody></table></div>';
    }
  } // end function listFilesInDir

  /**
  * Deletes files and images from the "files/" directory
  * @return void
  * @param string $sFileName
  * @param int    $iImage
  */
  private function deleteFilesFromDirs( $sFileName, $iImage ){
    if( !isset( $this->aFilesAll ) ){
      $oFFS = FlatFilesSerialize::getInstance( );
      foreach( new DirectoryIterator( DIR_LANG ) as $oFileDir ) {
        if( $oFileDir->isFile( ) && strstr( $oFileDir->getFileName( ), '.php' ) ){
          $aLangs[] = substr( $oFileDir->getFileName( ), 0, 2 );
        }
      } // end foreach

      foreach( $aLangs as $sLang ){
        $aDatabaseFiles = $this->throwDbNames( );
        foreach( $aDatabaseFiles as $iKey => $sFile ){
         if( !isset( $this->aFilesImages ) )
            $this->aFilesImages = null;
          $aFiles = $oFFS->getData( str_replace( LANGUAGE.'_', $sLang.'_', $sFile ) );
          if( is_array( $aFiles ) && count( $aFiles ) > 0 ){
            foreach( $aFiles as $iKey => $aData ){
              if( !isset( $this->aFilesAll[$aData['sFileName']] ) )
                $this->aFilesAll[$aData['sFileName']] = 0;
              $this->aFilesAll[$aData['sFileName']]++;      
            } // end foreach
          }
        } // end foreach
      } // end foreach
    }

    if( isset( $this->aFilesAll[$sFileName] ) && $this->aFilesAll[$sFileName] > 0 )
      return null;

    if( $iImage == 1 && isset( $this->aDirs ) ){
      foreach( $this->aDirs as $mDir => $bValue ){
        if( is_file( DIR_FILES.$mDir.'/'.$sFileName ) )
          unlink ( DIR_FILES.$mDir.'/'.$sFileName );
      }
    }
    if( is_file( DIR_FILES.$sFileName ) )
      unlink ( DIR_FILES.$sFileName );
  } // end function deleteFilesFromDirs

  /**
  * Returns thumb directory names
  * @return array
  */
  private function generateThumbDirs( ){
    foreach( new DirectoryIterator( DIR_FILES ) as $oFileDir ) {
      if( is_numeric( $oFileDir->getFilename( ) ) && $oFileDir->isDir( ) ){
        $this->aDirs[$oFileDir->getFilename( )] = true;
      }
    } // end foreach
  } // end function generateThumbDirs

  /**
  * Saves file descriptions and sizes
  * @return void
  * @param array $aForm
  * @param int $iLinkType
  * @param int $iLink
  */
  public function saveFiles( $aForm, $iLinkType = 1, $iLink = null ){
    if( isset( $aForm['aFilesDescription'] ) && is_array( $aForm['aFilesDescription'] ) ){
      if( isset( $aForm['aFilesDelete'] ) )
        $this->deleteSelectedFiles( $aForm['aFilesDelete'], $iLinkType );

      if( isset( $iLink ) && is_numeric( $iLink ) ){
        if( isset( $this->aLinkFilesImages[$iLink] ) ){
          $iCount = count( $this->aLinkFilesImages[$iLink] );
          for( $i = 0; $i < $iCount; $i++ ){
            if( isset( $this->aFilesImages[$this->aLinkFilesImages[$iLink][$i]] ) )
              $aFiles[$this->aLinkFilesImages[$iLink][$i]] = $this->aFilesImages[$this->aLinkFilesImages[$iLink][$i]];
          } // end for
        }
      }
      else{
        if( isset( $this->aFilesImages ) ){
          $aFiles = $this->aFilesImages;
        }
      }

      if( isset( $aFiles ) ){
        foreach( $aFiles as $iFile => $aData ){
          if( !isset( $aForm['aFilesDelete'][$iFile] ) && isset( $aForm['aFilesDescription'][$iFile] ) ){
            $aForm['aFilesDescription'][$aData['iFile']] = changeTxt( trim( $aForm['aFilesDescription'][$aData['iFile']] ), '' );
            $bSizes = null;

            if( !isset( $aData['sDescription'] ) )
              $aData['sDescription'] = '';

            if( isset( $aForm['aFilesDescription'][$aData['iFile']] ) && $aForm['aFilesDescription'][$aData['iFile']] != $aData['sDescription'] ){
              $this->aFilesImages[$aData['iFile']]['sDescription'] = $aForm['aFilesDescription'][$aData['iFile']];
              $bChanged = true;
            }

            if( isset( $aForm['aFilesSizes1'][$aData['iFile']] ) && $aForm['aFilesSizes1'][$aData['iFile']] != $aData['iSize1'] ){
              $this->aFilesImages[$aData['iFile']]['iSize1'] = $aForm['aFilesSizes1'][$aData['iFile']];
              $bChanged = true;
              $bSizes = true;
              if( $GLOBALS['config']['display_thumbnail_2'] !== true )
                $aForm['aFilesSizes2'][$aData['iFile']] = $aForm['aFilesSizes1'][$aData['iFile']];
            }

            if( isset( $aForm['aFilesSizes2'][$aData['iFile']] ) && $aForm['aFilesSizes2'][$aData['iFile']] != $aData['iSize2'] ){
              $this->aFilesImages[$aData['iFile']]['iSize2'] = $aForm['aFilesSizes2'][$aData['iFile']];
              $bChanged = true;
              $bSizes = true;
            }

            if( $aForm['aFilesPositions'][$aData['iFile']] != $aData['iPosition'] ){
              $this->aFilesImages[$aData['iFile']]['iPosition'] = $aForm['aFilesPositions'][$aData['iFile']];
              $bChanged = true;
            }
            
            if( isset( $aForm['aFilesTypes'][$aData['iFile']] ) && $aForm['aFilesTypes'][$aData['iFile']] != $aData['iType'] ){
              if( $iLinkType == 2 && $aForm['aFilesTypes'][$aData['iFile']] == 1 )
                $bSizes = true;
              $this->aFilesImages[$aData['iFile']]['iType'] = $aForm['aFilesTypes'][$aData['iFile']];
              $bChanged = true;
            }

            if( isset( $bSizes ) ){
              $this->generateThumbs( $this->aFilesImages[$aData['iFile']]['sFileName'], $this->aFilesImages[$aData['iFile']]['iSize1'], $this->aFilesImages[$aData['iFile']]['iSize2'], ( ( $iLinkType == 2 ) ? $this->aFilesImages[$aData['iFile']]['iType'] : null ) );
            }
          }
        } // end foreach
      }

      if( isset( $bChanged ) ){
        $oFFS = FlatFilesSerialize::getInstance( );
        $oFFS->saveData( $this->throwDbNames( $iLinkType ), $this->createArray( $iLinkType ) );
      }
    }
  } // end function saveFiles

  /**
  * Adds files from a server
  * @param array  $aForm
  * @param int    $iLink
  * @param int    $iLinkType
  * @param string $sLinkName
  */
  public function addFilesFromServer( $aForm, $iLink, $iLinkType, $sLinkName ){
    if( isset( $aForm['aDirFiles'] ) ){
      $i = 0;
      $oFFS = FlatFilesSerialize::getInstance( );

      $this->mData = null;

      foreach( $aForm['aDirFiles'] as $iKey => $sFile ){
        if( is_file( DIR_FILES.$sFile ) ){
          if( $GLOBALS['config']['change_files_names'] === true && isset( $_POST['sName'] ) ){
            $this->mData[$i]['sFileName'] = $oFFS->checkIsFile( change2Url( $_POST['sName'] ).'.'.$oFFS->throwExtOfFile( $sFile ), DIR_FILES );
            $this->mData[$i]['sFileNamePrimary'] = $sFile;
          }
          else{
            $this->mData[$i]['sFileName'] = $sFile;
          }
          if( !is_file( DIR_FILES.$this->mData[$i]['sFileName'] ) )
            copy( DIR_FILES.$sFile, DIR_FILES.$this->mData[$i]['sFileName'] );
          if( isset( $aForm['aDirFilesSizes1'][$iKey] ) ){
            $this->mData[$i]['iSize1'] = $aForm['aDirFilesSizes1'][$iKey];
            if( $GLOBALS['config']['display_thumbnail_2'] !== true )
              $aForm['aDirFilesSizes2'][$iKey] = $aForm['aDirFilesSizes1'][$iKey];
          }
          if( isset( $aForm['aDirFilesSizes2'][$iKey] ) )
            $this->mData[$i]['iSize2'] = $aForm['aDirFilesSizes2'][$iKey];
          $this->mData[$i]['iType'] = ( isset( $aForm['aDirFilesTypes'][$iKey] ) && is_numeric( $aForm['aDirFilesTypes'][$iKey] ) ) ? $aForm['aDirFilesTypes'][$iKey] : 1;
          $this->mData[$i]['iPosition'] = is_numeric( $aForm['aDirFilesPositions'][$iKey] ) ? $aForm['aDirFilesPositions'][$iKey] : 0;
          $this->mData[$i]['sDescription'] = changeTxt( $aForm['aDirFilesDescriptions'][$iKey], '' );
          $this->mData[$i][$sLinkName] = $iLink;
          $i++;
        }
      }

      if( isset( $this->mData ) )
        $this->addFiles( $iLinkType );
    }
  } // end function addFilesFromServer

  /**
  * Adds files
  * @return void
  * @param int $iLinkType
  */
  private function addFiles( $iLinkType ){
    if( isset( $this->mData ) && is_array( $this->mData ) ){
      $oFFS = FlatFilesSerialize::getInstance( );
      $sFile = $this->throwDbNames( $iLinkType );
      $iLastId = $oFFS->throwLastId( $sFile, 'iFile' );
      $iCount = count( $this->mData );
      $i = 0;

      foreach( $this->mData as $iKey => $aData ){
        $aData['iPhoto'] = ( $oFFS->checkCorrectFile( $aData['sFileName'], 'gif|jpg|png|jpeg' ) == true ) ? 1 : 0;

        if( $aData['iPhoto'] == 1 ){
          $this->generateThumbs( $aData['sFileName'], $aData['iSize1'], $aData['iSize2'], $aData['iType'] );
          if( !is_numeric( $aData['iSize1'] ) )
            $aData['iSize1'] = 0;
          if( $GLOBALS['config']['display_thumbnail_2'] !== true )
            $aData['iSize2'] = $aData['iSize1'];
          if( !is_numeric( $aData['iSize2'] ) )
            $aData['iSize2'] = 0;
        }
        else{
          $aData['iType'] = '';
          $aData['iSize1'] = '';
          $aData['iSize2'] = '';
        }
        
        $aData['iFile'] = ++$iLastId;

        if( isset( $aData['sFileNamePrimary'] ) ){
          $this->deleteFilesFromDirs( $aData['sFileNamePrimary'], $aData['iPhoto'] );
        }

        $this->aFilesImages[$aData['iFile']] = $aData;
        $i++;
      } // end foreach

      $oFFS->saveData( $sFile, $this->createArray( $iLinkType ) );

      $this->mData = null;
    }
  } // end function addFiles 

  /**
  * Generates thumbnails
  * @return void
  * @param string $sFileName
  * @param int $iSize1
  * @param int $iSize2
  * @param int $iType
  */
  private function generateThumbs( $sFileName, $iSize1, $iSize2, $iType = null ){
    $oImage = ImageJobs::getInstance( );

    $aImgSize = $oImage->throwImgSize( DIR_FILES.$sFileName );
    if( defined( 'MAX_DIMENSION_OF_IMAGE' ) && ( $aImgSize['width'] > MAX_DIMENSION_OF_IMAGE || $aImgSize ['height'] > MAX_DIMENSION_OF_IMAGE ) ){
      if( $aImgSize['width'] < $oImage->iMaxForThumbSize && $aImgSize['height'] < $oImage->iMaxForThumbSize ){
        $oImage->setThumbSize( MAX_DIMENSION_OF_IMAGE );
        $oImage->createThumb( DIR_FILES.$sFileName, DIR_FILES, $sFileName );
      }
    }
    
    if( isset( $GLOBALS['config']['images_sizes'][$iSize1] ) )
      $iSize1 = $GLOBALS['config']['images_sizes'][$iSize1];
    else
      $iSize1 = $GLOBALS['config']['images_sizes'][0];

    if( isset( $GLOBALS['config']['images_sizes'][$iSize2] ) )
      $iSize2 = $GLOBALS['config']['images_sizes'][$iSize2];
    else
      $iSize2 = $GLOBALS['config']['images_sizes'][0];

    if( isset( $_GET['iProduct'] ) && isset( $iType ) && $iType == 1 && isset( $GLOBALS['config']['image_preview_size'] ) && is_numeric( $GLOBALS['config']['image_preview_size'] ) ){
      $iSize3 = $GLOBALS['config']['image_preview_size'];
      $sThumbsDir3 = DIR_FILES.$iSize3.'/';
      if( !is_dir( $sThumbsDir3 ) ){
        mkdir( $sThumbsDir3 );
        chmod( $sThumbsDir3, FILES_CHMOD );
      }
    }

    $sThumbsDir1 = DIR_FILES.$iSize1.'/';
    $sThumbsDir2 = DIR_FILES.$iSize2.'/';

    if( !is_dir( $sThumbsDir1 ) ){
      mkdir( $sThumbsDir1 );
      chmod( $sThumbsDir1, FILES_CHMOD );
    }
    if( !is_dir( $sThumbsDir2 ) ){
      mkdir( $sThumbsDir2 );
      chmod( $sThumbsDir2, FILES_CHMOD );
    }

    if( !is_file( $sThumbsDir1.$sFileName ) )
      $oImage->createCustomThumb( DIR_FILES.$sFileName, $sThumbsDir1, $iSize1, $sFileName, true );
    if( !is_file( $sThumbsDir2.$sFileName ) )
      $oImage->createCustomThumb( DIR_FILES.$sFileName, $sThumbsDir2, $iSize2, $sFileName, true );
    if( isset( $sThumbsDir3 ) && !is_file( $sThumbsDir3.$sFileName ) )
      $oImage->createCustomThumb( DIR_FILES.$sFileName, $sThumbsDir3, $iSize3, $sFileName, true );
  } // end function generateThumbs

  /**
  * Function creates a file data array before it is saved to the database
  * @return array
  * @param $iLinkType
  */
  protected function createArray( $iLinkType = 1 ){
    if( isset( $this->aFilesImages ) ){
      // Sorting array before it will save to file 
      foreach( $this->aFilesImages as $iKey => $aValue ){
        $aSort[$iKey][0] = (int) $aValue['iPosition'];
        $aSort[$iKey][1] = $aValue['sFileName'];
        $aSort[$iKey][2] = $aValue['iFile'];
      } // end foreach
      if( isset( $aSort ) ){
        sort( $aSort );

        foreach( $aSort as $iKey => $aValue ){
          $aSave[] = compareArrays( ( $iLinkType == 1 ) ? $GLOBALS['aPagesFilesFields'] : $GLOBALS['aProductsFilesFields'], $this->aFilesImages[$aValue[2]] );
        } // end foreach

        return $aSave;
      }
    }
  } // end function createArray

  /**
  * Lists recently added files
  * @return string
  */
  public function listLastFiles( $iLinkType = 2 ){

    $aDbFiles = $this->throwDbNames( );
    if( !isset( $aDbFiles[$iLinkType] ) )
      return null;

    $oFFS = FlatFilesSerialize::getInstance( );
    $aFiles = $oFFS->getData( $aDbFiles[$iLinkType] );
    $iCount = count( $aFiles );
    if( isset( $aFiles ) && is_array( $aFiles ) && $iCount > 0 ){
      foreach( $aFiles as $iFile => $aData ){
        $aSort[] = Array( $aData['iFile'], $iFile );
      } // end foreach
    }

    if( isset( $aSort ) ){
      rsort( $aSort );
      $oProduct = ProductsAdmin::getInstance( );
      $iMax = 5;
      if( $iCount > $iMax )
        $iCount = $iMax;

      $content = null;

      for( $i = 0; $i < $iCount; $i++ ){
        $aData = $aFiles[$aSort[$i][1]];
        
        $content .= '<tr><td class="id">'.$aData['iFile'].'</td><td class="name"><a href="'.DIR_FILES.$aData['sFileName'].'" target="_blank">'.$aData['sFileName'].'</a></td><td class="data"><a href="?p=products-form&amp;iProduct='.$aData['iProduct'].'">'.$oProduct->aProducts[$aData['iProduct']]['sName'].'</a></td></tr>';
      } // end for
      
      return '<table cellspacing="0"><thead><tr><td>'.$GLOBALS['lang']['Id'].'</td><td>'.$GLOBALS['lang']['Name'].'</td><td>'.$GLOBALS['lang']['Added_to'].'</td></tr></thead>'.$content.'</tbody></table>';
    }
  } // end function listLastFiles

  /**
  * Uploads a file to a server
  * @return string
  * @param string $sFileName
  */
  public function uploadFile( $sFileName ){
    $oFFS = FlatFilesSerialize::getInstance( );
    if( $oFFS->checkCorrectFile( $sFileName, $GLOBALS['config']['allowed_extensions'] ) ){
      $sFileNameNew = $oFFS->checkIsFile( $oFFS->changeFileName( $sFileName ), DIR_FILES );  
      if( isset( $_FILES['sFileName']['tmp_name'] ) && move_uploaded_file( $_FILES['sFileName']['tmp_name'], DIR_FILES.$sFileNameNew ) ){
        $_SESSION['aUploadedFiles'][$sFileNameNew] = true;
        return '{"success":true}';
      }
      elseif( file_put_contents( DIR_FILES.$sFileNameNew, file_get_contents( "php://input" ) ) ){
        $oImage = ImageJobs::getInstance( );
        $_SESSION['aUploadedFiles'][$sFileNameNew] = true;
        $sSizeInfo = ( $oFFS->checkCorrectFile( $sFileNameNew, 'gif|jpg|png|jpeg' ) == true && $oImage->checkImgMaxDimension( DIR_FILES.$sFileNameNew ) !== true ) ? ', "size_info":true' : null;
        return '{"success":true'.$sSizeInfo.'}';
      }
      else{
        return '{"success":false}';
      }
    }
    else{
      return '{error:"Incorrect extension"}';
    }
  } // end function uploadFile

};
?>
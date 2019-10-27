<?php
class Files
{

  public $aImagesDefault;
  public $aFilesImages;
  protected $aLinkFilesImages;
  protected $aFiles;
  protected $aImages;
  protected $aFields;
  protected $aImagesTypes;
  protected $mData = null;
  private static $oInstance = null;

  public static function getInstance( $mValue = null, $bProduct = null ){  
    if( !isset( self::$oInstance ) ){
      self::$oInstance = new Files( $mValue, $bProduct );  
    }  
    return self::$oInstance;  
  } // end function getInstance

  /**
  * Constructor
  * @return void
  * @param mixed $mValue
  * @param bool $bProduct
  */
  private function __construct( $mValue, $bProduct = null ){
    $this->generateCache( $mValue, $bProduct );
  } // end function __construct

  /**
  * Return database name
  * @return mixed
  * @param int  $iDbType
  */
  protected function throwDbNames( $iDbType = null ){
    $aFiles[1] = DB_PAGES_FILES;
    $aFiles[2] = DB_PRODUCTS_FILES;

    if( isset( $iDbType ) )
      return isset( $aFiles[$iDbType] ) ? $aFiles[$iDbType] : null;
    else
      return $aFiles;
  } // end function throwDbNames

  /**
  * Displays images by types
  * @return string
  * @param int $iLink
  * @param int $iType
  * @param bool $bLinks
  */
  public function listImagesByTypes( $iLink, $iType = 1, $bLinks = true ){
    if( isset( $this->aImagesTypes[$iLink][$iType] ) ){
      $content = null;
      $iCount = count( $this->aImagesTypes[$iLink][$iType] );

      for( $i = 0; $i < $iCount; $i++ ){
        $aData = $this->aFilesImages[$this->aImagesTypes[$iLink][$iType][$i]];
        $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
        $aData['sAlt'] = isset( $aData['sDescription'] ) ? $aData['sDescription'] : null;

        $content .= '<li class="l'.$aData['sStyle'].'">'.( isset( $bLinks ) ? '<a href="'.DIR_FILES.$aData['sFileName'].'" class="quickbox['.$iLink.']" title="'.$aData['sAlt'].'">' : null ).'<img src="'.DIR_FILES.$aData['iSizeValue2'].'/'.$aData['sFileName'].'" alt="'.$aData['sAlt'].'" />'.( isset( $bLinks ) ? '</a>' : null );

        if( !empty( $aData['sDescription'] ) )
          $content .= '<div>'.$aData['sDescription'].'</div>';

        $content .= '</li>';

      } // end for

      if( isset( $content ) )
        return '<ul class="imagesList" id="imagesList'.$iType.'">'.$content.'</ul>';
    }
  } // end function listImagesByTypes

  /**
  * Displays preview images by types
  * @return string
  * @param int $iLink
  * @param int $iType
  * @param bool $bLinks
  */
  public function listPreviewImages( $iLink, $iType = 1, $bLinks = true ){

    if( isset( $this->aImagesTypes[$iLink][$iType] ) && isset( $GLOBALS['config']['image_preview_size'] ) && is_numeric( $GLOBALS['config']['image_preview_size'] ) ){
      $content = null;
      $i = 0;
      $iCount = count( $this->aImagesTypes[$iLink][$iType] );

      for( $i = 0; $i < $iCount; $i++ ){
        $iFile = $this->aImagesTypes[$iLink][$iType][$i];
        if( isset( $this->aFilesImages[$iFile] ) ){
          $aData = $this->aFilesImages[$iFile];
          $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
          $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;
          $aData['sAlt'] = isset( $aData['sDescription'] ) ? $aData['sDescription'] : null;

          if( $i == 0 ){
            $content .= '<script type="text/javascript">
                var sFilesDir = "'.DIR_FILES.'";
                var sPreviewDir = "'.$GLOBALS['config']['image_preview_size'].'/";
              </script>
              <div id="imagesList'.$iType.'" class="imagePreview"><a href="'.DIR_FILES.$aData['sFileName'].'" id="previewLink" title="'.$aData['sAlt'].'"><img src="'.DIR_FILES.$GLOBALS['config']['image_preview_size'].'/'.$aData['sFileName'].'" alt="'.$aData['sAlt'].'" id="imgPreview" /></a>';

            if( !empty( $aData['sDescription'] ) )
              $content .= '<div id="defaultDescription">'.$aData['sDescription'].'</div>';
            $content .= '</div>';

            if( $iCount > 1 )
              $content .= '<ul class="imagesList" id="imagesListPreview">';
          }

          if( $iCount > 1 )
            $content .= '<li class="l'.$aData['sStyle'].'"><a href="'.DIR_FILES.$aData['sFileName'].'" onmouseover="previewImage( this, \''.$aData['sFileName'].'\', '.($i).' )" class="quickbox[preview]" title="'.$aData['sAlt'].'"><img src="'.DIR_FILES.$aData['iSizeValue2'].'/'.$aData['sFileName'].'" alt="'.$aData['sAlt'].'" /></a></li>';
        }
      }
      if( isset( $content ) )
        return $content.( ( $i > 1 ) ? '</ul>' : null );
    }
  } // end function listImagesByTypes

  /**
  * Displays a default image
  * @return string
  * @param int $iLink
  * @param int $iLinkType
  * @param bool $bLinks
  * @param string $sLink
  */
  public function getDefaultImage( $iLink, $iLinkType = 1, $bLinks = null, $sLink = null ){
    if( isset( $this->aImagesDefault[$iLinkType][$iLink] ) ){
      if( isset( $bLinks ) ){
        $sLink = isset( $sLink ) ? '<a href="'.$sLink.'" tabindex="-1">' : '<a href="'.DIR_FILES.$this->aImagesDefault[$iLinkType][$iLink]['sFileName'].'" class="quickbox[images]">';
      }
      return '<div class="photo">'.$sLink.'<img src="'.DIR_FILES.$this->aImagesDefault[$iLinkType][$iLink]['iSizeValue1'].'/'.$this->aImagesDefault[$iLinkType][$iLink]['sFileName'].'" alt="'.( isset( $this->aImagesDefault[$iLinkType][$iLink]['sDescription'] ) ? $this->aImagesDefault[$iLinkType][$iLink]['sDescription'] : $this->aImagesDefault[$iLinkType][$iLink]['sFileName'] ).'" />'.( isset( $bLinks ) ? '</a>' : null ).'</div>';
    }
  } // end function getDefaultImage

  /**
  * Lists all files
  * @return string
  * @param int    $iLink
  */
  public function listFiles( $iLink ){
    $content = null;
    if( isset( $this->aFiles[$iLink] ) ){
      $oFFS = FlatFilesSerialize::getInstance( );
      $iCount = count( $this->aFiles[$iLink] );
      $aExt = throwIconsFromExt( );

      for( $i = 0; $i < $iCount; $i++ ){
        $aData = $this->aFilesImages[$this->aFiles[$iLink][$i]];
        $aData['iStyle'] = ( $i % 2 ) ? 0: 1;
        $aData['sStyle'] = ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1;

        $aName = $oFFS->throwNameExtOfFile( $aData['sFileName'] );
        if( !isset( $aExt[$aName[1]] ) )
          $aExt[$aName[1]] = 'nn';
        $aData['sIcon'] = 'ico_'.$aExt[$aName[1]];

        $content .= '<li class="l'.$aData['sStyle'].'"><img src="'.DIR_FILES.'ext/'.$aData['sIcon'].'.gif" alt="ico" /><a href="'.DIR_FILES.$aData['sFileName'].'">'.$aData['sFileName'].'</a>';
        if( !empty( $aData['sDescription'] ) )
          $content .= ', <em>'.$aData['sDescription'].'</em>';
        $content .= '</li>';
      } // end for

      if( isset( $content ) ){
        return '<ul id="filesList">'.$content.'</ul>';
      }
    }
  } // end function listFiles

  /**
  * Generates cache variables
  * @return void
  * @param mixed $mValue
  * @param bool $bProduct
  */
  public function generateCache( $mValue = null, $bProduct = null ){
    global $config;

    $oFFS = FlatFilesSerialize::getInstance( );
    $aFiles = $this->throwDbNames( );
    $aKeys = Array( 1 => 'iPage', 'iProduct' );
    $iSize1 = 0;
    $iSize2 = 0;
    $this->aImages = null;
    $this->aFiles = null;
    $this->aImagesTypes = null;
    $this->aLinkFilesImages = null;
    $this->aImagesDefault = null;

    foreach( $aFiles as $iKey => $sValue ){
      $sKey = $aKeys[$iKey];
      if( is_file( $sValue ) ){
        $aData = $oFFS->getData( $sValue );
        if( is_array( $aData ) && count( $aData ) > 0 ){
          foreach( $aData as $iKeyFile => $aValue ){
            $bDefault = null;
            $bDefine = null;

            if( !isset( $this->aImagesDefault[$iKey][$aValue[$sKey]] ) && !empty( $aValue['iPhoto'] ) && $aValue['iPhoto'] == 1 )
              $bDefault = true;
            if( isset( $mValue ) && ( ( ( ( isset( $bProduct ) && $iKey == 2 ) || ( !isset( $bProduct ) && $iKey == 1 ) ) && ( $mValue == $aValue[$sKey] || $mValue === true ) ) ) )
              $bDefine = true;

            if( isset( $bDefault ) || isset( $bDefine ) ){
              if( isset( $bDefault ) || ( isset( $bDefine ) && !empty( $aValue['iPhoto'] ) && $aValue['iPhoto'] == 1 ) ){
                if( !isset( $aValue['iSize1'] ) || !is_numeric( $aValue['iSize1'] ) )
                  $aValue['iSize1'] = $iSize1;
                if( !isset( $aValue['iSize2'] ) || !is_numeric( $aValue['iSize2'] ) )
                  $aValue['iSize2'] = $iSize1;
                $aValue['iSizeValue1'] = $config['images_sizes'][$aValue['iSize1']];
                $aValue['iSizeValue2'] = $config['images_sizes'][$aValue['iSize2']];
                if( isset( $bDefault ) )
                  $this->aImagesDefault[$iKey][$aValue[$sKey]] = $aValue;
                if( isset( $bDefine ) ){
                  $this->aImages[$aValue[$sKey]][] = $aValue['iFile'];
                  $this->aImagesTypes[$aValue[$sKey]][$aValue['iType']][] = $aValue['iFile'];
                }
              }
              else{
                $this->aFiles[$aValue[$sKey]][] = $aValue['iFile'];
              }

              if( isset( $bDefine ) ){
                $this->aFilesImages[$aValue['iFile']] = $aValue;
                $this->aLinkFilesImages[$aValue[$sKey]][] = $aValue['iFile'];              
              }
            }
          } // end foreach
        }
      }
    } // end foreach
  } // end function generateCache
};
?>
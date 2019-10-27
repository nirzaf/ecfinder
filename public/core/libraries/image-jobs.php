<?php
/**
* ImageJobs - changing images
* @access   public
* @version  0.5
* @require  FileJobs
* @require  Trash
*/
final class ImageJobs extends FileJobs
{

  private $iThumbX = 100;
  private $iThumbY = 100;
  private $iQuality = 80;
  private $iGifQuality = 100;
  private $iThumbAdd = '_m';
  private $iCustomThumbX = 250;
  private $iCustomThumbY = 250;
  private $sCustomThumbAdd = null;
  private $sExt = 'jpg';
  private $sExtDot = '.jpg';
  private $fRatio = 0.80;
  private static $oInstance = null;
  public $iMaxForThumbSize = 2000;
  private $aBackgroundGif = Array( 'red' => 255, 'green' => 255, 'blue' => 255 );

  public static function getInstance( ){
    if( !isset( self::$oInstance ) ){
      self::$oInstance = new ImageJobs( );
    }
    return self::$oInstance;
  } // end function getInstance

  /**
  * Constuctor
  * @return void
  * @param  int   $iThumbSize
  */
  private function __construct( $iThumbSize = 100 ){
    $this->iThumbX = $iThumbSize;
  } // end function __construct

  /**
  * Sets thumb size
  * @return void
  * @param  int   $iThumbSize
  */
  public function setThumbSize( $iThumbSize = 100 ){
    $this->iThumbX = $iThumbSize;
  } // end function setThumbSize

  /**
  * Sets thumb quality
  * @return void
  * @param  int   $iThumbQuality
  */
  public function setThumbQuality( $iThumbQuality = 80 ){
    $this->iQuality = $iThumbQuality;
  } // end function setThumbQuality

  /**
  * Sets name addition for thumb
  * @return void
  * @param  int   $iThumbAdd
  */
  public function setThumbAdd( $iThumbAdd = '_m' ){
    $this->iThumbAdd = $iThumbAdd;
  } // end function setThumbAdd

  /**
  * Sets name addition for custom thumb
  * @return void
  * @param  int   $iThumbAdd
  */
  public function setCustomThumbAdd( $sThumbAdd = null ){
    $this->sCustomThumbAdd = $sThumbAdd;
  } // end function setCustomThumbAdd

  /**
  * Sets max dimension of picture (when bigger thumb wont be create)
  * @return void
  * @param  int   $iMaxForThumbSize
  */
  public function setMaxForThumbSize( $iMaxForThumbSize = 2000 ){
    $this->iMaxForThumbSize = $iMaxForThumbSize;
  } // end function setMaxForThumbSize

  /**
  * Sets ratio of image
  * @return void
  * @param  int   $fRatio
  */
  public function setRatio( $fRatio = 0.80 ){
    $this->fRatio = $fRatio;
  } // end function setRatio

  /**
  * Clears propeties of object (set to default)
  * @return void
  */
  public function clearAll( ){
    $this->iThumbX = 100;
    $this->iThumbY = 100;
  } // end function clearAll

  /**
  * Returns image size in px
  * @return array/int
  * @param string $imgSrc
  * @param mixed  $sOption
  */
  public function throwImgSize( $imgSrc, $sOption = null ){
    $aImg = getImageSize( $imgSrc );

    $aImgSize['width'] = $aImg[0];
    $aImgSize['height'] = $aImg[1];

    if( $sOption == 'width' || $sOption == 'height' )
      return $aImgSize[$sOption];
    else
      return $aImgSize;
  } // end function throwImgSize

  /**
  * Returns image width in px
  * @return int
  * @param  string  $imgSrc
  */
  public function throwImgWidth( $imgSrc ){
    return $this->throwImgSize( $imgSrc, 'width' );
  } // end function throwImgWidth

  /**
  * Returns image height in px
  * @return int
  * @param  string  $imgSrc
  */
  public function throwImgHeight( $imgSrc ){
    return $this->throwImgSize( $imgSrc, 'height' );
  } // end function throwImgHeight

  /**
  * Checks if maximum dimension of image is not over the limit
  * @return boolean
  * @param string $sImgLocation
  */
  public function checkImgMaxDimension( $sImgLocation ){
    $aImgSize = $this->throwImgSize( $sImgLocation );
    if( $aImgSize['width'] < $this->iMaxForThumbSize && $aImgSize['height'] < $this->iMaxForThumbSize )
      return true;
    else
      return false;
  } // end function checkImgMaxDimension

  /**
  * Creates new custom size thumb
  * @return string
  * @param string $sImgSource
  * @param string $sImgDestDir
  * @param int $iSize
  * @param string $sImgOutput
  * @param bool $bOverwrite
  */
  public function createCustomThumb( $sImgSource, $sImgDestDir, $iSize = null, $sImgOutput = false, $bOverwrite = null ){

    if( !is_dir( $sImgDestDir ) || $this->checkCorrectFile( $sImgSource, 'jpg|jpeg|gif|png' ) == 0 )
      return null;

    if( $this->checkImgMaxDimension( $sImgSource ) ){

      $sImgExt = $this->throwExtOfFile( $sImgSource );

      if( $sImgOutput == false )
        $sImgOutput = $this->throwNameOfFile( $sImgSource ) . $this->sCustomThumbAdd . '.' . $sImgExt;

      if( !isset( $bOverwrite ) )
        $sImgOutput = $this->checkIsFile( $sImgOutput, $sImgDestDir );

      $iOldSize = $this->iThumbX;
      if( is_numeric( $iSize ) )
        $this->setThumbSize( $iSize );
      else
        $this->setThumbSize( $this->iCustomThumbX );

      $sFile = $this->createThumb( $sImgSource, $sImgDestDir, $sImgOutput );
      $this->setThumbSize( $iOldSize );
      return $sFile;
    }
    else
      return null;
  } // end function createCustomThumb


  /**
  * Function make image thumbs
  * @return int
  * @param string $sImgSource   - source file, from it thumb is created
  * @param string $sImgDestDir  - destination directory for thumb
  * @param string $sImgOutput   - picture name after change (default old name with _m addition)
  */
  public function createThumb( $sImgSource, $sImgDestDir, $sImgOutput = false, $iQuality = null ) {

    if( !is_dir( $sImgDestDir ) || $this->checkCorrectFile( $sImgSource, 'jpg|jpeg|gif|png' ) == 0 )
      return null;

    if( !is_numeric( $iQuality ) )
      $iQuality = $this->iQuality;

    $sImgExt = $this->throwExtOfFile( $sImgSource );

    if( $sImgOutput == false )
      $sImgOutput = basename( $sImgSource, '.'.$sImgExt ) . $this->iThumbAdd . '.' . $sImgExt;

    $sImgOutput = $this->changeFileName( $sImgOutput );

    $sImgBackup = $sImgDestDir.$sImgOutput . "_backup.jpg";
    copy( $sImgSource, $sImgBackup );
    $aImgProperties = GetImageSize( $sImgBackup );

    if ( !$aImgProperties[2] == 2 ) {
      return null;
    }
    else {
      switch( $sImgExt ) {
        case 'jpg':
          $mImgCreate = ImageCreateFromJPEG( $sImgBackup );
            break;
        case 'jpeg':
          $mImgCreate = ImageCreateFromJPEG( $sImgBackup );
            break;
        case 'png':
          $mImgCreate = ImageCreateFromPNG( $sImgBackup );
            break;
        case 'gif':
          $mImgCreate = ImageCreateFromGIF( $sImgBackup );
      }

      $iImgCreateX = ImageSX( $mImgCreate );
      $iImgCreateY = ImageSY( $mImgCreate );

      $iScaleX = $this->iThumbX / ( $iImgCreateX );
      $this->iThumbY = $iImgCreateY * $iScaleX;

      $iRatio = $this->iThumbX / $this->iThumbY;

      if( $iRatio < $this->fRatio ) {
        $this->iThumbY = $this->iThumbX;
        $iScaleY = $this->iThumbY / ( $iImgCreateY );
        $this->iThumbX = $iImgCreateX * $iScaleY;
      }

      $this->iThumbX = ( int )( $this->iThumbX );
      $this->iThumbY = ( int )( $this->iThumbY );
	  
      
	  $mImgDest = imagecreatetruecolor( $this->iThumbX, $this->iThumbY );
	
    if( $sImgExt == 'png' ){
      imagealphablending( $mImgDest, false );
      $iTransparent = imagecolorallocatealpha( $mImgDest, 0, 0, 0, 127 );
      imagecolortransparent( $mImgDest, $iTransparent );
      imagefill( $mImgDest, 0, 0, $iTransparent );
      imagesavealpha( $mImgDest, true );
    }
    elseif ($sImgExt == 'gif'){
      imagealphablending( $mImgDest, true );
      imagesavealpha( $mImgDest, false );
      $iBackgroundGif = imagecolorallocate( $mImgDest, $this->aBackgroundGif['red'], $this->aBackgroundGif['green'],$this->aBackgroundGif['blue'] );
      imagefill( $mImgDest, 0, 0, $iBackgroundGif );
    }

      unlink( $sImgBackup );

      if( function_exists( 'imagecopyresampled' ) )
        $sCreateFunction = 'imagecopyresampled';
      else
        $sCreateFunction = 'imagecopyresized';

      if( !$sCreateFunction( $mImgDest, $mImgCreate, 0, 0, 0, 0, $this->iThumbX, $this->iThumbY, $iImgCreateX, $iImgCreateY ) ) {
        imagedestroy( $mImgCreate );
        imagedestroy( $mImgDest );
        return null;
      }
      else {
        imagedestroy( $mImgCreate );
        if( !is_file( $sImgDestDir.$sImgOutput ) ) {
          touch( $sImgDestDir.$sImgOutput );
          chmod( $sImgDestDir.$sImgOutput, FILES_CHMOD );
        }
        switch( $sImgExt ) {
          case 'jpg':
            $Image = ImageJPEG( $mImgDest, $sImgDestDir.$sImgOutput, $iQuality );
            break;
          case 'jpeg':
            $Image = ImageJPEG( $mImgDest, $sImgDestDir.$sImgOutput, $iQuality );
            break;
          case 'png':
            $Image = ImagePNG( $mImgDest, $sImgDestDir.$sImgOutput );
            break;
          case 'gif':
            if( function_exists( "imagegif" ) )
              $Image = ImageGIF( $mImgDest, $sImgDestDir.$sImgOutput );
            else{
              if( $iQuality > 0 )
                $iQuality = floor( ( $iQuality - 1 ) / 10 );
              $Image = ImagePNG( $mImgDest, $sImgDestDir.$sImgOutput, $iQuality );
            }
        }
        if ( $Image  ) {
          imagedestroy( $mImgDest );
          return $sImgOutput;
        }
        imagedestroy( $mImgDest );
      }
    return null;
    }

  } // end function createThumb

};


?>
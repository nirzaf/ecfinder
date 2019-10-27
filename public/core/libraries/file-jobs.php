<?php
if( !defined( 'FILES_CHMOD' ) )
  define( 'FILES_CHMOD', 0777 );

/**
* FileJobs
* @access   public 
* @version  0.7
*/
class FileJobs
{

  protected $sFileName;
	protected $sChmod = FILES_CHMOD;
	
  /**
  * Add name to variable
  * @return void
  * @param string $sFileName
  */
  public function setFileName( $sFileName ){
    $this->sFileName = $sFileName;
  } // end function setFileName
	
  /**
  * Creates file
  * @return bool
  * @param string $sFileName
  */
	public function addFile( $sFileName = null ){
		
		if( isset( $sFileName ) )
			$this->setFileName( $sFileName );	

		if( is_file( $this->sFileName ) )
			return false;
		else{
			touch( $this->sFileName );
			chmod( $this->sFileName, $this->sChmod );
			if( is_file( $this->sFileName ) )
				return true;
			else
				return false;
		}
	} // end function addFile

  /**
  * Return file name without extension
  * @return string
  * @param string $sName
  */
	public function throwNameOfFile( $sName ){
		$aExp = explode( '.', $sName );
    if( isset( $aExp[0] ) && isset( $aExp[1] ) ){
      unset( $aExp[count( $aExp )-1] );
      $sName = implode( '.', $aExp );
      return $sName;
    }
    else
      return $sName;
	} // end function throwNameOfFIle

  /**
  * Return extension from file name
  * @return string
  * @param string $sName
  */
	public function throwExtOfFile( $sName ){
		$aExp = explode( '.', $sName );
    if( isset( $aExp[0] ) && isset( $aExp[1] ) ){
      return strtolower( $aExp[count( $aExp )-1] ); 
    }
    else
      return null;
	} // end function throwExtOfFile

  /**
  * Return extension and file name in array
  * @return array
  * @param string $sName
  */
  public function throwNameExtOfFile( $sName ){
    return Array( $this->throwNameOfFile( $sName ), $this->throwExtOfFile( $sName ) );
  } // end function throwNameExtOfFile

  /**
  * Return file content
  * @return string
  * @param string $sFile
  */
  public function throwFile( $sFile ){
    return is_file( $sFile ) ? file_get_contents( $sFile ) : null;
  } // end function throwFile

  /**
  * Check file extensions
  * For example if file have jpg or jpeg or gif or png extension then public function return true
  * @return int
  * @param string $sName
  * @param string $is
  */
	public function checkCorrectFile( $sName, $is = 'jpg|jpeg|png|gif' ){
		return preg_match( '/('.$is.')/i', $this->throwExtOfFile( $sName ) );
	} // end function checkCorrectFile

  /**
  * Change file name from strange name to latin
  * @return string
  * @param string $sFileName
  */
  public function changeFileName( $sFileName ){
    return change2Latin( str_replace( Array( '$', '\'', '"', '~', '/', '\\', '?', '#', '%', '+', '*', ':', '|', '<', '>', ' ', '__' ), '_', $sFileName ) );
  } // end function changeFileName

  /**
  * If file with set name exists then create uniq name for file
  * @return string
  * @param string $sFileOutName
  * @param string $sOutDir
  */
  public function checkIsFile( $sFileOutName, $sOutDir = '' ){
    
    $sFileName = $this->throwNameOfFile( $sFileOutName );
    $sExt = $this->throwExtOfFile( $sFileOutName );

    for( $i = 1; is_file( $sOutDir.$sFileOutName ); $i++ )
      $sFileOutName = $sFileName.'-'.$i.'.'.$sExt;

    return $sFileOutName;
  } // end function checkIsFile

  /**
  * Upload file on server
  * @return string
  * @param array  $aFiles
  * @param string $sOutDir
  * @param mixed  $sFileOutName
  */
  public function uploadFile( $aFiles, $sOutDir = null, $sFileOutName = null ){
    $sUpFileSrc = $aFiles['tmp_name'];
    $sUpFileName = $this->changeFileName( $aFiles['name'] );

    if( !isset( $sFileOutName ) )
      $sFileOutName = $sUpFileName;

    $sFileOutName = $this->checkIsFile( $sFileOutName, $sOutDir );

    if( move_uploaded_file( $sUpFileSrc, $sOutDir.$sFileOutName ) ){
      chmod( $sOutDir.$sFileOutName, $this->sChmod );
      return $sFileOutName;
    }
    else
      return null; 
  } // end function uploadFile

  /**
  * Delete all files and directories from directory
  * @return void
  * @param string $sDir
  */
  public function truncateDir( $sDir ){
    foreach( new DirectoryIterator( $sDir ) as $oFileDir ) {
      if( $oFileDir->isFile( ) ){
        unlink( $oFileDir->getPathname( ) );
      }
      else{
        if( $oFileDir->isDir( ) && ( !strstr( $oFileDir->getFilename( ), '.' ) && !strlen( $oFileDir->getFilename( ) ) < 3 ) ){
          $this->truncateDir( $oFileDir->getPathname( ).'/' );
          rmdir( $oFileDir->getPathname( ) );
        }
      }
    } // end foreach
  } // end function truncateDir

};
?>
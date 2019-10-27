<?php
/**
* FlatFilesSerialize
* @access   public 
* @version  1.2
* @author   OpenSolution
*/
class FlatFilesSerialize extends FileJobs
{

  /*
  * If you want cache file to protect from clearing then set it true
  * If is set true then script will work little slower
  */
  private $bCache = null;

  private $aData = null;
  private static $oInstance = null;

  public static function getInstance( ){  
    if( !isset( self::$oInstance ) ){  
      self::$oInstance = new FlatFilesSerialize( );  
    }  
    return self::$oInstance;  
  } // end function getInstance

  /**
  * Get data from file and return
  * @return mixed
  * @param string $sFileName
  * @param bool   $bCache
  */
  function getData( $sFileName, $bCache = null ){
    if( is_file( $sFileName ) ){
      if( isset( $bCache ) && isset( $this->aData[$sFileName] ) )
        return $this->aData[$sFileName];
      else{
        $aData = unserialize( file_get_contents( $sFileName, null, null, 15 ) );
        if( isset( $bCache ) )
          $this->aData[$sFileName] = $aData;
        return $aData;
      }
    }
  } // end function getFile

  /**
  * Save to file data
  * @return mixed
  * @param string $sFileName
  * @param array  $aData
  */
  function saveData( $sFileName, $aData ){
    if( is_file( $sFileName ) && isset( $this->bCache ) ){
      $sCacheData = file_get_contents( $sFileName );
    }
    file_put_contents( $sFileName, '<?php exit; ?>'."\n".serialize( $aData ) );
    if( isset( $this->aData[$sFileName] ) ){
      unset( $this->aData[$sFileName] );
    }

    if( isset( $sCacheData ) && filesize( $sFileName ) == 0 ){
      file_put_contents( $sFileName, $sCacheData );
    }
  } // end function saveData

  /**
  * Return last id of file
  * @return int
  * @param mixed  $mData
  * @param string $sIndex
  */
  function throwLastId( $mData, $sIndex ){
    if( !is_array( $mData ) )
      $mData = $this->getData( $mData );

    $iLastId = 0;

    if( isset( $mData ) && is_array( $mData ) ){
      foreach( $mData as $aValue ){
        if( $aValue[$sIndex] > $iLastId )
          $iLastId = $aValue[$sIndex];
      }
    }

    return $iLastId;
  } // end function throwLastId

  /**
  * Return data key by index and value
  * @return int
  * @param mixed $mData
  * @param string $sIndex
  * @param mixed $mValue
  * @param bool $bCache
  */
  function throwDataKey( $mData, $sIndex, $mValue, $bCache = null ){
    $aData = isset( $bCache ) ? $this->getData( $mData, true ) : $mData;
    if( isset( $aData ) && is_array( $aData ) ){
      foreach( $aData as $mKey => $aValue ){
        if( $aValue[$sIndex] == $mValue )
          return $mKey;
      }
    }
  } // end function throwDataKey

  /**
  * Delete data from array
  * @return void
  * @param string $sFileName
  * @param mixed  $mDataDelete
  * @param string $sKey
  * @param array  $aData
  */
  function deleteData( $sFileName, $mDataDelete, $sKey, $aData ){
    if( !is_array( $mDataDelete ) )
      $mDataDelete = Array( $mDataDelete );

    foreach( $aData as $iKey => $aValue ){
      if( in_array( $aValue[$sKey], $mDataDelete ) ){
        unset( $aData[$iKey] );
        $bDeleted = true;
      }
    } // end foreach

    if( isset( $bDeleted ) ){
      $this->saveData( $sFileName, $aData );
    }

  } // end function deleteData

  /**
  * Return file data in HTML select
  * @return string
  * @param mixed  $mData
  * @param mixed  $mKey
  * @param string $sKeyVerify
  * @param string $sIndexValue
  * @param mixed  $mIndexName
  */
  function throwDataSelect( $mData, $sKey, $sKeyVerify, $sIndexValue, $mIndexName ){
    if( !is_array( $mData ) )
      $mData = $this->getData( $mData );

    if( isset( $mData ) && is_array( $mData ) && count( $mData ) > 0 ){
      $sOption = null;

      foreach( $mData as $iKey => $aValue ){
        if( is_array( $mIndexName ) ){
          $sName = null;
          foreach( $mIndexName as $sIndex ){
            if( isset( $aValue[$sIndex] ) ){
              $sName .= $aValue[$sIndex].' ';
            }
          } // end foreach
          $sName = rtrim( $sName );
        }
        else{
          $sName = $aValue[$mIndexName];
        }

        $sSelected = ( isset( $sIndexValue ) && $aValue[$sKey] == $sKeyVerify ) ? ' selected="selected"' : null;
        $sOption .= '<option value="'.$aValue[$sIndexValue].'"'.$sSelected.'>'.$sName.'</option>';
      } // end foreach
      
      return $sOption;
    }
  } // end function throwDataSelect

};
?>
<?php
final class OrdersAdmin extends Orders
{

  /**
  * Generates cache variables
  * @return void
  */
  public function generateCache( ){

    if( !is_file( DB_ORDERS ) )
      return null;

    $this->aOrdersFields = $GLOBALS['aOrdersFields'];
    $this->aOrdersExtFields = $GLOBALS['aOrdersExtFields'];
    $this->aOrdersProductsFields = $GLOBALS['aOrdersProductsFields'];

    $rFile = fopen( DB_ORDERS, 'r' );
    $i = 0;
    while( !feof( $rFile ) ){
      $sContent = fgets( $rFile );
      if( $i > 0 && !empty( $sContent ) ){
        $aData = unserialize( trim( $sContent ) );
        $this->aOrders[$aData['iOrder']] = $aData;
        $this->aOrders[$aData['iOrder']]['sDate'] = displayDate( $aData['iTime'], $GLOBALS['config']['date_format_admin_orders'] );
        $this->aOrders[$aData['iOrder']]['sStatus'] = $this->throwStatus( $aData['iStatus'] );        
      }
      $i++;
    } // end while
    fclose( $rFile );
  } // end function generateCache

  /**
  * Returns orders list
  * @return string
  * @param int $iList
  */
  public function listOrdersAdmin( $iList = null ){
    global $lang;

    if( !isset( $this->aOrders ) )
      $this->generateCache( );

    $content = null;

    if( ( isset( $GLOBALS['sPhrase'] ) && !empty( $GLOBALS['sPhrase'] ) ) || ( isset( $_GET['iStatus'] ) && is_numeric( $_GET['iStatus'] ) ) ){
      $aOrders = $this->generateOrdersSearchListArray( $GLOBALS['sPhrase'], ( isset( $GLOBALS['iStatus'] ) ? $GLOBALS['iStatus'] : null ) );
    }
    else{
      if( isset( $this->aOrders ) ){
        foreach( $this->aOrders as $iOrder => $aData ){
          $aOrders[] = $iOrder;
        } // end foreach
      }
    }

    if( isset( $aOrders ) ){
      rsort( $aOrders );
      $iCount = count( $aOrders );
      if( !isset( $iList ) ){
        $iList = $GLOBALS['config']['admin_list'];
      }

      $aKeys = countPageNumber( $iCount, ( isset( $_GET['iPage'] ) ? $_GET['iPage'] : null ), $iList );
      $this->mData = null;

      for( $i = $aKeys['iStart']; $i < $aKeys['iEnd']; $i++ ){
        $aData = $this->aOrders[$aOrders[$i]];

        $content .= '<tr class="l'.( ( $i % 2 ) ? 0: 1 ).'"><td class="id">'.$aData['iOrder'].'</td><td class="name"><a href="?p=orders-form&amp;iOrder='.$aData['iOrder'].'">'.$aData['sFirstName'].' '.$aData['sLastName'].'</a></td><td class="email"><a href="mailto:'.$aData['sEmail'].'">'.$aData['sEmail'].'</a></td><td class="phone">'.( isset( $aData['sPhone'] ) ? $aData['sPhone'] : '&nbsp;' ).'</td><td class="company">'.( isset( $aData['sCompanyName'] ) ? $aData['sCompanyName'] : '&nbsp;' ).'</td><td class="date">'.$aData['sDate'].'</td><td class="status"><input type="checkbox" name="aStatus['.$aData['iOrder'].']" value="1" class="checkbox" /><a href="?p=orders-form&amp;iOrder='.$aData['iOrder'].'">'.( ( $aData['iStatus'] == 1 ) ? '<b>'.$aData['sStatus'].'</b>' : $aData['sStatus'] ).'</a></td><td class="options"><a href="?p=orders-form&amp;iOrder='.$aData['iOrder'].'"><img src="'.DIR_TEMPLATES.'admin/img/ico_edit.gif" alt="'.$lang['edit'].'" title="'.$lang['edit'].'" /></a> <a href="?p=orders-delete&amp;iOrder='.$aData['iOrder'].'" onclick="return del( );"><img src="'.DIR_TEMPLATES.'admin/img/ico_del.gif" alt="'.$lang['delete'].'" title="'.$lang['delete'].'"/></a></td></tr>';
      } // end for

      if( isset( $content ) ){
        $GLOBALS['sPages'] = countPagesClassic( $iCount, $iList, $aKeys['iPageNumber'], changeUri( $_SERVER['REQUEST_URI'] ) );
        return $content;
      }
    }
  } // end function listOrdersAdmin
  
  /**
  * Saves order status
  * @return void
  * @param array  $aForm
  */
  public function saveOrders( $aForm ){
    if( isset( $aForm['aStatus'] ) && is_array( $aForm['aStatus'] ) && $aForm['iStatus'] > 0 ){
      if( !isset( $this->aOrders ) )
        $this->generateCache( );

      foreach( $aForm['aStatus'] as $iOrder => $sValue ){
        if( $this->aOrders[$iOrder]['iStatus'] != $aForm['iStatus'] ){
          $aChange[$iOrder] = $aForm['iStatus'];
        }
      }
    }

    if( isset( $aChange ) ){
      $iTime = time( );

      $rFile = fopen( DB_ORDERS.'-backup', 'w' );
      fwrite( $rFile, '<?php exit; ?>'."\n" );
      foreach( $this->aOrders as $iOrder => $aValue ){
        if( isset( $aChange[$iOrder] ) )
          $aValue['iStatus'] = $aChange[$iOrder];
        fwrite( $rFile, serialize( compareArrays( $this->aOrdersFields, $aValue ) )."\n" );
      } // end foreach
      fclose( $rFile );

      $rFile1 = fopen( DB_ORDERS_EXT, 'r' );
      $rFile2 = fopen( DB_ORDERS_EXT.'-backup', 'w' );
      fwrite( $rFile2, '<?php exit; ?>'."\n" );
      $i = 0;
      while( !feof( $rFile1 ) ){
        $sContent = trim( fgets( $rFile1 ) );
        if( $i > 0 && !empty( $sContent ) ){
          $aData = unserialize( $sContent );
          if( isset( $aChange[$aData['iOrder']] ) ){
            $aData['aStatuses'][] = Array( 0 => $iTime, 1 => $aChange[$aData['iOrder']] );
            fwrite( $rFile2, serialize( $aData )."\n" );
          }
          else
            fwrite( $rFile2, $sContent."\n" );
        }
        $i++;
      } // end while
      fclose( $rFile1 );
      fclose( $rFile2 );

      $this->moveDatabaseFiles( );
      $this->generateCache( );
    }
  } // end function saveOrders

  /**
  * Returns orders array
  * @return array
  * @param string $sPhrase
  * @param int    $iStatus
  */
  private function generateOrdersSearchListArray( $sPhrase, $iStatus ){
    if( isset( $this->aOrders ) ){
      if( isset( $iStatus ) && !is_numeric( $iStatus ) )
        $iStatus = null;

      if( !empty( $sPhrase ) ){
        $aWords = getWordsFromPhrase( $sPhrase );
        $iCount = count( $aWords );
      }

      foreach( $this->aOrders as $iOrder => $aData ){
        $bFound = true;

        if( isset( $iStatus ) && $iStatus != $aData['iStatus'] ){
          $bFound = null;
        }

        if( isset( $bFound ) && isset( $aWords ) ){
          if( !findWords( $aWords, $iCount, implode( ' ', $aData ) ) ){
            $aNotFound[$iOrder] = true;
            $bFound = null;
          }
        }

        if( isset( $bFound ) ){
          $aFound[$iOrder] = true;
        }
      }

      if( isset( $aNotFound ) && isset( $aWords ) && isset( $_GET['iProducts'] ) && $_GET['iProducts'] == 1 ){
        $rFile = fopen( DB_ORDERS_PRODUCTS, 'r' );
        $i2 = 0;
        while( !feof( $rFile ) ){
          $sContent = trim( fgets( $rFile ) );
          if( $i2 > 0 && !empty( $sContent ) ){
            $aData = unserialize( $sContent );
            if( isset( $aNotFound[$aData['iOrder']] ) ){
              if( findWords( $aWords, $iCount, $aData['sName'].' '.$aData['fPrice'] ) )
                $aFound[$aData['iOrder']] = true;
            }
          }
          $i2++;
        } // end while
        fclose( $rFile );
      }

      if( isset( $aFound ) ){
        foreach( $this->aOrders as $iOrder => $aData ){
          if( isset( $aFound[$iOrder] ) )
            $aReturn[] = $iOrder;
        } // end foreach   
        return $aReturn;
      }
    }  
  } // end function generateOrdersSearchListArray

  /**
  * Deletes an order
  * @return void
  * @param int  $iOrder
  */
  public function deleteOrder( $iOrder ){
    if( isset( $this->aOrders[$iOrder] ) ){
      unset( $this->aOrders[$iOrder] );

      $rFile = fopen( DB_ORDERS.'-backup', 'w' );
      fwrite( $rFile, '<?php exit; ?>'."\n" );
      foreach( $this->aOrders as $iKey => $aValue ){
        fwrite( $rFile, serialize( compareArrays( $this->aOrdersFields, $aValue ) )."\n" );
      } // end foreach
      fclose( $rFile );

      $rFile1 = fopen( DB_ORDERS_EXT, 'r' );
      $rFile2 = fopen( DB_ORDERS_EXT.'-backup', 'w' );
      fwrite( $rFile2, '<?php exit; ?>'."\n" );
      $i = 0;
      while( !feof( $rFile1 ) ){
        $sContent = fgets( $rFile1 );
        if( $i > 0 && !empty( $sContent ) ){
          $aData = unserialize( trim( $sContent ) );
          if( $aData['iOrder'] != $iOrder ){
            fwrite( $rFile2, trim( $sContent )."\n" );
          }
        }
        $i++;
      } // end while
      fclose( $rFile1 );
      fclose( $rFile2 );

      $rFile1 = fopen( DB_ORDERS_PRODUCTS, 'r' );
      $rFile2 = fopen( DB_ORDERS_PRODUCTS.'-backup', 'w' );
      fwrite( $rFile2, '<?php exit; ?>'."\n" );
      $i = 0;
      while( !feof( $rFile1 ) ){
        $sContent = fgets( $rFile1 );
        if( $i > 0 && !empty( $sContent ) ){
          $aData = unserialize( trim( $sContent ) );
          if( $aData['iOrder'] != $iOrder ){
            fwrite( $rFile2, trim( $sContent )."\n" );
          }
        }
        $i++;
      } // end while
      fclose( $rFile1 );
      fclose( $rFile2 );

      $this->moveDatabaseFiles( );

    }
  } // end function deleteOrder

  /**
  * Saves order data
  * @return void
  * @param array  $aForm
  */
  public function saveOrder( $aForm ){
    $aOrderBefore = $this->aOrders[$aForm['iOrder']];
    $this->aOrders[$aForm['iOrder']] = $aForm = array_merge( $this->aOrders[$aForm['iOrder']], changeMassTxt( $aForm, 'H', Array( 'sComment', 'LenHNds' ) ) );

    $rFile = fopen( DB_ORDERS.'-backup', 'w' );
    fwrite( $rFile, '<?php exit; ?>'."\n" );
    foreach( $this->aOrders as $iKey => $aValue ){
      fwrite( $rFile, serialize( compareArrays( $this->aOrdersFields, $aValue ) )."\n" );
    } // end foreach
    fclose( $rFile );

    $rFile1 = fopen( DB_ORDERS_EXT, 'r' );
    $rFile2 = fopen( DB_ORDERS_EXT.'-backup', 'w' );
    fwrite( $rFile2, '<?php exit; ?>'."\n" );
    $i = 0;
    while( !feof( $rFile1 ) ){
      $sContent = trim( fgets( $rFile1 ) );
      if( $i > 0 && !empty( $sContent ) ){
        $aData = unserialize( $sContent );
        if( $aData['iOrder'] == $aForm['iOrder'] ){
          if( $aForm['iStatus'] != $aOrderBefore['iStatus'] ){
            $aData['aStatuses'][] = Array( 0 => time( ), 1 => $aForm['iStatus'] );
          }
          
          $aStatuses = isset( $aData['aStatuses'] ) ? Array( 'aStatuses' => $aData['aStatuses'] ) : null;
          if( isset( $aStatuses ) )
            fwrite( $rFile2, serialize( array_merge( compareArrays( $this->aOrdersExtFields, $aForm ), $aStatuses ) )."\n" );  
          else
            fwrite( $rFile2, serialize( compareArrays( $this->aOrdersExtFields, $aForm ) )."\n" );  
          
        }
        else{
          fwrite( $rFile2, $sContent."\n" );
        }
      }
      $i++;
    } // end while
    fclose( $rFile1 );
    fclose( $rFile2 );
    unset( $this->aOrders );

    if( !empty( $aForm['aNewProduct']['iProduct'] ) && !empty( $aForm['aNewProduct']['sName'] ) && !empty( $aForm['aNewProduct']['fPrice'] ) && !empty( $aForm['aNewProduct']['iQuantity'] ) ){
      $bChangeProducts = true;
      $bAddProduct = true;
    }

    $this->generateProducts( $aForm['iOrder'] );
    if( isset( $this->aProducts ) || isset( $bAddProduct ) ){
      if( isset( $aForm['aProductsDelete'] ) ){
        $bChangeProducts = true;
      }
      else{
        if( isset( $this->aProducts ) ){
          foreach( $this->aProducts as $iElement => $aData ){
            if( isset( $aForm['aProducts'][$iElement] ) && ( $aForm['aProducts'][$iElement]['sName'] != $aData['sName'] || $aForm['aProducts'][$iElement]['fPrice'] != $aData['fPrice'] || $aForm['aProducts'][$iElement]['iQuantity'] != $aData['iQuantity'] ) ){
              $bChangeProducts = true;
              break;
            }
          } // end foreach
        }
      }

      if( isset( $bChangeProducts ) ){
        $rFile1 = fopen( DB_ORDERS_PRODUCTS, 'r' );
        $rFile2 = fopen( DB_ORDERS_PRODUCTS.'-backup', 'w' );
        fwrite( $rFile2, '<?php exit; ?>'."\n" );
        $i = 0;
        $iLastElement = 0;
        while( !feof( $rFile1 ) ){
          $sContent = trim( fgets( $rFile1 ) );
          if( $i > 0 && !empty( $sContent ) ){
            $aData = unserialize( $sContent );
            if( $aData['iOrder'] == $aForm['iOrder'] ){
              if( !isset( $aForm['aProductsDelete'][$aData['iElement']] ) ){
                fwrite( $rFile2, serialize( compareArrays( $this->aOrdersProductsFields, array_merge( $aData, changeMassTxt( $aForm['aProducts'][$aData['iElement']] ) ) ) )."\n" );
              }
            }
            else{
              fwrite( $rFile2, $sContent."\n" );
            }
            if( $aData['iElement'] > $iLastElement )
              $iLastElement = $aData['iElement'];
          }
          $i++;
        } // end while

        if( isset( $bAddProduct ) ){
          $aAdd = $aForm['aNewProduct'];
          $aAdd['iElement'] = ( $iLastElement + 1 );
          $aAdd['fPrice'] = normalizePrice( $aAdd['fPrice'] );
          $aAdd['sName'] = trim( $aAdd['sName'] );
          $aAdd['iOrder'] = $aForm['iOrder'];
          fwrite( $rFile2, serialize( compareArrays( $this->aOrdersProductsFields, changeMassTxt( $aAdd, 'H' ) ) )."\n" );
        }
        fclose( $rFile1 );
        fclose( $rFile2 );
      }
    }
    $this->moveDatabaseFiles( );

  } // end function saveOrder

  /**
  * Lists all payment and shipping methods
  * @return string
  * @param int $iType
  */
  public function listPaymentsShippingAdmin( $iType = 1 ){
    global $lang;

    $content = null;
    $aData = $this->throwPaymentsShipping( $iType );
    
    if( isset( $aData ) ){
      if( $iType == 2 )
        $aPayments = $this->throwPaymentsShipping( 1 );

      $i = 0;
      $sAction = ( $iType == 2 ) ? 'shipping' : 'payments';
      foreach( $aData as $iKey => $aData ){
        $sPayments = null;
        if( isset( $aPayments ) && isset( $aData['aPayments'] ) ){
          foreach( $aData['aPayments'] as $iPayment => $mPrice ){
            if( isset( $aPayments[$iPayment] ) )
              $sPayments .= $aPayments[$iPayment]['sName'].', ';
          } // end foreach
        }

        $content .= '<tr class="l'.( ( $i % 2 ) ? 0: 1 ).'"><td class="id">'.$aData['iId'].'</td><td class="name"><a href="?p='.$sAction.'-form&amp;iId='.$aData['iId'].'">'.$aData['sName'].'</a></td><td class="status">'.throwYesNoTxt( isset( $aData['iStatus'] ) ? $aData['iStatus'] : 0 ).'</td>';
        
        if( $iType == 2 ){
          $content .= '<td class="price">'.$aData['fPrice'].'</td><td class="payments">'.$sPayments.'</td>';
        }

        $content .= '<td class="options"><a href="?p='.$sAction.'-form&amp;iId='.$aData['iId'].'"><img src="'.DIR_TEMPLATES.'admin/img/ico_edit.gif" alt="'.$lang['edit'].'" title="'.$lang['edit'].'" /></a> <a href="?p='.$sAction.'-delete&amp;iId='.$aData['iId'].'" onclick="return del( );"><img src="'.DIR_TEMPLATES.'admin/img/ico_del.gif" alt="'.$lang['delete'].'" title="'.$lang['delete'].'"/></a></td></tr>';
        $i++;
      } // end foreach

      if( isset( $content ) )
        return $content;
    }
  } // end function listPaymentsShippingAdmin

  /**
  * Deletes payment and shipping data
  * @return void
  * @param int $iId
  */
  public function deletePaymentShipping( $iId ){
    $oFFS = FlatFilesSerialize::getInstance( );
    $aPaymentsShipping = $oFFS->getData( DB_PAYMENTS_SHIPPING, true );
    unset( $aPaymentsShipping[$iId] );
    $oFFS->saveData( DB_PAYMENTS_SHIPPING, $aPaymentsShipping );
  } // end function deletePaymentShipping

  /**
  * Saves payment or shipping data
  * @return void
  * @param array $aForm
  * @param int $iType
  */
  public function savePaymentShipping( $aForm, $iType = 1 ){
    $oFFS = FlatFilesSerialize::getInstance( );

    $aPaymentsShipping = $oFFS->getData( DB_PAYMENTS_SHIPPING, true );

    if( isset( $aForm['iId'] ) && is_numeric( $aForm['iId'] ) ){
    }
    else{
      $aForm['iId'] = $oFFS->throwLastId( DB_PAYMENTS_SHIPPING, 'iId' ) + 1;
    }    

    if( !isset( $aForm['iStatus'] ) )
      $aForm['iStatus'] = 0;

    if( isset( $aForm['fPrice'] ) && is_numeric( str_replace( ',', '.', $aForm['fPrice'] ) ) )
      $aForm['fPrice'] = normalizePrice( $aForm['fPrice'] );

    $aPaymentsShipping[$aForm['iId']] = compareArrays( $GLOBALS['aPaymentsShippingFields'], changeMassTxt( $aForm, '' ) );
    $aPaymentsShipping[$aForm['iId']]['iType'] = $iType;
    if( isset( $aPaymentsShipping[$aForm['iId']]['aPayments'] ) )
      unset( $aPaymentsShipping[$aForm['iId']]['aPayments'] );

    if( isset( $aForm['aPayments'] ) ){
      foreach( $aForm['aPayments'] as $iId => $iValue ){
        $mPrice = isset( $aForm['aPaymentsPrices'][$iId] ) ? $aForm['aPaymentsPrices'][$iId] : null;
        $aPaymentsShipping[$aForm['iId']]['aPayments'][$iId] = $mPrice;
      }
    }

    $oFFS->saveData( DB_PAYMENTS_SHIPPING, $aPaymentsShipping );

    return $aForm['iId'];
  } // end function savePaymentShipping

  /**
  * Returns a list of shipping and related payment options
  * @return string
  * @param int $iId
  */
  public function listShippingPaymentsForm( $iId = null ){
    $oFFS = FlatFilesSerialize::getInstance( );
    $content = null;

    $aPayments = $this->throwPaymentsShipping( 1 );
    if( isset( $aPayments ) ){
      $aShipping = $this->throwPaymentsShipping( 2 );
      if( isset( $iId ) && isset( $aShipping[$iId] ) && isset( $aShipping[$iId]['aPayments'] ) ){
        $aCheck = $aShipping[$iId]['aPayments'];
      }

      $i = 0;
      foreach( $aPayments as $iKey => $aData ){
        $content .= '<tr><td><input type="checkbox" name="aPayments['.$aData['iId'].']"'.( isset( $aCheck[$iKey] ) ? ' checked="checked"' : null ).' value="1" onclick="changeInputStatus( this, \'aPayments'.$aData['iId'].'\' )" /></td><td><b>'.$aData['sName'].'</b></td><td><input type="text" name="aPaymentsPrices['.$aData['iId'].']" value="'.( isset( $aCheck[$iKey] ) ? $aCheck[$iKey] : null ).'" class="inputr'.( !isset( $aCheck[$iKey] ) ? ' inputrd' : null ).'" size="10" id="aPayments'.$aData['iId'].'" /> '.$GLOBALS['lang']['example'].' 10, -10, 10%</td></tr>';
        $i++;
      } // end foreach

      return $content;
    }
  } // end function listShippingPaymentsForm

  /**
  * Returns a status list
  * @return string
  * @param int $iOrder
  */
  public function listOrderStatuses( $iOrder ){
    if( isset( $this->aOrders[$iOrder]['aStatuses'] ) ){
      $content = null;
      $aStatuses = $this->aOrders[$iOrder]['aStatuses'];
      rsort( $aStatuses );

      foreach( $aStatuses as $iKey => $aValue ){
        $content .= '<li><span>'.displayDate( $aValue[0], $GLOBALS['config']['date_format_admin_orders'] ).'</span> - <strong>'.$this->throwStatus( $aValue[1] ).'</strong></li>';
      } // end foreach

      if( isset( $content ) ){
        return '<ul id="status">'.$content.'</ul>';
      }
    }
  } // end function listOrderStatuses

  /**
  * Deletes temporary data files the script operates on and overwrites files in the "database/" directory
  * @return void
  */
  private function moveDatabaseFiles( ){

    if( is_file( DB_ORDERS_PRODUCTS.'-backup' ) ){
      unlink( DB_ORDERS_PRODUCTS );
      rename( DB_ORDERS_PRODUCTS.'-backup', DB_ORDERS_PRODUCTS );
      chmod( DB_ORDERS_PRODUCTS, FILES_CHMOD );
    }

    if( is_file( DB_ORDERS_EXT.'-backup' ) ){
      unlink( DB_ORDERS_EXT );
      rename( DB_ORDERS_EXT.'-backup', DB_ORDERS_EXT );
      chmod( DB_ORDERS_EXT, FILES_CHMOD );
    }

    if( is_file( DB_ORDERS.'-backup' ) ){
      unlink( DB_ORDERS );
      rename( DB_ORDERS.'-backup', DB_ORDERS );
      chmod( DB_ORDERS, FILES_CHMOD );
    }
  } // end function moveDatabaseFiles

  /**
  * Lists recent orders
  * @return string
  */
  public function listLastOrders( ){
    if( !isset( $this->aOrders ) )
      $this->generateCache( );
    $content= null;

    if( isset( $this->aOrders ) ){
      foreach( $this->aOrders as $iOrder => $aData ){
        $aOrders[] = $iOrder;
      } // end foreach
    }

    if( isset( $aOrders ) ){
      rsort( $aOrders );
      $iMax = 5;
      $iCount = count( $aOrders );
      if( $iCount > $iMax )
        $iCount = $iMax;
      
      $content = null;

      for( $i = 0; $i < $iCount; $i++ ){
        $aData = $this->aOrders[$aOrders[$i]];
        $content .= '<tr><td class="id">'.$aData['iOrder'].'</td><td class="name"><a href="?p=orders-form&amp;iOrder='.$aData['iOrder'].'">'.$aData['sFirstName'].' '.$aData['sLastName'].'</a></td><td class="data">'.$aData['sDate'].'</td></tr>';
      } // end for
      
      unset( $this->aOrders );
      return '<table><thead><tr><td>'.$GLOBALS['lang']['Id'].'</td><td>'.$GLOBALS['lang']['Name'].'</td><td>'.$GLOBALS['lang']['Date'].'</td></tr></thead>'.$content.'</tbody></table>';
    }
  } // end function listLastOrders

  /**
  * Lists ordered products
  * @return string
  * @param int $iOrder
  */
  public function listProductsAdmin( $iOrder ){
    global $lang;

    $content = null;

    if( !isset( $this->aProducts ) ){
      $this->generateProducts( $iOrder );
    }

    if( isset( $this->aProducts ) ){
      $i = 0;
      $iCount = count( $this->aProducts );
      foreach( $this->aProducts as $aData ){
        $content .= '<tr><td class="id"><a href="?p=products-form&amp;iProduct='.$aData['iProduct'].'&amp;sLang='.$GLOBALS['sOrderLang'].'" target="_blank">'.$aData['iProduct'].'</a></td><td class="name"><input type="text" name="aProducts['.$aData['iElement'].'][sName]" value="'.$aData['sName'].'" class="input" size="40" data-form-check="required" /></td><td class="price"><input type="text" name="aProducts['.$aData['iElement'].'][fPrice]" value="'.displayPrice( $aData['fPrice'] ).'" class="inputr" size="7" maxlength="15" data-form-check="float" /></td><td class="quantity"><input type="text" name="aProducts['.$aData['iElement'].'][iQuantity]" value="'.$aData['iQuantity'].'" class="input" size="2" maxlength="3" data-form-check="int" /></td><td class="summary">'.displayPrice( normalizePrice( $aData['fSummary'] ) ).'</td><td class="options"><input type="checkbox" name="aProductsDelete['.$aData['iElement'].']" value="1" /></td></tr>';
        $i++;
      }

      $this->fProductsSummary = normalizePrice( $this->fProductsSummary );
      $this->aOrders[$iOrder]['fOrderSummary'] = $this->aOrders[$iOrder]['fProductsSummary'] = $this->fProductsSummary;
      $this->aOrders[$iOrder]['sOrderSummary'] = $this->aOrders[$iOrder]['sProductsSummary'] = displayPrice( $this->fProductsSummary );
      if( !empty( $this->aOrders[$iOrder]['fPaymentShippingPrice'] ) ){
        $this->aOrders[$iOrder]['fOrderSummary'] = normalizePrice( $this->fProductsSummary +  $this->aOrders[$iOrder]['fPaymentShippingPrice'] );
        $this->aOrders[$iOrder]['sOrderSummary'] = displayPrice( $this->aOrders[$iOrder]['fOrderSummary'] );
      }

      return $content;
    }
  } // end function listProductsAdmin
};
?>
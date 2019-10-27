<?php
class Orders
{

  public $aOrders = null;
  public $aProducts = null;
  public $fProductsSummary = null;
  protected $aOrdersFields = null;
  protected $aOrdersExtFields = null;
  protected $aOrdersProductsFields = null;
  protected $aOrdersTempFields = null;

  /**
  * Lists products in the basket
  * @return string
  * @param mixed $mData
  * @param bool $bPrint
  */
  public function listProducts( $mData = null, $bPrint = null ){
    global $lang;

    $content = null;

    if( !isset( $this->aProducts ) ){
      if( isset( $mData ) && is_numeric( $mData ) ){
        $this->generateProducts( $mData );
      }
      else{
        $this->generateBasket( );
      }
    }

    if( isset( $this->aProducts ) ){
      $i = 0;
      $iCount = count( $this->aProducts );
      foreach( $this->aProducts as $aData ){
        $aData['sPrice'] = displayPrice( normalizePrice( $aData['fPrice'] ) );
        $aData['sSummary'] = displayPrice( normalizePrice( $aData['fSummary'] ) );
        if( isset( $mData ) ){
          // order form and order print
          if( isset( $bPrint ) ){
            $content .= "\n".'- '.$aData['sName'].' - '.$lang['Price'].': '.$aData['sPrice'].' '.$GLOBALS['config']['currency_symbol'].', '.$lang['Quantity'].': '.$aData['iQuantity'].', '.$lang['Summary'].': '.$aData['sSummary'].' '.$GLOBALS['config']['currency_symbol'];
          }
          else{
            $content .= '<tr class="l'.( ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1 ).'">
                <th>
                  '.(isset( $aData['sLinkName'] )?'<a href="'.$aData['sLinkName'].'">'.$aData['sName'].'</a>':$aData['sName']).'
                </th>
                <td class="price">
                  '.$aData['sPrice'].'
                </td>
                <td class="quantity">
                  '.$aData['iQuantity'].'
                </td>
                <td class="summary">
                  '.$aData['sSummary'].'
                </td>
              </tr>';
          }
        }
        else{
          // basket
          $content .= '<tr class="l'.( ( $i == ( $iCount - 1 ) ) ? 'L': $i + 1 ).'">
            <th>
              <a href="'.$aData['sLinkName'].'">'.$aData['sName'].'</a>
            </th>
            <td class="price">
              '.$aData['sPrice'].'
            </td>
            <td class="quantity">
              <label for="quantity'.$aData['iProduct'].'">'.$lang['Quantity'].'</label><input type="text" name="aProducts['.$aData['iProduct'].']" value="'.$aData['iQuantity'].'" size="3" maxlength="4" class="input" id="quantity'.$aData['iProduct'].'" data-form-check="int" />
            </td>
            <td class="summary">
              '.$aData['sSummary'].'
            </td>
            <td class="del">
              <a href="'.$aData['sLinkDelete'].'" title="'.$lang['Delete'].' - '.$aData['sName'].'">'.$lang['Delete'].'</a>
            </td>
          </tr>';
        }
        $i++;
      }

      $this->fProductsSummary = normalizePrice( $this->fProductsSummary );
      if( isset( $mData ) && is_numeric( $mData ) && isset( $this->aOrders[$mData] ) ){
        $this->aOrders[$mData]['fOrderSummary'] = $this->aOrders[$mData]['fProductsSummary'] = $this->fProductsSummary;
        $this->aOrders[$mData]['sOrderSummary'] = $this->aOrders[$mData]['sProductsSummary'] = displayPrice( $this->fProductsSummary );
        if( !empty( $this->aOrders[$mData]['fPaymentShippingPrice'] ) ){
          $this->aOrders[$mData]['fOrderSummary'] = normalizePrice( $this->fProductsSummary +  $this->aOrders[$mData]['fPaymentShippingPrice'] );
          $this->aOrders[$mData]['sOrderSummary'] = displayPrice( $this->aOrders[$mData]['fOrderSummary'] );
        }
      }

      return $content;
    }
  } // end function listProducts

  /**
  * Generates a variable with products in the basket
  * @return void
  */
  public function generateBasket( ){
    $oFFS = FlatFilesSerialize::getInstance( );

    $aBasket = $oFFS->getData( DB_ORDERS_TEMP );
    $iOrder = $_SESSION['iCustomer'.LANGUAGE];
    $_SESSION['iOrderQuantity'.LANGUAGE] = 0;
    $_SESSION['fOrderSummary'.LANGUAGE] = null;

    if( isset( $aBasket[$iOrder] ) && count( $aBasket[$iOrder] ) > 0 ){
      $oProduct = Products::getInstance( );
      $this->aProducts = null;
      $this->fProductsSummary = null;

      foreach( $aBasket[$iOrder] as $iProduct => $aData ){
        $aData['iProduct'] = $iProduct;
        $this->aProducts[$iProduct] = $aData;
        $this->aProducts[$iProduct]['sLinkName'] = $oProduct->aProducts[$aData['iProduct']]['sLinkName'];
        $this->aProducts[$iProduct]['fSummary'] = normalizePrice( $aData['fPrice'] * $aData['iQuantity'] );
        if( isset( $GLOBALS['aData']['sLinkName'] ) )
          $this->aProducts[$iProduct]['sLinkDelete'] = $GLOBALS['aData']['sLinkName'].'&amp;iProductDelete='.$aData['iProduct'];
        $_SESSION['iOrderQuantity'.LANGUAGE] += $aData['iQuantity'];
        $_SESSION['fOrderSummary'.LANGUAGE]  += ( $aData['fPrice'] * $aData['iQuantity'] );        
      } // end foreach

      if( isset( $_SESSION['fOrderSummary'.LANGUAGE] ) ){
        $this->fProductsSummary = $_SESSION['fOrderSummary'.LANGUAGE] = normalizePrice( $_SESSION['fOrderSummary'.LANGUAGE] );
      }
    }
  } // end function generateBasket

  /**
  * Generates a variable with products in an order
  * @return void
  * @param int  $iOrder
  */
  public function generateProducts( $iOrder ){
    $rFile = fopen( DB_ORDERS_PRODUCTS, 'r' );
    $i = 0;
    $this->fProductsSummary = null;
    while( !feof( $rFile ) ){
      $sContent = fgets( $rFile );
      if( $i > 0 && !empty( $sContent ) ){
        $aData = unserialize( trim( $sContent ) );
        if( $aData['iOrder'] == $iOrder ){
          $this->aProducts[$aData['iElement']] = $aData;
          $this->aProducts[$aData['iElement']]['fSummary'] = normalizePrice( $this->aProducts[$aData['iElement']]['fPrice'] * $this->aProducts[$aData['iElement']]['iQuantity'] );
          $this->fProductsSummary += $this->aProducts[$aData['iElement']]['fPrice'] * $this->aProducts[$aData['iElement']]['iQuantity'];
        }
      }
      $i++;
    } // end while
    fclose( $rFile );

    if( isset( $this->fProductsSummary ) ){
      $this->fProductsSummary = normalizePrice( $this->fProductsSummary );
    }
  } // end function generateProducts

  /**
  * Checks if the basket is empty
  * @return bool
  */
  public function checkEmptyBasket( ){
    $this->generateBasket( );
    return ( isset( $this->aProducts ) ) ? false : true;
  } // end function checkEmptyBasket

  /**
  * Deletes a product from the basket
  * @return void
  * @param int  $iProduct
  */
  public function deleteFromBasket( $iProduct ){
    $iOrder = $_SESSION['iCustomer'.LANGUAGE];
    $oFFS = FlatFilesSerialize::getInstance( );

    $aBasket = $oFFS->getData( DB_ORDERS_TEMP );
    if( isset( $aBasket[$iOrder][$iProduct] ) ){
      unset( $aBasket[$iOrder][$iProduct] );
      if( count( $aBasket[$iOrder] ) == 0 )
        unset( $aBasket[$iOrder] );
      $oFFS->saveData( DB_ORDERS_TEMP, $aBasket );
    }        
  } // end function deleteFromBasket

  /**
  * Saves the basket
  * @return void
  * @param array $aData
  * @param bool $bAdd
  */
  public function saveBasket( $aData, $bAdd = null ){
    if( isset( $aData ) && is_array( $aData ) ){
      $iOrder = $_SESSION['iCustomer'.LANGUAGE];
      $oFFS = FlatFilesSerialize::getInstance( );
      $oProduct = Products::getInstance( );

      $aBasket = $oFFS->getData( DB_ORDERS_TEMP );
      
      foreach( $aData as $iProduct => $iQuantity ){
        if( $iQuantity > 0 && $iQuantity <= $GLOBALS['config']['max_product_quantity'] && is_numeric( $iQuantity ) && isset( $oProduct->aProducts[$iProduct] ) ){
          $iQuantity = trim( $iQuantity );

          if( isset( $aBasket[$iOrder][$iProduct] ) ){
            if( isset( $bAdd ) ){
              $iSum = ( $aBasket[$iOrder][$iProduct]['iQuantity'] + $iQuantity );
              if( $iSum > 0 && $iSum <= $GLOBALS['config']['max_product_quantity'] ){
                $aBasket[$iOrder][$iProduct]['iQuantity'] += (int) $iQuantity;
                $bChanged = true;
              }
            }
            else{
              if( $iQuantity != $aBasket[$iOrder][$iProduct]['iQuantity'] ){
                $aBasket[$iOrder][$iProduct]['iQuantity'] = (int) $iQuantity;
                $bChanged = true;              
              }
            }
          }
          else{
            
            $aBasket[$iOrder][$iProduct] = Array( 'iQuantity' => (int) $iQuantity, 'fPrice' => $oProduct->aProducts[$iProduct]['mPrice'], 'sName' => $oProduct->aProducts[$iProduct]['sName'] );
            $bChanged = true;
            $bDeleteOldOrders = true;
          }
        }
      } // end foreach

      if( isset( $bChanged ) ){
        if( isset( $bDeleteOldOrders ) ){
          $iTime = time( );
          foreach( $aBasket as $iKey => $aValue ){
            if( $iTime - substr( $iKey, 0, 10 ) >= 259200 )
              unset( $aBasket[$iKey] );
          } // end foreach
        }

        if( isset( $aBasket ) )
          $oFFS->saveData( DB_ORDERS_TEMP, $aBasket );
      }
    }

  } // end function saveBasket

  /**
  * Checks order fields
  * @return bool
  * @param array  $aForm
  */
  public function checkFields( $aForm ){
    if( isset( $aForm['sShippingPayment'] ) ){
      $aExp = explode( ';', $aForm['sShippingPayment'] );
      if( isset( $aExp[0] ) && isset( $aExp[1] ) )
        $sPrice = $this->throwShippingPaymentPrice( $aExp[0], $aExp[1] );
    }
    else{
      $aShipping = $this->throwPaymentsShipping( 2 );
      if( isset( $aShipping ) )
        return false;
      else
        $sPrice = true;
    }

    if(
      checkFormFields( $aForm, Array( 'sFirstName' => true, 'sLastName' => true, 'sStreet' => true, 'sZipCode' => true, 'sCity' => true, 'sPhone' => true, 'sEmail' => Array( 'email' ), 'sComment' => Array( 'textarea', false ) ), true )
      && isset( $sPrice )
      && ( ( isset( $aForm['iRules'] ) && isset( $aForm['iRulesAccept'] ) ) || !isset( $aForm['iRules'] ) )
    ){
      return true;
    }
    else
      return false;
  } // end function checkFields

  /**
  * Adds order to the database
  * @return int
  * @param array  $aForm
  */
  public function addOrder( $aForm ){
    $oFFS = FlatFilesSerialize::getInstance( );

    $this->aOrdersFields = $GLOBALS['aOrdersFields'];
    $this->aOrdersExtFields = $GLOBALS['aOrdersExtFields'];
    $this->aOrdersProductsFields = $GLOBALS['aOrdersProductsFields'];

    $aForm = changeMassTxt( $aForm, 'Hs', Array( 'sComment', 'LenHsNds' ) );
    if( isset( $aForm['sShippingPayment'] ) ){
      $aExp = explode( ';', $aForm['sShippingPayment'] );
      $aShipping = $this->throwPaymentShipping( $aExp[0] );
      $aPayment = $this->throwPaymentShipping( $aExp[1] );
      $aForm['iShipping'] = $aShipping['iId'];
      $aForm['iPayment'] = $aPayment['iId'];
      $aForm['mPaymentPrice'] = $this->throwShippingPaymentPrice( $aExp[0], $aExp[1] );
      $aForm['fShippingPrice'] = $aShipping['fPrice'];
      $aForm['mShipping'] = $aShipping['sName'];
      $aForm['mPayment'] = $aPayment['sName'];
    }

    $aForm = array_merge( $aForm, Array( 'iOrder' => ( $this->throwLastId( DB_ORDERS, 'iOrder' ) + 1 ), 'iTime' => time( ), 'sIp' => $_SERVER['REMOTE_ADDR'], 'iStatus' => 1, 'sLanguage' => LANGUAGE ) );

    file_put_contents( DB_ORDERS, serialize( compareArrays( $this->aOrdersFields, $aForm ) )."\n", FILE_APPEND );
    file_put_contents( DB_ORDERS_EXT, serialize( compareArrays( $this->aOrdersExtFields, $aForm ) )."\n", FILE_APPEND );

    if( isset( $this->aProducts ) ){
      $iElement = $this->throwLastId( DB_ORDERS_PRODUCTS, 'iElement' ) + 1;
      foreach( $this->aProducts as $aData ){
        file_put_contents( DB_ORDERS_PRODUCTS, serialize( compareArrays( $this->aOrdersProductsFields, Array( 'iElement' => (int) $iElement++, 'iOrder' => $aForm['iOrder'], 'iProduct' => $aData['iProduct'], 'iQuantity' => $aData['iQuantity'], 'fPrice' => $aData['fPrice'], 'sName' => $aData['sName'] ) ) )."\n", FILE_APPEND );
      }
    }

    $aBasket = $oFFS->getData( DB_ORDERS_TEMP );
    $iOrderTemp = $_SESSION['iCustomer'.LANGUAGE];
    if( isset( $aBasket[$iOrderTemp] ) ){
      unset( $aBasket[$iOrderTemp] );
      $oFFS->saveData( DB_ORDERS_TEMP, $aBasket );
    }       
    
    $_SESSION['iOrderQuantity'.LANGUAGE] = 0;
    $_SESSION['fOrderSummary'.LANGUAGE] = null;

    return $aForm['iOrder'];
  } // end function addOrder

  /**
  * Returns last id in the database
  * @return int
  * @param string $sFile
  * @param string $sKey
  */
  private function throwLastId( $sFile, $sKey ){
    $rFile = fopen( $sFile, 'r' );
    $i = 0;
    $iLastId = 0;
    while( !feof( $rFile ) ){
      $sContent = fgets( $rFile );
      if( $i > 0 && !empty( $sContent ) ){
        $aData = unserialize( trim( $sContent ) );
        if( $aData[$sKey] > $iLastId )
          $iLastId = $aData[$sKey];
      }
      $i++;
    } // end while
    fclose( $rFile );

    return $iLastId;
  } // end function throwLastId

  /**
  * Returns order status
  * @return string
  * @param int    $iStatus
  */
  public function throwStatus( $iStatus = null ){
    global $lang;
    $aStatus[1] = $lang['Orders_pending'];
    $aStatus[2] = $lang['Orders_processing'];
    $aStatus[3] = $lang['Orders_finished'];
    $aStatus[4] = $lang['Orders_canceled'];
    return isset( $iStatus ) ? $aStatus[$iStatus] : $aStatus;
  } // end function throwStatus

  /**
  * Returns order data
  * @return array
  * @param int  $iOrder
  */
  public function throwOrder( $iOrder ){

    if( isset( $this->aOrders[$iOrder] ) ){
      $aData = $this->aOrders[$iOrder];
    }
    else{
      $rFile = fopen( DB_ORDERS, 'r' );
      $i = 0;
      while( !feof( $rFile ) ){
        $sContent = fgets( $rFile );
        if( $i > 0 && !empty( $sContent ) ){
          $aOrder = unserialize( trim( $sContent ) );
          if( $aOrder['iOrder'] == $iOrder ){
            $aData = $aOrder;
            break;
          }
        }
        $i++;
      } // end while
      fclose( $rFile );
    }

    if( isset( $aData ) ){
      $rFile = fopen( DB_ORDERS_EXT, 'r' );
      $i = 0;
      while( !feof( $rFile ) ){
        $sContent = fgets( $rFile );
        if( $i > 0 && !empty( $sContent ) ){
          $aOrder = unserialize( trim( $sContent ) );
          if( $aOrder['iOrder'] == $iOrder ){
            $aDataExt = $aOrder;
            break;
          }
        }
        $i++;
      } // end while
      fclose( $rFile );

      if( isset( $aDataExt ) ){
        $aData = array_merge( $aData, $aDataExt );
        if( isset( $aData['iShipping'] ) ){
          $aData['fPaymentShippingPrice'] = generatePrice( isset( $aData['fShippingPrice'] ) ? $aData['fShippingPrice'] : 0, isset( $aData['mPaymentPrice'] ) ? $aData['mPaymentPrice'] : 0 );
          $aData['sPaymentShippingPrice'] = displayPrice( $aData['fPaymentShippingPrice'] );
        }
        $aData['sDate'] = displayDate( $aData['iTime'], defined( 'CUSTOMER_PAGE' ) ? $GLOBALS['config']['date_format_customer_orders'] : $GLOBALS['config']['date_format_admin_orders'] );
        $this->aOrders[$iOrder] = $aData;
        return $aData;
      }
    }
  } // end function throwOrder

  /**
  * Returns saved order's id
  * @return int
  * @param string $sOrder
  */
  public function throwSavedOrderId( $sOrder ){
    $oFFS = FlatFilesSerialize::getInstance( );
    $aBasket = $oFFS->getData( DB_ORDERS_TEMP );
    if( isset( $aBasket ) && is_array( $aBasket ) && count( $aBasket ) > 0 ){
      foreach( $aBasket as $iOrder => $aValue ){
        if( $sOrder == md5( $iOrder ) ){
          return $iOrder;
        }
      }
    }
    return null;
  } // end function throwSavedOrderId

  /**
  * Returns payment and shipping costs
  * @return string
  * @param int  $iShipping
  * @param int  $iPayment
  */
  private function throwShippingPaymentPrice( $iShipping, $iPayment ){
    $aShipping = $this->throwPaymentsShipping( 2 );
    $aPayments = $this->throwPaymentsShipping( 1 );
    if( isset( $aShipping[$iShipping] ) && isset( $aPayments[$iPayment] ) ){
      if( isset( $aShipping[$iShipping]['aPayments'][$iPayment] ) ){
        return $aShipping[$iShipping]['aPayments'][$iPayment];
      }
    }
  } // end function throwShippingPaymentPrice

  /**
  * Returns shipping and payment select
  * @return string
  */
  public function throwShippingPaymentsSelect( ){
    $aShipping = $this->throwPaymentsShipping( 2 );
    $aPayments = $this->throwPaymentsShipping( 1 );
    if( isset( $aShipping ) && isset( $aPayments ) ){
      $content = null;
      foreach( $aShipping as $iShipping => $aData ){
        if( isset( $aData['aPayments'] ) ){
          foreach( $aData['aPayments'] as $iPayment => $sPriceModify ){
            if( isset( $aPayments[$iPayment] ) ){
              $fShippingPaymentPrice = !empty( $sPriceModify ) ? generatePrice( $aData['fPrice'], $sPriceModify ) : $aData['fPrice'];
              $content .= 
              '<option value="'.$iShipping.';'.$iPayment.';'.$fShippingPaymentPrice.'">'.
                $aData['sName'].' - '.$aPayments[$iPayment]['sName'].': '.
                displayPrice( $fShippingPaymentPrice ).' '.$GLOBALS['config']['currency_symbol']
              .'</option>';
            }
          } // end foreach
        }
      } // end foreach
      return $content;
    }
  } // end function throwShippingPaymentsSelect

  /**
  * Returns payment or shipping data
  * @return array
  * @param int $iId
  * @param int $iType
  */
  public function throwPaymentShipping( $iId, $iType = 1 ){
    $oFFS = FlatFilesSerialize::getInstance( );
    $aPaymentsShipping = $oFFS->getData( DB_PAYMENTS_SHIPPING, true );
    if( isset( $aPaymentsShipping[$iId] ) )
      return $aPaymentsShipping[$iId];
  } // end function throwPayment

  /**
  * Sends email to admin with order details
  * @return void
  * @param int $iOrder
  */
  public function sendEmailWithOrderDetails( $iOrder ){
    global $lang, $config;

    $aData = $this->throwOrder( $iOrder );
    $sMailContent = str_replace( '|n|', "\n", $lang['Order_customer_email_head']."\n------------------------\n".$lang['Order_customer_personal']."\n------------------------\n".$aData['sFirstName'].' '.$aData['sLastName'].( isset( $aData['sCompanyName'] ) ? "\n".$aData['sCompanyName'] : null )."\n".$aData['sStreet']."\n".$aData['sZipCode'].' '.$aData['sCity']."\n".$aData['sPhone']."\n".$aData['sEmail'].( isset( $aData['sComment'] ) ? "\n\n".$lang['Comment'].': '.$aData['sComment'] : null )."\n------------------------\n".$lang['Order_customer_products']."\n------------------------".$this->listProducts( $iOrder, true )."\n------------------------\n".( isset( $aData['iShipping'] ) ? $lang['Order_customer_shipping']."\n------------------------\n".$aData['mShipping'].' ('.$aData['mPayment'].') = '.$this->aOrders[$iOrder]['sPaymentShippingPrice'].' '.$config['currency_symbol']."\n\n" : null ).$lang['Summary_cost'].': '.$this->aOrders[$iOrder]['sOrderSummary'].' '.$config['currency_symbol']."\n------------------------\n".$lang['Order_customer_email_foot'] );

    // the following phrase must be present in the email's content. See the license - http://opensolution.org/licenses.html
    $sMailContent .= "\n\n".( LANGUAGE == 'pl' ) ? 'Wysłane przez program Quick.Cart' : 'Sent by the Quick.Cart program';

    if( $config['send_customer_order_details'] === true ){
      @mail( $aData['sEmail'], '=?UTF-8?B?'.base64_encode( $lang['Order_customer_info_title'].$iOrder ).'?=', $sMailContent, 'MIME-Version: 1.0'."\r\n".'Content-type: text/plain; charset=UTF-8'."\r\n".( ( $config['emails_from_header_option'] == 2 ) ? 'Reply-to: '.$config['orders_email'] : 'From: '.$config['orders_email'] ) );
    }

    @mail( $config['orders_email'], '=?UTF-8?B?'.base64_encode( $lang['Order_customer_info_title'].$iOrder ).'?=', $sMailContent, 'MIME-Version: 1.0'."\r\n".'Content-type: text/plain; charset=UTF-8'."\r\n".( ( $config['order_details_from_customer'] === true && $config['emails_from_header_option'] == 1 ) ? 'From: '.$aData['sEmail'] : ( ( $config['emails_from_header_option'] == 1 ) ? 'From: '.$config['orders_email'] : 'Reply-to: '.$aData['sEmail'] ) ) );
  } // end function sendEmailWithOrderDetails

  /**
  * Returns payment or shipping array filtered by status
  * @return array
  * @param int $iType
  */
  protected function throwPaymentsShipping( $iType = 1 ){
    $oFFS = FlatFilesSerialize::getInstance( );
    $iStatus = throwStatus( );
    $aData = $oFFS->getData( DB_PAYMENTS_SHIPPING, true );

    if( isset( $aData ) && is_array( $aData ) && count( $aData ) > 0 ){
      foreach( $aData as $iKey => $aPaymentShipping ){
        if( $aPaymentShipping['iType'] == $iType && ( !isset( $aPaymentShipping['iStatus'] ) || isset( $aPaymentShipping['iStatus'] ) && $aPaymentShipping['iStatus'] >= $iStatus ) ){
          $aReturn[$iKey] = $aPaymentShipping;
        }
      } // end foreach
      if( isset( $aReturn ) )
        return $aReturn;
    }
  } // end function throwPaymentsShipping

};
?>
<?php
// database/xx_pages.php fields - where xx is en, pl, cz etc.
$aPagesFields = Array( 'iPage', 'iPageParent', 'sName', 'sNameTitle', 'sNameUrl', 'iStatus', 'iPosition', 'iType', 'iSubpagesShow', 'iProducts', 'sDescriptionShort', 'sUrl', 'sMetaDescription', 'sTheme', 'sDescriptionFull' );

// database/xx_pages_files.php fields - where xx is en, pl, cz etc.
$aPagesFilesFields = Array( 'iFile', 'iPage', 'sFileName', 'iPhoto', 'iPosition', 'iType', 'iSize1', 'iSize2', 'sDescription' );

// database/xx_products.php fields - where xx is en, pl, cz etc.
$aProductsFields = Array( 'iProduct', 'sName', 'sNameUrl', 'mPrice', 'iStatus', 'iPosition',  'sAvailable', 'sNameTitle', 'sTemplate', 'sTheme', 'sMetaKeywords', 'sMetaDescription', 'sDescriptionShort', 'sDescriptionFull' );

// database/xx_products_files.php fields - where xx is en, pl, cz etc.
$aProductsFilesFields = Array( 'iFile', 'iProduct', 'sFileName', 'iPhoto', 'iPosition', 'iType', 'iSize1', 'iSize2', 'sDescription' );

// database/xx_payments_shipping.php fields - where xx is en, pl, cz etc.
$aPaymentsShippingFields = Array( 'iId', 'iType', 'iStatus', 'sName', 'fPrice' );

// database/orders.php fields
$aOrdersFields = Array( 'iOrder', 'sLanguage', 'iStatus', 'iTime', 'sFirstName', 'sLastName', 'sCompanyName', 'sStreet', 'sZipCode', 'sCity', 'sPhone', 'sEmail' );

// database/orders_ext.php fields
$aOrdersExtFields = Array( 'iOrder', 'iShipping', 'iPayment', 'mShipping', 'fShippingPrice', 'mPayment', 'mPaymentPrice', 'sIp', 'sComment' );

// database/orders_products.php fields
$aOrdersProductsFields = Array( 'iElement', 'iOrder', 'iProduct', 'iQuantity', 'fPrice', 'sName' );

// database/orders_temp.php fields
$aOrdersTempFields = Array( 'iQuantity', 'fPrice', 'sName' );
?>
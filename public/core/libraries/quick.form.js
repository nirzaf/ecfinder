/* 
Quick.Form v1.1
License:
  Code in this file (or any part of it) can be used only as part of Quick.Cms.Ext v6.1 or later. All rights reserved by OpenSolution.
*/

var reEmail = /^[a-z0-9_.-]+([_\\.-][a-z0-9]+)*@([a-z0-9_\.-]+([\.][a-z]{2,15}))+$/i;
var reDate = /[0-9]{4}[-][0-9]{2}[-][0-9]{2}/;
var reExt = /[0-9]{4}[-][0-9]{2}[-][0-9]{2}/;

(function($){
$.fn.quickform = function( aOptions ){
  var aErrorsTxt = [],
      aErrorsObj = [],
      aConfig = {},
      aDefault = {
        bVisibleOnly: false,
        oCallbackBefore: function() {},
      };

  // function of displaying errors resulting by incorrect form filling
  function alertErrors( aErrors ){
    var sFormError = '';
    if( aErrors.length > 0 ){
      for( var i = 0; i < aErrors.length; i++ )
        sFormError += aErrors[i]+'\n';
      return sFormError;
    }
    return null;
  }
  
  // inccorectly filled inputs highlighting
  function throwErrors( aErrorsObj ){
    for( var i = 0; i < aErrorsObj.length; i++ ){
      aErrorsObj[i].addClass( throwWarningClass( ) );
    }
  }

  function throwWarningClass( ){
    if( typeof aCF['sWarningClass'] !== 'undefined' )
      return aCF['sWarningClass'];
    else
      return 'warning-required'
  }

  // function deleting repeated values from the array
  function arrayUnique( a ){
    return a.reduce( function( p, c ){
      if( p.indexOf(c) < 0 )
        p.push(c);
      return p;
    }, [] );
  }

  // checking the input on the basis of data attribute
  function checkInput( oObj, aDataForm ){
    var bDataIf = oObj.attr('data-form-if'),
        sVal = ( (!oObj.is( 'input[type=file]') && !oObj.is( 'select' ) ) ? oObj.val().trim() : oObj.val());

    if( ( bDataIf && sVal != '' ) || ( !bDataIf ) ){

      if( aDataForm[0] == 'required' ){
        if( !sVal ){
          return 'sWarning';
        }
        else if( ( $.isNumeric( aDataForm[1] ) && sVal.length < aDataForm[1] ) ){
          return 'sTooShort';
        }
        else if( ( $.isNumeric( aDataForm[2] ) && sVal.length > aDataForm[2] ) ){
          return 'sTooLong';
        }
        else if( oObj.attr( 'type' ) == 'checkbox' && oObj.prop( 'checked' ) != true ){
          return 'sWarning';
        }
      }
      else if( aDataForm[0] == 'int' ){
        if( $.isNumeric( sVal ) && Math.floor( sVal ) == (sVal*1) ){
          if( $.isNumeric( aDataForm[1] ) && parseInt( sVal ) < aDataForm[1] )
            return 'sToSmallValue';
          if( $.isNumeric( aDataForm[2] ) && parseInt( sVal ) > aDataForm[2] )
            return 'sTooLargeValue';
        }
        else{
          return 'sInt';
        }
      }
      else if( aDataForm[0] == 'float' ){
        sVal = sVal.replace( ',', '.' );
        oObj.val( sVal );
        if( $.isNumeric( sVal ) ){
          if( $.isNumeric( aDataForm[1] ) && parseFloat( sVal ) < aDataForm[1] )
            return 'sToSmallValue';
          else if( $.isNumeric( aDataForm[2] ) && parseFloat( sVal ) > aDataForm[2] ){
            return 'sTooLargeValue';
          }
        }
        else{
          return 'sFloat';
        }
      }
      else if( aDataForm[0] == 'email' && sVal.search( reEmail ) == -1 ){
        return 'sEmail';
      }
      else if( aDataForm[0] == 'date' && sVal.search( reDate ) == -1 ){
        return 'sDate';
      }
      else if( aDataForm[0] == 'ext' ){
        var sExt = sVal.split('.');
        if( sExt.length < 2 || ( sExt[sExt.length - 1] ).toLowerCase().search( aDataForm[1] ) == -1 )
          return 'sWarning'; 
      }
      else if( aDataForm[0] == 'select' && sVal == "" ){
        return 'sSelect';
      }
    }
  }
  
  // inputs checking
  function checkInputs( oObj, aDataForm ){
    var sError = checkInput( oObj, aDataForm );
    oObj.removeClass( throwWarningClass( ) );

    if( typeof sError == 'string' ){
      aErrorsObj[aErrorsObj.length] = oObj;
      aErrorsTxt[aErrorsTxt.length] = ( typeof oObj.attr( 'data-form-msg' ) == 'string' ) ? oObj.attr( 'data-form-msg' ) : aCF[sError];
    }
  }

  // function will be called at the form approval
  $(this).submit(function(){
    aConfig = $.extend({}, aDefault, aOptions);
    if( typeof aConfig.oCallbackBefore === 'function' ){
      if( aConfig.oCallbackBefore.call( this ) === false )
        return false;
    }
    var sFilter = aConfig.bVisibleOnly === true ? ':visible' : '*';
    aErrorsTxt = [];
    aErrorsObj = [];
    $( this ).find( "input, textarea, select" ).filter( sFilter ).each(function( index ){
      var sDataCheck = $(this).attr('data-form-check');

      if( typeof sDataCheck != 'undefined' && sDataCheck != '' ){
        checkInputs( $(this), sDataCheck.split(";") );
      }
    }); 
    aErrorsTxt = arrayUnique( aErrorsTxt );
    throwErrors( aErrorsObj );
    var sError = alertErrors( aErrorsTxt );
    if( typeof sError == 'string' ){
      if( $( '.main-form ul.tabs' ).length > 0 ){
        displayTab( '#'+aErrorsObj[0].closest( '.forms' ).prop( 'id' ).replace( 'tab-', '' ) );
      }
      aErrorsObj[0].focus();
      alert( sError );
      return false;
    }
  });
};
})(jQuery);

/* 
Quick.Box v1.1-qc (v1.1 modified for Quick.Cart - product details images preview)
License:
  Code in this file (or any part of it) can be used only as part of Quick.Cart v6.7 or later. All rights reserved by OpenSolution.
*/

// initialization of variables
var oQuickbox = {},
    iAllImages = 0;
    iCurrentImage = 0,
    sQuickGallery = null,
    sNavOutside = 'null', // possible options: null (default), arrows, close, all
    sLoading = '<span class="loading">Loading...</span>';

// download to the cache of all images in the appropriate class
function getQuickboxCache(){
  var aLinks = $('a[class*="quickbox"]');
  if( aLinks.length ){
    aLinks.each( addLinkToCache );
  }
  if( $( '#previewLink' ).length ){
    if( typeof oQuickbox['preview'] !== 'undefined' ){
      $( '#previewLink' ).click( function( e ){ e.preventDefault(); $( '#imagesListPreview a' ).first().click(); } );
    }
    else{
      $( '#previewLink' ).addClass( 'quickbox' );
      addLinkToCache( 0, $( '#previewLink' ) );
    }
  }
}

function addLinkToCache( iI, oEl ){
  // get the cache attribute
  var sClass = $(oEl).attr( 'class' );
  $(oEl).click(function( e ){
    e.preventDefault();
  });
  // check the gallery type e.g.( quickbox[top] )
  var iStartIndex = sClass.indexOf('['),
      iEndIndex = sClass.indexOf(']'),
      sIndex = sClass.substr(iStartIndex+1,iEndIndex-iStartIndex-1);
  
  // images categorizing
  if( !( oQuickbox[sIndex] ) ){
    if( sIndex == '' )
      sIndex = 'single';
    if( !oQuickbox[sIndex] )
      oQuickbox[sIndex] = [];
  }
  // creating arrays of images
  if( ( oQuickbox[sIndex] ) ){
    oQuickbox[sIndex][oQuickbox[sIndex].length] = $(oEl); 
    $(oEl).attr( 'onclick', 'quickboxInitialize( this, "'+sIndex+'", '+( oQuickbox[sIndex].length - 1 )+' )' );
  }
} 

// keyboard handling
function keyUpHandler( e ){
  ( e.keyCode == 37 && $( '#quick-box .prev:visible').length > 0 ) ? changeImage( 'prev' ) : null;
  ( e.keyCode == 39 && $( '#quick-box .next:visible').length > 0 ) ? changeImage( 'next' ) : null;
  ( e.keyCode == 27 ) ? $('#quick-box').remove() : null;
}

// loading the image to display
function loadImagesWithDetail( ){
  if( $( '#quick-box .image-wrapper img' ).length )
    $( '#quick-box .image-wrapper img' ).remove();
  
  changePositions();
  // adding the appropriate elements before loading the image
  $( '#quick-box .prev span, #quick-box .next span' ).hide();
  $( '#quick-box .image-wrapper .loading' ).remove();
  $( '#quick-box .image-wrapper' ).append( sLoading );
  $( '#quick-box .description' ).hide();

  // image loading
  $('<img src="'+ oQuickbox[sQuickGallery][iCurrentImage].attr( 'href' ) +'"/>').load(function() {
    // change elements properties after load
    $( '#quick-box .prev span, #quick-box .next span' ).show();
    $( '#quick-box .image-wrapper .loading' ).remove();
    $('#quick-box .image-wrapper').append( $(this) );
    $( '#quick-box .description' ).remove();

    // adding description to the image if there is such
    if( typeof( oQuickbox[sQuickGallery][iCurrentImage].attr( 'title' ) ) != 'undefined' && oQuickbox[sQuickGallery][iCurrentImage].attr( 'title' ) != '' )
      $( '#quick-box .quick-box-container' ).append( '<p class="description">'+oQuickbox[sQuickGallery][iCurrentImage].attr( 'title' ) +'</p>' ).show();
    updateControls();
    changePositions();
  });
}

// loading content to display
function loadContent( sClass ){
  $('#quick-box .image-wrapper').removeClass( 'image-wrapper' ).addClass( 'content-wrapper' );
  $('#quick-box .prev, #quick-box .next').remove();
  $('#quick-box .content-wrapper').prepend( $( '.'+sClass ).clone() );
  $('#quick-box .content-wrapper .'+sClass).show();
  changeContentPosition();
}

// setting pop-up position
function changeContentPosition( ){
  iTopPos = Math.round(($(window).height()-$('#quick-box .content-wrapper').height())/2)+'px';
  iLeftPos = Math.round(($(window).width()-$('#quick-box .content-wrapper').width())/2)+'px';        
  $( '#quick-box .quick-box-container' ).animate({ top: iTopPos, left: iLeftPos});
}

// function is responsible for adapting images to the appropriate size
function resizeImage( aSizes ){
  var iRatio = aSizes['iImageHeight'] > 0 ? ( Math.round( aSizes['iImageHeight'] / aSizes['iImageWidth'] * 100 ) / 100 ) : 0;
  // if the height is bigger
  if( iRatio >= 1 ){
    if( ( aSizes['iWindowHeight'] < aSizes['iImageHeight'] ) || ( aSizes['iWindowWidth'] < aSizes['iImageWidth'] ) ){
      aSizes['iImageHeight'] = aSizes['iWindowHeight'];
      aSizes['iImageWidth'] = Math.round( aSizes['iImageHeight'] / iRatio );
      // if the width is bigger than the screen width
      if( aSizes['iWindowWidth'] < aSizes['iImageWidth'] ){
        aSizes['iImageWidth'] = aSizes['iWindowWidth'];
        aSizes['iImageHeight'] = Math.round( aSizes['iImageWidth'] * iRatio );
      }
      changeImageSize( aSizes );
    }
  }
  else if( iRatio > 0 ){ // if the width is bigger
    if( ( aSizes['iWindowWidth'] < aSizes['iImageWidth'] ) || ( aSizes['iWindowHeight'] < aSizes['iImageHeight'] ) ){
      aSizes['iImageWidth'] = aSizes['iWindowWidth'];
      aSizes['iImageHeight'] = Math.round( aSizes['iImageWidth'] * iRatio );
      // if the height is bigger than the screen height
      if( aSizes['iWindowHeight'] < aSizes['iImageHeight'] ){
        aSizes['iImageHeight'] = aSizes['iWindowHeight'];
        aSizes['iImageWidth'] = Math.round( aSizes['iImageHeight'] / iRatio );
      }
      changeImageSize( aSizes );
    }
  }
  return aSizes;
}

// function imposing image description on the image
function changeImageSize( aSizes ){
  $( '#quick-box .quick-box-container img' ).css({ height: aSizes['iImageHeight']+'px', width: aSizes['iImageWidth']+'px' });
  $( '#quick-box .description' ).css({ position: 'absolute', bottom: '0px' });
  var iDescHeight = parseInt( $( '#quick-box .description' ).height() ) + 10;
  $( '#quick-box .image-wrapper .navigation' ).css( 'bottom', iDescHeight+'px' );
}

// function sets the container and image size
function changePositions(){
  var iContainerHeight = null,
      iTopPos = null,
      iLeftPos = null, 
      aSizes = { 'iWindowHeight' : $( window ).height(), 'iWindowWidth': $( window ).width(), 'iImageHeight' : $( '#quick-box .quick-box-container img' ).height(), 'iImageWidth' : $( '#quick-box .quick-box-container img' ).width() };

  // calling the function to change the image size
  aSizes = resizeImage( aSizes );
  $( '#quick-box .description' ).css({ width: aSizes['iImageWidth']+'px' });

  iContainerHeight = $( '#quick-box .quick-box-container' ).height();
  iTopPos = Math.round((aSizes['iWindowHeight']-aSizes['iImageHeight'])/2)+'px';
  iLeftPos = Math.round((aSizes['iWindowWidth']-aSizes['iImageWidth'])/2)+'px';        
  
  // setting the appropriate image container position
  $( '#quick-box .quick-box-container' ).css({ top: iTopPos, left: iLeftPos });
}

// updating image viewer controls
function updateControls(){
  $('#quick-box .close').show();
  ( iCurrentImage == 0 || sQuickGallery == 'single' ) ? $('#quick-box .prev').hide() : $('#quick-box .prev').show();
  ( iCurrentImage == iAllImages - 1 || sQuickGallery == 'single' ) ? $('#quick-box .next').hide() : $('#quick-box .next').show();
  ( $( '#quick-box .navigation' ).length ) ? $( '#quick-box .navigation' ).remove() : null;
  ( iAllImages > 1 && sQuickGallery != 'single' ) ? $( '#quick-box .image-wrapper' ).append( '<p class="navigation">'+( iCurrentImage + 1 )+'/'+( iAllImages )+'</p>' ) : null;
}

// image changing
function changeImage( sDirection, bSwipe ){
  // image backward
  if( sDirection == 'prev' && ( iCurrentImage - 1 )  > -1 ){
    iCurrentImage-=1;
    var bChange = true;
  }
  // image forward
  if( sDirection == 'next' && ( iCurrentImage + 1 ) < iAllImages ){
    iCurrentImage+=1;
    var bChange = true;
  }

  // if the image can be changed
  if( bChange ){
    loadImagesWithDetail();
    bChange = null;
  }
}

// main function of the script
function quickboxInitialize( oObj, sClass, iStartImage ){
  var bArrows,
      bClose,
      bContent = ( typeof $( oObj ).attr( 'data-quickbox-msg' ) != 'undefined' ) ? true : false;
  if( !( $( '#quick-box' ).length ) ){
    $( 'body' ).append( '<div id="quick-box" onkeypress="keyUpHandler" ><div class="background"></div><div class="quick-box-container"><div class="image-wrapper"></div></div></div>' );
    
    // setting variables to adequate values
    sQuickGallery = sClass;
    iCurrentImage = iStartImage;
    iAllImages = oQuickbox[sQuickGallery].length;
    // adding controls in the right places
    if( typeof sNavOutside == 'string' ){
      if( sNavOutside == 'arrows' )
        bArrows = true;
      if( sNavOutside == 'close' )
        bClose = true;
      if( sNavOutside == 'all' )
        bArrows = bClose = true;
    }
    $( '#quick-box '+( ( typeof bClose != 'undefined' ) ? '' : '.image-wrapper' ) ).prepend( '<a href="#" class="close" aria-label="close">x</a>' );
    $( '#quick-box '+( ( typeof bArrows != 'undefined' ) ? '' : '.image-wrapper' ) ).prepend( '<a href="#" class="prev"><span>&#60;</span></a><a href="#" class="next"><span>&#62;</span></a>' );

    bContent === true ? loadContent( $( oObj ).attr( 'data-quickbox-msg' ) ): loadImagesWithDetail();

    // appropriate action to events triggered by the user
    // changing image size in window
    $(window).resize(function(){if( bContent === true ){changeContentPosition();}else{$( '#quick-box .quick-box-container img' ).css( { height: 'auto', width: 'auto' } );changePositions();}});


    $( '#quick-box .background, #quick-box .close' ).click(function(e){e.preventDefault();$('#quick-box').remove();document.onkeyup = null;});

    $( '#quick-box .prev' ).click(function(e){changeImage( 'prev' );e.preventDefault();});
    $( '#quick-box .next' ).click(function(e){changeImage( 'next' );e.preventDefault();});
    if( $( '#quick-box' ) ) document.onkeyup = keyUpHandler;
  }
}

$(document).ready(function(){
  // cache function calling after the page loaded 
  getQuickboxCache();
});

function var_dump( oObj ){
  var sOut = '';
  for( var i in oObj ){
    sOut += i + ": " + oObj[i] + "\n";
  } // end for
  console.log( sOut );
  //var oPre = document.createElement( 'pre' );
  //oPre.innerHTML = sOut;
  //document.body.appendChild( oPre );
} // end function var_dump

function var_print( sOut ){
  //console.log( oObj );
  var oPre = document.createElement( 'pre' );
  oPre.innerHTML = sOut;
  document.body.appendChild( oPre );
} // end function var_dump

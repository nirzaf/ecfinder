/*
* Common Admin JS scripts
*/

function redirectToUrl( sUrl ){
  window.location = sUrl;
}

function displayTab( sBlock ){
  if( sBlock && gEBI( sBlock ) ){
    for( var i = 0; i < aTabsId.length; i++ ){
      gEBI( aTabsId[i] ).style.display = 'none';
    } // end for

    gEBI( sBlock ).style.display = 'block';
    createCookie( 'sSelectedTab', sBlock, 2 );

    var aLi = gEBI( 'tabsNames' ).getElementsByTagName( 'li' );
    for( var i = 0; i < aLi.length; i++ ){
      removeClassName( aLi[i].getElementsByTagName( 'a' )[0], 'selected' );
      if( aLi[i].className == sBlock )
        addClassName( aLi[i].getElementsByTagName( 'a' )[0], 'selected' );
    } // end for

    gEBI( 'tabs' ).className = '';
    if( sBlock != 'tabOptions' )
      gEBI( 'tabs' ).className = 'extended';
  }
}


function checkSelectedTab( ){
  if( isset( 'bDone' ) && bDone === true ){
    var sSelectedName = throwCookie( 'sSelectedTab' );
    if( sSelectedName && sSelectedName != '' ){
      if( sSelectedName == 'tabAddFiles' )
        sSelectedName = 'tabAddedFiles';
      displayTab( sSelectedName, null );
    }
    else
      displayTab( aTabsId[0] );
  }
  else{
    displayTab( aTabsId[0] );
    delCookie( 'sSelectedTab' );
  }
}


var aTabsId = Array( );
function getTabsArray( ){
  if( typeof document.getElementsByClassName == 'function' ){
    var aTabs = gEBI( 'tabs' ).getElementsByClassName( 'tab' );
    for( var i = 0; i < aTabs.length; i++ ){
      aTabsId[aTabsId.length] = aTabs[i].getAttribute( 'id' );
    } // end for
  }
  else{
    var aTabs = gEBI( 'tabs' ).getElementsByTagName( '*' );
    for( var i = 0; i < aTabs.length; i++ ){
      if( aTabs[i].className == 'tab' )
        aTabsId[aTabsId.length] = aTabs[i].getAttribute( 'id' );
    } // end for
  }
}

function displayTabs( bShow ){
  if( bShow == true ){
    gEBI( "tabs" ).style.display = "block";
    gEBI( "tabsHide" ).style.display = "inline";
    gEBI( "tabsShow" ).style.display = "none";
  }
  else{
    gEBI( "tabs" ).style.display = "none";
    gEBI( "tabsHide" ).style.display = "none";
    gEBI( "tabsShow" ).style.display = "inline";
  }
}

function checkType( ){
  if( gEBI( 'oPageParent' ).value == "" ){
    gEBI( "type" ).style.display = "";
  }
  else
    gEBI( "type" ).style.display = "none";
}

function Browser() {
  var ua, s, i;
  this.isIE    = false;  // Internet Explorer
  this.isOP    = false;  // Opera
  this.isNS    = false;  // Netscape
  this.version = null;
  ua = navigator.userAgent;
  s = "Opera";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isOP = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }
  s = "Netscape6/";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }
  s = "Gecko";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = 6.1;
    return;
  }
  s = "MSIE";
  if ((i = ua.indexOf(s))) {
    this.isIE = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }
}

var browser = new Browser();
var activeButton = null;
if (browser.isIE)
  document.onmousedown = pageMousedown;
else
  document.addEventListener("mousedown", pageMousedown, true);

function pageMousedown(event) {
  var el;
  if (activeButton == null)
    return;
  if (browser.isIE)
    el = window.event.srcElement;
  else
    el = (event.target.tagName ? event.target : event.target.parentNode);
  if (el == activeButton)
    return;
  if (getContainerWith(el, "DIV", "menu") == null) {
    resetButton(activeButton);
    activeButton = null;
  }
}

var sLastSubMenuId = null;
function resetAllSubMenus( menuId ){
  if( !sLastSubMenuId || sLastSubMenuId != menuId )
    sLastSubMenuId = menuId;
  if( typeof document.getElementsByClassName == 'function' ){
    var aEl = gEBI( 'header' ).getElementsByClassName( 'menu' );
    for( var i = 0; i < aEl.length; i++ ){
      if( aEl[i].getAttribute( 'id' ) != sLastSubMenuId )
        aEl[i].style.visibility = "hidden";
    } // end for
  }
  else{
    var aEl = gEBI( 'header' ).getElementsByTagName( '*' );
    for( var i = 0; i < aEl.length; i++ ){
      if( aEl[i].className == 'menu' && aEl[i].getAttribute( 'id' ) != sLastSubMenuId ){
        aEl[i].style.visibility = "hidden";
      }
    } // end for
  }
}

function buttonClick(event, menuId) {
  resetAllSubMenus( menuId );
  var button;
  if (browser.isIE)
    button = window.event.srcElement;
  else
    button = event.currentTarget;
  button.blur();
  if (button.menu == null) {
    button.menu = document.getElementById(menuId);
    if (button.menu.isInitialized == null)
      menuInit(button.menu);
  }
  if (activeButton != null && button != activeButton)
    resetButton(activeButton);
  if (button != activeButton) {
    depressButton(button);
    activeButton = button;
  }
  else
    activeButton = null;
  return false;
}

function buttonMouseover(event, menuId) {
  var button;
  if (browser.isIE)
    button = window.event.srcElement;
  else
    button = event.currentTarget;
  if (activeButton != null && activeButton != button)
    buttonClick(event, menuId);
}

function depressButton(button) {
  var x, y;
  button.className += " menuButtonActive";
  x = getPageOffsetLeft(button);
  y = getPageOffsetTop(button) + button.offsetHeight;
  if (browser.isIE) {
    x += button.offsetParent.clientLeft;
    y += button.offsetParent.clientTop;
  }
  button.menu.style.left = x + "px";
  button.menu.style.top  = y + "px";
  button.menu.style.visibility = "visible";
}

function resetButton(button) {
  removeClassName(button, "menuButtonActive");
  if (button.menu != null) {
    closeSubMenu(button.menu);
    button.menu.style.visibility = "hidden";
  }
}

function menuMouseover(event) {
  var menu;
  if (browser.isIE)
    menu = getContainerWith(window.event.srcElement, "DIV", "menu");
  else
    menu = event.currentTarget;
  if (menu.activeItem != null)
    closeSubMenu(menu);
}

function menuItemMouseover(event, menuId) {
  var item, menu, x, y;
  if (browser.isIE)
    item = getContainerWith(window.event.srcElement, "A", "menuItem");
  else
    item = event.currentTarget;
  menu = getContainerWith(item, "DIV", "menu");
  if (menu.activeItem != null)
    closeSubMenu(menu);
  menu.activeItem = item;
  item.className += " menuItemHighlight";
  if (item.subMenu == null) {
    item.subMenu = document.getElementById(menuId);
    if (item.subMenu.isInitialized == null)
      menuInit(item.subMenu);
  }
  x = getPageOffsetLeft(item) + item.offsetWidth;
  y = getPageOffsetTop(item);
  var maxX, maxY;
  if (browser.isIE) {
    maxX = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft) +
      (document.documentElement.clientWidth != 0 ? document.documentElement.clientWidth : document.body.clientWidth);
    maxY = Math.max(document.documentElement.scrollTop, document.body.scrollTop) +
      (document.documentElement.clientHeight != 0 ? document.documentElement.clientHeight : document.body.clientHeight);
  }
  if (browser.isOP) {
    maxX = document.documentElement.scrollLeft + window.innerWidth;
    maxY = document.documentElement.scrollTop  + window.innerHeight;
  }
  if (browser.isNS) {
    maxX = window.scrollX + window.innerWidth;
    maxY = window.scrollY + window.innerHeight;
  }
  maxX -= item.subMenu.offsetWidth;
  maxY -= item.subMenu.offsetHeight;
  if (x > maxX)
    x = Math.max(0, x - item.offsetWidth - item.subMenu.offsetWidth
      + (menu.offsetWidth - item.offsetWidth));
  y = Math.max(0, Math.min(y, maxY));
  item.subMenu.style.left = x + "px";
  item.subMenu.style.top  = y + "px";
  item.subMenu.style.visibility = "visible";
  if (browser.isIE)
    window.event.cancelBubble = true;
  else
    event.stopPropagation();
}

function closeSubMenu(menu) {
  if (menu == null || menu.activeItem == null)
    return;
  if (menu.activeItem.subMenu != null) {
    closeSubMenu(menu.activeItem.subMenu);
    menu.activeItem.subMenu.style.visibility = "hidden";
    menu.activeItem.subMenu = null;
  }
  removeClassName(menu.activeItem, "menuItemHighlight");
  menu.activeItem = null;
}

function menuInit(menu) {
  var itemList, spanList;
  var textEl, arrowEl;
  var itemWidth;
  var w, dw;
  var i, j;
  if (browser.isIE) {
    menu.style.lineHeight = "2.5ex";
    spanList = menu.getElementsByTagName("SPAN");
    for (i = 0; i < spanList.length; i++)
      if (hasClassName(spanList[i], "menuItemArrow")) {
        spanList[i].style.fontFamily = "Webdings";
        spanList[i].firstChild.nodeValue = "4";
      }
  }
  itemList = menu.getElementsByTagName("A");
  if (itemList.length > 0)
    itemWidth = itemList[0].offsetWidth;
  else
    return;
  for (i = 0; i < itemList.length; i++) {
    spanList = itemList[i].getElementsByTagName("SPAN");
    textEl  = null;
    arrowEl = null;
    for (j = 0; j < spanList.length; j++) {
      if (hasClassName(spanList[j], "menuItemText"))
        textEl = spanList[j];
      if (hasClassName(spanList[j], "menuItemArrow")) {
        arrowEl = spanList[j];
      }
    }
    if (textEl != null && arrowEl != null) {
      textEl.style.paddingRight = (itemWidth 
        - (textEl.offsetWidth + arrowEl.offsetWidth)) + "px";
      if (browser.isOP)
        arrowEl.style.marginRight = "0px";
    }
  }
  if (browser.isIE) {
    w = itemList[0].offsetWidth;
    itemList[0].style.width = w + "px";
    dw = itemList[0].offsetWidth - w;
    w -= dw;
    itemList[0].style.width = w + "px";
  }
  menu.isInitialized = true;
}

function getContainerWith(node, tagName, className) {
  while (node != null) {
    if (node.tagName != null && node.tagName == tagName &&
        hasClassName(node, className))
      return node;
    node = node.parentNode;
  }
  return node;
}

function hasClassName(el, name) {
  var i, list;
  list = el.className.split(" ");
  for (i = 0; i < list.length; i++)
    if (list[i] == name)
      return true;
  return false;
}

function removeClassName(el, name) {
  var i, curList, newList;
  if (el.className == null)
    return;
  newList = new Array();
  curList = el.className.split(" ");
  for (i = 0; i < curList.length; i++)
    if (curList[i] != name)
      newList.push(curList[i]);
  el.className = newList.join(" ");
}

function getPageOffsetLeft(el) {
  var x;
  x = el.offsetLeft;
  if (el.offsetParent != null)
    x += getPageOffsetLeft(el.offsetParent);
  return x;
}

function getPageOffsetTop(el) {
  var y;
  y = el.offsetTop;
  if (el.offsetParent != null)
    y += getPageOffsetTop(el.offsetParent);
  return y;
}

function del( sInfo ){
  if( !sInfo )
    sInfo = '';
  if( confirm( delShure+sInfo+' ?' ) ) 
    return true;
  else 
    return false
}

function sure( ){
  if( confirm( confirmShure ) ) 
    return true;
  else 
    return false
}

function delConfirm( iId ){
  if( sCurrentElementName )
    var sInfo = ': "'+sCurrentElementName+'"';
  if( !bDeleteUnusedFiles || bDeleteUnusedFiles != '1' ){
    return del( sInfo );
  }
  else{
    var oCancel = new LertButton(Cancel, function() {
      //do nothing
    });
    var oButton0 = new LertButton(aDelTxt[0], function() {
      window.location.href = aDelUrl[0] + iId;
    });
    var oButton1 = new LertButton(aDelTxt[1], function() {
      window.location.href = aDelUrl[1] + iId;
    });
    var message = '<strong>'+delShure+sInfo+' ?</strong>';
    var delConfirmLert = new Lert(
      message,
      [oButton0,oButton1,oCancel],
      {
        defaultButton:oButton0,
        icon:'templates/admin/img/dialog-warning.png'
      });
    delConfirmLert.display();
  }
  return false;
}

function firstNotice( ){
  var sCookieName = 'bLicense'+sVersion.replace('.','');
  var bFirstNotice = throwCookie( sCookieName );
  if( !bFirstNotice ){
    var oClose = new LertButton(Close, function() {
      createCookie( sCookieName, true, 180 );
    });
    var licenseNotice = new Lert(
      sFirstNotice,
      [oClose],
      {
        defaultButton:oClose,
        icon:'templates/admin/img/dialog-warning.png'
      });
    licenseNotice.display();
    gEBI('lertWindow').setAttribute('class','lert-first-notice');
  }
}

function cursor( ){
  if( document.form.sLogin.value == "" ){
    document.form.sLogin.focus( );
  }
  else{
    document.form.sPass.focus( );        
  }
}

var sCurrentElementName = null;
function showPreviewButton( oObj ){
  oObj.getElementsByTagName( 'a' )[1].className = '';
  var oEl = oObj.getElementsByTagName( 'a' )[0];
  if( oEl.innerText )
    sCurrentElementName = oEl.innerText;
  else
    sCurrentElementName = oEl.innerHTML.replace( /\&lt;br\&gt;/gi,"\n").replace(/(&lt;([^&gt;]+)&gt;)/gi, "" );
}

function hidePreviewButton( oObj ){
  oObj.getElementsByTagName( 'a' )[1].className = 'preview';
  sCurrentElementName = null;
}

function listTableSearch( sPhrase, sTableId, iCell ) {
	var aPhrases = sPhrase.value.toLowerCase().split(" ");
  var oTable = gEBI( sTableId ).tBodies[0];
  var sDisplay = null;
	for( var i = 0; i < oTable.rows.length; i++ ){
		sDisplay = '';
		for( var j = 0; j < aPhrases.length; j++ ){
			if( oTable.rows[i].cells[iCell].innerHTML.replace( /<[^>]+>/g, '' ).toLowerCase().indexOf( aPhrases[j] ) < 0 )
				sDisplay = 'none';
			oTable.rows[i].style.display = sDisplay;
		}
	}
}

function displayFilesDirHead( iFile, iPhoto ){
  //
  var aTh = gEBI( 'files-dir-head-tr' ).getElementsByTagName( 'th' );
  for( var i = 0; i < aTh.length; i++ ){
    removeClassName( aTh[i], 'hidden' );
  } // end for
  var aTd = gEBI( 'fileTr'+iFile ).getElementsByTagName( 'td' );
  for( var i = 0; i < aTd.length; i++ ){
    if( aTd[i].className == 'position' ){
      aTd[i].innerHTML = '<input type="text" name="aDirFilesPositions['+iFile+']" value="0" maxlength="3" class="inputr" />';
    }
    else if( aTd[i].className == 'description' ){
      aTd[i].innerHTML = '<input type="text" name="aDirFilesDescriptions['+iFile+']" class="input" />';
    }
    else if( aTd[i].className == 'place' && iPhoto == 1 ){
      aTd[i].innerHTML = '<select name="aDirFilesTypes['+iFile+']" onclick="rememberLastOption( this )" onchange="extNotice( this )">'+sPhotoTypesSelect+'</select>';
    }
    else if( aTd[i].className == 'thumb1' && iPhoto == 1 ){
      aTd[i].innerHTML = '<select name="aDirFilesSizes1['+iFile+']">'+sSize1Select+'</select>';
    }
    else if( aTd[i].className == 'thumb2' && iPhoto == 1 ){
      aTd[i].innerHTML = '<select name="aDirFilesSizes2['+iFile+']">'+sSize2Select+'</select>';
    }
  } // end for
}

var oXmlHttp = new XMLHttpRequest();

function refreshFiles(){
  gEBI( 'filesFromDirList' ).innerHTML = '<img src="templates/admin/img/loading.gif" alt="Loading..." class="loading" />';
  if( oXmlHttp.readyState == 4 || oXmlHttp.readyState == 0 ){
    oXmlHttp.open( "GET", sPhpSelf+"?p=files-in-dir", true );
    oXmlHttp.onreadystatechange = function handleServerResponse(){
      if( oXmlHttp.readyState == 4 && oXmlHttp.status == 200 ){
        gEBI( 'filesFromDirList' ).innerHTML = oXmlHttp.responseText;
        handleCheckedFiles();
      }
    }
    oXmlHttp.send( null );
  }
  else
    setTimeout( 'refreshFiles()', 1000 );
	gEBI( 'attachingFilesInfo' ).style.display = 'block';
}

function handleCheckedFiles(){
  var aTr = gEBI( 'files-dir-table' ).getElementsByTagName( 'tr' );
  for( var i = 0; i < aTr.length; i++ ){
    var oBox = aTr[i].getElementsByTagName( 'input' )[0];
    if( oBox.checked == true )
      oBox.onclick();
    else
      break;
  } // end for
}

var bHideShortDescription = throwCookie( 'bHideShortDescription' );
function displayShortDescription( bClick ){
  if( gEBI( 'shortDescription' ).style.display == 'table-row' ){
    gEBI( 'shortDescription' ).style.display = 'none';
    gEBI( 'displaySD' ).style.display = 'inline';
    gEBI( 'hideSD' ).style.display = 'none';
    if( bClick )
      createCookie( 'bHideShortDescription', 1 );
  }
  else if( bClick || bHideShortDescription != 1 ){
    gEBI( 'shortDescription' ).style.display = 'table-row';
    gEBI( 'displaySD' ).style.display = 'none';
    gEBI( 'hideSD' ).style.display = 'inline';
    if( bClick )
      delCookie( 'bHideShortDescription' );
  }
}

var aSelectCache = Array();
var aSelectCacheMap = Array();
var aSelectCacheAttr = Array();
function cacheSelect( sId, sClone, sCloneCnt ){
  aSelectCache[sId] = Array();
  aSelectCacheMap[sId] = Array();
  aSelectCacheAttr[sId] = Array();
  var oSelect = gEBI( sId );
  for( var i = 0; i < oSelect.options.length; i++ ){
    aSelectCache[sId][oSelect.options[i].value] = oSelect.options[i].innerHTML;
    aSelectCacheMap[sId][i] = oSelect.options[i].value;
  } // end for
  aSelectCacheAttr[sId]['name'] = oSelect.name;
  aSelectCacheAttr[sId]['size'] = oSelect.size;

  gEBI( sCloneCnt ).innerHTML = gEBI( sId ).parentNode.innerHTML;
  gEBI( sCloneCnt ).children[0].id = sClone;
  gEBI( sClone ).name = null;
  gEBI( sClone ).title = null;
}

function listOptionsSearch( sPhrase, sSelectId, sClone ) {
	var aPhrases = sPhrase.value.toLowerCase().split(" ");
  var aSelect = aSelectCache[sSelectId];
  var aHide = Array();
	for( iId in aSelect ){
		aHide[iId] = false;
		for( var j = 0; j < aPhrases.length; j++ ){
			if( aSelect[iId].replace( /^(&nbsp;)+/g, '' ).toLowerCase().indexOf( aPhrases[j] ) < 0 )
    		aHide[iId] = true;
		}
	} // end for
  var iId = null;
  oParent = gEBI( sSelectId ).parentNode;
  oParent.innerHTML = gEBI( sClone ).parentNode.innerHTML;
  oParent.children[0].id = sSelectId;
  oParent.children[0].size = aSelectCacheAttr[sSelectId]['size'];
  oParent.children[0].name = aSelectCacheAttr[sSelectId]['name'];
  var oObj = gEBI( sSelectId );
  var oClone = gEBI( sClone );
	for( var i = aSelectCacheMap[sSelectId].length-1; i >= 0; i-- ){
    iId = aSelectCacheMap[sSelectId][i];
    if( aHide[iId] && aHide[iId] === true && oClone.options[i].selected != true ){
      oObj.remove( i );
    }
	} // end for
  cloneClick( oClone, sSelectId );
} 

function cloneClick( oObj, iIdClone ){
  var aSelected = Array();
  for( var i = 0; i < oObj.options.length; i++ ){
    if( oObj.options[i].selected == true )
      aSelected[oObj.options[i].value] = true;
  } // end for
  var oClone = gEBI( iIdClone );
  for( var i = 0; i < oClone.options.length; i++ ){
    if( aSelected[oClone.options[i].value] )
      oClone.options[i].selected = true;
    else
      oClone.options[i].selected = false;
  } // end for
} 

function changeInputStatus( oObj, sId ){
  if( oObj.checked )
    gEBI( sId ).className = 'inputr';
  else
    gEBI( sId ).className = 'inputr inputrd';
}

function checkProductsNamesWidth( ){
  var oTable = gEBI( 'list' ).tBodies[0];
	var j = 1;
  var oA = null;
	for( var i = 0; i < oTable.rows.length; i++ ){
    oA = oTable.rows[i].cells[j].children[0];
    if( oA.innerHTML.length > iMaxNameLength ){
      oA.onmouseover = ( function(oObj,sTxt){ return function(){showFullName(oObj,sTxt);} } )( oA, oA.innerHTML );
      oA.innerHTML = oA.innerHTML.substring( 0, iMaxNameLength-3 )+'...';
      oA.onmouseout = ( function(oObj,sTxt){ return function(){hideFullName(oObj,sTxt);} } )( oA, oA.innerHTML );
      oA.setAttribute( 'style', 'display:inline-block;width:'+oA.offsetWidth+'px;' );
    }
  } // end for
  oTable.rows[1].cells[1].setAttribute( 'style', 'width:'+oTable.rows[1].cells[1].offsetWidth+'px;' );
}

function showFullName( oObj, sValue ){
  oObj.innerHTML = sValue;
}

function hideFullName( oObj, sValue ){
  oObj.innerHTML = sValue;
}

var iSubpagesShowLast;
function extNotice( oObj, aTxt ){
  if( ( oObj.name == 'iSubpagesShow' && ( oObj.value == 4 || oObj.value == 5 ) ) || ( oObj.name != 'iSubpagesShow' && ( oObj.value == 3 || oObj.value == 0 ) ) ){
    var oCancel = new LertButton(Cancel, function() {
      //do nothing
    });
    var message = '<strong>'+sExtNotice+'<br /><br /><a href="http://demo.opensolution.org/Quick.Cart.Ext/" target="_blank">'+sExtDemo+'</a></strong>';
    var extNoticeLert = new Lert(
      message,
      [oCancel],
      {
        defaultButton:oCancel,
        icon:'templates/admin/img/ico_help.png'
      });
    extNoticeLert.display();
    if( isset( 'iSubpagesShowLast' ) ){
      oObj.selectedIndex = iSubpagesShowLast;
      iSubpagesShowLast = null;
    }
  }
}

function rememberLastOption( oObj ){
  if( oObj.value == 0 || oObj.value == 1 || oObj.value == 2 || oObj.value == 3 )
    iSubpagesShowLast = oObj.selectedIndex;
}

/* PLUGINS */
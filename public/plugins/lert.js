/*
* Lert v1.0
* by Jeffrey Sambells - http://JeffreySambells.com
* For more information on this script, visit http://JeffreySambells.com/openprojects/lert/
* Licensed under the Creative Commons Attribution 2.5 License - http://creativecommons.org/licenses/by/2.5/
* Icons from Tango Desktop Project http://tango.freedesktop.org/Tango_Desktop_Project
*/
function Lert(message, buttons, options) {
this.message_ = message;
this.buttons_ = buttons;
this.defaultButton_ = options.defaultButton || this.buttons_[0];
this.icon_ = options.icon || null;
}

Lert.prototype.display = function() {
var body = document.getElementsByTagName ('BODY')[0];
var pageScroll = getPageScroll();
var pageSize = getPageSize();

var overlay = document.getElementById('lertOverlay');
if(!overlay) {
var overlay = document.createElement("div");
overlay.setAttribute('id','lertOverlay');
overlay.style.display = 'none';
body.appendChild(overlay);
}

overlay.style.height=pageSize[1]+'px';
overlay.style.display='block';

var container = document.getElementById('lertContainer');
if(!container) {
var container = document.createElement("div");
container.setAttribute('id','lertContainer');
container.style.display = 'none';
body.appendChild(container);
}

container.style.top = ( pageScroll[1] + (pageSize[3] / 3) ) + 'px';
container.style.display = 'block';

var win = document.createElement('div');
win.setAttribute('id','lertWindow');

if(this.icon_ != null) {
var icon = document.createElement('img');
icon.setAttribute('src',this.icon_);
icon.setAttribute('id','lertIcon');
icon.setAttribute('alt','');
win.appendChild(icon);
}

var message = document.createElement('p');
message.setAttribute('id','lertMessage');
message.innerHTML = this.message_;
win.appendChild(message);

var buttons = document.createElement('div');
buttons.setAttribute('id','lertButtons');

var oldKeyDown = document.onkeydown;

for(i in this.buttons_) {
var button = this.buttons_[i];
if(button.getDom) {
var domButton = button.getDom(function() {
container.style.display = 'none';
overlay.style.display = 'none';
document.onkeydown=oldKeyDown;
container.innerHTML = '';
button.onclick_;
},this.defaultButton_);
buttons.appendChild(domButton);
}
}
win.appendChild(buttons);

document.onkeydown = this.keyboardControls;

container.appendChild(win);
}

Lert.prototype.keyboardControls = function(e) {
if (e == null) { keycode = event.keyCode; }
else { keycode = e.which; }
if(keycode==13) { document.getElementById('lertDefaultButton').onclick(); }
}

function LertButton(label, event, options) {
this.label_ = label;
this.onclick_ = event;
this.eventClick = function() {};
}

LertButton.prototype.getDom = function(eventCleanup,defaultButton) {
var button = document.createElement('a');
button.setAttribute('href','javascript:void(0);');
button.className = 'lertButton';
if(this == defaultButton) button.setAttribute('id','lertDefaultButton');
button.innerHTML = this.label_;

var eventOnclick =  this.onclick_;
button.onclick = function() {
eventCleanup();
eventOnclick();
}
this.eventClick = button.onclick;
return button;
}

function getPageScroll(){

var yScroll;

if (self.pageYOffset) {
yScroll = self.pageYOffset;
} else if (document.documentElement && document.documentElement.scrollTop){
yScroll = document.documentElement.scrollTop;
} else if (document.body) {
yScroll = document.body.scrollTop;
}

arrayPageScroll = new Array('',yScroll)
return arrayPageScroll;
}

function getPageSize(){

var xScroll, yScroll;

if (window.innerHeight && window.scrollMaxY) {
xScroll = document.body.scrollWidth;
yScroll = window.innerHeight + window.scrollMaxY;
} else if (document.body.scrollHeight > document.body.offsetHeight){
xScroll = document.body.scrollWidth;
yScroll = document.body.scrollHeight;
} else {
xScroll = document.body.offsetWidth;
yScroll = document.body.offsetHeight;
}

var windowWidth, windowHeight;
if (self.innerHeight) {
windowWidth = self.innerWidth;
windowHeight = self.innerHeight;
} else if (document.documentElement && document.documentElement.clientHeight) {
windowWidth = document.documentElement.clientWidth;
windowHeight = document.documentElement.clientHeight;
} else if (document.body) {
windowWidth = document.body.clientWidth;
windowHeight = document.body.clientHeight;
}

if(yScroll < windowHeight){
pageHeight = windowHeight;
} else {
pageHeight = yScroll;
}

if(xScroll < windowWidth){
pageWidth = windowWidth;
} else {
pageWidth = xScroll;
}

arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight)
return arrayPageSize;
}

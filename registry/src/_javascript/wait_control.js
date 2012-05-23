/*
Copyright 2009 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*******************************************************************************/

// Wait Control Globals
// -----------------------------------------------------------------------------
var wcTimeout = null;
var wcPicIndex = 0;
var wcShowElapsedTime = true;
var wcStartTime;
var wcImagesRootPath = '';   // The path to the root of control images & icons/
                             // This can be set in script just before the waitControl div
							 // with a call to wcSetImagePath(path)
							
var aryPics = null;

// Initialisation Functions
// -----------------------------------------------------------------------------
function wcInit(imagePath)
{
	wcImagesRootPath = imagePath;

	aryPics = new Array(10);
	aryPics[0] = wcImagesRootPath + "running_1.png";
	aryPics[1] = wcImagesRootPath + "running_2.png";
	aryPics[2] = wcImagesRootPath + "running_3.png";
	aryPics[3] = wcImagesRootPath + "running_4.png";
	aryPics[4] = wcImagesRootPath + "running_5.png";
	aryPics[5] = wcImagesRootPath + "running_6.png";
	aryPics[6] = wcImagesRootPath + "running_7.png";
	aryPics[7] = wcImagesRootPath + "running_8.png";
	aryPics[8] = wcImagesRootPath + "running_9.png";
	aryPics[9] = wcImagesRootPath + "running_10.png";
	
	// Put the control on the page, but in a place where it's not visible.
	document.write('<div id="waitControl" style="display: block; position: absolute; z-index: 200; width: 380px; right: -100000px;"></div>');
	
	// Set up the animation pics, and get the browser to preload and cache the required images.
	var waitControl = getObject('waitControl');
	waitControl.innerHTML = wcGetInnerHTML();

}

// Wait Control Functions
// -----------------------------------------------------------------------------
function wcGetInnerHTML()
{
	var text = '';
	text += '<div id="waitControlCloseBar" style="position: absolute; z-index: 210;"><span onclick="wcDisposeWait()" style="cursor: pointer;" title="Hide">x</span></div>';
	text += '<div id="waitControlMessage" style="position: absolute; z-index: 210;">Please Wait...</div>';
	text += '<img id="waitControlProgressBack" style="position: absolute; z-index: 220;" src="' + wcImagesRootPath + 'bg_progress.png" alt="" />';
	text += '<img id="waitControlProgress" style="position: absolute; z-index: 230;" src="' + wcImagesRootPath + 'progress.png" alt="" />';
	
	var zindex = 240;
	for( var i=0; i < aryPics.length; i++ )
	{
		zindex++;
		text += '<div id="waitControlRunning_' + i + '" class="waitControlRunning" style="position: absolute; z-index:' + zindex + ';"><img src="' + aryPics[i] + '" alt="" /></div>';
	}	
	
	text += '<div id="waitControlTime" style="position: absolute; z-index: 210;"></div>';
	text += '<div id="waitControlAction" style="position: absolute; z-index: 210;"></div>';
	text += '<img id="waitControlBackground" src="' + wcImagesRootPath + 'bg.png" alt="" />';
	return text;
}

function wcRunWait()
{	
	var waitFPS = 30;
	
	var waitTime = getObject('waitControlTime');
	var wcWaitControlProgress = getObject('waitControlProgress');

	// Show the running bar animation.
	var frameIndex = wcPicIndex%aryPics.length;
	wcPicIndex++;
	var nextFrameIndex = wcPicIndex%aryPics.length;
	
	var thisFrame = getObject('waitControlRunning_' + frameIndex);
	var nextFrame = getObject('waitControlRunning_' + nextFrameIndex);
	
	thisFrame.style.visibility = "hidden";
	nextFrame.style.visibility = "visible";

	if( wcShowElapsedTime ){
		waitTime.innerHTML = "Elapsed Time: " + wcGetWaitTime();
	} else {
		waitTime.innerHTML = "";
	}

    wcTimeout = window.setTimeout('wcRunWait();', 1000/waitFPS);
}

function wcPleaseWait(showElapsedTime, action)
{
	wcStartTime = new Date().getTime();
	var waitControl = getObject('waitControl');
	waitControl.innerHTML = wcGetInnerHTML();

	if( action != null )
	{
		var wcWaitAction = getObject('waitControlAction');
		if( action.length > 60 )
		{
			wcWaitAction.innerHTML = action.substring(0, 60) + '...';
		}
		else
		{
			wcWaitAction.innerHTML = action;
		}
	}

	// Set the display mode so that offsetWidth
	// and height will have values.
	waitControl.style.visibility = 'hidden';
	waitControl.style.display = 'block';
		
	if( !showElapsedTime ){ 
		wcShowElapsedTime = false;
	}
	
	// Hide all of animation frames.
	var frame = null;
	for( var i=0; i < aryPics.length; i++ )
	{
		frame = getObject('waitControlRunning_' + i);
		frame.style.visibility = "hidden";
	}
	
	// Get the center of the doc as a % of the current size.
	// This way it will still be visible close to the centre if the window is resized.
	var docWidth = document.body.clientWidth;
	var docHeight = document.body.clientHeight;
	var cLeft = parseInt((docWidth - parseInt(waitControl.offsetWidth,10))*50/docWidth, 10);
	var cTop = 0;

	// Show it.
	displayObjectAt(waitControl, cLeft+'%', cTop+'px');
	
	// Scroll to the top of the screen to show the dialog.
	window.scrollTo(0,0);
		
	// Animate it...
	if( wcTimeout != null ){ window.clearTimeout(wcTimeout); }
	wcPicIndex = 0;
	wcRunWait();
	
	return true;
}

function wcGetWaitTime()
{
	var strOut = "";
	var timeNow = new Date().getTime();
	var intTotalSeconds = (timeNow - wcStartTime)/1000;
	
	var intSeconds = parseInt(intTotalSeconds%60, 10);
	var intMinutes = parseInt((intTotalSeconds/60)%60, 10);
	
	var strSeconds = String(intSeconds,10);
	if( strSeconds.length < 2 ){ strSeconds = "0"+strSeconds; }
	
	var strMinutes = String(intMinutes,10);
	if( strMinutes.length < 2 ){ strMinutes = "0"+strMinutes; }

	strOut = '00:' + strMinutes + ":" + strSeconds
	
	return strOut;
}

function wcDisposeWait()
{
	var waitControl = getObject('waitControl');
	displayObject(waitControl, 'none');
	waitControl.innerHTML = '';
	window.clearTimeout(wcTimeout);
}



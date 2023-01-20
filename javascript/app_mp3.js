//for reference  on this implementation check out  https://higuma.github.io/web-audio-recorder-js/
//webkitURL is deprecated but nevertheless
//this file is used on audio_recorder_mp3.php and requires WebAudioRecorder.min.js

URL = window.URL || window.webkitURL;

var gumStream; 						//stream from getUserMedia()
var recorder; 						//WebAudioRecorder object
var input; 							//MediaStreamAudioSourceNode  we'll be recording
var encodingType; 					//holds selected encoding for resulting audio (file)
var encodeAfterRecord = true;       // when to encode

// shim for AudioContext when it's not avb. 
var AudioContext = window.AudioContext || window.webkitAudioContext;
var audioContext; //new audio context to help us record

var encodingTypeSelect = document.getElementById("encodingTypeSelect");
var recordButton = document.getElementById("recordButton");
var stopButton = document.getElementById("stopButton");
//var mp3filename = document.getElementById("filename");
var mp3filename = document.getElementById("filename").value;


//add events to those 2 buttons
recordButton.addEventListener("click", startRecording);
stopButton.addEventListener("click", stopRecording);

function startRecording() {
	console.log("startRecording() called");
	document.getElementById('prep').style.display="inline";
	

	/*
		Simple constraints object, for more advanced features see
		https://addpipe.com/blog/audio-constraints-getusermedia/
	*/
    
    var constraints = { audio: true, video:false }

    /*
    	We're using the standard promise based getUserMedia() 
    	https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia
	*/

	navigator.mediaDevices.getUserMedia(constraints).then(function(stream) {
		__log("getUserMedia() success, stream created, initializing WebAudioRecorder...");

		/*
			create an audio context after getUserMedia is called
			sampleRate might change after getUserMedia is called, like it does on macOS when recording through AirPods
			the sampleRate defaults to the one set in your OS for your playback device

		*/
		audioContext = new AudioContext();

		//update the format 
		document.getElementById("formats").innerHTML="Format: 2 channel "+encodingTypeSelect.options[encodingTypeSelect.selectedIndex].value+" @ "+audioContext.sampleRate/1000+"kHz"

		//assign to gumStream for later use
		gumStream = stream;
		
		/* use the stream */
		input = audioContext.createMediaStreamSource(stream);
		
		//stop the input from playing back through the speakers
		//input.connect(audioContext.destination)

		//get the encoding 
		encodingType = encodingTypeSelect.options[encodingTypeSelect.selectedIndex].value;
		//var encodingType = "mp3"; 
		
		//disable the encoding selector
		//encodingTypeSelect.disabled = true;

		recorder = new WebAudioRecorder(input, {
		  workerDir: "javascript/", // must end with slash
		  encoding: encodingType,
		  numChannels:2, //2 is the default, mp3 encoding supports only 2
		  onEncoderLoading: function(recorder, encoding) {
		    // show "loading encoder..." display
		    __log("Loading "+encoding+" encoder...");
		  },
		  onEncoderLoaded: function(recorder, encoding) {
		    // hide "loading encoder..." display
		    __log(encoding+" encoder loaded");
		  }
		});

		recorder.onComplete = function(recorder, blob) { 
			__log("Encoding complete");
			createDownloadLink(blob,recorder.encoding);
			encodingTypeSelect.disabled = false;
		}

		recorder.setOptions({
		  timeLimit:30,
		  encodeAfterRecord:encodeAfterRecord,
	      ogg: {quality: 0.5},
	      mp3: {bitRate: 64}
	    });

		//start the recording process
		recorder.startRecording();

		 __log("Recording started");
		 document.getElementById('prep').style.display="none";
		 document.getElementById('mic').style.display="inline";
		 document.getElementById('stopButton').style.display="inline";
	 	document.getElementById('recordButton').style.display="none";


	}).catch(function(err) {
	  	//enable the record button if getUSerMedia() fails
    	recordButton.disabled = false;
    	stopButton.disabled = true;

	});

	//disable the record button
    recordButton.disabled = true;
    stopButton.disabled = false;
}

function stopRecording() {
	console.log("stopRecording() called");
	document.getElementById('mic').style.display="none";
	  document.getElementById('recordButton').style.display="none";
	  document.getElementById('stopButton').style.display="none";
	  	document.getElementById('prep').style.display="inline";


	//stop microphone access
	gumStream.getAudioTracks()[0].stop();

	//disable the stop button
	stopButton.disabled = true;
	recordButton.disabled = false;
	
	//tell the recorder to finish the recording (stop recording + encode the recorded audio)
	recorder.finishRecording();

	__log('Recording stopped');
}

function createDownloadLink(blob,encoding) {
	
	var url = URL.createObjectURL(blob);
	var au = document.createElement('audio');
	var li = document.createElement('li');
	var link = document.createElement('a');

	//add controls to the <audio> element
	au.controls = true;
	au.src = url;

	//link the a element to the blob
	link.href = url;
	link.download = new Date().toISOString() + '.'+encoding;
	link.innerHTML = link.download;

	//add the new audio and a elements to the li element
	li.appendChild(au);
	//li.appendChild(link);

	//add the li element to the ordered list
	recordingsList.appendChild(li);
	
	//Need to upload Blob and then show the continue button to the user
	
	uploadBlob(blob); 
	
		document.getElementById('prep').style.display="none";
	document.getElementById('continueButton').style.display="inline";
	document.getElementById('restart').style.display="inline";


}



//helper function
function __log(e, data) {
	log.innerHTML += "\n" + e + " " + (data || '');
}


//Dan's upload functions

function transferComplete(evt) 
{
  	console.log("The transfer is complete.");
  	document.getElementById('upload').style.display="none";
  	document.getElementById('mic').style.display="none";
	document.getElementById('stopButton').style.display="none";

}

function transferFailed(evt) 
{
  	console.log("An error occurred while transferring the file.");
}
                
function uploadBlob(blob)
{
	var xhr=new XMLHttpRequest();
                
    //If upload is in process, show or hide animations and buttons               
    document.getElementById('upload').style.display="inline";

    //Add event listeners for successful load or error             
	xhr.upload.addEventListener("load", transferComplete);
	xhr.upload.addEventListener("error", transferFailed);
				
                
    xhr.onload=function(e) {
                if(this.readyState === 4) {
                 console.log("Server returned: ",e.target.responseText);
                }
                };
                
    
                var fd=new FormData();
                fd.append("audio_data",blob, mp3filename);
                xhr.open("POST","audio_recorder_up_mp3.php",true);
                xhr.send(fd);

}







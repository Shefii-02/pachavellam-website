<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <video width="320" height="240" autoplay></video><br>
    <button id="shareScreen">Capture Screen</button>
    <button id="rec" disabled>Record</button>
    <button id="pause" disabled>pause</button>

    <button id="stop" disabled>Stop</button>
        <a id="downloadLink" download="mediarecorder.mp4" name="mediarecorder.mp4" href></a>

    <div class="label" id="#label">0</div>
    <div id="error"></div>




<script>
        var shareBtn = document.querySelector("button#shareScreen");
        shareBtn.onclick = shareScreen;

        var recBtn = document.querySelector("button#rec");
        recBtn.onclick = onBtnRecordClicked;

        var stopBtn = document.querySelector("button#stop");
        stopBtn.onclick = onBtnStopClicked;

        var pauseBtn = document.querySelector("button#pause");
        pauseBtn.onclick = onBtnPauseClicked;


        
        


        var videoElement = document.querySelector("video");
        videoElement.style.backgroundColor = "black";

        var downloadLink = document.querySelector("a#downloadLink");

        var mediaRecorder;
        var localStream = null;
        document.getElementById("error").innerHTML = "";

        function shareScreen(){
        console.log("shareScreen");
        document.getElementById("error").innerHTML = "";
        var screenConstraints = { video: true, audio: true };
        navigator.mediaDevices.getDisplayMedia(screenConstraints).then(function(screenStream){
            /* use the screen & audio stream */

            var micConstraints = {audio:true};
            navigator.mediaDevices.getUserMedia(micConstraints).then(function(micStream) {
            /* use the microphone stream */

            //create a new stream in which to pack everything together
            var composedStream = new MediaStream();

            //add the screen video stream
            screenStream.getVideoTracks().forEach(function(videoTrack) {
                composedStream.addTrack(videoTrack);
            });

            //create new Audio Context
            var context = new AudioContext();

            //create new MediaStream destination. This is were our final stream will be.
            var audioDestinationNode = context.createMediaStreamDestination();

            //check to see if we have a screen stream and only then add it
            if (screenStream && screenStream.getAudioTracks().length > 0) {
                //get the audio from the screen stream
                const systemSource = context.createMediaStreamSource(screenStream);

                //set it's volume (from 0.1 to 1.0)
                const systemGain = context.createGain();
                systemGain.gain.value = 1.0;

                //add it to the destination
                systemSource.connect(systemGain).connect(audioDestinationNode);

            }

            //check to see if we have a microphone stream and only then add it
            if (micStream && micStream.getAudioTracks().length > 0) {
                //get the audio from the microphone stream
                const micSource = context.createMediaStreamSource(micStream);

                //set it's volume
                const micGain = context.createGain();
                micGain.gain.value = 1.0;

                //add it to the destination
                micSource.connect(micGain).connect(audioDestinationNode);
            }

            //add the combined audio stream
            audioDestinationNode.stream.getAudioTracks().forEach(function(audioTrack) {
                composedStream.addTrack(audioTrack);
            });

            //pass over to function that shows the stream and activates the recording controls
            onCombinedStreamAvailable(composedStream)

            }).catch(function(err) {
                console.log(err);
                document.getElementById("error").innerHTML = "You need a microphone to run ";
            });
        }).catch(function(err) {
            console.log(err);
            document.getElementById("error").innerHTML = "You need to share your screen to run ";
        });
        }

        function onCombinedStreamAvailable(stream) {
        console.log("onCombinedStreamAvailable");
        localStream = stream;
        
        videoElement.srcObject = localStream;
        videoElement.play();
        videoElement.muted = true;
        recBtn.disabled = false;
        shareBtn.disabled = true;
        stopBtn.disabled = true;
        pauseBtn.disabled    = true;
        }




        function onBtnRecordClicked() {
        console.log("onBtnRecordClicked");
            if (localStream != null) {
                mediaRecorder = new MediaRecorder(localStream);
                mediaRecorder.onstop = function() {
                console.log("mediaRecorder.onstop");
                
                var blob = new Blob(chunks, { type: "video/mp4" });
                chunks = [];
                var videoURL = window.URL.createObjectURL(blob);

                downloadLink.href = videoURL;
                videoElement.src = videoURL;
                downloadLink.innerHTML = "Download video file";
                }
                
                let chunks = [];
                mediaRecorder.ondataavailable = function(e) {
                chunks.push(e.data);
                }

                mediaRecorder.start(2);
                console.log(mediaRecorder.state);
                
                recBtn.style.background = "red";
                recBtn.style.color = "black";
                
                recBtn.disabled = true;
                shareBtn.disabled = true;
                stopBtn.disabled = false;
                pauseBtn.disabled    = false; 

                resumeBtn()

            }else{
                console.log("localStream is missing");
            }

           
        }

        function onBtnPauseClicked(){
               
            
            
                if (mediaRecorder.state === "recording") {
                    mediaRecorder.pause();
                    pauseBtn.innerHTML = "Resume";
                    console.log("recording paused")
                    pause__Btn()
                } 
                else if (mediaRecorder.state === "paused") {
                    mediaRecorder.resume();
                    pauseBtn.innerHTML = "Pause";
                    console.log("resume recording")
                    resumeBtn()
                }

                recBtn.disabled = true;
                shareBtn.disabled = true;
                stopBtn.disabled = false;
                pauseBtn.disabled    = false;

            
        }

     


        function onBtnStopClicked(){
            mediaRecorder.stop();
            console.log(mediaRecorder.state);
            console.log("recorder stopped");
            recBtn.style.background = "";
            recBtn.style.color = "";
        }




  var delay = 1000;

  var label = document.getElementById("#label");
  var value = parseInt(label.textContent);


    var t = new timer(function () {
        label.textContent = ++value;
    }, delay);



    function resumeBtn(){
    t.resume();
    }

    function pause__Btn(){
    t.pause();
    }

    
    function resetBtn(){

        t.reset();
    }


function timer(callback, delay) {
  var timerId;
  var start;
  var remaining = delay;

  this.pause = function () {
    window.clearTimeout(timerId);
    remaining -= new Date() - start;
  };

  var resume = function () {
    start = new Date();
    timerId = window.setTimeout(function () {
      remaining = delay;
      resume();
      callback();
    }, remaining);
  };
  this.resume = resume;

  this.reset = function () {
    remaining = delay;
  };
}







</script>


</body>
</html>
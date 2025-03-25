<html lang="en"><head>

  <meta charset="UTF-8">
  <meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

  
  
  
<style>
:root {
  --container-width: 100%;
  --container-bg-color: #000;
  
  --stream-container-width: 100%; /* 100% or 900px */
  
  --chat-height: 500px;
  --chat-width: 300px;
}

div {
  box-sizing: border-box;
}

body {
  background-color: #111;
  font-family: "Microsoft JhengHei", Arial, 'LiHei Pro', Helvetica, sans-serif;
  margin: 0;
  padding: 20px 0;
}


#stream-container {
  background-color: var(--container-bg-color);
  min-height: var(--chat-height);
  width: var(--stream-container-width);
  max-width: 100%;
  margin: 0 auto;
}
#player {
  height: 100% !important;
  max-height: 100vh !important;
}
#stream-embed-wrapper {
  float: left;
  height: var(--chat-height);
  width: calc( 100% - var(--chat-width) );
}

#chat-embed-wrapper {
  float: right;
  height: var(--chat-height);
  width: var(--chat-width);
}

@media (max-width: 600px) {
  #stream-embed-wrapper {
    float: none;
    width: 100%;
    height:auto;
  }
  #chat-embed-wrapper {
    float: none;
    width: 100%;
  }
  #chat-embed-wrapper{
      display:block !important;
  }
#player{
    width:100% !important;
    height:auto !important;
}
  
}
.clear_both {
  clear: both;
}



</style>




</head>

<body>
<a href="">asdf</a>
    
<div id="stream-container">
   stream 
  <div id="stream-embed-wrapper">
  
      <div id="player"></div>
  </div>
 
   chat 
  <div id="chat-embed-wrapper"></div>
  
  <div class="clear_both"></div>
</div>


      <script >
const VIDEO_ID = "lj2eCs1Alr0"; // for live chat
/*
code from:
https://stackoverflow.com/questions/52468303/how-to-embed-youtube-livestream-chat
*/
let chat_embed_wrapper = document.getElementById("chat-embed-wrapper");  
let chat_embed_iframe = document.createElement("iframe");  
chat_embed_iframe.referrerPolicy = "origin";  
chat_embed_iframe.src = `https://www.youtube.com/live_chat?v=${VIDEO_ID}&embed_domain=${window.location.hostname}`;  
chat_embed_iframe.frameBorder = "0";  
chat_embed_iframe.id = "chat-embed-iframe";  
chat_embed_iframe.style.height = "100%";
chat_embed_iframe.style.width = "100%";
chat_embed_wrapper.appendChild(chat_embed_iframe);

let change_stream_embed_iframe_size = function(){
  let stream_container = document.getElementById("stream-container");
  let stream_embed_wrapper = document.getElementById("stream-embed-wrapper");
  let stream_embed_iframe = document.getElementById("player");
  
  if(typeof window.orientation !== 'undefined'){ //on phone
    chat_embed_wrapper.style.display = 'none';
    stream_embed_iframe.width = stream_container.clientWidth;
  }else{ // on pc (desktop browsers == 'undefined')
    stream_embed_iframe.width = stream_embed_wrapper.clientWidth;
  }
};
change_stream_embed_iframe_size();

window.onresize = function(event) {
  //console.log("[window.onresize]");
  change_stream_embed_iframe_size();
};
    </script>

  <script>
      
      // 2. This code loads the IFrame Player API code asynchronously.
      var tag = document.createElement('script');

      tag.src = "https://www.youtube.com/iframe_api";
      var firstScriptTag = document.getElementsByTagName('script')[0];
      firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

      // 3. This function creates an <iframe> (and YouTube player)
      //    after the API code downloads.
      var player;
      function onYouTubeIframeAPIReady() {
        player = new YT.Player('player', {
        //   height: '100%',
          width: '100%',
          videoId: 'lj2eCs1Alr0',
          playerVars: {
            'playsinline': 1
          },
          events: {
            'onReady': onPlayerReady,
            'onStateChange': onPlayerStateChange
          }
        });
      }

      // 4. The API will call this function when the video player is ready.
      function onPlayerReady(event) {
        event.target.playVideo();
      }

      // 5. The API calls this function when the player's state changes.
      //    The function indicates that when playing a video (state=1),
      //    the player should play for six seconds and then stop.
      var done = false;
      function onPlayerStateChange(event) {
        if (event.data == YT.PlayerState.PLAYING && !done) {
          setTimeout(stopVideo, 6000);
          done = true;
        }
      }
      
      function stopVideo() {
        player.stopVideo();
      }
    </script>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script>

                $(document).ready(function()
                {
                    setTimeout(function()
                    {
                        alert('o');
                         var element = document.getElementsByClassName('.ytp-youtube-button')[0];
                       
                        element.style.display = "none";
                    }, 
                    30000);
                });
  </script>
  
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script>
  $('a[href]').click(function(){

        /
              alert(1);
          });
  </script>


 
</body></html>
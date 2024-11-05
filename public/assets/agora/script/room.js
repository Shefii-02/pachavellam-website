// let messagesContainer = document.getElementById('messages__container')
// messagesContainer.scrollTop = messagesContainer.scrollHeight




import {App_ID} from './env.js'


let appID = App_ID
let token = null

let uid = sessionStorage.getItem('rtmUID')
if(uid === null || uid === undefined){  
    uid = String(Math.floor(Math.random() * 232))
    sessionStorage.setItem('rtmUID',uid)
}


let urlParams = new URLSearchParams(window.location.search)

let room = urlParams.get('room')

let displayName = sessionStorage.getItem('display_name')

let roomName = sessionStorage.getItem('room_name')
let myAvatar = sessionStorage.getItem('avatar')

let host;
let hostId;

if(room === null || displayName === null){
    window.location = `join.html?room=${room}`
}

// let room = 'default'


let initiate = async () => {

    let rtmClient = await AgoraRTM.createInstance(appID)
    await rtmClient.login({uid, token})

    try{
        let attributes = await rtmClient.getChannelAttributesByKeys(room, ['room_name','host_id'])
        roomName= attributes.room_name.value
        hostId = attributes.host_id.value

        if(uid === hostId){
            host = true
            document.getElementById('stream__controls').style.display = 'flex'
        }
    }
    catch(error){

            await rtmClient.setChannelAttributes(room, {'room_name' : roomName,'host':displayName,'host_image' : myAvatar,'host_id' : uid})
            host = true
            document.getElementById('stream__controls').style.display = 'flex'

        }

    const channel = await rtmClient.createChannel(room)
    await channel.join()

    



    let lobbyChannel = await rtmClient.createChannel('lobby')
    await lobbyChannel.join()

    lobbyChannel.on('MemberJoined', async(memberId) =>{
        
        let participants = await  channel.getMembers()
        if(participants[0] === uid){
            let lobbyMembers = await lobbyChannel.getMembers()
            for(let i=0; lobbyMembers.length > i; i++){
                rtmClient.sendMessageToPeer({text:JSON.stringify({'room':room,'type':'room_added'})},lobbyMembers[i]);
            }
        }
    })


    await rtmClient.addOrUpdateLocalUserAttributes({'name' :displayName})


    channel.on('MemberLeft', async (memberId) => {

        removeParticipantFromDom(memberId)

        let participants = await channel.getMembers()
        updateParticipantTotal(participants)
    })

    channel.on('MemberJoined', async(memberId) =>{
        addParticipantToDom(memberId)
    })

    channel.on('ChannelMessage', async (messageData, memberId) =>{

        let data = JSON.parse(messageData.text)
        let name = data.displayName
        let new_avatar = data.avatar
        addMessageToDom(data.message, memberId, name, new_avatar)

        
        let participants = await channel.getMembers()
        updateParticipantTotal(participants)

    })


    let addParticipantToDom = async(memberId) => {

        let {name} = await rtmClient.getUserAttributesByKeys(memberId, ['name'])



        let membersWrapper = document.getElementById('member__list')
        let messageItem =   `<div id='member__${memberId}__wrapper' class="member__wrapper">
                                <span class="green__icon"></span>
                                <p class="name_user">${name}</p>
                            </div>`
        membersWrapper.innerHTML += messageItem
    }

    let addMessageToDom = (messageData,memberId,display_name,avatar) => {
        
        let created = new Date().toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'})

        if(created.startsWith("0")){
            created = created.substring(1)
        }
        
        let messagesWarapper = document.getElementById('messages')
        let messageItem = `<div class="message-wrapper reverse">
            <div class="profile-picture">
              <img src="${avatar}" alt="pp">
            </div>
            <div class="message-content">
              <p class="name">${display_name}</p>
              <p class="time">${created}</p>
              <div class="message">${messageData}</div>
            </div>
          </div>`
        

        messagesWarapper.insertAdjacentHTML('beforeend',messageItem)

        // messagesContainer.scrollTop = messagesContainer.scrollHeight
        let lastMessage = document.querySelector('#messages .message-wrapper:last-child');
        lastMessage.scrollIntoView();
    }


    let sendMessage = async (e) => {
        e.preventDefault()
        let message = e.target.message.value
        channel.sendMessage({text:JSON.stringify({'message':message,'displayName':displayName,'avatar' : myAvatar})})

        addMessageToDom(message, uid,displayName,myAvatar)

        // let attributes = await rtmClient.getChannelAttributesByKeys(room,['lastMess'])
        // let messages =[]
        // await rtmClient.addOrUpdateChannelAttributes(room, {'messages' : roomName})

        e.target.reset()
    }

    let updateParticipantTotal = (participants) =>{
        let total = document.getElementById('member__count')
        total.innerText = participants.length
    }

    let getParticipants = async () => {
        let participants = await channel.getMembers()

        if(participants.length <= 1){
            let lobbyMembers = await lobbyChannel.getMembers()
            for(let i=0; lobbyMembers.length > i; i++){
                rtmClient.sendMessageToPeer({text:JSON.stringify({'room':room,'type':'room_added'})},lobbyMembers[i]);
            }
        }


        updateParticipantTotal(participants)

        for(let i=0; participants.length > i; i++){
            addParticipantToDom(participants[i])
        }
    }

    let removeParticipantFromDom = (memberId) => {
        document.getElementById(`member__${memberId}__wrapper`).remove()
    }

    let leaveChannel = async () => {
        await channel.leave()
        rtmClient.logout()
    }

    window.addEventListener('beforeunload', leaveChannel)

    getParticipants()

    let messageForm = document.getElementById('message__form')
    messageForm.addEventListener('submit', sendMessage)
}

//RTC Config

let rtcUid = Math.floor(Math.random() * 232)
let config = {
    appId : App_ID,
    token : null,
    uid : rtcUid,
    channel:room,
}

let localTracks = []
let localScreenTracks;

let rtcClient = AgoraRTC.createClient({mode:'live','codec':'vp8'})
let streaming = false;
let sharingScreen = false



let InitiateRtc = async () => {
     await rtcClient.join(config.appId, config.channel, config.token, config.uid)
  
    
     await rtcClient.on('user-unpublished', handleUSerLeft)
     await rtcClient.on('user-published', handleUSerPublished)

}

let toggleStream = async()=>{
    if(!streaming){
        streaming = true
        toggleVideoShare()
        document.getElementById('stream-btn').innerText = "Pause Stream"
    }
    else{
        streaming = false
        document.getElementById('stream-btn').innerText = "Play Stream"

        for(let i=0; localTracks.length > i; i++){
            localTracks[i].stop()
            localTracks[i].close()
        }

        await  rtcClient.unpublish([localTracks[0],localTracks[1]])


    }
}
 
let toggleVideoShare = async () => {
    await rtcClient.setClientRole('host')

    // localTracks = await AgoraRTC.createMicroPhoneAndCameraTracks()
    localTracks[0] = await AgoraRTC.createMicrophoneAudioTrack();
    localTracks[1] = await AgoraRTC.createCameraVideoTrack();

    document.getElementById('video__stream').innerHTML = ''


    let player =`<div class="video-controller" id="user-controller-${rtcUid}">
                    <div class="video-player" id="user-${rtcUid}"></div>
                </div>`

    document.getElementById('video__stream').insertAdjacentHTML('beforeend',player)
    
    localTracks[1].play(`user-${rtcUid}`)
    await rtcClient.publish([localTracks[0], localTracks[1]])
}


let handleUSerPublished = async (user, mediaType) =>{

    await rtcClient.subscribe(user, mediaType)

    if(mediaType === 'video'){
        let player = document.getElementById(`user-container-${rtcUid}`)
        if(player != null){
            player.remove()
        }
        player =`<div class="video-controller" id="user-controller-${rtcUid}">
                    <div class="video-player" id="user-${rtcUid}"></div>
                </div>`

        document.getElementById('video__stream').insertAdjacentHTML('beforeend',player)
        user.videoTrack.play(`user-${rtcUid}`)
    }
    if(mediaType === 'audio'){
        user.audioTrack.play()
    }

}



let handleUSerLeft = async (user) => {
    document.getElementById(`video__stream`).innerHTML =''
}
    



let toggleCamera = async (e) => {
    if(streaming){
        if(localTracks[1].muted){  
            localTracks[1].setMuted(false)
            e.target.style.backgroundColor = '#ffffff'
        }
        else{
            localTracks[1].setMuted(true)
            e.target.style.backgroundColor = '#EE4B2B'
        }
    }
    else{   
        alert('Stream not Started')
    }

}


let toggleMic = async (e) => {
   
    if(streaming){
        if(localTracks[0].muted){
            localTracks[0].setMuted(false)
            e.target.style.backgroundColor = '#ffffff'
        }
        else{
            localTracks[0].setMuted(true)
            e.target.style.backgroundColor = '#EE4B2B'
        }
    }
    else{   
        alert('Stream not Started')
    }
}


let toggleScreenShare = async()=>{
    if(sharingScreen){
       
        sharingScreen = false
        await rtcClient.unpublish([localScreenTracks])
        toggleVideoShare()
        document.getElementById('screen-btn').innerText = 'share screen'
    }
    else{
      
        sharingScreen = true

        document.getElementById('screen-btn').innerText = 'share camera'
        localScreenTracks = await AgoraRTC.createScreenVideoTrack()
        document.getElementById('video__stream').innerHTML = ""

        let player = document.getElementById(`user-container-${rtcUid}`)
        if(player != null){
            player.remove()
        }
        player =`<div class="video-controller" id="user-controller-${rtcUid}">
                    <div class="video-player" id="user-${rtcUid}"></div>
                </div>`

        document.getElementById('video__stream').insertAdjacentHTML('beforeend',player)
        localScreenTracks.play(`user-${rtcUid}`)
        await rtcClient.unpublish([localTracks[0],localTracks[1]])
        await rtcClient.publish([localScreenTracks])
    }
}


let leaveStream = async (e) => {
    window.location="/";
}


document.getElementById('mic-btn').addEventListener('click', toggleMic)
document.getElementById('camera-btn').addEventListener('click', toggleCamera)
document.getElementById('screen-btn').addEventListener('click', toggleScreenShare)
document.getElementById('stream-btn').addEventListener('click', toggleStream)
document.getElementById('stream-leave-btn').addEventListener('click', leaveStream)




initiate()
InitiateRtc()
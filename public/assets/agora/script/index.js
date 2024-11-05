


import {App_ID} from './env.js'


let appID = App_ID
let token = null
let uid = String(Math.floor(Math.random() * 1232))

let roomsData={}

let initiate = async () =>{
    let rtmClient = await AgoraRTM.createInstance(appID)
    await rtmClient.login({uid, token})
 

    let lobbyChannel = await rtmClient.createChannel('lobby')
    await lobbyChannel.join()

  



    rtmClient.on('MessageFromPeer', async (message,peerId) =>{
        let messageData = JSON.parse(message.text)
        let count = await rtmClient.getChannelMemberCount([messageData.room])
        
        roomsData[messageData.room] = {'members' : count}

        let rooms = document.getElementById("rooms_container")
        let room = document.getElementById(`room__${messageData.room}`)
        if(room === null){
            room = await buildRoom(count, messageData.room)
      
            rooms.insertAdjacentHTML('beforeend',room)
        }

    })

    let buildRoom =  async(count, room_id)=>{

        let attributes = await rtmClient.getChannelAttributesByKeys(room_id,['room_name','host','host_image'])
        let  roomName  = attributes.room_name.value
        let hostName = attributes.host.value
        let hostImage = attributes.host_image.value;

        let roomItem = `<div class='room_item' id="room__${room_id}">
                            <img src="${hostImage}" width="50px">
                            <span>Hosted : ${hostName} </span>
                            <h6>Room Name : ${roomName}</h6>
                            <span>${count} peoples are live</span>
                            <a href="join.html?room=${room_id}">Join Room</a>
                        </div>`
        return roomItem
    }


    let checkHeartBeat = async () => {

        for (let room_id in roomsData) {
            let count = await rtmClient.getChannelMemberCount([room_id])
            if(count[room_id] < 1){
                document.getElementById(`room__${room_id}`).remove()
                delete roomsData[room_id]
            }else{
                let newRoom; // = document.getElementById(`room__${room_id}`)
                let rooms =document.getElementById('room_container')
                newRoom = await buildRoom(count[room_id], room_id)
                // rooms.insertAdjacentHTML(newRoom)
                document.getElementById(`room__${room_id}`).innerHTML = newRoom

            }
            
        }
    }
    let interval = setInterval(()=>{
        checkHeartBeat()
    }, 2000)

    return () => clearInterval(interval)

}


initiate()
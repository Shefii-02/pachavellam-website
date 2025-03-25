let avatar 


let lobbyForm = document.getElementById('lobby__form')
lobbyForm.addEventListener('submit',(e) => {
    e.preventDefault()

    if(!avatar){
        alert('you msut select an avatar before joining a room')
        return 
    }

    let urlParams = new URLSearchParams(window.location.search)
    let roomId = urlParams.get('room')

    sessionStorage.setItem('display_name',e.target.name.value)
    lobbyForm.reset();
    window.location = `room.html?room=${roomId}`
})

let avatarOptions = document.getElementsByClassName('avatar__option')
for(let i=0; avatarOptions.length > i; i++){

    avatarOptions[i].addEventListener('click',(e) => { 
        
            
        for(let i=0; avatarOptions.length>i;i++){
            avatarOptions[i].classList.remove('avatar__option__selected')
        }

        avatarOptions[i].classList.add('avatar__option__selected')

            avatar = e.target.src
            sessionStorage.setItem('avatar',avatar)
    })

  
}

// lobbyForm.room.addEventListener('keyup', (e) =>{
//     let cleanned_value = e.target.value.replace(' ', '')
//     e.target.value = cleanned_value.toUpperCase()
// })
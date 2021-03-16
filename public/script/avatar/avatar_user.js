window.onload = () => {
    let avatars = document.querySelectorAll(".avatar-choice")
    for (const avatar of avatars) {
        avatar.addEventListener("click", function () {
            let xmlhttp = new XMLHttpRequest;
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
                    let response = JSON.parse(this.responseText);
                    
                    const avatarProfil = document.querySelectorAll(".avatar-profil>img")
                    avatarProfil[0].src = '/img/avatar/' + response.path + '.png'

                    const avatarChoices = document.querySelectorAll(".avatar-choice")
                    console.log(avatarChoices);

                    for (let a of avatarChoices) {
                        
                        if (a.dataset.path == response.path) {
                            a.classList.add('selected-avatar')
                        } else {
                            a.classList.remove('selected-avatar')
                        }
                    }
                }
            };
            xmlhttp.open("get", `/user/profil/avatar/${this.dataset.path}`)
            xmlhttp.send()
        })
    }
}
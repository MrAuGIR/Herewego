

document.querySelectorAll('.form-check-input').forEach(input => {

    // pour chaque check box on ecoute le click
    input.addEventListener('change', () =>{


        // On fabrique la "queryString"
        const Params = new URLSearchParams();
        Params.append('userId', input.getAttribute('data-user'))
        

        // On récupère l'url active
        const Url = new URL(window.location.href);
        console.log(Params);
        console.log(Url);

        requeteAjax(Url,Params);

    })

})





function requeteAjax(url, params){

    
    path = url.pathname + "?"+ params.toString() + "&ajax=1"
    
    // On lance la requête ajax
    fetch(path, {
        
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    }).then(response => 
        response.json()
    ).then(data => {
        
        if (data.success) {
            document.getElementById('message').innerText ="Changement effectué"
        } else {
            alert(data.error)                    
        }

        // On met à jour l'url
       history.pushState({}, null, url.pathname);
    }).catch(e => alert(e));


}
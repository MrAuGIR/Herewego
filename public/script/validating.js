window.onload= () =>{

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



}

function requeteAjax(url, params){


    // On lance la requête ajax
    fetch(url.pathname + "?" + params.toString() + "&ajax=1", {
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    }).then(response => 
        response.json()
    ).then(data => {
        // On va chercher la zone de contenu
        const content = document.querySelector("#content");

        // On remplace le contenu
        content.innerHTML = data.content;

        // On met à jour l'url
        history.pushState({}, null, url.pathname + "?" + params.toString());
    }).catch(e => alert(e));


}
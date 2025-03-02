window.onload = () => {
    //gestion des liens "supprimer"
    let links = document.querySelectorAll("[data-delete]")

    // on boucle sur links
    for (link of links) {
        // on ecoute le click
        link.addEventListener("click", function(e){
            // on empeche la navigation
            e.preventDefault()
            //ondemande confirmation
            if(confirm("Voulez vous supprimer définitivement cette image ?")) {
                // on envoie une requete AJAX vers le href du lien avec la methode DELETE
                fetch(this.getAttribute("href"), { //promesse
                    method: "DELETE",
                    headers: {
                        'X-Requested-With': "XMLHttpRequest",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({"_token": this.dataset.token}) // dataset va chercher tous les attribut dans le a qui commence par 'data'
                }).then(    //si promesse tenue
                    // on recupère la réponse en json
                    response => response.json()
                ).then( data => {
                    if (data.success) {
                        this.parentElement.parentElement.remove();
                    } else {
                        alert(data.error)                    
                    }
                }).catch(e => alert(e))
            }
        })
    }

    
}


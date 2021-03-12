window.onload = () => {

    const FiltersForm = document.querySelector("#filters");

    // On boucle sur les input de type checkbox
    document.querySelectorAll("#filters .input-check").forEach(input => {

        input.addEventListener("change", () => {

            const Form = new FormData(FiltersForm);

            // On fabrique la "queryString"
            const Params = new URLSearchParams();

            // Ici on intercepte les clics
            // On récupère les données du formulaire
            console.log('je click sur un check box catégorie')
        

            Form.forEach((value, key) => {
                Params.append(key, value);
            });

            // On récupère l'url active
            const Url = new URL(window.location.href);

            requeteAjax(Url,Params);
        });

    });   

    document.querySelectorAll('#filters #localisation').forEach(input => {
        input.addEventListener('input', () =>{

            const Form = new FormData(FiltersForm);

            // On fabrique la "queryString"
            const Params = new URLSearchParams();

            // Ici on intercepte les clics
            // On récupère les données du formulaire
            console.log('je tape dans le input localisation')
        

            Form.forEach((value, key) => {
                Params.append(key, value);
            });

            // On récupère l'url active
            const Url = new URL(window.location.href);

            requeteAjax(Url,Params);
        });
    });
       
        
    document.querySelector('select').addEventListener('change',()=>{

            const Form = new FormData(FiltersForm);

            // On fabrique la "queryString"
            const Params = new URLSearchParams();

            // Ici on intercepte les clics
            // On récupère les données du formulaire
            console.log('je selection un ordre dans le select')
        

            Form.forEach((value, key) => {
                Params.append(key, value);
            });

            // On récupère l'url active
            const Url = new URL(window.location.href);

            requeteAjax(Url,Params);
       
    });
            

            
        
    
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
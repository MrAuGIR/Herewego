//AJAX
var xmlhttp = new XMLHttpRequest();


// direction -> start (ville de depart) , end (ville d'arrive)
function envoieAjax(id, direction){

    let input = document.getElementById(id)

    input.addEventListener('input', function(e){

        e.preventDefault();
        if(input.value.length> 3)
        {
            console.log(`valeur de la saisie ${input.value}`)
            value = input.value.replaceAll(' ','%20'); // on remplace les espace par %20

            let urlApi = `https://api-adresse.data.gouv.fr/search/?q=${value}` 

            xmlhttp.open("GET", urlApi, true);
                xmlhttp.onreadystatechange = function() {

                    if (this.readyState == 4 && this.status == 200) {
                        var data = JSON.parse(this.responseText);
                        console.log(data); 
                        //let html = miseEnForm(data);
                        let html = '<ul>';
                        for( adresse of data.features){
                            html += `<a href="#resultatApiEvent" class="list-result nav-link" onclick="selectR(this,'${direction}')" nameAdd="${adresse.properties.name}" city="${adresse.properties.city}" citycode="${adresse.properties.citycode}" postcode="${adresse.properties.postcode}" x="${adresse.properties.x}" y="${adresse.properties.y}">${adresse.properties.label}</a><br>`
                        }
                        html += '</ul>'
                        document.getElementById('resultatApi'+direction).innerHTML=html;
                    }
                };
                xmlhttp.send();
        }

    })
}

function selectR(e,chaine){ 
    let cityCode= e.getAttribute('citycode');
    let postcode = e.getAttribute('postcode');
    let city = e.getAttribute('city');
    let nameAdd = e.getAttribute('nameAdd');

    let x = e.getAttribute('x');
    let y = e.getAttribute('y');
    document.getElementById('postcode'+chaine).value=postcode;
    document.getElementById('nameAdd'+chaine).value=nameAdd;
    document.getElementById('city'+chaine).value=city;
    document.getElementById('x'+chaine).value=x;
    document.getElementById('y'+chaine).value=y;

    //on vide le champ de resultat
    document.getElementById('resultatApi'+chaine).innerHTML="";
    //document.getElementById('adress'+chaine).value="";
}


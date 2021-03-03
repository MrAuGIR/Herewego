//AJAX
var xmlhttp = new XMLHttpRequest();

let adressInput = document.getElementById('adressInput')

adressInput.addEventListener('input', function(){

    if(adressInput.value.length> 3)
    {
        console.log(`valeur de la saisie ${adressInput.value}`)
        value = adressInput.value.replaceAll(' ','%20'); // on remplace les espace par %20

        let urlApi = `https://api-adresse.data.gouv.fr/search/?q=${value}` 

         xmlhttp.open("GET", urlApi, true);
            xmlhttp.onreadystatechange = function() {

                if (this.readyState == 4 && this.status == 200) {
                    var data = JSON.parse(this.responseText);
                    console.log(data); 
                    //let html = miseEnForm(data);
                    let html = '<ul>';
                    for( adresse of data.features){
                        html += `<a href="#" class="list-result nav-link" onclick="selectR(this)" nameAdd="${adresse.properties.name}" city="${adresse.properties.city}" citycode="${adresse.properties.citycode}" postcode="${adresse.properties.postcode}" x="${adresse.properties.x}" y="${adresse.properties.y}">${adresse.properties.label}</a><br>`
                    }
                    html += '</ul>'
                    document.getElementById('resultatApi').innerHTML=html;
                }
            };
            xmlhttp.send();
    }

})


function selectR(e){ 
    let cityCode= e.getAttribute('citycode');
    let postcode = e.getAttribute('postcode');
    let city = e.getAttribute('city');
    let nameAdd = e.getAttribute('nameAdd');
    console.log('nameAdd:'+nameAdd)
    let x = e.getAttribute('x');
    let y = e.getAttribute('y');
    document.getElementById('postcode').value=postcode;
    document.getElementById('nameAdd').value=nameAdd;
    document.getElementById('city').value=city;
    document.getElementById('x').value=x;
    document.getElementById('y').value=y;

    //on vide le champ de resultat
    document.getElementById('resultatApi').innerHTML="";
}


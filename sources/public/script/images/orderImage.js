//gestion de l'importance des images
let inputs = document.querySelectorAll("[data-order]")
for (input of inputs) {
    input.addEventListener("change", function(e){    
        let xmlhttp = new XMLHttpRequest;
        xmlhttp.open("get", `/event/picture/order/${e.target.value}/${this.dataset.id}`)
        xmlhttp.send()
    })
}
//gestion de l'importance des images
let inputs = document.querySelectorAll("[data-order]")
for (input of inputs) {
    input.addEventListener("change", function(e){
        let xmlhttp = new XMLHttpRequest;
        xmlhttp.open("post", `/event/picture/order/${this.dataset.id}`)
        xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
        let params = `value=${encodeURIComponent(e.target.value)}&_token=${encodeURIComponent(this.dataset.token)}`
        xmlhttp.send(params)
    })
}

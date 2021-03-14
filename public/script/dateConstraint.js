window.onload = () => {

    goStartedAt = document.getElementById('transport_goStartedAt');
    goEndedAt = document.getElementById('transport_goEndedAt');

    returnStartedAt = document.getElementById('transport_returnStartedAt');
    returnEndedAt = document.getElementById('transport_returnEndedAt');

    /* si changement de date sur le depart ALLER*/
    goStartedAt.addEventListener('change', () => {
        console.log(goStartedAt.value);
        goEndedAt.min = goStartedAt.value;
    });

    /* si changement de date sur l'arrivé ALLER*/
    goEndedAt.addEventListener('change', () => {
        returnStartedAt.min = goEndedAt.value;
    });

    /*si changement de date sur le départ Retour*/
    returnStartedAt.addEventListener('change', () => {
        returnEndedAt.min = returnStartedAt.value;
    })

    
}
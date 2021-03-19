const btnCopy = document.querySelector('#btn-copy');
const toCopy = document.querySelector('#to-copy');

btnCopy.addEventListener('click', () => {
    console.log('tag copié');
    navigator.clipboard.writeText(toCopy.innerText);
});

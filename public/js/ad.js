// Script JS pour le bouton qui permet dajouter des sousformulaires dimages dans une annonce

$('#add_image').click(function(){
    // On récupère le numéro des futurs champs qu'on va créer cf video 6.3 4minutes
    const index = +$('#widgets-counter').val(); // le + est la pour retourner un int et pas str

    // On récupère le prototype des entrées (le code nécessaire à générer une nouvelle entrée, click droit inspect sur navigateur...)
    const tmpl = $('#ad_images').data('prototype').replace(/__name__/g, index); // replace "__name__" par l'index (en regex)
    // Ainsi dans le code nécessaire, ad_image_NumeroDeLindex_url

    // Inject ce code au sein de la div avec id ad_images
    $('#ad_images').append(tmpl);

    $('#widgets-counter').val(index + 1); // sans le '+' d'avant aurait additioné en mode addition de string !

    // Gestion du bouton supprimer
    handleDeleteButtons();
});

// Ici on gère la suppression d'un sous-formulaire d'image
function handleDeleteButtons() {
    $('button[data-action="delete"]').click(function(){
        const target = this.dataset.target; // this est l'élèment html qui a déclenché l'event, dataset->data-*
        $(target).remove();
    });
}

// On ajoute la gestion de bouton suppression pour le cas où on édite une annonce avec des images existantes

// Gestion du bug où ajoute une image à un ad existant mais delete une autre image du coup
function updateCounter() {
    // Le problème qu'on résout : le input hidden value=0 à chaque fois, or doit pas être = 0 si images déjà présentes
    const count = $('#ad_images div.form-group').length;
    $('#widgets-counter').val(count);

}

updateCounter(); // Faut pas oublier de l'appeler quand même
handleDeleteButtons();

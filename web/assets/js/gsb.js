/*****************************************************************************
 * Chargement de la page principale
 * ***************************************************************************/ 
$(function() {
    
    // datepicker
    $("#txtDateHF").datepicker({ dateFormat: "dd/mm/yy" }).val()	  

    // affiche les div de class .alert pendant 4 sec puis disparaît
    $(".alert").show("slow").delay(10000).hide("slow");

    //--- sur le clic d'un bouton de class .close ---
    // cache les div de class .alert lorsqu'on clique sur un bouton de class .close
    $(".close").click(function() {
        $(".alert").hide("slow");
    });      
});

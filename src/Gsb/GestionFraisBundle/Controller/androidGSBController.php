<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once ("Includes/class.pdogsb.inc.php");
require_once ("Includes/class.fctgsb.inc.php");

// connexion à la base de données gsb
$pdo = PdoGsb::getPdoGsb();

// Si le serveur a enregistré une méthode "post" alors
if ($_SERVER["REQUEST_METHOD"] == "POST") {

   if ($_REQUEST["op"]=="authentification") {
       
        // récupére le login et le mdp saisis dans la BDD et retourne les 
        // informations d'un employé
        $login = $_REQUEST['login'];
        $mdp = $_REQUEST['mdp'];
        $resultat[] = $pdo->getInfosEmploye($login,$mdp);
    
        print(json_encode($resultat));
            
        // Si la saisie n'est pas valide alors affiche un message d'erreur 
//        if(!is_array($employe)){
//        FctGsb::ajouterErreur("Login ou mot de passe incorrect");
//        return $this->render('GsbGestionFraisBundle:Default:index.html.twig',
//        array('erreurs' => $_REQUEST['erreurs']));
//                
//        // Sinon on renvoie le resultat
//        } else {
//            print(json_encode($resultat));
//        }
       
       
   }
   // affiche le petit test du formulaire connexion.html
    else {
        $login = $_REQUEST['login'];
	$mdp = $_REQUEST['mdp'];
	// test affichage avec methode 'post'
	echo "Bonjour ".$_POST['login']. "<br>votre mot de passe est ".$_POST['mdp']."<br>";
	// test affichage avec methode 'request'
	echo "Bonjour ".$login. "<br>votre mot de passe est ".$mdp."<br>";
		
    }
   
    
}                      
    
?>


<?php

namespace Gsb\GestionFraisBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

require_once ("Includes/class.pdogsb.inc.php");
require_once ("Includes/class.fctgsb.inc.php");

/**
 * Contrôleur d'accueil de l'application GSB 
 *	
 * @package default
 * @author Thierry Raimond
 * @version    1.0
 */
class DefaultController extends Controller
{
    /**
     * Vérifie si un utilisateur est déjà connecté sinon affiche l'écran 
     * d'authentification des utilisateurs
     * @return fonction|twig retourne la fonction connexion() ou la page 
     * index.html.twig
     */
    public function indexAction()
    {
        // test si un employé est connecté
        $estConnecte = \FctGsb::estConnecte();
        if (!isset($_REQUEST['uc']) || !$estConnecte) {
            $_REQUEST['uc'] = 'connexion';
        }	 
        $uc = $_REQUEST['uc'];
        switch ($uc) {
            case 'connexion':
                // gestion de la connexion des employés
                return $this->connexion();
                break;
            default :
                return $this->render('GsbGestionFraisBundle:Default:index.html.twig',
                    array('erreurs' => 0));
                break;
        }
    }
    /**
     * Module de connexion des employées
     * @return fonction|twig retourne la fonction sommaire ou affiche à nouveau 
     * l'écran d'authentification avec un ou plusieurs messages d'erreur
     */
    private function connexion() {
        
        $pdo = \PdoGsb::getPdoGsb();
        // Si le serveur a enregistré une méthode "post" alors
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
                      
            // récupére le login et le mdp saisis dans la BDD et retourne les 
            // informations d'un employé
            $login = $_REQUEST['login'];
            $mdp = $_REQUEST['mdp'];
            $employe = $pdo->getInfosEmploye($login,$mdp);
            
            // Si la saisie n'est pas valide alors affiche un message d'erreur 
            if(!is_array($employe)){
                \FctGsb::ajouterErreur("Login ou mot de passe incorrect");
                return $this->render('GsbGestionFraisBundle:Default:index.html.twig',
                    array('erreurs' => $_REQUEST['erreurs']));
                
            // Sinon enregistre dans une varable de session les infos d'un employe
            } else {
                $id = $employe['id'];
                $nom =  $employe['nom'];
                $prenom = $employe['prenom'];
                $metier = $employe['metier'];
                \FctGsb::connecter($id,$nom,$prenom,$metier);
                
                // si un comptable est connecté et la date actuelle est comprise
                // dans la période de la campagne de validation soit 
                // entre le dix mois actuel et le vingt du mois acutel
                // alors mise à jour des états des fiches "saisies en cours" 
                // vers "clôturés.
                if ($metier == 'Comptable' && \FctGsb::estDateCampagneValidation()) {
                    
                    $nbMajCampagneValidation = $pdo->
                        majEtatFicheFraisCampagneValidation();
                    // si il y a eu des mises à jours
                    // alors un message d'information s'affiche à l'écran
                    if ($nbMajCampagneValidation != 0) {
                        $_SESSION['alerte'] = "Le script de la campagne de 
                        validation a été lancé avec succés. ".
                        $nbMajCampagneValidation." fiche(s) a ou ont été "
                        ."automatiquement clôturé(s).";
                        $session = $this->get('session');
                        $session->set('alerte', $_SESSION['alerte']);
                    }
                }
                return $this->sommaire();
            }
        } else {
            // sinon affiche le formulaire de connexion
            return $this->render('GsbGestionFraisBundle:Default:index.html.twig',
                    array('erreurs' => 0));
        }
    }
    /**
     * Affiche le sommaire
     * @return twig affiche le sommaire.html.twig
     */
    private function sommaire() {
        
        // si l'utilisateur connecté est un comptable mise à jour du tableau de bord 
        // et cache le menu "Suivi du remboursement des frais" sur la navbar sinon 
        // l'affiche
		if($_SESSION['metier'] == 'Comptable') {
            
            // Enregistre dans une variable de session le nombre
            // de fiches de frais en fonction de son état
            \FctGsb::actualiserEtatFiche();
		}
        
		return $this->render('GsbGestionFraisBundle::sommaire.html.twig',
            array('session' => $_SESSION,
                  'erreurs' => 0));
    }
}

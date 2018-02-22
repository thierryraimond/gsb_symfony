<?php
// src/Gsb/GestionFraisBundle/Controller/AdministrerFraisController.php

namespace Gsb\GestionFraisBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

require_once ("Includes/class.pdogsb.inc.php");
require_once ("Includes/class.fctgsb.inc.php");

/**
 * Contrôleur qui permet aux comptables d'administrer les fiches de frais des
 * visiteurs
 * 
 * Consultation de la fiche de frais de tous les visiteurs
 *	
 * @package default
 * @author Thierry Raimond
 * @version    1.0
 */
class AdministrerFraisController extends Controller{
    
    /**
     * lance le formulaire de consultation de frais par mois et par visiteur
     * 
     * affiche seulement le formulaire forSelectionnerMoisVisiteur qui contient les 
     * listes des mois par visiteur OU affiche seulement le formulaire contenant la 
     * liste des visiteurs selectionnerVisiteur
     * @return twig deux retours possibles : 
     * forSelectionnerMoisVisiteur.html.twig ou selectionnerVisiteur.html.twig
     */
    public function administrerAction() {
        
        $pdo = \PdoGsb::getPdoGsb();

        // récupére tous les visiteurs de la bdd
		$lesVisiteurs = $pdo->getLesVisiteurs();
        // si il ne s'agit pas d'une premiere utilsation du formulaire selectionner 
        // un visiteur 
        if ($_SESSION['idEmploye'] != "null") {
            // alors on recupere le nom et prenom de l'id employé cité en paramètre 
            $employe = $pdo->getPrenomNomEmploye($_SESSION['idEmploye']);
        } else {
            // sinon on affecte un tableau null à la variable $employe
            $employe = array("null", "null");
        }

        // Si le serveur a enregistré une méthode "post" alors
        // autrement dit si un visiteur a été séléctionné alors
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // récupére dans une variable de session l'employé qui a été sélectionné 
            // dans le formulaire formSelectionnerVisiteur
            $_SESSION['idEmploye'] = $_REQUEST['lstVisiteur'];
            //$idVisiteur = $_SESSION['idEmploye'];
            
            $lesVisiteurs = $pdo->getLesVisiteurs();
            $employe = $pdo->getPrenomNomEmploye($_SESSION['idEmploye']);
            $lesMois = $pdo->getLesMoisDisponibles($_SESSION['idEmploye']);
            // Afin de sélectionner par défaut le dernier mois dans la zone de liste
            // on demande toutes les clés, et on prend la première,
            // les mois étant triés décroissants
            $lesCles = array_keys( $lesMois );
            $moisASelectionner = $lesCles[0];
            
            // affiche seulement le formulaire formSelectionnerMoisVisiteur qui
            // contient les listes des mois par visiteur
            return $this->render('GsbGestionFraisBundle:AdministrerFrais:selectionnerMoisVisiteur.html.twig',
                array(
                        'session'           => $_SESSION,
                        'erreurs'           => 0,
                        'employe'           => $employe,
                        'lesVisiteurs'      => $lesVisiteurs,
                        'lesMois'           => $lesMois,
                        'moisASelectionner' => $moisASelectionner,
            ));
            
        }
        
        //affiche seulement le formulaire contenant la liste des visiteurs
        return $this->render('GsbGestionFraisBundle:AdministrerFrais:selectionnerVisiteur.html.twig',
            array(
                    'session'      => $_SESSION,
                    'erreurs'      => 0,
                    'employe'      => $employe,
                    'lesVisiteurs' => $lesVisiteurs
            ));
        
    }
    
    /**
     * Affiche la fiche de frais complète du visiteur selectionné pour le 
     * mois selectionné dans le formulaire formSelectionnerMoisVisiteur
     * @return twig la page 
     * GsbGestionFraisBundle:AdministrerFrais:administrerFrais.html.twig 
     */
    public function etatAction() {
        
        $pdo = \PdoGsb::getPdoGsb();
        
        $lesVisiteurs = $pdo->getLesVisiteurs();
		$employe = $pdo->getPrenomNomEmploye($_SESSION['idEmploye']);
        $_SESSION['leMois'] = $_REQUEST['lstMois'];
		$lesMois=$pdo->getLesMoisDisponibles($_SESSION['idEmploye']);
		$moisASelectionner = $_SESSION['leMois'];

		$lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($_SESSION['idEmploye'],$_SESSION['leMois']);
		$lesFraisForfait= $pdo->getLesFraisForfait($_SESSION['idEmploye'],$_SESSION['leMois']);
		$lesInfosFicheFrais = $pdo->getLesInfosFicheFrais($_SESSION['idEmploye'],$_SESSION['leMois']);
		$numAnnee = substr( $_SESSION['leMois'],0,4);
		$numMois = substr( $_SESSION['leMois'],4,2);
		$libEtat = $lesInfosFicheFrais['libEtat'];
		$montantValide = $lesInfosFicheFrais['montantValide'];
		$nbJustificatifs = $lesInfosFicheFrais['nbJustificatifs'];
		$dateModif =  $lesInfosFicheFrais['dateModif'];
		$dateModif = \FctGsb::dateAnglaisVersFrancais($dateModif);
        
        return $this->render('GsbGestionFraisBundle:AdministrerFrais:administrerFrais.html.twig',
            array(
                    'lesMois'             => $lesMois,
                    'moisASelectionner'   => $moisASelectionner,
                    'session'             => $_SESSION,
                    'erreurs'             => 0,
                    'lesFraisHorsForfait' => $lesFraisHorsForfait,
                    'lesFraisForfait'     => $lesFraisForfait,
                    'lesInfosFicheFrais'  => $lesInfosFicheFrais,
                    'numAnnee'            => $numAnnee,
                    'numMois'             => $numMois,
                    'libEtat'             => $libEtat,
                    'montantValide'       => $montantValide,
                    'nbJustificatifs'     => $nbJustificatifs,
                    'dateModif'           => $dateModif,
                    'employe'             => $employe,
                    'lesVisiteurs'        => $lesVisiteurs
            ));
        
    }
    
    /**
     * Affiche toutes les fiches de frais par mois pour le visiteur selectionné
     * @return fonction afficherAction(0,"modifierFiche")
     */
    public function modifierAction() {
        
        return $this->afficher(0,"modifierFiche");
    }
    
    /**
     * Controle les éléments saisis et validés dans le formulaire 
     * formFraisForfait.html.twig si tout est bon alors enregistre la saisie dans
     * la bdd sinon affiche un message d'erreur
     * 
     * @return fonction modifierAction() ou 
     * afficherAction($_REQUEST['erreurs'],"modifierFiche")
     */
    public function saisirForfaitAction() {
        
        // déclaration des variables initiales d'usage
        $idVisiteur = $_SESSION['idEmploye'];
        $mois = $_SESSION['leMois'];
        $numAnnee = substr( $mois,0,4);
        $numMois = substr( $mois,4,2);
        $pdo= \PdoGsb::getPdoGsb();
        
        $lesFrais = $_REQUEST['lesFrais'];
        if (\FctGsb::lesQteFraisValides($lesFrais)) {
            $pdo->majFraisForfait($idVisiteur,$mois,$lesFrais);
        } else {
            \FctGsb::ajouterErreur("Les valeurs des frais doivent être numériques");
            return $this->afficher($_REQUEST['erreurs'],"modifierFiche");
        }
        return $this->modifierAction();
    }
    
    /**
     * Controle les éléments saisis et validés dans le formulaire 
     * formFraisHorsForfait.html.twig si tout est bon alors enregistre la saisie dans
     * la bdd sinon affiche un message d'erreur
     * @return fonction modifierAction() ou 
     * afficherAction($_REQUEST['erreurs'],"modifierFiche")
     */
    public function saisirHorsForfaitAction() {
        
        // déclaration des variables initiales d'usage
        $idVisiteur = $_SESSION['idEmploye'];
        $mois = $_SESSION['leMois'];
        $numAnnee = substr( $mois,0,4);
        $numMois = substr( $mois,4,2);
        $pdo = \PdoGsb::getPdoGsb();
        
        $nbJustificatifs = $_REQUEST['nbJustificatifs'];
        if (\FctGsb::estEntierPositif($nbJustificatifs)) {
            $pdo->majNbJustificatifs($idVisiteur,$mois,$nbJustificatifs);
        } else {
            ajouterErreur("la valeur du nombre de justificatif(s) doit être "
                . "numériques");
            
            return $this->afficher($_REQUEST['erreurs'],"modifierFiche");
            
        }
        return $this->modifierAction();
    }
    
    /**
     * supprime la ligne de frais hors forfait en bdd via l'id en paramètre
     * 
     * @param $idFrais
     * @return fonction modifierAction()
     */
    public function supprimerAction($idFrais)
    {
        $pdo= \PdoGsb::getPdoGsb();
		
		$pdo->supprimerFraisHorsForfait($idFrais);
        
        return $this->modifierAction();
    }
    
    /**
     * reporte la ligne de frais hors forfait au mois suivant
     * 
     * modifie le mois à m+1 de la ligne de frais hors forfait en bdd et vérifie
     * si il existe une fiche de frais à m+1 si oui alors rajoute la ligne à la fiche
     * sinon création d'une nouvelle fiche et ajoute la ligne de frais.
     * @param $idFrais
     * @return fonction modifierAction()
     */
    public function reporterAction($idFrais)
    {
        // déclaration des variables initiales d'usage
        $idVisiteur = $_SESSION['idEmploye'];
        $mois = $_SESSION['leMois'];
        $numAnnee = substr( $mois,0,4);
        $numMois = substr( $mois,4,2);
        $pdo = \PdoGsb::getPdoGsb();
        
        // incrémentation du mois
        $numAnneSuivant = $numAnnee;
        $numMoisSuivant = $numMois;
        if ($numMois == '12') {
            $numMoisSuivant = '01';
            $numAnneSuivant++;
            $moisSuivant = $numAnneSuivant.$numMoisSuivant;
        } else {
            $moisSuivant = $numAnneSuivant.$numMoisSuivant+1;
        }
        
        // si le visiteur n'a pas de ligne de frais créé pour le mois cité en 
        // paramètre alors le systeme créé une nouvelle fiche de frais pour le mois 
        // en paramètre et récupère le dernier mois en cours de traitement, met à 
        // 'CL' son champs état
        if ($pdo->estPremierFraisMois($idVisiteur,$moisSuivant)) {
        	$pdo->creeNouvellesLignesFrais($idVisiteur,$moisSuivant);
		}
        
        // modification du mois à m+1 dans la ligne de frais hors forfait
        $pdo->majMoisLigneHorsForfait($idFrais,$moisSuivant);
        
        return $this->modifierAction();  
    }
    /**
     * Valide la fiche de frais d'un employe
     * @return fonction administrerAction()
     */
    public function validerFicheAction() {
        
        // déclaration des variables initiales d'usage
        $idVisiteur = $_SESSION['idEmploye'];
        $mois = $_SESSION['leMois'];
        $numAnnee = substr( $mois,0,4);
        $numMois = substr( $mois,4,2);
        $pdo = \PdoGsb::getPdoGsb();
        
        // récupère le total de la fiche = total frais forfait + total frais hors
        // forfait
        $totalFraisForfait = $pdo->getTotalFraisForfait($idVisiteur, $mois);
        $totalFraisHorsForfait = $pdo->getTotalFraisHorsForfait($idVisiteur, $mois);
        $totalFraisFiche = $totalFraisForfait + $totalFraisHorsForfait;

        // valide la fiche de frais
        $pdo->validerFicheFrais($idVisiteur,$mois,$totalFraisFiche);
        // Actualise le tableau de bord
        \FctGsb::actualiserEtatFiche();
        
        return $this->administrerAction(); 
    }
    /**
     * Actualise le tableau de bord et affiche la page selectionSuivis.html.twig
     * @return twig affiche selectionSuivis.html.twig
     */
    public function suivreFicheAction() {
                
        // Actualise le tableau de bord
        // Enregistre dans une variable de session le nombre
		// de fiches de frais en fonction de son état
        \FctGsb::actualiserEtatFiche();
	
        return $this->render(
            'GsbGestionFraisBundle:AdministrerFrais:selectionSuivis.html.twig',
                    array('session' => $_SESSION,
                          'erreurs' => 0));
    }
    /**
     * Affiche la liste des fiches en fonction de l'état choisis dans la page 
     * Suivis.html.twig
     * @param string $libEtat
     * @return twig affiche la page Suivis.html.twig
     */
    public function voirFicheEtatAction($libEtat) {
        
        $pdo = \PdoGsb::getPdoGsb();
        $lesFichesFrais = $pdo->getLesFichesFraisParEtat($libEtat);
        
        return $this->render('GsbGestionFraisBundle:AdministrerFrais:Suivis.html.twig',
                    array('session'        => $_SESSION,
                          'erreurs'        => 0,
                          'lesFichesFrais' => $lesFichesFrais,
                          'etat'           => $libEtat
        ));
    }
    /**
     * Affiche la fiche détaillé pour un visiteur et un mois donnés
     * @param string $idVisiteur
     * @param string $mois
     * @return fonction afficherAction(0,"administrerFrais")
     */
    public function voirEtatAction($idVisiteur, $mois) {
        
        $pdo = \PdoGsb::getPdoGsb();
        $_SESSION['idEmploye'] = $idVisiteur;
        $_SESSION['leMois'] = $mois;
        
        return $this->afficher(0,"administrerFrais");
    }
    /**
     * Passe à l'état "Rembourser" la fiche de frais en cours
     * 
     * @return fonction administrerAction()
     */
    public function rembourserFicheAction() {
        
        // déclaration des variables initiales d'usage
        $idVisiteur = $_SESSION['idEmploye'];
        $mois = $_SESSION['leMois'];
        $numAnnee = substr( $mois,0,4);
        $numMois = substr( $mois,4,2);
        $pdo = \PdoGsb::getPdoGsb();
        
        // Modifie l'état à Rembourser et met la date de modification à aujourd'hui
        $pdo->majEtatFicheFrais($idVisiteur,$mois,'RB');
        // Actualise le tableau de bord
        \FctGsb::actualiserEtatFiche();
        
        return $this->administrerAction();
    }
    /**
     * Passe à l'état "Clôturer" la fiche de frais en cours
     * 
     * @return fonction administrerAction()
     */
    public function cloturerFicheAction() {
        
        // déclaration des variables initiales d'usage
        $idVisiteur = $_SESSION['idEmploye'];
        $mois = $_SESSION['leMois'];
        $numAnnee = substr( $mois,0,4);
        $numMois = substr( $mois,4,2);
        $pdo = \PdoGsb::getPdoGsb();
        
        // Modifie l'état à Clôturer et met la date de modification à aujourd'hui
        $pdo->majEtatFicheFrais($idVisiteur,$mois,'CL');
        // Actualise le tableau de bord
        \FctGsb::actualiserEtatFiche();
        
        return $this->administrerAction();
    }
    
    /**
     * Affiche la page saisie en paramètre avec toutes les fiches de frais par mois 
     * pour le visiteur selectionné. affiche également les erreurs en paramètre 
     * @param array[]|string $erreurs un ou plusieurs messages d'erreurs
     * @param twig $twig nom de la page html.twig à afficher
     * @return twig affiche la page twig choisie
     */
    private function afficher($erreurs, $twig) {
        
        $pdo = \PdoGsb::getPdoGsb();
        
        $lesVisiteurs = $pdo->getLesVisiteurs();
		$employe = $pdo->getPrenomNomEmploye($_SESSION['idEmploye']);
		$lesMois=$pdo->getLesMoisDisponibles($_SESSION['idEmploye']);
		$moisASelectionner = $_SESSION['leMois'];
        
        // récupère le total des frais forfaitisés pour l'employé et le mois cités
        // en paramètre
        $totalFraisForfait = $pdo->getTotalFraisForfait($_SESSION['idEmploye'],
            $_SESSION['leMois']);
        // récupère le total des frais hors forfait pour l'employé et le mois cités
        // en paramètre
        $totalFraisHorsForfait = $pdo->getTotalFraisHorsForfait(
            $_SESSION['idEmploye'], $_SESSION['leMois']);

		$lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($_SESSION['idEmploye'],$_SESSION['leMois']);
		$lesFraisForfait= $pdo->getLesFraisForfait($_SESSION['idEmploye'],$_SESSION['leMois']);
		$lesInfosFicheFrais = $pdo->getLesInfosFicheFrais($_SESSION['idEmploye'],$_SESSION['leMois']);
		$numAnnee = substr( $_SESSION['leMois'],0,4);
		$numMois = substr( $_SESSION['leMois'],4,2);
		$libEtat = $lesInfosFicheFrais['libEtat'];
		$montantValide = $lesInfosFicheFrais['montantValide'];
		$nbJustificatifs = $lesInfosFicheFrais['nbJustificatifs'];
		$dateModif =  $lesInfosFicheFrais['dateModif'];
		$dateModif = \FctGsb::dateAnglaisVersFrancais($dateModif);
        //recupére le nombre de ligne du mois encours hors forfait
        $nbLignesHorsforfait = count($lesFraisHorsForfait);
        
        // affiche la fiche de frais complete du mois selectionné pour le visiteur
        // selectionné
        return $this->render(
            'GsbGestionFraisBundle:AdministrerFrais:'.$twig.'.html.twig',
            array(
                    'lesMois'               => $lesMois,
                    'moisASelectionner'     => $moisASelectionner,
                    'session'               => $_SESSION,
                    'erreurs'               => $erreurs,
                    'lesFraisHorsForfait'   => $lesFraisHorsForfait,
                    'lesFraisForfait'       => $lesFraisForfait,
                    'lesInfosFicheFrais'    => $lesInfosFicheFrais,
                    'numAnnee'              => $numAnnee,
                    'numMois'               => $numMois,
                    'libEtat'               => $libEtat,
                    'montantValide'         => $montantValide,
                    'nbJustificatifs'       => $nbJustificatifs,
                    'dateModif'             => $dateModif,
                    'employe'               => $employe,
                    'lesVisiteurs'          => $lesVisiteurs,
                    'nbLignesHorsforfait'   => $nbLignesHorsforfait,
                    'totalFraisForfait'     => $totalFraisForfait,
                    'totalFraisHorsForfait' => $totalFraisHorsForfait
        ));
    }
}  

    


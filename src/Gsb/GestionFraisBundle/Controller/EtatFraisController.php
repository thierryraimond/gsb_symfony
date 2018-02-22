<?php
// src/Gsb/GestionFraisBundle/Controller/EtatFrais.php

namespace Gsb\GestionFraisBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

require_once ("Includes/class.pdogsb.inc.php");
require_once ("Includes/class.fctgsb.inc.php");

/**
* Contrôleur qui affiche les frais en détail de chaque employé
* 
* affiche le detail d'une fiche de frais en selectionnant un mois 
* 
*	
* @package default
* @author Thierry Raimond
* @version    1.0
*/
class EtatFraisController extends Controller {
    
    // affiche toutes les fiches de frais par mois pour le visiteur connecté
    public function consulterAction() {
        
        $idVisiteur = $_SESSION['idVisiteur'];
        $pdo= \PdoGsb::getPdoGsb();
        
        $employe = $pdo->getPrenomNomEmploye($_SESSION['idVisiteur']);
        
        $lesMois = $pdo->getLesMoisDisponibles($idVisiteur);
        // Afin de sélectionner par défaut le dernier mois dans la zone de liste
        // on demande toutes les clés, et on prend la première,
        // les mois étant triés décroissants
        $lesCles = array_keys( $lesMois );
        $moisASelectionner = $lesCles[0];
        
        // Si le serveur a enregistré une méthode "post" alors
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            
            $leMois = $_REQUEST['lstMois']; 
            $moisASelectionner = $leMois;
            $_SESSION['leMois'] = $leMois;
            
            $lesFraisHorsForfait = $pdo->
                getLesFraisHorsForfait($idVisiteur,$leMois);
            $lesFraisForfait= $pdo->
                getLesFraisForfait($idVisiteur,$leMois);
            $lesInfosFicheFrais = $pdo->
                getLesInfosFicheFrais($idVisiteur,$leMois);
        
    		$numAnnee =substr( $leMois,0,4);
        	$numMois =substr( $leMois,4,2);
            $libEtat = $lesInfosFicheFrais['libEtat'];
            $montantValide = $lesInfosFicheFrais['montantValide'];
            $nbJustificatifs = $lesInfosFicheFrais['nbJustificatifs'];
            $dateModif =  $lesInfosFicheFrais['dateModif'];
            $dateModif = \FctGsb::dateAnglaisVersFrancais($dateModif);
        
        // affiche la fiche de frais complete du mois selectionne pour le visiteur
        // connecté
        return $this->render('GsbGestionFraisBundle:EtatFrais:etatFrais.html.twig',
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
                    'dateModif'           => $dateModif
            ));
        
        }
        
        //affiche seulement le formulaire de la liste des mois du visiteur connecté
        return $this->render('GsbGestionFraisBundle:EtatFrais:selectionnerMois.html.twig',
            array(
                    'lesMois'           => $lesMois,
                    'moisASelectionner' => $moisASelectionner,
                    'session'           => $_SESSION,
                    'erreurs'           => 0
            ));
    }
}

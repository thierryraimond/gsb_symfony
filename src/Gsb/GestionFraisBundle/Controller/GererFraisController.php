<?php
// src/Gsb/GestionFraisBundle/Controller/GererFraisController.php

namespace Gsb\GestionFraisBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

require_once ("Includes/class.pdogsb.inc.php");
require_once ("Includes/class.fctgsb.inc.php");

/**
 * Contrôleur qui gère les frais d'un employé
 * 
 * Vérification des saisis, modification ou suppression d'un visiteur dans les
 * éléments de sa fiche frais. Puis enregistre en bdd sinon affiche un ou plusieurs
 * messages d'erreur
 *	
 * @package default
 * @author Thierry Raimond
 * @version    1.0
 */
class GererFraisController extends Controller {
    
    /**
     * Affiche les formulaires de saisis avec les informations déjà enregistrés
     * précédement pour le mois en cours et le visiteur connecté
     * @return fonction afficher(0) ou revient au sommaire avec les erreurs affichées
     */
    public function gererAction() {
        
        // déclaration des variables initiales d'usage
        $idVisiteur = $_SESSION['idVisiteur'];
        $mois = \FctGsb::getMois(date("d/m/Y"));
        $numAnnee = substr( $mois,0,4);
        $numMois = substr( $mois,4,2);
        $pdo = \PdoGsb::getPdoGsb();
        
        //si le visiteur n'a pas de ligne de frais créé dans le mois en cours
        //alors le systeme créé une nouvelle fiche de frais pour le mois en cours et
        //récupère le dernier mois en cours de traitement, met à 'CL' son champs état
        if ($pdo->estPremierFraisMois($idVisiteur,$mois)) {
        	$pdo->creeNouvellesLignesFrais($idVisiteur,$mois);
            // Actualise le tableau de bord
            \FctGsb::actualiserEtatFiche();
		}
                   
        $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur,
            $mois);
        $lesFraisForfait= $pdo->getLesFraisForfait($idVisiteur,$mois);
        //recupére le nombre de ligne du mois encours hors forfait
        $nbLignesHorsforfait = count($lesFraisHorsForfait);
        
        // si la fiche du mois n'est pas en cours de saise alors affiche un message 
        // d'alerte sinon affiche la page de saisie de la fiche de frais
        if (!$pdo->estEtatSaisieEnCours($idVisiteur,$mois)) {
            \FctGsb::ajouterErreur("Votre fiche de frais est en cours de traitement."
                . " Revenez le mois suivant pour saisir vos nouveaux frais.");
            return $this->render('GsbGestionFraisBundle::sommaire.html.twig',
                    array('session' => $_SESSION,
                          'erreurs' => $_REQUEST['erreurs']));
        } else {
            // affiche la fiche de frais complete du mois selectionne pour le visiteur
            // connecté
            return $this->afficher(0);
        }    
    }
    /**
     * supprime la ligne de frais hors forfait en bdd via l'id en paramètre
     * 
     * @param integer $idFrais
     * @return fonction gererAction()
     */
    public function supprimerAction($idFrais)
    {
        $pdo= \PdoGsb::getPdoGsb();
		$pdo->supprimerFraisHorsForfait($idFrais);
        return $this->gererAction();
    }
    
    /**
     * Controle les éléments saisis et validés dans le formulaire 
     * formHorsFraisForfait.html si tout est bon alors enregistre la saisie dans
     * la bdd sinon affiche un message d'erreur
     * @return fonction gererAction()
     */
    public function saisirHorsForfaitAction() {
        
        // déclaration des variables initiales d'usage
        $idVisiteur = $_SESSION['idVisiteur'];
        $mois = \FctGsb::getMois(date("d/m/Y"));
        $numAnnee = substr( $mois,0,4);
        $numMois = substr( $mois,4,2);
        $pdo = \PdoGsb::getPdoGsb();
        
		$dateFrais = $_REQUEST['dateFrais'];
		$libelle = $_REQUEST['libelle'];
		$montant = $_REQUEST['montant'];
		\FctGsb::valideInfosFrais($dateFrais, $libelle, $montant);
		if (\FctGsb::nbErreurs() != 0 ) {
            return $this->afficher($_REQUEST['erreurs']);
		} else {
			$pdo->creeNouveauFraisHorsForfait(
                $idVisiteur, $mois, $libelle, $dateFrais, $montant);
		}
        return $this->gererAction();
    }
    /**
     * Controle les éléments saisis et valider dans le formulaire 
     * formFraisForfait.html si tout est bon alors enregistre la saisie dans
     * la bdd sinon affiche un message d'erreur
     * @return fonction gererAction()
     */
    public function saisirForfaitAction() {
        
        // déclaration des variables initiales d'usage
        $idVisiteur = $_SESSION['idVisiteur'];
        $mois = \FctGsb::getMois(date("d/m/Y"));
        $numAnnee = substr( $mois,0,4);
        $numMois = substr( $mois,4,2);
        $pdo= \PdoGsb::getPdoGsb();
        
        $lesFrais = $_REQUEST['lesFrais'];
        if (\FctGsb::lesQteFraisValides($lesFrais)) {
            $pdo->majFraisForfait($idVisiteur,$mois,$lesFrais);
        } else {
            \FctGsb::ajouterErreur("Les valeurs des frais doivent être numériques");
            return $this->afficher($_REQUEST['erreurs']);
        }
        return $this->gererAction();
    }
    /**
     * affiche la fiche de frais complète du mois selectionné pour le visiteur
     * connecté
     * @param array[]|string $erreurs
     * @return retourne la page gererFrais.html.twig
     */
    private function afficher($erreurs) {
        // déclaration des variables initiales d'usage
        $idVisiteur = $_SESSION['idVisiteur'];
        $mois = \FctGsb::getMois(date("d/m/Y"));
        $numAnnee = substr( $mois,0,4);
        $numMois = substr( $mois,4,2);
        $pdo= \PdoGsb::getPdoGsb();
        $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur,$mois);
        $lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur,$mois);
        //recupére le nombre de ligne du mois encours hors forfait
        $nbLignesHorsforfait = count($lesFraisHorsForfait);
        // affiche la fiche de frais complete du mois selectionne pour le visiteur
        // connecté
        return $this->render(
                'GsbGestionFraisBundle:GererFrais:gererFrais.html.twig', array(
                    'mois'                => $mois,
                    'numAnnee'            => $numAnnee,
                    'numMois'             => $numMois,
                    'lesFraisHorsForfait' => $lesFraisHorsForfait,
                    'lesFraisForfait'     => $lesFraisForfait,
                    'nbLignesHorsforfait' => $nbLignesHorsforfait,
                    'session'             => $_SESSION,
                    'erreurs'             => $erreurs
            ));
    }
}

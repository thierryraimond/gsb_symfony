<?php
// src/Gsb/GestionFraisBundle/Controller/FpdfController.php

namespace Gsb\GestionFraisBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

require_once ("Includes/class.pdogsb.inc.php");
require_once ("Includes/class.fctgsb.inc.php");
require_once ("Includes/class.pdf.inc.php");

/**
 * Contrôleur qui permet de générer un fichier au format PDF grace à la classe FPDF
 *	
 * @package default
 * @author Thierry Raimond
 * @version    1.0
 * @link       http://www.fpdf.org
 */
class FpdfController extends Controller {
    
    //put your code here
    public function pdfAction() {
        
        $pdo= \PdoGsb::getPdoGsb();
        
        $lesFraisHorsForfait = $pdo->
            getLesFraisHorsForfait($_SESSION['idVisiteur'],$_SESSION['leMois']);
        $lesFraisForfait = $pdo->
            getLesFraisForfait($_SESSION['idVisiteur'],$_SESSION['leMois']);
        $lesInfosFicheFrais = $pdo->
            getLesInfosFicheFrais($_SESSION['idVisiteur'],$_SESSION['leMois']);
        
    	$numAnnee = substr( $_SESSION['leMois'],0,4);
        $numMois = substr( $_SESSION['leMois'],4,2);
        $libEtat = $lesInfosFicheFrais['libEtat'];
        $montantValide = $lesInfosFicheFrais['montantValide'];
        $nbJustificatifs = $lesInfosFicheFrais['nbJustificatifs'];
        $dateModif =  $lesInfosFicheFrais['dateModif'];
        $dateModif = \FctGsb::dateAnglaisVersFrancais($dateModif);
        
        // récupère la valeur du champ 'pdf' de la table 'fichefrais'
        $infoPdf = $lesInfosFicheFrais['pdf'];
        // Si le champ 'pdf' est égal à "1" alors
        if ($infoPdf == 1) {
            // la fiche est éditable
            // Instanciation de la classe dérivée PDF
            $pdf = new \PDF;
            $pdf->AliasNbPages();
            $pdf->AddPage();
            $pdf->SetFont('Times','',12);
        
            // Titres des colonnes
            $headerForfait = array('Frais Forfaitaires','Quantité', 'Montant unitaire', 'Total');
            $headerHorsForfait = array('Date', 'Libellé', 'Montant');
            
            // Les sous parties
            $titreFiche = "Fiche de frais du mois ".$numMois."-".$numAnnee." pour le "
            .$_SESSION['metier']." ".$_SESSION['prenom']." ".$_SESSION['nom'];
            $libelleEtat = "Etat : ".$libEtat." depuis le ".$dateModif;
            $libelleMontantValide = "Montant validé : ".$montantValide." euros";
            $signature = "Fait à Paris, le ".$dateModif;
        
            $pdf->partie($titreFiche,10);
            $pdf->partie($libelleEtat,5);
            $pdf->partie($libelleMontantValide,20);
            $pdf->sousTitre('Eléments forfaitisés');
            $pdf->FancyTableFraisForfait($headerForfait,$lesFraisForfait);
            $pdf->sousTitre('Descriptif des éléments hors fofait');
            $pdf->FancyTableFraisHorsForfait($headerHorsForfait,$lesFraisHorsForfait);
            $pdf->signature($signature);
        
            $pdf->Output();
            // maj de la valeur 'pdf' de la fiche de frais à '0' = non éditable
            $pdo->majPdfFicheFrais($_SESSION['idVisiteur'],$_SESSION['leMois'],0);
            
        } else {
            // le champ 'pdf' est égal à "0" la fiche n'est plus éditable alors on
            // affiche un message d'erreur
            \FctGsb::ajouterErreur("la fiche a déjà été éditée. Dans un soucis d'"
                . "écologie (orientation 'Green-IT') la fiche n'est plus imprimable "
                . "dans son état actuel.");
            
            $lesMois = $pdo->getLesMoisDisponibles($_SESSION['idVisiteur']);
            $moisASelectionner = $_SESSION['leMois'];
            
            // retourne au détail de la fiche de frais avec le message d'erreur 
            // affiché
            return $this->render('GsbGestionFraisBundle:EtatFrais:etatFrais.html.twig',
            array(
                    'lesMois'             => $lesMois,
                    'moisASelectionner'   => $moisASelectionner,
                    'session'             => $_SESSION,
                    'erreurs'             => $_REQUEST['erreurs'],
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
        
        
    }
}

<?php
// src/Gsb/GestionFraisBundle/Controller/Includes/class.pdf.inc.php

require("fpdf.php");

/**
 * Classe qui permet de générer un fichier au format PDF grace à la classe FPDF
 *	
 * @package default
 * @author Thierry Raimond
 * @version    1.0
 * @link       http://www.fpdf.org
 */
class PDF extends FPDF {
    
    // En-tête
    function Header()
    {
        global $titre;
        $titre = 'Remboursement de frais engagés';
        // Logo
        $this->Image('http://trsrv.no-ip.org:8080/gsb_symfony/web/assets/images/logo_transp_82x50.png',
            10,8,0,0);
        //$this->Image('T:\wamp\www\gsb_symfony\web\assets\images\logo_transp_82x50.png',
        //    10,8,0,0);
        // Police Arial gras 15
        $this->SetFont('Arial','B',15);
        // Calcul de la largeur du titre et positionnement
        $w = $this->GetStringWidth($titre)+6;
        $this->SetX((210-$w)/2);
        // Couleurs du cadre, du fond et du texte
        $this->SetDrawColor(0,80,180); // bleu
        $this->SetFillColor(255,255,255); // blanc
        $this->SetTextColor(0,80,180); // bleu
        // Epaisseur du cadre (1 mm)
        $this->SetLineWidth(1);
        // Titre
        $this->Cell($w,10,utf8_decode($titre),1,1,'C',TRUE);
        // Saut de ligne
        $this->Ln(10);
    }

    // Pied de page
    function Footer()
    {
        // Positionnement à 1,5 cm du bas
        $this->SetY(-15);
        // Police Arial italique 8
        $this->SetFont('Arial','I',8);
        // Numéro de page
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
    
    // Tableau coloré
    function FancyTableFraisForfait($header, $data)
    {
        // Couleurs, épaisseur du trait et police grasse
        $this->SetFillColor(255,0,0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128,0,0);
        $this->SetLineWidth(.3);
        $this->SetFont('','B');
        // En-tête
        $w = array(49, 47, 47, 47);
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C',true);
        $this->Ln();
        // Restauration des couleurs et de la police
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Données
        $fill = false;
        foreach($data as $row)
        {
            $this->Cell($w[0],6,utf8_decode($row[1]),'LR',0,'L',$fill);
            $this->Cell($w[1],6,number_format($row[3],0,',',' '),'LR',0,'C',$fill);
            $this->Cell($w[2],6,number_format($row[2],2,',',' '),'LR',0,'R',$fill);
            $this->Cell($w[3],6,number_format($row[3]*$row[2],2,',',' '),'LR',0,'R',
                $fill);
            $this->Ln();
            $fill = !$fill;
        }
        // Trait de terminaison
        $this->Cell(array_sum($w),0,'','T');
        $this->Ln(10);


    }
    
    function sousTitre($sousTitre) {
        // Arial 12
        $this->SetFont('Arial','',11);
        // Couleur de fond
        $this->SetFillColor(200,220,255);
        // Titre
        $this->Cell(0,9,utf8_decode($sousTitre),1,1,'L',true);
        // Saut de ligne
        $this->Ln(0);

    }
    
    function partie($libelle, $nbLn) {
        $this->SetFont('');
        // Titre
        $this->Cell(0,9,utf8_decode($libelle));
        // Saut de ligne
        $this->Ln($nbLn);
    }
    
    // Tableau coloré
    function FancyTableFraisHorsForfait($header, $data)
    {
        // Couleurs, épaisseur du trait et police grasse
        $this->SetFillColor(0,80,180);
        $this->SetTextColor(255);
        $this->SetDrawColor(0,80,180);
        $this->SetLineWidth(.3);
        $this->SetFont('','B');
        // En-tête
        $w = array(30, 125, 35);
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C',true);
        $this->Ln();
        // Restauration des couleurs et de la police
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Données
        $fill = false;
        foreach($data as $row)
        {
            $this->Cell($w[0],6,utf8_decode($row[4]),'LR',0,'C',$fill);
            $this->Cell($w[1],6,utf8_decode($row[3]),'LR',0,'L',$fill);
            $this->Cell($w[2],6,number_format($row[5],2,',',''),'LR',0,'R',$fill);
            $this->Ln();
            $fill = !$fill;
        }
        // Trait de terminaison
        $this->Cell(array_sum($w),0,'','T');
        $this->Ln(10);


    }
    
    function signature($signature) {
        $this->SetFont('');
        // Titre
        $this->Cell(0,9,utf8_decode($signature),0,0,'R',FALSE);
        // Saut de ligne
        $this->Ln(10);
    }
    
}

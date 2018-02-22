<?php
/** 
 * Fonctions pour l'application GSB
 *
 * Classe qui contient toutes les fonctions statiques de l'application GSB
 * @package default
 * @author Cheri Bibi, Thierry Raimond
 * @version    1.0
 */
Class FctGsb {
    /**
     * Teste si un quelconque employé est connecté
     * @return boolean vrai ou faux 
     */
    public static function estConnecte(){
        return isset($_SESSION['idVisiteur']);
    }
    /**
     * Enregistre dans une variable session les infos d'un employe
     * @param string $id 
     * @param string $nom
     * @param string $prenom
     * @param string $metier
     */
    public static function connecter($id,$nom,$prenom, $metier){
        $_SESSION['idVisiteur'] = $id; 
        $_SESSION['nom'] = $nom;
        $_SESSION['prenom'] = $prenom;
        $_SESSION['metier'] = $metier;
        $_SESSION['idEmploye'] = "null"; // pour l'admnistration des frais des comptables
    }
    /**
     * Détruit la session active
     */
    public static function deconnecter(){
        session_destroy();
    }
    /**
     * Transforme une date au format français jj/mm/aaaa vers le format anglais 
     * aaaa-mm-jj
     * 
     * @assert ('01/01/2016') == '2016-01-01'
     * 
     * @param date $madate au format  jj/mm/aaaa
     * @return date la date au format anglais aaaa-mm-jj
     */
    public static function dateFrancaisVersAnglais($maDate){
        @list($jour,$mois,$annee) = explode('/',$maDate);
        return date('Y-m-d',mktime(0,0,0,$mois,$jour,$annee));
    }
    /**
     * Transforme une date au format format anglais aaaa-mm-jj vers le format français 
     * jj/mm/aaaa 
     *
     * @assert ('2016-02-02') == '02/02/2016'
     * 
     * @param date $madate au format  aaaa-mm-jj
     * @return date la date au format français jj/mm/aaaa
     */
    public static function dateAnglaisVersFrancais($maDate){
        @list($annee,$mois,$jour)=explode('-',$maDate);
        $date="$jour"."/".$mois."/".$annee;
        return $date;
    }
    /**
     * retourne le mois au format aaaamm selon le jour dans le mois
     *
     * @assert ('02/02/2016') == "201602"
     * 
     * @param date $date au format  jj/mm/aaaa
     * @return string le mois au format aaaamm
     */
    public static function getMois($date){
		@list($jour,$mois,$annee) = explode('/',$date);
		if(strlen($mois) == 1){
			$mois = "0".$mois;
        }
		return $annee.$mois;
    }
    /* gestion des erreurs*/
    /**
     * Indique si une valeur est un entier positif ou nul
     *
     * @assert (1) == true
     * @assert (0) == true
     * @assert ('r') == false
     * 
     * @param $valeur
     * @return boolean vrai ou faux
     */
    public static function estEntierPositif($valeur) {
        return preg_match("/[^0-9]/", $valeur) == 0;
    }
    /**
     * Indique si un tableau de valeurs est constitué d'entiers positifs ou nuls
     *
     * @assert (array(0,1,2)) == true
     * @assert (array(-1,'n',1)) == false
     * 
     * @param array[] $tabEntiers : le tableau
     * @return boolean vrai ou faux
     */
    public static function estTableauEntiers($tabEntiers) {
        $ok = true;
        foreach($tabEntiers as $unEntier){
        	if(!self::estEntierPositif($unEntier)){
             	$ok=false; 
            }
        }
        return $ok;
    }
    /**
     * Vérifie si une date est supérieur à la date actuelle
     *
     * @assert ('01/02/9999') == true
     * @assert ('01/01/1000') == false
     * 
     * @param date $dateTestee au format dd/mm/aaaa
     * @return boolean vrai ou faux
     */
    public static function estDateSuperieure($dateTestee) {
        @list($jour,$mois,$annee) = explode('/',date("d/m/Y"));
        @list($jourTeste,$moisTeste,$anneeTeste) = explode('/',$dateTestee);
        return ($anneeTeste.$moisTeste.$jourTeste > $annee.$mois.$jour);
    }
    /**
     * Vérifie si une date est inférieure d'un an à la date actuelle
     *
     * @assert ('01/01/2016') == false
     * @assert ('01/01/2013') == true
     * 
     * @param date $dateTestee au format jj/mm/aaaa
     * @return boolean vrai ou faux
     */
    public static function estDateDepassee($dateTestee) {
        $dateActuelle=date("d/m/Y");
        @list($jour,$mois,$annee) = explode('/',$dateActuelle);
        $annee--;
        $AnPasse = $annee.$mois.$jour;
        @list($jourTeste,$moisTeste,$anneeTeste) = explode('/',$dateTestee);
        return ($anneeTeste.$moisTeste.$jourTeste < $AnPasse); 
    }
    /**
     * Vérifie la validité du format d'une date française jj/mm/aaaa 
     *
     * @assert ('01/01/2016') == true
     * @assert ('20160101') == false
     * 
     * @param date $date 
     * @return boolean vrai ou faux
     */
    public static function estDateValide($date) {
        $tabDate = explode('/',$date);
        $dateOK = true;
        if (count($tabDate) != 3) {
            $dateOK = false;
        }
        else {
            if (!self::estTableauEntiers($tabDate)) {
                $dateOK = false;
            }
            else {
                if (!checkdate($tabDate[1], $tabDate[0], $tabDate[2])) {
                    $dateOK = false;
                }
            }
        }
        return $dateOK;
    }
    /**
     * Vérifie que le tableau de frais ne contient que des valeurs numériques 
     * 
     * @assert (array(0,1,2)) == true
     * @assert (array(-1,'n',1)) == false
     * 
     * @param integer $lesFrais 
     * @return boolean vrai ou faux
     */
    public static function lesQteFraisValides($lesFrais) {
        return self::estTableauEntiers($lesFrais);
    }
    /**
     * Vérifie la validité des trois arguments : la date, le libellé du frais et le 
     * montant 
     *
     * des message d'erreurs sont ajoutés au tableau des erreurs
     * @param date $dateFrais au format jj/mm/aaaa
     * @param string $libelle 
     * @param integer $montant
     */
    public static function valideInfosFrais($dateFrais,$libelle,$montant) {
        if($dateFrais==""){
            self::ajouterErreur("Le champ date ne doit pas être vide");
        }
        else {
            if(!self::estDatevalide($dateFrais)){
                self::ajouterErreur("Date invalide");
            }	
            else {
                if(self::estDateDepassee($dateFrais)){
                    self::ajouterErreur("date d'enregistrement du frais dépassé, "
                        . "plus de 1 an");
                }
                else {
                    if(self::estDateSuperieure($dateFrais)) {
                        self::ajouterErreur("date d'enregistrement du frais est "
                            . "supérieure à aujourd'hui");
                    }
                }
            }
        }
        if($libelle == "") {
            self::ajouterErreur("Le champ description ne peut pas être vide");
        }
        if($montant == "") {
            self::ajouterErreur("Le champ montant ne peut pas être vide");
        }
        else
            if( !is_numeric($montant) ) {
                self::ajouterErreur("Le champ montant doit être numérique");
            }
    }
    /**
     * Ajoute le libellé d'une erreur au tableau des erreurs 
     * @param string $msg : le libellé de l'erreur 
     */
    public static function ajouterErreur($msg) {
        if (!isset($_REQUEST['erreurs'])) {
            $_REQUEST['erreurs']=array();
        } 
        $_REQUEST['erreurs'][]=$msg;
    }
    /**
     * Retoune le nombre de lignes du tableau des erreurs 
     * @return le nombre d'erreurs
     */
    public static function nbErreurs(){
        if (!isset($_REQUEST['erreurs'])){
            return 0;
        }
        else{
            return count($_REQUEST['erreurs']);
        }
    }
    /**
     * Vérifie si la date actuelle est comprise entre le dix du mois actuel et le
     * vingt du mois actuel
     * @return boolean vrai ou faux
     */
    public static function estDateCampagneValidation() {
        @list($jour,$mois,$annee) = explode('/',date("d/m/Y"));
        $dateActuelle = $annee.$mois.$jour;
        $dixDuMoisActuel = $annee.$mois."10";
        $vingtDuMoisActuel = $annee.$mois."20";
        return (($dateActuelle >= $dixDuMoisActuel) 
            && ($dateActuelle <= $vingtDuMoisActuel));
    }
    /**
     * Enregistre dans une variable de session le nombre de fiches de frais en fonction 
     * de son état
     */
    public static function actualiserEtatFiche() {
        $pdo= \PdoGsb::getPdoGsb();
        $_SESSION['nbFichesSaisieEnCours'] = $pdo->getNbFichesEtat('CR');
        $_SESSION['nbFichesACloturer'] = $pdo->getNbFichesACloturer();
        $_SESSION['nbFichesCloturees'] = $pdo->getNbFichesEtat('CL');
        $_SESSION['nbFichesValidees'] = $pdo->getNbFichesEtat('VA');
        $_SESSION['nbFichesRemboursees'] = $pdo->getNbFichesEtat('RB');
    }
    /**
     * Echappe les caractères spéciaux d'une chaîne.
     *
     * Envoie la chaîne $str échappée, càd avec les caractères considérés 
     * spéciaux par MySql (tq la quote simple) précédés d'un \, ce qui annule 
     * leur effet spécial
     *
     * @param string $str chaîne à échapper
     * @return string  
     */    
    public static function filtrerChainePourBD($str) {
        if ( ! get_magic_quotes_gpc() ) { 
            $str = addslashes($str);
        }
        return $str;
    }
    
    /**
     * Vérifie la validité des trois arguments : la date, le libellé du frais et le 
     * montant 
     *
     * des message d'erreurs sont ajoutés à une chaine de caractères
     * @param date $dateFrais au format jj/mm/aaaa
     * @param string $libelle 
     * @param integer $montant
     */
    public static function validerInfosFrais($dateFrais,$libelle,$montant) {
        $resultat = "";
        if($dateFrais==""){
            $resultat = $resultat ."Le champ date ne doit pas etre vide;";
        }
        else {
            if(!self::estDatevalide($dateFrais)){
                $resultat = $resultat ."Date invalide";
            }	
            else {
                if(self::estDateDepassee($dateFrais)){
                    $resultat = $resultat ."date d enregistrement du frais depasse, "
                        . "plus de 1 an";
                }
                else {
                    if(self::estDateSuperieure($dateFrais)) {
                        $resultat = $resultat ."date d enregistrement du frais est "
                            . "superieure e aujourd hui";
                    }
                }
            }
        }
        if($libelle == "") {
            $resultat = $resultat ."Le champ description ne peut pas etre vide";
        }
        if($montant == "") {
            $resultat = $resultat ."Le champ montant ne peut pas etre vide";
        }
        else
            if( !is_numeric($montant) ) {
                $resultat = $resultat ."Le champ montant doit etre numerique";
            }
        return $resultat;
    }
}
?>
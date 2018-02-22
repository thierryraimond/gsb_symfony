<?php
/** 
 * Classe d'accès aux données. 
 * 
 * Utilise les services de la classe PDO
 * pour l'application GSB
 * Les attributs sont tous statiques,
 * les 4 premiers pour la connexion
 * $monPdo de type PDO 
 * $monPdoGsb qui contiendra l'unique instance de la classe
 * @package default
 * @author Cheri Bibi
 * @version    1.0
 * @link       http://www.php.net/manual/fr/book.pdo.php
 */
class PdoGsb {   		
      	private static $serveur='mysql:host=trsrv.ddns.net';
      	private static $bdd='dbname=gsb';   		
      	private static $user='root' ;    		
      	private static $mdp='london06' ;	
		private static $monPdo;
		private static $monPdoGsb=null;
    /**
     * Constructeur privé, crée l'instance de PDO qui sera sollicitée
     * pour toutes les méthodes de la classe
     */				
	private function __construct(){
    	PdoGsb::$monPdo = new PDO(PdoGsb::$serveur.';'.PdoGsb::$bdd, PdoGsb::$user, PdoGsb::$mdp); 
		PdoGsb::$monPdo->query("SET CHARACTER SET utf8");
	}
	public function _destruct(){
		PdoGsb::$monPdo = null;
	}
    /**
     * Fonction statique qui crée l'unique instance de la classe
     * 
     * Appel : $instancePdoGsb = PdoGsb::getPdoGsb();
     * @return objet l'unique objet de la classe PdoGsb
     */
	public static function getPdoGsb(){
		if(PdoGsb::$monPdoGsb==null){
			PdoGsb::$monPdoGsb= new PdoGsb();
		}
		return PdoGsb::$monPdoGsb;  
	}
    /**
    * Retourne les informations d'un employé
    * 
    * @param string $login 
    * @param string $mdp
    * @return array l'id, le nom et le prénom sous la forme d'un tableau associatif 
    */
	public function getInfosEmploye($login, $mdp) {
		$req = "SELECT employe.id as id, employe.nom as nom,
				employe.prenom as prenom, employe.idMetier as idMetier,
				metier.libelle as metier
				FROM employe inner join metier on employe.idMetier = metier.id 
				WHERE employe.login='$login' and employe.mdp='$mdp'";
		$rs = PdoGsb::$monPdo->query($req);
		$ligne = $rs->fetch();
		return $ligne;
	}
    /**
     * Retourne sous forme d'un tableau associatif toutes les lignes de frais hors 
     * forfait concernées par les deux arguments
     * 
     * La boucle foreach ne peut être utilisée ici car on procède
     * à une modification de la structure itérée - transformation du champ date-
     * @param string $idVisiteur 
     * @param string $mois sous la forme aaaamm
     * @return array[][] tous les champs des lignes de frais hors forfait sous la 
     * forme d'un tableau associatif 
     */
	public function getLesFraisHorsForfait($idVisiteur,$mois){
	    $req = "SELECT * 
	    		FROM lignefraishorsforfait 
	    		WHERE lignefraishorsforfait.idvisiteur ='$idVisiteur'
	    		AND lignefraishorsforfait.mois = '$mois' ";	
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		$nbLignes = count($lesLignes);
		for ($i=0; $i<$nbLignes; $i++){
			$date = $lesLignes[$i]['date'];
			$lesLignes[$i]['date'] = \FctGsb::dateAnglaisVersFrancais($date);
		}
		return $lesLignes; 
	}
    /**
     * Retourne le nombre de justificatif d'un visiteur pour un mois donné
     * @param string $idVisiteur 
     * @param string $mois sous la forme aaaamm
     * @return integer le nombre entier de justificatifs 
     */
	public function getNbjustificatifs($idVisiteur, $mois){
		$req = "SELECT fichefrais.nbjustificatifs as nb
				FROM fichefrais 
				WHERE fichefrais.idvisiteur ='$idVisiteur' 
				AND fichefrais.mois = '$mois'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		return $laLigne['nb'];
	}
    /**
     * Retourne sous forme d'un tableau associatif toutes les lignes de frais au forfait
     * concernées par les deux arguments
     * @param string $idVisiteur 
     * @param string $mois sous la forme aaaamm
     * @return array[] l'id, le libelle, le montant du forfait et la quantité sous la forme d'un tableau associatif 
     */
	public function getLesFraisForfait($idVisiteur, $mois){
		$req = "SELECT fraisforfait.id as idfrais, fraisforfait.libelle as libelle, 
		fraisforfait.montant as forfaitmontant, lignefraisforfait.quantite as quantite
		FROM lignefraisforfait INNER JOIN fraisforfait 
		ON fraisforfait.id = lignefraisforfait.idfraisforfait
		WHERE lignefraisforfait.idvisiteur ='$idVisiteur'
        AND lignefraisforfait.mois='$mois' 
		ORDER BY lignefraisforfait.idfraisforfait";	
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes; 
	}
    /**
     * Retourne tous les id de la table FraisForfait
     * 
     * @return array[] un tableau associatif 
     */
	public function getLesIdFrais(){
		$req = "SELECT fraisforfait.id as idfrais 
				FROM fraisforfait
				ORDER BY fraisforfait.id";
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes;
	}
    /**
     * Met à jour la table ligneFraisForfait
     * 
     * Met à jour la table ligneFraisForfait pour un visiteur et
     * un mois donné en enregistrant les nouveaux montants
     * @param string $idVisiteur 
     * @param string $mois sous la forme aaaamm
     * @param array[] $lesFrais tableau associatif de clé idFrais et de valeur la quantité pour ce frais
     * @return array[] un tableau associatif 
     */
	public function majFraisForfait($idVisiteur, $mois, $lesFrais){
		$lesCles = array_keys($lesFrais);
		foreach($lesCles as $unIdFrais){
			$qte = $lesFrais[$unIdFrais];
			$req = "update lignefraisforfait set lignefraisforfait.quantite = $qte
			where lignefraisforfait.idvisiteur = '$idVisiteur' and lignefraisforfait.mois = '$mois'
			and lignefraisforfait.idfraisforfait = '$unIdFrais'";
			PdoGsb::$monPdo->exec($req);
		}
	}
    /**
     * met à jour le nombre de justificatifs de la table ficheFrais
     * pour le mois et le visiteur concerné
     * @param string $idVisiteur 
     * @param string $mois sous la forme aaaamm
     */
	public function majNbJustificatifs($idVisiteur, $mois, $nbJustificatifs) {
		$req = "UPDATE fichefrais
                SET nbjustificatifs = $nbJustificatifs 
                WHERE fichefrais.idvisiteur = '$idVisiteur'
                AND fichefrais.mois = '$mois'";
		PdoGsb::$monPdo->exec($req);	
	}
    /**
     * Teste si un visiteur possède une fiche de frais pour le mois passé en argument
     * @param string $idVisiteur 
     * @param string $mois sous la forme aaaamm
     * @return boolean vrai ou faux 
     */	
	public function estPremierFraisMois($idVisiteur,$mois)
	{
		$ok = false;
		$req = "SELECT COUNT(*) AS nblignesfrais 
                FROM fichefrais 
                WHERE fichefrais.mois = '$mois'
                AND fichefrais.idvisiteur = '$idVisiteur'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		if($laLigne['nblignesfrais'] == 0){
			$ok = true;
		}
		return $ok;
	}
    /**
     * Test si un visiteur a renseigné au moins une ligne de frais hors forfait pour 
     * le mois en argument
     * @param string $idVisiteur
     * @param string $mois sous la forme aaaamm
     * @return boolean vrai ou faux
     */	
	public function contientLigneFraisHorsForfait($idVisiteur,$mois){
		$req = "select count(*) as nblignesfraishorsforfait
				from lignefraishorsforfait
				where lignefraishorsforfait.mois = '$mois'
				and lignefraishorsforfait.idVisiteur = '$idVisiteur'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		if($laLigne['nblignesfraishorsforfait'] == 0){
			$ok = true;
		} else {
			$ok = false;
		}
		return $ok;
	}
    /**
     * Retourne le dernier mois en cours d'un visiteur
     * @param string $idVisiteur 
     * @return string le mois sous la forme aaaamm
     */	
	public function dernierMoisSaisi($idVisiteur){
		$req = "select max(mois) as dernierMois from fichefrais where fichefrais.idvisiteur = '$idVisiteur'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		$dernierMois = $laLigne['dernierMois'];
		return $dernierMois;
	}
    /**
     * Crée une nouvelle fiche de frais et les lignes de frais au forfait pour un
     *  visiteur et un mois donnés
     * 
     * récupère le dernier mois en cours de traitement, met à 'CL' son champs 
     * idEtat, crée une nouvelle fiche de frais
     * avec un idEtat à 'CR' et crée les lignes de frais forfait de quantités nulles 
     * @param string $idVisiteur 
     * @param string $mois sous la forme aaaamm
     */
	public function creeNouvellesLignesFrais($idVisiteur,$mois){
		//retourne le dernier mois de la fiche de frais en cours d'un visiteur
		$dernierMois = $this->dernierMoisSaisi($idVisiteur);
		//retourne les informations d'une fiche de frais d'un visiteur 
		//pour un mois donné
		$laDerniereFiche = $this->getLesInfosFicheFrais($idVisiteur,$dernierMois);
		//si la fiche du dernier mois en cours est à l'état "saisi"
		//alors le modifie à l'état "clôturé"
		if($laDerniereFiche['idEtat']=='CR'){
				$this->majEtatFicheFrais($idVisiteur, $dernierMois,'CL');				
		}
		//création de la nouvelle fiche du mois en cours 
		$req = "INSERT INTO fichefrais(idvisiteur,mois,nbJustificatifs,
				montantValide,dateModif,idEtat, pdf)
				VALUES('$idVisiteur','$mois',0,0,now(),'CR',1)";
		PdoGsb::$monPdo->exec($req);
		//Retourne tous les id de la table FraisForfait puis pour
		//chaque id ajoute une ligne de frais forfait de quantités nulles
		$lesIdFrais = $this->getLesIdFrais();
		foreach($lesIdFrais as $uneLigneIdFrais){
			$unIdFrais = $uneLigneIdFrais['idfrais'];
			$req = "INSERT INTO lignefraisforfait(idvisiteur,mois,idFraisForfait,
					quantite) 
					VALUES('$idVisiteur','$mois','$unIdFrais',0)";
			PdoGsb::$monPdo->exec($req);
		 }
	}
    /**
     * Crée un nouveau frais hors forfait pour un visiteur un mois donné
     * à partir des informations fournies en paramètre
     *
     * @param string $idVisiteur 
     * @param string $mois sous la forme aaaamm
     * @param string $libelle : le libelle du frais
     * @param date $date : la date du frais au format français jj//mm/aaaa
     * @param integer $montant : le montant
     */
	public function creeNouveauFraisHorsForfait($idVisiteur,$mois,$libelle,$date,$montant){
		$dateFr = \FctGsb::dateFrancaisVersAnglais($date);
		$req = "INSERT INTO lignefraishorsforfait 
		values('','$idVisiteur','$mois','".\FctGsb::filtrerChainePourBD($libelle)."','$dateFr','$montant')";
		PdoGsb::$monPdo->exec($req);
	}
    /**
     * Supprime le frais hors forfait dont l'id est passé en argument
     * @param $idFrais 
     */
	public function supprimerFraisHorsForfait($idFrais){
		$req = "delete from lignefraishorsforfait where lignefraishorsforfait.id =$idFrais ";
		PdoGsb::$monPdo->exec($req);
	}
    /**
     * Retourne les mois pour lesquel un visiteur a une fiche de frais 
     * @param string $idVisiteur 
     * @return array[][] un tableau associatif de clé un mois -aaaamm- et de valeurs 
     * l'année et le mois correspondant 
     */
	public function getLesMoisDisponibles($idVisiteur){
		$req = "SELECT fichefrais.mois as mois 
				FROM  fichefrais 
				WHERE fichefrais.idvisiteur ='$idVisiteur'
				ORDER BY fichefrais.mois desc ";
		$res = PdoGsb::$monPdo->query($req);
		$lesMois = array();
		$laLigne = $res->fetch();
		while($laLigne != null)	{
			$mois = $laLigne['mois'];
			$numAnnee = substr( $mois,0,4);
			$numMois = substr( $mois,4,2);
			$lesMois["$mois"] = array(
				"mois" => "$mois",
				"numAnnee" => "$numAnnee",
				"numMois" => "$numMois"
			);
			$laLigne = $res->fetch(); 		
		}
		return $lesMois;
	}
    /**
     * Retourne les visiteurs 
     * @return array[] tous les champs des lignes employés sous la forme d'un tableau
     *  associatif
     */
	public function getLesVisiteurs() {
		$req = "SELECT employe.id as id, employe.prenom as prenom, employe.nom as nom
				FROM employe INNER JOIN metier ON employe.idMetier = metier.id
                WHERE metier.libelle = 'Visiteur'
				ORDER BY employe.id";
		$res = PdoGsb::$monPdo->query($req);
		$lesVisiteurs = $res->fetchAll();
		return $lesVisiteurs;
	}	
    /**
     * Retourne les informations d'une fiche de frais d'un visiteur pour un mois 
     * donné
     * @param string $idVisiteur 
     * @param string $mois sous la forme aaaamm
     * @return array[] un tableau avec des champs de jointure entre une fiche de 
     * frais et la ligne d'état 
     */	
	public function getLesInfosFicheFrais($idVisiteur,$mois) {
		$req = "SELECT ficheFrais.idEtat AS idEtat,
                ficheFrais.dateModif AS dateModif, ficheFrais.nbJustificatifs AS 
                nbJustificatifs, ficheFrais.montantValide AS montantValide, 
                ficheFrais.pdf AS pdf, etat.libelle AS libEtat 
                FROM fichefrais INNER JOIN Etat ON ficheFrais.idEtat = Etat.id 
                WHERE fichefrais.idvisiteur ='$idVisiteur'
                AND fichefrais.mois = '$mois'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		return $laLigne;
	}
    /**
     * Modifie l'état et la date de modification d'une fiche de frais
     *
     * Modifie le champ idEtat et met la date de modif à aujourd'hui
     * @param string $idVisiteur 
     * @param string $mois sous la forme aaaamm
     */
	public function majEtatFicheFrais($idVisiteur,$mois,$etat){
		$req = "UPDATE ficheFrais 
				SET idEtat = '$etat', dateModif = now(), pdf = 1
				WHERE fichefrais.idvisiteur ='$idVisiteur' 
				AND fichefrais.mois = '$mois'";
		PdoGsb::$monPdo->exec($req);
	}
    /**
     * Retourne le prenom et le nom d'un employé
     * @param string $id
     * @return array[] le nom et le prénom sous la forme d'un tableau associatif
     */
	public function getPrenomNomEmploye($id) {
		$req = "SELECT employe.nom as nom, employe.prenom as prenom
				FROM employe 
				WHERE employe.id='$id'";
		$rs = PdoGsb::$monPdo->query($req);
		$ligne = $rs->fetch();
		return $ligne;
	}
    /**
     * Retourne le nombre de fiches de frais en fonction de l'état défini en 
     * paramètre
     * @param string $idEtat
     * @return integer le nombre de fiches de frais en cours de saisie 
     */
	public function getNbFichesEtat($idEtat) {
		$req = "SELECT COUNT(idEtat) as nbFichesEtat 
				FROM fichefrais 
				WHERE idEtat='$idEtat'";
		$res = PdoGsb::$monPdo->query($req);
		$ligne = $res->fetch();
		$res->closeCursor();
		return $ligne['nbFichesEtat'];
	}
    /**
     * Retourne le nombre de fiches de frais à clôturer
     * @return integer le nombre de fiches de frais à clôturer
     */
	public function getNbFichesACloturer() {
		//recupère le mois actuel sous la forme aaaamm
		$moisActuel = \FctGsb::getMois(date("d/m/Y"));
		$req = "SELECT COUNT(idEtat) as nbFichesACloturer
				FROM fichefrais
				WHERE idEtat='CR'
				AND mois<'$moisActuel'";
		$res = PdoGsb::$monPdo->query($req);
		$ligne = $res->fetch();
		$res->closeCursor();
		return $ligne['nbFichesACloturer'];
	}
    /**
     * Modifie l'état 'CR' par 'CL' et la date de modification des fiches de frais
     * pour les fiches ayant l'état 'CR' et le mois de création de la fiche 
     * inférieure au mois actuel
     * @return integer le nombre de fiches de frais modifiées
     */
	public function majEtatFicheFraisCampagneValidation() {
		//recupère le mois actuel sous la forme aaaamm
		$moisActuel = \FctGsb::getMois(date("d/m/Y"));
		$req = "UPDATE ficheFrais
				SET idEtat = 'CL', dateModif = now(), pdf = 1
				WHERE fichefrais.idEtat ='CR'
				AND fichefrais.mois < '$moisActuel'";
		//prepare statement
		$stmt = PdoGsb::$monPdo->prepare($req);
		//execute la requête
		$stmt->execute();
		return $stmt->rowCount();
	}
    /**
     * Modifie le mois d'une ligne de frais hors forfait
     * 
     * Modifie le champ mois par la date citée en paramètre
     * @param integer $idFrais
     * @param string $mois sous la forme aaaamm
     */
	public function majMoisLigneHorsForfait($idFrais,$mois) {
		$req = "UPDATE lignefraishorsforfait 
				SET mois = '$mois'
				WHERE lignefraishorsforfait.id ='$idFrais'";
		PdoGsb::$monPdo->exec($req);
	}
    /**
     * Récupère le total des frais forfaitisés pour l'employé et le mois cités 
     * en paramètre
     * @param string $idVisiteur
     * @param string $mois sous la forme aaaamm
     * @return integer le total des frais forfaitisés pour l'employé et le mois en 
     * paramètre
     */
    public function getTotalFraisForfait($idVisiteur, $mois) {
        $req = "SELECT SUM(lignefraisforfait.quantite * fraisforfait.montant) AS 
                totalFraisForfait
                FROM lignefraisforfait INNER JOIN fraisforfait
                ON lignefraisforfait.idFraisForfait = fraisforfait.id
                WHERE lignefraisforfait.idvisiteur = '$idVisiteur'
                AND lignefraisforfait.mois = '$mois'";
        $res = PdoGsb::$monPdo->query($req);
		$ligne = $res->fetch();
		$res->closeCursor();
		return $ligne['totalFraisForfait'];
    }
    /**
     * Récupère le total des frais hors forfait pour l'employé et le mois cités 
     * en paramètre
     * @param string $idVisiteur
     * @param string $mois sous la forme aaaamm
     * @return integer le total des frais hors forfait pour l'employé et le mois en paramètre
     */
    public function getTotalFraisHorsForfait($idVisiteur, $mois) {
        $req = "SELECT SUM(montant) AS 
                totalFraisHorsForfait
                FROM lignefraishorsforfait 
                WHERE lignefraishorsforfait.idvisiteur = '$idVisiteur'
                AND lignefraishorsforfait.mois = '$mois'";
        $res = PdoGsb::$monPdo->query($req);
		$ligne = $res->fetch();
		$res->closeCursor();
		return $ligne['totalFraisHorsForfait'];
    }
    /**
     * Valide la fiche de frais d'un employé
     * 
     * Modifie le champ idEtat par 'VA', met la date de modif à aujourd'hui et met
     * à jour le champ montantValide par le montant en paramètre
     * 
     * @param string $idVisiteur 
     * @param string $mois sous la forme aaaamm
     * @param integer $total
     */
    public function validerFicheFrais($idVisiteur,$mois,$total) {
		$req = "UPDATE ficheFrais 
				SET idEtat = 'VA', dateModif = now(), montantValide = '$total', 
                pdf = 1
				WHERE fichefrais.idvisiteur ='$idVisiteur' 
				AND fichefrais.mois = '$mois'";
		PdoGsb::$monPdo->exec($req);
	}
    /**
     * Test si un visiteur possède une fiche de frais à l'état "Saisie en cours" pour
     * le mois passé en paramètre
     * @param string $idVisiteur
     * @param string $mois sous la forme aaaamm
     * @return boolean vrai ou faux
     */
    public function estEtatSaisieEnCours($idVisiteur,$mois) {
        $ok = true;
        $req = "SELECT  COUNT(*) as nblignesfrais
                FROM fichefrais
                WHERE fichefrais.idEtat = 'CR'
                AND fichefrais.mois = '$mois'
                AND fichefrais.idVisiteur = '$idVisiteur'";
        $res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		if($laLigne['nblignesfrais'] == 0){
			$ok = false;
		}
		return $ok;
    }
    /**
     * Retourne les fiches frais de l'état passé en argument
     * @param string $libEtat
     * @return array[] tous les champs des fiches avec l'état en argument sous la 
     * forme d'un tableau associatif
     */
    public function getLesFichesFraisParEtat($libEtat) {
        $req = "SELECT fichefrais.idVisiteur AS idVisiteur, fichefrais.mois AS mois,
                fichefrais.nbJustificatifs AS nbJustificatifs, 
                fichefrais.montantValide AS montantValide, fichefrais.dateModif AS
                dateModif, fichefrais.idEtat AS idEtat, employe.nom AS nom,
                employe.prenom AS prenom, etat.libelle AS libEtat
                FROM etat INNER JOIN (employe INNER JOIN fichefrais 
                ON employe.id = fichefrais.idVisiteur) ON etat.id = fichefrais.idEtat
                WHERE etat.libelle = '$libEtat'";
        $res = PdoGsb::$monPdo->query($req);
		$lesFichesFrais = $res->fetchAll();
		return $lesFichesFrais;    
    }
    /**
     * Modifie la valeur du champ pdf d'une fiche de frais
     *
     * Modifie le champ pdf avec la valeur en paramètre (0 = non éditable, 
     * 1 = éditable)
     * @param string $idVisiteur 
     * @param string $mois sous la forme aaaamm
     * @param integer $pdf 
     */
	public function majPdfFicheFrais($idVisiteur,$mois,$pdf){
		$req = "UPDATE ficheFrais 
				SET pdf = '$pdf'
				WHERE fichefrais.idvisiteur ='$idVisiteur' 
				AND fichefrais.mois = '$mois'";
		PdoGsb::$monPdo->exec($req);
	}
   /**
    * Retourne la quantité d'un frais forfait via l'idVisiteur, la date et l'idFraisForfait 
    * cités en paramètre
    * 
    * @param string $idVisiteur
    * @param string $mois
    * @param string $idFraisForfait
    * @return array la quantité d'un frais forfait via l'idVisiteur, la date et l'idFraisForfait 
    * ciités en paramètre 
    */
    public function getQteFraisForfait($idVisiteur, $mois, $idFraisForfait) {
	$req = "SELECT lignefraisforfait.quantite as quantite 
		FROM lignefraisforfait  
		WHERE lignefraisforfait.idvisiteur = '$idVisiteur' 
        	AND lignefraisforfait.mois = '$mois' 
		AND lignefraisforfait.idFraisForfait ='$idFraisForfait'";
	$rs = PdoGsb::$monPdo->query($req);
	$ligne = $rs->fetch();
        $rs->closeCursor();
	return $ligne;
    }
    /**
     * Modifie la quantité d'une ligne de frais forfait
     *
     * Modifie le champ quantite avec la valeur en paramètre
     * 
     * @param string $idVisiteur 
     * @param string $mois sous la forme aaaamm
     * @param string $idFraisForfait
     * @param string $quantite 
     */
    public function majQteLigneFraisForfait($idVisiteur, $mois, $idFraisForfait, $quantite){
		$req = "UPDATE lignefraisforfait 
                        SET quantite = '$quantite'
			WHERE lignefraisforfait.idvisiteur ='$idVisiteur' 
			AND lignefraisforfait.mois = '$mois'
                        AND lignefraisforfait.idFraisForfait = '$idFraisForfait'";
		PdoGsb::$monPdo->exec($req);
    }
}
?>
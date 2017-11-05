<?php

/**
 * Activity class for galette Subscription plugin
 *
 * PHP version 5
 *
 * Copyright © 2009-2016 The Galette Team
 *
 * This file is part of Galette (http://galette.tuxfamily.org).
 *
 * Galette is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Galette is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Galette. If not, see <http://www.gnu.org/licenses/>.
 */
 
use Galette\Entity\Group as Group;
use Zend\Db\Sql\Expression;
use Analog\Analog;

class Activity {

    const TABLE = 'activities';
    //clé primaire de la table activities
	const PK = 'id_group';

	//champs de la table activities de la bdd
    private $_fields = array(
        '_id_group' => 'integer',
        '_price1' => 'decimal',
        '_price2' => 'decimal',
        '_price3' => 'decimal',
        '_price4' => 'decimal',
        '_lieu' => 'text',
        '_jours' => 'text',
        '_horaires' => 'text',
        '_renseignements' => 'text',
		'_complet' => 'integer',
		'_autovalidation' => 'integer'
    );
	
	//variables le l'objet
    private $_id_group;
    private $_group_name = '';
    private $_price1 = 0.0;
    private $_price2 = 0.0;
    private $_price3 = 0.0;
    private $_price4 = 0.0;
    private $_lieu = '';
    private $_jours = '';
    private $_horaires = '';
    private $_renseignements = '';
	private $_complet=0;
    private $_autovalidation=0;
    
    
    

    /**
     * Construit une nouvellle activité à partir de la BDD (à partir de son ID) ou vierge
     * 
     * @param int|object $args Peut être null, un ID ou une ligne de la BDD
     */
    public function __construct($args = null) {
        global $zdb;

        if (is_int($args)) {
            try {
                $select = $zdb->select(SUBSCRIPTION_PREFIX . self::TABLE);
                $select->where(self::PK . ' = ' . $args);
                $results=$zdb->execute($select);
				if ($results->count() == 1) {
                    $this->_loadFromRS($results->current());
                }
                
            } catch (Exception $e) {
                Analog\Analog::log(
                        'Something went wrong :\'( | ' . $e->getMessage() . "\n" .
                        $e->getTraceAsString(), Analog\Analog::ERROR
                );
            }
        } else if (is_object($args)) {
            $this->_loadFromRS($args);
        }
    }

    

    /**
     * Protège les guillemets et apostrophes en les transformant en caractères qui ne gênent pas en HTML
     * 
     * @param string $str Chaîne à transformée
     * 
     * @return string Chaîne protégée
     */
    private static function protectQuote($str) {
        return str_replace(array('\'', '"'), array('’', '“'), $str);
    }

  
    /**
	 * Exécute une requête SQL pour savoir si l'id_group à créer est valide (est présent dans la table group)
     * Retourne 1 si l’id existe dans la table group et activity ->update
	 * 0 s’il n’est dans aucune table
	 * 2 si s’il existe dans la table group mais pas dans la table activity ->insert
	 * $result=0
	 *
     * @param Activity $object L'objet dont on cherche l'id group
     */
    static function is_id_group($object) 
		{
		global $zdb;
		$result=0;
		// Statut
		$req1 = $zdb->select(Galette\Entity\Group::TABLE);
		$req1->where(array('id_group'=>$object->id_group))
			 ->limit(1);
		$results=$zdb->execute($req1);		
		if ($results->count() == 1) 
			{
			$result=2;
			$req2 = $zdb->select(SUBSCRIPTION_PREFIX . self::TABLE);
			$req2->where(array('id_group'=> $object->id_group))
				->limit(1);
			$results2=$zdb->execute($req2);
			if ($results2->count() == 1) 
				{
				$result=1;
				}//fin du 2eme if
			}//fin du 1er if
		return $result;
		}//fin de la fonction
	
    /**
	 * Exécute une requête SQL pour savoir si l'activité est au complet
     * Retourne 1 si oui
	 * 0 sinon
	 * $result
	 *
     * @param 
     */
    public function is_full() 
		{
		global $zdb;
		$result=0;
			
		$req2 = $zdb->select(SUBSCRIPTION_PREFIX . self::TABLE);
		$req2->where(array('id_group'=> $this->_id_group,
						   'complet'=> '1'
						   ))
			->limit(1);
		$results2=$zdb->execute($req2);
		if ($results2->count() == 1) 
			{
			$result=1;
			}//fin du 2eme if
			
		return $result;
		}//fin de la fonction
    
	/**
	 * Exécute une requête SQL pour avoir la liste des groupes parents
     * Retourne un tableau de groups si il existe, 0 sinon
	 * 
     * @param void
     */
    static function get_parentgrouplist() 
		{
		global $zdb;
		$result=0;
			
		$select = $zdb->select(Group::TABLE);
		$select->where('parent_group IS NULL');
		$results=$zdb->execute($select);
		if ($results->count() > 0) 
			{
			$groups = array();
            foreach ( $results as $row ) 
				{
					$groups[] = new Group($row);
				}
            $result=$groups;
			}//fin du if
			
		return $result;
		}//fin de la fonction

	/**
	 * Exécute une requête SQL pour savoir si l'activité est parente ou non
     * Retourne 1 si elle est parente, 0 sinon
	 * 
     * @param void
     */
    public function is_parent_group() 
		{
		global $zdb;
		$result=0;
			
		$select = $zdb->select(Group::TABLE);
		$select->where(array(
							'parent_group IS NULL',
							'id_group'=> $this->_id_group
							));
		$results=$zdb->execute($select);
		if ($results->count() > 0) 
			{
			$result=1;
			}//fin du if
			
		return $result;
		}//fin de la fonction
	
	
  /** 
     * Enregistre l'élément en cours que ce soit en insert ou update
     * 
     * @return bool False si l'enregistrement a échoué, true si aucune erreur
     */
    public function store() {
        global $zdb;

        try {
            $values = array();

            foreach ($this->_fields as $k => $v) {
				$values[substr($k, 1)] = $this->$k;
				//si c'est un prix (price1, price2...)
				if(substr($k, 1,5)=='price')
					{
					$number=$values[substr($k, 1)];//float
					$number=number_format($number, 2, '.', '');//formatte l'affichage à 2 décimales, retourne un string avec un .
					$values[substr($k, 1)] =$number;//une insertion d'un float avec une virgule tronque la valeur, d'où la convertion en string #bug 71
					}
            }
			$res=$this->is_id_group($this);
			
			if ($res=='2') 
				{
				$insert = $zdb->insert(SUBSCRIPTION_PREFIX . self::TABLE, $values);
				$insert->values($values);
				$add = $zdb->execute($insert);
				if ($add->count() > 0) 
					{
						$this->_id_group = $zdb->driver->getLastGeneratedValue();
					} else {
							throw new Exception(_T("ACTIVITY.AJOUT ECHEC res=2"));
							}
				}//fin du if res==2 
			if ($res=='1') 
				{
				$update = $zdb->update(SUBSCRIPTION_PREFIX . self::TABLE);
				$update->set($values);
				$update->where(
                    self::PK . '=' . $this->_id_group
                );
				$edit = $zdb->execute($update);
				}//fin du if res==1
			else {
					//echo ('res=0');
				 }
            return true;
			}//fin du try
			catch (Exception $e) 
				{
					Analog\Analog::log(
                    'Something went wrong :\'( | ' . $e->getMessage() . "\n" .
                    $e->getTraceAsString(), Analog\Analog::ERROR
					);
					return false;
				}	
    }//fin de la fonction

	/**
     * remove all adherent from Group
     *
     * @param id_group, l'id du groupe contenant les adhérents
	 * Return 1 si ok, 0 sinon
     */
	 
	 static function remove($id_group) {
        global $zdb;
		$where=array(
					"id_group=".$id_group
					);
		$delete = $zdb->delete(Group::GROUPSUSERS_TABLE);
        $delete->where($where);
        $rem=$zdb->execute($delete);
		return $rem;
    }
	
	/**
     * Get groups list
     *
     * @param boolean $full Return full list
     *
     * @return Zend_Db_RowSet
     */
    static function getList($full = true)
    {
        global $zdb, $login;
        try {
            $select = $zdb->select(Group::TABLE, 'a');
            $select->join(
                array('b' => PREFIX_DB . Group::GROUPSUSERS_TABLE),
                'a.' . Group::PK . '=b.' . Group::PK,
                array('members' => new Expression(('count(b.' . Group::PK . ')'))), 'left');
			
            if ( $full !== true ) {
                $select->where('parent_group IS NULL');
            }

            $select->group('a.' . Group::PK)
                ->group('a.group_name')
                ->group('a.creation_date')
                ->group('a.parent_group')
                ->order('a.group_name ASC');
			$results = $zdb->execute($select);
			$groups = array();
           foreach ( $results as $row ) {
				 $groups[] = new Group($row);
            }
            return $groups;
        } catch (\Exception $e) {
            Analog::log(
                'Cannot list groups | ' . $e->getMessage(),
                Analog::WARNING
            );
        }
    }//fin de la fonction
  
    /**
	*
     * Exécute une requête SQL pour récupérer les données d'une activité
     * 
     * Ne retourne rien.
     * 
     * @param Activity $object L'activité cherchée contenant l'id_group
     */
    static function getActivity($object) {
        global $zdb;

        // Statut
        $select_id_grp = $zdb->select( SUBSCRIPTION_PREFIX . Activity::TABLE);
        $select_id_grp->join(PREFIX_DB . Galette\Entity\Group::TABLE, PREFIX_DB . Galette\Entity\Group::TABLE . '.id_group = ' . PREFIX_DB . SUBSCRIPTION_PREFIX . Activity::TABLE . '.id_group')
                ->where(array(PREFIX_DB . Galette\Entity\Group::TABLE.'.id_group'=> $object->id_group))
                ->limit(1);
        $results = $zdb->execute($select_id_grp);
		if ($results->count() == 1) {
            $activity = $results->current();
            $object->_id_group = $activity->id_group;
            $object->_group_name = $activity->group_name;
            $object->_price1 = $activity->price1;
            $object->_price2 = $activity->price2;
            $object->_price3 = $activity->price3;
            $object->_price4 = $activity->price4;
            $object->_lieu = $activity->lieu;
            $object->_jours = $activity->jours;
            $object->_horaires = $activity->horaires;
            $object->_renseignements = $activity->renseignements;
            $object->_complet = $activity->complet;
            $object->_autovalidation = $activity->autovalidation;
            
        }
    }

	    /** Copy past of function getDbFields() of Adherent with another table
     * Retrieve fields from database
     *
     * @return array
     */
    public static function getDbFields()
    {
        global $zdb;
        $columns = $zdb->getColumns(SUBSCRIPTION_PREFIX . self::TABLE);
        $fields = array();
        foreach ( $columns as $col ) {
            $fields[] = $col->getName();
        }
        return $fields;
    }
	
	
	 /**
	 * modifies le type des prix de string à float
	 *
	 * @param Price (string)
	 *
	 * @return price en float
	 */
	public function floatprice($price)
	{
		$number=str_replace(",", ".", $price);
		$number=floatval ($number);
		$number=number_format($number, 2, '.', '');
		$price2=round($number,2);
		return $price2;
	}
	
	
	 /**
	 * Check posted values validity
	 *
	 * @param array $values   All values to check, basically the $_POST array
	 *                        after sending the form
	 *
	 * @return true|array
	 */
	public function check($values)
	{
		global $zdb;
		$valid = '1';
		$fields = self::getDbFields();
		foreach ( $fields as $key ) {
				//first of all, let's sanitize values
				$key = strtolower($key);
				if ( isset($values[$key]) ) {
					$value = stripslashes(trim($values[$key]));
					$this->$key=$value;
					
					switch($key) 
						{
						//vérification des prix et formatage (remplacement , par . conversion en float limitation à 2 décimales et arrondi)
						case 'price1':
						case 'price2':
						case 'price3':
						case 'price4':
            
						if ( $value<0 ) 
							{
							$valid = '0';	
							}
						$number=str_replace(",", ".", $value);
						$number=floatval ($number);
						$number=number_format($number, 2, '.', '');
						$this->$key=round($number,2);
						}
				}				
			}
			return $valid;
	}			
 
    
  
    /**
     * Global getter method
     *
     * @param string $name name of the property we want to retrive
     *
     * @return false|object the called property
     */
    public function __get($name) {
        $rname = '_' . $name;
        if (substr($rname, 0, 3) == '___') {
            return false;
        }
        switch ($name) {
            case 'name':
                return str_replace('\'', '’', $this->_group_name);
            case 'price1':
                return number_format($this->_price1, 2, ',', ' ');
            case 'price2':
                return number_format($this->_price2, 2, ',', ' ');
            case 'price3':
                return number_format($this->_price3, 2, ',', ' ');
            case 'price4':
                return number_format($this->_price4, 2, ',', ' ');
            case 'jours':
                return str_replace('\'', '’', $this->_jours);
            case 'horaires':
                return str_replace('\'', '’', $this->_horaires);
            case 'renseignements':
                return str_replace('\'', '’', $this->_renseignements);
            default:
                return $this->$rname;
        }
    }

    /**
     * Global setter method
     *
     * @param string $name  name of the property we want to assign a value to
     * @param object $value a relevant value for the property
     *
     * @return void
     */
    public function __set($name, $value) {
        $rname = '_' . $name;
        $this->$rname = $value;
    }

}

?>

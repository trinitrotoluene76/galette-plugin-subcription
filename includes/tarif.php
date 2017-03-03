<?php
/**
 * Tarif for galette Subscription plugin
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
 *
 * @category  Plugins
 * @package   GaletteSubscription
 *
 * @author    Amaury FROMENT <amaury.froment@gmail.com>
 * @copyright 2009-2016 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   0.7.8
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7.8
 */
use Galette\Entity\DynamicFields as DynamicFields;
use Galette\DynamicFieldsTypes\DynamicFieldType as DynamicFieldType;

//Nom du champ dynamique comportant les différents choix
$Field_name='Appartenance';

//--------------------------------->récupération du statut:
$dyn_fields = new DynamicFields();

// declare dynamic field values
$adherent0 = $dyn_fields->getFields('adh', $id_adh, true);

// - declare dynamic fields for display
$disabled['dyn'] = array();
$dynamic_fields = $dyn_fields->prepareForDisplay(
    'adh',
    $adherent0,
    $disabled['dyn'],
    0
);
foreach ( $dynamic_fields as $k => $v ) 
	{
	//modification pour bug #40
	if($dynamic_fields[$k]['field_name'] == $Field_name || $dynamic_fields[$k]['field_name'] == $Field_name." (not translated)")
	//fin de la modif
		{
		//récupération des statuts (personnel NS, ext...)
		$form_name=$dynamic_fields[$k]['choices'];

		}
	}
//------------------------------------------------>récupération du statut fin


/**
 * Exécute une requête SQL retournant le statut (champ dynamique) de l'adhérent (ne fonctionne pas pour le super admin)
 * Retourne $statut
 * 
 * $statut=0 si il y a une erreur
 *
 * @param id_adh: l'id de l'adhérent et field_name: le nom du champ recherché, ici "Statut"
 */
 
	global $zdb;
		$statut=0;
		// dans un 1er temps on retourne le field_id et le field_val
		$select = $zdb->select(DynamicFields::TABLE, 'a');
		$select->join(
					array('b' => PREFIX_DB . DynamicFieldType::TABLE),
					'b.field_id = a.field_id'
					)
                ->where(array(
								'b.field_name'=> $Field_name,
								'a.item_id'=> $id_adh
							));
		$results = $zdb->execute($select);
		if ($results->count() == 1) 
			{
			$resultat= $results->current();
            $field_val=$resultat->field_val;
			$field_id=$resultat->field_id;
			
			//dans un 2eme temps on retourne la valeur du statut de l'adhérent
			$select2 = $zdb->select('field_contents_'.$field_id);
			$select2->where(array('id'=> $field_val));
			$results2 = $zdb->execute($select2);
			if ($results2->count() == 1) 
				{
				$resultat2= $results2->current();
				$statut=$resultat2->val;
				}//fin du 2eme if
			
			}//fin du 1er if
//--------------------------------------------------->

/**
 * compare la date de naissance de l'adhérent avec la date actuelle
 * retourne age_category=1 si <18ans
 * age_category=2 si <=25ans && >18ans
 * age_category=3 si >25ans
 * 
 */	
$birthdate= DateTime::createFromFormat(_T("Y-m-d"),$member->birthdate);
$today= new DateTime("now");
$age=$birthdate->diff($today);
$age=$age->format('%Y');
$age_limit1=18;
$age_limit2=25;

if($age<$age_limit1)
	{
	$age_category=1;
	}//fin du if
if($age<=$age_limit2 && $age>$age_limit1)
	{
	$age_category=2;
	}//fin du if
if($age>$age_limit2)
	{
	$age_category=3;
	}//fin du if

//---------------------------------------------------> 
	
/**
 * classe l'adhérent dans une catégorie tarifaire
 * retourne $category
 * $category=[0]=Personnel Nexter et assimilés
 * $category=[1]=Enfant du Personnel Nexter et assimilés <18 ans
 * $category=[2]=Enfant du Personnel Nexter et assimilés <=25 ans
 * $category=[3]=Extérieurs et Enfants du Personnel Nexter >25 ans


$form_name
array (size=7)
  0 => string '1-Personnel Nexter Satory' (length=25)
  1 => string '3-Personnel extérieur Nexter' (length=29)
  2 => string '1-Assistance technique, intérimaire, stagiaire, TNS MArs' (length=57)
  3 => string '1-Personnel retraité Nexter Satory' (length=35)
  4 => string '1-Personnel civil de la base de soutien' (length=39)
  5 => string '1-Personnel militaire de la base de soutien' (length=43)
  6 => string '1-Conjoint ou enfant du personnel Nexter Satory' (length=47)
  
	0 => Personnel Nexter
	1 => Famille Nexter (conjoint ou enfant)
	2 => Assistance technique, intérimaire, stagiaire, TNS MArs
	3 => Retraité Nexter ou conjoint
	4 => Base de Soutien ou famille (civil ou militaire)
	5 => Extérieur

 */

 //si personnel NS (hors conjoint/enfant)
if($statut!=$form_name[5] && $statut!=$form_name[1])
	{
	$category=0;
	}

else
	{
	$category=3;
	
	//si conjoint ou enfant de personelle NS
	if($statut==$form_name[1])
		{
		 switch ($age_category) 
			{
			case 1:
			$category=1; break;
			case 2:
			$category=2; break;
			case 3:
			$category=3; break;
			default:
			$category=3; break;
			}
		}
	}

//---------------------------------------------------> 
?>
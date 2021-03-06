<?php

/**
 * View activity for galette Subscription plugin
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
 
define('GALETTE_BASE_PATH', '../../');
require_once GALETTE_BASE_PATH . 'includes/galette.inc.php';
use Galette\Entity\Adherent as Adherent;
use Galette\Entity\Group as Group;

if (!$login->isLogged()) {
    header('location: ' . GALETTE_BASE_PATH . 'index.php');
    die();
}
$id_adh = get_numeric_form_value('id_adh', '');

if ( !$login->isSuperAdmin() ) {
    if ( !$login->isAdmin() && !$login->isStaff() && !$login->isGroupManager()
        || $login->isAdmin() && $id_adh == ''
        || $login->isStaff() && $id_adh == ''
        || $login->isGroupManager() && $id_adh == ''
    ) {
        $id_adh = $login->id;
    }
}
require_once '_config.inc.php';
// Si l'adhérent est un groupmanager, on charge l'activité correspondante (on a donc besoin de id_group)
$member = new Adherent();
//on rempli l'Adhérent par ses caractéristiques à l'aide de son id
$member->load($id_adh);
$picture=$member->picture->hasPicture();


	//passage des informations au template
	$tpl->assign('page_title', _T("View Activity"));
	//Set the path to the current plugin's templates,
	//but backup main Galette's template path before
	$orig_template_path = $tpl->template_dir;
	$tpl->template_dir = 'templates/' . $preferences->pref_theme;

		//création de l'objet activité
		$activity = new Activity();
		//récupération de l'id_group ainsi que le nom du groupe managé
		$activity->id_group = $_GET['id_group'];
		 
		//hydrate l'activité avec les données de la bdd. L'objet passé en paramètre doit être une activité avec un id_group valide
		$activity->getActivity($activity);
		$group = new Group();
		$group->load($activity->id_group);
		//affiche le nom,tel,mail du manager de group + pour changer la manager de group, allez dans la page gestion group
		$managers=$group->getManagers();
		
		//récupération de la liste des fichiers vierges
		$file= new File();
		$file->id_act=$activity->id_group;
		$file->vierge=1;
		$files_vierges=array();
		$files_vierges=$file->getFileListVierge();
		
		$tpl->assign('activity',$activity);
		$tpl->assign('managers',$managers);
		$tpl->assign('picture',$picture);
		$tpl->assign('files_vierges',$files_vierges);
		
$content = $tpl->fetch('view_activity.tpl', SUBSCRIPTION_SMARTY_PREFIX);
$tpl->assign('content', $content);
//Set path to main Galette's template
$tpl->template_dir = $orig_template_path;
$tpl->display('page.tpl', SUBSCRIPTION_SMARTY_PREFIX);
?>

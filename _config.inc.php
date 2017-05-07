<?php

/**
 * Configuration file for galette Subscription plugin
 *
 * PHP version 5
 *
 * Copyright © 2009-2017 The Galette Team
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

define('SUBSCRIPTION_PREFIX', 'subscription_');
define('SUBSCRIPTION_SMARTY_PREFIX', 'plugins_subscription');

//défini le chemin vers les classes pour l'ensemble du pluggin
require_once 'classes/activity.class.php';
require_once 'classes/subscription.class.php';
require_once 'classes/file.class.php';
require_once 'classes/followup.class.php';
?>

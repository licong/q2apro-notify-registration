<?php
/*
	Plugin Name: Notify on User Registration
	Plugin URI: http://www.q2apro.com/plugins/notify-registration
	Plugin Description: Notifies the admin or somebody else by email if a new user registers in the forum
	Plugin Version: 0.1
	Plugin Date: 2014-04-02
	Plugin Author: q2apro.com
	Plugin Author URI: http://www.q2apro.com/
	Plugin License: GPLv3
	Plugin Minimum Question2Answer Version: 1.5
	Plugin Update Check URI: https://raw.github.com/q2apro/q2a-notify-registration/master/qa-plugin.php

	This program is free software. You can redistribute and modify it 
	under the terms of the GNU General Public License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.gnu.org/licenses/gpl.html
	
*/

	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}

	// language file
	qa_register_plugin_phrases('q2apro-notify-registration-lang-*.php', 'q2apro_notifyregistration_lang');

	qa_register_plugin_module('event', 'q2apro-notify-registration.php', 'q2apro_notify_registration', 'Notify on User Registration');

/*
	Omit PHP closing tag to help avoid accidental output
*/
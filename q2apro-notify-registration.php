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
		header('Location: ../');
		exit;
	}

	class q2apro_notify_registration {

		function init_queries($tableslc) {
			// none
		}
		
		// option's value is requested but the option has not yet been set, set it
		function option_default($option) {
			switch($option) {
				case 'q2apro_notifyregistration_enabled':
					return 1; // true
				case 'q2apro_notifyregistration_mail':
					return qa_opt('feedback_email'); // true
				default:
					return null;				
			}
		}
		
		function admin_form(&$qa_content){
			// process the admin form if admin hit Save-Changes-button
			$ok = null;
			if (qa_clicked('q2apro_notifyregistration_save')) {
				qa_opt('q2apro_notifyregistration_enabled', (bool)qa_post_text('q2apro_notifyregistration_enabled')); // empty or 1
				qa_opt('q2apro_notifyregistration_mail', qa_post_text('q2apro_notifyregistration_mail')); // string
				$ok = qa_lang('admin/options_saved');
			}
			
			// form fields to display frontend for admin
			$fields = array();
			
			// enable or disable plugin
			$fields[] = array(
				'type' => 'checkbox',
				'label' => qa_lang('q2apro_notifyregistration_lang/enable_plugin'),
				'tags' => 'NAME="q2apro_notifyregistration_enabled"',
				'value' => qa_opt('q2apro_notifyregistration_enabled'),
			);

			$fields[] = array(
				'type' => 'input',
				'label' => qa_lang('q2apro_notifyregistration_lang/inform_mail'),
				'tags' => 'NAME="q2apro_notifyregistration_mail"',
				'value' => qa_opt('q2apro_notifyregistration_mail'),
			);
			
			return array(           
				'ok' => ($ok && !isset($error)) ? $ok : null,
				'fields' => $fields,
				'buttons' => array(
					array(
						'label' => qa_lang('main/save_button'),
						'tags' => 'name="q2apro_notifyregistration_save"',
					),
				),
			);
		}

		function process_event($event, $userid, $handle, $cookieid, $params) {
			
			if($event=='u_register' && qa_opt('q2apro_notifyregistration_enabled')) {
				// memo: $params holds only two keys: $params['email'] and $params['level']
				$emailto = qa_opt('q2apro_notifyregistration_mail');
				
				// get user data
				$userdata = qa_db_read_one_assoc(
									qa_db_query_sub('SELECT created,createip,email,level FROM `^users`
														WHERE userid = #', $userid ));
				
				// get ip address
				$ipaddress = long2ip($userdata['createip']);
				
				// get profile fields specified
				// not working with v1.6.3, see http://www.question2answer.org/qa/33178/
				/* $userMetas = qa_db_read_all_assoc( qa_db_query_sub('SELECT title,content FROM `^userprofile`
																	WHERE `userid` = #
																	AND `content` > ""
																	;', $userid) );
				
				$metacount = count($userMetas); // number of fields filled out
				$usermetaData = $metacount." profile fields specified:\n\n";
				if($metacount>0) {
					foreach($userMetas as $metaItem) {
						$usermetaData .= $metaItem['title'].': '.$metaItem['content'].'\n';
					}
				}
				*/

				// get country from IP 
				$client  = @$_SERVER['HTTP_CLIENT_IP'];
				$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
				$remote  = $_SERVER['REMOTE_ADDR'];
				$country  = 'Unknown';
				if(filter_var($client, FILTER_VALIDATE_IP)) {
					$ip = $client;
				}
				elseif(filter_var($forward, FILTER_VALIDATE_IP)) {
					$ip = $forward;
				}
				else {
					$ip = $remote;
				}
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "http://www.geoplugin.net/json.gp?ip=".$ip);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				$ip_data_in = curl_exec($ch); // string
				curl_close($ch);
				$ip_data = json_decode($ip_data_in,true);
				$ip_data = str_replace('&quot;', '"', $ip_data); // for PHP 5.2 see stackoverflow.com/questions/3110487/
				if($ip_data && $ip_data['geoplugin_countryName'] != null) {
					$country = $ip_data['geoplugin_countryName'];
				}

				
				// mail content
				$mailsubject = 'New user has registered in '.qa_opt('site_title'); 
				$mailbody = 'User:     '.$handle.'
Email:    '.$userdata['email'].'
Profile:  '.qa_opt('site_url').'user/'.$handle.'

Created:  '.$userdata['created'].'
IP:       '.$ipaddress.'
IP-Link:  '.qa_opt('site_url').'ip/'.$ipaddress.'
Country:  '.$country.'

User-ID:  '.$userid;
				
				// notify admin by email
				qa_send_notification(null, $emailto, null, $mailsubject, $mailbody, null);

			} // end u_register
				
		} // end process_event
	
	} // end class


/*
	Omit PHP closing tag to help avoid accidental output
*/
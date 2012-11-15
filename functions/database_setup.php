<?php

global $photoboard_db_version;
$photoboard_db_version = '1.0';

/**
 * Make sure custom tables are up to date
 */
function photoboard_update_db()
{
	global $wpdb, $photoboard_db_version;

	//no need to update if we are current
	if (get_site_option('photoboard_db_version') == $photoboard_db_version) return;

	$table_name = $wpdb->prefix . "photoboard_people";

	$sql = "CREATE TABLE $table_name (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` char(50) DEFAULT NULL,
  `last_name` char(50) DEFAULT NULL,
  `other_names` varchar(255) DEFAULT NULL,
  `photo` char(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
);
    );";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

	add_option( "photoboard_db_version", $photoboard_db_version );
}
//if( !defined('DOING_AJAX') || !DOING_AJAX )
//	add_action('admin_init', 'photoboard_update_db');

/**
 * Import photoboard data
 *
 * Looks for CSV file exported from Gravity Forms and imports.
 * Does not replace existing records, unless PHOTOBOARD_IMPORT_ALL is true
 */
add_action('admin_init', 'photoboard_data_import');
function photoboard_data_import()
{
	global $wpdb;

	if( !file_exists(PHOTOBOARD_IMPORT_FILE) ) return FALSE;
	iconv_set_encoding("internal_encoding", "UTF-8");

	$handle = fopen( PHOTOBOARD_IMPORT_FILE, 'r' );
	while( ($import = fgetcsv($handle, 0, ",")) !== FALSE )
	{
		//get field names from first row
		if( !isset($field_names) )
		{
			//remove utf-8 BOM
			if(substr($import[0], 0,3) == pack("CCC",0xef,0xbb,0xbf))
				$import[0]=trim( substr($import[0],3), '"' );

			$field_names = $import;

			//set field names
			foreach( $import as $key=>$field_name )
			{
				$field_name = trim($field_name);
				if( 'Name (First)'==$field_name ) $field_names[$key] = 'first_name';
				if( 'Name (Last)'==$field_name ) $field_names[$key] = 'last_name';
				if( 'Do you have any other/former names you are or were known by?'==$field_name ) $field_names[$key] = 'other_names_bool';
				if( 'Other/former names'==$field_name ) $field_names[$key] = 'other_names';
				if( 'Email'==$field_name ) $field_names[$key] = 'email';
				if( 'Upload your photo'==$field_name ) $field_names[$key] = 'photo';

				if( 'I would like a new photo taken for the online photoboard'==$field_name ) $field_names[$key] = 'new_photo';
				if( 'I am happy for you to use my existing photoboard photo'==$field_name ) $field_names[$key] = 'use_existing';
				if( 'I am happy for you to use any of my older photoboard photos.'==$field_name ) $field_names[$key] = 'use_any';
				if( 'I only want my photo to appear on a local version of the photoboard'==$field_name ) $field_names[$key] = 'local_only';

				if( 'Are you currently living in the community or surrounding area?'==$field_name ) $field_names[$key] = 'living_local';
				if( 'Your current role'==$field_name ) $field_names[$key] = 'current_role';
				if( 'What country are you originally from?'==$field_name ) $field_names[$key] = 'country_origin';
				if( 'What year did you first visit the community?'==$field_name ) $field_names[$key] = 'year_first_visit';
				if( 'What year did you leave the community?'==$field_name ) $field_names[$key] = 'year_leave';
				if( 'How long have you lived in the community?'==$field_name ) $field_names[$key] = 'how_long_in_community';
				if( 'Brief statement about yourself'==$field_name ) $field_names[$key] = 'statement';
				if( 'Location'==$field_name ) $field_names[$key] = 'location';
				if( 'What country do you currently live in?'==$field_name ) $field_names[$key] = 'country_current';

				if( 'Findhorn Foundation (LES department coworker)'==$field_name ) $field_names[$key] = 'ff_member';
				if( 'Findhorn Foundation (management or admin department)'==$field_name ) $field_names[$key] = 'ff_member_admin';
				if( 'Findhorn Foundation (long term guest)'==$field_name ) $field_names[$key] = 'ff_guest';

				if( 'Findhorn Foundation (committed volunteer)'==$field_name && !isset($ff_volunteer) )
				{
					$ff_volunteer = $field_names[$key] = 'ff_volunteer';
					continue;
				}

				if( 'Findhorn Foundation (other)'==$field_name  && !isset($ff_other) )
				{
					$ff_other = $field_names[$key] = 'ff_other';
					continue;
				}

				if( 'NFA (employee, committed volunteer or elected representative)'==$field_name  && !isset($nfa_staff) )
				{
					$nfa_staff = $field_names[$key] = 'nfa_staff';
					continue;
				}
				if( 'NFA (member)'==$field_name  && !isset($nfa_member) )
				{
					$nfa_member = $field_names[$key] = 'nfa_member';
					continue;
				}

				if( 'Employee or committed volunteer of other NFA member organisation'==$field_name && !isset($nfa_org_member) )
				{
					$nfa_org_member = $field_names[$key] = 'nfa_org_member';
					continue;
				}

				if( 'Other'==$field_name && !isset($other_affiliation) )
				{
					$other_affiliation = $field_names[$key] = 'other_affiliation';
					continue;
				}

				if( 'Findhorn Foundation (member, co-worker etc)'==$field_name ) $field_names[$key] = 'ff_member2';
				if( 'Findhorn Foundation (long term guest, but not member, co-worker etc)'==$field_name ) $field_names[$key] = 'ff_guest2';

				if( 'Findhorn Foundation (committed volunteer)'==$field_name && isset($ff_volunteer) ) $field_names[$key] = 'ff_volunteer2';
				if( 'Findhorn Foundation (other)'==$field_name  && isset($ff_other) ) $field_names[$key] = 'ff_other2';
				if( 'NFA (employee, committed volunteer or elected representative)'==$field_name  && isset($nfa_staff) ) $field_names[$key] = 'nfa_staff2';
				if( 'NFA (member)'==$field_name  && isset($nfa_member) ) $field_names[$key] = 'nfa_member2';
				if( 'Employee or committed volunteer of other NFA member organisation'==$field_name && isset($nfa_org_member) ) $field_names[$key] = 'nfa_org_member2';
				if( 'Other'==$field_name && isset($other_affiliation) ) $field_names[$key] = 'other_affiliation2';

				if( 'Other affiliations'==$field_name ) $field_names[$key] = 'other_affiliations';
				if( 'Anything else?'==$field_name ) $field_names[$key] = 'comment';

				if( 'Entry Date'==$field_name ) $field_names[$key] = 'mod_date';
				if( 'Entry Id'==$field_name ) $field_names[$key] = 'id';

				if( !empty($field_names[$key]) && strpos($field_names[$key], ' ') !== FALSE )
					$field_names[$key] = str_replace(' ', '_', $field_names[$key]);
			}
			continue;
		}

		$field_vals = array_combine( $field_names, $import );
		array_walk( $field_vals, function(&$t) {
			if( is_string($t) ) $t = trim($t);
		} );

		if( $field_vals['id']==104 )
		{
			$a=1;
		}

		//we are reimporting existing ID
		$record_exists = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}photoboard_people WHERE id={$field_vals['id']};" );

		//make sure we only have one record for any person
		//import is in reverse order, so if ID already exists we don't import any more
		$sql = $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}photoboard_people WHERE first_name='%s' AND last_name='%s' LIMIT 1;", $field_vals['first_name'], $field_vals['last_name'] );
		$prev_id = $wpdb->get_var( $sql );
		if( $prev_id && !$record_exists )
		{
			trigger_error("Duplicate record for {$field_vals['first_name']} {$field_vals['last_name']} (orig id:{$prev_id}, new id:{$field_vals['id']})", E_USER_NOTICE );
			continue;
		}

		if( $record_exists && !PHOTOBOARD_IMPORT_ALL ) continue;

		//normalise case on names where people entered ALL CAPS!
		foreach( array('first_name', 'last_name' ) as $f )
		{
			if( mb_strtoupper($field_vals[$f]) == $field_vals[$f] )
				$field_vals[$f] = mb_convert_case( $field_vals[$f], MB_CASE_TITLE );
		}

		//normalise year fields
		foreach( array( 'year_first_visit', 'year_leave' ) as $yr_field )
		{
			if( preg_match( '/[0-9]{4}/', $field_vals[ $yr_field ], $yrs ) )
				$field_vals[ $yr_field ] = $yrs[0];
			else
				$field_vals[ $yr_field ] = '';
		}

		//normalise country fields
		foreach( array( 'country_origin', 'country_current' ) as $ct_field )
		{
			$field_vals[ $ct_field ] = get_country_code( $field_vals[ $ct_field ] );
		}

		//set single booleans
		$bools = array( 'new_photo', 'use_existing', 'use_any', 'local_only', 'ff_member_admin' );
		foreach( $bools as $bool )
			if( !empty( $field_vals[$bool] ) ) $field_vals[$bool] = TRUE;

		//set all double booleans (which come from separate fields for old/current members)
		$bools = array( 'ff_member', 'ff_guest', 'ff_volunteer', 'ff_other', 'nfa_staff', 'nfa_member', 'nfa_org_member', 'other_affiliation' );
		foreach( $bools as $bool )
			if( !empty( $field_vals[$bool] ) || !empty( $field_vals[$bool.'2'] ) ) $field_vals[$bool] = TRUE;

		//other booleans
		$field_vals['living_local'] = (bool) ('living-locally'==$field_vals['living_local']);

		//if we can use any photo, we can also use existing
		if( $field_vals['use_any'] ) $field_vals['use_existing'] = 1;

		//if photo, move it to proper place and update field so it just has file name
		if( $field_vals['photo'] )
		{
			$photo = str_replace( PHOTOBOARD_PIX_URL_STRIP, '', $field_vals['photo'] );
			$photo_path = PHOTOBOARD_PIX_IMPORT_DIR . $photo;
			$file_name = basename( $photo_path );
			if( file_exists($photo_path) )
			{
				$p = @exif_read_data($photo_path);
				if( !empty($p['DateTime']) )
				{
					$photo_date = date('Y-m', strtotime($p['DateTime']) );
				}
				elseif( preg_match( '|.*/([0-9]{4})/([0-9]{2})/[^/].*$|', $photo_path, $m) )
				{
					$photo_date = "{$m[1]}-{$m[2]}";
				}

				$file_name = "{$photo_date}.jpg";
				@mkdir( PHOTOBOARD_PIX_DIR . "/{$field_vals['id']}", 0755, TRUE );
				copy( $photo_path, PHOTOBOARD_PIX_DIR . "/{$field_vals['id']}/{$file_name}");
			}
			$field_vals['photo'] = $file_name;
		}
		elseif( $field_vals['use_existing'] )
		{
			copy_existing_photo( $field_vals );
		}


		$no_sets = array( 'other_names_bool', 'new_photo', 'ff_member2', 'ff_guest2', 'ff_volunteer2', 'ff_other2', 'nfa_staff2', 'nfa_member2', 'nfa_org_member2', 'other_affiliation2', 'comment', 'Created_By_(User_Id)', 'Source_Url', 'Transaction_Id', 'Payment_Amount', 'Payment_Date', 'Payment_Status', 'Post_Id', 'User_Agent', 'User_IP' );
		foreach( $no_sets as $no_set )
			unset($field_vals[$no_set]);

		if( $record_exists )
		{
			$wpdb->update( "{$wpdb->prefix}photoboard_people", $field_vals, array( 'id'=>$field_vals['id'] ) );
		}
		else
		{
			$wpdb->insert( "{$wpdb->prefix}photoboard_people", $field_vals );
		}
	}

	unlink(PHOTOBOARD_IMPORT_FILE);
}

/**
 * Check any records which don't have a photo but could use an existing one
 */
function photoboard_update_from_existing_photos()
{
	global $wpdb;
	$sql = "SELECT id,first_name,last_name FROM {$wpdb->prefix}photoboard_people WHERE use_existing=1 AND photo=''";
	foreach( $wpdb->get_results( $sql, ARRAY_A ) as $entry )
	{
		if( copy_existing_photo( $entry ) )
			$wpdb->update( "{$wpdb->prefix}photoboard_people", array( 'photo'=>$entry['photo'] ), array( 'id'=>$entry['id'] ) );
		else
			$wpdb->update( "{$wpdb->prefix}photoboard_people", array( 'photo'=>'X' ), array( 'id'=>$entry['id'] ) );
	}
}
add_action( 'admin_init', 'photoboard_update_from_existing_photos' );

/**
 * Copy photo from existing photos dir to dir for this person
 *
 * @param $person
 *
 * @return bool
 */
function copy_existing_photo( &$person )
{
	//use existing photo
	$existing_name = remove_accents( strtolower($person['first_name'] . $person['last_name']) );
	$existing_name = str_replace( array("'", '"', '-', '_', ' ', ',', '.'), '', $existing_name );

	if( file_exists( PHOTOBOARD_OLD_PIX_IMPORT_DIR . '/' . $existing_name ) )
	{
		$file_name = photoboard_get_recent_photo( PHOTOBOARD_OLD_PIX_IMPORT_DIR . '/' . $existing_name );
		$person['photo'] = $file_name;
		@mkdir( PHOTOBOARD_PIX_DIR . "/{$person['id']}", 0755, TRUE );
		return copy( PHOTOBOARD_OLD_PIX_IMPORT_DIR . "/{$existing_name}/{$file_name}", PHOTOBOARD_PIX_DIR . "/{$person['id']}/{$file_name}");
	}

	return FALSE;
}

/**
 * Get the most recent photo from directory.
 *
 * Assumes that all files in directory are image files and named yyyy-mm.jpg
 *
 * @param $dir
 *
 * @return bool|mixed
 */
function photoboard_get_recent_photo( $dir )
{
	return array_pop( photoboard_get_all_photos($dir) );
}

/**
 * Gets all photos in form yyyy-mm.jpg for a person
 *
 * @param $dir
 */
function photoboard_get_all_photos( $dir )
{
	if( !is_dir($dir) ) return array();

	$photos = array();

	foreach( scandir( $dir ) as $file )
	{
		if( preg_match('|[0-9]{4}-[0-9]{2}\.jpg|', $file) )
			$photos[] = $file;
	}

	asort($photos);
	return $photos;
}

function get_country_name( $code )
{
	$countries = array(
		'AF'=>'Afghanistan',
		'AX'=>'Åland Islands',
		'AL'=>'Albania',
		'DZ'=>'Algeria',
		'AS'=>'American Samoa',
		'AD'=>'Andorra',
		'AO'=>'Angola',
		'AI'=>'Anguilla',
		'AQ'=>'Antarctica',
		'AG'=>'Antigua And Barbuda',
		'AR'=>'Argentina',
		'AM'=>'Armenia',
		'AW'=>'Aruba',
		'AU'=>'Australia',
		'AT'=>'Austria',
		'AZ'=>'Azerbaijan',
		'BS'=>'Bahamas',
		'BH'=>'Bahrain',
		'BD'=>'Bangladesh',
		'BB'=>'Barbados',
		'BY'=>'Belarus',
		'BE'=>'Belgium',
		'BZ'=>'Belize',
		'BJ'=>'Benin',
		'BM'=>'Bermuda',
		'BT'=>'Bhutan',
		'BO'=>'Bolivia',
		'BQ'=>'Bonaire, Sint Eustatius and Saba',
		'BA'=>'Bosnia and Herzegovina',
		'BW'=>'Botswana',
		'BV'=>'Bouvet Island',
		'BR'=>'Brazil',
		'IO'=>'British Indian Ocean Territory',
		'BN'=>'Brunei Darussalam',
		'BG'=>'Bulgaria',
		'BF'=>'Burkina Faso',
		'BI'=>'Burundi',
		'KH'=>'Cambodia',
		'CM'=>'Cameroon',
		'CA'=>'Canada',
		'CV'=>'Cape Verde',
		'KY'=>'Cayman Islands',
		'CF'=>'Central African Republic',
		'TD'=>'Chad',
		'CL'=>'Chile',
		'CN'=>'China',
		'CX'=>'Christmas Island',
		'CC'=>'Cocos (Keeling) Islands',
		'CO'=>'Colombia',
		'KM'=>'Comoros',
		'CG'=>'Congo',
		'CD'=>'Congo, the Democratic Republic of the',
		'CK'=>'Cook Islands',
		'CR'=>'Costa Rica',
		'CI'=>'Côte d\'Ivoire',
		'HR'=>'Croatia',
		'CU'=>'Cuba',
		'CW'=>'Curaçao',
		'CY'=>'Cyprus',
		'CZ'=>'Czech Republic',
		'DK'=>'Denmark',
		'DJ'=>'Djibouti',
		'DM'=>'Dominica',
		'DO'=>'Dominican Republic',
		'EC'=>'Ecuador',
		'EG'=>'Egypt',
		'SV'=>'El Salvador',
		'GQ'=>'Equatorial Guinea',
		'ER'=>'Eritrea',
		'EE'=>'Estonia',
		'ET'=>'Ethiopia',
		'FK'=>'Falkland Islands (Malvinas)',
		'FO'=>'Faroe Islands',
		'FJ'=>'Fiji',
		'FI'=>'Finland',
		'FR'=>'France',
		'GF'=>'French Guiana',
		'PF'=>'French Polynesia',
		'TF'=>'French Southern Territories',
		'GA'=>'Gabon',
		'GM'=>'Gambia',
		'GE'=>'Georgia',
		'DE'=>'Germany',
		'GH'=>'Ghana',
		'GI'=>'Gibraltar',
		'GR'=>'Greece',
		'GL'=>'Greenland',
		'GD'=>'Grenada',
		'GP'=>'Guadeloupe',
		'GU'=>'Guam',
		'GT'=>'Guatemala',
		'GG'=>'Guernsey',
		'GN'=>'Guinea',
		'GW'=>'Guinea-Bissau',
		'GY'=>'Guyana',
		'HT'=>'Haiti',
		'HM'=>'Heard Island and McDonald Islands',
		'VA'=>'Holy See (Vatican City State)',
		'HN'=>'Honduras',
		'HK'=>'Hong Kong',
		'HU'=>'Hungary',
		'IS'=>'Iceland',
		'IN'=>'India',
		'ID'=>'Indonesia',
		'IR'=>'Iran, Islamic Republic of',
		'IQ'=>'Iraq',
		'IE'=>'Ireland',
		'IM'=>'Isle of Man',
		'IL'=>'Israel',
		'IT'=>'Italy',
		'JM'=>'Jamaica',
		'JP'=>'Japan',
		'JE'=>'Jersey',
		'JO'=>'Jordan',
		'KZ'=>'Kazakhstan',
		'KE'=>'Kenya',
		'KI'=>'Kiribati',
		'KP'=>'Korea, Democratic People\'s Republic of',
		'KR'=>'Korea, Republic of',
		'KW'=>'Kuwait',
		'KG'=>'Kyrgyzstan',
		'LA'=>'Lao People\'s Democratic Republic',
		'LV'=>'Latvia',
		'LB'=>'Lebanon',
		'LS'=>'Lesotho',
		'LR'=>'Liberia',
		'LY'=>'Libyan Arab Jamahiriya',
		'LI'=>'Liechtenstein',
		'LT'=>'Lithuania',
		'LU'=>'Luxembourg',
		'MO'=>'Macao',
		'MK'=>'Macedonia, The Former Yugoslav Republic Of',
		'MG'=>'Madagascar',
		'MW'=>'Malawi',
		'MY'=>'Malaysia',
		'MV'=>'Maldives',
		'ML'=>'Mali',
		'MT'=>'Malta',
		'MH'=>'Marshall Islands',
		'MQ'=>'Martinique',
		'MR'=>'Mauritania',
		'MU'=>'Mauritius',
		'YT'=>'Mayotte',
		'MX'=>'Mexico',
		'FM'=>'Micronesia, Federated States of',
		'MD'=>'Moldova, Republic of',
		'MC'=>'Monaco',
		'MN'=>'Mongolia',
		'ME'=>'Montenegro',
		'MS'=>'Montserrat',
		'MA'=>'Morocco',
		'MZ'=>'Mozambique',
		'MM'=>'Myanmar',
		'NA'=>'Namibia',
		'NR'=>'Nauru',
		'NP'=>'Nepal',
		'NL'=>'Netherlands',
		'NC'=>'New Caledonia',
		'NZ'=>'New Zealand',
		'NI'=>'Nicaragua',
		'NE'=>'Niger',
		'NG'=>'Nigeria',
		'NU'=>'Niue',
		'NF'=>'Norfolk Island',
		'MP'=>'Northern Mariana Islands',
		'NO'=>'Norway',
		'OM'=>'Oman',
		'PK'=>'Pakistan',
		'PW'=>'Palau',
		'PS'=>'Palestinian Territory, Occupied',
		'PA'=>'Panama',
		'PG'=>'Papua New Guinea',
		'PY'=>'Paraguay',
		'PE'=>'Peru',
		'PH'=>'Philippines',
		'PN'=>'Pitcairn',
		'PL'=>'Poland',
		'PT'=>'Portugal',
		'PR'=>'Puerto Rico',
		'QA'=>'Qatar',
		'RE'=>'Réunion',
		'RO'=>'Romania',
		'RU'=>'Russian Federation',
		'RW'=>'Rwanda',
		'BL'=>'Saint Barthélemy',
		'SH'=>'Saint Helena',
		'KN'=>'Saint Kitts and Nevis',
		'LC'=>'Saint Lucia',
		'MF'=>'Saint Martin (French Part)',
		'PM'=>'Saint Pierre and Miquelon',
		'VC'=>'Saint Vincent and the Grenadines',
		'WS'=>'Samoa',
		'SM'=>'San Marino',
		'ST'=>'Sao Tome and Principe',
		'SA'=>'Saudi Arabia',
		'SN'=>'Senegal',
		'RS'=>'Serbia',
		'SC'=>'Seychelles',
		'SL'=>'Sierra Leone',
		'SG'=>'Singapore',
		'SX'=>'Sint Maarten (Dutch Part)',
		'SK'=>'Slovakia',
		'SI'=>'Slovenia',
		'SB'=>'Solomon Islands',
		'SO'=>'Somalia',
		'ZA'=>'South Africa',
		'GS'=>'South Georgia and the South Sandwich Islands',
		'SS'=>'South Sudan',
		'ES'=>'Spain',
		'LK'=>'Sri Lanka',
		'SD'=>'Sudan',
		'SR'=>'Suriname',
		'SJ'=>'Svalbard and Jan Mayen',
		'SZ'=>'Swaziland',
		'SE'=>'Sweden',
		'CH'=>'Switzerland',
		'SY'=>'Syrian Arab Republic',
		'TW'=>'Taiwan, Province of China',
		'TJ'=>'Tajikistan',
		'TZ'=>'Tanzania, United Republic of',
		'TH'=>'Thailand',
		'TL'=>'Timor-Leste',
		'TG'=>'Togo',
		'TK'=>'Tokelau',
		'TO'=>'Tonga',
		'TT'=>'Trinidad and Tobago',
		'TN'=>'Tunisia',
		'TR'=>'Turkey',
		'TM'=>'Turkmenistan',
		'TC'=>'Turks and Caicos Islands',
		'TV'=>'Tuvalu',
		'UG'=>'Uganda',
		'UA'=>'Ukraine',
		'AE'=>'United Arab Emirates',
		'GB'=>'United Kingdom',
		'US'=>'United States',
		'UM'=>'United States Minor Outlying Islands',
		'UY'=>'Uruguay',
		'UZ'=>'Uzbekistan',
		'VU'=>'Vanuatu',
		'VE'=>'Venezuela',
		'VN'=>'Vietnam',
		'VG'=>'Virgin Islands, British',
		'VI'=>'Virgin Islands, U.S.',
		'WF'=>'Wallis and Futuna',
		'EH'=>'Western Sahara',
		'YE'=>'Yemen',
		'ZM'=>'Zambia',
		'ZW'=>'Zimbabwe',
	);

	return empty($countries[$code]) ? '???' : $countries[$code];
}

function get_country_code( $text='' )
{
	if( !$text ) return '';

	$countries = array (
		'afghanistan' => 'AF',
		'alandislands' => 'AX',
		'aaland' => 'AX',
		'aland' => 'AX',
		'albania' => 'AL',
		'algeria' => 'DZ',
		'americansamoa' => 'AS',
		'andorra' => 'AD',
		'angola' => 'AO',
		'anguilla' => 'AI',
		'antarctica' => 'AQ',
		'antiguaandbarbuda' => 'AG',
		'argentina' => 'AR',
		'armenia' => 'AM',
		'aruba' => 'AW',
		'australia' => 'AU',
		'austria' => 'AT',
		'osterreich' => 'AT',
		'oesterreich' => 'AT',
		'azerbaijan' => 'AZ',
		'bahamas' => 'BS',
		'bahrain' => 'BH',
		'bangladesh' => 'BD',
		'barbados' => 'BB',
		'belarus' => 'BY',
		'belgium' => 'BE',
		'belgie' => 'BE',
		'belgien' => 'BE',
		'belgique' => 'BE',
		'belize' => 'BZ',
		'benin' => 'BJ',
		'bermuda' => 'BM',
		'bhutan' => 'BT',
		'bolivia' => 'BO',
		'bonaire' => 'BQ',
		'sinteustatius' => 'BQ',
		'saba' => 'BQ',
		'bosniaandherzegovina' => 'BA',
		'botswana' => 'BW',
		'bouvetisland' => 'BV',
		'brazil' => 'BR',
		'brasil' => 'BR',
		'britishindianoceanterritory' => 'IO',
		'brunei' => 'BN',
		'darussalam' => 'BN',
		'bulgaria' => 'BG',
		'burkinafaso' => 'BF',
		'burundi' => 'BI',
		'cambodia' => 'KH',
		'cameroon' => 'CM',
		'canada' => 'CA',
		'capeverde' => 'CV',
		'caymanislands' => 'KY',
		'centralafricanrepublic' => 'CF',
		'chad' => 'TD',
		'chile' => 'CL',
		'china' => 'CN',
		'zhongguo' => 'CN',
		'zhonghua' => 'CN',
		'peoplesrepublic' => 'CN',
		'christmasisland' => 'CX',
		'cocos(keeling)islands' => 'CC',
		'colombia' => 'CO',
		'comoros' => 'KM',
		'congo' => 'CG',
		'thedemocraticrepublicofthecongo' => 'CD',
		'cookislands' => 'CK',
		'costarica' => 'CR',
		'cotedivoire' => 'CI',
		'croatia' => 'HR',
		'hrvatska' => 'HR',
		'cuba' => 'CU',
		'curacao' => 'CW',
		'cyprus' => 'CY',
		'czechrepublic' => 'CZ',
		'ceska' => 'CZ',
		'denmark' => 'DK',
		'danmark' => 'DK',
		'djibouti' => 'DJ',
		'dominica' => 'DM',
		'dominicanrepublic' => 'DO',
		'ecuador' => 'EC',
		'egypt' => 'EG',
		'elsalvador' => 'SV',
		'equatorialguinea' => 'GQ',
		'eritrea' => 'ER',
		'estonia' => 'EE',
		'eesti' => 'EE',
		'ethiopia' => 'ET',
		'falklandislands' => 'FK',
		'malvinas' => 'FK',
		'faroeislands' => 'FO',
		'foroyar' => 'FO',
		'faeroerne' => 'FO',
		'fiji' => 'FJ',
		'finland' => 'FI',
		'suomi' => 'FI',
		'france' => 'FR',
		'republiquefrancaise' => 'FR',
		'frenchguiana' => 'GF',
		'frenchpolynesia' => 'PF',
		'frenchsouthernterritories' => 'TF',
		'gabon' => 'GA',
		'gambia' => 'GM',
		'georgia' => 'GE',
		'germany' => 'DE',
		'bundesrepublik' => 'DE',
		'deutschland' => 'DE',
		'ghana' => 'GH',
		'gibraltar' => 'GI',
		'greece' => 'GR',
		'greenland' => 'GL',
		'gronland' => 'GL',
		'grenada' => 'GD',
		'guadeloupe' => 'GP',
		'guam' => 'GU',
		'guatemala' => 'GT',
		'guernsey' => 'GG',
		'guinea' => 'GN',
		'guinea-bissau' => 'GW',
		'guyana' => 'GY',
		'haiti' => 'HT',
		'heardislandandmcdonaldislands' => 'HM',
		'holysee' => 'VA',
		'vaticancity' => 'VA',
		'honduras' => 'HN',
		'hongkong' => 'HK',
		'hungary' => 'HU',
		'iceland' => 'IS',
		'island' => 'IS',
		'india' => 'IN',
		'indonesia' => 'ID',
		'iran' => 'IR',
		'iraq' => 'IQ',
		'ireland' => 'IE',
		'eire' => 'IE',
		'isleofman' => 'IM',
		'israel' => 'IL',
		'italy' => 'IT',
		'italia' => 'IT',
		'jamaica' => 'JM',
		'japan' => 'JP',
		'nippon' => 'JP',
		'nihon' => 'JP',
		'jersey' => 'JE',
		'jordan' => 'JO',
		'kazakhstan' => 'KZ',
		'kenya' => 'KE',
		'kiribati' => 'KI',
		'northkorea' => 'KP',
		'democraticpeoplesrepublicofnorthkorea' => 'KP',
		'southkorea' => 'KR',
		'republicofsouthkorea' => 'KR',
		'kuwait' => 'KW',
		'kyrgyzstan' => 'KG',
		'laopeoplesdemocraticrepublic' => 'LA',
		'latvia' => 'LV',
		'lebanon' => 'LB',
		'lesotho' => 'LS',
		'liberia' => 'LR',
		'libya' => 'LY',
		'libyanarabjamahiriya' => 'LY',
		'liechtenstein' => 'LI',
		'lithuania' => 'LT',
		'luxembourg' => 'LU',
		'macao' => 'MO',
		'theformeryugoslavrepublicofmacedonia' => 'MK',
		'madagascar' => 'MG',
		'malawi' => 'MW',
		'malaysia' => 'MY',
		'maldives' => 'MV',
		'mali' => 'ML',
		'malta' => 'MT',
		'marshallislands' => 'MH',
		'martinique' => 'MQ',
		'mauritania' => 'MR',
		'mauritius' => 'MU',
		'mayotte' => 'YT',
		'mexico' => 'MX',
		'mexicanos' => 'MX',
		'federatedstatesofmicronesia' => 'FM',
		'republicofmoldova' => 'MD',
		'monaco' => 'MC',
		'mongolia' => 'MN',
		'montenegro' => 'ME',
		'montserrat' => 'MS',
		'morocco' => 'MA',
		'mozambique' => 'MZ',
		'myanmar' => 'MM',
		'namibia' => 'NA',
		'nauru' => 'NR',
		'nepal' => 'NP',
		'netherlands' => 'NL',
		'holland' => 'NL',
		'nederland' => 'NL',
		'newcaledonia' => 'NC',
		'newzealand' => 'NZ',
		'nicaragua' => 'NI',
		'niger' => 'NE',
		'nigeria' => 'NG',
		'niue' => 'NU',
		'norfolkisland' => 'NF',
		'northernmarianaislands' => 'MP',
		'norway' => 'NO',
		'norge' => 'NO',
		'noreg' => 'NO',
		'oman' => 'OM',
		'pakistan' => 'PK',
		'palau' => 'PW',
		'palestine' => 'PS',
		'palestinianterritory' => 'PS',
		'panama' => 'PA',
		'papuanewguinea' => 'PG',
		'paraguay' => 'PY',
		'peru' => 'PE',
		'philippines' => 'PH',
		'pitcairn' => 'PN',
		'poland' => 'PL',
		'portugal' => 'PT',
		'puertorico' => 'PR',
		'qatar' => 'QA',
		'reunion' => 'RE',
		'romania' => 'RO',
		'russianfederation' => 'RU',
		'russia' => 'RU',
		'rossiya' => 'RU',
		'rwanda' => 'RW',
		'saintbarthelemy' => 'BL',
		'sainthelena' => 'SH',
		'saintkittsandnevis' => 'KN',
		'saintlucia' => 'LC',
		'saintmartin' => 'MF',
		'saintpierreandmiquelon' => 'PM',
		'saintvincentandthegrenadines' => 'VC',
		'samoa' => 'WS',
		'sanmarino' => 'SM',
		'saotomeandprincipe' => 'ST',
		'saudiarabia' => 'SA',
		'senegal' => 'SN',
		'serbia' => 'RS',
		'seychelles' => 'SC',
		'sierraleone' => 'SL',
		'singapore' => 'SG',
		'sintmaarten' => 'SX',
		'slovakia' => 'SK',
		'slovenia' => 'SI',
		'solomonislands' => 'SB',
		'somalia' => 'SO',
		'southafrica' => 'ZA',
		'southgeorgia' => 'GS',
		'southsandwichislands' => 'GS',
		'southsudan' => 'SS',
		'spainespana' => 'ES',
		'srilanka' => 'LK',
		'sudan' => 'SD',
		'suriname' => 'SR',
		'svalbard' => 'SJ',
		'janmayen' => 'SJ',
		'swaziland' => 'SZ',
		'sweden' => 'SE',
		'sverige' => 'SE',
		'switzerland' => 'CH',
		'swissconfederation' => 'CH',
		'schweiz' => 'CH',
		'suisse' => 'CH',
		'svizzera' => 'CH',
		'svizra' => 'CH',
		'syrianarabrepublic' => 'SY',
		'syria' => 'SY',
		'taiwan' => 'TW',
		'tajikistan' => 'TJ',
		'tanzania' => 'TZ',
		'thailand' => 'TH',
		'timor-leste' => 'TL',
		'togo' => 'TG',
		'tokelau' => 'TK',
		'tonga' => 'TO',
		'trinidadandtobago' => 'TT',
		'tunisia' => 'TN',
		'turkey' => 'TR',
		'turkiye' => 'TR',
		'turkmenistan' => 'TM',
		'turksandcaicosislands' => 'TC',
		'tuvalu' => 'TV',
		'uganda' => 'UG',
		'ukraine' => 'UA',
		'ukrayina' => 'UA',
		'unitedarabemirates' => 'AE',
		'uae' => 'AE',
		'emirates' => 'AE',
		'unitedkingdom' => 'GB',
		'greatbritain' => 'GB',
		'england' => 'GB',
		'uk' => 'GB',
		'wales' => 'GB',
		'scotland' => 'GB',
		'northernireland' => 'GB',
		'gb' => 'GB',
		'british' => 'GB',
		'unitedstates' => 'US',
		'american' => 'US',
		'usa' => 'US',
		'unitedstatesofamerica' => 'US',
		'us' => 'US',
		'unitedstatesminoroutlyingislands' => 'UM',
		'uruguay' => 'UY',
		'uzbekistan' => 'UZ',
		'vanuatu' => 'VU',
		'venezuela' => 'VE',
		'vietnam' => 'VN',
		'britishvirginislands' => 'VG',
		'usvirginislands' => 'VI',
		'wallisandfutuna' => 'WF',
		'westernsahara' => 'EH',
		'yemen' => 'YE',
		'zambia' => 'ZM',
		'zimbabwe' => 'ZW',
	);

	//normalise chars etc
	$text = strtolower( remove_accents( $text ) );
	$text = str_replace( array('&',' ', ',', '.'), array('and','','',''), $text );

	//check for exact match
	if( !empty($countries[$text]) )
		return $countries[$text];

	//no exact match, so go through each to find best match
	//countries array is contained in text
	foreach( $countries as $c_text=>$code )
	{
		if( strpos($text, $c_text) !== FALSE ) return $code;
	}

	//text is contained in countries array
	foreach( $countries as $c_text=>$code )
	{
		if( strpos($c_text, $text) !== FALSE ) return $code;
	}

	//no match
	return '??' . $text;

}

/* EOF */
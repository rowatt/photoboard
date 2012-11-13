<?php

class Photoboard_Person
{
	//properties direct from DB
	public $id = 0;
	public $first_name;
	public $last_name;
	public $other_names;
	public $photo;
	public $use_existing;

	public $email;
	public $use_any;
	public $local_only;
	public $living_local;
	public $current_role;
	public $country_origin;
	public $country_current;
	public $year_first_visit;
	public $year_leave;
	public $how_long_in_community;
	public $statement;
	public $location;
	public $ff_member;
	public $ff_member_admin;
	public $ff_guest;
	public $ff_volunteer;
	public $ff_other;
	public $nfa_staff;
	public $nfa_member;
	public $nfa_org_member;
	public $other_affiliation;
	public $other_affiliations;
	public $mod_date;

	//properties initialised by init_vars()
	public $full_name;
	public $raw_img_src; //src for raw, unsized image
	public $img_src; //src for cropped/sized full size image
	public $thumb_src; //src for cropped/sized thumb
	public $blank_thumb_src; //src for cropped/sized thumb placeholder while loading
	public $photo_date; //date of photo if known

	//properties for config
	public $thumb_w = 100;
	public $thumb_h = 100;

	public $img_w = 600;
	public $img_h = 600;

	private $nocache = FALSE;

	public function __construct( $id )
	{
		global $photoboard_people, $wpdb;

		//get data from global photoboard_people object if it exists
		if( $photoboard_people instanceof Photoboard_People )
		{
			if( !empty($photoboard_people->people[$id]) )
				$this->set_data( $photoboard_people->people[$id] );
		}

		//do DB query if we don't yet have any data for person id
		if( !$this->id )
			$this->set_data( $wpdb->get_row("SELECT * from {$wpdb->prefix}photoboard_people WHERE id={$id}") );

		//if still no id, then record doesn't exist so no more initialisation
		if( !$this->id ) return;

		$this->init_vars();
	}

	private function init_vars()
	{
		$this->full_name = $this->first_name . ($this->first_name && $this->last_name ? ' ' : '') . $this->last_name;
		$photo_file = PHOTOBOARD_PIX_DIR . $this->id . '/' . $this->photo;
		$blank_src = PHOTOBOARD_THEME_PATH_SITE . '/img/blank.png';

		if( $this->photo && file_exists( $photo_file ) )
		{
			$img_data = @exif_read_data( $photo_file );

			if( empty($img_data['COMPUTED']['Width']) || empty($img_data['COMPUTED']['Height']) )
			{
				//invalid image format
				$this->raw_img_src = PHOTOBOARD_THEME_PATH_WP . '/img/img-invalid.png';
				$blank_src = PHOTOBOARD_THEME_PATH_WP . '/img/img-invalid.png';
			}
			elseif( $img_data['COMPUTED']['Height'] < PHOTOBOARD_MIN_IMAGE_SIZE || $img_data['COMPUTED']['Width'] < PHOTOBOARD_MIN_IMAGE_SIZE )
			{
				//image too small
				$this->raw_img_src = PHOTOBOARD_THEME_PATH_WP . '/img/img-too-small.png';
				$blank_src = PHOTOBOARD_THEME_PATH_WP . '/img/img-too-small.png';
			}
			elseif( $img_data['COMPUTED']['Height'] * $img_data['COMPUTED']['Width'] > PHOTOBOARD_MAX_IMAGE_SIZE )
			{
				//image too big
				$this->raw_img_src = PHOTOBOARD_THEME_PATH_WP . '/img/img-too-big.png';
				$blank_src = PHOTOBOARD_THEME_PATH_WP . '/img/img-too-big.png';
			}
			else
			{
				//photo ok
				$this->raw_img_src = PHOTOBOARD_PIX_PATH . $this->id . '/' . $this->photo;
				$this->photo_date = str_ireplace( '.jpg', '', $this->photo );
			}
		}
		else
		{
			//no image file found
			$this->raw_img_src = PHOTOBOARD_THEME_PATH_WP . '/img/blank.png';
		}

		$this->img_src = TT_URL_2 . "?src={$this->raw_img_src}&w={$this->img_w}&h={$this->img_h}";
		$this->thumb_src = TT_URL . "?src={$this->raw_img_src}&w={$this->thumb_w}&h={$this->thumb_h}";
		$this->blank_thumb_src = TT_URL . "?src={$blank_src}&w={$this->thumb_w}&h={$this->thumb_h}";

		if( $this->nocache )
		{
			$this->thumb_src = $this->thumb_src . '&nocache=' . rand(0,9999) ;
			$this->img_src = $this->img_src . '&nocache=' . rand(0,9999) ;
		}
	}

	private function set_data( $data )
	{
		if( !is_array($data) && !is_object($data) ) return FALSE;
		foreach( $data as $key=>$item )
		{
			$this->$key = $item;
		}
		return TRUE;
	}

	public function get_full_img_info()
	{
		$output = "<div class='full-img'><img src='{$this->img_src}' /></div>";

		$output .= "<article class='transparent-bg'>";

		$output .= $this->format_info( $this->full_name, 'name primary', '%s <i class="icon-info-sign"></i>', 'h1' );
		$output .= $this->format_info( $this->other_names, 'other-names primary', '<i>aka %s</i>' );
		$output .= $this->format_info( get_person_affiliations($this), 'affiliations secondary', '<i class="icon icon-sitemap"></i> %s' );
		$output .= $this->format_info( get_person_location($this), 'location secondary', '<i class="icon icon-globe"></i> %s' );
		$output .= $this->format_info( get_person_dates($this), 'dates secondary', '<i class="icon icon-calendar"></i> %s');
		$output .= $this->format_info( ucfirst(trim($this->statement)), 'statement secondary', '<i class="icon icon-comment"></i> %s'  );

		$mod_udate = strtotime($this->mod_date);
		if( $mod_udate > 946684800 ) // 1 Jan 2000
			$output .= $this->format_info( date( 'F Y', $mod_udate ) , 'last-updated secondary', 'last updated %s' );

		$photo_udate = strtotime($this->photo_date);
		if( $photo_udate > 31536000 ) // 1 Jan 1971
			$output .= $this->format_info( date( 'F Y', $photo_udate ) , 'last-updated secondary', 'photo taken %s' );

	    $output .= "</article>";

		return $output;
	}

	private function format_info( $txt, $class, $wrapper='', $element='div')
	{
		if( !$txt ) return '';

		if( $wrapper )
			$txt = sprintf( $wrapper, $txt );

		return "<{$element} class='{$class}'>{$txt}</{$element}>";
	}

	public function get_thumb()
	{
		$classes = $this->get_classes();
		return "<article class='person-thumb {$classes}' id='p-{$this->id}'><img src='{$this->thumb_src}' alt='photo of {$this->full_name}' /><p class='transparent-bg'>{$this->full_name}</p></article>\n";
	}

	private function get_classes()
	{
		$classes = array();

		if( $this->ff_member || $this->ff_member_admin ) $classes['ff_member'] = 'ff_member';
		if( $this->ff_other ) $classes['ff_other'] = 'ff_other';
		if( $this->ff_guest ) $classes['ff_guest'] = 'ff_guest';
		if( $this->ff_volunteer ) $classes['ff_volunteer'] = 'ff_volunteer';
		if( $this->nfa_staff ) $classes['nfa_staff'] = 'nfa_staff';
		if( $this->nfa_member || $this->nfa_org_member ) $classes['nfa_member'] = 'nfa_member';
		if( $this->other_affiliation ) $classes['other_affiliation'] = 'other_affiliation';

		if( $this->country_origin ) $classes[ 'country_origin' . $this->country_origin ] = 'country_origin-' . $this->country_origin;

		if( $this->living_local )
			$classes[ 'current' ] = 'current';
		else
			$classes[ 'current' ] = 'not-current';

		switch( $this->location )
		{
			case 'Forres (not Cluny)':
				$loc_class = 'forres';
				break;

			case 'Cluny':
				$loc_class = 'cluny';
				break;

			case 'Findhorn (not Park)':
				$loc_class = 'findhorn';
				break;

			case 'Park':
				$loc_class = 'park';
				break;

			case 'Kinloss':
				$loc_class = 'kinloss';
				break;

			case '':
			default:
				$loc_class = 'other';
				break;
		}

		$classes['location'] = 'location-' . $loc_class;

		return implode( ' ', $classes );
	}
}

/* EOF */
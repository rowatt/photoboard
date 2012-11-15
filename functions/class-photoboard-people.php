<?php

class Photoboard_People
{
	public $people = array();

	public function __construct( $and_where='' )
	{
		global $wpdb, $photoboard_people;

		if( !$photoboard_people instanceof Photoboard_People )
			$photoboard_people = $this;

		$where = ' WHERE 1=1';
		if( !$this->is_community_ip() )
			$where .= ' AND local_only=0';
		$where .= ' AND status=0'; //anything >0 means record deleted, replaced etc
		$where .= ' AND ( photo<>"" AND photo<>"X" )';
		if( $and_where )
			$where .= ' AND ' . $and_where;

		$people = $wpdb->get_results("SELECT * from {$wpdb->prefix}photoboard_people{$where} ORDER BY first_name");

		foreach( $people as $person )
		{
			$this->people[$person->id] = $person;
		}

	}

	public function get_html_thumbs()
	{
		$html = '';
		foreach( $this->people as $person )
		{
			$obj_person = new Photoboard_Person($person->id);
			$html .= $obj_person->get_thumb();
		}
		return $html;
	}

	private function is_community_ip()
	{
		return in_array( $_SERVER['REMOTE_ADDR'], array(
		                                                '127.0.0.1', //local machine
		                                                '81.174.224.21', //MRA home
		                                                '81.5.166.91', //Park
		                                                '81.174.141.214' //Cluny
		                                           ) );
	}

}

/* EOF */
<?php

function get_how_long( $person )
{
	switch( $person->how_long_in_community )
	{
		case 'less1':
			$txt = 'less than 1 year';
			break;

		case '1to5':
			$txt = '1 to 5 years';
			break;

		case '5to10':
			$txt = '5 to 10 years';
			break;

		case '10to20':
			$txt = '10 to 20 years';
			break;

		case 'more20':
			$txt = 'more than 20 years';
			break;

		default:
			$txt = '';
	}

	return $txt;
}

function get_person_location( $person )
{
	$txts = array();

	if( $person->living_local )
	{
		if( $person->location )
		{
			$person->location = str_ireplace( 'park', 'the Park', $person->location );
			$txts[] = "currently living locally in {$person->location}";
		}
		else
		{
			$txts[] = 'currently living locally';
		}
	}
	elseif( $person->country_current && $person->country_current==$person->country_origin )
		$txts[] = 'currently living in and originally from ' . get_country_name( $person->country_current );
	elseif( $person->country_current && $person->country_current<>$person->country_origin )
		$txts[] = 'currently living in ' . get_country_name( $person->country_current );

	if( $person->country_origin && '?'<>substr($person->country_origin, 0, 1) && $person->country_current<>$person->country_origin )
	{
		$txts[] = 'originally from ' . get_country_name( $person->country_origin );
	}

	if( empty($txts) ) return '';

	$txts[0] = ucfirst( $txts[0] );

	return implode( ', ', $txts ) . '.';
}

function get_person_dates( $person )
{
	$txts = array();
	$years = '';
	$how_long = '';

	if( $person->year_first_visit )
		$txts[] = "First came to Findhorn in {$person->year_first_visit}";

	if( $person->year_leave )
		$txts[] = "left in {$person->year_leave}";

	if( !empty($txts) )
	{
		$txts[0] = ucfirst( $txts[0] );
		$years = implode( ', ', $txts ) . '. ';
	}

	if( $person->how_long_in_community )
		$how_long = 'Has spent ' . get_how_long($person) . ' in the community.';

	return trim( $years . ' ' . $how_long );
}

function get_person_affiliations( $person )
{
	$affils = array();

	if( $person->living_local )
	{
		if( $person->ff_member ) $affils[] = 'FF co-worker (LES)';
		$intro = '';
	}
	else
	{
		if( $person->ff_member ) $affils[] = 'FF member/co-worker';
		$intro = 'Formerly ';
	}

	if( $person->ff_other ) $affils[] = 'FF (other)';
	if( $person->ff_member_admin ) $affils[] = 'FF co-worker (management & admin)';
	if( $person->ff_guest ) $affils[] = 'FF long term guest';
	if( $person->ff_volunteer ) $affils[] = 'FF committed volunteer';
	if( $person->nfa_staff ) $affils[] = 'NFA staff, council or volunteer';
	if( $person->nfa_member ) $affils[] = 'NFA member';
	if( $person->nfa_org_member ) $affils[] = 'work for NFA member organisation';
	if( $person->other_affiliation ) $affils[] = trim($person->other_affiliations);

	if( $affils )
		return $intro . implode( ', ', $affils ) . '.';
	else
		return '';

}

function get_affiliation_filters()
{
	$filters = array();

	$filters['ff_member'] = 'FF co-worker/member';
	$filters['ff_guest'] = 'FF long term guest';
	$filters['ff_volunteer'] = 'FF committed volunteer';
	$filters['ff_other'] = 'FF other';
	$filters['nfa_staff'] = 'NFA staff/council/volunteer';
	$filters['nfa_member'] = 'NFA member';
	$filters['other_affiliation'] = 'Other';

	return $filters;
}

function get_location_filters()
{
	$filters = array();
	$filters['park'] = 'Park';
	$filters['cluny'] = 'Cluny';
	$filters['findhorn'] = 'Findhorn (not Park)';
	$filters['kinloss'] = 'Kinloss';
	$filters['forres'] = 'Forres (not Cluny)';
	$filters['other_location'] = 'Other';

	return $filters;
}

function get_filter_checkboxes( $list=array() )
{
	$html = "<ul class='filter-list'>";

	foreach( $list as $key=>$item )
	{
		$html .= "<li class='filter-checkbox'><input type='checkbox' name='filter-{$key}' id='filter-{$key}' value='{$key}' checked='checked'><label for='filter-{$key}'>{$item}</label></li>";
	}

	$html .= "</ul>";

	return $html;
}


/*
Which areas below you are currently affiliated with? Check all that apply.

Findhorn Foundation (LES department coworker)
Findhorn Foundation (management or admin department)
Findhorn Foundation (long term guest)
Findhorn Foundation (committed volunteer)
Findhorn Foundation (other)
NFA (employee, committed volunteer or elected representative)
NFA (member)
Employee or committed volunteer of other NFA member organisation
Other


When you were living in the community, which of the areas below were you primarily affiliated with?

Findhorn Foundation (member, co-worker etc)
Findhorn Foundation (long term guest, but not member, co-worker etc)
Findhorn Foundation (committed volunteer)
Findhorn Foundation (other)
NFA (employee, committed volunteer or elected representative)
NFA (member)
Employee or committed volunteer of other NFA member organisation
Other
*/
/* EOF */
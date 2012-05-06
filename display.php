<?php
/**
 * adjunct form display file
 * @package adjunct
 * @author Christopher Roussel <christopher@impleri.net>
 */



class adb_display {

	// show details on a metro area (all campuses, average pay, etc)
	public static function showMetro() {}

	// show a small info box on the metro area
	private static function boxMetro() {}

	// show summary of a campus as a table row
	public static function rowCampus() {}

	// show details on a campus (all contracts, benefits, etc)
	public static function showCampus() {}

	// show a details of a contract as a table row
	public static function rowContract() {}

	// show a details of a benefit as a table row
	public static function rowBenefit() {}

	// show a search form
	public static function searchForm() {}

	// show search form results
	public static function searchResults() {}

	// show an input/update form for adding information
	public static function inputForm() {}

	// show a list of campuses ordered by $field (e.g. a top ten adjusted salaries)
	public static function listCampusBy ($field, $order='DESC') {}

}

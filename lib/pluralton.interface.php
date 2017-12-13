<?php

	////////////////////////////////////////////////////////////
	//
	// Pluraltons are "parametarized singletons".
	//
	// Each class only has one unique instance of the object
	// per given set of parameters.
	///////////////////////////////////////////////////////////
interface pluralton {
	//	protected static $arPluraltons;  // interfaces don't allow properties
	public static function getInstance($arParams);
}

?>
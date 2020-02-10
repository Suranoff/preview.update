<?
/*
 * В настройках ничего не добавлял кроме инфоблока.
 * Вообще можно было бы добавить настройку шага для аякса для обновления элементов. Полезно если элементов предусматривается очень много.
 *
*/


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc as Loc;

Loc::loadMessages(__FILE__);

try
{
	if (!Main\Loader::includeModule('iblock'))
		throw new Main\LoaderException(Loc::getMessage('STANDARD_ELEMENTS_PARAMETERS_IBLOCK_MODULE_NOT_INSTALLED'));

	$iblockTypes = \CIBlockParameters::GetIBlockTypes(Array("-" => " "));

	$iblocks = [0 => ""];
	if (isset($arCurrentValues['IBLOCK_TYPE']) && strlen($arCurrentValues['IBLOCK_TYPE']))
	{
	    $filter = array(
	        'TYPE' => $arCurrentValues['IBLOCK_TYPE'],
	        'ACTIVE' => 'Y'
	    );
	    $iterator = \CIBlock::GetList(array('SORT' => 'ASC'), $filter);
	    while ($iblock = $iterator->GetNext())
	    {
	        $iblocks[$iblock['ID']] = $iblock['NAME'];
	    }
	}

	$arComponentParameters = array(
		'PARAMETERS' => array(
			'IBLOCK_TYPE' => Array(
				'PARENT' => 'BASE',
				'NAME' => Loc::getMessage('STANDARD_ELEMENTS_PARAMETERS_IBLOCK_TYPE'),
				'TYPE' => 'LIST',
				'VALUES' => $iblockTypes,
				'DEFAULT' => '',
				'REFRESH' => 'Y'
			),
			'IBLOCK_ID' => array(
				'PARENT' => 'BASE',
				'NAME' => Loc::getMessage('STANDARD_ELEMENTS_PARAMETERS_IBLOCK_ID'),
				'TYPE' => 'LIST',
				'VALUES' => $iblocks
			),
		)
	);
}
catch (Main\LoaderException $e)
{
	ShowError($e->getMessage());
}
?>
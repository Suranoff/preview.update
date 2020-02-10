<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Main\Localization\Loc;
/*
 * Здесь поминимуму сделал. Как указывал в настройках можно сделать пошаговый аякс, который за один запрос будет обрабатывать 50 элементов например.
 * Добавить прогресс бар для красоты и отображение различных статусов, подключить логирование итд.
 *
*/
?>

<div class="main_block">
    <form id="preview_update_form" action="" method="post">
        <?=bitrix_sessid_post()?>
        <input type="hidden" name="ajax_send" value="y">
        <input type="submit" value="<?=Loc::getMessage('UPDATE_ELEMENTS_BUTTON')?>">
    </form>
    <div class="status_block"></div>
</div>




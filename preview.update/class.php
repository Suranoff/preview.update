<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;

Loc::loadMessages(__FILE__);

class PreviewUpdateComponent extends \CBitrixComponent
{
    const COLLECTION_PROPERTY_CODE = 'COLLECTION'; // при необходимости можно вынести в настройки компонента, селект для выбора нужного свойства

    const API_URL = 'https://sebekon.ru/api/v2/collection/';

    protected $errors = [];

    protected $is_admin;

    protected $items = [];

    public function __construct($component = null)
    {
        parent::__construct($component);
        CJSCore::Init(["jquery"]);
        $this->is_admin = $this->getCurrentUser()->IsAdmin();
    }

    /**
     * @return array|bool|\CAllUser|\CUser
     */
    protected function getCurrentUser()
    {
        global $USER;
        return $USER;
    }

    /**
     * проверим является ли пользователь админом
     */
    protected function checkRights()
    {
        if (!$this->is_admin)
            throw new \Bitrix\Main\AccessDeniedException();
    }

    /**
     * Проверяет заполнение обязательных параметров
     * @throws ArgumentNullException
     */
    protected function checkParams()
    {
        if ($this->arParams['IBLOCK_ID'] <= 0)
            throw new ArgumentNullException('IBLOCK_ID');
    }

    /**
     * Получаем описание коллекции по api
     * @var string
     * @return string
     */
    protected function getCollectionDescription($collection)
    {
        // здесь получаем описание коллекции запросом api, например через \Bitrix\Main\Web\HttpClient
        $someDescription = 'Какое-то описание для коллекции '.$collection;
        return $someDescription;
    }

    /**
     * Получим описания для всех коллекций
     * @return array
     */
    protected function getAllCollectionsDescriptions()
    {
        $collectionsDescriptions = [];
        foreach ($this->collections as $collection) {
            $collectionsDescriptions[$collection] = $this->getCollectionDescription($collection);
        }

        return $collectionsDescriptions;
    }

    /**
     * Обновим описание элемента с помощью старого ядра, так как в новом еще не реализовано
     */
    protected function updateElementDescription($id, $desc)
    {
        $el = new CIBlockElement;
        $result = $el->Update($id, ['PREVIEW_TEXT' => $desc]);

        if (!$result) {
            $this->errors[] = Loc::getMessage("UPDATE_ERROR").' '.$id.': '.$el->LAST_ERROR;
        }
        return true;
    }

    /**
     * Обновим описание всех элементов
     */
    protected function updateDescriptions()
    {
        $collectionsDescriptions = $this->getAllCollectionsDescriptions();
        foreach ($this->items as $item) {
            $collectionDesc = $collectionsDescriptions[$item['PROPERTY_'.self::COLLECTION_PROPERTY_CODE.'_VALUE']];
            if ($item['PREVIEW_TEXT'] != $collectionDesc) {
                $this->updateElementDescription($item['ID'], $collectionDesc);
            }
        }
    }

    /**
     * Получаем все элементы каталога и коллекции для дальнейшей работы с помощью старого ядра.
     * Так как нам нужно получить значение свойства удобней использовать его.
     * Можно было бы использовать D7 если самому сформировать ORM сущность для таблицы значений свойств инфоблока,
     * но в данной ситуации это не критично считаю.
     * Также можно было бы кешировать результат и использовать тегированный кеш, чтобы он обновлялся только при изменении элементов инфоблока
     */
    protected function prepareData()
    {
        $collections = [];
        $items = [];

        $res = CIBlockElement::GetList([], ['IBLOCK_ID' => $this->arParams["IBLOCK_ID"], '!PROPERTY_'.self::COLLECTION_PROPERTY_CODE => false], false, false, ['PROPERTY_'.self::COLLECTION_PROPERTY_CODE, 'ID', 'PREVIEW_TEXT']);
        while ($item = $res->fetch()) {
            $collections[] = $item['PROPERTY_'.self::COLLECTION_PROPERTY_CODE.'_VALUE'];
            $items[] = $item;
        }

        $this->collections = array_unique($collections);
        $this->items = $items;
    }

    public function executeComponent()
    {
        try
        {
            global $APPLICATION;

            \Bitrix\Main\Loader::includeModule("iblock");

            $this->checkRights();

            $request = Application::getInstance()->getContext()->getRequest();

            if ($request->isPost() && check_bitrix_sessid() && $request->getPost("ajax_send") == 'y') {
                $APPLICATION->RestartBuffer();

                $this->checkParams();
                $this->prepareData();
                $this->updateDescriptions();

                if ($this->errors)
                    throw new \Exception(implode('', $this->errors));

                ShowMessage(["TYPE" => "OK", "MESSAGE" => Loc::getMessage("UPDATE_OK")]);
                die;
            }

            $this->includeComponentTemplate();
        }
        catch(Exception $e)
        {
            $exceptionHandling = Config\Configuration::getValue("exception_handling");
            if ($exceptionHandling["debug"])
            {
                throw $e;
            }
            else
            {
                ShowError($e->getMessage());
            }
        }
    }
}
?>
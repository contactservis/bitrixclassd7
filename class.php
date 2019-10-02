<?php
// подключение пролога Битрикса
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

// подключение классов для HL блоков
use Bitrix\Main\Loader;
Loader::includeModule("highloadblock");
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

// Наследуем класс от Класса Битрикса компонентов
class DemoClass extends CBitrixComponent
{ 

    // загрузка параметров подключения компоненты
    public function onPrepareComponentParams($arParams)
    {
        $result = array(
            "CACHE_TYPE" => $arParams["CACHE_TYPE"],
            "CACHE_TIME" => isset($arParams["CACHE_TIME"]) ?$arParams["CACHE_TIME"]: 36000000,            
            "IBLOCK_ID" => intval($arParams["IBLOCK_ID"]),
            "SECTION_ID" => intval($arParams["SECTION_ID"]),
            "ELEMENT_ID" => intval($arParams["ELEMENT_ID"]),
        );
        return $result;
    }

    // перевод параметров в arrResult
    function paramsUser($arParams){
        $arResult["INFOBLOCK_ID"]   = $arParams["IBLOCK_ID"];
        $arResult["SECTION_ID"]     = $arParams["SECTION_ID"];
        $arResult["ELEMENT_ID"]     = $arParams["ELEMENT_ID"];
        return $arResult;
    }

    private function getElementsByID(){
        // задаем параметры
        $IBlock_id  = $this->$arResult['IBLOCK_ID'];
        $Section_id = $this->$arResult['SECTION_ID'];
        $Element_id = $this->$arResult['ELEMENT_ID'];

        // подключаем модуль инфоблока
        if(CModule::IncludeModule("iblock")){
            
            // фильтр
            $arFilter = Array(
                'IBLOCK_ID' => IntVal($IBlock_id),                
                'PROPERTY_NAME-USER-PROPERTY' => '' // Фильтр по пользовательскому свойству
            );

            // количество на странице, номер страницы ...
            $arPage = Array(
                'nPageSize'=>10
            );

            // запрос с параметрами
            $result = CIBlockElement::GetList(Array(), $arFilter, false, $arPage);

            // массив элементов
            $arElements = Array();

            // получаем поля элементов
            while($element = $result->GetNextElement())
            {
                // поля элемента
                $arFields = $element->GetFields();                
                
                // изображения
                $arFields['PREVIEW_PICTURE_URL'] = CFile::GetPath($arFields['PREVIEW_PICTURE']);
                $arFields['DETAIL_PICTURE_URL']  = CFile::GetPath($arFields['DETAIL_PICTURE']);

                // пользовательские свойства элемента
                $UserProperty = CIBlockElement::GetProperty($IBlock_id, $arFields['ID']);
                // если нужно получить значения без ~ (без тильды к значенинию применено htmlspecialcharsEx) ->GetNext(true, false)
                while($arProperty = $UserProperty->GetNext()){
                    // записываем свойства в массив с кодом свойства
                    $arFields['USER_PROPERTY'][$arProperty['CODE']] = $arProperty;
                }

                // добавляем свойства элементу
                $arElements[] = $arFields;
            }
        }
            
    }

    // 
    public function executeComponent()
    {
            // добавляем параметры в arResult  который пойдет в шаблон
            $this->arResult = array_merge($this->arResult, $this->paramsUser($this->arParams));          

            // получить элемент по ID
            $this->arResult['ELEMENT']      = $this->getElementByID($this->arResult['ELEMENT_ID']);            

            // подключение шабона
            $this->includeComponentTemplate();
            return $this->arResult;

    }

}
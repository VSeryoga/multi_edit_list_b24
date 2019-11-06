<?require_once ($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include.php");

global $USER;
if (!$USER->IsAuthorized()) exit();

switch ($_REQUEST['action']) {

	case 'sumList':

		CModule::IncludeModule('lists');
		$obList = new CList($_REQUEST['list']);
		$listFields = $obList->GetFields();
		// print_r($listFields);
		foreach ($listFields as $key => $value) {
			$filterable[$key] = '';

		}

		@$filterOption = new Bitrix\Main\UI\Filter\Options('lists_list_elements_'.$_REQUEST['list']);
		@$filterData = $filterOption->getFilter('lists_list_elements_'.$_REQUEST['list']);
		
		foreach($filterData as $key => $value)
		{

			
				
			if (is_array($value))
			{
				if (empty($value))
					continue;
			}
			
			elseif(strlen($value) <= 0)
				continue;

			if(substr_count((string)$value, 'CONTACT') > 0){
				$tmp = json_decode($value);
				$value = $tmp->CONTACT;
			}
			if(substr_count((string)$value, 'COMPANY') > 0){
				$tmp = json_decode($value);
				$value = $tmp->COMPANY;
			}

			if(substr($key, -5) == "_from")
			{
				$new_key = substr($key, 0, -5);
				$op = (!empty($filterData[$new_key."_numsel"]) && $filterData[$new_key."_numsel"] == "more") ? ">" : ">=";
				$value = date('Y-m-d 00:00:00', strtotime($value));
			}
			elseif(substr($key, -3) == "_to")
			{
				$new_key = substr($key, 0, -3);
				$op = (!empty($filterData[$new_key."_numsel"]) && $filterData[$new_key."_numsel"] == "less") ? "<" : "<=";
				if(array_key_exists($new_key, (array)$dateFilter))
				{
					$dateFormat = $DB->dateFormatToPHP(Csite::getDateFormat());
					if(substr_count($new_key, 'DATE') < 1){
							$timeFormat = "Y-m-d";
						}
					$dateParse = date_parse_from_format($dateFormat, $value);
					if(!strlen($dateParse["hour"]) && !strlen($dateParse["minute"]) && !strlen($dateParse["second"]))
					{
						$timeFormat = $DB->dateFormatToPHP(CSite::getTimeFormat());
						
						$value .= " ".date($timeFormat, mktime(23, 59, 59, 0, 0, 0));
					}
				}
				$value = date('Y-m-d 23:59:59', strtotime($value));
			}
			else
			{
				$op = "";
				$new_key = $key;
			}

			if($key == "CREATED_BY" || $key == "MODIFIED_BY")
			{
				if(!intval($value))
				{
					$userId = array();
					$userQuery = CUser::GetList(
						$by = "ID",
						$order = "ASC",
						array("NAME" => $value),
						array("FIELDS" => array("ID"))
					);
					while($user = $userQuery->fetch())
						$userId[] = $user["ID"];
					if(!empty($userId))
						$value = $userId;
				}
			}



			if(array_key_exists($new_key, $filterable))
			{
				if($op == "")
					$op = $filterable[$new_key];
				$arFilter[$op.$new_key] = $value;
			}

			if($key == "FIND" && trim($value))
			{
				$op = "*";
				$arFilter[$op."SEARCHABLE_CONTENT"] = $value;
			}

			if($listFields[$new_key]['PROPERTY_USER_TYPE']['USER_TYPE'] == 'Date'){
				$arFilter[$op.$new_key] = date('Y-m-d', strtotime($value));
			}


		}

		//получим список массив полей для подсчита
		$properties = CIBlockProperty::GetList([], ["USER_TYPE" => "Money", "IBLOCK_ID" => $_REQUEST['list']]);
		$monayFields = [];
		while ($prop_fields = $properties->GetNext())
		{
			$monayFields[] = 'PROPERTY_'.$prop_fields['ID'];
		}

		$arFilter["IBLOCK_ID"] = $_REQUEST['list'];

		$arSelect = Array("ID", "IBLOCK_ID", );

		$arSelect = array_merge($arSelect, $monayFields);

		$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
		$sum = [];
		while($ob = $res->GetNextElement()){ 
		 	$arFields = $ob->GetFields();  
			$sum['PROPERTY_88'] += (float)str_replace('|', '', $arFields['PROPERTY_88_VALUE']);
			$sum['PROPERTY_89'] += (float)str_replace('|', '', $arFields['PROPERTY_89_VALUE']);
		}

		foreach ($sum as $key => $value) {
			$result[$key] =  CurrencyFormat($value, 'RUB');
		}

		echo \Bitrix\Main\Web\Json::encode($result);

		break;
	case 'getListSelect':

		$res = CIBlockElement::GetList(Array(), ['IBLOCK_ID' => 12], false, false, $arSelect);

		while($ob = $res->GetNextElement()){ 
		 	$arFields = $ob->GetFields();  
			$result[$arFields['ID']] = $arFields['NAME'];
		}

		echo \Bitrix\Main\Web\Json::encode($result);

		break;

	case 'editList':
		print_r($_REQUEST);
		$arProps = [];
		foreach ($_REQUEST['data'] as $key => $props) {
			foreach ($props as $k => $v) {
				$arProps[str_replace('PROPERTY_', '', $k)] = iconv('UTF-8', 'windows-1251', $v);
			}
			print_r($arProps);
			CIBlockElement::SetPropertyValuesEx($key, 28, $arProps);

		}
		# code...
		break;

	default:
		# code...
		break;
}



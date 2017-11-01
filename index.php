<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("123");
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/mainpage.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once 'curl/curl.php';
require_once 'jquery/phpQuery.php';
require_once 'trans/trans.php';
if (!CModule::IncludeModule("iblock")) die('err_functions_'.__LINE__);
	CModule::IncludeModule('iblock');
	unset($GLOBALS['IBLOCK_ID']);
	header('Content-Type: text/html; charset=UTF-8');
	mb_internal_encoding('UTF-8');
	mb_http_output('UTF-8');
	mb_http_input('UTF-8');
	mb_regex_encoding('UTF-8');


	function dobavlenie($sonameCatalog,$nameCatalog,$creator){
		$obBlocktype = new CIBlockType;
		$ib = new CIBlock;
		$ibp = new CIBlockProperty;
		$obBlocktype->Add(array("ID"=>$sonameCatalog, "SECTIONS"=>"Y", 'LANG'=>Array("en"=>Array("NAME"=>$sonameCatalog))));
		$arFields = Array(
			"ACTIVE" => "Y",
			"NAME" => $nameCatalog,
			"CODE" => $creator,
			"IBLOCK_TYPE_ID" => $sonameCatalog,
			"SITE_ID" => CMainPage::GetSiteByHost(),
			"SORT" => "1",
			"LIST_PAGE_URL" => "#SITE_DIR#/catalog/",
			"SECTION_PAGE_URL" => "#SITE_DIR#/catalog/#SECTION_CODE#/",
			"DETAIL_PAGE_URL" => "#SITE_DIR#/catalog/#SECTION_CODE#/#ELEMENT_CODE#/",         
		);
		if ($ID > 0)
			$res = $ib->Update($ID, $arFields);
		else
		{
			$ID = $ib->Add($arFields);
			$res = ($ID>0);
			session_start();
			$_SESSION['MAINID'] = $ID;
			echo "Создан тип инфоблока: ".$sonameCatalog."<br>";
			echo "Создан инфоблок: ".$nameCatalog."<br>";
			echo "ID инфоблока: ".$_SESSION['MAINID']."<br>";
			echo "<br>";
		}
		/****/
		$arFields = Array(
			"NAME" => "Дополнительные фотографии",
			"ACTIVE" => "Y",
			"MULTIPLE" => "Y",
			"SORT" => 1,
			"CODE" => "MORE_PHOTO",
            "PROPERTY_TYPE" => "F", // Файл
            "FILE_TYPE" => "jpg, gif, bmp, png, jpeg",   
            "IBLOCK_ID" => $ID,
            "HINT" => "Допускается произвольное число дополнительных фотографий. Добавьте одну, и появится поле для добавленя следующей.",
        );

		$propId = $ibp->Add($arFields);
		if ($propId > 0)
		{
			$arFields["ID"] = $propId;
			$arCommonProps[$arFields["CODE"]] = $arFields;
			echo "&mdash; Добавлено свойство ".$arFields["NAME"].";<br />";
		}
		else
			echo "&mdash; Ошибка добавления свойства ".$arFields["NAME"]."<br />";
		/****/
		/****/
		$arFields = Array(
			"NAME" => "Характеристика",
			"ACTIVE" => "Y",
			"MULTIPLE" => "N",
			"SORT" => 2,
			"CODE" => "TECH",
			"PROPERTY_TYPE" => "S",
			"USER_TYPE" => "HTML", 
			"IBLOCK_ID" => $ID,
		);

		$propId = $ibp->Add($arFields);
		if ($propId > 0)
		{
			$arFields["ID"] = $propId;
			$arCommonProps[$arFields["CODE"]] = $arFields;
			echo "&mdash; Добавлено свойство ".$arFields["NAME"].";<br />";
		}
		else
			echo "&mdash; Ошибка добавления свойства ".$arFields["NAME"]."<br />";
		/****/
		/****/
		$arFields = Array(
			"NAME" => "Цена",
			"ACTIVE" => "Y",
			"MULTIPLE" => "N",
			"SORT" => 3,
			"CODE" => "PRICE",
			"PROPERTY_TYPE" => "N", 
			"IBLOCK_ID" => $ID,
		);

		$propId = $ibp->Add($arFields);
		if ($propId > 0)
		{
			$arFields["ID"] = $propId;
			$arCommonProps[$arFields["CODE"]] = $arFields;
			echo "&mdash; Добавлено свойство ".$arFields["NAME"]."<br />";
		}
		else
			echo "&mdash; Ошибка добавления свойства ".$arFields["NAME"]."<br />";
		/****/
		/****/
		$arFields = Array(
			"NAME" => "Описание",
			"ACTIVE" => "Y",
			"MULTIPLE" => "N",
			"SORT" => 4,
			"CODE" => "PROP",
			"PROPERTY_TYPE" => "S", 
			"IBLOCK_ID" => $ID,
		);

		$propId = $ibp->Add($arFields);
		if ($propId > 0)
		{
			$arFields["ID"] = $propId;
			$arCommonProps[$arFields["CODE"]] = $arFields;
			echo "&mdash; Добавлено свойство ".$arFields["NAME"]."<br />";
			echo "<br>";
		}
		else
			echo "&mdash; Ошибка добавления свойства ".$arFields["NAME"]."<br />";
		/****/


	}
	function addCatalog($name,$pic,$discr,$idBlock,$sortId){
		global $bid;
		global $dd;
		$bid = array();
		$bs = new CIBlockSection;
		$codeId = str2url($name);
		$arFields = Array(
			"ACTIVE" => Y,
			"IBLOCK_SECTION_ID" => 0,
			"IBLOCK_ID" => $idBlock,
			"NAME" => $name,
			"SORT" => $sortId,
			"CODE" => $codeId,
			"PICTURE" => $pic,
			"DESCRIPTION" => $discr,
			"DESCRIPTION_TYPE" => $discr
		);
		if ($ID = $bs->Add($arFields)) {		
			echo 'Добавлен раздел: ID '. $ID. ' &mdash; '. $name  .';<br>'  ;
			$dd = $ID;
		} else {
			echo "Error[" . $ID . "]: " . $el->LAST_ERROR . '<br />'; 
		}

	}

	/******/
	/****/

	function add($nazvanie,$link,$fcatalog,$fname,$fpic,$fdiscr)
	{
		global $dd;
		global $links;
		$curl = new Curl();
		$query = $nazvanie;
		$response = $curl->get($link, $query);
		$doc = phpQuery::newDocument($response->body);
		$products = $doc->find($fcatalog);
		$db = array();
		$links = array();
		$i = 0;
		foreach ($products as $product){
			$pq = pq($product);
			$name = $pq->find($fname)->text();
			$pic = $pq->find($fpic)->attr('src');
			$discr = $pq->find($fdiscr)->text();
			$file_arr = CFile::MakeFileArray($pic);
			/**/
			$link = $pq->find($fname)->attr('href');
			$links[] = array($link);
			$i++; 
			/**/
    // var_dump("<pre>",$file_arr,"</pre>");
			$nazvanie = array('name' => $name, 'pic' => $file_arr, 'discr' => $discr);
      // var_dump("<pre>",$nazvanie,"</pre>");
			addCatalog($nazvanie[name],$file_arr,$nazvanie[discr],$_SESSION['MAINID'],$i);
			$db[] = array($dd);
    // array_push($db, "dd");
      // var_dump("<pre>",$nazvanie,"</pre>");
		}
		$_SESSION['id'] = $db;
		$_SESSION['links'] = $links;
        // echo count($products);  
	} 

	function addEL($blId,$nameId,$picId,$discrId,$techId,$priseId,$propId){
		$el = new CIBlockElement;
		$codeId = str2url($nameId);
		$fields = array(
			'IBLOCK_ID' => $_SESSION['MAINID'],
			'NAME' => $nameId,
			"CODE" => $codeId,
			"IBLOCK_SECTION_ID" => $blId,
			'ACTIVE' => "Y",
			'PREVIEW_PICTURE' => $picId,
			'PREVIEW_TEXT' => $discrId,
			'DETAIL_PICTURE' => $picId,
			'DETAIL_TEXT' => $discrId,
			"PROPERTY_VALUES" => array(
				"TECH" => $techId,
				"PRICE" => $priseId,
				"MORE_PHOTO" => $picId,
				"PROP" => $propId  
			)
		);

		if ($PRODUCT_ID = $el->Add($fields)) {
			echo 'Добавлен элемент, ID: ' . $PRODUCT_ID .'<br>' ;
		} else {
			echo "Error[" . $PRODUCT_ID . "]: " . $el->LAST_ERROR . '<br />'; 
		}
	}

function addIn($nazvanie,$link,$fcatalog,$fname,$fpic,$fdiscr,$ftech,$fprice,$razdel,$fprop)
	{
		$curl = new Curl();
		$query = $nazvanie;
		$response = $curl->get($link, $query);
		$doc = phpQuery::newDocumentPHP($response, $contentType = null);
		$products = $doc->find($fcatalog);
		foreach ($products as $product){
			$pq = pq($product);
			$name = $pq->find($fname)->text();
			$pic = $pq->find($fpic)->attr('href');
			$discr = $pq->find($fdiscr)->text();
			$techs = $pq->find($ftech)->html();
			/***/
			$techq = preg_replace("/<([a-z][a-z0-9]*)(?:[^>]*(\shref=['\"][^'\"]*['\"]))?[^>]*?(\/?)>/i",'<$1$2$3>', $techs);
			$tech = preg_replace("/width=\"60\%\"/i",'<$1$2$3>', $techq);
			/***/
			$file_arr = CFile::MakeFileArray($pic);
			$priceIn = $pq->find($fprice)->text();
			$price = preg_replace('~[^0-9]+~','',$priceIn);

			$prop = $pq->find($fprop)->text();
			$nazvanie = array('name' => $name, 'pic' => $file_arr, 'discr' => $discr, 'tech' => $tech, 'price' => $price, 'prop' => $prop, );
    //var_dump("<pre>",$tech,"</pre>");
	addEL($razdel,$nazvanie[name],$file_arr,$nazvanie[discr], $nazvanie[tech], $nazvanie[price], $nazvanie[prop]);
    //var_dump("<pre>",$nazvanie,"</pre>");
		}
		// var_dump("<pre>",$techw,"</pre>");
		// echo $techw;
	} 

	function addIn_all($nazvanie,$nomerRazdel,$link,$fcatalog,$fname,$flink)
	{
		$curl = new Curl();
		$query = $nazvanie;
		$response = $curl->get($link, $query);
		$doc = phpQuery::newDocument($response->body);
		$products = $doc->find($fcatalog);
		foreach ($products as $product){
			$pq = pq($product);
			$name = $pq->find($fname)->text();
			$link = $pq->find($flink)->attr('href');
			$fulllink = "http://optimus-cctv.ru".$link;
			$nazvanie = array('name' => $name, 'link' => $fulllink);
			addIn("$nazvanie",$nazvanie[link],"td.main_content",".flypage h1",".cat_image a",".tabber_item_active","#teh_har",".cat_price",$nomerRazdel, ".osn_har");
    // var_dump("<pre>",$fulllink,"</pre>");
		}   
   // var_dump("<pre>",$nazvanie,"</pre>");
	} 

	function addBase(){
		dobavlenie("parser","catalog","roofoos");
		add("tovar","http://optimus-cctv.ru/catalog",".jshop .jshop_categ",".product_link","img.jshop_img","p.category_short_description");
	}

	function addTovar(){
		$n=0; 
		$n++; 
		$uName = "Something_".$n;
		$count = count($_SESSION['links']);
		for ($i=0;$i<$count;$i++) {
			$uMainLink = "http://optimus-cctv.ru".$_SESSION['links'][$i][0];
			addIn_all($uName, $_SESSION['id'][$i][0], $uMainLink, ".jshop_list_product .cat", ".cat_name a", ".cat_image a");
		}
	}




//addBase();
//addTovar();




	function testBase(){
		dobavlenie("test","catalog","roofoos");
		add("tovar","http://optimus-cctv.ru/catalog",".jshop .jshop_categ",".product_link","img.jshop_img","p.category_short_description");
	}


	function testaddTovar(){
		$n=0; 
		$n++; 
		$uName = "Something_".$n;

  // $count = count($_SESSION['links']);
  // for ($i=0;$i<$count;$i++) {
  //   $uMainLink = "http://optimus-cctv.ru".$_SESSION['links'][$i][0];
    // addIn_all($uName, $_SESSION['id'][$i][0], $uMainLink, ".jshop_list_product .cat", ".cat_name a", ".cat_image a img");
  // }
		$uMainLink = "http://optimus-cctv.ru".$_SESSION['links'][8][0];
		addIn_all($uName, $_SESSION['id'][8][0], $uMainLink, ".jshop_list_product .cat", ".cat_name a", ".cat_image a");
	}

	//testBase();
	//testaddTovar();

// session_destroy();
//var_dump("<pre>",$_SESSION,"</pre>");






	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
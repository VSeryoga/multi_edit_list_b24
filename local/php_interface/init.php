<?
AddEventHandler('main', 'OnEpilog', function(){ 
    $arJsConfig = array( 
        'custom_main' => array( 
            'js' => '/local/js/custom_lists.js',
            'css' => '/local/css/custom.css'
        )
    ); 
    foreach ($arJsConfig as $ext => $arExt) { 
        \CJSCore::RegisterExt($ext, $arExt); 
    } 

    CUtil::InitJSCore(array('jquery2', 'custom_main')); 

});
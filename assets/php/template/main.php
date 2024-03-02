<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use App\App;
use Bitrix\Main\Page\Asset;

?>
<!DOCTYPE html>
<html class="html html_no-js" lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="HandheldFriendly" content="true">
    <meta name="MobileOptimized" content="width">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="format-detection" content="telephone=no">
    <meta name="format-detection" content="address=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- favicon-->
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#f4f7fb">
    <meta name="theme-color" content="#ffffff">

    <?if (App::isDevEnv()):?>
    <meta name="robots" content="noindex,nofollow">
    <?endif?>

    <title><?$APPLICATION->ShowTitle()?></title>
    <meta name="description" content="Законодательное Собрание Краснодарского края">

    <!-- Styles-->
    <?php
    $asset = Asset::getInstance();
    $asset->addCss(SITE_TEMPLATE_PATH . "/custom.css");
    $asset->addCss(App::frontendPath() . '/styles/app.min.css');

    $asset->addJs(App::frontendPath() . '/scripts/jquery.min.js');
    \Bitrix\Main\Page\Asset::getInstance()->addJs(\App\App::frontendPath() . '/scripts/jquery-datepicker.min.js');
    \Bitrix\Main\Page\Asset::getInstance()->addJs(\App\App::frontendPath() . '/scripts/jquery.modal.min.js');

    $asset->addJs(App::frontendPath() . '/scripts/jquery.validate.js');
    $asset->addJs(App::frontendPath() . '/scripts/additional-methods.js');
    $asset->addJs(App::frontendPath() . '/scripts/messages_ru.js');
    $asset->addJs(App::frontendPath() . '/scripts/jquery.inputmask.bundle.min.js');

    $asset->addJs(App::frontendPath() . '/scripts/slick.min.js');
    $asset->addJs(App::frontendPath() . '/scripts/tooltipster.bundle.min.js');
    $asset->addJs(App::frontendPath() . '/scripts/dropzone.js');
    $asset->addJs(App::frontendPath() . '/scripts/app.js');

    $APPLICATION->ShowHead();
    ?>



<body class="page">
<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();
   for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
   k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(89388304, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true
   });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/89388304" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

<?$APPLICATION->ShowPanel()?>
<div id="svg-sprite" data-src="<?= App::frontendPath() ?>/img/sprites.svg"></div>

<!-- Header-->
<header class="header">
    <div class="header__top-block">
        <div class="container">
            <div class="row justify-content-between">
                <div class="col-auto">
                    <a class="link link_iconed link_theme-light-transp" href="?special_version=Y">
                        <svg class="icon link__icon link__glasses-symbol">
                            <use xlink:href="#link__glasses-symbol"></use>
                        </svg>
                        <span class="link__text">Версия для слабовидящих</span>
                    </a>
                </div>
                <div class="col-auto">
                    <div class="header__soc-wrapper">
                        <!-- socials -->
                        <?$APPLICATION->IncludeFile("/include/socials.php")?>
                        <a class="link link_iconed link_theme-liveplay" href="/online/">
                            <svg class="icon link__icon link__liveplay-symbol">
                                <use xlink:href="#link__liveplay-symbol"></use>
                            </svg>
                            <span class="link__text">Прямая трансляция</span>
                        </a>
                    </div>
                </div>
                <div style="display: none;">
                    <?
                    // $APPLICATION->IncludeComponent(
                    //     "rnds:rnds.esia.oauth",
                    //     "rl",
                    //     Array(
                    //         "EsiaOAuthSite" => "https://esia.gosuslugi.ru", //URL системы - поставщика услуг (ЕСИА)
                    //         // "EsiaOAuthSite" => $GLOBALS['ESIA_PATH'], //URL системы - поставщика услуг (ЕСИА)
                    //         "EsiaOAuthRedirectURI" => "http://kubzsk.ru/feedback/", //Определяет адрес возврата в ИС после логаута пользователя в ЕСИА
                    //         "EsiaOAuthSourceURL" => "http://kubzsk.ru/feedback/", //Определяет адрес возврата в ИС после авторизации пользователя в ЕСИА
                    //         "EsiaOAuthClientId" => $GLOBALS['ESIA_MNEMONIC'], //Мнемоника, полученная при регистрации ИС в минкомсвязи
                    //         "EsiaOAuthScope" => $GLOBALS['ESIA_SCOPES'], //Перечень областей данных к которым предоставляется доступ для ИС после авторизации
                    //         "EsiaOAuthKey" => "keys/secret_WEBKUBZSK.key", //Путь к файлу, содержащему закрытый ключ ИС
                    //         "EsiaOAuthCert" => "keys/cert_WEBKUBZSK.cer", //Путь к файлу, содержащему открытый ключ ИС
                    //         "EsiaOAuthUserGroupId" => $GLOBALS['ESIA_GROUP_ID'], //Идентификатор группы пользователей ЕСИА
                    //         "EsiaOAuthShowInterface" => "Y", //Определяет необходимость отображения кнопки “ВОЙТИ”/”ВЫЙТИ” для компоненты
                    //         "EsiaOAuthUseCurl" => "Y",
                    //         "EsiaOAuthOnlyTrustedUsers" => "Y", //Принимает значения “Y”/”N” и определяет необходимость авторизации пользователей только с подтвержденными учетными записями (УЗ)
                    //         "EsiaOAuthUnTrustedUserRedirectURI" => "http://kubzsk.ru/feedback/esia_logout.php", //Определяет адрес раздела ИС, в котором содержится информация для пользователей не прошедших авторизацию по причине не подтвержденности УЗ
                    //       ),
                    //     false);

                    $APPLICATION->IncludeComponent(
                        "rnds:rnds.esia.oauth.gost",
                        "",
                        Array(
                            "OAuth2UserGroupsId" => $GLOBALS['ESIA_GROUP_ID'],
                            "OAuth2Site" => $GLOBALS['ESIA_PATH'],
                            "OAuth2LogoutUri" => "https://kubzsk.ru/feedback/",
                            "OAuth2AuthUri" => "https://kubzsk.ru/feedback/",
                            "OAuth2RefreshTokenUri" => "https://kubzsk.ru/?refresh_token=true",
                            "OAuth2UnTrustedUserRedirectURI" => "https://kubzsk.ru/feedback/esia_logout.php",
                            'OAuth2ClientId' => $GLOBALS['ESIA_MNEMONIC'],
                            "OAuth2Scope" => $GLOBALS['ESIA_SCOPES'],
                            "OAuth2IsDebug" => true,
                            "OAuth2UseCurl" => true,
                            "OAuth2CreateSessionUser" => "N",
                            "OAuth2UpdateAuthUser" => "Y",
                            "OAuth2ShowInterface" => "Y",
                            // "OAuth2IsTestEnvironment" => false,
                            // "OAuth2OnlyTrustedUsers" => "Y",
                            "OAuth2OnlyVerifyedData" => "Y",
                            "OAuth2PkeyPath" => "/var/www/html/releases/80/local/components/rnds/rnds.esia.oauth.gost/lib/Keys/GOST3410_2012_256/secret.key",
                            "OAuth2CertPath" => "/var/www/html/releases/80/local/components/rnds/rnds.esia.oauth.gost/lib/Keys/GOST3410_2012_256/cert.crt",
                            'OAuth2EsiaCertPath' => '/var/www/html/releases/80/local/components/rnds/rnds.esia.oauth.gost/lib/Keys/GOST3410_2012_256/esia-prod.crt'
                          ),
                        false);
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="header__bottom-block">
        <div class="container">
            <div class="row justify-content-between align-items-center">
                <div class="col-12 col-sm-auto">
                    <a class="main-logo" href="/" title="На главную">
                        <svg class="icon main-logo__icon main-logo__main-logo-symbol">
                            <!--use xlink:href="#main-logo__main-logo-symbol"></use-->
                            <image xlink:href="/images/30_years_logo.svg" width="90" height="36" />
                        </svg>
<img border="0" src="/images/ban.png" width="59" height="39">
                        <h1 class="main-logo__title-wrapper">
                            <span class="main-logo__title">Законодательное Собрание</span>
                            <span class="main-logo__subtitle">Краснодарского края</span>
                        </h1>
                    </a>
                </div>
                <div class="col-auto">
                    <?$APPLICATION->IncludeComponent(
	"bitrix:menu", 
	"header.top.short", 
	array(
		"ALLOW_MULTI_SELECT" => "N",
		"CHILD_MENU_TYPE" => "",
		"DELAY" => "N",
		"MAX_LEVEL" => "1",
		"MENU_CACHE_GET_VARS" => array(
			0 => "Законодательное Собрание",
			1 => "",
		),
		"MENU_CACHE_TIME" => "3600",
		"MENU_CACHE_TYPE" => "N",
		"MENU_CACHE_USE_GROUPS" => "Y",
		"ROOT_MENU_TYPE" => "topshort",
		"USE_EXT" => "N",
		"COMPONENT_TEMPLATE" => "header.top.short"
	),
	false
);?>
                </div>
            </div>
        </div>
        <div class="main-submenu">
            <div class="main-submenu__wrapper">
                <div class="container">
                    <div class="search-form main-submenu__search">
                        <form class="search-form__control" action="/search/">
                            <div class="search-form__input-group">
                                <svg class="icon search-form__icon search-form__search-symbol">
                                    <use xlink:href="#search-form__search-symbol"></use>
                                </svg>
                                <label class="search-form__input-control" for="main-search">Поиск по сайту</label>
                                <input name="q" class="search-form__input" type="text" id="main-search">
                                <span class="search-form__focus-line"></span>
                            </div>
                        </form>
                    </div>
                </div>
                <nav class="all-menu-links all-menu-links_dropdown main-submenu__menu-wrapper">
                    <div class="container">
                        <div class="row">
                            <?$APPLICATION->IncludeComponent("bitrix:menu", "header.inner.block", Array(
                                "ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
                                "CHILD_MENU_TYPE" => "",	// Тип меню для остальных уровней
                                "DELAY" => "N",	// Откладывать выполнение шаблона меню
                                "MAX_LEVEL" => "1",	// Уровень вложенности меню
                                "MENU_CACHE_GET_VARS" => array(	// Значимые переменные запроса
                                    0 => "Законодательное Собрание",
                                ),
                                "MENU_CACHE_TIME" => "3600",	// Время кеширования (сек.)
                                "MENU_CACHE_TYPE" => "N",	// Тип кеширования
                                "MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
                                "ROOT_MENU_TYPE" => "col1",	// Тип меню для первого уровня
                                "USE_EXT" => "N",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
                                "COMPONENT_TEMPLATE" => ".default"
                            ),
                                false
                            );?>
                            <?$APPLICATION->IncludeComponent("bitrix:menu", "header.inner.block", Array(
                                "ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
                                "CHILD_MENU_TYPE" => "",	// Тип меню для остальных уровней
                                "DELAY" => "N",	// Откладывать выполнение шаблона меню
                                "MAX_LEVEL" => "1",	// Уровень вложенности меню
                                "MENU_CACHE_GET_VARS" => array(	// Значимые переменные запроса
                                    0 => "Деятельность",
                                ),
                                "MENU_CACHE_TIME" => "3600",	// Время кеширования (сек.)
                                "MENU_CACHE_TYPE" => "N",	// Тип кеширования
                                "MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
                                "ROOT_MENU_TYPE" => "col2",	// Тип меню для первого уровня
                                "USE_EXT" => "N",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
                                "COMPONENT_TEMPLATE" => ".default"
                            ),
                                false
                            );?>
                            <?$APPLICATION->IncludeComponent("bitrix:menu", "header.inner.block", Array(
                                "ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
                                "CHILD_MENU_TYPE" => "",	// Тип меню для остальных уровней
                                "DELAY" => "N",	// Откладывать выполнение шаблона меню
                                "MAX_LEVEL" => "1",	// Уровень вложенности меню
                                "MENU_CACHE_GET_VARS" => array(	// Значимые переменные запроса
                                    0 => "Материалы для СМИ",
                                ),
                                "MENU_CACHE_TIME" => "3600",	// Время кеширования (сек.)
                                "MENU_CACHE_TYPE" => "N",	// Тип кеширования
                                "MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
                                "ROOT_MENU_TYPE" => "col3",	// Тип меню для первого уровня
                                "USE_EXT" => "N",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
                                "COMPONENT_TEMPLATE" => ".default"
                            ),
                                false
                            );?>
                            <?$APPLICATION->IncludeComponent("bitrix:menu", "header.inner.block", Array(
                                "ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
                                "CHILD_MENU_TYPE" => "",	// Тип меню для остальных уровней
                                "DELAY" => "N",	// Откладывать выполнение шаблона меню
                                "MAX_LEVEL" => "1",	// Уровень вложенности меню
                                "MENU_CACHE_GET_VARS" => array(	// Значимые переменные запроса
                                    0 => "Представительская деятельность",
                                ),
                                "MENU_CACHE_TIME" => "3600",	// Время кеширования (сек.)
                                "MENU_CACHE_TYPE" => "N",	// Тип кеширования
                                "MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
                                "ROOT_MENU_TYPE" => "col4",	// Тип меню для первого уровня
                                "USE_EXT" => "N",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
                                "COMPONENT_TEMPLATE" => ".default"
                            ),
                                false
                            );?>
                        </div>
                    </div>
                </nav>
                <div class="container">
                    <div class="row">
                        <div class="share-iformation main-submenu__share-iformation">
                            <div class="share-iformation__inner">
                                <div class="copyright">
                                    <div class="text text_theme-grey copyright__text">При полном либо частичном использовании информации гиперссылка на источник обязательна</div>
                                </div>
                                <?/*
                                <div class="social-links">
                                    <a class="social-links__link" href="#" title="Вконтакте1">
                                        <svg class="icon social-links__icon social-links__vkontakte-symbol">
                                            <use xlink:href="#social-links__vkontakte-symbol"></use>
                                        </svg>
                                    </a>
                                    <a class="social-links__link" href="#" title="Facebook">
                                        <svg class="icon social-links__icon social-links__facebook-symbol">
                                            <use xlink:href="#social-links__facebook-symbol"></use>
                                        </svg>
                                    </a>
                                    <a class="social-links__link" href="#" title="Twitter">
                                        <svg class="icon social-links__icon social-links__twitter-symbol">
                                            <use xlink:href="#social-links__twitter-symbol"></use>
                                        </svg>
                                    </a>
                                    <a class="social-links__link" href="#" title="Одноклассники">
                                        <svg class="icon social-links__icon social-links__odnoklassniki-symbol">
                                            <use xlink:href="#social-links__odnoklassniki-symbol"></use>
                                        </svg>
                                    </a>
                                </div>
                                */?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Content-->
<main class="content" id="<?$APPLICATION->ShowViewContent('mainId');?>">
<?$APPLICATION->IncludeComponent('rr:preheader', '')?>#WORK_AREA#<?php
use App\App;
?>

</main>

<?php
//die();
?>

<div class="modal-window" id="modal-form-errors">
    <div class="modal-window__header">
        <div class="modal-window__title">Ошибка</div>
        <a class="modal-window__close" href="#" rel="modal:close">
            <svg class="icon modal-window__icon modal-window__close-symbol">
                <use xlink:href="#modal-window__close-symbol"></use>
            </svg>
        </a>
    </div>
    <div class="modal-window__body">
        <div class="text text_paragraph-16 text_theme-charcoal-grey modal-window__text">Ошибка!</div>
    </div>
    <div class="modal-window__footer">
        <a class="button button_theme-cool-grey modal-window__cancel" href="#" rel="modal:close">
            <span class="button__text">Отмена</span>
        </a>
        <a class="button button_theme-blue modal-window__continue" href="#" rel="modal:close">
            <span class="button__text">Продолжить</span>
        </a>
    </div>
</div>

<div class="slider-modal" id="slider-modal"></div>

<!-- Footer-->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="main-logo main-logo_footer col-12 col-lg-4">
                <h1 class="main-logo__title-wrapper">
                    <span class="main-logo__title">Законодательное Собрание</span>
                    <span class="main-logo__subtitle">Краснодарского края</span>
                </h1>
                <?/*<a href="/feedback/" class="button button_theme-medium-grey footer__feedback-button">Обратная связь</a>*/?>
            </div>
            <nav class="all-menu-links all-menu-links_footer col-12 col-lg-8">
                <div class="row">
                    <? 
                    $APPLICATION->IncludeComponent("bitrix:menu", "footer.inner.block", Array(
                        "ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
                        "CHILD_MENU_TYPE" => "",	// Тип меню для остальных уровней
                        "DELAY" => "N",	// Откладывать выполнение шаблона меню
                        "MAX_LEVEL" => "1",	// Уровень вложенности меню
                        "MENU_CACHE_GET_VARS" => array(	// Значимые переменные запроса
                            0 => "Законодательное Собрание",
                        ),
                        "MENU_CACHE_TIME" => "3600",	// Время кеширования (сек.)
                        "MENU_CACHE_TYPE" => "N",	// Тип кеширования
                        "MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
                        "ROOT_MENU_TYPE" => "col1",	// Тип меню для первого уровня
                        "USE_EXT" => "N",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
                        "COMPONENT_TEMPLATE" => ".default"
                    ),
                        false
                    );  ?>
                    <? $APPLICATION->IncludeComponent("bitrix:menu", "footer.inner.block", Array(
                        "ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
                        "CHILD_MENU_TYPE" => "",	// Тип меню для остальных уровней
                        "DELAY" => "N",	// Откладывать выполнение шаблона меню
                        "MAX_LEVEL" => "1",	// Уровень вложенности меню
                        "MENU_CACHE_GET_VARS" => array(	// Значимые переменные запроса
                            0 => "Деятельность",
                        ),
                        "MENU_CACHE_TIME" => "3600",	// Время кеширования (сек.)
                        "MENU_CACHE_TYPE" => "N",	// Тип кеширования
                        "MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
                        "ROOT_MENU_TYPE" => "col2",	// Тип меню для первого уровня
                        "USE_EXT" => "N",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
                        "COMPONENT_TEMPLATE" => ".default"
                    ),
                        false
                    ); ?>
                    <? $APPLICATION->IncludeComponent("bitrix:menu", "footer.inner.block", Array(
                        "ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
                        "CHILD_MENU_TYPE" => "",	// Тип меню для остальных уровней
                        "DELAY" => "N",	// Откладывать выполнение шаблона меню
                        "MAX_LEVEL" => "1",	// Уровень вложенности меню
                        "MENU_CACHE_GET_VARS" => array(	// Значимые переменные запроса
                            0 => "Материалы для СМИ",
                        ),
                        "MENU_CACHE_TIME" => "3600",	// Время кеширования (сек.)
                        "MENU_CACHE_TYPE" => "N",	// Тип кеширования
                        "MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
                        "ROOT_MENU_TYPE" => "col3",	// Тип меню для первого уровня
                        "USE_EXT" => "N",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
                        "COMPONENT_TEMPLATE" => ".default"
                    ),
                        false
                    ); ?>
                    <? $APPLICATION->IncludeComponent("bitrix:menu", "footer.inner.block", Array(
                        "ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
                        "CHILD_MENU_TYPE" => "",	// Тип меню для остальных уровней
                        "DELAY" => "N",	// Откладывать выполнение шаблона меню
                        "MAX_LEVEL" => "1",	// Уровень вложенности меню
                        "MENU_CACHE_GET_VARS" => array(	// Значимые переменные запроса
                            0 => "Представительская деятельность",
                        ),
                        "MENU_CACHE_TIME" => "3600",	// Время кеширования (сек.)
                        "MENU_CACHE_TYPE" => "N",	// Тип кеширования
                        "MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
                        "ROOT_MENU_TYPE" => "col4",	// Тип меню для первого уровня
                        "USE_EXT" => "N",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
                        "COMPONENT_TEMPLATE" => ".default"
                    ),
                        false
                    ); ?>
                </div>
            </nav>
            <?/*<div class="col-12">
                <a href="/feedback/" class="button button_theme-medium-grey footer__feedback-button-mobile">Обратная связь</a>
            </div>*/?>
            <div class="share-iformation">
                <div class="share-iformation__inner">
                    <div class="copyright">
                        <div class="text text_theme-grey copyright__text">
                            <? $APPLICATION->IncludeComponent('rr:static.text', '', [
                                'IBLOCK_ID' => IBLOCK_STATIC,
                                'CODE' => 'footer-copyright'
                            ]) ?>
                        </div>
<!-- Yandex.Metrika informer -->
<a href="https://metrika.yandex.ru/stat/?id=89388304&amp;from=informer" target="_blank" rel="nofollow"><img src="https://informer.yandex.ru/informer/89388304/3_0_FFFFFFFF_EFEFEFFF_0_pageviews" style="width:88px; height:31px; border:0;" alt="Яндекс.Метрика" title="Яндекс.Метрика: данные за сегодня (просмотры, визиты и уникальные посетители)" class="ym-advanced-informer" data-cid="89388304" data-lang="ru" /></a>
<!-- /Yandex.Metrika informer -->
                    </div>
                    <!-- socials -->
                    <? $APPLICATION->IncludeFile("/include/socials.php") ?>
                </div>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
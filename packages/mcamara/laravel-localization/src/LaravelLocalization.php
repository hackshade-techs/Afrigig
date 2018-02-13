<?php
/**
 * JobClass - Geolocalized Job Board Script
 * Copyright (c) BedigitCom. All Rights Reserved
 *
 * Website: http://www.bedigit.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from Codecanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
 */

namespace Larapen\LaravelLocalization;

use App\Models\Category;
use App\Models\Language;
use App\Models\Page;
use Illuminate\Support\Facades\Route;
use Larapen\LaravelLocalization\Exceptions\SupportedLocalesNotDefined;
use Larapen\LaravelLocalization\Exceptions\UnsupportedLocaleException;
use Illuminate\Config\Repository;
use Illuminate\View\Factory;
use Illuminate\Translation\Translator;
use Illuminate\Routing\Router;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Request as RequestFacade;

class LaravelLocalization
{
    /**
     * Config repository.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $configRepository;

    /**
     * Illuminate view Factory.
     *
     * @var \Illuminate\View\Factory
     */
    protected $view;

    /**
     * Illuminate translator class.
     *
     * @var \Illuminate\Translation\Translator
     */
    protected $translator;

    /**
     * Illuminate router class.
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * Illuminate request class.
     *
     * @var \Illuminate\Routing\Request
     */
    protected $request;

    /**
     * Illuminate request class.
     *
     * @var Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Illuminate request class.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Default locale
     *
     * @var string
     */
    protected $defaultLocale;

    /**
     * Supported Locales
     *
     * @var array
     */
    protected $supportedLocales;

    /**
     * Current locale
     *
     * @var string
     */
    protected $currentLocale = false;

    /**
     * An array that contains all routes that should be translated
     *
     * @var array
     */
    protected $translatedRoutes = [];

    /**
     * Name of the translation key of the current route, it is used for url translations
     *
     * @var string
     */
    protected $routeName;

    /**
     * Creates new instance.
     * @throws UnsupportedLocaleException
     */
    public function __construct()
    {
        $this->app = app();

        $this->configRepository = $this->app['config'];
        $this->view = $this->app['view'];
        $this->translator = $this->app['translator'];
        $this->router = $this->app['router'];
        $this->request = $this->app['request'];

        // set default locale
        // $this->defaultLocale = $this->configRepository->get('app.locale');

        // Default language (from config)
        $this->defaultLocale = config('applang.abbr', config('app.locale'));

        $supportedLocales = $this->getSupportedLocales();

        if (empty($supportedLocales[$this->defaultLocale])) {
            throw new UnsupportedLocaleException("The default locale is not supported.");
        }
    }

    /**
     * Set and return current locale
     *
     * @param  string $locale Locale to set the App to (optional)
     *
     * @return string                    Returns locale (if route has any) or null (if route does not have a locale)
     */
    public function setLocale($locale = null)
    {
        if (empty($locale) || !is_string($locale)) {
            // If the locale has not been passed through the function
            // it tries to get it from the first segment of the url
            $locale = $this->request->segment(1);
        }

        if (!empty($this->supportedLocales[$locale])) {
            $this->currentLocale = $locale;
        } else {
            // if the first segment/locale passed is not valid
            // the system would ask which locale have to take
            // it could be taken by the browser
            // depending on your configuration

            $locale = null;

            // if we reached this point and hideDefaultLocaleInURL is true
            // we have to assume we are routing to a defaultLocale route.
            if ($this->hideDefaultLocaleInURL()) {
                $this->currentLocale = $this->defaultLocale;
            }
            // but if hideDefaultLocaleInURL is false, we have
            // to retrieve it from the browser...
            else {
                $this->currentLocale = $this->getCurrentLocale();
            }
        }

        $this->app->setLocale($this->currentLocale);

        // Regional locale such as de_DE, so formatLocalized works in Carbon
        $regional = $this->getCurrentLocaleRegional();
        if ($regional) {
            setlocale(LC_TIME, $regional . '.utf8');
        }

        return $locale;
    }

    /**
     * Set and return supported locales
     *
     * @param  array $locales Locales that the App supports
     */
    public function setSupportedLocales($locales)
    {
        $this->supportedLocales = $locales;
    }

    /**
     * Returns an URL adapted to $locale or current locale
     *
     * @param  string $url URL to adapt. If not passed, the current url would be taken.
     * @param  string|boolean $locale Locale to adapt, false to remove locale
     *
     * @throws UnsupportedLocaleException
     *
     * @return string                       URL translated
     */
    public function localizeURL($url = null, $locale = null)
    {
        return $this->getLocalizedURL($locale, $url);
    }


    /**
     * Returns an URL adapted to $locale
     *
     * @throws SupportedLocalesNotDefined
     * @throws UnsupportedLocaleException
     *
     * @param  string|boolean $locale Locale to adapt, false to remove locale
     * @param  string|false $url URL to adapt in the current language. If not passed, the current url would be taken.
     * @param  array $attributes Attributes to add to the route, if empty, the system would try to extract them from the url.
     * @param  array $params For JobClass.
     *
     *
     * @return string|false                URL translated, False if url does not exist
     */
    public function getLocalizedURL($locale = null, $url = null, $attributes = [], $params = [])
    {
        if ($locale === null) {
            $locale = $this->getCurrentLocale();
        }

        // For JobClass
        if ($this->exceptRedirectionPath()) {
            return false;
        }

        // For JobClass
        if (!$this->checkLocaleInSupportedLocales($locale)) {
            return false;
        }

        if (empty($attributes)) {
            $attributes = $this->extractAttributes($url);
        }

        if (empty($url)) {
            if (!empty($this->routeName)) {
                return $this->getURLFromRouteNameTranslated($locale, $this->routeName, $attributes);
            }

            // For JobClass
            // ==================

            // Search parameters
            $queryString = (RequestFacade::getQueryString() ? ('?' . RequestFacade::getQueryString()) : '');

            // Check the country code (required for multi-countries website)
            if (!isset($params['countryCode']) or empty($params['countryCode'])) {
                if (isset($attributes['countryCode']) and !empty($attributes['countryCode'])) {
                    $params['countryCode'] = $attributes['countryCode'];
                }
                if (!isset($params['countryCode']) or empty($params['countryCode'])) {
                    if ($this->request->has('d')) {
                        $params['countryCode'] = strtolower($this->request->get('d'));
                    }
                }
            }

            $preUri = '';
            if (!(config('laravellocalization.hideDefaultLocaleInURL') == true && $locale == config('applang.abbr'))) {
                $preUri = $locale . '/';
            }

            // Search : Index
            if (str_contains(Route::currentRouteAction(), 'SearchController@index')) {
                // Build URL
                if (isset($params['countryCode'])) {
                    $r_url = trans('routes.v-search', ['countryCode' => $params['countryCode']], 'messages', $locale);
                } else {
                    $r_url = trans('routes.v-search', [], 'messages', $locale);
                }
                $url = app('url')->to($preUri . $r_url) . $queryString;

                return $url;
            } // Search : Category
            else if (
                str_contains(Route::currentRouteAction(), 'SearchController@category') or
                str_contains(Route::currentRouteAction(), 'SearchController@subCategory')
            ) {
                // Get category or sub-category translation
                if (isset($params['catSlug'])) {
                    $cat = LaravelLocalization::getCategoryBySlug($params['catSlug'], $locale);
                    if (!is_null($params['subCatSlug']) and !empty($cat)) {
                        $sub_cat = LaravelLocalization::getSubCategoryBySlug($cat->tid, $params['subCatSlug'], $locale);
                    }

                    // Build URL
                    $r_url = '';
                    if (isset($cat) and isset($sub_cat) and !empty($cat) and !empty($sub_cat)) {
                        $r_url = trans('routes.v-search-subCat',
                            [
                                'countryCode' => $params['countryCode'],
                                'catSlug'     => $cat->slug,
                                'subCatSlug'  => $sub_cat->slug,
                            ], 'messages', $locale);
                    } else {
                        if (isset($cat) and !empty($cat)) {
                            $r_url = trans('routes.v-search-cat', ['countryCode' => $params['countryCode'], 'catSlug' => $cat->slug], 'messages', $locale);
                        }
                    }
                    $url = app('url')->to($preUri . $r_url) . $queryString;

                    return $url;
                }
            } // Search : Location - Laravel Routing don't support PHP rawurlencode() function
            else if (str_contains(Route::currentRouteAction(), 'SearchController@location')) {
                if (isset($params['cityName'])) {
                    $r_url = trans('routes.v-search-location', ['city' => $params['cityName'], 'id' => $params['cityId']], 'messages', $locale);
                    $url = app('url')->to($preUri . $r_url) . $queryString;

                    return $url;
                }
            } // Pages
            else if (str_contains(Route::currentRouteAction(), 'PageController@index')) {
                if (isset($params['pageSlug'])) {
                    $page = LaravelLocalization::getPageBySlug($params['pageSlug'], $locale);

                    if (!empty($page)) {
                        $r_url = trans('routes.v-page', ['slug' => $page->slug], 'messages', $locale);
                        $url = app('url')->to($preUri . $r_url) . $queryString;

                        return $url;
                    }
                }
            } else {
                //$url = $this->request->fullUrl();
                $url = url($this->request->getRequestUri());
            }
        }

        if ($locale && $translatedRoute = $this->findTranslatedRouteByUrl($url, $attributes, $this->currentLocale)) {
            return $this->getURLFromRouteNameTranslated($locale, $translatedRoute, $attributes);
        }

        $base_path = $this->request->getBaseUrl();
        $parsed_url = mb_parse_url($url); //=> UTF-8
        $url_locale = $this->getDefaultLocale();

        if (!$parsed_url || empty($parsed_url['path'])) {
            $path = $parsed_url['path'] = "";
        } else {
            $parsed_url['path'] = str_replace($base_path, '', '/' . ltrim($parsed_url['path'], '/'));
            $path = $parsed_url['path'];
            foreach($this->getSupportedLocales() as $localeCode => $lang) {
                $parsed_url['path'] = preg_replace('%^/?' . $localeCode . '/%', '$1', $parsed_url['path']);
                if ($parsed_url['path'] !== $path) {
                    $url_locale = $localeCode;
                    break;
                }

                $parsed_url['path'] = preg_replace('%^/?' . $localeCode . '$%', '$1', $parsed_url['path']);
                if ($parsed_url['path'] !== $path) {
                    $url_locale = $localeCode;
                    break;
                }
            }
        }

        $parsed_url['path'] = ltrim($parsed_url['path'], '/');

        if ($translatedRoute = $this->findTranslatedRouteByPath($parsed_url['path'], $url_locale)) {
            return $this->getURLFromRouteNameTranslated($locale, $translatedRoute, $attributes);
        }

        if (!empty($locale) && ($locale != $this->defaultLocale || !$this->hideDefaultLocaleInURL())) {
            $parsed_url['path'] = $locale . '/' . ltrim($parsed_url['path'], '/');
        }
        $parsed_url['path'] = ltrim(ltrim($base_path, '/') . '/' . $parsed_url['path'], '/');

        //Make sure that the pass path is returned with a leading slash only if it come in with one.
        if (starts_with($path, '/') === true) {
            $parsed_url['path'] = '/' . $parsed_url['path'];
        }
        $parsed_url['path'] = rtrim($parsed_url['path'], '/');

        $url = $this->unparseUrl($parsed_url);

        if ($this->checkUrl($url)) {
            return $url;
        }

        return $this->createUrlFromUri($url);
    }

    /**
     * For JobClass
     * Get category or sub-category by slug
     *
     * @param $slug
     * @param $locale
     * @return null
     */
    public static function getCategoryBySlug($slug, $locale)
    {
        $cat = null;

        if ($slug == '' or $locale == '') {
            return $cat;
        }

        $tmpCat = Category::where('translation_lang', config('app.locale'))->where('slug', 'LIKE', $slug)->first();
        if (!empty($tmpCat)) {
            $cat = Category::transById($tmpCat->tid, $locale);
        }

        return $cat;
    }

    /**
     * For JobClass
     * Get sub-category by category translated ID and by sub-category slug
     *
     * @param $parentTid
     * @param $slug
     * @param $locale
     * @return null
     */
    public static function getSubCategoryBySlug($parentTid, $slug, $locale)
    {
        $subCat = null;

        if ($slug == '' or $locale == '') {
            return $subCat;
        }

        $tmpSubCat = Category::where('translation_lang', config('app.locale'))->where('parent_id', '=', $parentTid)->where('slug', 'LIKE', $slug)->first();
        if (!empty($tmpSubCat)) {
            $subCat = Category::transById($tmpSubCat->tid, $locale);
        }

        return $subCat;
    }

    /**
     * For LaraClassified
     * Get page by slug
     *
     * @param $slug
     * @param $locale
     * @return null
     */
    public static function getPageBySlug($slug, $locale)
    {
        $page = null;

        if ($slug == '' or $locale == '') {
            return $page;
        }

        $tmpPage = Page::where('translation_lang', config('app.locale'))->where('slug', 'LIKE', $slug)->first();
        if (!empty($tmpPage)) {
            $page = Page::transById($tmpPage->tid, $locale);
        }

        return $page;
    }

    /**
     * For JobClass
     * Don't translate these path or folders
     *
     * @return bool
     */
    public function exceptRedirectionPath()
    {
        // Use url() for this paths
        if (in_array($this->request->segment(1), [
            '_debugbar',
            'assets',
            'css',
            'js',
            'pic',
            'ajax',
            'api',
            'script',
            'tools',
            'images',
            'admin',
            'home',
        ])) {
            return true;
        }

        return false;
    }


    /**
     * Returns an URL adapted to the route name and the locale given
     *
     * @throws SupportedLocalesNotDefined
     * @throws UnsupportedLocaleException
     *
     * @param  string|boolean $locale Locale to adapt
     * @param  string $transKeyName Translation key name of the url to adapt
     * @param  array $attributes Attributes for the route (only needed if transKeyName needs them)
     *
     * @return string|false    URL translated
     */
    public function getURLFromRouteNameTranslated($locale, $transKeyName, $attributes = [])
    {
        if (!$this->checkLocaleInSupportedLocales($locale)) {
            throw new UnsupportedLocaleException('Locale \'' . $locale . '\' is not in the list of supported locales.');
        }

        if (!is_string($locale)) {
            $locale = $this->getDefaultLocale();
        }

        $route = "";

        if (!($locale === $this->defaultLocale && $this->hideDefaultLocaleInURL())) {
            $route = '/' . $locale;
        }
        if (is_string($locale) && $this->translator->has($transKeyName, $locale)) {
            $translation = $this->translator->trans($transKeyName, [], "", $locale);
            $route .= "/" . $translation;

            $route = $this->substituteAttributesInRoute($attributes, $route);

        }

        if (empty($route)) {
            // This locale does not have any key for this route name
            return false;
        }

        return rtrim($this->createUrlFromUri($route));


    }

    /**
     * It returns an URL without locale (if it has it)
     * Convenience function wrapping getLocalizedURL(false)
     *
     * @param  string|false $url URL to clean, if false, current url would be taken
     *
     * @return string           URL with no locale in path
     */
    public function getNonLocalizedURL($url = null)
    {
        return $this->getLocalizedURL(false, $url);
    }

    /**
     * Returns default locale
     *
     * @return string
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * Return an array of all supported Locales
     *
     * @throws SupportedLocalesNotDefined
     * @return array
     */
    public function getSupportedLocales()
    {
        if (!empty($this->supportedLocales)) {
            return $this->supportedLocales;
        }

        // Get supported languages from database
        try {
            $active_languages = Language::all()->toArray();
            $localizable_languages_array = [];

            if (count($active_languages)) {
                foreach($active_languages as $key => $lang) {
                    $lang['regional'] = $lang['locale'];
                    $localizable_languages_array[$lang['abbr']] = $lang;
                }

                $this->supportedLocales = $localizable_languages_array;

                return $localizable_languages_array;
            }
        } catch (\Exception $e) {
            /*
             * Database or tables don't exists.
             * The script will display an error or will start the installation.
             * Please don't change anything here.
             */
        }

        // Get supported languages from config
        $localizable_languages_array = $this->configRepository->get('laravellocalization.supportedLocales');

        if (empty($localizable_languages_array) || !is_array($localizable_languages_array)) {
            throw new SupportedLocalesNotDefined();
        }

        $this->supportedLocales = $localizable_languages_array;

        return $localizable_languages_array;
    }

    /**
     * Returns current locale name
     *
     * @return string current locale name
     */
    public function getCurrentLocaleName()
    {
        return $this->supportedLocales[$this->getCurrentLocale()]['name'];
    }

    /**
     * Returns current locale native name
     *
     * @return string current locale native name
     */
    public function getCurrentLocaleNative()
    {
        return $this->supportedLocales[$this->getCurrentLocale()]['native'];
    }

    /**
     * Returns current locale direction
     *
     * @return string current locale direction
     */
    public function getCurrentLocaleDirection()
    {

        if (!empty($this->supportedLocales[$this->getCurrentLocale()]['dir'])) {
            return $this->supportedLocales[$this->getCurrentLocale()]['dir'];
        }

        switch($this->getCurrentLocaleScript()) {
            // Other (historic) RTL scripts exist, but this list contains the only ones in current use.
            case 'Arab':
            case 'Hebr':
            case 'Mong':
            case 'Tfng':
            case 'Thaa':
                return 'rtl';
            default:
                return 'ltr';
        }

    }

    /**
     * Returns current locale script
     *
     * @return string current locale script
     */
    public function getCurrentLocaleScript()
    {
        return $this->supportedLocales[$this->getCurrentLocale()]['script'];
    }

    /**
     * Returns current language's native reading
     *
     * @return string current language's native reading
     */
    public function getCurrentLocaleNativeReading()
    {
        return $this->supportedLocales[$this->getCurrentLocale()]['native'];
    }

    /**
     * Returns current language
     *
     * @return string current language
     */
    public function getCurrentLocale()
    {
        if ($this->currentLocale) {
            return $this->currentLocale;
        }

        if ($this->useAcceptLanguageHeader()) {
            $negotiator = new LanguageNegotiator($this->defaultLocale, $this->getSupportedLocales(), $this->request);

            return $negotiator->negotiateLanguage();
        }

        // or get application default language
        return $this->configRepository->get('app.locale');
    }

    /**
     * Returns current regional
     * @return string current regional
     */
    public function getCurrentLocaleRegional()
    {
        // need to check if it exists, since 'regional' has been added
        // after version 1.0.11 and existing users will not have it
        if (isset($this->supportedLocales[$this->getCurrentLocale()]['regional']))
            return $this->supportedLocales[$this->getCurrentLocale()]['regional'];
        else
            return null;
    }

    /**
     * Returns supported languages language key
     *
     * @return array    keys of supported languages
     */
    public function getSupportedLanguagesKeys()
    {
        return array_keys($this->supportedLocales);
    }


    /**
     * Check if Locale exists on the supported locales array
     *
     * @param string|boolean $locale string|bool Locale to be checked
     * @throws SupportedLocalesNotDefined
     * @return boolean is the locale supported?
     */
    public function checkLocaleInSupportedLocales($locale)
    {
        $locales = $this->getSupportedLocales();
        if ($locale !== false && empty($locales[$locale])) {
            return false;
        }

        return true;
    }

    /**
     * Change route attributes for the ones in the $attributes array
     *
     * @param $attributes array Array of attributes
     * @param string $route string route to substitute
     * @return string route with attributes changed
     */
    protected function substituteAttributesInRoute($attributes, $route)
    {
        foreach($attributes as $key => $value) {
            $route = str_replace("{" . $key . "}", $value, $route);
            $route = str_replace("{" . $key . "?}", $value, $route);
        }

        // delete empty optional arguments that are not in the $attributes array
        $route = preg_replace('/\/{[^)]+\?}/', '', $route);

        return $route;
    }

    /**
     * Returns translated routes
     *
     * @return array translated routes
     */
    protected function getTranslatedRoutes()
    {
        return $this->translatedRoutes;
    }

    /**
     * Set current route name
     * @param string $routeName current route name
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }

    /**
     * Translate routes and save them to the translated routes array (used in the localize route filter)
     *
     * @param  string $routeName Key of the translated string
     *
     * @return string                Translated string
     */
    public function transRoute($routeName)
    {
        if (!in_array($routeName, $this->translatedRoutes)) {
            $this->translatedRoutes[] = $routeName;
        }

        return $this->translator->trans($routeName);
    }

    /**
     * Returns the translation key for a given path
     *
     * @param  string $path Path to get the key translated
     *
     * @return string|false            Key for translation, false if not exist
     */
    public function getRouteNameFromAPath($path)
    {
        $attributes = $this->extractAttributes($path);

        $path = str_replace(url('/'), "", $path);
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        $path = str_replace('/' . $this->currentLocale . '/', '', $path);
        $path = trim($path, "/");

        foreach($this->translatedRoutes as $route) {
            if ($this->substituteAttributesInRoute($attributes, $this->translator->trans($route)) === $path) {
                return $route;
            }
        }

        return false;
    }

    /**
     * Returns the translated route for the path and the url given
     *
     * @param  string $path Path to check if it is a translated route
     * @param  string $url_locale Language to check if the path exists
     *
     * @return string|false            Key for translation, false if not exist
     */
    protected function findTranslatedRouteByPath($path, $url_locale)
    {
        // check if this url is a translated url
        foreach($this->translatedRoutes as $translatedRoute) {
            if ($this->translator->trans($translatedRoute, [], "", $url_locale) == rawurldecode($path)) {
                return $translatedRoute;
            }
        }

        return false;
    }

    /**
     * Returns the translated route for an url and the attributes given and a locale
     *
     * @throws SupportedLocalesNotDefined
     * @throws UnsupportedLocaleException
     *
     * @param  string|false|null $url Url to check if it is a translated route
     * @param  array $attributes Attributes to check if the url exists in the translated routes array
     * @param  string $locale Language to check if the url exists
     *
     * @return string|false                Key for translation, false if not exist
     */
    protected function findTranslatedRouteByUrl($url, $attributes, $locale)
    {
        if (empty($url)) {
            return false;
        }

        // check if this url is a translated url
        foreach($this->translatedRoutes as $translatedRoute) {
            $routeName = $this->getURLFromRouteNameTranslated($locale, $translatedRoute, $attributes);

            if ($this->getNonLocalizedURL($routeName) == $this->getNonLocalizedURL($url)) {
                return $translatedRoute;
            }

        }

        return false;
    }

    /**
     * Returns true if the string given is a valid url
     *
     * @param  string $url String to check if it is a valid url
     *
     * @return boolean        Is the string given a valid url?
     */
    protected function checkUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }


    /**
     * Returns the config repository for this instance
     *
     * @return Repository    Configuration repository
     *
     */
    public function getConfigRepository()
    {
        return $this->configRepository;
    }


    /**
     * Returns the translation key for a given path
     *
     * @return boolean       Returns value of useAcceptLanguageHeader in config.
     */
    protected function useAcceptLanguageHeader()
    {
        return $this->configRepository->get('laravellocalization.useAcceptLanguageHeader');
    }

    /**
     * Returns the translation key for a given path
     *
     * @return boolean       Returns value of hideDefaultLocaleInURL in config.
     */
    public function hideDefaultLocaleInURL()
    {
        return $this->configRepository->get('laravellocalization.hideDefaultLocaleInURL');
    }

    /**
     * Create an url from the uri
     * @param    string $uri Uri
     *
     * @return  string  Url for the given uri
     */
    public function createUrlFromUri($uri)
    {
        $uri = ltrim($uri, "/");
        $queryString = (RequestFacade::getQueryString() ? ('?' . RequestFacade::getQueryString()) : '');

        if (empty($this->baseUrl)) {
            return app('url')->to($uri);
        }

        return $this->baseUrl . $uri;
    }

    /**
     * Sets the base url for the site
     * @param string $url Base url for the site
     *
     */
    public function setBaseUrl($url)
    {
        if (substr($url, -1) != "/")
            $url .= "/";

        $this->baseUrl = $url;
    }

    /**
     * Extract attributes for current url
     *
     * @param  string|null|false $url to extract attributes, if not present, the system will look for attributes in the current call
     *
     * @return array    Array with attributes
     *
     */
    protected function extractAttributes($url = false)
    {
        if (!empty($url)) {
            $attributes = [];
            $parse = parse_url($url);
            if (isset($parse['path'])) {
                $parse = explode("/", $parse['path']);
            } else {
                $parse = [];
            }
            $url = [];
            foreach($parse as $segment) {
                if (!empty($segment))
                    $url[] = $segment;
            }

            foreach($this->router->getRoutes() as $route) {
                $path = $route->getUri();
                if (!preg_match("/{[\w]+}/", $path)) {
                    continue;
                }

                $path = explode("/", $path);
                $i = 0;

                $match = true;
                foreach($path as $j => $segment) {
                    if (isset($url[$i])) {
                        if ($segment === $url[$i]) {
                            $i++;
                            continue;
                        }
                        if (preg_match("/{[\w]+}/", $segment)) {
                            // must-have parameters
                            $attribute_name = preg_replace(["/}/", "/{/", "/\?/"], "", $segment);
                            $attributes[$attribute_name] = $url[$i];
                            $i++;
                            continue;
                        }
                        if (preg_match("/{[\w]+\?}/", $segment)) {
                            // optional parameters
                            if (!isset($path[$j + 1]) || $path[$j + 1] !== $url[$i]) {
                                // optional parameter taken
                                $attribute_name = preg_replace(["/}/", "/{/", "/\?/"], "", $segment);
                                $attributes[$attribute_name] = $url[$i];
                                $i++;
                                continue;
                            }

                        }
                    } else if (!preg_match("/{[\w]+\?}/", $segment)) {
                        // no optional parameters but no more $url given
                        // this route does not match the url
                        $match = false;
                        break;
                    }
                }

                if (isset($url[$i + 1])) {
                    $match = false;
                }

                if ($match) {
                    return $attributes;
                }
            }

        } else {
            if (!$this->router->current()) {
                return [];
            }

            $attributes = $this->router->current()->parameters();
            $response = event('routes.translation', [$attributes]);

            if (!empty($response)) {
                $response = array_shift($response);
            }

            if (is_array($response)) {
                $attributes = array_merge($attributes, $response);
            }
        }


        return $attributes;
    }

    /**
     * Build URL using array data from parse_url
     *
     * @param array|false $parsed_url Array of data from parse_url function
     *
     * @return string               Returns URL as string.
     */
    protected function unparseUrl($parsed_url)
    {
        if (empty($parsed_url)) {
            return "";
        }

        $url = "";
        $url .= isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $url .= isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $url .= isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $url .= $user . (($user || $pass) ? "$pass@" : '');

        if (!empty($url)) {
            $url .= isset($parsed_url['path']) ? '/' . ltrim($parsed_url['path'], '/') : '';
        } else {
            $url .= isset($parsed_url['path']) ? $parsed_url['path'] : '';
        }

        $url .= isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $url .= isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

        // For JobClass : Sub-folder support. Added at version 1.3
        $default_url_parsed = parse_url(url('/'));
        if (isset($default_url_parsed['path'])) {
            $default_url_parsed['path'] = ltrim($default_url_parsed['path'], '/');
            if (!isset($parsed_url['scheme']) and $default_url_parsed['path'] != $parsed_url['path']) {
                $url = str_replace($default_url_parsed['path'], '', $url);
                $url = preg_replace('#/+#', '/', $url);
                $url = url($url);
            }
        }

        return $url;
    }
}

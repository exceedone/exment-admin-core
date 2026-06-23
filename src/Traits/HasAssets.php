<?php

namespace ExmentAdminCore\Admin\Traits;

trait HasAssets
{
    /**
     * @var array<string>
     */
    public static $script = [];

    /**
     * @var array
     */
    public static $deferredScript = [];

    /**
     * @var array<string>
     */
    public static $style = [];

    /**
     * @var array<string>
     */
    public static $css = [];

    /**
     * @var array<string>
     */
    public static $csslast = [];

    /**
     * @var array<string>
     */
    public static $js = [];

    /**
     * @var array<string>
     */
    public static $jslast = [];

    /**
     * @var array<string>
     */
    public static $html = [];

    /**
     * @var array<string>
     */
    public static $headerJs = [];

    /**
     * @var string
     */
    public static $manifest = 'vendor/open-admin/minify-manifest.json';

    /**
     * @var array<mixed>
     */
    public static $manifestData = [];

    /**
     * @var array<string, string>
     */
    public static $min = [
        'js'  => 'vendor/open-admin/open-admin.min.js',
        'css' => 'vendor/open-admin/open-admin.min.css',
    ];

    /**
     * @var array<string>
     */
    public static $baseCss = [
        'vendor/open-admin/font-awesome/css/all.min.css',
        'vendor/open-admin/font-awesome/css/v4-shims.min.css',
        'vendor/open-admin/sweetalert2/dist/sweetalert2.css',
        'vendor/open-admin/bootstrap5-editable/css/bootstrap-editable.css',
        'vendor/open-admin/nprogress/nprogress.css',
        'vendor/open-admin/sweetalert2/sweetalert2.min.css',
        'vendor/open-admin/toastify-js/toastify.css',
        'vendor/open-admin/flatpickr/flatpicker-custom.css',
        'vendor/open-admin/choicesjs/styles/choices.min.css',
        'vendor/open-admin/sortablejs/nestable.css',

        // custom open admin stuff
        // generated through sass
        'vendor/open-admin/open-admin/css/styles.css',
        'vendor/open-admin/AdminLTE4/css/adminlte.min.css',
        'vendor/open-admin/AdminLTE/dist/css/AdminLTE.min.css',
        'vendor/open-admin/open-admin/css/custom.css',
        'vendor/open-admin/toastr/build/toastr.min.css',
        "/vendor/open-admin/bootstrap-duallistbox/dist/bootstrap-duallistbox.min.css?v=4.0.2",

        'vendor/open-admin/open-admin/css/bootstrap-icons.css',
        'vendor/open-admin/bootstrap-fileinput/css/fileinput.min.css',
    ];

    /**
     * @var array<string>
     */
    public static $baseJs = [
        "vendor/open-admin/open-admin/js/bootstrap.bundle.min.js",
        "vendor/open-admin/open-admin/js/popper.min.js",
        'vendor/open-admin/bootstrap-fileinput/js/fileinput.min.js',
        "vendor/open-admin/bootstrap-fileinput/js/ja.min.js",
        'vendor/open-admin/AdminLTE/plugins/slimScroll/jquery.slimscroll.min.js',
        'vendor/open-admin/bootstrap-duallistbox/dist/jquery.bootstrap-duallistbox.min.js?v=4.0.2',
        
        'vendor/open-admin/AdminLTE/dist/js/app.min.js',
        'vendor/open-admin/AdminLTE4/js/adminlte.min.js',
        'vendor/open-admin/jquery-pjax/jquery.pjax.js',
        'vendor/open-admin/nprogress/nprogress.js',
        'vendor/open-admin/nestable/jquery.nestable.js',
        'vendor/open-admin/toastr/build/toastr.min.js',
        'vendor/open-admin/bootstrap5-editable/js/bootstrap-editable.min.js',
        'vendor/open-admin/open-admin/js/editable-init.js',
        'vendor/open-admin/sweetalert2/dist/sweetalert2.min.js',

        'vendor/open-admin/nprogress/nprogress.js',
        'vendor/open-admin/axios/axios.min.js',
        'vendor/open-admin/toastify-js/toastify.js',
        'vendor/open-admin/flatpickr/flatpickr.min.js',
        'vendor/open-admin/choicesjs/scripts/choices.min.js',
        'vendor/open-admin/sortablejs/Sortable.min.js',
     
        'vendor/open-admin/open-admin/js/polyfills.js',
        'vendor/open-admin/open-admin/js/helpers.js',
        'vendor/open-admin/open-admin/js/open-admin.js',
        'vendor/open-admin/open-admin/js/open-admin-actions.js',
        'vendor/open-admin/open-admin/js/open-admin-grid.js',
        'vendor/open-admin/open-admin/js/open-admin-grid-inline-edit.js',
        'vendor/open-admin/open-admin/js/open-admin-form.js',
        'vendor/open-admin/open-admin/js/open-admin-toastr.js',
        'vendor/open-admin/open-admin/js/open-admin-resource.js',
        'vendor/open-admin/open-admin/js/open-admin-tree.js',
        'vendor/open-admin/open-admin/js/open-admin-selectable.js',
    ];

    /**
     * @var string
     */
    public static $jQuery = 'https://code.jquery.com/jquery-3.7.1.min.js';

    /**
     * @var array
     */
    public static $minifyIgnoresCss = [];
    public static $minifyIgnoresJs = [];

    /**
     * Add css or get all css.
     *
     * @param null|array<mixed> $css
     * @param bool $minify
     *
     * @return array<mixed>|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function css($css = null, $minify = true)
    {
        static::ignoreMinify('css', $css, $minify);

        if (!is_null($css)) {
            return self::$css = array_merge(self::$css, (array) $css);
        }

        if (!$css = static::getMinifiedCss()) {
            $css = array_merge(static::$css, static::baseCss());
        }
        
        $css = array_merge($css, self::$csslast); // add css added at end
        $css = array_merge($css, static::$minifyIgnoresCss); // add minified ignored files
        $css = array_filter(array_unique($css));

        return view('admin::partials.css', compact('css'));
    }

    /**
     * @param null $css
     * @param bool $minify
     *
     * @return array<string>|null
     */
    public static function baseCss($css = null, $minify = true)
    {
        static::ignoreMinify('css', $css, $minify);

        if (!is_null($css)) {
            return static::$baseCss = $css;
        }

        $skin = config('admin.skin', 'skin-blue-light');

        array_unshift(static::$baseCss, "vendor/open-admin/AdminLTE/dist/css/skins/{$skin}.min.css");

        return static::$baseCss;
    }

    /**
     * Add js or get all js.
     *
     * @param null|array<mixed> $js
     * @param bool $minify
     *
     * @return array<mixed>|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function js($js = null, $minify = true)
    {
        static::ignoreMinify('js', $js, $minify);

        if (!is_null($js)) {
            return self::$js = array_merge(self::$js, (array) $js);
        }

        if (!$js = static::getMinifiedJs()) {
            $js = array_merge(static::baseJs(), static::$js);
        }

        $js = array_merge($js, static::$jslast); // add minified ignored files
        $js = array_filter(array_unique($js));

        return view('admin::partials.js', compact('js'));
    }

    /**
     * Add js or get all js.
     *
     * @param null $js
     *
     * @return array<mixed>|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function headerJs($js = null)
    {
        if (!is_null($js)) {
            return self::$headerJs = array_merge(self::$headerJs, (array) $js);
        }

        return view('admin::partials.js', ['js' => array_unique(static::$headerJs)]);
    }

    /**
     * @param null $js
     * @param bool $minify
     *
     * @return array<string>|null
     */
    public static function baseJs($js = null, $minify = true)
    {
        static::ignoreMinify('js', $js, $minify);

        if (!is_null($js)) {
            return static::$baseJs = $js;
        }
        array_push(static::$baseJs,         'vendor/open-admin/flatpickr/l10n/' . config('app.locale') . '.js'); //4.6.13 version
        // array_push(static::$baseJs, 'vendor/exment/js/customscript.js');
        return static::$baseJs;
    }

    /**
     * Add css at end of array.
     *
     * @param null $css
     *
     * @return array<mixed>
     */
    public static function csslast($css)
    {
        return self::$csslast = array_merge(self::$csslast, (array) $css);
    }

    /**
     * Add js at end of array.
     *
     * @param null $js
     *
     * @return array<mixed>
     */
    public static function jslast($js)
    {
        return self::$jslast = array_merge(self::$jslast, (array) $js);
    }


    /**
     * @param string $assets
     * @param bool   $ignore
     */
    public static function ignoreMinify($type, $assets, $ignore = true)
    {
        if (!$ignore) {
            if ($type == 'css') {
                static::$minifyIgnoresCss[] = $assets;
            } else {
                static::$minifyIgnoresJs[] = $assets;
            }
        }
    }

    /**
     * @param string $script
     * @param bool   $deferred
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function script($script = '', $deferred = false)
    {
        if (!empty($script)) {
            if ($deferred) {
                return self::$deferredScript = array_merge(self::$deferredScript, (array) $script);
            }

            return self::$script = array_merge(self::$script, (array) $script);
        }

        $script = collect(static::$script)
            ->merge(static::$deferredScript)
            ->unique()
            ->map(function ($line) {
                return $line;
                //@see https://stackoverflow.com/questions/19509863/how-to-remove-js-comments-using-php
                $pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/';
                $line = preg_replace($pattern, '', $line);

                return preg_replace('/\s+/', ' ', $line);
            });

        return view('admin::partials.script', compact('script'));
    }

    /**
     * get script. Pure script, ignore $(function), and script tag 
     *
     * @return \Illuminate\View\View
     */
    public static function purescript()
    {
        return view('admin::partials.purescript', ['script' => array_unique(self::$script)]);
    }

    /**
     * @param string $style
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function style($style = '')
    {
        if (!empty($style)) {
            return self::$style = array_merge(self::$style, (array) $style);
        }

        $style = collect(static::$style)
            ->unique()
            ->map(function ($line) {
                return preg_replace('/\s+/', ' ', $line);
            });

        return view('admin::partials.style', compact('style'));
    }

    /**
     * @param string $html
     *
     * @return array<mixed>|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function html($html = '')
    {
        if (!empty($html)) {
            return self::$html = array_merge(self::$html, (array) $html);
        }

        return view('admin::partials.html', ['html' => array_unique(self::$html)]);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected static function getManifestData($key)
    {
        if (!empty(static::$manifestData)) {
            return static::$manifestData[$key];
        }

        static::$manifestData = json_decode(
            file_get_contents(public_path(static::$manifest)),
            true
        );

        return static::$manifestData[$key];
    }

    /**
     * @return bool|mixed
     */
    protected static function getMinifiedCss()
    {
        if (!config('admin.minify_assets') || !file_exists(public_path(static::$manifest))) {
            return false;
        }

        return static::getManifestData('css');
    }

    /**
     * @return bool|mixed
     */
    protected static function getMinifiedJs()
    {
        if (!config('admin.minify_assets') || !file_exists(public_path(static::$manifest))) {
            return false;
        }

        return static::getManifestData('js');
    }

    /**
     * @return string
     */
    public function jQuery()
    {
        return admin_asset(static::$jQuery);
    }

    /**
     * @param $component
     */
    public static function component($component, $data = [])
    {
        $string = view($component, $data)->render();

        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $string);
        libxml_use_internal_errors(false);

        if ($head = $dom->getElementsByTagName('head')->item(0)) {
            foreach ($head->childNodes as $child) {
                if ($child instanceof \DOMElement) {
                    if ($child->tagName == 'style' && !empty($child->nodeValue)) {
                        static::style($child->nodeValue);
                        continue;
                    }

                    if ($child->tagName == 'link' && $child->hasAttribute('href')) {
                        static::css($child->getAttribute('href'));
                    }

                    if ($child->tagName == 'script') {
                        if ($child->hasAttribute('src')) {
                            static::js($child->getAttribute('src'));
                        } else {
                            static::script(';(function () {' . $child->nodeValue . '})();');
                        }

                        continue;
                    }
                }
            }
        }

        $render = '';

        if ($body = $dom->getElementsByTagName('body')->item(0)) {
            foreach ($body->childNodes as $child) {
                if ($child instanceof \DOMElement) {
                    if ($child->tagName == 'style' && !empty($child->nodeValue)) {
                        static::style($child->nodeValue);
                        continue;
                    }

                    if ($child->tagName == 'script' && !empty($child->nodeValue)) {
                        static::script(';(function () {' . $child->nodeValue . '})();');
                        continue;
                    }

                    if ($child->tagName == 'template') {
                        if ($child->getAttribute('render') == 'true') {
                            // this will render the template tags right into the dom. Don't think we want this
                            $html = '';
                            foreach ($child->childNodes as $childNode) {
                                $html .= $child->ownerDocument->saveHTML($childNode);
                            }
                        } else {
                            // this leaves the template tags in place, so they won't get rendered right away
                            $sub_doc = new \DOMDocument();
                            $sub_doc->appendChild($sub_doc->importNode($child, true));
                            $html = $sub_doc->saveHTML();
                        }
                        $html && static::html($html);

                        continue;
                    }
                }

                $render .= $body->ownerDocument->saveHTML($child);
            }
        }

        return trim($render);
    }
}

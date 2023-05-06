{{--<script type="importmap">--}}
{{--  {--}}
{{--    "imports": {--}}
{{--      "vue": "https://unpkg.com/vue@3/dist/vue.esm-browser.js",--}}
{{--      "VueRouter": "https://unpkg.com/vue-router@4"--}}
{{--    }--}}
{{--  }--}}
{{--</script>--}}
<script type="module">
    // import { createApp } from 'vue'
    var vueApp;

        // $(document).ready(function() {
        @if($vueWithRouter)

        $( document ).ajaxSend(function() {
            $.ti.loadingIndicator.show()
        });
        $( document ).ajaxComplete(function() {
            $.ti.loadingIndicator.hide()
        });

        var routerMap = [
            {
                path: '/',
                component: {}
            },
            {
                path: '/404',
                component: getPageComponent("/404"),
            },
            {
                path: '/:pathMatch(.*)*',
                redirect: to => {
                    return {path: '/404'}
                }
            },
            @foreach($vueThemePages as $themePage)
            @continue($themePage->permalink === '/')
            @continue($themePage->permalink === '/404')
            {
                path: "{{$themePage->permalink}}",
                component: getPageComponent("{{$themePage->permalink}}")
            },
            @endforeach
        ];

        var baseUrl = "{{ $baseUrl }}";
        if (baseUrl != '/') {
            baseUrl = '/' + baseUrl;
        }

        var Router = VueRouter.createRouter($.extend({
            history: VueRouter.createWebHashHistory(),
            routes: routerMap
        }, JSON.parse('@json([])')));

    //fix oc ajax framework request url
    // Router.beforeEach( function (transition) {
    //     var path = transition.to.path;
    //     if (baseUrl != '/') {
    //         path = baseUrl + path;
    //     }
    //
    //     $.ajaxSetup({
    //         url: path
    //     });
    //
    //     transition.next();
    // });

        vueApp = Vue.createApp({
            data() {
                return {
                    components: parseComponentsList(JSON.parse('@json($vueComponents)'))
                }
            }
        })

        vueApp.use(Router).mount('{{$vueAppSelector}}')

    // Router.redirect({
    //     '*': '/404'
    // });

        {{--Router.start(vueApp, '{{$vueAppSelector}}');--}}

        // multiple version of jQuery.getScript()
        $.getMultiScripts = function(arr, path) {
            var _arr = $.map(arr, function(scr) {
                return $.getScript( (path||"") + scr );
            });

            _arr.push($.Deferred(function( deferred ){
                $( deferred.resolve );
            }));

            return $.when.apply($, _arr);
        }

        // returns vue component for page
        function getPageComponent(url) {

            return function(resolve, reject) {
                //request page data (template, assets, vue components)
                $.ajax(url, {
                    cache: false,
                    success: function(data, status, request) {
                        //download and execute javascripts
                        $.getMultiScripts(data.assets.js).done(function() {
                            var components = parseComponentsList(data.components);
                            //return complete vue component
                            resolve({
                                template: data.template,
                                components: components,
                            });
                        });
                        //download and attach css styles
                        data.assets.css.forEach(function(item, i, arr) {
                            $('<link/>', {
                                rel: 'stylesheet',
                                type: 'text/css',
                                href: item
                            }).appendTo('head');
                        });
                    }
                });

            }
        }

        @else

        vueApp = Vue.createApp({
            data() {
                return {
                    components: parseComponentsList(JSON.parse('@json($vueComponents)'))
                }
            }
        }).mount('{{$vueAppSelector}}')

        @endif


        function parseComponentsList(components_arr) {
            //all page's vue components
            var components = {};
            for (var compTag in components_arr) {
                var compName = components_arr[compTag];
                components[compTag] = eval(compName);
            }

            return components
        }
    // });
</script>
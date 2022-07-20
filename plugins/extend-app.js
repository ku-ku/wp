import Vue from 'vue';
import Vuetify from 'vuetify';
import WpMsg from '~/components/WpMsg';

if (
                    (typeof window["$"] === "undefined")
                ||  (typeof window["$"].ajax === "undefined")
               ){
    window["$"] = require("jquery");
}

export default async function( ctx ){
      
    const { app, env } = ctx;
    
    if (!app.mixins) {
        app.mixins = [];
    }
    
    /**
     * for snack-bar messages (see methods.msg -> $nuxt.msg)
     */
    var appMsg = null;
    
    var conte = $(".page-content");
    if ( conte.length > 0 ){        //attache root to bx-conte
        conte.append("<div></div>");
        conte = conte.children().last().get(0);
        app.el = conte;
    }
            
    app.mixins.push({
        beforeCreate(){
            if (typeof env.YAM_ID !== "undefined"){
               (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
                m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
               (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
                ym(env.YAM_ID, "init", {
                     clickmap:false,
                     trackLinks:false,
                     accurateTrackBounce:false
                });
            }
        },
        methods: {
            /**
             * Call back-end api
             * @param {String} q - that a query (required)
             * @param {Object?} params - add`s
             */
            async api(q, params){
                const opts = {
                    url: env.apiUrl,
                    data: {
                        q: q,
                    },
                    dataType: "json"
                };
                if (typeof params !== "undefined"){
                    opts.data.params = params;
                } else {
                    opts.data.params = {};
                }
                // +period always
                const p = $nuxt.$store.getters['period'];
                opts.data.params.period = {
                    start: p.start.toISOString(),
                    end: p.end.toISOString()
                };

                return $.ajax(opts);
            },
            /**
             * Messaging: show/hide app-message on snackbar
             * @param {Object} msg text, color?, timeout?
             */
            msg(msg){
                if (!(!!appMsg)){
                    const el = $('<div id="app-msg"></div>').appendTo($(this.$el).find('.v-application'));
                    const c = new Vue({
                                functional: true,
                                el: el.get(0),
                                vuetify: new Vuetify(this.$vuetify.theme.themes),
                                components: { WpMsg },
                                render(h){return h(WpMsg, {ref:"app-msg"});}
                            });
                    appMsg = c.$refs["app-msg"];
                }
                return appMsg.show(msg);
            }
        }       //methods
    });
};

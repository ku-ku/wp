import Vue from 'vue';
import Vuetify from 'vuetify';
import WpMsg from '~/components/WpMsg';
import mom from "moment";
mom.locale("ru");
window["$moment"] = mom;


import '@mdi/font/css/materialdesignicons.css';


if (
                    (typeof window["$"] === "undefined")
                ||  (typeof window["$"].ajax === "undefined")
               ){
    window["$"] = require("jquery");
}

Vue.prototype.$eventHub = new Vue();


export default async function( ctx ){
    const { app, env, store } = ctx;
    
    if (!app.mixins) {
        app.mixins = [];
    }
    
    
    /**
     * for snack-bar messages (see methods.msg -> $nuxt.msg)
     * appMsg = null,
     */
    var worker = null;
    
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
            worker = new Worker( ctx.isDev ? "ws.js" : '/wp/app/ws.js' );
            worker.onmessage = ({data}) => {
                if (
                        (data.success)
                     && (data.type==="read")
                    ) {
                    store.commit("data/set", data);
                }
            };
            worker.postMessage({type:"init", env});
        },
        mounted(){
            var n = 0;
            const _get = async()=>{
                try {
                    await store.dispatch("data/user");
                    const u = store.state.data.user;
/*                    
                    if (u.haswp){
                        setTimeout(() => worker.postMessage({type:"read"}), 500);
                    }
*/
                } catch(e) {
                   n++;
                   if (n < 3){
                       setTimeout(_get, 500);
                   }
                }
            };
            _get();
        },
        beforeDestroy(){
            try {
                if (!!worker){
                    worker.terminate();
                }
            } catch(e){}
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
                
                if ("user" === q){
                    opts.async= false;
                }
                
                // +period always sending
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
            },
             */
            cache(data){
                if (1==1){
                    return;
                }
                if (!!worker){
                    worker.postMessage({type:"save", data});
                }
            },
            hidextras(){
                $("body > .header").css({display: "none"});
                $(".content .content-header").css({display: "none"});
                //$(".v-main").css({padding: "initial"});
            }
        }       //methods
    });
};

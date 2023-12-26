<template>
    <v-form v-on:submit.stop.prevent="save">
        <v-row>
            <v-col cols="6">
                <v-text-field label="Текущий год" 
                              v-model="old"
                              required>
                </v-text-field>
            </v-col>
            <v-col cols="6">
                <v-text-field label="Новый год" 
                              autofocus
                              v-model="to"
                              required>
                </v-text-field>
            </v-col>
        </v-row>
    </v-form>
</template>
<script>
import { mxForm } from '~/utils/mxForm.js';    
export default {
    name: 'WpMoveReds',
    mixins: [ mxForm ],
    inject: ["needs"],
    data(){
        return {
            old: 0,
            to:  0
        };
    },
    created(){
        this.old = (new Date()).getFullYear();
        this.to = this.old + 1;
        this.needs("title", "Перенос праздничных дней");
    },
    methods: {
        validate(){
            return (Number.parseInt(this.old) > 1900)
                 &&(Number.parseInt(this.to) > 1900);

        },
        async save(){
            console.log(`moving ${ this.old } - ${ this.to }`);
            const opts = {
                    url: $nuxt.context.env.apiUrl,
                    data: {
                        q: "reds",
                        params: {
                                    period: {
                                        start: $moment([this.old, 0, 1]).toISOString(),
                                        end:   $moment([this.old, 11, 31, 23, 59, 59]).toISOString()
                                    }
                        }
                    },
                    dataType: "json"
            };
            try {
                const from = await $.ajax(opts);
                console.log("from" , from);
                opts.data.params.period = {
                    start: $moment([this.to, 0, 1]).toISOString(),
                    end:   $moment([this.to, 11, 31, 23, 59, 59]).toISOString()
                };
                const to = await $.ajax(opts);
                let errs = [], all = 0, added = 0;
                from.filter( d1 => d1.UF_YEARATTR ).forEach( async d1 => {
                    all++;
                    let n = to.findIndex( d2 => d1.UF_TEXT.toLowerCase()===d2.UF_TEXT.toLowerCase() );
                    if ( n > -1){
                        return;
                    }
                    
                    try {
                        let res = await $nuxt.api("reds", {
                                action: "save",
                                item: {
                                    ID: -1,
                                    UF_TEXT: d1.UF_TEXT,
                                    UF_YEARATTR: d1.UF_YEARATTR,
                                    UF_WWWATTR:  d1.UF_WWWATTR,
                                    UF_ADT: $moment(d1.UF_ADT).add(1, "year").toISOString()
                                }
                        });
                        if (!res.success){
                            throw {message: res.error};
                        }
                        added++;
                    } catch(e){
                        errs.push(d1);
                        console.log('ERR (day)', d1, e);
                    }
                });
                $nuxt.msg({
                    text: `Скопировано ${ added } из ${ all } записей ( ошибок- ${ (errs.length > 0) ? errs.length : 'нет' } )`
                });
                if ( errs.length < 1 ){
                    this.$emit("success");
                }
            } catch(e){
                console.log('ERR (move reds)', e);
                this.$emit("error", e);
            }
        }   //save
    }
}    
</script>
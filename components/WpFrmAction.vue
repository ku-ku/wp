<template>
<v-form class="wp-action">
    <v-row>
        <v-col cols="4">
            <wp-date-input label="Дата, время проведения"
                            :value="item.UF_ADT"
                            :error="errs.UF_ADT"
                            v-on:change="set('UF_ADT', $event)">
            </wp-date-input>
        </v-col>
        <v-col cols="8">
            <v-autocomplete dense
                            full-width
                            label="Подразделение"
                            no-data-text="нет данных"
                            v-model="item.UF_DVS"
                            hide-no-data
                            item-value="ID"
                            clearable
                            required
                            hide-details
                            :error="errs.UF_DVS"
                            :filter="filterByName"
                            :items="divisions">
                <template v-slot:selection="{ item }">
                    <div class="text-truncate">
                        {{item.UF_CODE}}. {{item.UF_NAME}}
                    </div>    
                </template>
                <template v-slot:item="{ item }">
                    <div class="text-truncate">
                        {{item.UF_CODE}}. {{item.UF_NAME}}
                    </div>    
                </template>
            </v-autocomplete>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="4">
            <v-checkbox
                v-model="item.UF_GRATTR"
                label="Согласно графику"
                value="1"
                color="primary"
                hide-details>
            </v-checkbox>
        </v-col>
        <v-col cols="4">
            <v-checkbox
                v-model="item.UF_DAYATTR"
                label="В течение дня"
                value="1"
                color="primary"
                hide-details>
            </v-checkbox>
        </v-col>
        <v-col cols="4">
            <v-checkbox
                v-model="item.UF_SPECATTR"
                label="Особой важности"
                color="red"
                value="1"
                hide-details>
            </v-checkbox>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
            <v-textarea label="Мероприятие" rows="3"
                        v-model="item.UF_TEXT"
                        :error="errs.UF_TEXT"></v-textarea>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
            <v-combobox dense
                            full-width
                            no-data-text="нет данных"
                            v-model="item.UF_PLACE"
                            hide-no-data
                            clearable
                            required
                            hide-details
                            item-value="ID"
                            item-text="UF_PLACE"
                            label="Место проведения"
                            :items="places"
                            :error="errs.UF_PLACE">
            </v-combobox>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
            <v-autocomplete dense
                            full-width
                            label="Руководитель"
                            no-data-text="нет данных"
                            v-model="item.UF_CHIEF"
                            hide-no-data
                            item-value="ID"
                            item-text="UF_EMPNAME"
                            clearable
                            required
                            hide-details
                            :error="errs.UF_CHIEF"
                            :items="employees">
            </v-autocomplete>
            <v-row class="mt-5">
                <v-col cols="6">
                    <v-btn small 
                           block 
                           tile
                           v-on:click="adds('HEADS')">
                        <v-badge :content="get('HEADS')"
                                 v-bind:class="{'no-val': !get('HEADS')}"
                                 color="secondary lighten-4">
                            Дополнительно...
                        </v-badge>
                    </v-btn>
                    <div class="sub text-truncate"
                         v-if="get('HEADS')">
                        {{get('HEAD-NAMES')}}
                    </div>
                </v-col>
                <v-col cols="6">
                    <v-btn small 
                           block 
                           tile
                           v-on:click="adds('EMPS')">
                        <v-badge :content="get('EMPS')"
                                 v-bind:class="{'no-val': !get('EMPS')}"
                                 color="secondary lighten-4">
                           Ответственные за подготовку...
                        </v-badge>
                    </v-btn>
                    <div class="sub text-truncate"
                         v-if="get('EMPS')">
                        {{get('EMP-NAMES')}}
                    </div>
                </v-col>
            </v-row>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="2">
            <v-checkbox
                v-model="item.UF_READY"
                label="ГОТОВО"
                color="primary"
                value="1"
                hide-details>
            </v-checkbox>
        </v-col>
        <v-col cols="4">
            <v-checkbox
                v-model="item.UF_WWWATTR"
                label="Публиковать в www"
                color="green accent-4"
                value="1"
                hide-details>
            </v-checkbox>
        </v-col>
        <v-col cols="6">
            <v-autocomplete v-model="item.UF_STATUS"
                            dense
                            full-width
                            label="Отметка о проведении"
                            no-data-text="нет данных"
                            hide-no-data
                            clearable
                            required
                            hide-details
                            item-value="ID"
                            item-text="name"
                            :items="statuses">
            </v-autocomplete>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
            <v-textarea label="Примечание" rows="2"
                        v-model="item.UF_COMMENTS"></v-textarea>
        </v-col>
    </v-row>
    <v-row class="wp-action__meta" v-if="!has('add')">
        <v-col cols="12">
            <span><v-icon small>mdi-account</v-icon>{{item.UF_AUTHOR}}</span>
            <span><v-icon small>mdi-clock</v-icon>{{_fmt_dt(item.UF_INSTIME)}}</span>
        </v-col>
    </v-row>
    <wp-dialog ref="dlg"
               :mode="DIA_MODES.emplist" 
               v-on:change="onadds" />
</v-form>
</template>
<script>
import $moment from "moment";
import { mxForm } from '~/utils/mxForm.js';
import { DIA_MODES, empty } from "~/utils/";
import { mapState } from 'vuex';

const DEF_ITEM = {
    ID: -1,
    UF_RED: 0,
    UF_ADT: null,
    UF_DVS: null,
    UF_GRATTR: 0,
    UF_DAYATTR: 0,
    UF_SPECATTR: 0,
    UF_WWWATTR: 0,
    UF_TEXT: null,
    UF_PLACE: null,
    UF_CHIEF: null,
    UF_STATUS: null,
    UF_COMMENTS: null    
};

const FIX_ITEM = Object.assign(DEF_ITEM, {fixed: false});


export default {
    name: "WpFrmAction",
    mixins: [ mxForm ],
    fetchOnServer: false,
    components: {
        WpDialog: () => import("~/components/WpDialog.vue")
    },
    beforeCreate(){
        /** preload's */
        const store = $nuxt.$store;
        store.dispatch("data/list", "divisions").then(()=>{
            setTimeout(()=>{
                store.dispatch("data/list", "employees").then(()=>{
                    setTimeout(()=>{
                        store.dispatch("data/list", "places");
                    }, 300);
                })
            }, 300);
        });
    },
    data(){
        return {
            DIA_MODES,
            error: null,
            meta_key: 'HEADS',        //UF_META key: headers(HEADS) | responsibles(EMPS): see adds()
            item: DEF_ITEM,
            errs: {}
        };
    },
    computed: {
                divisions(){
                    return this.$store.getters["data/divisions"];
                },
                ...mapState({
                    statuses:  state => state.data.statuses,
                    employees: state => state.data.employees,
                    places:    state => state.data.places.map( p => p.UF_PLACE )
                })
    },
    methods: {
        async use(item){
            this.errs = {};
            this.item = {
                ID: item.ID || -1
            };
            if ( this.item.ID > 0 ){
                const resp = await $nuxt.api("acts", { ID: this.item.ID }); //full read data by ID
                this.item = Array.isArray(resp) ? resp[0] : {};
                this.item.UF_STATUS = Number(this.item.UF_STATUS);
            } else {
                if (!item.UF_ADT){
                    item.UF_ADT = new Date();
                }
                item.UF_DVS = this.$store.state.data.division?.ID;
                if (FIX_ITEM.fixed){
                    if (item.UF_DVS != FIX_ITEM.UF_DVS){
                        FIX_ITEM.fixed = false; //reset on DVS-changed
                        this.$emit("defix");
                    }
                }
                //restore saved value`s
                if (FIX_ITEM.fixed){
                    item.UF_DVS  = FIX_ITEM.UF_DVS;
                    item.UF_TEXT = FIX_ITEM.UF_TEXT;
                    item.UF_PLACE= FIX_ITEM.UF_PLACE;
                    item.UF_CHIEF= FIX_ITEM.UF_CHIEF;
                    item.UF_STATUS=FIX_ITEM.UF_STATUS;
                    item.EMPS    = FIX_ITEM.EMPS;
                }
                this.item = item;
            }
        },
        /**
         * Call add's choose dlg
         * @param {string} q HEADS | EMPS for list
         */
        adds(q){
            //get choose dlg
            this.meta_key = q;
            const arr = this.item[q];
            this.$refs["dlg"].open(arr);
        },
        onadds(e){
            this.item[this.meta_key] = e; 
            this.$forceUpdate();
        },
        set(q, val){
            switch(q){
                case "fix":
                    FIX_ITEM.fixed = !FIX_ITEM.fixed;
                    if (FIX_ITEM.fixed){
                        this.fixfix();
                    }
                    return FIX_ITEM.fixed;
                default:
                    this.item[q] = val;
                    break;
            }
        },
        get(q){
            switch(q){
                case "HEADS":
                case "EMPS":
                    return (this.item.hasOwnProperty(q)) 
                                ? this.item[q]?.length
                                : null;
                case "HEAD-NAMES":
                case "EMP-NAMES":
                    var names = [];
                    this.item[(q==="HEAD-NAMES") ? "HEADS" : "EMPS"]?.map( id => {
                        const n = this.employees?.findIndex( e => (e.ID === id) );
                        if ( n > -1){
                            var s = this.employees[n].UF_EMPNAME;
                            var a = s.split(/\s+/g);
                            if (a.length > 2){
                                s = `${a[0]} ${a[1].substr(0, 1)}.${a[2].substr(0, 1)}.`;
                            }
                            names.push(s);
                        }
                    });
                    return names.join(', ');
            }
            return false;
        },
        filterByName(item, s){
            if ( empty(s) || (s.length < 2) ){
                return true;
            }
            const re = new RegExp('(' + s + ')+', 'gi');
            return (
                            re.test(item.hasOwnProperty("LAST_NAME") ? item.LAST_NAME : item.UF_NAME) 
                        || ( item.hasOwnProperty("UF_CODE") && re.test(item.UF_CODE) )
                    );
        },
        validate(){
            const _RQS = ["UF_ADT", "UF_DVS", "UF_TEXT", "UF_PLACE", "UF_CHIEF"],
                  errs = { n: 0 };
            _RQS.map( r => {
                if ( empty(this.item[r]) ){
                    errs.n++;
                    errs[r] = true;
                }
            });
            this.errs = errs;
            return (errs.n === 0);
        },
        fixfix(){
            Object.keys(this.item).map( k => {
                FIX_ITEM[k] = this.item[k];
            });
        },
        async save(){
            console.log('saving', this.item);
            if (this.$fetchState?.pending){
                return;
            }
            this.$fetchState = {
                pending: true,
                timestamp: Date.now()
            };
            try {
                this.item.UF_ADT = $moment(this.item.UF_ADT).toDate();
                await this.$store.dispatch("data/upd", {acts: this.item});
                this.$emit("success", this.item);
                if (FIX_ITEM.fixed){
                    this.fixfix();
                }
            } catch(e){
                this.$fetchState = {
                    error: e
                };
                this.$emit("error", e);
            } finally {
                this.$fetchState.pending = false;
            }
            return false;
        }   //save
    }   //methods
}
</script>
<style lang="scss">
    form.wp-action{
        & textarea {
            line-height: 1.25;
        }
        & .wp-action__meta{
            & .col{
                display: flex;
                justify-content: flex-end;
                align-items: center;
                font-size: 0.75rem;
                color: var(--v-secondary-base);
                & .v-icon{margin-right: 0.35rem;}
                & > * {margin-right: 1rem;}
            }
        }
        & .v-badge{
            &.no-val{
                & .v-badge__badge{
                    display: none;
                }
            }
        }
        & .sub{
            color: var(--v-secondary-base);
            font-size: 0.75rem;
        }
    }        
</style>
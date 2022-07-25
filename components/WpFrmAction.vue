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
                label="В течении дня"
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
            <v-textarea label="Мероприятие" rows="2"
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
                            label="Место проведения"
                            :items="places"
                            item-value="NAME"
                            :error="errs.UF_PLACE"
                            >
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
                            item-value="ID"
                            clearable
                            required
                            hide-details
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


export default {
    name: "WpFrmAction",
    mixins: [ mxForm ],
    fetchOnServer: false,
    components: {
        WpDialog: () => import("~/components/WpDialog.vue")
    },
    beforeCreate(){
        /** preload's */
        (async ()=>{
            try {
                await $nuxt.$store.dispatch("data/list", "statuses");
                await $nuxt.$store.dispatch("data/list", "divisions");
                await $nuxt.$store.dispatch("data/list", "employees");
                await $nuxt.$store.dispatch("data/list", "places");
            } catch(e){
                console.log('ERR (action dirs)', e);
            }
        })();
    },
    async fetch(){
        const ID = this.item?.ID || -1;
        console.log("fetching act #", ID);
        if ( ID > 0 ){
            const resp = await $nuxt.api("acts", { ID }); //full read data
            this.item = Array.isArray(resp) ? resp[0] : {};
            this.item.UF_STATUS = Number(this.item.UF_STATUS);
        }
    },
    data(){
        return {
            DIA_MODES,
            error: null,
            meta_key: 'HEADS',        //UF_META key: headers(HEADS) | responsibles(EMPS): see adds()
            item: {
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
            },
            errs: {}
        };
    },
    computed: mapState({
        statuses:  state => state.data.statuses,
        divisions: state => state.data.divisions,
        employees: state => state.data.employees,
        places:    state => state.data.places
    }),
    methods: {
        use(item){
            this.item = {ID: item.ID};
            this.$fetch();
            //this.$fetch();
        },
        /**
         * Call add's choose dlg
         * @param {string} q HEADS | EMPS for list
         */
        adds(q){
            this.meta_key = q;
            //get choose dlg
            const arr = this.item[q]
            this.$refs["dlg"].open(arr);
        },
        onadds(e){
            this.item[this.meta_key] = e; 
            this.$forceUpdate();
        },
        set(q, val){
            console.log(`change ${q}`, val);
            switch(q){
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
            }
            return false;
        },
        filterByName(item, s){
            if ( empty(s) || (s.length < 2) ){
                return true;
            }
            const re = new RegExp('(' + s + ')+', 'gi');
            return re.test(item.hasOwnProperty("LAST_NAME") ? item.LAST_NAME : item.UF_NAME);
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
        async save(){
            console.log('saving', this.item);
            try {
                this.item.UF_ADT = $moment(this.item.UF_ADT).toDate();
                await this.$store.dispatch("data/upd", {acts: this.item});
                this.$emit("success", this.item);
            } catch(e){
                this.$emit("error", e);
            }
            return false;
        }   //save
        
    }   //methods
}
</script>
<style lang="scss">
    form.wp-action{
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
    }        
</style>
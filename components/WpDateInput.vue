<template>
    <v-text-field :label="label"
                  v-model="text"
                  :error="!valid"
                  v-on:blur="validate"
                  v-bind:class="{timed: type==='datetime'}">
        <template v-slot:append-outer>
            <v-menu ref="menu"
                    v-model="menu"
                    :close-on-content-click="false"
                    :return-value.sync="date"
                    transition="scale-transition"
                    offset-y
                    min-width="auto">
                <template v-slot:activator="{ on, attrs }">
                    <v-btn icon v-on="on"><v-icon small>mdi-calendar</v-icon></v-btn>
                </template>
                <v-date-picker v-model="date"
                               no-title
                               locale="ru-ru"
                               scrollable>
                </v-date-picker>
            </v-menu>
        </template>
    </v-text-field>
</template>
<script>
import moment from "moment";
window["$moment"] = moment;
import Inputmask from "inputmask";
import { empty } from "~/utils";


export default {
    name: "WpDateInput",
    props: {
        label: {
            type: String,
            required: true
        }, 
        value: {
            type: [String, Date]
        },
        type: {
            type: String,
            default: "datetime" /** date | datetime */
        }
    },
    data(){
        return {
            menu: false,
            text: null,
            valid: true
        }
    },
    mounted(){
        this.$nextTick(()=>{
            const mask = ("datetime"===this.type) ? "99.99.9999 99:99" : "99.99.9999";
            Inputmask({mask: mask}).mask($(this.$el).find("input").get(0));
        });
    },
    computed: {
        mask(){
            return ("datetime"===this.type) ? "DD.MM.YYYY HH:mm" : "DD.MM.YYYY";
        },
        /** for picker */
        date: {
            get(){
                const m = moment(this.text, this.mask);
                return ( !empty(this.text)&&m.isValid() ) ? m.toISOString() : null;
            },
            set(dt){
                if (!empty(dt)){
                    this.text = moment(dt, "YYYY-MM-DD").format(this.mask);
                }
                this.menu = false;
                $(this.$el).find("input").trigger("focus");
            }
        }
    },
    methods: {
        validate(){
            const _empty = empty(this.text);
            const m = _empty ? moment.invalid() : moment(this.text, this.mask);
            this.valid = _empty || m.isValid();
            if (this.valid) {
                this.$emit('change', _empty ? null : m.toDate() );
            }
            return this.valid;
        }
    },
    watch: {
        value: {
            immediate: true, 
            handler(val) {
                this.valid = true;
                if ( empty(val) ){
                    this.text = null;
                } else {
                    const m = moment(val);
                    this.text = m.isValid() ? m.format(this.mask) : val;
                }
            }
        }
    }
};
</script>
<style lang="scss" scoped>
    .v-text-field{
        max-width: 12rem;
        &.timed{
            max-width: 16rem;
        }
    }
</style>
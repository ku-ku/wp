<template>
    <v-text-field :label="label"
                  v-model="text"
                  :error="!valid"
                  v-on:blur="validate">
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

const mask = 'DD.MM.YYYY HH:mm';

export default {
    name: "WpDateInput",
    props: {
        label: {
            type: String,
            required: true
        }, 
        value: {
            type: [String, Date],
            required: true
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
            Inputmask({mask: "99.99.9999 99:99"}).mask($(this.$el).find("input").get(0));
        });
    },
    computed: {
        /** for picker */
        date: {
            get(){
                const m = moment(this.text, mask);
                return ( !empty(this.text)&&m.isValid() ) ? m.toISOString() : null;
            },
            set(dt){
                if (!empty(dt)){
                    this.text = moment(dt, "YYYY-MM-DD").format(mask);
                }
                this.menu = false;
                $(this.$el).find("input").trigger("focus");
            }
        }
    },
    methods: {
        validate(){
            const m = moment(this.text, mask);
            if ( empty(this.text) ){
                this.valid = true;
            } else {
                this.valid = m.isValid();
            }
            if (this.valid && !empty(this.text)) {
                this.$emit('change', m.toDate() );
            }
            return this.valid;
        }
    },
    watch: {
        value: {
            immediate: true, 
            handler (val) {
                console.log('set a dt', val);
                this.valid = true;
                this.text = (val instanceof Date) ? moment(val).format(mask) : val;
            }
        }
    }
};
</script>
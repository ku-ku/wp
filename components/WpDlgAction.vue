<template>
        <v-form>
                    <v-row>
                        <v-col cols="4">
                            <wp-date-input label="Дата, время проведения"
                                           :value="regDt"
                                           v-on:change="set('regDt', $event)">
                            </wp-date-input>
                        </v-col>
                        <v-col cols="8">
                            <v-autocomplete dense
                                            full-width
                                            label="Подразделение"
                                            no-data-text="нет данных"
                                            :items="divisions">
                            </v-autocomplete>
                        </v-col>
                    </v-row>
        </v-form>
</template>
<script>

export default {
    name: "WpDlgAction",
    props: ['id'],
    data(){
        return {
            regDt: null
        }
    },
    computed: {
        divisions(){

        }
    },
    methods: {
        has(q){
            switch(q){
                case "add":
                    return this.id < 0;
            }
        },
        set(q, val){
            console.log('set', q, val);
            this[q] = val;
        }
    }   //methods
}
</script>

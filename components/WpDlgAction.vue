<template>
    <v-dialog v-model="show"
              max-width="800">
        <v-toolbar dark dense color="primary">
            <v-icon small>mdi-bank-plus</v-icon>&nbsp;{{ has('add') ? 'Новое мероприятие' : 'Редактирование'}}
            <v-spacer />
            <v-btn small icon v-on:click="show = false">
                <v-icon>mdi-close</v-icon>
            </v-btn>
        </v-toolbar>
        <v-form>
            <v-card>
                <v-card-text>
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
                </v-card-text>
                <v-card-actions>
                    <v-btn small tile color="primary"
                           type="submit">
                        <v-icon small>mdi-file-send-outline</v-icon>сохранить
                    </v-btn>
                    <v-btn small tile outlined color="secondary"
                           v-on:click="show = false">
                        <v-icon small>mdi-close</v-icon>закрыть
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-form>
    </v-dialog>
</template>
<script>

export default {
    name: "WpDlgAction",
    data(){
        return {
            show: false,
            id: -1,
            regDt: null
        }
    },
    computed: {
        divisions(){

        }
    },
    methods: {
        open(id){
            this.id = id;
            this.show = (new Date()).getTime();
            if (id < 0){
                this.regDt = new Date();
            }
        },
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
<style lang="scss" scoped>
    .v-card{
        &__actions{
            justify-content: flex-end;
        }
    }
</style>
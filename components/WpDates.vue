<template>
    <v-dialog v-model="show"
              max-width="480">
        <v-form v-on:submit.stop.prevent="ok" 
                ref="form">
            <v-card class="wp-dates">
                <v-card-title>Задайте период</v-card-title>
                <v-card-text>
                    <v-row>
                        <v-col cols="6">
                            <wp-date-input label="Дата, время начала"
                                           v-model="start"
                                           :rules="[ rules.empty ]" 
                                           v-on:change="start = $event" />
                        </v-col>
                        <v-col>
                            <wp-date-input label="Дата, время окончания"
                                           v-model="end"
                                           :rules="[ rules.empty ]" 
                                           v-on:change="end = $event" />
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-card-actions>
                    <v-btn small
                           tile 
                           outlined
                           color="secondary"
                           v-on:click="close">
                        Отмена
                    </v-btn>
                    <v-btn small 
                           tile 
                           color="primary"
                           type="submit">
                        Ок
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-form>    
    </v-dialog>
</template>
<script>
import moment from "moment";
import { empty } from "~/utils";
    
export default {
    name: 'WpDates',
    data(){
        return {
            show:  false,
            start: null,
            end:   null,
            _r: null,       //Promise.resolv
            rules: {
                empty: val => !empty(val) || "Это поле должно быть заполнено"
            }
        };
    },
    methods: {
        async open(){
            const p = this.$store.getters['period'];
            const d = new Date(p.start.year(), p.start.month());
            this.start = d;
            this.end   = $moment(d).add(1, 'month').add(-1, 'seconds').toDate();
            
            this.show = (new Date()).getTime();
            
            return new Promise( resolve =>{
                this._r = resolve;
            });
        },
        ok(){
            if ( !this.$refs["form"].validate() ){
                $nuxt.msg({text:'Необходимо заполнить обе даты', timeout: 6000, color: 'warning'});
                return false;
            }
            
            if (this._r){
                this._r({
                            start: this.start, 
                            end:   this.end
                        });
                this._r = null;
            }
            this.show = false;
            
            return false;
        },
        close(){
            if (this._r){
                this._r(null);
                this._r = null;
            }
            this.show = false;
        }
    },
    watch: {
        start(val){
            var val = val || new Date();
            const d = new Date(val.getFullYear(), val.getMonth());
            this.end = $moment(d).add(1, 'month').add(-1, 'seconds').toDate();
        }
    }
}
</script>
<style lang="scss" scoped>
    .wp-dates{
        & .v-card__actions {
            justify-content: flex-end;
        }
    }
</style>
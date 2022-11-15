<template>
    <v-list class="wp-divisions"
            dense>
        <v-list-item-group v-if="has('divisions')" 
                           v-model="selected"
                           color="primary"
                           multiple>
            <v-list-item v-for="dvs in divisions"
                        :key="'dvs-' + dvs.ID"
                        :value="dvs.ID">
                <v-list-item-icon>
                    {{dvs.UF_CODE}}
                </v-list-item-icon>
                <v-list-item-content class="flex-column align-start">
                    {{dvs.UF_NAME}}
                </v-list-item-content>
                <v-list-item-icon>
                    <v-icon v-if="has('checked', dvs)" small>mdi-checkbox-outline</v-icon>
                </v-list-item-icon>
            </v-list-item>
        </v-list-item-group>
        <v-list-item v-else>
            <v-skeleton-loader type="list-item-avatar@3"></v-skeleton-loader>
        </v-list-item>
    </v-list>
</template>
<script>
import { mxForm } from '~/utils/mxForm.js';
import { empty } from '~/utils/';

export default {
    name: 'WpSelDvss',
    mixins: [ mxForm ],
    inject: ['needs'],
    data(){
        return {
            selected: [],
            search: null
        };
    },
    created(){
        this.$store.dispatch("data/list", "divisions");
        this.needs('title', 'Подразделения');
        this.needs('searchable', true);
    },
    computed: {
        divisions(){
            const dvss = this.$store.getters["data/divisions"];
            if (empty(this.search)){
                return dvss;
            } else {
                const re = new RegExp('(' + this.search + ')+', 'gi');
                return dvss?.filter( e => re.test(e.UF_NAME) || re.test(e.UF_CODE) );
            }
        }
    },
    methods: {
        has(q, v){
            switch(q){
                case "checked":
                    return this.selected.findIndex( s => (s == v.ID) ) > -1;
                case "divisions":
                    return Array.isArray(this.divisions);
            }
            return false;
        },
        use(items){
            console.log('using', items);
            this.selected = Array.isArray(items) ? items : [];
        },
        validate(){
            return true;
        },
        save(){
            this.$emit("success", this.selected);
        }
    }
}    
</script>
<style lang="scss">
.wp-divisions{
    & .v-list {
        &-item{
            &__icon{
                align-self: center;
            }
        }
    }
}
</style>    

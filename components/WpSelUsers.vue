<template>
    <v-list class="act-employees"
            dense>
        <v-list-item-group v-if="has('employees')" 
                           v-model="selected"
                           color="primary"
                           multiple>
            <v-list-item v-for="emp in employees"
                        :key="'emp-' + emp.ID"
                        :value="emp.ID">
                <v-list-item-icon>
                    <v-icon v-if="has('checked', emp)" small>mdi-checkbox-outline</v-icon>
                </v-list-item-icon>
                <v-list-item-content class="flex-column align-start">
                    {{emp.UF_EMPNAME}}
                    <v-row class="emp-meta">
                        <v-col cols="auto">{{emp.DVS_NAME}}</v-col>
                        <v-col>{{emp.STAFF_NAME}}</v-col>
                    </v-row>
                </v-list-item-content>
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
    name: 'WpSelUsers',
    mixins: [ mxForm ],
    inject: ['needs'],
    data(){
        return {
            selected: [],
            search: null
        };
    },
    created(){
        this.$store.dispatch("data/list", "employees");
        this.needs('title', 'Сотрудники');
        this.needs('searchable', true);
    },
    computed: {
        employees(){
            if (empty(this.search)){
                return this.$store.state.data.employees;
            } else {
                const re = new RegExp('(' + this.search + ')+', 'gi');
                return this.$store.state.data.employees?.filter( e => re.test(e.UF_EMPNAME));
            }
        }
    },
    methods: {
        has(q, v){
            switch(q){
                case "checked":
                    return this.selected.findIndex( s => s===v.ID) > -1;
                case "employees":
                    return Array.isArray(this.employees);
            }
            return false;
        },
        use(items){
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
.act-employees{
    & .v-list {
        &-item{
            &__icon{
                align-self: center;
            }
            & .emp-meta{
                font-size: 0.75rem;
                color: var(--v-secondary-base);
            }
        }
    }
}
</style>    
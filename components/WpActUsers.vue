<template>
    <v-list class="act-employees"
            dense>
        <v-list-item-group v-if="has('employees')" 
                           v-model="selected"
                           color="primary"
                           multiple
                           v-on:change="sel">
            <v-list-item v-for="emp in employees"
                        :key="'emp-' + emp.ID"
                        :value="emp.ID">
                <v-list-item-icon>
                    <v-icon></v-icon>
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

export default {
    name: 'WpActUsers',
    mixins: [ mxForm ],
    data(){
        return {
            selected: []
        };
    },
    created(){
        this.$store.dispatch("data/list", "employees");
    },
    computed: {
        employees(){
            return this.$store.state.data.employees;
        }
    },
    methods: {
        has(q){
            switch(q){
                case "employees":
                    return this.employees?.length > 0;
            }
        },
        sel(a){
            console.log('sel', a);
        }
    }
}    
</script>
<style lang="scss">
    .act-employees{
        & .emp-meta{
            font-size: 0.75rem;
            color: var(--v-secondary-base);
        }
    }
</style>    
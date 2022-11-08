<template>
    <v-list class="act-employees"
            ref="list"
            dense>
        <v-list-item-group v-if="has('employees')" 
                           v-model="selected"
                           color="primary"
                           multiple
                           v-on:change="change">
            <v-list-item v-for="emp in employees"
                        :key="'emp-' + emp.ID"
                        :value="emp.ID"
                        v-bind:class="{seached: emp.seached}"
                        :data-item-id="emp.ID">
                <v-list-item-icon>
                    <v-icon v-if="emp.checked" small>mdi-checkbox-outline</v-icon>
                    <v-icon v-else small>mdi-checkbox-blank-outline</v-icon>
                </v-list-item-icon>
                <v-list-item-content class="flex-column align-start">
                    {{emp.UF_EMPNAME}}
                    <v-row class="emp-meta">
                        <v-col cols="8">{{emp.DVS_NAME}}</v-col>
                        <v-col cols="4" class="text-truncate">{{emp.STAFF_NAME}}</v-col>
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
            selected: [],  /* selected ID`s */
            search: null
        };
    },
    async fetch(){
        return this.$store.dispatch("data/list", "employees");
    },
    mounted(){
        this.needs('title', 'Сотрудники');
        this.needs('searchable', true);
    },
    computed: {
        employees(){
            const re = empty(this.search) ? null : new RegExp(`(${ this.search })+`, 'gi');
            
            return [...this.$store.state.data.employees || []].map(e =>{
                e.seached = (re) ? re.test(e.UF_EMPNAME) : false;
                e.checked = this.selected.findIndex( s => s===e.ID) > -1;
                return e;
            }).sort( (e1, e2) => {
                if (e1.checked){
                    return (e2.checked) ? e1.UF_EMPNAME.localeCompare(e2.UF_EMPNAME) : -1;
                } else if (e2.checked){
                    return (e1.checked) ? e1.UF_EMPNAME.localeCompare(e2.UF_EMPNAME) : 1;
                }
                return e1.UF_EMPNAME.localeCompare(e2.UF_EMPNAME);
            });
        },
        names(){
            const sels = [],
                  emps = this.$store.state.data.employees || [];
            this.selected.forEach( s => {
                const n = emps.findIndex( e => e.ID == s);
                if ( n > -1){
                    sels.push(emps[n].UF_EMPNAME);
                }
            });
            return sels.length > 0 ? sels.join(", ") : '';
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
            this.search = null;
            this.selected = Array.isArray(items) ? items : [];
            this.needs('info', this.names);
        },
        validate(){
            return true;
        },
        change(){
            this.$nextTick(()=>{
                this.needs('info', this.names);
                $(".v-dialog.v-dialog--active .v-card .v-card__text").animate({ scrollTop: 0 });
            });
        },
        save(){
            this.$emit("success", this.selected);
        }
    },
    watch: {
        search(val){
            this.$nextTick(()=>{
                if ( empty(val) ){
                    $(".v-dialog.v-dialog--active .v-card .v-card__text").animate({ scrollTop: 0 });
                } else {
                    const el = $(this.$refs["list"].$el).find(".seached");
                    if (el.length > 0){
                        el.get(0).scrollIntoView();
                    } else {
                        $nuxt.msg({text: `"${val}" - ничего не найдено`, timeout: 3000});
                    }
                }
            });
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
            &.seached{
                font-weight: 500;
            }
            & .emp-meta{
                flex: 1 1 100%;
                width: 100%;
                font-size: 0.75rem;
                color: var(--v-secondary-base);
                font-weight: 400 !important;
            }
        }
    }
}
</style>
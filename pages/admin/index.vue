<template>
    <v-container>
        <v-row class="wp-auth fill-height" justify="center" align="center">
            <v-col cols="11" md="6">
                <v-form v-on:submit.stop.prevent="onauth" action="#" v-model="valid">
                    <v-card class="elevation-3">
                        <v-card-title>
                            <div class="form-icon">
                                <v-icon :color="has('user') ? 'primary': 'default'">
                                    {{has('user')?'mdi-account':'mdi-account-lock'}}
                                </v-icon>
                            </div>
                            <div class="form-title" v-html="title"></div>
                        </v-card-title>
                        <v-card-text>
                            <v-text-field
                                label="Логин"
                                name="login"
                                v-model="user.u"
                                autofocus
                                :rules="[rules.empty]"
                                required>
                                <v-icon slot="prepend" small>mdi-account</v-icon>
                            </v-text-field>
                            <v-text-field
                                label="Пароль"
                                name="p"
                                type="password"
                                :rules="[rules.empty]"
                                v-model="user.p">
                                <v-icon slot="prepend" small>mdi-asterisk</v-icon>
                            </v-text-field>
                            <v-alert color="warning" 
                                     dark 
                                     class="my-5" 
                                     icon="mdi-alert"
                                     v-if="has('error')">
                                {{ error }}
                            </v-alert>
                        </v-card-text>
                        <v-card-actions>
                            <v-btn type="submit" 
                                   tile
                                   :loading="pending"
                                   dark 
                                   :color="has('user') ? 'primary' : 'red darken-4'">
                                <template v-if="has('user')">
                                    <v-icon>mdi-check-circle-outline</v-icon>&nbsp;ok
                                </template>
                                <template v-else>Войти</template>
                            </v-btn>
                        </v-card-actions>
                        <v-footer>
                            <v-spacer />
                            <span>Законодательное Собрание Краснодарского края</span>
                        </v-footer>
                    </v-card>
                </v-form>
            </v-col>
        </v-row>
    </v-container>    
</template>
<script>
import { empty } from "~/utils";

const USER_DEFS = {
    id: null,
    title: '',  //user title
    u: '',
    p: ''
};

export default {
  name: 'SignInPage',
  data() {
    return {
            valid: false,
            pending: false,
            user: {id: null, u: null, p: null},
            error: '',
            rules: {
                empty: val => !empty(val) || "Необходимо заполнить"
            }
        };
    },
    head(){
        return {
            title: 'Авторизация'
        };
    },
    computed: {
        title(){
            return 'План мероприятий | Авторизация';
        }
    },
    methods: {
        has(q) {
            switch(q){
                case 'user':
                    return !empty(this.user.id); 
                case 'error':
                    return !empty(this.error); 
            }
            return false;
        },
        async onauth() {
            const {u, p} = this.user;
            if ( empty(u) || empty(p) ) {
                this.valid = false;
                this.error = 'Для входа необходимо ввести Ваши e-mail и пароль';
                $('input[name="u"]').trigger('focus');
                return false;
            }
            this.error = '';
            this.pending = true;
            try {
                const user = await $nuxt.api("auth", this.user);
                this.$store.commit("data/set", { user });
                if (user.id < 1){
                    throw {message: 'Not Authorized'};
                }
                this.user.id = user.id;
                
                setTimeout( () => {
                    this.$router.replace({name: 'index'});
                }, 1000);
            } catch(e) {
                console.log('ERR (login)', e);
                this.error = 'Логин или пароль неверный';
            } finally {
                this.pending = false;
            }
            return false;
        }     //onauth
    }   //methods
};
</script>

<style lang="scss" scoped>
    .wp-auth{
        min-height: calc(100vh - 56px);
        align-content: center;
        align-items: center;
        justify-content: center;
        & .v-card {
            &__title{
                text-transform: uppercase;
                font-weight: 300;
                font-size: 1rem;
                word-break: break-word;
                flex-wrap: nowrap;
                line-height: 1.25;
                align-content: center;
                justify-content: center;
                    
                & .v-icon{
                    line-height: 1 !important;
                    margin-right: 1rem;
                    border-radius: 500px;
                    padding: 0.25rem;
                    border: 1px solid #ccc;
                    width: 3rem;
                    text-align: center;
                    height: 3rem;
                }
            }
            &__actions{
                text-align: center;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                & .v-btn{
                    width:14rem;
                    margin-bottom: 1rem;
                }
            }
        }
        & .v-footer{
            font-size: 0.75rem;
        }
    }
</style>

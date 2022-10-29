<template>
    <v-btn text 
           :to="{name:'auth'}" 
           :dark="dark"
           color="primary" 
           v-show="has('show')"
           tile>
        <v-icon small>mdi-login</v-icon>
    </v-btn>
</template>
<script>
export default {
    name: 'WpAuthBtn',
    props: {
        dark: {
            type: Boolean,
            required: false,
            default: false
        }
    },
    methods: {
        has(q){
            switch(q){
                case 'show':
                    return (
                                ('/'===this.$route.path) 
                             || (this.$store.getters["user"]?.id > 0)
                    );
            }
        }
    }
}
</script>
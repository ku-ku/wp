<template>
<v-sheet>
    <h3>Структура</h3>
    <v-data-table :headers="headers"
                  :items="all"
                  :items-per-page="30"></v-data-table>
</v-sheet>
</template>
<script>
export default {
    name: 'WpDivisions',
    async asyncData({store}){
        return {
            all: await store.dispatch("data/list", "divisions")
        }
    },
    data(){
        return {
            headers: [
                { text: 'Код', value: 'UF_CODE' },
                { text: 'Наименование', value: 'UF_NAME' },
                { text: 'Активно', value: 'UF_ACTIVE' },
                { text: 'Порядок', value: 'UF_SORT' }
            ]
        };
    }
}
</script>
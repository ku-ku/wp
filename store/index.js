
export const getters = {
    user: async state => {
        return await $nuxt.$store.dispatch("data/user");
    }
}
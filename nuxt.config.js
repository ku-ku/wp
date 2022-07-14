const isDev = (process.env.NODE_ENV === 'development');

export default {
  ssr: false,

  target: 'static',

  head: {
    titleTemplate: '%s - Законодательное Собрание Краснодарского края',
    title: 'ПЛАН МЕРОПРИЯТИЙ',
    htmlAttrs: {
      lang: 'ru'
    },
    meta: [
      { charset: 'utf-8' },
      { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      { hid: 'description', name: 'description', content: '' },
      { name: 'format-detection', content: 'telephone=no' }
    ],
    link: [
      { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }
    ]
  },

  css: [
    '~/assets/index.scss'
  ],

  plugins: [
    '~/plugins/extend-app.js'
  ],

  components: true,

  buildModules: [
    '@nuxtjs/vuetify'
  ],

  modules: [
    '@nuxtjs/proxy'
  ],


  env: {
    YAM_ID: '89388304',
    apiUrl: isDev ? '/api/' : '/wp/api'
  },

  router: {
      mode: "hash"
  },

  proxy: {
    "/api/": {
        target: "https://kubzsk.ru/wp/api/",
        pathRewrite: {"^/api/": ""}
    }
  },
  vuetify: {
    customVariables: ['~/assets/variables.scss'],
    theme: {
      options: { customProperties: true },
      dark: false,
      light: true,
      themes: {
        light: {
          primary: '#3b4256',
/*          
          accent: colors.grey.darken3,
          secondary: colors.amber.darken3,
          info: colors.teal.lighten1,
          warning: colors.amber.base,
          error: colors.deepOrange.accent4,
          success: colors.green.accent3
*/          
        }
      }
    }
  },

  build: {
    publicPath: isDev ? undefined : '//kubzsk.ru/wp/app',
  }
}

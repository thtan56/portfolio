module.exports = {
  "transpileDependencies": [
    "vuetify"
  ],
  devServer: {
    proxy: {
      '^/php': {
        target: 'http://portfolio',
        ws: true,
        changeOrigin: true
      },
      '^/api': {
        target: 'http://portfolio',
        ws: true,
        changeOrigin: true
      },
      '^/static': {
        target: 'http://portfolio',
        ws: true,
        changeOrigin: true
      },      
    }
  }
}

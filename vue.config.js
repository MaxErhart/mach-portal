module.exports = {
  publicPath: '/dist/',
  runtimeCompiler: true,
  chainWebpack: config => {
    config.module
      // .rule('html')
      .rule('vue')
      // .use('html-loader').loader('html-loader')
      .use('vue-loader')
      .tap(options => {
        options['compilerOptions'] = {
          ...options.compilerOptions || {},
          isCustomElement: tag => tag === 'ion-icon'
        };
        return options;
      })
  }
}

/* eslint-disable global-require, import/no-extraneous-dependencies */
const postcssConfig = {
	plugins: [
		require('autoprefixer'),
		require('cssnano')({
			preset: 'default',
		}),
	],
};

module.exports = postcssConfig;

const path = require('path');
const autoprefixer = require('autoprefixer');
const overrideBrowserslist = require('@wordpress/browserslist-config');
const globImporter = require('node-sass-glob-importer');
const webpack = require('webpack');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const LiveReloadPlugin = require('webpack-livereload-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const postcssPlugins = require('@wordpress/postcss-plugins-preset');

const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');
const { resolve } = require('@wordpress/scripts/config/webpack.config');

// const isProduction = process.env.NODE_ENV === 'production';
const isProduction = process.env.NODE_ENV = true;
const mode = isProduction ? 'production' : 'development';
const extensionPrefix = isProduction ? '.min' : '';
console.log("prod", isProduction, mode);

const paths = {
    css: 'build/css/',
    img: 'build/img/',
    font: 'build/font/',
    js: 'build/js/',
    lang: 'languages/',
};

const loaders = {
    css: {
        loader: 'css-loader',
        options: {
            sourceMap: !isProduction,
        },
    },
    postCss: {
        loader: 'postcss-loader',
        options: {
            postcssOptions: {
                plugins: postcssPlugins,
                config: {
                    ident: 'postcss',
                    plugins: postcssPlugins,
                },
            },
            sourceMap: !isProduction,
        },
    },
    sass: {
        loader: 'sass-loader',
        options: {
            sourceMap: !isProduction,
        },
    },
};


const getLiveReloadPort = (inputPort) => {
    const parsedPort = parseInt(inputPort, 10);

    return Number.isInteger(parsedPort) ? parsedPort : 35729;
};

const newConfig = {
    ...defaultConfig,
    entry: {
        // index: path.resolve(process.cwd(), 'src', 'index.js'),
        'order-status-admin': path.resolve(process.cwd(), 'src/order-status-admin', 'index.js'),
        'order-admin': path.resolve(process.cwd(), 'src/order-admin', 'index.js'),
    },
    module: {
        rules: [
            {
                test: /\.(ts|js)x?$/,
                exclude: /node_modules/,
                use: {
                    loader: "babel-loader",
                    options: {
                        presets: [
                            "@babel/preset-env",
                            "@babel/preset-react",
                            "@babel/preset-typescript",
                        ],
                    },
                },
            },
            {
                enforce: 'pre',
                test: /\.js|.jsx/,
                loader: 'import-glob',
                exclude: /(node_modules)/,
            },
            {
                test: /\.js|.jsx/,
                loader: 'babel-loader',
                query: {
                    presets: [
                        '@wordpress/default',
                    ],
                    plugins: [
                        [
                            '@wordpress/babel-plugin-makepot',
                            {
                                'output': `${paths.lang}wppdev-woocommerce-order-status-js.pot`,
                            }
                        ],
                        'transform-class-properties',
                    ],
                },
                exclude: /(node_modules|bower_components)/,
            },
            {
                test: /\.html$/,
                loader: 'raw-loader',
                exclude: /node_modules/,
            },
            {
                test: /\.css$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    loaders.css,
                    // loaders.postCss,
                ],
                exclude: /node_modules/,
            },
            {
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    loaders.css,
                    // loaders.postCss,
                    loaders.sass,
                ],
                exclude: /node_modules/,
            },
            {
                test: /\.(ttf|eot|svg|woff2?)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
                use: [
                    {
                        loader: 'file-loader',
                        options: {
                            name: '[name].[ext]',
                            outputPath: paths.font,
                        },
                    },
                ],
                exclude: /(assets)/,
            },
        ],
    },
};
console.log(newConfig);
console.log(newConfig.module.rules.map(rule => rule && console.log(rule)));
module.exports = newConfig;

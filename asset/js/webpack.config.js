const path = require('path');
const MergeIntoSingleFilePlugin = require('webpack-merge-and-include-globally');

module.exports = {
    entry: './src/index.js',
    output: {
        filename: '[name]',
        path: path.resolve(__dirname, 'dist'),
    },
    plugins: [
        new MergeIntoSingleFilePlugin({
            "bundle.js": [
                path.resolve(__dirname, 'src/util.js'),
                path.resolve(__dirname, 'src/index.js')
            ],
            "bundle.css": [
                path.resolve(__dirname, 'src/css/main.css'),
                path.resolve(__dirname, 'src/css/local.css')
            ]
        })
    ]
};

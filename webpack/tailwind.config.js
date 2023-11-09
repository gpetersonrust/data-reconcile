const { file_path } = require("./library/constants/global");
const fg = require('fast-glob');

const phpFilesPattern = "**/*.php"; // The glob pattern to find PHP files recursively

// Use fast-glob to find PHP files
const phpFiles = fg.sync(phpFilesPattern, {
  ignore: ["node_modules", "dist", ".git"],
  cwd: file_path, // The base directory where the search starts
});

module.exports = {
  mode: 'jit',
  content: phpFiles, // Set the content property to include the found PHP files

  theme: {
    extend: {
      colors: {
        app: {
          primary: '#f91942',
          ['primary-light']: '#ff395d',
          light: '#5c5c5c',
          secondary: '#f919420f',
          tertiary: '#FCB316',
          confirm: '#54ba1d',
          raven: '#333333cc;',
          dark: '#1b1b1b',
          ['light-grey']: '#fafafa',
        },
      },
    },
  },
};

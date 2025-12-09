const { postArchive } = require('./build-utils');
const pkg = require('../package.json');

postArchive(pkg.name);

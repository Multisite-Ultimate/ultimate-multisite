const { execSync } = require('child_process');
const pkg = require('../package.json');

try {
  // Uses composer-archive-project plugin which creates archive with root folder
  execSync(`composer archive-project --format=zip --file=${pkg.name}`, {
    stdio: 'inherit',
  });
  console.log(`✅ Created archive: ${pkg.name}`);
} catch (error) {
  console.error('❌ Failed to create archive:', error.message);
  process.exit(1);
}

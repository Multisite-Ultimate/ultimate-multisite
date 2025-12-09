const { execSync } = require('child_process');
const pkg = require('../package.json');

try {
  execSync(`composer archive --format=zip --file=${pkg.name}`, {
    stdio: 'inherit',
  });
  console.log(`✅ Created archive: ${pkg.name}`);
} catch (error) {
  console.error('❌ Failed to create archive:', error.message);
  process.exit(1);
}

const fs = require("fs");
const path = require("path");
const { execSync } = require("child_process");

// Utility: Copy files
function copyFile(src, dest) {
  console.log(`📁 Copying from "${src}" to "${dest}"...`);
  fs.mkdirSync(path.dirname(dest), { recursive: true });
  fs.copyFileSync(src, dest);
  console.log(`✅ Copied file.`);
}

// Utility: Delete folder
function deleteFolder(targetPath) {
  console.log(`🗑️ Deleting folder: "${targetPath}"...`);
  if (fs.existsSync(targetPath)) {
    console.log(`✅ Folder deleted.`);
    fs.rmSync(targetPath, { recursive: true, force: true });
  } else {
    console.log(`ℹ️ Folder not found, skipping delete: "${targetPath}"`);
  }
}

// Utility: Delete all *.min.js or *.min.css
function cleanMinified(dir, ext) {
  console.log(`🧹 Cleaning *.min.${ext} files in "${dir}"...`);
  const walk = (dirPath) => {
    fs.readdirSync(dirPath).forEach((file) => {
      const fullPath = path.join(dirPath, file);
      if (fs.statSync(fullPath).isDirectory()) {
        walk(fullPath);
      } else if (file.endsWith(`.min.${ext}`)) {
        console.log(`🗑️ Deleting file: ${fullPath}`);
        fs.unlinkSync(fullPath);
      }
    });
  };
  walk(dir);
  console.log(`✅ Minified *.${ext} cleanup complete.`);
}

// Utility: Post archive process - no longer needed with composer-archive-project plugin
function postArchive(packageName) {
  console.log(`✅ Archive ready: ${packageName}.zip\n`);
}

console.log(`🏁 Build process finished`);

// Exports
module.exports = {
  copyFile,
  deleteFolder,
  cleanMinified,
  postArchive,
};

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

// Utility: Post archive process
function postArchive(packageName) {
  const zipName = `${packageName}.zip`;
  const extractDir = packageName;

  console.log(`🔧 Starting post-archive process for: ${zipName}`);

  deleteFolder(extractDir);

  console.log(`📦 Extracting ${zipName} to ${extractDir}...`);
  try {
    if (process.platform === "win32") {
      execSync(
        `powershell -Command "Expand-Archive -Path '${zipName}' -DestinationPath '${extractDir}' -Force"`,
        { stdio: "inherit" }
      );
    } else {
      execSync(`unzip ${zipName} -d ${extractDir}`, { stdio: "inherit" });
    }
    console.log(`✅ Extraction complete.`);
  } catch (err) {
    console.error(`❌ Failed to extract archive:`, err.message);
    process.exit(1);
  }

  // 3. Delete the original zip
  console.log(`🗑️ Deleting original zip file: ${zipName}`);
  fs.unlinkSync(zipName);

  // 4. Re-create ZIP
  console.log(`📦 Re-zipping ${extractDir} into ${zipName}...`);
  try {
    if (process.platform === "win32") {
      execSync(
        `powershell -Command "Compress-Archive -Path '${extractDir}\\*' -DestinationPath '${zipName}' -Force"`,
        { stdio: "inherit" }
      );
    } else {
      execSync(`zip -r -9 ${zipName} ${extractDir}`, { stdio: "inherit" });
    }
    console.log(`✅ Zip created: ${zipName}`);
  } catch (err) {
    console.error(`❌ Failed to create zip archive:`, err.message);
    process.exit(1);
  }

  // 5. Cleanup extracted folder
  console.log(`🧹 Cleaning up folder: ${extractDir}`);
  fs.rmSync(extractDir, { recursive: true, force: true });
  console.log(`✅ Done. Archive is ready.\n`);
}

console.log(`🏁 Build process finished`);

// Exports
module.exports = {
  copyFile,
  deleteFolder,
  cleanMinified,
  postArchive,
};

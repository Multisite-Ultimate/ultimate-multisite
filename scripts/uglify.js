const glob = require("glob");
const { execSync } = require("child_process");

const files = glob.sync("assets/js/**/*.js").filter(f => !f.endsWith(".min.js"));

console.log(`🔧 Starting minifying process for .js files`);

files.forEach((file) => {
  const outFile = file.replace(/\.js$/, ".min.js");
  console.log(`Uglifying: ${file} → ${outFile}`);
  execSync(`npx uglifyjs "${file}" -c -m -o "${outFile}"`);
});

console.log(`✅ DONE creating minified .js files`);

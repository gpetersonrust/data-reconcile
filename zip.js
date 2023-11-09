const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

class DirectoryCloner {
  /**
   * Clones the current directory to the desktop.
   * This method copies the current directory to the user's desktop and then zips it.
   */
  static cloneToDesktop() {
    // Step 1: Get the desktop path and the current directory's name
    const desktopPath = path.join(require('os').homedir(), 'Desktop');
    const currentDirName = path.basename(__dirname);

    // Step 2: Create the path for the desktop directory
    const desktopDir = path.join(desktopPath, currentDirName);

    // Step 3: Clone the current directory to the desktop

    this.copyDirectorySync(__dirname, desktopDir);

    // Step 4: After cloning, zip the directory on the desktop
    this.zipDirectory(desktopDir);
  }

  /**
   * Copies a file from source to destination (Synchronous version).
   * @param {string} src - The source file path.
   * @param {string} dest - The destination file path.
   */
  static copyFileSync(src, dest) {
    fs.copyFileSync(src, dest);

  }

  /**
   * Copies a directory and its contents recursively from source to destination (Synchronous version).
   * @param {string} src - The source directory path.
   * @param {string} dest - The destination directory path.
   */
  static copyDirectorySync(src, dest) {
    // Check if the source directory is 'node_modules'; if yes, skip it
    if (path.basename(src) === 'node_modules') {
      return;
    }

    // Create the destination directory
    fs.mkdirSync(dest, { recursive: true });

    // Read the contents of the source directory
    const files = fs.readdirSync(src, { withFileTypes: true });

    files.forEach((file) => {
      const srcPath = path.join(src, file.name);
      const destPath = path.join(dest, file.name);

      if (file.isDirectory()) {
        // If it's a directory, recursively copy the subdirectory
        this.copyDirectorySync(srcPath, destPath);
      } else {
        // If it's a file, copy it to the destination
        this.copyFileSync(srcPath, destPath);
      }
    });
  }

  /**
   * Zips the directory on the desktop using the 'zip' command.
   * @param {string} directoryPath - The path of the directory to be zipped.
   */
  static zipDirectory(directoryPath) {
    const zipFilePath = `${directoryPath}.zip`;
    // console.log(`Zipping ${directoryPath} to ${zipFilePath}`);
       console.log(directoryPath);
     

    // Use the 'zip' terminal command to create a zip file of the directory
    const zipCommand = ` 
     cd "${directoryPath}" && zip -r "${zipFilePath}" "."`;

    try {
      execSync(zipCommand);
     } catch (error) {
      console.error(`Error zipping the directory: ${error.message}`);
    }
  }

  /**
   * Deletes the zip file and cloned directory on the desktop if they exist,
   * then re-clone and zip the current directory.
   */
  static cleanupDesktop() {
    const desktopPath = path.join(require('os').homedir(), 'Desktop');
    const currentDirName = path.basename(__dirname);
    const desktopDir = path.join(desktopPath, currentDirName);
    const zipFilePath = `${desktopDir}.zip`;

    // Use the 'rm' command to delete the zip file and cloned directory
    const rmCommand = `rm -rf "${zipFilePath}" "${desktopDir}"`;

    try {
      execSync(rmCommand);

    } catch (error) {
      console.error(`Error deleting files: ${error.message}`);
    }

    // After cleanup, clone the directory and zip it again
    this.cloneToDesktop();
  }
}

// Example usage: Cleanup the existing zip file and cloned directory on the desktop, then re-clone and zip
DirectoryCloner.cleanupDesktop();

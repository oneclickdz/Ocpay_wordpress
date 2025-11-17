# GitHub Actions Workflow Documentation

## Release Plugin Workflow

This workflow automates the version increment and release process for the OCPay WordPress plugin.

### Overview

The workflow performs the following steps:
1. Automatically increments the version number (patch, minor, or major)
2. Updates version in all relevant files (`ocpay-woocommerce.php` and `composer.json`)
3. Commits the version bump
4. Creates a plugin zip file (excluding development files)
5. Creates a git tag for the new version
6. Creates a GitHub release with the zip file attached

### How to Use

1. **Trigger the workflow manually:**
   - Go to the repository on GitHub
   - Click on "Actions" tab
   - Select "Release Plugin" workflow
   - Click "Run workflow"
   - Choose the version bump type:
     - **patch**: Increments the last digit (e.g., 1.0.1 → 1.0.2) - for bug fixes
     - **minor**: Increments the middle digit (e.g., 1.0.1 → 1.1.0) - for new features
     - **major**: Increments the first digit (e.g., 1.0.1 → 2.0.0) - for breaking changes
   - Click "Run workflow" to start

2. **What happens next:**
   - The workflow runs automatically
   - Version is bumped in all files
   - Changes are committed and pushed
   - A new tag is created (e.g., v1.0.2)
   - A GitHub release is created with the plugin zip file
   - Users can download the zip file from the Releases page

### Files Modified by Workflow

The workflow updates version numbers in:
- `ocpay-woocommerce.php` (Plugin header and constant)
- `composer.json` (Version field)

### Files Excluded from Release Zip

Development files are excluded from the release zip based on `.distignore`:
- `.git` directory
- `.github` directory
- Git configuration files (`.gitignore`, `.gitattributes`)
- Development dependencies (`node_modules`, `vendor`)
- IDE files (`.vscode`, `.idea`)
- Build and test files
- Log files

### Requirements

- Repository must have write permissions enabled for GitHub Actions
- Workflow is configured with `contents: write` permission to create releases and tags

### Troubleshooting

**Issue**: Workflow fails to push commits
- **Solution**: Ensure GitHub Actions has write permissions in repository settings

**Issue**: Workflow fails to create release
- **Solution**: Check that `GITHUB_TOKEN` has proper permissions

**Issue**: Version not updating correctly
- **Solution**: Verify the current version format in `ocpay-woocommerce.php` matches `X.Y.Z` pattern

# Testing Guide - Times News Block

Complete guide to running all tests in the Times News Block plugin.

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [Unit Tests (JavaScript with Jest)](#1-unit-tests-javascript-with-jest)
3. [Unit Tests (PHP with PHPUnit)](#2-unit-tests-php-with-phpunit)
4. [End-to-End Tests (E2E)](#3-end-to-end-tests-e2e)
5. [Manual Testing](#4-manual-testing)
6. [VIP-Specific Testing](#5-vip-specific-testing)
7. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Required Software

```bash
# Check if Node.js is installed
node --version  # Should be 14+ or higher

# Check if npm is installed
npm --version

# Check if Docker is installed (for wp-env)
docker --version

# Check if Docker Compose is installed
docker-compose --version
```

### Install Dependencies

```bash
cd times-news-block

# Install npm dependencies
npm install

# Build the plugin
npm run build
```

---

## 1. Unit Tests (JavaScript with Jest)

### What These Test
- React components (Edit component)
- Block registration
- JavaScript functionality

### Running JavaScript Unit Tests

```bash
cd times-news-block

# Run all JavaScript unit tests
npm run test:unit

# Run tests in watch mode (re-runs on file changes)
npm run test:unit -- --watch

# Run tests with coverage report
npm run test:unit -- --coverage
```

### Expected Output

```
PASS  src/edit.test.js
  ✓ Edit component renders correctly (45ms)
  ✓ Edit component handles attribute changes (23ms)

Test Suites: 1 passed, 1 total
Tests:       2 passed, 2 total
Snapshots:   0 total
Time:        2.456s
```

### What's Being Tested

**File**: [src/edit.test.js](src/edit.test.js)

Tests include:
- Edit component renders without crashing
- InspectorControls render properly
- Attribute updates work correctly
- API fetching functionality

### If Tests Fail

1. **Check dependencies**:
   ```bash
   npm install --save-dev @testing-library/react @testing-library/jest-dom
   ```

2. **Clear Jest cache**:
   ```bash
   npm run test:unit -- --clearCache
   ```

3. **Check for missing mocks**:
   - `@wordpress/api-fetch` needs to be mocked
   - `@wordpress/i18n` needs to be mocked

---

## 2. Unit Tests (PHP with PHPUnit)

### What These Test
- PHP backend functions
- RSS feed fetching
- News article processing
- WordPress integration

### Setup PHPUnit

First time setup:

```bash
cd times-news-block

# Start wp-env (WordPress test environment)
npm run env:start

# Wait for WordPress to fully start (30-60 seconds)
# Check if it's running: http://localhost:8888

# Install PHPUnit in the WordPress container
npm run env -- run tests-cli "which phpunit" || npm run env -- run tests-cli "wp package install wp-cli/php-compat-command"
```

### Running PHP Unit Tests

```bash
# Run all PHP tests
npm run env -- run tests-cli phpunit /var/www/html/wp-content/plugins/times-news-block/tests/

# Run specific test file
npm run env -- run tests-cli phpunit /var/www/html/wp-content/plugins/times-news-block/tests/test-news-fetcher.php

# Run tests with verbose output
npm run env -- run tests-cli phpunit --verbose /var/www/html/wp-content/plugins/times-news-block/tests/
```

### Alternative: Run PHPUnit Directly (if installed locally)

```bash
# If you have PHPUnit installed locally
./vendor/bin/phpunit tests/

# Or via Composer
composer test
```

### Expected Output

```
PHPUnit 9.5.x by Sebastian Bergmann

...                                                                 3 / 3 (100%)

Time: 00:01.234, Memory: 10.00 MB

OK (3 tests, 8 assertions)
```

### What's Being Tested

**File**: [tests/test-news-fetcher.php](tests/test-news-fetcher.php)

Tests include:
- `times_news_block_fetch_news()` returns articles
- RSS feed parsing works correctly
- Caching mechanism functions properly
- Demo data fallback works

### If Tests Fail

1. **WordPress not running**:
   ```bash
   npm run env:start
   # Wait 60 seconds for full startup
   ```

2. **Plugin not activated**:
   ```bash
   npm run env -- run cli wp plugin activate times-news-block
   ```

3. **PHPUnit not found**:
   ```bash
   # Install via Composer
   composer require --dev phpunit/phpunit ^9

   # Or install in WordPress
   npm run env -- run tests-cli "composer global require phpunit/phpunit"
   ```

---

## 3. End-to-End Tests (E2E)

### What These Test
- Full user workflows in browser
- Block insertion and editing
- Settings page interaction
- Real browser testing with Puppeteer

### Setup E2E Tests

```bash
cd times-news-block

# Make sure wp-env is running
npm run env:start

# Wait for WordPress to be ready
# Check: http://localhost:8888

# Install E2E test dependencies (if not already installed)
npm install --save-dev @wordpress/e2e-test-utils puppeteer
```

### Running E2E Tests

```bash
# Run all E2E tests
npm run test:e2e

# Run specific test file
npm run test:e2e tests/e2e/times-news-block.spec.js

# Run with visible browser (not headless)
npm run test:e2e -- --puppeteer-headless=false

# Run with slowMo (easier to debug)
npm run test:e2e -- --puppeteer-slowMo=50
```

### Expected Output

```
PASS tests/e2e/times-news-block.spec.js (15.234s)
  Times News Block
    ✓ Should register the block (1234ms)
    ✓ Should insert the block (2345ms)
    ✓ Should display news articles (3456ms)

Test Suites: 1 passed, 1 total
Tests:       3 passed, 3 total
Snapshots:   0 total
Time:        15.456s
```

### What's Being Tested

**File**: [tests/e2e/times-news-block.spec.js](tests/e2e/times-news-block.spec.js)

Tests include:
- Block appears in inserter
- Block can be inserted into post
- Block settings work
- News articles display correctly
- AI filtering interface works

### If Tests Fail

1. **WordPress not accessible**:
   ```bash
   curl http://localhost:8888
   # Should return HTML
   ```

2. **Port conflicts**:
   ```bash
   # Stop wp-env
   npm run env:stop

   # Change port in .wp-env.json
   {
     "port": 8889
   }

   # Restart
   npm run env:start
   ```

3. **Puppeteer issues**:
   ```bash
   # Reinstall Puppeteer with Chromium
   npm install --save-dev puppeteer
   ```

---

## 4. Manual Testing

### Setup Test Environment

```bash
cd times-news-block

# Start WordPress
npm run env:start

# Build the plugin
npm run build

# Access WordPress
# Frontend: http://localhost:8888
# Admin: http://localhost:8888/wp-admin
# Username: admin
# Password: password
```

### Manual Test Checklist

#### ✅ Block Installation
1. Go to Plugins → Installed Plugins
2. Verify "Times News Block" is active
3. Check for any PHP errors

#### ✅ Block Insertion
1. Create new post/page
2. Click "+" to add block
3. Search for "Times News Block"
4. Insert the block
5. Verify it appears without errors

#### ✅ Block Settings
1. With block selected, open Settings sidebar
2. Test each control:
   - **Layout Style**: Change between Grid, List, Cards, Compact, Featured
   - **Number of Articles**: Adjust slider (1-20)
   - **Category**: Select different feeds
   - **AI Filtering**: Toggle on/off
   - **Filtering Criteria**: Enter text when AI enabled

#### ✅ News Display
1. Verify news articles load
2. Check images display
3. Verify titles, descriptions, dates appear
4. Click article links (should open in new tab)
5. Test all 5 layout styles

#### ✅ Settings Page
1. Go to Settings → Times News Block
2. **API Configuration**:
   - Enter OpenAI API key
   - Save settings
   - Verify it saves correctly
3. **RSS Feeds**:
   - View default feeds
   - Add new feed
   - Edit existing feed
   - Remove feed
   - Disable/enable feeds
   - Save and verify

#### ✅ AI Filtering
1. Enable AI filtering in block
2. Enter criteria: "technology news"
3. Verify articles are filtered
4. Try different criteria
5. Check console for errors

#### ✅ Caching
1. Load block (first request - slow)
2. Reload page (second request - fast from cache)
3. Wait 15 minutes
4. Reload (cache expires, fetches again)

#### ✅ Responsive Design
1. Desktop view (1920px)
2. Tablet view (768px)
3. Mobile view (375px)
4. Test all layouts on each size

---

## 5. VIP-Specific Testing

### Test Object Cache

```bash
# SSH into WordPress container
npm run env -- run cli bash

# Install WP-CLI cache command
wp package install wp-cli/cache-command

# Test object cache
wp cache flush
wp cache get times_news_YOUR_KEY times_news_block
wp cache set times_news_test "test_value" times_news_block 900

# Exit container
exit
```

### Test Rate Limiting

**Method 1: Browser Console**
```javascript
// Open browser console on block editor
// Run this 101 times to trigger rate limit
for (let i = 0; i < 101; i++) {
  console.log(`Request ${i+1}`);
  // Enable AI and change criteria to force new API calls
}
```

**Method 2: Check Logs**
```bash
# View WordPress error logs
npm run env -- logs

# Filter for rate limit messages
npm run env -- logs | grep "rate limit"
```

### Test Performance Tracking

```bash
# View error logs for performance warnings
npm run env -- logs | grep "Slow"

# Should see messages like:
# "Slow RSS fetch detected (2.34s)"
# "Slow AI filtering detected (5.67s)"
```

### Test API Key Priority

**Test environment variable**:
```bash
# Add to wp-config.php via wp-env
npm run env -- run cli "wp config set TIMES_NEWS_OPENAI_KEY 'sk-test-key-from-env' --raw"

# Verify it's used (check logs)
npm run env -- logs | grep "OpenAI"
```

**Test with wp-config.php**:
```php
// Edit via wp-env
// Add this line to wp-config.php:
define( 'TIMES_NEWS_OPENAI_KEY', 'sk-your-test-key' );
```

### Test CSP Compliance

**Check for inline styles/scripts**:
```bash
# View Settings page source
curl http://localhost:8888/wp-admin/options-general.php?page=times-news-block | grep "<style>"
# Should return nothing (no inline styles)

curl http://localhost:8888/wp-admin/options-general.php?page=times-news-block | grep "<script>"
# Should only see external script tags with src attribute
```

**Verify external files load**:
```bash
# Check CSS file exists
curl http://localhost:8888/wp-content/plugins/times-news-block/admin/css/settings.css
# Should return CSS content

# Check JS file exists
curl http://localhost:8888/wp-content/plugins/times-news-block/admin/js/settings.js
# Should return JavaScript content
```

---

## 6. All Tests in One Go

Run complete test suite:

```bash
cd times-news-block

# 1. Start environment
npm run env:start

# 2. Build plugin
npm run build

# 3. Run JavaScript unit tests
npm run test:unit

# 4. Run PHP unit tests (if PHPUnit available)
npm run env -- run tests-cli phpunit /var/www/html/wp-content/plugins/times-news-block/tests/

# 5. Run E2E tests
npm run test:e2e

# 6. Manual verification
echo "✅ Automated tests complete! Now do manual testing at http://localhost:8888/wp-admin"
```

---

## 7. Continuous Integration (CI) Setup

### GitHub Actions Example

Create `.github/workflows/test.yml`:

```yaml
name: Test Times News Block

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v2

      - name: Setup Node.js
        uses: actions/setup-node@v2
        with:
          node-version: '16'

      - name: Install dependencies
        run: npm install

      - name: Build plugin
        run: npm run build

      - name: Run JavaScript tests
        run: npm run test:unit

      - name: Start wp-env
        run: npm run env:start

      - name: Run E2E tests
        run: npm run test:e2e
```

---

## Troubleshooting

### Issue: "npm run env:start" fails

**Solution**:
```bash
# Check Docker is running
docker ps

# If not running, start Docker Desktop

# Clean up old containers
npm run env:stop
docker system prune -a

# Try again
npm run env:start
```

---

### Issue: Port 8888 already in use

**Solution**:
```bash
# Find what's using port 8888
lsof -i :8888

# Kill the process
kill -9 <PID>

# Or change port in .wp-env.json
{
  "port": 8889
}
```

---

### Issue: Tests timeout

**Solution**:
```bash
# Increase timeout in package.json
{
  "scripts": {
    "test:e2e": "wp-scripts test-e2e --timeout=60000"
  }
}
```

---

### Issue: Block not appearing in editor

**Solution**:
```bash
# Rebuild plugin
npm run build

# Clear WordPress cache
npm run env -- run cli "wp cache flush"

# Restart wp-env
npm run env:stop
npm run env:start
```

---

### Issue: PHPUnit not found

**Solution**:
```bash
# Install PHPUnit via Composer
composer require --dev phpunit/phpunit ^9

# Or install globally in wp-env
npm run env -- run tests-cli "composer global require phpunit/phpunit"
```

---

## Quick Reference Commands

```bash
# Environment
npm run env:start          # Start WordPress
npm run env:stop           # Stop WordPress
npm run env -- logs        # View logs

# Building
npm run build              # Production build
npm run start              # Development build with watch

# Testing
npm run test:unit          # JavaScript unit tests
npm run test:e2e           # End-to-end tests

# WordPress CLI
npm run env -- run cli wp plugin list
npm run env -- run cli wp cache flush
npm run env -- run cli wp user list
```

---

## Expected Test Coverage

| Test Type | Files Covered | What's Tested |
|-----------|--------------|---------------|
| **JS Unit** | `src/edit.js`, `src/index.js` | React components, block registration |
| **PHP Unit** | `times-news-block.php` | RSS fetching, caching, AI filtering |
| **E2E** | Full plugin | User workflows, integration |
| **Manual** | Everything | UI/UX, visual testing, edge cases |

---

## Success Criteria

✅ **All tests passing**:
- JavaScript unit tests: 100% pass
- PHP unit tests: 100% pass
- E2E tests: 100% pass
- Manual testing: All features work

✅ **No errors in logs**:
```bash
npm run env -- logs | grep -i error
# Should be minimal/expected errors only
```

✅ **Build succeeds**:
```bash
npm run build
# Should complete without errors
```

✅ **Plugin activates**:
- No PHP errors
- Block appears in inserter
- Settings page accessible

---

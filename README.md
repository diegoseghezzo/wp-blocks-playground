# Times News Block

A WordPress Gutenberg block that displays news articles with AI-powered filtering capabilities. Built with modern WordPress development practices, comprehensive testing, and following the official `@wordpress/create-block` workflow.
At the moment implementing RSS feeds as data source, but it's intended to implement proper APIS for a more detailed implementation.

## Features

- **RSS Feed Integration**: Fetches live news from RSS feeds
- **Multiple Categories**: Support for All News, World, Business, Sport, and Culture
- **AI-Powered Filtering**: Use OpenAI to intelligently filter and rank news based on custom criteria
- **Responsive Design**: Beautiful grid layout that works on all devices
- **Caching System**: Built-in transient caching to optimize performance
- **Customizable**: Control number of articles, category selection, and filtering criteria
- **Comprehensive Testing**: PHPUnit, Jest, and E2E tests included
- **Accessible**: Follows WordPress accessibility standards

## Screenshots

The block displays news articles in a responsive grid with:
- Article title (linked to source)
- Featured image
- Description/excerpt
- Publication date
- Hover effects and smooth transitions

## Requirements

- WordPress 6.1 or higher
- PHP 7.4 or higher
- Node.js 18+ and npm
- Docker (for wp-env local development)

## Installation

### For Development

1. Clone or download this repository into your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone git@github.com:diegoseghezzo/wp-blocks-playground.git times-news-block
   cd times-news-block
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Build the block:
   ```bash
   npm run build
   ```

4. Activate the plugin in WordPress admin dashboard

### Using wp-env (Recommended for Development)

This project includes wp-env configuration for easy local development:

1. Install dependencies:
   ```bash
   npm install
   ```

2. Start the local WordPress environment:
   ```bash
   npm run env:start
   ```

3. Access your WordPress site:
   - Frontend: http://localhost:8888
   - Admin: http://localhost:8888/wp-admin
   - Username: `admin`
   - Password: `password`

4. Build and watch for changes:
   ```bash
   npm start
   ```

5. Stop the environment when done:
   ```bash
   npm run env:stop
   ```

## Configuration

### OpenAI API Key (Optional)

To enable AI-powered news filtering:

1. Go to **Settings > Times News Block** in WordPress admin
2. Enter your OpenAI API key
3. Save changes

Get your API key from: https://platform.openai.com/api-keys

## Usage

### Adding the Block

1. Create or edit a post/page
2. Click the "+" button to add a block
3. Search for "Times News Block"
4. Insert the block

### Block Settings

#### General Settings
- **Number of Articles**: Choose how many articles to display (1-20)
- **Category**: Select from All News, World, Business, Sport, or Culture

#### AI Filtering (Optional)
- **Enable AI-Powered Filtering**: Toggle to enable AI filtering
- **Filtering Criteria**: Describe what kind of news you want

Example criteria:
- "technology and innovation news"
- "environmental and climate stories"
- "economic analysis and market trends"
- "stories about artificial intelligence"

### Block Attributes

The block supports the following attributes:

```json
{
  "newsCount": 5,
  "category": "all",
  "useAI": false,
  "userCriteria": ""
}
```

## Development

### Project Structure

```
times-news-block/
├── src/
│   ├── edit.js              # Block editor component
│   ├── save.js              # Block save (returns null for dynamic block)
│   ├── index.js             # Block registration
│   ├── view.js              # Frontend interactivity
│   ├── editor.scss          # Editor styles
│   ├── style.scss           # Frontend and editor styles
│   └── edit.test.js         # Jest tests for React components
├── tests/
│   ├── bootstrap.php        # PHPUnit bootstrap
│   ├── test-news-fetcher.php  # PHPUnit tests
│   └── e2e/
│       └── times-news-block.spec.js  # E2E tests
├── times-news-block.php     # Main plugin file
├── block.json               # Block metadata
├── package.json             # Node dependencies and scripts
├── .wp-env.json            # wp-env configuration
├── phpunit.xml.dist        # PHPUnit configuration
└── README.md               # This file
```

### Available Scripts

```bash
# Development
npm start              # Start development build with watch mode
npm run build         # Production build
npm run format        # Format code
npm run lint:css      # Lint CSS/SCSS
npm run lint:js       # Lint JavaScript

# Testing
npm run test:unit     # Run Jest unit tests
npm run test:e2e      # Run E2E tests

# Environment
npm run env:start     # Start wp-env
npm run env:stop      # Stop wp-env
npm run env:clean     # Clean wp-env data

# Other
npm run plugin-zip    # Create plugin ZIP file
npm run packages-update  # Update WordPress packages
```

## Testing

This project includes comprehensive testing:

### 1. PHPUnit Tests (Backend)

Tests for PHP functionality including:
- News fetching and caching
- REST API endpoints
- Block rendering
- Settings and options

Run PHPUnit tests:
```bash
# Set up WordPress test environment first
bash bin/install-wp-tests.sh wordpress_test root '' localhost latest

# Run tests
vendor/bin/phpunit
```

### 2. Jest Tests (Frontend)

Tests for React components including:
- Component rendering
- User interactions
- API integration
- Error handling

Run Jest tests:
```bash
npm run test:unit
```

### 3. E2E Tests

End-to-end tests using @wordpress/e2e-test-utils:
- Block insertion
- Settings interaction
- Publishing workflow
- Multiple instances

Run E2E tests:
```bash
npm run env:start
npm run test:e2e
```

## Architecture

### Backend (PHP)

- **RSS Feed Integration**: Uses WordPress `fetch_feed()` for reliable RSS parsing
- **REST API**: Custom endpoint at `/wp-json/times-news-block/v1/news`
- **Caching**: Transient API with 15-minute cache
- **Dynamic Rendering**: Server-side block rendering for better performance
- **OpenAI Integration**: Optional AI filtering using GPT-3.5-turbo

### Frontend (React)

- **Modern React**: Uses hooks (useState, useEffect)
- **WordPress Components**: Leverages @wordpress/components for UI
- **API Fetch**: Uses @wordpress/api-fetch for REST communication
- **Inspector Controls**: Rich sidebar controls for block settings

### Styling

- **SCSS**: Uses Sass for maintainable styles
- **Responsive Grid**: CSS Grid with mobile-first approach
- **Dark Mode**: Supports prefers-color-scheme
- **Accessibility**: Focus states and ARIA labels

## API Reference

### REST Endpoint

```
GET /wp-json/times-news-block/v1/news
```

**Parameters:**
- `count` (int): Number of articles (default: 5)
- `category` (string): News category (default: 'all')
- `useAI` (boolean): Enable AI filtering (default: false)
- `criteria` (string): AI filtering criteria (default: '')

**Response:**
```json
[
  {
    "title": "Article Title",
    "link": "https://...",
    "description": "Article description...",
    "pubDate": "2024-01-01 12:00:00",
    "image": "https://..."
  }
]
```

### PHP Functions

#### `times_news_block_fetch_news( $count, $category, $use_ai, $user_criteria )`
Fetches news articles from The Times RSS feed.

#### `times_news_block_filter_with_ai( $articles, $user_criteria, $count )`
Filters and ranks articles using OpenAI.

#### `times_news_block_render_callback( $attributes, $content, $block )`
Renders the block on the frontend.

## Performance Considerations

- **Caching**: All RSS feeds are cached for 15 minutes
- **Lazy Loading**: Images use native lazy loading
- **Efficient Queries**: Minimal database queries
- **Conditional Loading**: AI only called when enabled
- **Transient Cleanup**: Automatic transient expiration

## Security

- **Input Sanitization**: All user inputs are sanitized
- **Output Escaping**: All outputs are properly escaped
- **Nonce Verification**: WordPress nonces for form submissions
- **Capability Checks**: Proper permission checks
- **Secure API Calls**: HTTPS for all external requests

## Troubleshooting

### News not loading
- Check if The Times RSS feeds are accessible
- Verify server can make external HTTP requests
- Check WordPress error logs

### AI filtering not working
- Verify OpenAI API key is correctly entered
- Check API key has sufficient credits
- Review error logs for API response errors

### Block not appearing
- Ensure plugin is activated
- Run `npm run build` to compile assets
- Clear browser cache
- Check browser console for JavaScript errors

## Future Enhancements

- [ ] Support for additional news sources (REST API, GraphQL API)
- [ ] Custom CSS editor in block settings
- [ ] Article bookmarking/favorites
- [ ] Email notifications for new articles
- [ ] Advanced filtering options (date range, keywords)
- [ ] Export articles to PDF/CSV
- [ ] Integration with other AI providers (Claude, Gemini)

## Author

Diego Seghezzo


# AI Interaction Guide - Times News Block

## Where & How AI Works in This Block

### 📍 **Where to Configure AI**

#### 1. **WordPress Admin Settings** (Required First!)
Before you can use AI features, you need to add your OpenAI API key:

1. Go to **WordPress Admin**: http://localhost:8888/wp-admin
2. Navigate to **Settings > Times News Block**
3. Enter your **OpenAI API Key**
4. Click **Save Changes**

**Get an API Key**: https://platform.openai.com/api-keys

---

#### 2. **Block Editor Settings** (Where You Use AI)
Once the API key is configured, you can use AI filtering in the block editor:

1. **Add the Times News Block** to your post/page
2. Select the block (click on it)
3. In the **right sidebar**, look for the **"AI Filtering" panel**
4. **Toggle on** "Enable AI-Powered Filtering"
5. Enter your **Filtering Criteria** in the text area
6. The news will automatically update based on your criteria!

---

## 🤖 **How AI Filtering Works**

### The Flow:

```
1. Block fetches 15 news articles from The Times RSS
   ↓
2. You enable AI and enter criteria (e.g., "technology news")
   ↓
3. Block sends article titles + descriptions to OpenAI
   ↓
4. OpenAI analyzes each article against your criteria
   ↓
5. OpenAI ranks articles by relevance (0-10 score)
   ↓
6. Block displays top-ranked articles
```

### What AI Does:

- **Analyzes** each article's title and description
- **Ranks** articles based on how well they match your criteria
- **Filters** to show only the most relevant articles
- **Sorts** by relevance score (highest first)

---

## 💡 **Example AI Criteria to Try**

### General Topics
- `"technology and innovation"`
- `"business and economics"`
- `"environmental news and climate change"`
- `"political news and policy"`
- `"sports and athletics"`

### Specific Interests
- `"artificial intelligence and machine learning"`
- `"renewable energy and sustainability"`
- `"stock market and investment news"`
- `"healthcare and medical breakthroughs"`
- `"space exploration and astronomy"`

### Advanced Criteria
- `"positive news about scientific discoveries"`
- `"articles about emerging markets in Asia"`
- `"news relevant to small business owners"`
- `"stories about social impact and community"`
- `"breaking news about international relations"`

---

## 🎨 **New Display Formats**

You now have **5 different layout options**! Change them in the block settings:

### 1. **Grid (3 columns)** - Default
- Best for: Displaying many articles
- Layout: 3 columns on desktop, 1 on mobile
- Style: Card-based with images on top

### 2. **List (full width)**
- Best for: Featured content, detailed view
- Layout: Full-width horizontal cards
- Style: Image on left, content on right

### 3. **Cards (2 columns)**
- Best for: Balanced layout with emphasis
- Layout: 2 columns with larger cards
- Style: Enhanced shadows, premium look

### 4. **Compact (minimal)**
- Best for: Sidebar widgets, quick scans
- Layout: Vertical list with small images
- Style: Minimal padding, efficient use of space

### 5. **Featured (magazine style)**
- Best for: Homepage hero sections
- Layout: First article spans full width, others in 2 columns
- Style: Large images, magazine-style typography

---

## 🔧 **Complete Setup Example**

### Step-by-Step: Setting Up AI-Filtered Tech News

1. **Configure API Key** (one-time setup)
   ```
   WordPress Admin → Settings → Times News Block
   Enter OpenAI API Key → Save
   ```

2. **Add Block to Page**
   ```
   Edit a post/page → Click + → Search "Times News Block"
   ```

3. **Configure Layout** (in sidebar)
   ```
   Layout Style: Featured (magazine style)
   ```

4. **Set Number of Articles**
   ```
   Number of Articles: 6
   ```

5. **Choose Category**
   ```
   Category: Business
   ```

6. **Enable AI Filtering**
   ```
   Toggle ON: Enable AI-Powered Filtering
   ```

7. **Enter AI Criteria**
   ```
   "Technology startups and innovation in the business sector"
   ```

8. **Preview** - Watch the magic happen!
   ```
   Articles automatically filter to show tech/innovation business news
   First article displays large (featured), others in grid below
   ```

---

## 🚀 **Advanced AI Use Cases**

### Use Case 1: **Tech Blog Sidebar**
```
Layout: Compact
Number: 5
Category: All
AI: ON
Criteria: "breaking technology news and product launches"
```

### Use Case 2: **Finance Homepage**
```
Layout: Featured
Number: 7
Category: Business
AI: ON
Criteria: "stock market analysis and economic indicators"
```

### Use Case 3: **Sports Section**
```
Layout: Cards
Number: 6
Category: Sport
AI: ON
Criteria: "major tournament results and championship news"
```

### Use Case 4: **Environment Page**
```
Layout: List
Number: 8
Category: World
AI: ON
Criteria: "climate change, renewable energy, and environmental policy"
```

---

## 💰 **AI Cost & Performance**

### OpenAI API Costs
- **Model Used**: GPT-3.5 Turbo
- **Cost Per Request**: ~$0.001 - $0.002 (less than a penny!)
- **Caching**: 15-minute cache reduces API calls
- **Estimated Monthly Cost**: $1-5 for typical usage

### Performance
- **Response Time**: 1-3 seconds (includes API call)
- **Caching**: Subsequent loads are instant (cached)
- **Fallback**: If AI fails, shows regular RSS feed

---

## 🎯 **Tips for Best Results**

### Writing Good AI Criteria

**✅ Good Criteria:**
- `"technology and innovation"` - Clear, specific topics
- `"economic analysis with data"` - Specific type of content
- `"positive environmental news"` - Sentiment + topic

**❌ Poor Criteria:**
- `"good stuff"` - Too vague
- `"everything about anything"` - Not specific enough
- Single words like `"news"` - Use phrases instead

### Optimization Tips

1. **Be Specific**: More specific criteria = better filtering
2. **Use 2-4 Keywords**: Sweet spot for accuracy
3. **Include Context**: "for small business owners" helps AI understand
4. **Test and Iterate**: Try different criteria to find what works best

---

## 🐛 **Troubleshooting AI Features**

### AI Not Working?

**Check 1: API Key Configured?**
```
Settings → Times News Block → Check if API key is entered
```

**Check 2: AI Toggle Enabled?**
```
Block sidebar → AI Filtering panel → Toggle should be ON
```

**Check 3: Criteria Entered?**
```
Filtering Criteria textarea should have text
```

**Check 4: Check Browser Console**
```
F12 → Console tab → Look for errors
```

### Common Issues

**Issue**: "No articles found"
- **Solution**: Try less restrictive criteria or check API key

**Issue**: Takes long to load
- **Solution**: Normal! AI analysis takes 1-3 seconds. Subsequent loads are cached.

**Issue**: Same results with/without AI
- **Solution**: Your criteria might match all articles. Try more specific criteria.

---

## 📊 **What Gets Sent to OpenAI?**

### Privacy & Data

**What is sent:**
- Article titles
- Article descriptions (excerpts)
- Your filtering criteria

**What is NOT sent:**
- User personal information
- WordPress admin data
- Full article content
- Site analytics

**Data Retention:**
- OpenAI may store for 30 days for abuse monitoring
- No data is shared with third parties by this plugin
- See OpenAI's privacy policy for their data handling

---

## 🎓 **Learning Resources**

### Understanding the Code

**AI Logic Location**: `times-news-block/times-news-block.php`
- Function: `times_news_block_filter_with_ai()` (lines 200-270)
- See how articles are ranked and filtered

**Frontend Integration**: `times-news-block/src/edit.js`
- Lines 86-111: AI toggle and criteria input
- Lines 34-39: API call with AI parameters

### Customization Ideas

- Change AI model (try GPT-4 for better accuracy)
- Adjust number of articles fetched for AI (currently 3x requested)
- Modify the AI prompt template
- Add sentiment analysis
- Implement article summarization

---

## 🎉 **Demo for Clients**

### Quick Demo Script

**Show without AI:**
1. "Here's the block showing latest news from The Times"
2. "Let's change it to show 10 articles"
3. "Switch to different category - Business"

**Enable AI:**
4. "Now watch this - enable AI filtering"
5. "Let's say we only want technology news"
6. "Type: 'artificial intelligence and tech innovation'"
7. "Notice how the articles instantly update to match!"

**Change Layouts:**
8. "And we have 5 different display formats"
9. "Watch it transform: Grid → List → Cards → Compact → Featured"
10. "Each optimized for different use cases"

**Wow Factor:**
- Real-time updates
- Intelligent filtering
- Beautiful layouts
- Professional design

---

## Summary

**AI Configuration**: Settings → Times News Block → Enter API Key

**AI Usage**: Block Sidebar → AI Filtering Panel → Toggle ON → Enter Criteria

**5 Layouts**: Grid, List, Cards, Compact, Featured

**Cost**: Pennies per month

**Performance**: 1-3 seconds first load, instant after caching

**Best Practice**: Specific, multi-word criteria for best results

---

**You now have a powerful, AI-enhanced news block that will impress any client!** 🚀

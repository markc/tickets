---
title: "FAQ System Guide"
description: "Self-service knowledge base usage and management"
order: 3
category: "user"
version: "1.0"
last_updated: "2025-06-10"
---

# FAQ System Guide

TIKM's FAQ system provides a powerful self-service knowledge base that helps customers find answers quickly and reduces ticket volume.

## For Customers

### Accessing the FAQ System

- **Direct Access**: Visit `/faq` from any page
- **Dashboard Link**: Click "Browse FAQ" from your dashboard
- **Ticket Creation**: FAQ suggestions appear while creating tickets
- **Search Integration**: Global search includes FAQ articles

### Browsing FAQs

#### By Categories

FAQs are organized by departments/offices:

- **General**: Common questions applicable to all users
- **Technical Support**: Technical issues and troubleshooting
- **Billing**: Account and payment-related questions
- **Sales**: Product information and purchasing

#### By Popularity

- **Most Viewed**: Articles accessed most frequently
- **Recently Updated**: Latest additions and modifications
- **Trending**: Currently popular articles

### Searching for Answers

#### Search Features

- **Smart Search**: TNTSearch integration for relevant results
- **Auto-Complete**: Suggestions as you type
- **Fuzzy Matching**: Finds results even with typos
- **Content Search**: Searches both titles and article content

#### Search Tips

1. **Use Keywords**: Include specific terms related to your issue
2. **Try Variations**: Use different words for the same concept
3. **Be Specific**: Add context like error messages or feature names
4. **Check Spelling**: Correct spelling improves results

### Using FAQ Articles

#### Article Structure

Each FAQ article contains:

- **Question Title**: Clear, searchable question
- **Detailed Answer**: Step-by-step solution or explanation
- **Related Articles**: Links to similar topics
- **Last Updated**: When the article was last modified
- **Helpful Rating**: Community feedback on usefulness

#### Interactive Elements

- **Rate Articles**: Mark as helpful or not helpful
- **Print Version**: Printer-friendly format
- **Share Link**: Direct link to specific article
- **Copy Solution**: Copy code snippets or procedures

### When FAQs Don't Help

If you can't find the answer you need:

1. **Try Different Search Terms**: Rephrase your query
2. **Check Related Articles**: Browse suggested related content
3. **Contact Support**: Create a ticket for personalized help
4. **Request New FAQ**: Suggest topics for new articles

## For Agents

### Managing FAQ Content

Agents can contribute to the knowledge base to help customers and reduce repetitive tickets.

#### Creating New FAQs

1. **Access Admin Panel**: Navigate to `/admin`
2. **FAQ Resource**: Go to FAQs section
3. **Create New**: Click "Create FAQ"
4. **Fill Details**:
   - **Question**: Clear, searchable question
   - **Answer**: Comprehensive answer with formatting
   - **Office**: Department this FAQ applies to
   - **Tags**: Keywords for better searchability
5. **Save**: Article becomes immediately available

#### Content Guidelines

##### Writing Effective Questions

- **Customer Language**: Use terms customers would use
- **Specific Problems**: Address specific issues, not generic topics
- **Search-Friendly**: Include keywords customers might search for
- **Clear Intent**: Make the question's purpose obvious

##### Writing Helpful Answers

- **Step-by-Step**: Break complex procedures into numbered steps
- **Screenshots**: Include images when helpful
- **Code Examples**: Format code blocks properly
- **Links**: Reference related articles or external resources
- **Context**: Explain why something works, not just how

#### Markdown Formatting

TIKM supports rich markdown formatting:

```markdown
# Main Heading
## Section Heading
### Subsection

**Bold text** for emphasis
*Italic text* for subtle emphasis

- Bullet points
- For lists
- Of items

1. Numbered steps
2. For procedures
3. In order

`Inline code` for commands
```

Code blocks for longer examples:
```
Complete code examples
With proper formatting
```

> Callout boxes for important notes

[Link text](URL) for external references
```

#### Content Organization

##### Office Assignment

- **General FAQs**: Available to all customers
- **Office-Specific**: Only visible for that department
- **Internal FAQs**: Agent-only knowledge base

##### Tagging Strategy

Use consistent tags for better organization:
- **Feature Tags**: login, tickets, attachments, email
- **Issue Tags**: error, troubleshooting, how-to
- **Platform Tags**: web, mobile, api
- **Urgency Tags**: critical, common, rare

### Using FAQs in Support

#### During Ticket Resolution

- **Link to FAQs**: Include relevant FAQ links in responses
- **Create from Tickets**: Turn common questions into FAQs
- **Update Existing**: Improve FAQs based on customer feedback
- **Track Usage**: Monitor which FAQs are most helpful

#### FAQ-First Approach

1. **Check FAQs**: Before responding, see if FAQ exists
2. **Link Existing**: Point customers to relevant articles
3. **Enhance FAQ**: Add missing information to existing articles
4. **Create New**: Turn unique solutions into new FAQs

## For Administrators

### FAQ Management Strategy

#### Content Planning

##### Regular Review

- **Monthly Audits**: Review FAQ usage statistics
- **Outdated Content**: Update or remove obsolete articles
- **Gap Analysis**: Identify missing topics from ticket trends
- **Performance Metrics**: Track FAQ effectiveness

##### Content Calendar

- **Seasonal Updates**: Update FAQs for product changes
- **Feature Releases**: Create FAQs for new features
- **Common Issues**: Document recurring support issues
- **User Feedback**: Act on customer suggestions

#### Advanced Features

##### Search Analytics

- **Search Terms**: Monitor what customers search for
- **No Results**: Identify content gaps
- **Popular Articles**: Promote most useful content
- **Exit Points**: See where customers leave FAQ

##### Integration Management

- **Ticket Integration**: Configure FAQ suggestions in ticket forms
- **Search Configuration**: Tune search relevance and indexing
- **Office Permissions**: Control which offices can manage FAQs
- **Approval Workflows**: Set up content review processes

### Technical Configuration

#### Search Optimization

TIKM uses TNTSearch for FAQ searching:

```php
// Search configuration in config/scout.php
'tntsearch' => [
    'storage' => storage_path('scout'),
    'fuzziness' => true,
    'fuzzy' => [
        'prefix_length' => 2,
        'max_expansions' => 50,
        'distance' => 2
    ],
]
```

#### Performance Tuning

- **Indexing**: Regular reindexing for optimal search
- **Caching**: Cache frequently accessed articles
- **Database**: Optimize FAQ queries for large volumes
- **CDN**: Serve images and assets from CDN

## Best Practices

### Content Creation

#### Writing Guidelines

1. **Customer-Centric**: Write from customer perspective
2. **Action-Oriented**: Focus on solving problems
3. **Scannable**: Use headings, bullets, and formatting
4. **Complete**: Include all necessary information
5. **Current**: Keep content up-to-date

#### Maintenance

1. **Regular Updates**: Review and update monthly
2. **User Feedback**: Act on ratings and comments
3. **Analytics Review**: Monitor usage patterns
4. **Content Pruning**: Remove outdated articles

### Organization

#### Categorization

- **Logical Grouping**: Group related articles together
- **Consistent Naming**: Use clear, descriptive categories
- **Hierarchy**: Organize from general to specific
- **Cross-Reference**: Link related articles

#### Search Optimization

- **Keyword Rich**: Include terms customers use
- **Natural Language**: Write how customers ask questions
- **Synonyms**: Include alternative terms
- **Common Misspellings**: Account for typical errors

## Measuring Success

### Key Metrics

#### Usage Statistics

- **Page Views**: Track total FAQ visits
- **Search Queries**: Monitor search behavior
- **Article Ratings**: Measure content quality
- **Time on Page**: Gauge content engagement

#### Support Impact

- **Ticket Reduction**: Measure decreased ticket volume
- **Deflection Rate**: Calculate FAQ vs ticket creation ratio
- **Resolution Time**: Track faster problem resolution
- **Customer Satisfaction**: Monitor satisfaction scores

### Continuous Improvement

#### Feedback Loops

1. **Customer Ratings**: Implement rating system
2. **Agent Feedback**: Regular content review sessions
3. **Analytics Review**: Monthly performance analysis
4. **Content Updates**: Regular refresh cycles

#### Evolution Strategy

- **Trending Topics**: Create content for emerging issues
- **User Requests**: Respond to customer suggestions
- **Product Changes**: Update for system modifications
- **Best Practices**: Adopt industry standards

---

**Next**: [Admin Guide](../admin/admin-guide.md) - System administration guide
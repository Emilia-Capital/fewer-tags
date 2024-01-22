[![CS](https://github.com/jdevalk/fewer-tags/actions/workflows/cs.yml/badge.svg)](https://github.com/jdevalk/fewer-tags/actions/workflows/cs.yml)
[![Lint](https://github.com/jdevalk/fewer-tags/actions/workflows/lint.yml/badge.svg)](https://github.com/jdevalk/fewer-tags/actions/workflows/lint.yml)
[![Security](https://github.com/Emilia-Capital/fewer-tags/actions/workflows/security.yml/badge.svg)](https://github.com/Emilia-Capital/fewer-tags/actions/workflows/security.yml)

# Fewer Tags
One of the most common SEO problems on WordPress sites is that people add too many tags to posts. The WordPress interface makes it incredibly easy to do so, and with every tag you add, you add another URL to your site for search engines to crawl and index. This plugin minimizes that effect by setting a minimum number of posts needed for a tag to be “live” on your site.

Tags that have fewer than the configured number of posts:
 * don't work on your site, and are redirected to your homepage.
 * no longer show up in tag listings.
 * are no longer linked in WordPress core's or Yoast SEO generated XML sitemaps.

This positively affects your site's SEO and also leads to less crawling, as you have fewer useless tag pages.

### Development

To test the Playground specific setup in development, add the following to your `wp-config.php`:

```php
define( 'IS_PLAYGROUND_PREVIEW', true );
```

## Frequently Asked Questions

### Can I safely install this on an existing site?

So you have a site with a lot of tags that don't add any value? Yes, you can safely add this plugin. It will redirect the useless tag pages to your site's homepage.

### Should I also noindex my tag pages?

No, you should not noindex your tag pages. If your tag pages have more than 10 posts in them, they are valuable ways of getting your site crawled and of combining related content. There's no reason to noindex those pages at that point. What you could (and should) do is add descriptions to those tag pages.

### How can I report security bugs?

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/fewer-tags)

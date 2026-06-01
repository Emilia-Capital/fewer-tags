[![CS](https://github.com/ProgressPlanner/fewer-tags/actions/workflows/cs.yml/badge.svg)](https://github.com/ProgressPlanner/fewer-tags/actions/workflows/cs.yml)
[![Lint](https://github.com/ProgressPlanner/fewer-tags/actions/workflows/lint.yml/badge.svg)](https://github.com/ProgressPlanner/fewer-tags/actions/workflows/lint.yml)
[![Security](https://github.com/ProgressPlanner/fewer-tags/actions/workflows/security.yml/badge.svg)](https://github.com/ProgressPlanner/fewer-tags/actions/workflows/security.yml)
[![PHPUnit](https://github.com/ProgressPlanner/fewer-tags/actions/workflows/phpunit.yml/badge.svg)](https://github.com/ProgressPlanner/fewer-tags/actions/workflows/phpunit.yml)

![Fewer Tags](/.wordpress-org/github_banner_fewer_tags_pp.png)

# Fewer Tags

Fewer Tags helps WordPress sites avoid thin, low-value tag archives.

Instead of letting every tag create another archive URL, the plugin lets you set a minimum number of posts a tag needs before it is considered live on your site. Tags below that threshold are hidden from visitors and search engines, redirected to your homepage, and excluded from supported XML sitemaps.

As of version 2.0, Fewer Tags also lets you merge terms across taxonomies and create redirects when you merge or delete terms — all the functionality previously in Fewer Tags Pro is now included for free.

That means fewer useless tag pages, cleaner taxonomy archives, and less crawl waste.

## What it does

Fewer Tags lets you define the minimum number of posts a tag needs before it becomes live. Tags below that threshold:

- redirect their archive page to your homepage
- no longer appear in tag listings
- no longer appear in WordPress core XML sitemaps
- no longer appear in Yoast SEO XML sitemaps
- no longer appear in Slim SEO XML sitemaps

The default threshold is 10 posts, and you can change it under **Settings → Reading**.

## Merging and redirects

- Merge any tag, category, or custom taxonomy term into another — even across taxonomies. All posts from the source term are moved to the target term, and a 301 redirect is created from the old term archive to the new one.
- When you delete a term, Fewer Tags prompts you to create a redirect to the homepage or any other URL.
- Redirects are created via the [Redirection plugin](https://wordpress.org/plugins/redirection/) or Yoast SEO Premium.

## Why use it?

Many WordPress sites accumulate lots of tags that only contain one or two posts. Those tag archives rarely help users, and they create extra URLs for search engines to crawl and index.

Fewer Tags gives you a simple way to keep useful tag archives while suppressing the ones that add little value.

## How it works

- On the front end, low-volume tag archives redirect to the homepage.
- On posts, low-volume tags are filtered from tag output.
- In the WordPress admin, you can see whether a tag is live in the Tags overview.
- In supported sitemap providers, low-volume tags are excluded automatically.

## Installation

1. Install **Fewer Tags** from your WordPress dashboard or upload the plugin manually.
2. Activate the plugin.
3. Go to **Settings → Reading**.
4. Choose how many posts a tag needs before it becomes live on your site.
5. For the merge and redirect-on-delete features, install and activate the [Redirection plugin](https://wordpress.org/plugins/redirection/) or Yoast SEO Premium.

## FAQ

### Can I safely install this on an existing site?

Yes. If your site already has lots of low-value tags, Fewer Tags will start suppressing tag archives that fall below your chosen threshold.

### Should I noindex tag pages instead?

Usually no. Tag archives can be useful when they group enough related content. Fewer Tags is designed to keep valuable tag archives live while suppressing the weak ones.

### How can I report security bugs?

Please use the Patchstack Vulnerability Disclosure Program to report security issues: https://patchstack.com/database/vdp/fewer-tags

## Learn more

- Plugin home: https://progressplanner.com/plugins/fewer-tags/
- Research: https://fewertags.com/research/
- Free plugin walkthrough: https://www.youtube.com/watch?v=KItn1X1qMas

## Development

To test the Playground-specific setup in development, add the following to your `wp-config.php`:

```php
define( 'IS_PLAYGROUND_PREVIEW', true );
```

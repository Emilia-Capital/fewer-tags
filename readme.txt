=== Fewer Tags ===
Contributors: joostdevalk, aristath, filipi, progressplanner
Tags: seo, tags, taxonomy, sitemap, archives
Requires at least: 6.7
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.0
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Manage your site's tags: set minimum post counts, merge terms across taxonomies, and create redirects when deleting or merging terms.

== Description ==
One of the most common SEO problems on WordPress sites is that people add too many tags to posts. In fact, [our research shows](https://fewertags.com/research/) that _most_ WordPress sites use tags wrong. The WordPress interface makes it incredibly easy to do so, and with every tag you add, you add another URL to your site for search engines to crawl and index.

Fewer Tags solves this problem for you! It does that by:

**Minimum post count threshold**

Tags that have less than the configured number of posts (default: 10):

* Are hidden from visitors and search engines on your site, and are redirected to your homepage.
* They don't show up in tag listings.
* They are no longer linked in WordPress core's or Yoast SEO or Slim SEO generated XML sitemaps.

**Merge terms**

* Merge any tag, category, or custom taxonomy term into another — even across taxonomies.
* All posts from the source term are moved to the target term.
* A 301 redirect is automatically created from the old term archive to the new one.

**Redirect on delete**

* When you delete a term, Fewer Tags prompts you to create a redirect to the homepage or any other URL.
* Redirects are created via the [Redirection plugin](https://wordpress.org/plugins/redirection/) or Yoast SEO Premium.

This positively affects your site's SEO and also leads to less crawling, as you have fewer useless tag pages.

See [this video](https://www.youtube.com/watch?v=KItn1X1qMas) if you want to learn how to use Fewer Tags.

https://www.youtube.com/watch?v=KItn1X1qMas

You can learn more about Fewer Tags on [fewertags.com](https://fewertags.com/)!

== Frequently Asked Questions ==

= Can I safely install this on an existing site? =

So you have a site with a lot of tags that don't add any value? Yes, you can safely add this plugin. It will redirect the useless tag pages to your site's homepage.

= Should I also noindex my tag pages? =

No, you should not noindex your tag pages. If your tag pages have more than 10 posts in them, they are valuable ways of getting your site crawled and of combining related content. There's no reason to noindex those pages at that point. What you could (and should) do is add descriptions to those tag pages.

= Do I need the Redirection plugin or Yoast SEO Premium? =

For the merge and redirect-on-delete features to create actual redirects, you need either the [Redirection plugin](https://wordpress.org/plugins/redirection/) (free) or Yoast SEO Premium installed and activated. Without either plugin, merging and deleting will still work, but no redirects will be created.

= How can I report security bugs? =

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/fewer-tags)

== Installation ==
1. Search for Fewer Tags on the repository.
2. Install the plugin.
3. Optionally: go to Settings → Reading and adjust the default minimum number of posts (the default is 10).
4. For merge and redirect features, install and activate the [Redirection plugin](https://wordpress.org/plugins/redirection/) or Yoast SEO Premium.
5. You're done.

== Screenshots ==
1. The Fewer Tags settings on the Settings → Reading screen.
2. Fewer Tags adds a column to the Tags overview page, showing which tags are live and which aren't.
3. Search engines agree with us, this is Fabrice Canel, Head of Bing, on LinkedIn.

== Changelog ==

= 2.0 =

Major release: all Fewer Tags Pro functionality is now included in the free plugin.

New features:

* Merge terms — merge any tag, category, or custom taxonomy term into another, including cross-taxonomy merges.
* Redirect on delete — when you delete a term, get prompted to create a redirect to the homepage or any other URL.
* Redirect creation via Redirection plugin or Yoast SEO Premium.
* Admin notice when neither Redirection nor Yoast SEO Premium is installed, with install/activate buttons.

= 1.5.1 =

* Fixes a case where the `keywords` output in the Yoast SEO schema might be of the wrong type.

= 1.5 =

Enhancements:

* Add support for Slim SEO, props [Anh Tran](https://profiles.wordpress.org/rilwis/).

Bugfixes:

* Reinstates the "View" action for other taxonomies than tags.

Development:

* Updated PHPCompatibility, added PHPStan and fixed all resulting issues.

= 1.4.1 =

* Trigger rebuild because we broke the WordPress.org plugin build system.

= 1.4 =

* Changed the option name from `joost_min_posts_count` to `fewer_tags` so it's more recognizable for people.
* Added uninstall functionality that removes the setting from the database on uninstall of the plugin.
* Simplified the autoloader, no longer requiring composer packages.
* Added videos for Fewer Tags Free and Fewer Tags Pro to the readme.txt.

= 1.3.3 =

* Minor stability fixes.

= 1.3.2 =

* Fix fatal error caused by not loading the autoload file.

= 1.3 =

* Make sure the output of the Gutenberg terms block is filtered to when it's showing tags.
* Some minor optimizations to how the plugin is loaded to speed it up.

= 1.2 =

First release on WordPress.org.

= 1.1 =

Minor header and sanitization improvements.

= 1.0 =

Initial release on GitHub.

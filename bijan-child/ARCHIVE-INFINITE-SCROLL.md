# Product category infinite scroll

The existing `archive-ajax-filters` controller now enhances product categories.
The parent theme is unchanged. Ordinary numbered pages remain server-rendered,
with real pagination anchors and independent canonicals (Rank Math, Yoast, or
the fallback when neither is present). Intro, long description and FAQs appear
only on page 1. AJAX pagination replaces the list and synchronizes that content.

## Loading and compatibility

- IntersectionObserver prefetches **one** subsequent page at 1,800px from the
  viewport (2,800px for slower reported connections), then appends at 700px.
  These distances provide lead time; network speed cannot guarantee completion.
- Unfiltered continuation fetches the existing GET URL, using existing cache/CDN
  infrastructure. The response is the native archive HTML; only product cards,
  pagination and tiny page metadata are retained. No separate product query,
  new endpoint, library or server cache is added.
- Filtered continuation uses the existing sanitized POST query. Filter/sort
  changes abort continuation and invalidate stale responses. Requests time out
  after 20 seconds; retries are explicit and original products remain visible.
- Appended images use native lazy loading and asynchronous decoding while
  retaining responsive sources and dimensions. Existing delegated cart handlers
  apply to appended cards. The containing Swiper, if present, is updated.
- Product IDs prevent duplicate cards. A changed 12-hour shuffle window prompts
  an AJAX reset rather than combining incompatible product orders.
- Scroll updates the current URL with `replaceState`; explicit page/filter
  changes use `pushState`. Back/forward restores the stored page/filter state.
  Modified pagination clicks retain normal browser behavior.
- Pause/resume and manual loading allow footer access. Data Saver and browsers
  without IntersectionObserver start with manual loading. Automatic appends
  announce status without moving focus; manual loading focuses the first new link.
- Balanced archive wrappers and an empty-result template keep AJAX replacement
  consistent. Single-product wrappers still use the parent templates.

## Verification

`tests/archive-pagination-regression.php` checks page metadata, terminal pages,
self-canonicals, content gating and rendered populated/empty archive wrappers.

```sh
php tests/archive-pagination-regression.php
```

`tests/archive-infinite-scroll.cjs` uses Playwright and jQuery to test the actual
controller against isolated archive fixtures. It covers desktop/mobile,
prefetch reuse, stale cancellation, error recovery, empty results, shuffle
rotation, duplication, history, query-string pagination, Data Saver, keyboard
focus, real observers, pause/resume, terminal pages and JavaScript-disabled links.
Install those test dependencies in a temporary directory, then run with its
`node_modules` directory in `NODE_PATH`. `CHROMIUM_PATH` is optional if Playwright
already has its own browser installed.

```sh
NODE_PATH=/path/to/test/node_modules node tests/archive-infinite-scroll.cjs
```

This workspace contains the theme, not a running WordPress/WooCommerce install.
Before production rollout, purge the affected category page caches and verify
actual category pages with the site's active plugin settings: page-2 HTTP 200,
self-canonical and robots directives, responsive product images, cart/wishlist
interactions on appended products, filtering and history. Avoid enabling an SEO
plugin setting that noindexes pagination if these pages should be indexed.

## ACF category discovery rail

`inc/category-discovery.php` renders the existing `sub_categories` fields as
horizontal cards with 4:3 responsive images and titles underneath. Assets load
only on categories containing this ACF content (including later pages, which
can return to page 1 through AJAX). The first two small images are eager with
low fetch priority; remaining images are lazy. All keep dimensions and srcset.

The component uses native horizontal scrolling, proximity snapping and small
previous/next controls. It does not cancel touch or wheel events, intercept
vertical arrows, or set a restrictive touch-action. Autoplay advances one card
every three seconds, reversing at the ends without cloned content. It suspends
outside the viewport, in hidden tabs, during hover/focus/touch and for ten seconds
after manual scrolling. Reduced motion disables autoplay. There is an explicit
pause/resume button. One timeout, visibility/resize observers and a scroll-frame
update replace a continuous animation loop. AJAX replacement destroys old
listeners/observers before initializing the new rail.

The new JavaScript and CSS total approximately 3.3 KB when gzipped and add no
runtime dependency. The rendered component and its RTL/LTR geometry, mobile
layout, vertical scrolling, controls, autoplay, pause, reduced motion and AJAX
lifecycle are checked by `tests/category-discovery-render.php` and
`tests/category-discovery.cjs` (same Playwright setup as above; set `PHP_BINARY`
if PHP is outside PATH). Preview tests use illustrative fixture images, not
production ACF images. The located source is the category archive's ACF rail;
this repository contains no matching image-card ACF rail on single products.

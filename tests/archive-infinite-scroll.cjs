/* Run with Playwright and jQuery available via NODE_PATH. No live store writes. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('playwright');
const root = path.resolve(__dirname, '..');
const source = fs.readFileSync(path.join(root, 'bijan-child/assets/archive-ajax-filters.js'), 'utf8');
const jquery = fs.readFileSync(require.resolve('jquery'), 'utf8');
const css = fs.readFileSync(path.join(root, 'bijan-child/assets/archive-ajax-filters.css'), 'utf8');
const origin = 'https://archive.test';
const pageURL = (page, plain) => `${origin}/category/shirts/${plain ? (page > 1 ? `?paged=${page}` : '') : (page > 1 ? `page/${page}/` : '')}`;
const fixture = (page = 1, opts = {}) => {
  const url = pageURL(page, opts.plain);
  const cards = Array.from({ length: 6 }, (_, index) => {
    const id = opts.duplicate && index === 0 ? 6 : (page - 1) * 6 + index + 1;
    return `<li class="product post-${id}"><a href="/product/${id}/">${opts.filtered ? 'filtered-' : ''}Product ${id}</a><button class="add-to-cart">Add</button></li>`;
  }).join('');
  const pagination = `<nav class="woocommerce-pagination">${[1,2,3,4].map(n => `<a class="page-numbers ${n === page + 1 ? 'next' : ''}" href="${pageURL(n, opts.plain)}">${n}</a>`).join('')}</nav>`;
  return `<!doctype html><html dir="rtl"><head><meta charset="utf-8"><title>Shirts - ${page}</title><link rel="canonical" href="${url}"><style>${css} ul.products{display:grid;grid-template-columns:repeat(2,1fr);margin:0;gap:10px;padding:0}li.product{height:420px;list-style:none;background:#eee}body{margin:0}.entry-container{width:100%}.cloz-category-intro-section{height:100px}footer{height:400px}</style></head><body class="woocommerce archive"><main><header class="woocommerce-products-header"><h1>Shirts</h1>${page === 1 ? '<div class="cloz-category-intro-section">Category intro</div>' : ''}</header><div id="primary"><aside id="sidebar" class="sidebar-shop"><button data-cloz-archive-state='{"instock":"1"}'>Filter</button><button data-cloz-archive-state='{}'>Clear</button></aside><form id="sort-wrap" class="woocommerce-ordering"><button type="button" class="sort-item" data-sort="price">Price</button></form><div class="entry-container">${opts.empty ? '<p role="status">No products</p>' : `<div class="bijan-slider-wrap"><ul class="products">${cards}</ul></div><span class="cloz-archive-page-data" hidden data-page="${page}" data-pages="4" data-url="${url}" data-next="${page < 4 ? pageURL(page + 1, opts.plain) : ''}" data-order-window="${opts.rotated ? 2 : 1}"></span>${pagination}`}${page === 1 && !opts.empty ? '<section class="cloz-long-desc-section">Long description</section><section class="category-faq-section"><div class="faq-item"><button class="faq-question" aria-expanded="false">Question</button></div></section>' : ''}</div>${page === 1 ? '<div class="term-description">Native description</div>' : ''}</div></main><footer>Footer</footer><script>window.clozArchiveFilters={url:'${pageURL(1,opts.plain)}',key:'cloz_archive_ajax',state:{},infinite:true};window.boots=(window.boots||0)+1;</script><script src="/jquery.js"></script><script src="/archive.js"></script></body></html>`;
};

(async () => {
  const browser = await chromium.launch({ headless: true, ...(process.env.CHROMIUM_PATH ? { executablePath: process.env.CHROMIUM_PATH } : {}) });
  const errors = [];
  const setup = async (opts = {}) => {
    const context = await browser.newContext({ viewport: opts.mobile ? {width:390,height:844} : {width:1200,height:800}, reducedMotion:'reduce' });
    const page = await context.newPage();
    const requests = [];
    const behavior = { fail: false, delay: 0, empty: false, duplicate: false, rotated: false };
    page.on('pageerror', error => errors.push(error.message));
    if (!opts.realObserver) await page.addInitScript(() => {
      window.testObservers = [];
      window.IntersectionObserver = class {
        constructor(callback, options) { this.callback=callback; this.options=options; this.targets=new Set(); window.testObservers.push(this); }
        observe(target) { this.targets.add(target); }
        unobserve(target) { this.targets.delete(target); }
        disconnect() { this.targets.clear(); }
      };
      window.fireObserver = margin => window.testObservers.filter(o => o.options.rootMargin === margin).forEach(o => o.callback([...o.targets].map(target => ({target,isIntersecting:true}))));
    });
    if (opts.noObserver) await page.addInitScript(() => { delete window.IntersectionObserver; });
    if (opts.saveData) await page.addInitScript(() => { Object.defineProperty(navigator,'connection',{value:{saveData:true,effectiveType:'3g'}}); });
    await page.route(`${origin}/**`, async route => {
      const request = route.request();
      if (request.url().endsWith('jquery.js')) return route.fulfill({contentType:'text/javascript',body:jquery});
      if (request.url().endsWith('archive.js')) return route.fulfill({contentType:'text/javascript',body:source});
      const body = new URLSearchParams(request.postData() || '');
      const url = new URL(request.url());
      const number = request.method()==='POST' ? Number(body.get('paged') || 1) : Number(url.searchParams.get('paged') || url.pathname.match(/page\/(\d+)/)?.[1] || 1);
      requests.push({method:request.method(),number,body:Object.fromEntries(body),navigation:request.isNavigationRequest()});
      if (behavior.fail && !request.isNavigationRequest()) { behavior.fail=false; return route.fulfill({status:503,body:'Unavailable'}); }
      if (behavior.delay && !request.isNavigationRequest()) await new Promise(resolve => setTimeout(resolve, behavior.delay));
      return route.fulfill({contentType:'text/html',body:fixture(number,{...opts,...behavior,filtered:body.has('instock') || body.has('orderby')})}).catch(()=>{});
    });
    await page.goto(pageURL(opts.start || 1,opts.plain));
    await page.waitForFunction(() => document.querySelector('.cloz-infinite-controls'));
    return { page, context, requests, behavior };
  };
  const ready = page => page.waitForFunction(() => !document.body.classList.contains('cloz-archive-is-loading'));
  const fire = (page,margin='700px 0px') => page.evaluate(m => window.fireObserver(m),margin);
  const count = (page,n) => page.waitForFunction(n => document.querySelectorAll('ul.products > li.product').length===n,n);
  try {
    let t = await setup();
    await fire(t.page,'1800px 0px');
    await t.page.waitForFunction(() => window.performance.getEntriesByType('resource').some(r=>r.name.endsWith('/page/2/')));
    await fire(t.page,'1800px 0px');
    assert.equal(t.requests.filter(r=>r.number===2).length,1,'only one speculative request');
    assert.equal(await t.page.locator('li.product').count(),6,'prefetch does not append too early');
    await fire(t.page); await count(t.page,12);
    assert.equal(t.requests.filter(r=>r.number===2).length,1,'append reuses prefetched page');
    assert.equal(await t.page.locator('.cloz-long-desc-section').count(),1,'category content is not duplicated');
    assert.equal(await t.page.evaluate(()=>window.boots),1,'no document reload or script replay');
    await t.page.locator('.post-7').scrollIntoViewIfNeeded();
    await t.page.waitForURL('**/page/2/');
    assert.equal(await t.page.locator('link[rel="canonical"]').getAttribute('href'),pageURL(2),'URL and canonical follow visible page');
    await t.page.evaluate(()=>window.scrollTo(0,0)); await t.page.waitForURL(pageURL(1));
    await t.context.close(); console.log('PASS cached prefetch, append, SEO URL, scroll reversal');

    t = await setup({mobile:true});
    await t.page.locator('.woocommerce-pagination a').filter({hasText:'3'}).click(); await ready(t.page);
    await t.page.waitForURL('**/page/3/');
    assert.equal(await t.page.locator('li.product').count(),6);
    assert.equal(await t.page.locator('.cloz-category-intro-section,.cloz-long-desc-section,.category-faq-section,.term-description').count(),0,'page 3 has products without category content');
    assert.equal(t.requests.filter(r=>r.navigation).length,1);
    await t.page.goBack(); await ready(t.page); await t.page.waitForURL(pageURL(1));
    await t.page.waitForSelector('.cloz-category-intro-section');
    await t.page.locator('.faq-question').click();
    assert.equal(await t.page.locator('.faq-question').getAttribute('aria-expanded'),'true','FAQ works after AJAX back');
    await t.page.goForward(); await ready(t.page); await t.page.waitForURL('**/page/3/');
    await t.context.close(); console.log('PASS mobile pagination, category content, back/forward, AJAX FAQ');

    t = await setup();
    t.behavior.delay=180;
    await fire(t.page,'1800px 0px');
    await t.page.getByRole('button',{name:'Filter',exact:true}).click(); await ready(t.page);
    await fire(t.page); await count(t.page,12);
    assert.ok((await t.page.locator('li.product a').allTextContents()).every(x=>x.startsWith('filtered-')),'stale unfiltered response is not inserted');
    const next=t.requests.filter(r=>r.number===2).at(-1);
    assert.equal(next.method,'POST'); assert.equal(next.body.instock,'1');
    await t.context.close(); console.log('PASS filter cancels stale request and preserves filtered continuation');

    t = await setup(); t.behavior.fail=true;
    await fire(t.page); await t.page.getByRole('button',{name:'تلاش دوباره'}).waitFor();
    assert.equal(await t.page.locator('li.product').count(),6);
    await t.page.getByRole('button',{name:'تلاش دوباره'}).click(); await count(t.page,12);
    t.behavior.empty=true;
    await t.page.getByRole('button',{name:'Filter',exact:true}).click(); await ready(t.page);
    assert.equal(await t.page.locator('li.product').count(),0,'empty results replace products');
    t.behavior.empty=false;
    await t.page.getByRole('button',{name:'Clear',exact:true}).click(); await ready(t.page); await count(t.page,6);
    await t.context.close(); console.log('PASS network retry and recovery from empty results');

    t = await setup(); t.behavior.duplicate=true;
    await fire(t.page); await count(t.page,11);
    assert.equal(await t.page.locator('.post-6').count(),1);
    await t.context.close(); console.log('PASS duplicate product guard');

    t = await setup(); t.behavior.rotated=true;
    await fire(t.page); await t.page.getByRole('button',{name:'به‌روزرسانی فهرست'}).waitFor();
    assert.equal(await t.page.locator('li.product').count(),6);
    await t.page.getByRole('button',{name:'به‌روزرسانی فهرست'}).click(); await ready(t.page);
    await fire(t.page); await count(t.page,12);
    await t.context.close(); console.log('PASS 12-hour shuffle boundary safely resets');

    t = await setup({plain:true,start:2});
    await fire(t.page); await count(t.page,12);
    assert.equal(t.requests.at(-1).number,3);
    await t.context.close(); console.log('PASS direct paginated entry and query-string permalinks');

    t = await setup({saveData:true}); await fire(t.page); await fire(t.page,'2800px 0px');
    assert.equal(t.requests.length,1,'data saver starts with manual loading');
    await t.page.getByRole('button',{name:'نمایش محصولات بیشتر'}).click(); await count(t.page,12);
    assert.equal(await t.page.evaluate(()=>document.activeElement.closest('li')?.classList.contains('post-7')),true,'manual loading moves focus to new products');
    await t.context.close(); console.log('PASS data saver and keyboard focus');

    t = await setup({noObserver:true});
    await t.page.getByRole('button',{name:'نمایش محصولات بیشتر'}).click(); await count(t.page,12);
    await t.context.close(); console.log('PASS observer-less fallback');

    t = await setup(); t.behavior.fail=true;
    await t.page.getByRole('button',{name:'Filter',exact:true}).click(); await ready(t.page);
    await t.page.locator('.cloz-archive-error').waitFor();
    await fire(t.page); await count(t.page,12);
    assert.ok((await t.page.locator('li.product a').allTextContents()).every(x=>!x.startsWith('filtered-')),'failed filter leaves original state intact');
    await t.context.close(); console.log('PASS failed filter preserves list and infinite session');

    t = await setup({realObserver:true});
    await t.page.evaluate(() => window.scrollTo(0, 400));
    await count(t.page,12);
    assert.equal(t.requests.filter(r=>r.number===2).length,1);
    await t.page.getByRole('button',{name:'توقف بارگذاری خودکار'}).click();
    await t.page.locator('footer').scrollIntoViewIfNeeded();
    await t.context.close(); console.log('PASS real IntersectionObserver preloads and pause allows footer access');

    t = await setup();
    await t.page.getByRole('button',{name:'توقف بارگذاری خودکار'}).click();
    await fire(t.page); assert.equal(t.requests.length,1);
    await t.page.getByRole('button',{name:'ادامهٔ بارگذاری خودکار'}).click();
    await fire(t.page); await count(t.page,12);
    await fire(t.page); await count(t.page,18);
    await fire(t.page); await count(t.page,24);
    assert.equal(await t.page.locator('.cloz-load-more').isVisible(),false,'last page removes load control');
    await fire(t.page);
    assert.equal(t.requests.filter(r=>r.number>4).length,0,'no request beyond last page');
    assert.equal(await t.page.locator('.woocommerce-pagination a').count(),4,'pagination still exists at the end');
    await t.context.close(); console.log('PASS pause/resume and final page termination');

    const noJS = await browser.newContext({javaScriptEnabled:false});
    const noJSPage = await noJS.newPage();
    await noJSPage.route(`${origin}/**`, route => {
      const number=Number(new URL(route.request().url()).pathname.match(/page\/(\d+)/)?.[1]||1);
      return route.fulfill({contentType:'text/html; charset=utf-8',body:fixture(number)});
    });
    await noJSPage.goto(pageURL(1));
    await noJSPage.locator('.woocommerce-pagination a').filter({hasText:'2'}).click();
    assert.equal(noJSPage.url(),pageURL(2));
    assert.equal(await noJSPage.locator('li.product').count(),6);
    assert.equal(await noJSPage.locator('.cloz-category-intro-section,.cloz-long-desc-section,.category-faq-section,.term-description').count(),0);
    assert.equal(await noJSPage.locator('link[rel="canonical"]').getAttribute('href'),pageURL(2));
    await noJS.close(); console.log('PASS crawlable server pagination without JavaScript');

    assert.deepEqual(errors,[],'no browser exceptions');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode=1; });

const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),{execFileSync}=require('node:child_process'),{chromium}=require('playwright');
const root=path.resolve(__dirname,'..'),read=p=>fs.readFileSync(path.join(root,p),'utf8');
const html=execFileSync(process.env.PHP_BINARY||'php',[path.join(__dirname,'navigation-drawer-render.php')],{encoding:'utf8'});
const font=fs.readFileSync(path.join(root,'bijan/assets/fonts/iranyekanxfanum-regular.woff2')).toString('base64');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:process.env.CHROMIUM_PATH});

 try {for(const width of [320,390,768,1024,1440]) {

 const height=width===768?400:800;
 const page=await browser.newPage({viewport:{width,height},reducedMotion:'reduce'});
 await page.setContent(`<!doctype html><html dir="rtl"><head><style>${read('bijan/assets/css/style.min.css')}${read('bijan-child/style.css')}${read('bijan-child/assets/product-critical-mobile.css')}${read('bijan-child/assets/navigation-drawer.css')}@font-face{font-family:DrawerUI;src:url(data:font/woff2;base64,${font})}body{font-family:DrawerUI;margin:0}#header-container{height:70px;position:relative!important;display:flex;align-items:center;gap:20px}#header-container>button{position:static!important;transform:none!important;z-index:1;width:80px;height:44px}main{height:2000px}footer{height:120px}</style></head><body><header id="header-container"><button id="header-toggle-mobile-menu" class="toggle-mobile-menu">فهرست</button><button class="toggle-account-menu">حساب</button></header><main>محتوای صفحه</main><footer>فوتر</footer>${html}<div id="mobile-account-menu-container" class="mobile-menu-container"><a href="#account">حساب من</a></div><div id="mobile-menu-overlay" class="hide-desktop"></div></body></html>`);

 await page.addScriptTag({content:read('bijan-child/assets/mobile-header.js')});
 await page.evaluate(()=>document.fonts.ready);
 assert.equal(await page.locator('#mobile-menu-container').evaluate(e=>e.inert),true);
 await page.locator('#header-toggle-mobile-menu').click();
 assert.equal(await page.locator('#mobile-menu-container').getAttribute('aria-hidden'),'false');
 assert.equal(await page.locator('.clz-drawer-close').evaluate(e=>e===document.activeElement),true);
 const iconSize=await page.locator('.mobile-menu-wrap a').first().evaluate(e=>{const icon=document.createElement('img');icon.className='menu-item-icon';icon.width=300;icon.height=300;icon.alt='';e.prepend(icon);const r=icon.getBoundingClientRect();icon.remove();return {width:r.width,height:r.height}});
 assert.deepEqual(iconSize,{width:20,height:20},'Native menu images cannot enlarge rows');
 const closeGeometry=await page.locator('.clz-drawer-close').evaluate(e=>{const b=e.getBoundingClientRect(),s=e.querySelector('svg').getBoundingClientRect();return {dx:Math.abs(b.x+b.width/2-s.x-s.width/2),dy:Math.abs(b.y+b.height/2-s.y-s.height/2),size:b.width}});
 assert.ok(closeGeometry.dx<1&&closeGeometry.dy<1&&closeGeometry.size>=44,'Close icon is centered within its touch target');
 const geo=await page.locator('#mobile-menu-container').evaluate(e=>{const r=e.getBoundingClientRect();return {x:r.x,right:r.right,height:r.height,overflow:e.scrollWidth>e.clientWidth}});
 assert.ok(geo.x>=0&&geo.right===width&&geo.height===height&&!geo.overflow,JSON.stringify(geo));
 assert.equal(await page.locator('#mobile-menu-overlay').evaluate(e=>getComputedStyle(e).display),'block');
 assert.equal(await page.locator('main').evaluate(e=>e.inert),true);
 assert.equal(await page.locator('.clz-submenu-toggle').first().getAttribute('aria-expanded'),'true');
 await page.locator('.clz-submenu-toggle').nth(1).click();
 assert.equal(await page.locator('.clz-submenu-toggle').nth(1).getAttribute('aria-expanded'),'true');
 await page.locator('.clz-submenu-toggle').nth(1).click();
 assert.equal(await page.locator('.clz-submenu-toggle').nth(1).getAttribute('aria-expanded'),'false');
 await page.locator('.mobile-menu-scroll').evaluate(e=>e.scrollTop=e.scrollHeight);
 assert.equal(await page.locator('.clz-drawer-heading').evaluate(e=>e.getBoundingClientRect().y),0);
 assert.equal(await page.evaluate(()=>scrollY),0);
 await page.locator('.mobile-menu-scroll').evaluate(e=>e.scrollTop=0);
 const targets=await page.locator('#mobile-menu-container a:visible,#mobile-menu-container button:visible').evaluateAll(es=>es.map(e=>e.getBoundingClientRect().height));assert.ok(targets.every(h=>h>=44));
 await page.locator('.clz-drawer-close').focus();await page.keyboard.press('Shift+Tab');assert.equal(await page.locator('#mobile-menu-container a').last().evaluate(e=>e===document.activeElement),true);
 await page.keyboard.press('Tab');assert.equal(await page.locator('.clz-drawer-close').evaluate(e=>e===document.activeElement),true);
 // Native parent category href must survive the legacy menu click handler.
 assert.equal(await page.locator('.menu-item-has-children > a').first().evaluate(e=>{const ev=new MouseEvent('click',{bubbles:true,cancelable:true});let calls=0;Event.prototype.preventDefault.call(ev);ev.preventDefault=()=>{calls++};e.dispatchEvent(ev);return calls}),0);
 await page.locator('.mobile-menu-scroll').evaluate(e=>e.scrollTop=0);
 assert.equal(await page.locator('.clz-drawer-heading').evaluate(e=>e.getBoundingClientRect().y),0);

 if(width===390||width===1440) await page.screenshot({path:`/tmp/navigation-drawer-${width}.png`});
 await page.keyboard.press('Escape');await page.waitForFunction(()=>!document.body.classList.contains('mobile-menu-opened'));await page.waitForFunction(()=>document.activeElement===document.getElementById('header-toggle-mobile-menu'));assert.equal(await page.locator('main').evaluate(e=>e.inert),false);
 await page.locator('#header-toggle-mobile-menu').click();await page.locator('.clz-drawer-close').click();assert.equal(await page.locator('#mobile-menu-container').evaluate(e=>e.inert),true);
 await page.locator('#header-toggle-mobile-menu').click();await page.mouse.click(4,350);assert.equal(await page.locator('#mobile-menu-container').getAttribute('aria-hidden'),'true');
 await page.locator('.toggle-account-menu').click();assert.equal(await page.locator('#mobile-account-menu-container').getAttribute('aria-hidden'),'false');await page.keyboard.press('Escape');
 assert.equal(await page.locator('.toggle-account-menu').evaluate(e=>e===document.activeElement),true);
 console.log(`PASS ${width}px: right drawer, 44px targets, nested controls, real links, focus trap/return, Escape/close/overlay, account compatibility`);
 await page.close();
 }}finally{await browser.close()}
})().catch(e=>{console.error(e);process.exitCode=1});

const fs = require('node:fs');
const path = require('node:path');
const {execFileSync} = require('node:child_process');
const assert = require('node:assert/strict');
const {chromium} = require('playwright');
const root = path.resolve(__dirname, '..');
const component = execFileSync(process.env.PHP_BINARY || 'php', [path.join(__dirname,'category-discovery-render.php'),'--html'],{encoding:'utf8'});
const css = fs.readFileSync(path.join(root,'bijan-child/assets/category-discovery.css'),'utf8');
const js = fs.readFileSync(path.join(root,'bijan-child/assets/category-discovery.js'),'utf8');
(async()=>{
 const browser=await chromium.launch({headless:true,...(process.env.CHROMIUM_PATH?{executablePath:process.env.CHROMIUM_PATH}:{})});
 const errors=[];
 const setup=async({width=1100,dir='rtl',reduced=false,touch=false}={})=>{
  const context=await browser.newContext({viewport:{width,height:800},reducedMotion:reduced?'reduce':'no-preference',hasTouch:touch,isMobile:touch});
  const page=await context.newPage();page.on('pageerror',e=>errors.push(e.message));
  await page.setContent(`<!doctype html><html lang="fa" dir="${dir}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>body{margin:0;font-family:Arial,sans-serif;background:#fafcfc;color:#23373d}.shell{max-width:1060px;margin:30px auto;padding:0 18px}h1{font-size:24px}.after{height:2400px;margin-top:30px;border-top:1px solid #eee}${css}</style></head><body><main class="shell"><h1>انتخاب دستهٔ مورد علاقه</h1>${component}<div class="after">محصولات</div></main></body></html>`);
  await page.addScriptTag({content:js});
  await page.waitForFunction(()=>document.querySelector('.cloz-discovery-actions').hidden===false);
  return {context,page};
 };
 const position=p=>p.locator('.cloz-discovery-rail').evaluate(e=>e.scrollLeft);
 try {
  for (const opts of [{width:1100,dir:'rtl'},{width:390,dir:'rtl',touch:true},{width:1000,dir:'ltr'}]) {
   const {page,context}=await setup({...opts,reduced:true});
   const image=await page.locator('.cloz-discovery-image-wrap').first().boundingBox();
   const title=await page.locator('.cloz-discovery-title').first().boundingBox();
   assert.ok(Math.abs(image.width/image.height-4/3)<.02);
   assert.ok(title.y>=image.y+image.height-1,'title stays below image');
   assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),true,'no page-level horizontal overflow');
   const start=await position(page);
   await page.locator('.cloz-discovery-next').click();
   await page.waitForFunction(start=>Math.abs(document.querySelector('.cloz-discovery-rail').scrollLeft-start)>100,start);
   assert.equal(await page.evaluate(()=>window.scrollY),0,'navigation never scrolls page vertically');
   await page.locator('.cloz-discovery-prev').click();
   await page.waitForFunction(()=>Math.abs(document.querySelector('.cloz-discovery-rail').scrollLeft)<2);
   await page.locator('.cloz-discovery-rail').focus();
   await page.keyboard.press(opts.dir==='rtl'?'ArrowLeft':'ArrowRight');
   await page.waitForFunction(()=>Math.abs(document.querySelector('.cloz-discovery-rail').scrollLeft)>100);
   assert.equal(await page.locator('.cloz-discovery-pause').isVisible(),false,'reduced motion disables autoplay');
   if(opts.width===390) await page.screenshot({path:'/tmp/category-discovery-mobile.png'});
   if(opts.width===1100) { await page.locator('.cloz-discovery-prev').click();await page.locator('h1').click();await page.screenshot({path:'/tmp/category-discovery-desktop.png'}); }
   if(opts.touch) {
    await page.evaluate(()=>window.scrollTo(0,0));
    const touchBox=await page.locator('.cloz-discovery-rail').boundingBox();
    const cdp=await context.newCDPSession(page);
    const beforeTouch=await position(page);
    await cdp.send('Input.dispatchTouchEvent',{type:'touchStart',touchPoints:[{x:110,y:touchBox.y+50}]});
    for(let x=150;x<=270;x+=40) { await cdp.send('Input.dispatchTouchEvent',{type:'touchMove',touchPoints:[{x,y:touchBox.y+50}]}); await page.waitForTimeout(35); }
    await cdp.send('Input.dispatchTouchEvent',{type:'touchEnd',touchPoints:[]});
    await page.waitForFunction(v=>Math.abs(document.querySelector('.cloz-discovery-rail').scrollLeft)>Math.abs(v)+60,beforeTouch);
    assert.equal(await page.evaluate(()=>window.scrollY),0);
    await cdp.send('Input.dispatchTouchEvent',{type:'touchStart',touchPoints:[{x:220,y:touchBox.y+100}]});
    for(let dy=40;dy<=160;dy+=40) { await cdp.send('Input.dispatchTouchEvent',{type:'touchMove',touchPoints:[{x:220,y:touchBox.y+100-dy}]}); await page.waitForTimeout(35); }
    await cdp.send('Input.dispatchTouchEvent',{type:'touchEnd',touchPoints:[]});
    await page.waitForFunction(()=>window.scrollY>50);
    await page.evaluate(()=>window.scrollTo(0,0));
    await cdp.detach();
   }
   const railBox=await page.locator('.cloz-discovery-rail').boundingBox();
   await page.mouse.move(railBox.x+railBox.width/2,railBox.y+40);
   await page.mouse.wheel(0,450);
   await page.waitForFunction(()=>window.scrollY>100);
   await context.close();console.log(`PASS ${opts.width}px ${opts.dir}: geometry, responsive layout, arrows, keyboard, reduced motion, vertical wheel`);
  }
  let t=await setup();await t.page.mouse.move(1,700);
  await t.page.waitForFunction(()=>Math.abs(document.querySelector('.cloz-discovery-rail').scrollLeft)>100,{timeout:5000});
  await t.page.locator('.cloz-discovery-pause').click();
  assert.equal(await t.page.locator('[data-discovery-play-icon]').isVisible(),true,'paused state shows play icon');
  assert.equal(await t.page.locator('[data-discovery-pause-icon]').isVisible(),false);
  await t.page.waitForTimeout(500);const paused=await position(t.page);
  await t.page.mouse.move(1,700);await t.page.waitForTimeout(3300);
  assert.ok(Math.abs(await position(t.page)-paused)<2,'explicit pause holds');
  await t.page.locator('.cloz-discovery-pause').click();await t.page.locator('h1').click();await t.page.mouse.move(1,700);
  await t.page.waitForFunction(v=>Math.abs(document.querySelector('.cloz-discovery-rail').scrollLeft-v)>100,paused,{timeout:5000});
  await t.page.evaluate(()=>window.scrollTo(0,900));await t.page.waitForTimeout(500);const offscreen=await position(t.page);await t.page.waitForTimeout(3300);
  assert.ok(Math.abs(await position(t.page)-offscreen)<2,'offscreen pauses timer');
  await t.context.close();console.log('PASS autoplay cadence, explicit pause/resume and offscreen suspension');

  t=await setup();await t.page.mouse.move(1,700);
  await t.page.locator('.cloz-discovery-rail').focus();const focused=await position(t.page);await t.page.waitForTimeout(3300);
  assert.ok(Math.abs(await position(t.page)-focused)<2,'focus holds autoplay');
  await t.page.evaluate(()=>{const old=document.querySelector('.cloz-category-discovery');old.replaceWith(old.cloneNode(true));document.body.dispatchEvent(new CustomEvent('cloz_archive_updated'));});
  await t.page.locator('.cloz-discovery-next').click();
  await t.page.waitForFunction(()=>Math.abs(document.querySelector('.cloz-discovery-rail').scrollLeft)>100);
  await t.context.close();console.log('PASS focus suspension and AJAX reinitialization');
  assert.deepEqual(errors,[]);
 }finally{await browser.close()}
})().catch(e=>{console.error(e);process.exitCode=1});

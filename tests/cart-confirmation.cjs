const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {execFileSync} = require('node:child_process');
const {chromium} = require('playwright');
const root = path.resolve(__dirname, '..');
const markup = execFileSync(process.env.PHP_BINARY || 'php',[path.join(__dirname,'cart-confirmation-render.php'),'--html'],{encoding:'utf8'});
const source = fs.readFileSync(path.join(root,'bijan-child/assets/ajax-cart.js'),'utf8');
const css = fs.readFileSync(path.join(root,'bijan-child/assets/ajax-cart.css'),'utf8');
const jquery = fs.readFileSync(require.resolve('jquery'),'utf8');
const image='data:image/svg+xml;base64,'+Buffer.from('<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120"><rect width="120" height="120" fill="#e9f2ef"/><path d="M36 27 49 20Q60 27 71 20L84 27 105 46 87 61 78 53 78 105 42 105 42 53 33 61 15 46Z" fill="#86aaa4"/></svg>').toString('base64');
(async()=>{
 const browser=await chromium.launch({headless:true,...(process.env.CHROMIUM_PATH?{executablePath:process.env.CHROMIUM_PATH}:{})});
 const errors=[];
 const setup=async(width,height=800)=>{
  const context=await browser.newContext({viewport:{width,height},reducedMotion:'reduce'});
  const page=await context.newPage();page.on('pageerror',e=>errors.push(e.message));
  let requests=0, mode='success', withImage=true;
  await page.route('https://cart.test/**',route=>{
   if(route.request().url().includes('wc-ajax')) {
    requests++;
    const data=mode==='error'?{success:false,data:{message:'این محصول در حال حاضر موجود نیست. لطفاً محصول دیگری انتخاب کنید.'}}:{success:true,data:{fragments:{'.cart-count':'<span class="cart-count">1</span>'},cart_hash:'hash',item:{name:'پیراهن مردانه آستین بلند مدل کلاسیک با پارچهٔ پنبه‌ای و طرح چهارخانه',image:withImage?image:'',quantity:2,meta:'رنگ: سبز | سایز: بزرگ'}}};
    return route.fulfill({contentType:'application/json',body:JSON.stringify(data)});
   }
   return route.fulfill({contentType:'text/html; charset=utf-8',body:`<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><style>body{font-family:Arial;background:#f3f7f6;margin:0;padding:24px}button{font:inherit}.product-area{height:1100px}.cloz-cart-toast-region{inset:auto 12px 90px 12px;width:auto;transform:translateY(40px)}${css}</style></head><body><main><h1>صفحه محصول</h1><div class="product-area"><form class="cart"><input type="hidden" name="add-to-cart" value="12"><input name="quantity" type="number" value="2"><button class="single_add_to_cart_button" name="add-to-cart" value="12">افزودن به سبد خرید</button></form><button class="cloz-ajax-add-to-cart" data-product_id="13">افزودن از فهرست</button><span class="cart-count">0</span></div></main>${markup}</body></html>`});
  });
  await page.goto('https://cart.test/product/');
  await page.addScriptTag({content:jquery});
  await page.evaluate(()=>{window.ClozAjaxCart={endpoint:'https://cart.test/?wc-ajax=cloz_add_to_cart',cartUrl:'https://cart.test/cart/',timeout:100,requestTimeout:20000,successTitle:'به سبد خرید اضافه شد',errorTitle:'افزودن به سبد خرید انجام نشد',viewCart:'مشاهده سبد خرید',working:'در حال افزودن',genericError:'خطا'};});
  await page.addScriptTag({content:source});
  return {page,context,get requests(){return requests},error(){mode='error'},success(){mode='success'},noImage(){withImage=false}};
 };
 try {
  for(const width of [320,390,768,1280]) {
   const t=await setup(width);
   assert.equal(await t.page.locator('#cloz-cart-toast').isVisible(),false);
   await t.page.locator('.single_add_to_cart_button').click();
   await t.page.waitForFunction(()=>document.querySelector('#cloz-cart-toast').classList.contains('is-visible'));
   await t.page.waitForFunction(()=>!document.querySelector('.single_add_to_cart_button').disabled);
   const boxes=await t.page.evaluate(()=>{
    const rect=s=>{const r=document.querySelector(s).getBoundingClientRect();return {x:r.x,y:r.y,right:r.right,bottom:r.bottom,width:r.width,height:r.height}};
    return {toast:rect('#cloz-cart-toast'),close:rect('.cloz-cart-toast__close'),title:rect('.cloz-cart-toast__title'),details:rect('.cloz-cart-toast__details'),action:rect('.cloz-cart-toast__action'),viewport:{width:innerWidth,height:innerHeight}};
   });
   assert.ok(boxes.close.right<boxes.title.x+1,'RTL close is separate at the top left');
   assert.ok(boxes.details.y>=boxes.close.bottom,'details are below header');
   assert.ok(boxes.action.y>=boxes.details.bottom,'cart action is below product details');
   assert.ok(boxes.action.width>boxes.toast.width-44,'cart action uses full content width');
   assert.ok(boxes.toast.x>=15 && boxes.toast.right<=width-15,'confirmation fits viewport');
   assert.ok(Math.abs(boxes.toast.y+boxes.toast.height/2-boxes.viewport.height/2)<2,'confirmation stays centered despite old positioning styles');
   assert.equal(t.requests,1,'single product submit sends one request');
   assert.equal(await t.page.locator('.cart-count').textContent(),'1','cart fragments update');
   assert.equal(await t.page.locator('.cloz-cart-toast__progress').count(),0);
   await t.page.clock.install();await t.page.clock.fastForward(10000);
   assert.equal(await t.page.locator('#cloz-cart-toast').isVisible(),true,'confirmation remains open beyond old timeout');
   if(width===390)await t.page.screenshot({path:'/tmp/cart-confirmation-mobile.png'});
   if(width===1280)await t.page.screenshot({path:'/tmp/cart-confirmation-desktop.png'});
   await t.page.locator('.cloz-cart-toast__close').click();
   assert.equal(await t.page.locator('#cloz-cart-toast').isVisible(),false,'cross explicitly closes');
   assert.equal(await t.page.locator('.single_add_to_cart_button').evaluate(e=>e===document.activeElement),true,'focus returns to add button');
   await t.page.locator('.cloz-ajax-add-to-cart').click();await t.page.waitForFunction(()=>!document.querySelector('#cloz-cart-toast').hidden);
   assert.equal(t.requests,2,'loop buttons reopen confirmation');
   await t.page.keyboard.press('Escape');assert.equal(await t.page.locator('#cloz-cart-toast').isVisible(),false);
   await t.context.close();console.log(`PASS ${width}px: layout, centering, manual dismissal, persistence, focus, single/loop AJAX and cart fragments`);
  }
  const t=await setup(390,400);t.error();
  await t.page.locator('.single_add_to_cart_button').click();await t.page.waitForFunction(()=>document.querySelector('#cloz-cart-toast').classList.contains('is-error'));
  assert.equal(await t.page.locator('.cloz-cart-toast__action').isVisible(),false);
  assert.equal(await t.page.locator('.cloz-cart-toast__visual').isVisible(),false);
  await t.page.clock.install();await t.page.clock.fastForward(10000);assert.equal(await t.page.locator('#cloz-cart-toast').isVisible(),true,'error remains open');
  await t.page.locator('.cloz-cart-toast__close').click();t.success();t.noImage();
  await t.page.locator('.single_add_to_cart_button').click();await t.page.waitForFunction(()=>!document.querySelector('#cloz-cart-toast').hidden&&!document.querySelector('#cloz-cart-toast').classList.contains('is-error'));
  assert.equal(await t.page.locator('.cloz-cart-toast__image').getAttribute('src'),null,'missing image does not request the document');
  assert.equal(await t.page.locator('.cloz-cart-toast__action').isVisible(),true);
  assert.equal(await t.page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),true);
  await t.context.close();console.log('PASS error persistence, short viewport, image fallback and recovery');
  assert.deepEqual(errors,[]);
 }finally{await browser.close()}
})().catch(error=>{console.error(error);process.exitCode=1});

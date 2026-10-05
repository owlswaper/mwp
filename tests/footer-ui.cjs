const fs=require('node:fs'), path=require('node:path'), assert=require('node:assert/strict');
const {execFileSync}=require('node:child_process');
const {chromium}=require('playwright');
const root=path.resolve(__dirname,'..');
const menus=execFileSync(process.env.PHP_BINARY||'php',[path.join(__dirname,'footer-render.php'),'--html'],{encoding:'utf8'});
const css=fs.readFileSync(path.join(root,'bijan-child/assets/footer.css'),'utf8');
const parent=fs.readFileSync(path.join(root,'bijan/assets/css/style.min.css'),'utf8');
const child=fs.readFileSync(path.join(root,'bijan-child/style.css'),'utf8');
const iconFont=fs.readFileSync(path.join(root,'bijan/assets/fonts/iconly.woff2')).toString('base64');
const uiFont=fs.readFileSync(path.join(root,'bijan/assets/fonts/iranyekanxfanum-regular.woff2')).toString('base64');
const icons=fs.readFileSync(path.join(root,'bijan/assets/css/iconly.min.css'),'utf8').replace(/url\([^)]*\)/g,`url(data:font/woff2;base64,${iconFont})`);
const fonts=`@font-face{font-family:FooterUI;src:url(data:font/woff2;base64,${uiFont}) format('woff2');font-display:swap}`;
(async()=>{
 const browser=await chromium.launch({headless:true,...(process.env.CHROMIUM_PATH?{executablePath:process.env.CHROMIUM_PATH}:{})});
 try {
 for(const width of [320,390,768,1024,1440]){
  const context=await browser.newContext({viewport:{width,height:1000},reducedMotion:'reduce'});
  const page=await context.newPage();
  await page.setContent(`<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>:root{--primary-100:#008480;--primary-200:#008480;--text-400:#74757c;--text-300:#999;--text-200:#777;--text-100:#333;--line-height:2}${parent}${child}${icons}${fonts}body{font-family:FooterUI,Arial;background:#fff;margin:0}.page-width{max-width:1320px;margin-inline:auto;padding-inline:20px;box-sizing:border-box}.fixture-newsletter{height:100px}#newsletter-wrap{position:static!important;transform:none!important;margin-top:0!important;border-radius:20px!important;padding:20px!important;grid-template-columns:1fr!important;inset:auto!important;margin-bottom:30px}#footer{padding-top:40px;margin-top:0} ${css}</style></head><body class="${width>1200?'desktop':'mobile'}"><footer id="site-footer" class="site-footer"><div class="page-width" id="footer"><div id="newsletter-wrap"><div id="newsletter-icon-wrap" aria-hidden="true">✉</div><div id="newsletter-texts"><div id="newsletter-title">خبرنامهٔ کلوز</div><div id="newsletter-subtitle">از پیشنهادها و محصولات تازه باخبر شوید</div></div><div id="newsletter-forms-wrap"><form><input type="email" aria-label="ایمیل" placeholder="آدرس ایمیل شما"><button type="submit">عضویت</button></form></div></div><div class="footer-content" id="top-footer"><div class="footer-menus" style="--menus-count:2">${menus}</div><div id="footer-about-wrap"><div id="footer-about"><p>کلوز، همراه انتخاب‌های خاص شما در دنیای پیرسینگ و اکسسوری. <a href="https://footer.test/about/">دربارهٔ فروشگاه</a></p></div><div id="footer-org-items-wrap"><div style="background:#fff;color:#334844;border-radius:12px;padding:14px;text-align:center">نماد اعتماد</div></div></div><div id="footer-more-info-wrap"><div id="footer-more-info-inner"><div id="footer-more-info-top"><div id="footer-more-info-title">پشتیبانی کلوز</div><div id="footer-more-info-subtitle" class="footer-more-info-subtitle">برای راهنمایی خرید و پیگیری سفارش با ما در تماس باشید.</div></div><div id="footer-more-info-contact-wrap"><div id="footer-more-info-phones" class="footer-more-info-phones-just_first"><a class="footer-phone" href="tel:09981687867">09981687867</a></div><div id="footer-more-info-contact-subtitle" class="footer-more-info-subtitle"><p>هر روز حتی جمعه‌ها؛ پاسخ‌گویی در ساعات کاری.</p><p><a href="https://footer.test/chat/">گفت‌وگوی آنلاین</a> · <a href="https://footer.test/telegram/">تلگرام</a> · <a href="https://footer.test/bale/">بله</a></p></div></div></div></div></div><div class="footer-content" id="footer-market-buttons"><a class="market-button" href="https://footer.test/app/" style="padding:14px"><div class="market-button-top-text">دریافت برنامه</div><div class="market-button-text">کلوز</div></a></div><div class="footer-content" id="footer-copyright-wrap"><div id="footer-copyright"><p>تمام حقوق این وب‌سایت متعلق به فروشگاه کلوز است.</p></div></div></div></footer></body></html>`);
  await page.evaluate(()=>document.fonts.ready);
  const geometry=await page.evaluate(()=>{
   const links=[...document.querySelectorAll('.footer-menu-wrap li a')];
   return {overflow:document.documentElement.scrollWidth>innerWidth, links:links.map(e=>{const r=e.getBoundingClientRect();return {x:r.x,right:r.right,height:r.height,text:e.textContent}}),groups:[...document.querySelectorAll('.footer-menu-wrap')].map(e=>{const r=e.getBoundingClientRect();return {x:r.x,right:r.right,y:r.y,width:r.width}})};
  });
  assert.equal(geometry.overflow,false,`${width}px has no horizontal overflow`);
  assert.ok(geometry.links.every(e=>e.height>=44 && e.x>=0 && e.right<=width),`${width}px touch targets fit`);
  assert.equal(await page.locator('.footer-menu-wrap a').count(),8);
  await page.locator('.footer-menu-wrap a').first().focus();
  assert.equal(await page.locator('.footer-menu-wrap a').first().evaluate(e=>getComputedStyle(e).outlineStyle),'solid','keyboard focus is visible');
  const contrast=await page.evaluate(()=>{
   const parse=c=>{const n=c.match(/[\d.]+/g).map(Number);return [...n.slice(0,3),n[3]??1]};
   const composite=(fg,bg)=>[...fg.slice(0,3).map((c,i)=>c*fg[3]+bg[i]*(1-fg[3])),1];
   const lum=c=>c.slice(0,3).map(v=>v/255).reduce((s,v,i)=>s+(v<=.04045?v/12.92:((v+.055)/1.055)**2.4)*[.2126,.7152,.0722][i],0);
   const ratio=(a,b)=>{const x=lum(a),y=lum(b);return (Math.max(x,y)+.05)/(Math.min(x,y)+.05)};
   const result=[];
   const selectors=['.footer-section-title-text','.footer-menu-wrap li a','#footer-about p','#footer-about a','#footer-more-info-title','#footer-more-info-subtitle','#footer-more-info-contact-subtitle p','#footer-more-info-contact-subtitle a','.footer-phone','#footer-copyright p','.market-button-top-text','.market-button-text','#newsletter-title','#newsletter-subtitle','#newsletter-forms-wrap button','#newsletter-forms-wrap input'];
   for(const selector of selectors) for(const el of document.querySelectorAll(selector)){
    let stack=[],node=el;
    while(node){stack.unshift(getComputedStyle(node));node=node.parentElement}
    let backgrounds=[[255,255,255,1]];
    for(const style of stack){
     if(style.backgroundImage.includes('gradient')) backgrounds=[[253,95,27,1],[255,152,0,1]];
     const color=parse(style.backgroundColor);backgrounds=backgrounds.map(bg=>composite(color,bg));
    }
    const fg=parse(getComputedStyle(el).color);
    let value=Math.min(...backgrounds.map(bg=>ratio(composite(fg,bg),bg)));
    result.push({selector,ratio:value});
    if(el.tagName==='INPUT'){
     const color=parse(getComputedStyle(el,'::placeholder').color);
     result.push({selector:'placeholder',ratio:Math.min(...backgrounds.map(bg=>ratio(color,bg)))});
    }
   }
   return result;
  });
  assert.ok(contrast.every(item=>item.ratio>=4.5),JSON.stringify(contrast.filter(item=>item.ratio<4.5)));
  await page.locator('.footer-menu-wrap a').first().hover();
  assert.equal(await page.locator('.footer-menu-wrap a').first().evaluate(e=>getComputedStyle(e).color),'rgb(255, 255, 255)');
  if(width===390)await page.screenshot({path:'/tmp/footer-mobile.png',fullPage:true});
  if(width===1440)await page.screenshot({path:'/tmp/footer-desktop.png',fullPage:true});
  console.log(`PASS ${width}px layout, touch targets, native links, keyboard/hover; minimum text contrast ${Math.min(...contrast.map(i=>i.ratio)).toFixed(2)}:1`);
  await context.close();
 }
 }finally{await browser.close()}
})().catch(e=>{console.error(e);process.exitCode=1});

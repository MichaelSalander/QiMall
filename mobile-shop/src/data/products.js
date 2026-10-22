import {staticAsset} from './assets.js'
const catalog=[
{id:1,name:'植清素',cat:'日常保養',price:4000,unit:'盒',desc:'雙重植物酵素配方',image:'/static/products/2046747.jpg'},
{id:2,name:'膜動王',cat:'日常保養',price:4000,unit:'瓶',desc:'日常保養',image:''},
{id:3,name:'ALPHA PLUS 能量貼',cat:'科技生活',price:2240,unit:'盒',desc:'日常生活應用，隨心搭配',image:'/static/products/2046748_0.jpg',extra:['/static/products/2046750_0.jpg','/static/products/2046749_0.jpg']},
{id:4,name:'零酒精葡萄酒',cat:'品味飲食',price:1700,unit:'瓶',desc:'ROYAL VITIS・米隆莊園',image:'/static/products/2046751.jpg'},
{id:5,name:'輕纖效速軟糖',cat:'日常保養',price:3000,unit:'盒',desc:'日常輕鬆補充・20顆入',image:'/static/products/2046752.jpg'},
{id:6,name:'飛瀑沐浴淨水器組',cat:'居家沐浴',price:6600,unit:'組',desc:'沐浴淨水器・加濾心一顆',image:'/static/products/2046753.jpg'}]
export const products=catalog.map(p=>({...p,image:p.image?staticAsset(p.image):'',extra:(p.extra||[]).map(staticAsset)}))
export const after=(p,rate)=>Math.round(p.price*(1-rate/100))
export const money=n=>Number(n).toLocaleString('en-US')


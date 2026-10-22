<template>
<view class="app">
 <view class="top"><view class="brand-group"><view class="brand-mark"><image src="/static/ui/leaf.png"/></view><view><text class="brand">幸福選物</text><text class="english">WELLNESS & LIVING</text></view></view><text class="header-label">會員專屬</text></view>
 <template v-if="true">
  <view class="hero"><view class="hero-kicker"><image src="/static/ui/gift.png"/><text>幸福生活・會員禮遇</text></view><text class="headline">為你的日常，<text style="display:block">多選一份美好。</text></text><text class="description">從日常保養到居家生活，<text style="display:block">讓每一份積分，都用得值得。</text></text><button class="hero-cta" @tap="showCollection">探索精選商品<image src="/static/ui/arrow.png"/></button><view class="hero-benefit"><text>積分最高折抵</text><text class="benefit-number">50%</text><text>幸福VIP專屬</text></view></view>
  <view class="quick-links"><button class="quick-link" v-for="entry in quickEntries" :key="entry.label" @tap="selectCategory(entry.cat)"><view class="quick-icon"><image :src="staticAsset('/static/ui/'+entry.icon+'.png')"/></view><text>{{ entry.label }}</text></button></view>
  <view id="collection" class="row"><text class="section-label">為你精選</text><text class="hint">{{ filtered.length }} 件商品</text></view><text class="collection-subtitle">好好照顧自己，也好好享受生活。</text>
  <scroll-view :show-scrollbar="false" scroll-x class="category-scroll"><view class="categories"><button v-for="c in categories" :key="c" :class="{active:category===c}" @tap="category=c">{{ c }}</button></view></scroll-view>
  <view v-for="p in filtered" :key="p.id" class="product"><view class="product-visual" @tap="open(p)"><image v-if="p.image" class="product-image" :src="p.image" mode="aspectFill"/><view v-else class="placeholder"><text>膜動王</text></view><text class="image-tag">{{ p.cat }}</text><view class="visual-open"><text>商品介紹</text><image src="/static/ui/arrow.png"/></view></view><view class="product-body"><view @tap="open(p)"><text class="product-name">{{ p.name }}</text><text class="description">{{ p.desc }}</text></view><PricePanel :product="p"/><view class="product-footer"><text>積分折抵・會員專屬</text><button class="primary" @tap="add(p)">加入購物袋</button></view></view></view>
 </template>
 <view v-if="tab==='bag'" class="sheet-backdrop" @tap.self="tab='shop'"><view class="store-sheet"><view class="sheet-header"><text>購物袋 · {{ count }} 件</text><button class="sheet-close" @tap="tab='shop'">關閉</button></view><scroll-view scroll-y class="sheet-content"><view v-if="!cart.length" class="empty"><text>購物袋還沒有商品</text><button class="primary" @tap="tab='shop'">繼續選購</button></view><view v-for="p in cartItems" :key="p.id" class="panel"><text class="product-name">{{ p.name }}</text><text class="hint">原價 NT$ {{ money(p.price) }} / {{ p.unit }}</text><view class="quantity"><button @tap="change(p.id,-1)">−</button><text>{{ p.qty }}</text><button @tap="change(p.id,1)">＋</button><button class="remove" @tap="remove(p.id)">移除</button></view></view><view v-if="cart.length" class="panel summary"><view class="row"><text>原價合計</text><text>NT$ {{ money(total) }}</text></view><view class="row"><view><text>幸福會員</text><text class="hint">最高折抵30%</text></view><text>折後 NT$ {{ money(memberTotal) }}</text></view><view class="row"><view><text>幸福VIP</text><text class="hint">最高可抵50%</text></view><text>折後 NT$ {{ money(vipTotal) }}</text></view><text class="hint">折後價格需搭配足額積分。</text><button class="primary checkout-disabled" disabled>暫未開放結帳</button></view><view class="sheet-end"/></scroll-view></view></view>
 <view v-if="selectedProduct" class="sheet-backdrop" @tap.self="selectedProduct=null"><view class="store-sheet"><view class="sheet-header"><text>商品詳情</text><button class="sheet-close" @tap="selectedProduct=null">關閉</button></view><scroll-view scroll-y class="sheet-content"><view class="sheet-product"><text class="hint">{{ selectedProduct.cat }}</text><text class="product-name">{{ selectedProduct.name }}</text><text class="description">{{ selectedProduct.desc }}</text><PricePanel :product="selectedProduct"/><button class="primary" @tap="add(selectedProduct)">加入購物袋</button><text class="section-label source-heading">商品介紹</text><image v-for="src in detailImages" :key="src" :src="src" mode="widthFix" class="sheet-image" @tap="preview(src)"/></view><view class="sheet-end"/></scroll-view></view></view>
 <view class="disclaimer">折後價格需搭配足額積分。<text>幸福選物</text></view>
 <view v-if="tab==='member'" class="sheet-backdrop" @tap.self="tab='shop'"><view class="store-sheet"><view class="sheet-header"><text>會員中心</text><button class="sheet-close" @tap="tab='shop'">關閉</button></view><scroll-view scroll-y class="sheet-content"><MemberCenter/><view class="sheet-end"/></scroll-view></view></view>
 <BottomNav v-model="tab" :count="count"/>
</view>
</template>
<script setup>
import {staticAsset} from '../../data/assets.js'
import { computed,ref,watch } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import BottomNav from '../../components/BottomNav.vue'
import PricePanel from '../../components/PricePanel.vue'
import MemberCenter from '../../components/MemberCenter.vue'
import { products,after,money } from '../../data/products.js'
const quickEntries=[{label:'全部選物',cat:'全部商品',icon:'grid'},{label:'日常保養',cat:'日常保養',icon:'user'},{label:'品味飲食',cat:'品味飲食',icon:'gift'},{label:'居家沐浴',cat:'居家沐浴',icon:'drop'}]
function showCollection(){uni.pageScrollTo({selector:'#collection',duration:250})}
function selectCategory(value){category.value=value;showCollection()}
const categories=['全部商品','日常保養','科技生活','品味飲食','居家沐浴']
const category=ref('全部商品'),tab=ref('shop'),cart=ref([]),selectedProduct=ref(null)
onShow(()=>{let saved=uni.getStorageSync('selection-cart');cart.value=Array.isArray(saved)?saved.filter(i=>products.some(p=>p.id===i.id)&&Number.isInteger(i.qty)&&i.qty>0):[]})
watch(cart,value=>uni.setStorageSync('selection-cart',JSON.parse(JSON.stringify(value))),{deep:true})
const filtered=computed(()=>products.filter(p=>category.value==='全部商品'||p.cat===category.value))
const cartItems=computed(()=>cart.value.map(i=>({...products.find(p=>p.id===i.id),qty:i.qty})))
const count=computed(()=>cart.value.reduce((s,i)=>s+i.qty,0))
const total=computed(()=>cartItems.value.reduce((s,p)=>s+p.price*p.qty,0))
const memberTotal=computed(()=>cartItems.value.reduce((s,p)=>s+after(p,30)*p.qty,0))
const vipTotal=computed(()=>cartItems.value.reduce((s,p)=>s+after(p,50)*p.qty,0))
const detailImages=computed(()=>selectedProduct.value?[selectedProduct.value.image,...(selectedProduct.value.extra||[])].filter(Boolean):[])
function add(p){const item=cart.value.find(i=>i.id===p.id);if(item)item.qty++;else cart.value.push({id:p.id,qty:1});uni.showToast({title:'已加入購物袋'})}
function remove(id){cart.value=cart.value.filter(i=>i.id!==id)}
function change(id,delta){const i=cart.value.find(i=>i.id===id);if(i){i.qty+=delta;if(i.qty<=0)remove(id)}}
function open(p){selectedProduct.value=p}
function preview(current){uni.previewImage({current,urls:detailImages.value})}
</script>
<style src="../../styles/professional.css"></style>






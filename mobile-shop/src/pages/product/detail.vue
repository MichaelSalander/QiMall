<template><view class="detail"><template v-if="product"><image v-if="product.image" class="detail-cover" :src="product.image" mode="aspectFill" @tap="preview(product.image)"/><view class="detail-intro"><text class="hint">{{ product.cat }}</text><text class="title">{{ product.name }}</text><text class="description">{{ product.desc }}</text><PricePanel :product="product"/><button @tap="add">加入購物袋 ＋</button><text class="hint">折後價按最高比例試算，需搭配足額積分。</text></view><text class="heading">商品資料</text><text class="hint">以下為提供的原始商品素材，點圖片可放大。</text><image v-for="src in images" :key="src" :src="src" class="source-image" mode="widthFix" @tap="preview(src)"/><text v-if="!images.length" class="hint">商品圖片與詳細規格待補。</text></template><template v-else><text class="title">找不到此商品</text><button @tap="back">返回商城</button></template></view></template>
<script setup>
import {ref,computed} from 'vue'
import {onLoad} from '@dcloudio/uni-app'
import {products} from '../../data/products.js'
import PricePanel from '../../components/PricePanel.vue'
const product=ref(null)
onLoad(options=>{product.value=products.find(p=>p.id===Number(options.id))||null})
const images=computed(()=>product.value?[product.value.image,...(product.value.extra||[])].filter(Boolean):[])
function add(){let cart=uni.getStorageSync('selection-cart');if(!Array.isArray(cart))cart=[];const item=cart.find(i=>i.id===product.value.id);if(item)item.qty++;else cart.push({id:product.value.id,qty:1});uni.setStorageSync('selection-cart',cart);uni.showToast({title:'已加入購物袋'})}
function preview(current){uni.previewImage({current,urls:images.value})}
function back(){uni.reLaunch({url:'/pages/index/index'})}
</script>
<style src="../../styles/professional.css"></style>

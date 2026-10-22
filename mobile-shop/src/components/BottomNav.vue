<template><view class="nav-placeholder" aria-hidden="true"/><view class="app-nav"><button v-for="item in items" :key="item.key" :class="['app-nav-item',{active:modelValue===item.key}]" @tap="$emit('update:modelValue',item.key)"><view class="nav-icon-wrap"><image :src="staticAsset('/static/ui/'+item.icon+'.png')" class="app-nav-icon"/><text v-if="item.key==='bag'&&count" class="nav-badge">{{ count }}</text></view><text>{{ item.label }}</text><view v-if="modelValue===item.key" class="nav-dot"/></button></view></template>
<script setup>
import {staticAsset} from '../data/assets.js'
defineProps({modelValue:String,count:Number})
defineEmits(['update:modelValue'])
const items=[{key:'shop',label:'幸福選物',icon:'home'},{key:'bag',label:'購物袋',icon:'bag'},{key:'member',label:'會員中心',icon:'user'}]
</script>
<style scoped>.nav-placeholder{height:calc(78px + env(safe-area-inset-bottom));width:100%}.app-nav{box-sizing:border-box;position:fixed;left:50%;transform:translateX(-50%);bottom:0;width:100%;max-width:480px;z-index:20;display:flex;border-top:1px solid #e7efec;background:rgba(255,255,255,.98);padding:10px 10px calc(10px + env(safe-area-inset-bottom));box-shadow:0 -5px 24px #1b6b5f08}.app-nav-item{position:relative;flex:1;background:transparent;color:#687f7a;font-size:14px;line-height:1.7;padding:0;margin:0;border-radius:0}.app-nav-item.active{color:#009985;font-weight:600}.nav-icon-wrap{position:relative;width:40px;margin:auto auto 3px;height:30px}.app-nav-icon{width:25px;height:25px;opacity:.6}.active .app-nav-icon{opacity:1}.active .nav-icon-wrap{background:#e8f9f4;border-radius:10px}.nav-badge{position:absolute;top:-4px;right:-4px;min-width:19px;height:19px;line-height:19px;font-size:11px;border:2px solid white;background:#08ad97;color:white;border-radius:12px}.nav-dot{position:absolute;top:-10px;left:calc(50% - 12px);height:3px;width:24px;border-radius:3px;background:#08ad97}.app-nav-item::after{display:none!important;border:0!important}</style>





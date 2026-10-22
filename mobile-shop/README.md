# 幸福選物 uni-app 展示版

原始碼：本目錄。展示用靜態檔：../docs/mobile-shop。

公開網址：https://MichaelSalander.github.io/QiMall/mobile-shop/

展示範圍包含商品分類、商品詳情、購物袋、會員中心、積分與現金餘額欄位。尚未接真實會員資料與付款，餘額不顯示虛構數字，結帳與升級入口維持停用。

## 重建展示版

Node.js 22、pnpm 11.25.0。

    pnpm install --frozen-lockfile
    node tools/build-preview.cjs

將 dist/build/h5 的內容更新至儲存庫 docs/mobile-shop，推送 main 後由 GitHub Pages 更新。一般根目錄部署可使用 node node_modules/@dcloudio/vite-plugin-uni/bin/uni.js build。

第三方參考來源及授權位於 third-party/。本目錄與原 QiMall PHP 專案互相獨立。

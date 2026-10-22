const fs = require('fs')
const path = require('path')
const { spawnSync } = require('child_process')
const root = path.resolve(__dirname, '..')
const manifestPath = path.join(root, 'src/manifest.json')
const original = fs.readFileSync(manifestPath, 'utf8')
const base = process.env.SHOP_PREVIEW_BASE || '/QiMall/mobile-shop/'
if (!/^\/[a-zA-Z0-9/_-]+\/$/.test(base)) throw new Error('Invalid preview base')
try {
  const manifest = JSON.parse(original.replace(/^\uFEFF/, ''))
  manifest.h5.router.base = base
  manifest.h5.router.mode = 'hash'
  fs.writeFileSync(manifestPath, JSON.stringify(manifest, null, 2))
  const result = spawnSync(process.execPath, ['node_modules/@dcloudio/vite-plugin-uni/bin/uni.js', 'build'], { cwd: root, stdio: 'inherit' })
  if (result.error) throw result.error
  if (result.status !== 0) process.exitCode = result.status || 1
} finally {
  fs.writeFileSync(manifestPath, original)
}

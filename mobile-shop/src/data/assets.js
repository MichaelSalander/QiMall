// Keep static assets valid both under a hosted H5 subdirectory and on mini programs.
export function staticAsset(path) {
  let base = '/'
  // #ifdef H5
  base = import.meta.env.BASE_URL || '/'
  // #endif
  return base.replace(/\/?$/, '/') + path.replace(/^\/+/, '')
}

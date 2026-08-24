# WebP Lab — JPG to WebP Converter

A small **browser tool** that converts JPG/PNG images to WebP. No server, no install — open `index.html` and convert.

## Run it

1. Open `tools/jpg-to-webp/index.html` in Chrome, Edge, or Firefox.
2. Drop images (or click to browse).
3. Adjust quality.
4. Click **Convert to WebP** → download.

## How you build a tool like this

| Layer | Job |
| --- | --- |
| **HTML** | UI: file input, drop zone, quality slider, buttons |
| **CSS** | Layout and look |
| **JavaScript** | Read file → draw on canvas → encode WebP → download |

Core idea in code:

```js
const bitmap = await createImageBitmap(file);
const canvas = document.createElement("canvas");
canvas.width = bitmap.width;
canvas.height = bitmap.height;
canvas.getContext("2d").drawImage(bitmap, 0, 0);

canvas.toBlob((blob) => {
  // turn blob into a download link
}, "image/webp", 0.8);
```

## Other ways to build the same tool

1. **Browser app (this folder)** — easiest for learning; private (files stay on your machine).
2. **Node.js CLI** — use a library like `sharp` for batch/scripts.
3. **Python** — use Pillow (`pip install pillow`) for scripts/automation.

Start with the browser version. When you need bulk folders or automation, move to Node or Python.

## Stretch ideas

- Resize before converting (max width)
- Drag-to-reorder queue
- Zip all outputs
- Dark/light theme toggle

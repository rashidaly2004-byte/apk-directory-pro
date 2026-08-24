/**
 * JPG/PNG → WebP converter (browser-only)
 *
 * Flow:
 * 1. User picks files
 * 2. FileReader / createImageBitmap loads each image
 * 3. Draw onto <canvas>
 * 4. canvas.toBlob('image/webp', quality) encodes WebP
 * 5. Create a download link from the blob
 */

const fileInput = document.getElementById("file-input");
const dropzone = document.getElementById("dropzone");
const qualityInput = document.getElementById("quality");
const qualityValue = document.getElementById("quality-value");
const convertBtn = document.getElementById("convert-btn");
const clearBtn = document.getElementById("clear-btn");
const statusEl = document.getElementById("status");
const resultsEl = document.getElementById("results");

/** @type {File[]} */
let selectedFiles = [];

qualityInput.addEventListener("input", () => {
  qualityValue.textContent = qualityInput.value;
});

fileInput.addEventListener("change", () => {
  addFiles(fileInput.files);
  fileInput.value = "";
});

["dragenter", "dragover"].forEach((eventName) => {
  dropzone.addEventListener(eventName, (event) => {
    event.preventDefault();
    dropzone.classList.add("dragover");
  });
});

["dragleave", "drop"].forEach((eventName) => {
  dropzone.addEventListener(eventName, (event) => {
    event.preventDefault();
    dropzone.classList.remove("dragover");
  });
});

dropzone.addEventListener("drop", (event) => {
  addFiles(event.dataTransfer?.files);
});

convertBtn.addEventListener("click", () => {
  void convertAll();
});

clearBtn.addEventListener("click", clearAll);

/**
 * @param {FileList | File[] | null | undefined} fileList
 */
function addFiles(fileList) {
  if (!fileList?.length) return;

  const incoming = [...fileList].filter((file) =>
    /image\/(jpeg|jpg|png|webp)/i.test(file.type)
  );

  if (!incoming.length) {
    setStatus("Please choose JPG or PNG images.");
    return;
  }

  selectedFiles = [...selectedFiles, ...incoming];
  updateControls();
  setStatus(
    `${selectedFiles.length} file${selectedFiles.length === 1 ? "" : "s"} ready. Hit Convert.`
  );
}

function updateControls() {
  const hasFiles = selectedFiles.length > 0;
  convertBtn.disabled = !hasFiles;
  clearBtn.disabled = !hasFiles;
}

function clearAll() {
  selectedFiles = [];
  resultsEl.innerHTML = "";
  resultsEl.hidden = true;
  updateControls();
  setStatus("Cleared. Drop new images to convert.");
}

/**
 * @param {string} message
 */
function setStatus(message) {
  statusEl.textContent = message;
}

/**
 * @param {number} bytes
 */
function formatBytes(bytes) {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
}

/**
 * Convert one image File to a WebP Blob using canvas.
 * @param {File} file
 * @param {number} quality 0–1
 * @returns {Promise<{ blob: Blob, width: number, height: number, previewUrl: string }>}
 */
async function fileToWebp(file, quality) {
  const bitmap = await createImageBitmap(file);
  const canvas = document.createElement("canvas");
  canvas.width = bitmap.width;
  canvas.height = bitmap.height;

  const ctx = canvas.getContext("2d");
  if (!ctx) throw new Error("Canvas not supported in this browser.");

  ctx.drawImage(bitmap, 0, 0);
  bitmap.close();

  const blob = await new Promise((resolve, reject) => {
    canvas.toBlob(
      (result) => {
        if (result) resolve(result);
        else reject(new Error("WebP encoding failed. Try Chrome/Edge/Firefox."));
      },
      "image/webp",
      quality
    );
  });

  return {
    blob,
    width: canvas.width,
    height: canvas.height,
    previewUrl: URL.createObjectURL(blob),
  };
}

/**
 * @param {string} originalName
 */
function toWebpName(originalName) {
  return originalName.replace(/\.[^.]+$/, "") + ".webp";
}

async function convertAll() {
  if (!selectedFiles.length) return;

  const quality = Number(qualityInput.value) / 100;
  convertBtn.disabled = true;
  resultsEl.innerHTML = "";
  resultsEl.hidden = false;

  let done = 0;
  for (const file of selectedFiles) {
    setStatus(`Converting ${done + 1}/${selectedFiles.length}: ${file.name}`);

    try {
      const { blob, width, height, previewUrl } = await fileToWebp(file, quality);
      const saved = file.size - blob.size;
      const savedPct = file.size > 0 ? Math.round((saved / file.size) * 100) : 0;
      const downloadName = toWebpName(file.name);

      const item = document.createElement("li");
      item.innerHTML = `
        <img src="${previewUrl}" alt="Preview of ${downloadName}" width="88" height="66" />
        <div class="meta">
          <p class="name">${downloadName}</p>
          <p class="sizes">
            ${formatBytes(file.size)} → ${formatBytes(blob.size)}
            · ${width}×${height}
            ${saved > 0 ? `<span class="saved">· saved ${savedPct}%</span>` : ""}
          </p>
        </div>
        <a class="download" download="${downloadName}" href="${previewUrl}">Download</a>
      `;
      resultsEl.appendChild(item);
    } catch (error) {
      const item = document.createElement("li");
      item.innerHTML = `
        <div class="meta" style="grid-column: 1 / -1">
          <p class="name">${file.name}</p>
          <p class="sizes" style="color: var(--danger)">${
            error instanceof Error ? error.message : "Conversion failed"
          }</p>
        </div>
      `;
      resultsEl.appendChild(item);
    }

    done += 1;
  }

  setStatus(`Done. Converted ${done} file${done === 1 ? "" : "s"}.`);
  updateControls();
}

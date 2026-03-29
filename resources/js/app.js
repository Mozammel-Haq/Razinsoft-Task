import "./bootstrap";
/**
 * Product Gallery Manager — app.js
 * Handles: drag-drop upload, AJAX image remove, confirm modal, gallery lightbox
 */

let pgmSelectedFiles = [];

document.addEventListener("DOMContentLoaded", () => {
    initDropzone();
    initConfirmModal();
    initGalleryThumbs();
    initAjaxImageRemove();
});

/* ─────────────────────────────────────────
   DRAG & DROP UPLOAD ZONE
───────────────────────────────────────── */
function initDropzone() {
    const dropzone = document.getElementById("pgmDropzone");
    const fileInput = document.getElementById("pgmFileInput");
    const preview = document.getElementById("pgmPreviewGrid");

    if (!dropzone || !fileInput) return;

    // Click → trigger file picker
    dropzone.addEventListener("click", (e) => {
        if (e.target.tagName !== "INPUT") fileInput.click();
    });

    // Drag events
    ["dragenter", "dragover"].forEach((evt) => {
        dropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            dropzone.classList.add("is-over");
        });
    });

    ["dragleave", "drop"].forEach((evt) => {
        dropzone.addEventListener(evt, () =>
            dropzone.classList.remove("is-over"),
        );
    });

    dropzone.addEventListener("drop", (e) => {
        e.preventDefault();
        const files = e.dataTransfer.files;
        if (files.length) {
            addFiles(files, fileInput, preview);
        }
    });

    // Input change
    fileInput.addEventListener("change", () => {
        if (fileInput.files.length) {
            addFiles(fileInput.files, fileInput, preview);
        }
    });
}

/* Maintain selected files in memory + sync to input */
function syncSelectedFiles(input) {
    const dt = new DataTransfer();
    pgmSelectedFiles.forEach((file) => dt.items.add(file));
    input.files = dt.files;
}

function isDuplicateFile(file) {
    return pgmSelectedFiles.some(
        (existing) =>
            existing.name === file.name &&
            existing.size === file.size &&
            existing.lastModified === file.lastModified,
    );
}

function addFiles(files, input, preview) {
    const newFiles = [];

    Array.from(files).forEach((file) => {
        if (!isValidImage(file)) return;
        if (isDuplicateFile(file)) return;

        pgmSelectedFiles.push(file);
        newFiles.push(file);
    });

    if (!newFiles.length) return;

    syncSelectedFiles(input);
    renderPreviews(newFiles, preview, false);
}

function isValidImage(file) {
    const allowed = ["image/jpeg", "image/jpg", "image/png", "image/webp"];
    if (!allowed.includes(file.type)) {
        showInlineError(
            `"${file.name}" is not a valid image type (JPEG/PNG/WebP only).`,
        );
        return false;
    }
    if (file.size > 2 * 1024 * 1024) {
        showInlineError(`"${file.name}" exceeds the 2MB size limit.`);
        return false;
    }
    return true;
}

function renderPreviews(files, container, isSaved) {
    if (!container) return;

    Array.from(files).forEach((file) => {
        if (!isValidImage(file)) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            const item = buildPreviewItem(
                e.target.result,
                file.name,
                null,
                isSaved,
                file,
            );
            container.appendChild(item);
        };
        reader.readAsDataURL(file);
    });
}

function buildPreviewItem(src, name, imageId, isSaved, file) {
    const item = document.createElement("div");
    item.className = "pgm-preview-item" + (isSaved ? " is-saved" : "");
    if (imageId) item.dataset.imageId = imageId;
    if (!isSaved && file) {
        item.dataset.fileName = file.name;
        item.dataset.fileSize = file.size;
        item.dataset.fileLastModified = file.lastModified;
    }

    item.innerHTML = `
        <img src="${src}" alt="${name}" loading="lazy">
        <div class="pgm-preview-item__label">${name}</div>
        <button type="button" class="pgm-preview-item__remove" title="Remove image">
            <i class="ph ph-x"></i>
        </button>
    `;

    item.querySelector(".pgm-preview-item__remove").addEventListener(
        "click",
        () => {
            if (isSaved && imageId) {
                removeExistingImage(imageId, item);
            } else {
                removeTempImage(item);
            }
        },
    );

    return item;
}

function removeTempImage(itemEl) {
    if (!itemEl.dataset.fileName) {
        itemEl.remove();
        return;
    }

    pgmSelectedFiles = pgmSelectedFiles.filter(
        (f) =>
            !(
                f.name === itemEl.dataset.fileName &&
                f.size === Number(itemEl.dataset.fileSize) &&
                f.lastModified === Number(itemEl.dataset.fileLastModified)
            ),
    );

    const fileInput = document.getElementById("pgmFileInput");
    if (fileInput) {
        syncSelectedFiles(fileInput);
    }

    itemEl.remove();
}

/* ─────────────────────────────────────────
   AJAX — REMOVE EXISTING IMAGE (edit page)
───────────────────────────────────────── */
function initAjaxImageRemove() {
    // Render already-saved images in edit page preview grid
    const savedImages = document.querySelectorAll("[data-saved-image]");
    const previewGrid = document.getElementById("pgmPreviewGrid");

    if (!savedImages.length || !previewGrid) return;

    savedImages.forEach((img) => {
        const src = img.dataset.src;
        const id = img.dataset.savedImage;
        const name = img.dataset.name || "image";

        const item = buildPreviewItem(src, name, id, true);
        previewGrid.appendChild(item);
    });
}
function removeExistingImage(imageId, itemEl) {
    const productId = document.getElementById("pgmProductId")?.value;
    if (!productId) return;

    const routeTemplate =
        window.pgmRoutes?.imageDestroy ||
        "/products/__PRODUCT__/images/__IMAGE__";
    const url = routeTemplate
        .replace("__PRODUCT__", productId)
        .replace("__IMAGE__", imageId);

    const csrfToken = document.querySelector(
        'meta[name="csrf-token"]',
    )?.content;

    itemEl.style.opacity = "0.5";
    itemEl.style.pointerEvents = "none";

    fetch(url, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": csrfToken,
            Accept: "application/json",
        },
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                itemEl.style.transition = "all 0.25s ease";
                itemEl.style.transform = "scale(0.8)";
                itemEl.style.opacity = "0";
                setTimeout(() => itemEl.remove(), 250);
            } else {
                itemEl.style.opacity = "1";
                itemEl.style.pointerEvents = "auto";
                showInlineError(data.error || "Failed to remove image.");
            }
        })
        .catch(() => {
            itemEl.style.opacity = "1";
            itemEl.style.pointerEvents = "auto";
            showInlineError("Network error. Please try again.");
        });
}

/* ─────────────────────────────────────────
   CONFIRM DELETE MODAL
───────────────────────────────────────── */
function initConfirmModal() {
    const backdrop = document.getElementById("pgmDeleteModal");
    if (!backdrop) return;

    const confirmBtn = document.getElementById("pgmConfirmDelete");
    const cancelBtns = backdrop.querySelectorAll("[data-modal-close]");
    let pendingForm = null;

    const openModal = () => {
        backdrop.classList.remove("hidden");
        backdrop.classList.add("flex");
    };

    const closeModal = () => {
        backdrop.classList.remove("flex");
        backdrop.classList.add("hidden");
        pendingForm = null;
    };

    // Wire all delete-trigger buttons
    document.querySelectorAll("[data-delete-form]").forEach((btn) => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            const formId = btn.dataset.deleteForm;
            pendingForm = document.getElementById(formId);
            const name = btn.dataset.productName || "this product";

            backdrop.querySelector("#pgmDeleteTarget").textContent = name;
            openModal();
        });
    });

    confirmBtn?.addEventListener("click", () => {
        if (pendingForm) {
            pendingForm.submit();
        }
    });

    cancelBtns.forEach((btn) => btn.addEventListener("click", closeModal));

    backdrop.addEventListener("click", (e) => {
        if (e.target === backdrop) {
            closeModal();
        }
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            closeModal();
        }
    });
}

/* ─────────────────────────────────────────
   GALLERY THUMBNAIL SWITCHER (show page)
───────────────────────────────────────── */
function initGalleryThumbs() {
    const mainImg = document.getElementById("pgmGalleryMain");
    const thumbs = document.querySelectorAll(".pgm-gallery__thumb");

    if (!mainImg || !thumbs.length) return;

    thumbs.forEach((thumb) => {
        thumb.addEventListener("click", () => {
            const newSrc = thumb.dataset.src;

            // Smooth crossfade
            mainImg.style.opacity = "0";
            mainImg.style.transition = "opacity 0.2s ease";

            setTimeout(() => {
                mainImg.src = newSrc;
                mainImg.style.opacity = "1";
            }, 200);

            thumbs.forEach((t) => t.classList.remove("is-active"));
            thumb.classList.add("is-active");
        });
    });
}

/* ─────────────────────────────────────────
   INLINE ERROR UTILITY
───────────────────────────────────────── */
function showInlineError(msg) {
    const existing = document.getElementById("pgmInlineError");
    if (existing) existing.remove();

    const el = document.createElement("div");
    el.id = "pgmInlineError";
    el.className = "pgm-toast pgm-toast--error";
    el.style.position = "fixed";
    el.style.top = "80px";
    el.style.right = "var(--space-xl)";
    el.style.zIndex = "600";
    el.innerHTML = `<i class="ph-fill ph-warning-circle"></i><span>${msg}</span>`;

    document.body.appendChild(el);
    setTimeout(() => el.remove(), 4000);
}

import "./bootstrap";
/**
 * Product Gallery Manager — app.js
 * Handles: drag-drop upload, AJAX image remove, confirm modal, gallery lightbox
 */

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
            addFilesToInput(files, fileInput);
            renderPreviews(files, preview, false);
        }
    });

    // Input change
    fileInput.addEventListener("change", () => {
        if (fileInput.files.length) {
            renderPreviews(fileInput.files, preview, false);
        }
    });
}

/* Merge new files into a DataTransfer so we keep existing + new */
function addFilesToInput(newFiles, input) {
    const dt = new DataTransfer();

    // Preserve existing files
    if (input.files) {
        Array.from(input.files).forEach((f) => dt.items.add(f));
    }

    Array.from(newFiles).forEach((f) => {
        if (isValidImage(f)) dt.items.add(f);
    });

    input.files = dt.files;
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
            );
            container.appendChild(item);
        };
        reader.readAsDataURL(file);
    });
}

function buildPreviewItem(src, name, imageId, isSaved) {
    const item = document.createElement("div");
    item.className = "pgm-preview-item" + (isSaved ? " is-saved" : "");
    if (imageId) item.dataset.imageId = imageId;

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
                item.remove();
            }
        },
    );

    return item;
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

    const csrfToken = document.querySelector(
        'meta[name="csrf-token"]',
    )?.content;

    itemEl.style.opacity = "0.5";
    itemEl.style.pointerEvents = "none";

    fetch(`/products/${productId}/images/${imageId}`, {
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

    // Wire all delete-trigger buttons
    document.querySelectorAll("[data-delete-form]").forEach((btn) => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            const formId = btn.dataset.deleteForm;
            pendingForm = document.getElementById(formId);
            const name = btn.dataset.productName || "this product";

            backdrop.querySelector("#pgmDeleteTarget").textContent = name;
            backdrop.classList.add("is-open");
        });
    });

    confirmBtn?.addEventListener("click", () => {
        if (pendingForm) {
            pendingForm.submit();
        }
    });

    cancelBtns.forEach((btn) =>
        btn.addEventListener("click", () => {
            backdrop.classList.remove("is-open");
            pendingForm = null;
        }),
    );

    backdrop.addEventListener("click", (e) => {
        if (e.target === backdrop) {
            backdrop.classList.remove("is-open");
            pendingForm = null;
        }
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            backdrop.classList.remove("is-open");
            pendingForm = null;
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

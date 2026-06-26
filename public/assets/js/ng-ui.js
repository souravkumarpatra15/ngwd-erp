/* ===============================
   NGWebD Loader
================================ */
function showLoader(text = "Please wait...") {
  const loader = document.getElementById("ngLoader");
  const loaderText = document.getElementById("ngLoaderText");

  if (loaderText) {
    loaderText.innerText = text;
  }

  if (loader) {
    loader.classList.add("show");
  }
}

function hideLoader() {
  const loader = document.getElementById("ngLoader");

  if (loader) {
    loader.classList.remove("show");
  }
}


/* ===============================
   NGWebD Toast
================================ */
function showToast(message = "Action completed", type = "success", title = null) {
  let container = document.getElementById("ngToastContainer");

  if (!container) {
    container = document.createElement("div");
    container.id = "ngToastContainer";
    document.body.appendChild(container);
  }

  const icons = {
    success: "bi-check-circle-fill",
    error: "bi-x-circle-fill",
    warning: "bi-exclamation-triangle-fill",
    info: "bi-info-circle-fill"
  };

  const titles = {
    success: "Success",
    error: "Error",
    warning: "Warning",
    info: "Info"
  };

  const toast = document.createElement("div");
  toast.className = `ng-toast ${type}`;

  toast.innerHTML = `
    <div class="ng-toast-icon">
      <i class="bi ${icons[type] || icons.info}"></i>
    </div>

    <div class="ng-toast-content">
      <div class="ng-toast-title">${title || titles[type] || "Message"}</div>
      <div class="ng-toast-message">${message}</div>
    </div>

    <button type="button" class="ng-toast-close">&times;</button>
    <div class="ng-toast-progress"></div>
  `;

  container.appendChild(toast);

  const closeToast = () => {
    toast.style.animation = "ngToastOut .3s ease forwards";
    setTimeout(() => toast.remove(), 300);
  };

  toast.querySelector(".ng-toast-close").addEventListener("click", closeToast);

  setTimeout(closeToast, 4000);
}


/* ===============================
   Auto Loader on Forms
================================ */
document.addEventListener("submit", function (e) {
  const form = e.target;

  if (form.hasAttribute("data-no-loader")) {
    return;
  }

  showLoader("Processing...");
});


/* ===============================
   Auto Loader on Links
================================ */
document.addEventListener("click", function (e) {
  const link = e.target.closest("a");

  if (!link) return;

  const href = link.getAttribute("href");

  if (
    !href ||
    href === "#" ||
    href.startsWith("javascript:") ||
    link.hasAttribute("data-no-loader") ||
    link.getAttribute("target") === "_blank"
  ) {
    return;
  }

  showLoader("Loading...");
});
/* ===============================
   NGWebD Notification Sound
   Web Audio beep — no external audio file to host/miss.
   Browsers block audio until a user gesture, so the context is created
   lazily on first click/keypress and reused (not recreated) after that.
================================ */
let ngAudioCtx = null;
function ngUnlockAudio() {
  if (ngAudioCtx) return;
  const Ctx = window.AudioContext || window.webkitAudioContext;
  if (Ctx) ngAudioCtx = new Ctx();
}
document.addEventListener('click', ngUnlockAudio, { once: true });
document.addEventListener('keydown', ngUnlockAudio, { once: true });

function playNotifSound() {
  try {
    if (!ngAudioCtx) return; // no user gesture yet — browser would block it anyway
    if (ngAudioCtx.state === 'suspended') ngAudioCtx.resume();
    [0, 0.12].forEach((delay, i) => {
      const osc = ngAudioCtx.createOscillator();
      const gain = ngAudioCtx.createGain();
      osc.type = 'sine';
      osc.frequency.value = i === 0 ? 880 : 1108;
      gain.gain.setValueAtTime(0.001, ngAudioCtx.currentTime + delay);
      gain.gain.linearRampToValueAtTime(0.15, ngAudioCtx.currentTime + delay + 0.01);
      gain.gain.exponentialRampToValueAtTime(0.001, ngAudioCtx.currentTime + delay + 0.18);
      osc.connect(gain).connect(ngAudioCtx.destination);
      osc.start(ngAudioCtx.currentTime + delay);
      osc.stop(ngAudioCtx.currentTime + delay + 0.2);
    });
  } catch (e) { /* audio not available — fail silently */ }
}


/* ===============================
   NGWebD Loader
================================ */
let ngLoaderFailsafe = null;

function showLoader(text = "Please wait...") {
  const loader = document.getElementById("ngLoader");
  const loaderText = document.getElementById("ngLoaderText");

  if (loaderText) {
    loaderText.innerText = text;
  }

  if (loader) {
    loader.classList.add("show");
  }

  // Failsafe: if whatever triggered this never calls hideLoader() (e.g. a
  // request errors out with no .fail() handler, or times out silently),
  // force it closed after 20s instead of leaving the user stuck forever.
  clearTimeout(ngLoaderFailsafe);
  ngLoaderFailsafe = setTimeout(hideLoader, 20000);
}

function hideLoader() {
  const loader = document.getElementById("ngLoader");
  clearTimeout(ngLoaderFailsafe);

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

  const href = link.getAttribute("href") || "";

  if (
    !href ||
    href === "#" ||
    href.startsWith("#") ||
    href.startsWith("javascript:") ||
    link.hasAttribute("data-no-loader") ||
    link.hasAttribute("data-bs-toggle") ||
    link.getAttribute("role") === "tab" ||
    link.getAttribute("target") === "_blank"
  ) {
    return;
  }

  showLoader("Loading...");
});
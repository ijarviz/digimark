// ---------- Sidebar navigation ----------

document.querySelectorAll(".nav-item").forEach((btn) => {
  btn.addEventListener("click", () => {
    const target = btn.dataset.view;

    document.querySelectorAll(".nav-item").forEach((b) => b.classList.toggle("active", b === btn));
    document.querySelectorAll(".view").forEach((v) => v.classList.toggle("active", v.id === `view-${target}`));
  });
});

// ---------- Follower scrap forms ----------

document.querySelectorAll(".scrap-form").forEach((form) => {
  const platform = form.dataset.platform;
  const label = form.dataset.label || "followers";
  const usernameInput = form.querySelector(".username");
  const submitBtn = form.querySelector("button");
  const resultEl = form.closest(".card").querySelector(".result");
  const errorEl = form.closest(".card").querySelector(".error");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const username = usernameInput.value.trim().replace(/^@/, "");
    if (!username) return;

    resultEl.innerHTML = "";
    errorEl.textContent = "";
    submitBtn.disabled = true;
    submitBtn.textContent = "Scraping...";

    try {
      const res = await fetch(`/api/count?platform=${platform}&username=${encodeURIComponent(username)}`);
      const data = await res.json();

      if (!res.ok) throw new Error(data.error || "Gagal mengambil data.");

      resultEl.innerHTML = `
        <div class="count">${data.raw}</div>
        <div class="label">${label} @${data.username}${
          data.count !== null ? ` &middot; ${data.count.toLocaleString("id-ID")}` : ""
        }</div>
      `;
    } catch (err) {
      errorEl.textContent = err.message;
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = "Scrap";
    }
  });
});

// ---------- Video like/view scrap form ----------

const videoForm = document.getElementById("video-form");
const videoUrlInput = document.getElementById("video-url");
const videoSubmitBtn = document.getElementById("video-submit");
const videoError = document.getElementById("video-error");
const videoResult = document.getElementById("video-result");
const videoAuthor = document.getElementById("video-author");
const videoDesc = document.getElementById("video-desc");

function formatStat(n) {
  return typeof n === "number" ? n.toLocaleString("id-ID") : "–";
}

videoForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const url = videoUrlInput.value.trim();
  if (!url) return;

  videoError.textContent = "";
  videoResult.style.display = "none";
  videoSubmitBtn.disabled = true;
  videoSubmitBtn.textContent = "Scraping...";

  try {
    const res = await fetch(`/api/video-stats?url=${encodeURIComponent(url)}`);
    const data = await res.json();

    if (!res.ok) throw new Error(data.error || "Gagal mengambil data video.");

    videoResult.querySelector('[data-stat="views"]').textContent = formatStat(data.views);
    videoResult.querySelector('[data-stat="likes"]').textContent = formatStat(data.likes);
    videoResult.querySelector('[data-stat="comments"]').textContent = formatStat(data.comments);
    videoResult.querySelector('[data-stat="shares"]').textContent = formatStat(data.shares);
    videoResult.querySelector('[data-stat="saves"]').textContent = formatStat(data.saves);

    videoAuthor.textContent = data.author ? `@${data.author}` : "";
    videoDesc.textContent = data.desc || "";

    videoResult.style.display = "block";
  } catch (err) {
    videoError.textContent = err.message;
  } finally {
    videoSubmitBtn.disabled = false;
    videoSubmitBtn.textContent = "Scrap";
  }
});

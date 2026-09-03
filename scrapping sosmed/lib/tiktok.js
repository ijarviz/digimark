import { parseCount } from "./common.js";

export async function readTikTokFollowerCount(page, username) {
  await page.goto(`https://www.tiktok.com/@${username}`, { waitUntil: "domcontentloaded" });

  const el = page.locator('[data-e2e="followers-count"]').first();
  await el.waitFor({ state: "visible", timeout: 15000 }).catch(() => {});

  if (!(await el.count())) {
    throw new Error("Tidak menemukan elemen followers-count. Username mungkin salah atau belum login.");
  }

  const raw = (await el.innerText()).trim();
  return { username, raw, count: parseCount(raw) };
}

// TikTok embeds the full video payload (stats included) as JSON in a
// <script id="__UNIVERSAL_DATA_FOR_REHYDRATION__"> tag on the video page.
// Reading that is far more reliable than scraping the on-screen action-bar
// labels, which are formatted ("12.3K") and don't always include view count.
export async function readTikTokVideoStats(page, videoUrl) {
  await page.goto(videoUrl, { waitUntil: "domcontentloaded" });

  const item = await page.evaluate(() => {
    const el = document.querySelector("#__UNIVERSAL_DATA_FOR_REHYDRATION__");
    if (!el) return null;
    try {
      const data = JSON.parse(el.textContent);
      const detail = data?.__DEFAULT_SCOPE__?.["webapp.video-detail"];
      return detail?.itemInfo?.itemStruct || null;
    } catch {
      return null;
    }
  });

  if (!item || !item.stats) {
    throw new Error("Tidak menemukan data video. Link mungkin salah, video privat, atau sudah dihapus.");
  }

  const { diggCount, playCount, commentCount, shareCount, collectCount } = item.stats;
  // TikTok's payload mixes numbers and numeric strings across these fields
  // (e.g. collectCount often comes back as "590"), so normalize them all.
  const toNumber = (v) => (v === null || v === undefined ? null : Number(v));
  return {
    url: videoUrl,
    author: item.author?.uniqueId || null,
    desc: item.desc || "",
    views: toNumber(playCount),
    likes: toNumber(diggCount),
    comments: toNumber(commentCount),
    shares: toNumber(shareCount),
    saves: toNumber(collectCount),
    // item.createTime is a Unix timestamp (seconds) of when the video was
    // originally posted on TikTok — distinct from when it was added here.
    createTime: toNumber(item.createTime),
  };
}

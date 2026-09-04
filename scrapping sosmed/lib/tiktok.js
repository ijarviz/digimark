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

// Total attempts per lookup (1 = no retry). TikTok serves a bot-check page
// instead of the video when it rate-limits this IP under load; that clears on
// its own, so a retry with backoff recovers most of them.
const VIDEO_RETRIES = Math.max(1, Number(process.env.TIKTOK_SCRAPE_RETRIES) || 3);

const GENERIC_MISSING =
  "Tidak menemukan data video. Link mungkin salah, video privat, atau sudah dihapus.";
const BLOCKED_MSG =
  "TikTok memblokir permintaan (bot-check). Coba lagi nanti atau pasang proxy (PROXY_URL).";

// Pull TikTok's rehydration JSON out of the current page. Returns one of:
//   { item }          – found the video's itemStruct (may still lack .stats)
//   { blocked: true } – page looks like a captcha / bot-check wall, not the video
//   {}                – page rendered but carries no video payload
async function extractVideoPayload(page) {
  return page.evaluate(() => {
    const el = document.querySelector("#__UNIVERSAL_DATA_FOR_REHYDRATION__");
    if (el) {
      try {
        const data = JSON.parse(el.textContent);
        const item =
          data?.__DEFAULT_SCOPE__?.["webapp.video-detail"]?.itemInfo?.itemStruct || null;
        return item ? { item } : {};
      } catch {
        return {};
      }
    }
    // No rehydration blob at all — tell a bot-check page apart from a blank one
    // so the caller knows whether retrying is worth it.
    const text = (document.body?.innerText || "").toLowerCase();
    const blocked =
      /verify to continue|are you a robot|captcha|unusual traffic|security check|please wait|访问验证|请稍候/.test(
        text,
      ) || !!document.querySelector('[id*="captcha" i], [class*="captcha" i]');
    return { blocked };
  });
}

// TikTok embeds the full video payload (stats included) as JSON in a
// <script id="__UNIVERSAL_DATA_FOR_REHYDRATION__"> tag on the video page.
// Reading that is far more reliable than scraping the on-screen action-bar
// labels, which are formatted ("12.3K") and don't always include view count.
export async function readTikTokVideoStats(page, videoUrl) {
  let reason = GENERIC_MISSING;

  for (let attempt = 1; attempt <= VIDEO_RETRIES; attempt++) {
    if (attempt > 1) {
      // Exponential backoff with jitter: ~4s, ~8s, ~16s ...
      const backoff = Math.round(2 ** attempt * 1000 * (0.75 + Math.random() * 0.5));
      await page.waitForTimeout(backoff);
    }

    await page.goto(videoUrl, { waitUntil: "domcontentloaded", timeout: 45000 });
    // The blob is written during hydration, a beat after DOMContentLoaded.
    await page
      .waitForSelector("#__UNIVERSAL_DATA_FOR_REHYDRATION__", { timeout: 8000 })
      .catch(() => {});

    const { item, blocked } = await extractVideoPayload(page);

    if (item && item.stats) {
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

    // Any miss here — an empty payload or a bot-check shell — is usually
    // transient under load (TikTok degrades responses to this IP when hit in
    // bursts), so retry the lot. A genuinely dead/bad link just burns the
    // remaining attempts before failing.
    reason = blocked ? BLOCKED_MSG : GENERIC_MISSING;
  }

  throw new Error(reason);
}

import express from "express";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { chromium } from "playwright";
import { randomDelay } from "./scripts/utils.js";
import { appendHistory } from "./lib/common.js";
import { readTikTokFollowerCount, readTikTokVideoStats } from "./lib/tiktok.js";
import { readInstagramFollowerCount } from "./lib/instagram.js";
import { readYouTubeFollowerCount } from "./lib/youtube.js";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const DATA_DIR = path.join(__dirname, "data");
const PORT = process.env.PORT || 3000;
// Follower count is public data, no login needed for any platform. Runs
// headless (no visible window) by default; set HEADLESS=false to see it.
const HEADLESS = process.env.HEADLESS !== "false";

// Optional upstream proxy for the browser. Set PROXY_URL to a residential /
// mobile endpoint (e.g. "http://host:port" or "socks5://host:port") to stop
// TikTok bot-checking this datacenter IP; PROXY_USERNAME / PROXY_PASSWORD if
// it needs auth. Unset = direct connection.
const PROXY_URL = process.env.PROXY_URL || "";

// Pacing for the TikTok video-stats path. Requests are already serialized by
// the per-resource queue; this delay before each one keeps a bulk run (cron
// snapshot:tiktok, or the "Refresh All" button walking ~100 links) from
// tripping TikTok's rate limit. Tune via env.
const VIDEO_MIN_DELAY_MS = Number(process.env.TIKTOK_SCRAPE_MIN_DELAY_MS) || 1500;
const VIDEO_MAX_DELAY_MS = Number(process.env.TIKTOK_SCRAPE_MAX_DELAY_MS) || 3000;

const READERS = {
  tiktok: readTikTokFollowerCount,
  instagram: readInstagramFollowerCount,
  youtube: readYouTubeFollowerCount,
};
const PLATFORMS = Object.keys(READERS);
// "tiktok-video" is a separate resource (its own page/queue) from the
// "tiktok" follower-count resource, so looking up a video's stats never
// blocks on — or gets blocked by — a follower-count lookup.
const RESOURCES = [...PLATFORMS, "tiktok-video"];

let browser;
let browserPromise;
// Each resource gets its own page (isolated cookies/context) inside the
// same browser instance, so a lookup on one doesn't block another.
// Requests to the *same* resource still queue, since a single page can't
// navigate twice at once.
const pages = Object.fromEntries(RESOURCES.map((p) => [p, undefined]));
const queues = Object.fromEntries(RESOURCES.map((p) => [p, { value: Promise.resolve() }]));

function runQueued(queueRef, task) {
  const result = queueRef.value.then(task);
  queueRef.value = result.catch(() => {}); // keep the chain alive even after a failure
  return result;
}

async function launchBrowser() {
  const launchOptions = {
    headless: HEADLESS,
    args: ["--disable-blink-features=AutomationControlled"],
  };
  if (PROXY_URL) {
    launchOptions.proxy = { server: PROXY_URL };
    if (process.env.PROXY_USERNAME) launchOptions.proxy.username = process.env.PROXY_USERNAME;
    if (process.env.PROXY_PASSWORD) launchOptions.proxy.password = process.env.PROXY_PASSWORD;
    console.log(`Browser proxy: ${PROXY_URL}`);
  }
  browser = await chromium.launch(launchOptions);

  // If the window is closed (or the browser crashes), forget everything so
  // the next request relaunches a fresh browser + pages automatically.
  browser.on("disconnected", () => {
    browser = undefined;
    browserPromise = undefined;
    for (const p of RESOURCES) pages[p] = undefined;
  });
}

function ensureBrowser() {
  if (browser && browser.isConnected()) return Promise.resolve();
  if (!browserPromise) browserPromise = launchBrowser();
  return browserPromise;
}

// A page can become unusable either by being closed (window/tab closed) or
// by crashing mid-navigation (Chromium tab process dying, e.g. under memory
// pressure) — both need the same recovery: throw away the page and relaunch.
function isRecoverablePageError(err) {
  const message = String(err.message || err);
  return message.includes("closed") || message.includes("crashed");
}

function isPageUsable(page) {
  if (!page) return false;
  try {
    return !page.isClosed();
  } catch {
    return false; // isClosed() itself can throw if the connection was severed
  }
}

async function getPage(platform) {
  await ensureBrowser();
  if (isPageUsable(pages[platform])) return pages[platform];
  // Force English so number formatting stays predictable ("30.3M" instead
  // of a locale-specific abbreviation like "30,3 jt").
  const context = await browser.newContext({ viewport: null, locale: "en-US" });
  pages[platform] = await context.newPage();
  return pages[platform];
}

async function scrape(platform, username) {
  await randomDelay(800, 1800);
  const read = READERS[platform];
  try {
    const page = await getPage(platform);
    return await read(page, username);
  } catch (err) {
    // The page/window may have been closed or crashed mid-request — relaunch
    // once and retry instead of failing outright.
    if (isRecoverablePageError(err)) {
      pages[platform] = undefined;
      const page = await getPage(platform);
      return await read(page, username);
    }
    throw err;
  }
}

async function scrapeVideoStats(url) {
  await randomDelay(VIDEO_MIN_DELAY_MS, VIDEO_MAX_DELAY_MS);
  await ensureBrowser();
  // A fresh context per lookup. Reusing one long-lived page for video-stats
  // makes TikTok progressively serve a degraded "trouble playing this video"
  // shell after a few dozen navigations (the payload comes back empty even
  // though the video is fine). A clean context sidesteps that and costs
  // little next to the pacing delay above. Requests are still serialized by
  // the per-resource queue, so only one of these is open at a time.
  const context = await browser.newContext({ viewport: null, locale: "en-US" });
  try {
    const page = await context.newPage();
    return await readTikTokVideoStats(page, url);
  } finally {
    await context.close().catch(() => {});
  }
}

const app = express();
app.use(express.static(path.join(__dirname, "public")));

app.get("/api/count", (req, res) => {
  const platform = String(req.query.platform || "").toLowerCase();
  const username = String(req.query.username || "").replace(/^@/, "").trim();

  if (!READERS[platform]) {
    return res.status(400).json({ error: `Platform tidak dikenali (pakai: ${PLATFORMS.join(", ")}).` });
  }
  if (!username) {
    return res.status(400).json({ error: "Username wajib diisi." });
  }
  if (!/^[a-zA-Z0-9._-]{1,30}$/.test(username)) {
    return res.status(400).json({ error: "Username tidak valid." });
  }

  runQueued(queues[platform], async () => {
    const result = await scrape(platform, username);
    appendHistory(DATA_DIR, platform, username, result.raw, result.count);
    return result;
  })
    .then((result) => res.json(result))
    .catch((err) => res.status(500).json({ error: err.message || String(err) }));
});

app.get("/api/video-stats", (req, res) => {
  const url = String(req.query.url || "").trim();

  if (!url) {
    return res.status(400).json({ error: "Link video wajib diisi." });
  }
  let parsed;
  try {
    parsed = new URL(url);
  } catch {
    return res.status(400).json({ error: "Link video tidak valid." });
  }
  if (!/^(www\.|vt\.|vm\.|m\.)?tiktok\.com$/.test(parsed.hostname)) {
    return res.status(400).json({ error: "Link harus berupa URL video TikTok (tiktok.com)." });
  }

  runQueued(queues["tiktok-video"], () => scrapeVideoStats(url))
    .then((result) => res.json(result))
    .catch((err) => res.status(500).json({ error: err.message || String(err) }));
});

ensureBrowser().then(() => {
  app.listen(PORT, () => {
    console.log(`Server jalan di http://localhost:${PORT}`);
  });
});

import { chromium } from "playwright";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { randomDelay } from "./utils.js";
import { appendHistory } from "../lib/common.js";
import { readTikTokFollowerCount } from "../lib/tiktok.js";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const DATA_DIR = path.join(__dirname, "..", "data");
const HEADLESS = process.env.HEADLESS !== "false";

function parseArgs() {
  const args = Object.fromEntries(
    process.argv.slice(2).map((a) => {
      const [k, v] = a.replace(/^--/, "").split("=");
      return [k, v ?? true];
    })
  );
  return {
    username: (args.username || "").replace(/^@/, ""),
  };
}

async function main() {
  const { username } = parseArgs();

  if (!username) {
    console.error("Pakai: npm run count -- --username=akunkamu");
    process.exit(1);
  }

  const browser = await chromium.launch({
    headless: HEADLESS,
    args: ["--disable-blink-features=AutomationControlled"],
  });
  const context = await browser.newContext({ viewport: null, locale: "en-US" });
  const page = await context.newPage();

  await randomDelay(500, 1200);

  try {
    const { raw, count } = await readTikTokFollowerCount(page, username);
    console.log(`@${username} followers: ${raw}${count !== null ? ` (${count.toLocaleString("id-ID")})` : ""}`);
    appendHistory(DATA_DIR, "tiktok", username, raw, count);
  } finally {
    await browser.close();
  }
}

main().catch((err) => {
  console.error(err.message || err);
  process.exit(1);
});

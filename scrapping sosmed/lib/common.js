import fs from "node:fs";
import path from "node:path";

// Handles abbreviated counts like "12.3K" or "1.2M" (TikTok and Instagram
// both format follower counts this way).
export function parseCount(text) {
  const clean = text.trim().toUpperCase().replace(/,/g, "");
  const match = clean.match(/^([\d.]+)([KM]?)$/);
  if (!match) return null;
  const [, num, suffix] = match;
  const n = parseFloat(num);
  if (suffix === "K") return Math.round(n * 1_000);
  if (suffix === "M") return Math.round(n * 1_000_000);
  return Math.round(n);
}

export function appendHistory(dataDir, platform, username, raw, count) {
  fs.mkdirSync(dataDir, { recursive: true });
  const historyFile = path.join(dataDir, `follower-count-${platform}-${username}.csv`);
  const isNew = !fs.existsSync(historyFile);
  const line = `${new Date().toISOString()},${raw},${count ?? ""}\n`;
  if (isNew) fs.writeFileSync(historyFile, "timestamp,raw,count\n" + line, "utf8");
  else fs.appendFileSync(historyFile, line, "utf8");
}

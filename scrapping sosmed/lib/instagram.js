import { parseCount } from "./common.js";

// Instagram server-renders an <meta property="og:description"> tag like
// '1.2M Followers, 500 Following, 100 Posts - See Instagram photos and
// videos from Name (@username)', but only for real browser traffic — plain
// HTTP requests get served an empty app shell. Needs a rendered page.
export async function readInstagramFollowerCount(page, username) {
  await page.goto(`https://www.instagram.com/${username}/`, { waitUntil: "domcontentloaded" });

  await page
    .waitForFunction(
      () => {
        const el = document.querySelector('meta[property="og:description"]');
        return el && /Followers/i.test(el.content);
      },
      { timeout: 15000 }
    )
    .catch(() => {});

  const content = await page
    .locator('meta[property="og:description"]')
    .first()
    .getAttribute("content")
    .catch(() => null);

  if (!content) {
    throw new Error(
      "Tidak menemukan data followers. Username mungkin salah, akun private, atau Instagram meminta login untuk request ini."
    );
  }

  const countMatch = content.match(/^([\d.,]+[KM]?)\s+Followers/i);
  if (!countMatch) {
    throw new Error("Tidak bisa membaca jumlah followers dari halaman Instagram.");
  }

  const raw = countMatch[1];
  return { username, raw, count: parseCount(raw) };
}

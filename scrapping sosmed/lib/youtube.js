import { parseCount } from "./common.js";

// YouTube's channel page header (top block with name, handle, subscriber
// count) has no stable id, so match on tags/ids it has used historically and
// pull the "subscribers" text out of it — scoped to that block only, since
// the rest of the page (featured/related channels) contains other channels'
// subscriber counts too.
export async function readYouTubeFollowerCount(page, username) {
  await page.goto(`https://www.youtube.com/@${username}`, { waitUntil: "domcontentloaded" });

  await page
    .waitForFunction(
      () => {
        const header = document.querySelector(
          "ytd-page-header-renderer, #page-header, #channel-header, ytd-c4-tabbed-header-renderer"
        );
        return header && /subscribers?/i.test(header.innerText);
      },
      { timeout: 15000 }
    )
    .catch(() => {});

  const headerText = await page
    .evaluate(() => {
      const header = document.querySelector(
        "ytd-page-header-renderer, #page-header, #channel-header, ytd-c4-tabbed-header-renderer"
      );
      return header ? header.innerText : null;
    })
    .catch(() => null);

  if (!headerText) {
    throw new Error("Tidak menemukan halaman channel. Username/handle mungkin salah.");
  }

  const countMatch = headerText.match(/([\d.,]+[KM]?)\s+subscribers?/i);
  if (!countMatch) {
    throw new Error("Tidak bisa membaca jumlah subscriber dari halaman YouTube.");
  }

  const raw = countMatch[1];
  return { username, raw, count: parseCount(raw) };
}

import { setTimeout as sleep } from "node:timers/promises";

export function randInt(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

export async function randomDelay(minMs, maxMs) {
  await sleep(randInt(minMs, maxMs));
}

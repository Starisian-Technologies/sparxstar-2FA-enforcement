/************************************************************************
 * STARISIAN TECHNOLOGIES PROPRIETARY AND CONFIDENTIAL
 * __________________
 *
 * Copyright © 2026 Starisian Technologies (Max Barrett)
 * All Rights Reserved.
 *
 * NOTICE: All information, technical concepts, and intellectual property
 * contained herein are, and remain the property of Starisian Technologies.
 * This material is protected by trade secret, copyright, and patent law.
 *
 * Access to this software is granted strictly under the terms of a
 * Starisian Technologies Service Agreement, Commercial License, or
 * Employment Agreement. Dissemination, reproduction, or reverse
 * engineering of this material is strictly forbidden unless prior
 * written permission is obtained from Starisian Technologies.
 ************************************************************************/

/**
 * License-header gate (LICENSE_HEADER.md; owner ruling 2026-11-09).
 *
 * This repository is proprietary: LICENSE.md is the only license, and every
 * source file opens with the header defined in LICENSE_HEADER.md. Fails when:
 *
 *   1. a source file does not START with that header, byte for byte; or
 *   2. any tracked file carries a marker of a different license (an SPDX
 *      identifier, an open-source or source-available license name).
 *
 * Usage: node scripts/check-license-headers.js [--fix]
 *   --fix prepends the header to source files that lack one. It never
 *   rewrites a file that carries a different header; that needs a person.
 *
 * Ported from sparxstar-shine's check of the same name, itself ported from
 * sparxstar-3iatlas-identity-node. Plain Node with no dependencies, so the CI
 * job needs no install step in this PHP repository.
 */

import { execFileSync } from 'node:child_process';
import { readFileSync, writeFileSync } from 'node:fs';

const HEADER_DOC = 'LICENSE_HEADER.md';

/** Source files that must carry the header. */
const SOURCE = /\.php$/u;

/**
 * Markers of a license other than LICENSE.md. Assembled from parts so this
 * file does not match its own scan.
 */
const j = (...parts) => parts.join('');
const FOREIGN = [
  j('SPDX', '-License-Identifier'),
  j('MIT', ' License'),
  j('Apache', ' License'),
  j('Business', ' Source License'),
  j('GNU', ' General Public License'),
  j('GNU', ' Lesser General Public License'),
  j('GNU', ' Affero'),
  j('Mozilla', ' Public License'),
  j('BSD', ' License'),
  j('ISC', ' License'),
  j('Creative', ' Commons'),
  j('The', ' Unlicense'),
  j('Change', ' License:'),
  j('Licensed under the', ' '),
];

/** Files allowed to NAME other licenses: the license texts themselves. */
const FOREIGN_SCAN_EXEMPT = new Set(['LICENSE.md', HEADER_DOC, 'LICENSE', 'composer.lock']);

function canonicalHeader() {
  const doc = readFileSync(HEADER_DOC, 'utf8');
  const match = /## Full License Header[\s\S]*?```[^\n]*\n([\s\S]*?)```/u.exec(doc);
  if (!match) throw new Error(`${HEADER_DOC} has no "Full License Header" code block`);
  return match[1];
}

function trackedFiles() {
  return execFileSync('git', ['ls-files', '-z'], { encoding: 'utf8' }).split('\0').filter(Boolean);
}

const fix = process.argv.includes('--fix');
const header = canonicalHeader();
const problems = [];

for (const file of trackedFiles()) {
  let text;
  try {
    text = readFileSync(file, 'utf8');
  } catch {
    continue; // deleted in the working tree
  }

  if (!FOREIGN_SCAN_EXEMPT.has(file)) {
    for (const marker of FOREIGN) {
      if (text.includes(marker)) problems.push(`${file}: carries a non-proprietary license marker ("${marker}")`);
    }
  }

  if (!SOURCE.test(file)) continue;

  // A PHP file must open with <?php: anything before it is emitted as raw
  // output, so the tag is the prologue and the header follows it. Same
  // allowance Shine makes for a shebang, which must stay on line 1.
  const open = /^<\?php\r?\n/u.exec(text);
  if (!open) {
    problems.push(`${file}: does not open with <?php`);
    continue;
  }

  const prologue = open[0];
  const body = text.slice(prologue.length);
  if (body.startsWith(header)) continue;

  if (fix && !/^\s*\/\*[\s\S]*?(Copyright|License)/iu.test(body.slice(0, 400))) {
    writeFileSync(file, `${prologue}${header}\n${body}`);
    console.log(`header added: ${file}`);
    continue;
  }
  problems.push(`${file}: does not carry the proprietary header from ${HEADER_DOC} after <?php`);
}

if (problems.length > 0) {
  for (const p of problems) console.error(p);
  console.error(`\n${problems.length} license problem(s). This repository is proprietary: see LICENSE.md and ${HEADER_DOC}.`);
  process.exit(1);
}
console.log('license headers: ok');

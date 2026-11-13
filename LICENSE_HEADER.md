# Starisian Technologies License Headers

This repository is **proprietary**. `LICENSE.md` is its only license, and
every source file states that in the header below. No other license (open
source or source-available) applies to any file here, and no file may carry
another license's header or identifier.

---

## Full License Header

Every `.php` file must carry this block, byte for byte, immediately after
the `<?php` opening tag and before any other content (docblocks, `declare`,
namespace, imports).

A PHP file cannot open with the comment itself: anything before `<?php` is
emitted as raw output, so the tag comes first and the header follows it. This
is the same allowance Shine makes for a shebang, which must stay on line 1.

WordPress still finds the plugin headers: `get_file_data()` regex-scans the
first 8 KB of the file and does not require the plugin docblock to be the
first comment.

```
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
```

---

## Enforcement

`node scripts/check-license-headers.js` (CI job `license-headers`) fails when:

- a `.php` file does not carry the block above immediately after `<?php`; or
- any tracked file carries a marker of a different license (an SPDX
  identifier or another license's name). `LICENSE.md` and this file are the
  only exceptions.

`node scripts/check-license-headers.js --fix` adds the header to source files
that have none. It never replaces a different header: that needs a person to
look at where the file came from.

**No exemptions.** Test files and the PHPUnit bootstrap carry the header too.
JSON, XML and YAML files cannot hold this comment form and are covered by
`LICENSE.md` alone.

This repository was published under the MIT License in error. It contains no
code from the GPL-licensed Two Factor plugin it extends — only calls to that
plugin's published API — so relicensing it proprietary was safe. See the pull
request that introduced this file for the evidence.

*© 2026 Starisian Technologies (Max Barrett). All Rights Reserved. Patent Pending.*

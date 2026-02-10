# Project Coding DNA v2 (Strict)

## Intent
Use this project like a handcrafted static storefront. Avoid generic, framework-first output.

## Non-Negotiables
- Keep this as plain HTML + CSS + light vanilla JS.
- Do not introduce React, Vue, Next, build tools, or npm-based refactors unless explicitly requested.
- Do not replace the multi-page structure with SPA routing.
- Do not perform broad cleanup/refactor passes unless requested.
- Match nearby files first, then optimize only if asked.

## Architecture Contract
- This codebase is intentionally page-oriented and repetitive.
- Repeated header/footer/nav blocks are acceptable and expected.
- One-off inline style usage is acceptable when it matches existing files.
- Practical shipping speed is preferred over abstraction purity.

## Default Work Order
- Find the closest existing page and copy its structure pattern.
- Make the smallest edits needed for the requested feature.
- Keep class names and file boundaries consistent with current usage.
- Add responsive adjustments in `media-queries.css` if behavior changes on mobile/tablet.
- Stop after the requested scope; do not silently redesign unrelated sections.

## File and Pattern Mapping
- Homepage layout/style: `index.html` + `ecommerce-styles.css`.
- Category listing pages: `paintings.html`, `pottery.html`, `weaving.html` + `product.css`.
- Product detail pages: `painting-prod-det/*.html`, `pottery-prod-det/*.html`, `woven-prod-det/*.html` + each folder `prod-details.css`.
- Cart flow: `cart.html` + `cart.css`.
- Account/newsletter page: `accounts.html` + `accounts.css`.
- Shared responsive overrides: `media-queries.css`.

## HTML Rules for This Repo
- Prefer explicit semantic sections: `header`, `main`, `section`, `footer`.
- Keep markup readable and direct; avoid introducing component abstractions.
- Preserve relative path style and anchor behavior (`#about`, `#contact-us`, `#header`).
- Reuse existing nav/footer markup shape when adding new pages.

## CSS Rules for This Repo
- Keep class naming in the current style (`left-header`, `right-header`, `product-preview`, `cart-container`, `mid-section`).
- Prefer direct values over design-token systems unless asked.
- Keep the established visual language with warm maroon/brown accents (`rgba(123, 36, 36, ...)` family), white/black contrast, and Newsreader plus sans-serif typography.
- Use Flexbox-heavy layout patterns already present across files.

## JavaScript Rules for This Repo
- Keep JS minimal and DOM-first (`querySelector`, `addEventListener`, `localStorage`).
- Keep logic close to the page/feature; avoid introducing app architecture layers.
- Prefer simple, visible behavior over large data-model rewrites.

## UX and Content Rules
- Keep artisan storefront tone: product-first, clear CTA buttons, clear cart/account actions.
- Preserve existing content patterns when extending sections, even if placeholders exist.
- Do not rewrite all copy/text just to make it more polished unless requested.

## Anti-Generic Guardrails
- Do not output boilerplate "best-practice app skeletons."
- Do not force enterprise folder structures onto this repo.
- Do not normalize everything into strict DRY components by default.
- Do not treat repeated markup as a bug in this project context.

## Done Criteria for Any Change
- Fits static multi-page architecture.
- Reuses the nearest existing pattern.
- Keeps styling coherent with current brand language.
- Includes responsive consideration in `media-queries.css` when needed.
- Avoids unrelated refactors and generic framework patterns.

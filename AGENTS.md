# Project Coding DNA v3 (Generalised)

## Intent
Build this project as a clean, handcrafted multi-page web application.
Avoid generic app scaffolding and framework-heavy patterns.

## Non-Negotiables
- Use plain HTML, CSS and light vanilla JS.
- Do not introduce React, Vue, Next, build tools or npm refactors.
- Do not convert the project into a SPA.
- Do not perform broad refactors unless explicitly requested.
- Match the nearest existing page pattern before inventing new structure.

## Architecture Contract
- This project is intentionally multi-page.
- Repeated header/footer blocks are acceptable.
- Simplicity and clarity are preferred over abstraction.
- Practical delivery is preferred over architectural purity.

## Default Work Order
1. Find the closest existing page.
2. Copy its structure.
3. Make minimal edits for the requested feature.
4. Keep class names consistent with current usage.
5. Add responsive tweaks only if necessary.
6. Stop at requested scope.

## HTML Rules
- Use semantic sections: `header`, `main`, `section`, `footer`.
- Keep markup readable and direct.
- Avoid unnecessary wrappers.
- Preserve relative paths and anchor behaviour.
- Do not introduce component abstraction patterns.

## CSS Rules
- Keep class naming consistent and descriptive.
- Do not use inline styles or `<style>` blocks in pages; keep styling in external CSS files.
- Prefer direct values over design token systems.
- Maintain a calm, simple interface style.
- Use Flexbox for layout where appropriate.
- Add mobile adjustments in a shared media queries file if needed.

## JavaScript Rules
- Keep JS minimal.
- Use DOM-first logic (`querySelector`, `addEventListener`).
- Keep logic close to the page it belongs to.
- Avoid global state or large client-side architectures.
- Use `fetch()` for backend calls.

## UX Rules
- Keep interface calm and focused.
- Prioritise clarity over decoration.
- Avoid clutter.
- Keep actions obvious and accessible.

## Anti-Generic Guardrails
- Do not output boilerplate app skeletons.
- Do not restructure the entire project.
- Do not optimise for enterprise scalability.
- Do not introduce unnecessary complexity.

## Done Criteria
A change is complete when:
- It fits the multi-page architecture.
- It matches the nearest existing layout pattern.
- It keeps UI consistent.
- It avoids unrelated refactors.
- It remains simple and readable.

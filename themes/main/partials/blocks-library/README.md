# Partials Library

Extra theme partials not bundled into `themes/main` by default. Add one
when needed with:

```bash
./add-partial.sh <name>
```

| Partial | Description |
|---|---|
| `about-firm` | "About us" company/firm content block (was wired to a now-removed blueprint — needs its own data source if reused) |
| `testimonial-card` | Single testimonial card (headline, text, name, company, optional YouTube video). Used by `testimonial-carousel`. No JS required. |
| `booking-calendly` | Embeds a Calendly inline booking widget. Loads Calendly script lazily. Needs JS wired in dynamic-imports.js — see comment in .htm |
| `testimonial-carousel` | Swiper carousel of testimonials from Testimonials\Testimonial blueprint. Depends on `carousel` + `testimonial-card` partials. Needs JS wired in dynamic-imports.js — see comment in .htm |
| `content-image-text` | Generic content block: image + text side by side |
| `creative-tree` | Decorative tree-graphic block (visual flourish) |
| `field-select-nice` | Styled `<select>` using nice-select2 |
| `gallery` | Image gallery block |
| `list-advantages` | Bullet/icon list of advantages or benefits |
| `pagination` | Pagination control |
| `slider` | Generic content slider/carousel |
| `star-rating` | Star rating display widget |

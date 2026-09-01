# AION-IA Website

A static HTML, CSS and vanilla JavaScript website for AION-IA, a quantum research company. No build step, no server required, no frameworks.

## Running locally

Open `index.html` directly in a browser (double-click it, or drag it into a browser window). Every page links to the next with relative paths, so the whole site works straight from the filesystem.

To later deploy it as a static site (GitHub Pages, Netlify, Vercel static hosting, S3, etc.), push this exact folder and point the host at `index.html`.

## Structure

```
AION-IA/
├── index.html                     Homepage
├── physics-guardrail.html
├── engines.html
├── traffic.html                   Embeds the traffic optimisation app
├── powergrid.html                 Embeds the power grid optimisation app
├── aircraft.html                  Embeds the aircraft shape optimisation app
├── quantum-ide.html                Embeds the Quantum IDE
├── academic-collaboration.html
├── whitepapers.html                Access-gated research library
├── careers.html
├── products.html
├── contact.html
├── privacy-policy.html
├── terms.html
├── css/base.css                    Full design system: variables, layout, components
├── js/main.js                      Header/footer injection, nav, scroll reveal, canvas visuals
├── js/forms.js                     Form submission handling, whitepaper access gate
├── data/data.js                    Editable content: whitepapers, products, academic partners
└── whitepapers/                    Place whitepaper PDFs here (paper-01.pdf, paper-02.pdf, ...)
```

## Editing content

**Whitepapers** - edit `data/data.js`, the `AION_WHITEPAPERS` array. Each entry needs `num`, `title`, `authors`, `date`, `description` and `file` (a relative path into `/whitepapers/`). Place the matching PDF in `/whitepapers/`.

**Products** - edit `data/data.js`, the `AION_PRODUCTS` array. Add a new object to add a sixth product; no HTML changes needed.

**Academic partners** - edit `data/data.js`, the `AION_ACADEMIC` array. Replace the placeholder `name`, `description` and `link` fields for each of the five institutions. Do not publish this page with placeholder names still in place.

## Engine and IDE iframe URLs

Each of `traffic.html`, `powergrid.html`, `aircraft.html` and `quantum-ide.html` embeds an externally hosted application via a full-page `<iframe>`. To change the target application, edit the `src` attribute of the `<iframe id="engineFrame">` element in the relevant file.

If an embedded application's server sends headers that block iframe embedding (`X-Frame-Options` or `Content-Security-Policy: frame-ancestors`), the browser will refuse to render it. This must be resolved on the embedded application's server (allow-listing this site's origin), not worked around client-side. A fallback message with a direct link is shown if the frame fails to load.

## Forms and email delivery

The contact form, careers application form and whitepaper access form all post JSON to endpoints configured in `js/forms.js` (`AION_ENDPOINTS`):

- `/api/contact`
- `/api/careers-application`
- `/api/whitepaper-access`

No SMTP credentials or API keys exist anywhere in this codebase, by design. To make these forms actually deliver email (to `info@aion-ia.in` and `careers@aion-ia.in`), stand up a small backend (serverless function or lightweight API) at those paths that receives the JSON payload and sends the email through a provider of your choice. Until that backend exists, submitting a form shows a status message confirming the form is wired up but not yet connected.

## Whitepaper access gate

`whitepapers.html` shows a modal collecting name, phone and email before revealing the paper list. On submission it POSTs to `/api/whitepaper-access` (see above) and unlocks the list for the current browser session via `sessionStorage`. Wire the endpoint to a backend to actually record access and notify `info@aion-ia.in`.

## Design system

All shared tokens (color, type, spacing) live at the top of `css/base.css` as CSS custom properties. Typefaces are loaded from Google Fonts in each page's `<head>`: Fraunces (headings), Manrope (body), IBM Plex Mono (labels and data).

## Notes

- No em dash character appears anywhere in visible copy, by design.
- No benchmark numbers, partner names, or whitepaper content are fabricated; all such areas are clearly marked as editable placeholders.

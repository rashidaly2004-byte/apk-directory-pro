# HTML cheatsheet

Everything used in this course, in one place. `X` marks a void element (no closing tag).

## Page skeleton

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Page name — Site name</title>
  <meta name="description" content="One sentence, ~150 characters.">
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
  <a href="#content">Skip to content</a>
  <header>…</header>
  <main id="content">…</main>
  <footer>…</footer>
  <script src="/app.js" defer></script>
</body>
</html>
```

## Head

| Tag | Purpose |
| --- | --- |
| `<title>` | Tab label, bookmark name, search result headline |
| `<meta charset="utf-8">` X | Text encoding. First thing in the head |
| `<meta name="viewport" …>` X | Use the device's real width on mobile |
| `<meta name="description" …>` X | Search result snippet |
| `<meta property="og:title\|og:description\|og:image">` X | Link previews in chat apps and social |
| `<meta name="theme-color" content="#123">` X | Tints mobile browser UI |
| `<link rel="canonical" href="…">` X | The page's one true URL |
| `<link rel="icon" href="…">` X | Favicon |
| `<link rel="stylesheet" href="…">` X | Load CSS |
| `<script src="…" defer>` | Load JS without blocking parsing |

## Text

| Tag | Means |
| --- | --- |
| `<h1>`–`<h6>` | Document outline. One `h1`, never skip a level |
| `<p>` | Paragraph |
| `<br>` X | A line break that is part of the content (addresses, verse) |
| `<hr>` X | Thematic break |
| `<strong>` | Importance / urgency (bold by default) |
| `<em>` | Emphasis — the word you'd stress aloud (italic by default) |
| `<b>` / `<i>` | Bold / italic with no meaning. Rarely what you want |
| `<code>` | Code fragment or filename |
| `<pre>` | Preformatted: whitespace preserved |
| `<mark>` | Highlighted for the reader |
| `<small>` | Fine print, side comments |
| `<del>` / `<ins>` | Removed / added text |
| `<abbr title="…">` | Abbreviation, with expansion |
| `<time datetime="2026-08-24">` | Machine-readable date or time |
| `<blockquote cite="url">` | Block quotation |
| `<q>` | Short inline quotation |
| `<cite>` | Title of a work, or visible attribution |
| `<span>` | No meaning. A styling hook of last resort |

## Lists

```html
<ul><li>Item</li></ul>                    <!-- order doesn't matter -->
<ol start="3" reversed><li>Step</li></ol> <!-- order matters -->
<dl><dt>Term</dt><dd>Description</dd></dl><!-- name/value pairs -->
```

Nested lists go **inside** an `<li>`, never between two of them.

## Links

```html
<a href="https://example.com">Absolute — other people's sites</a>
<a href="about.html">Relative — same folder</a>
<a href="blog/post.html">Relative — into a subfolder</a>
<a href="../index.html">Relative — up one folder</a>
<a href="/pricing">Root-relative — needs a real server, not file://</a>
<a href="#section-id">Fragment — jumps within the page</a>
<a href="faq.html#shipping">Fragment on another page</a>
<a href="mailto:hi@example.com">Email</a>
<a href="tel:+441234567890">Phone</a>
<a href="report.pdf" download>Download</a>
<a href="https://example.com" target="_blank" rel="noopener noreferrer">New tab</a>
```

Link text must make sense read on its own. Never "click here".

## Images and media

```html
<img src="cat.jpg" alt="A ginger cat asleep on a keyboard"
     width="800" height="600" loading="lazy">      <!-- X: void element -->

<img src="squiggle.svg" alt="">                    <!-- decorative: empty alt -->

<figure>
  <img src="chart.png" alt="Revenue tripled from 2024 to 2026">
  <figcaption>Fig 1. Revenue, 2024–2026.</figcaption>
</figure>

<img src="p-800.jpg"
     srcset="p-400.jpg 400w, p-800.jpg 800w, p-1600.jpg 1600w"
     sizes="(max-width: 600px) 100vw, 800px" alt="…">

<picture>
  <source srcset="hero.avif" type="image/avif">
  <source srcset="hero.webp" type="image/webp">
  <img src="hero.jpg" alt="…">
</picture>

<video src="demo.mp4" controls poster="thumb.jpg" preload="metadata"
       width="640" height="360">
  <track kind="captions" src="demo.en.vtt" srclang="en" label="English" default>
</video>

<audio src="episode.mp3" controls></audio>

<iframe src="…" title="What this embed is" loading="lazy"></iframe>
```

Formats: SVG for logos/icons/diagrams, WebP or AVIF for photos, PNG for
screenshots and transparency, JPEG for maximum compatibility. Not GIF — use a
muted looping `<video>`.

## Structure

| Tag | Use for |
| --- | --- |
| `<header>` | Intro content for the page or for a section. Several allowed |
| `<nav>` | A block of navigation links |
| `<main>` | The page's unique content. **Exactly one**, no repeated site chrome inside |
| `<section>` | A thematic group that deserves a heading |
| `<article>` | Self-contained item that would make sense republished elsewhere |
| `<aside>` | Tangential content: pull quote, related links |
| `<footer>` | Closing content for the page or a section |
| `<div>` | No meaning. A box for CSS. Legitimate when there's nothing to say |

Decision order: republishable → `article`; deserves a heading → `section`;
neither → `div`.

## Tables

```html
<table>
  <caption>What the table shows</caption>
  <colgroup><col><col span="2" class="numeric"></colgroup>
  <thead>
    <tr><th scope="col">Item</th><th scope="col">Qty</th><th scope="col">Price</th></tr>
  </thead>
  <tbody>
    <tr><th scope="row">Notebook</th><td>2</td><td>£7.00</td></tr>
  </tbody>
  <tfoot>
    <tr><td colspan="2">Total</td><td>£7.00</td></tr>
  </tfoot>
</table>
```

`scope="col"` on column headers, `scope="row"` on the cell that names a row.
Spanned cells are not written, so every row must still add up. Tables are for
data, never layout.

## Forms

```html
<form action="/subscribe" method="post">
  <label for="email">Email</label>
  <input type="email" id="email" name="email" required autocomplete="email">
  <button type="submit">Subscribe</button>
</form>
```

- `name` — the key the value is submitted under. No `name`, no submission.
- `id` — connects the control to its `<label for>`. Not submitted.
- `method="get"` puts data in the query string; `post` in the request body.

Input types: `text` `email` `password` `tel` `url` `number` `date` `time`
`datetime-local` `month` `range` `color` `file` `search` `checkbox` `radio`
`hidden`.

Other controls: `<textarea rows="4">` (default value goes between the tags),
`<select>` with `<option value="">` and `<optgroup label="…">`,
`<fieldset>` + `<legend>` to group radios and checkboxes, `<datalist>`,
`<output>`, `<progress>`, `<meter>`.

Validation attributes: `required` `minlength` `maxlength` `min` `max` `step`
`pattern` `autocomplete`; `novalidate` on the form disables all of it.
Also `disabled` (not submitted), `readonly` (submitted), `autofocus`, `checked`,
`selected`, `multiple`, `accept`.

**Client-side validation is convenience, not security. Re-check on the server.**

## Global attributes

`id` (unique per page) · `class` (space-separated, any number) · `lang` ·
`title` · `hidden` · `style` (avoid in real work) · `data-*` (your own data) ·
`tabindex` · `aria-label` · `aria-labelledby` · `aria-describedby` ·
`aria-current` · `aria-expanded` · `role`

## Character entities

| Write | Get | Why |
| --- | --- | --- |
| `&lt;` | `<` | A bare `<` starts a tag |
| `&gt;` | `>` | Pairs with the above |
| `&amp;` | `&` | A bare `&` starts an entity |
| `&quot;` | `"` | Inside a double-quoted attribute |
| `&nbsp;` | non-breaking space | Keeps `10&nbsp;kg` on one line |
| `&copy;` `&mdash;` `&hellip;` `&pound;` | © — … £ | Convenience |

## Comments

```html
<!-- Ignored by the browser, but shipped to it. Never put secrets here. -->
```

## Accessibility checklist

1. Right element for the job — `<button>` acts, `<a href>` navigates.
2. `lang` on `<html>`.
3. Every image has `alt` (empty if decorative).
4. Every form control has a `<label>`.
5. Headings form a real outline: one `h1`, no skipped levels.
6. Landmarks present, plus a skip link to `<main>`.
7. Focus outlines visible — tab through the page and watch.
8. Link text meaningful in isolation.
9. Meaning never carried by colour alone; contrast is high.
10. Don't reach for ARIA when plain HTML already does it.

## Tools

- [W3C validator](https://validator.w3.org/nu/) — paste your markup, fix what it lists.
- Dev tools (F12) — Elements shows the tree the browser really built; Accessibility shows what a screen reader sees.
- Lighthouse, in Chrome dev tools — accessibility, performance and SEO audit.
- `python3 -m http.server` — serve a folder so root-relative paths work.
- [MDN element reference](https://developer.mozilla.org/en-US/docs/Web/HTML/Element) — the reference to bookmark.

## Dead things — never use

`<center>` `<font>` `<marquee>` `<blink>` `<big>` `<frameset>`, and the
`align`, `bgcolor`, `border`, `cellpadding` attributes. All of it is CSS's job
now. `<meta name="keywords">` is ignored by every search engine.

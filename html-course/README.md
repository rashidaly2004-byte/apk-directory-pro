# Learn HTML — a hands-on course

Eight short lessons, three exercises, one cheatsheet. Every lesson is itself an
HTML page, so you read it in the browser and then open the same file in your
editor to see how it was built. No installs, no build step, no framework.

## Start here

Open `index.html` in a browser. Double-clicking the file works.

Or serve the folder, which you'll want once you start using paths that begin
with `/`:

```bash
cd html-course
python3 -m http.server
# then open http://localhost:8000
```

Keep your editor and browser side by side. Edit, save, refresh. That loop is the
whole job.

## The course

| # | Lesson | Covers |
| --- | --- | --- |
| 01 | [Your first page](lessons/01-your-first-page.html) | Elements, tags, attributes, the doctype, head vs body, whitespace, comments |
| 02 | [Text](lessons/02-text.html) | Headings, paragraphs, inline semantics, lists, quotes, entities |
| 03 | [Links and paths](lessons/03-links.html) | Absolute, relative, root-relative and fragment URLs; `mailto:`; new tabs; nav |
| 04 | [Images and media](lessons/04-images-and-media.html) | `img`, alt text, formats, `figure`, `srcset`, `picture`, video, audio, iframes |
| 05 | [Page structure](lessons/05-structure.html) | Block vs inline, landmark elements, `section` vs `article` vs `div`, skip links |
| 06 | [Tables](lessons/06-tables.html) | Rows, header cells, `scope`, spanning, `colgroup`, what tables aren't for |
| 07 | [Forms](lessons/07-forms.html) | Labels, input types, selects, radios, fieldsets, built-in validation |
| 08 | [Head and accessibility](lessons/08-head-and-accessibility.html) | Metadata, Open Graph, favicons, the accessibility short list, validators |

Lesson 08 ends with a three-page final project.

## Exercises

Do the `starter.html` first — it's a plain HTML file with numbered TODO
comments. `solution.html` sits next to it, with comments explaining the choices.

| Exercise | Do it after | Practises |
| --- | --- | --- |
| [01 · Recipe card](exercises/01-recipe-card/starter.html) | Lesson 02 | Headings, paragraphs, both list types, entities |
| [02 · Profile page](exercises/02-profile-page/starter.html) | Lesson 05 | Semantic landmarks, links, images, skip links |
| [03 · Signup form](exercises/03-signup-form/starter.html) | Lesson 07 | Labels, input types, fieldsets, validation, a data table |

## Reference

[`cheatsheet.md`](cheatsheet.md) — every tag and attribute in the course on one
page, plus the accessibility checklist and the list of dead tags to avoid.

## Layout

```
html-course/
├── index.html                  course home — start here
├── README.md
├── cheatsheet.md
├── assets/
│   ├── course.css              styling for the lesson pages
│   └── sample-photo.svg        an image to practise with
├── lessons/
│   ├── 01-your-first-page.html
│   ├── 02-text.html
│   ├── 03-links.html
│   ├── 04-images-and-media.html
│   ├── 05-structure.html
│   ├── 06-tables.html
│   ├── 07-forms.html
│   └── 08-head-and-accessibility.html
└── exercises/
    ├── 01-recipe-card/{starter,solution}.html
    ├── 02-profile-page/{starter,solution}.html
    └── 03-signup-form/{starter,solution}.html
```

## Two habits worth building now

1. **View source on everything.** Right-click any page on the web and choose
   *View Page Source*. Every site you admire is markup somebody typed. Reading
   other people's HTML is how the shapes start to feel obvious.
2. **Validate before you debug.** Paste your markup into the
   [W3C validator](https://validator.w3.org/nu/). Unclosed tags and bad nesting
   cause most "why does it look like that?" mysteries, and the validator finds
   them in a second.

## Scope

HTML says what things *are*. CSS says how they *look*. JavaScript says what they
*do*. This course is only the first, deliberately: HTML is small enough to learn
properly in an afternoon, and CSS is far easier once you know what you're
styling. Lesson 08 points at where to go next.

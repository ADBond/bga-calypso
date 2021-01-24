# Dev notes

## Pre-commit

Copy pre-commit hook to git folder:

```bash
cp pre-commit .git/hooks/pre-commit
```

## Sass

Install sass with `npm`

```bash
npm install -g sass
```

Process the `scss` file - from root:

```bash
sass --watch calypso_style.scss calypso.css
```

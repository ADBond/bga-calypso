# Dev notes

## Pre-commit

Copy pre-commit hook to git folder:

```bash
cp pre-commit .git/hooks/pre-commit
```

## Sass

Install sass with `npm`. Obviously this means you need npm.

```bash
npm install -g sass
```

Process the `scss` file - from root:

```bash
sass --watch calypso_style.scss calypso.css
```

## turning off BGA background

Edit style in `common.css`: as follows (e.g.)

```css
html {
/*  background: url("../img/layout/back-main.jpg"); */
  background-color: #00FF00;
}
```

## compatibility

Do smt like (and origin)

```css
 -webkit-transform: rotate(45deg);
     -moz-transform: rotate(45deg);
      -ms-transform: rotate(45deg);
       -o-transform: rotate(45deg);
          transform: rotate(45deg);
```

## Misc framework notes

Can safely wrap invalid options:

```php
self::initGameStateLabels(
  array(
    ...
    "fakeGameOption" => 109,  # 109 not in gameoptions.json
    ...
  )
)
...

$val = self::getGameStateValue('fakeGameOption', 999);
# $val is now 999
```

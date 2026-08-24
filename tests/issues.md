# Bug report: WP_Icons_Registry sanitizer strips valid third-party icon markup

**Component:** Icons API (`wp_register_icon()` / `WP_Icons_Registry`)
**WordPress version tested:** 7.0.x / 7.1 nightly (`wp-includes/class-wp-icons-registry.php`, `wp-includes/icons.php`)
**Reported by:** nextgenthemes-icons plugin — a third-party collection registering ~21k icons (Bootstrap Icons, Phosphor)

## Summary

`WP_Icons_Registry::sanitize_icon_content()` uses a hard-coded kses allowlist that only knows the tags
`svg`, `path`, `polygon` and a small attribute set. Third-party icon collections that use other standard
SVG elements or presentation attributes are silently stripped at render time, producing broken or empty icons.
There is no filter to extend the allowlist, because the registry passes its array straight to `wp_kses()`,
bypassing every context-based lookup where `wp_kses_allowed_html` would fire.

## Root cause

`class-wp-icons-registry.php`, `sanitize_icon_content()`:

```php
$allowed_tags = array(
    'svg'     => array( 'class', 'xmlns', 'width', 'height', 'viewbox', 'aria-hidden', 'role', 'focusable' ),
    'path'    => array( 'fill', 'fill-rule', 'd', 'transform' ),
    'polygon' => array( 'fill', 'fill-rule', 'points', 'transform', 'focusable' ),
);
return wp_kses( $icon_content, $allowed_tags );
```

Why no plugin-side override exists:

- `wp_kses_allowed_html` (kses.php) only fires inside the context helper `wp_kses_allowed_html()`,
  which is **never called** on this path — the registry hands its own array directly to `wp_kses()`.
- The only hook reachable here is `pre_kses`, whose return value replaces the *string*; it cannot
  extend tags/attributes.
- `safe_style_css` and `wp_kses_uri_attributes` are irrelevant to element/attribute allowlisting.

## Impact on real-world icon libraries

| Collection | Stripped | Result |
|---|---|---|
| **bootstrap** | root `fill` (CSS covers ✓) | fine, except **4 icons lose shapes**: `align-bottom`, `align-top`, `circle-fill`, `dice-1` contain `<circle>/<rect>`; `opencollective.svg` loses `fill-opacity` |
| **phosphor-{thin,light,regular,fill,bold}** | every `<circle/line/polyline/rect/ellipse/g>` element deleted + all `stroke-*` attrs gone | **severe** — these are stroke-drawn styles; they collapse to naked paths |
| **phosphor-duotone** | same + `opacity` | severe |
| **phosphorflat-{thin,light,regular,fill,bold}** | only root `fill` (CSS ✓) | renders correctly |
| **phosphorflat-duotone** | path `opacity="0.2"` stripped (1,510/1,512 files) | both layers render solid — legible but not duotone |

Bootstrap Icons is also affected despite being path-based: any icon using `<circle>`/`<rect>`, or
`fill-opacity`, loses those attributes/elements.

## Steps to reproduce

1. Register an icon with markup containing an allowed tag plus a common shape:

   ```php
   wp_register_icon( 'test/circle-icon', [
       'label' => 'Circle',
       'content' =>
           '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor">' .
           '<circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="2" fill="none"/>' .
           '</svg>',
   ] );
   ```

2. Render via `wp_get_icon( 'test/circle-icon' )`.

3. Actual output: the `<circle>` element and all styling are gone — an empty `<svg>` remains.

4. Same result when using `file_path` pointing at any Phosphor `svgs/*.svg`
   (they consist mostly of `<line>`, `<circle>`, `<rect>`, `<polyline>` with `stroke-*` attributes).

## Existing reports / prior art

Searched WordPress Trac and the GitHub Gutenberg tracker (direct Trac pages return HTTP 403 to bots;
ticket details below come from search snippets).

| Where | Reference | Status | Relation |
|---|---|---|---|
| Trac | [#65795](https://core.trac.wordpress.org/ticket/65795) — "Icons: Improve the SVG sanitizer in WP_Icons_Registry" | open (per search snippet) | **Same root issue**: "That sanitizer is incomplete: it strips attributes and elements that are valid SVG markup." |
| Trac | [#64651](https://core.trac.wordpress.org/ticket/64651) — "Icons: Backport Icons Registry and wp/v2/icons endpoint" | tracking ticket | Opened the registry to third parties in 7.1; this bug undermines exactly that promise |
| Trac | [#63778](https://core.trac.wordpress.org/ticket/63778) — "Allow admins/editors to upload SVGs" | discussion | Broader "core needs a real SVG sanitizer" thread (Safe SVG et al.) |
| GitHub (gutenberg) | [PR #75550](https://github.com/WordPress/gutenberg/pull/75550) — "Icons Registry: improve SVG sanitizer" (t-hamano) | open, assigned, last updated 2026-08-18 | **Direct overlap**: extends the private sanitization methods "and defined as many SVG-friendly tags and attributes as possible". Follow-up on #72215 |
| GitHub (gutenberg) | [PR #80139](https://github.com/WordPress/gutenberg/pull/80139) (merged, 23.5.2) | merged | Establishes `WP_Icons_Registry_Gutenberg` — a **subclass overriding core's registry** — proving the subclass pattern is sanctioned upstream |
| GitHub (gutenberg) | [#80668](https://github.com/WordPress/gutenberg/issues/80668) (+ #79669, PR #80166) | open | Adjacent pain: third-party icons vanish when collection unregisters; shows third-party Icon API usage growing |

No Trac ticket was found that specifically covers `stroke*`/shape-element stripping for
third-party collections beyond #65795's general statement.

## Suggested fixes (core)

1. Ship a dedicated, well-tested `wp_sanitize_svg()` (see #63778 discussion) and use it in the registry,
   replacing the hand-rolled allowlist.
2. Short term: extend `sanitize_icon_content()` per Gutenberg PR #75550 — add
   `circle`, `ellipse`, `line`, `polyline`, `rect`, `g` plus presentation attributes
   (`fill-opacity`, `opacity`, `stroke`, `stroke-width`, `stroke-linecap`, `stroke-linejoin`,
   `stroke-miterlimit`) and geometry attributes (`cx cy r rx ry x y x1 x2 y1 y2 width height points`).
3. Add a filterable allowlist (e.g. `wp_icons_registry_allowed_svg_tags`) so collections can opt into
   additional safe markup without waiting for core cycles.

## Workaround used meanwhile (plugin-side, no core edit)

`$instance` is a `protected static` singleton property, so a subclass may replace it before `init`
(the same override pattern Gutenberg itself ships in 23.5.2):

```php
final class NGT_Icons_Registry extends \WP_Icons_Registry {

	public static function swap(): void {
		parent::$instance = new self(); // protected static, legal from child scope
	}

	protected function sanitize_icon_content( $icon_content ) {
		// extended allowlist per "Suggested fixes" above
	}
}

add_action( 'plugins_loaded', [ NGT_Icons_Registry::class, 'swap' ], 0 );
```

Caveat: depends on core internals; guarded with class/property checks and revisited each release.

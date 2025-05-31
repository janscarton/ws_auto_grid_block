# Responsive Grid Areas Block for Concrete CMS

## Overview

### Problem This Block Aims to Solve

Concrete CMS currently offers two ways to divide content areas into subregions: **Layouts** and **Containers**.

- **Layouts** are editor-created, one-off column splits using grid frameworks like Bootstrap or Foundation. Each column becomes its own area.
- **Containers** are developer-defined reusable templates that can include multiple nested areas.

However, neither approach gives editors control over layout settings like number of cells, spacing, or responsive behavior. This block addresses that limitation by allowing editors to create a **customizable grid of editable areas**, where they can control:
- Number of grid cells
- Spacing between cells
- Minimum width of each cell

This approach opens the door for reusable design patterns that adapt to the content editors place inside — without requiring manual layouts or custom containers for every variant.

### The Concrete Editing UI Interference

When a block is in edit mode, Concrete CMS renders a full-size `#ccm-menu-click-proxy` overlay on top of the block. This overlay enables block-level editing (move, delete, etc.) — but it **prevents interaction with nested areas and blocks** inside the block.

As a result, editors cannot interact with the individual editable cells created by this grid block.

### Workaround: Repositioning the Edit Proxy

To circumvent this, the block uses a **CSS-based approach** that reshapes the proxy's target element:

1. A wrapper element (`.ccm-block-edit`) is modified using the `:has()` selector in edit mode to shrink its height to match a small, visible element (a “click target” shown only in edit mode).
2. This causes the `#ccm-menu-click-proxy` overlay to only cover that small region — not the entire block.
3. The actual grid layout (`.ws-auto-grid`) is rendered outside of that region via CSS overflow, making the nested areas fully interactable.
4. A dynamic bottom margin is applied to `.ccm-block-edit` equal to the grid’s height, preventing overlap with content below the block.

This enables editors to access both the block’s settings and its nested content — even though Concrete’s editing UI wasn’t built for this kind of structure.

### Proposed Core Enhancement

This workaround wouldn't be necessary if Concrete CMS offered a way to **designate an overlay target** for a block explicitly.

We propose that Concrete CMS check for a `.ccm-block-edit-target` element inside the block. If found, it should use that element as the target for the editing overlay instead of defaulting to the entire block wrapper.

This would provide a clean, intentional way for advanced block developers to support layouts with internal areas and improve the flexibility of the editor interface.

### Request for Feedback

This implementation works — but it's tightly coupled to current Concrete CMS editing behavior. That raises a few concerns:

- **Is this approach brittle?** Will updates to the core editing UI break the workaround?
- **Are there performance or UX concerns** with creating many nested subareas?
- **How does versioning, duplication, or block export/import behave** when a block contains multiple nested areas?
- **Is there a better way** to solve this problem, or does this merit consideration for core support?

If you’ve built something similar — or see potential pitfalls — we’d love your feedback.

---

Thanks for checking this out. PRs and suggestions welcome!

## Implementation Details

When a page is in edit mode, Concrete CMS wraps each block’s content in a structure like this:

```html
<div data-container="block">
	<div class="ccm-block-edit" data-block-id="123"> <!-- The #ccm-menu-click-proxy targets and overlays this element -->
		<ul …><li><a class="ccm-edit-mode-inline-command-move" …>…</a></li></ul> <!-- The move block handle -->
		<div …><div data-block-menu="block-menu-b123" …>…</div></div> <!-- The edit block menu -->
		[block content here]
		<div class="ccm-block-cover"></div> <!-- highlights block when hovered -->
	</div>
</div>
```
When the user hovers the `.ccm-block-edit`, a `#ccm-menu-click-proxy` element is repositioned over it via JavaScript to capture clicks for editing options and moving the block:

```html
<div id="ccm-menu-click-proxy" class="ccm-menu-item-hover" style="top: 361px; left: 51px; width: 852px; height: 24px; border-radius: 0px;"></div>
```

By default, it overlays the entire .ccm-block-edit element, intercepting clicks for editing actions — but also preventing interaction with any nested areas or blocks inside the block’s content.

### CSS-Based Workaround for Overlay Control

To make the nested areas inside the block interactable in edit mode, the block uses a targeted CSS rule that manipulates how the `.ccm-block-edit` element behaves **only while in edit mode**. The goal is to shrink the height of the edit target and allow the real content to overflow below it.

This is done using the `:has()` selector:

```css
.ccm-block-edit:has(> .ws-auto-grid-edit-target.block-${instanceId}) { /* Targets only this block instance's wrapper */
	margin-bottom: calc(${gridHeight}px + 40px); /* Ensure sufficient space below edit target for the rest of the block to be displayed */
	height: 40px; /* Fixed height for the edit target overlay */
}
```

- The `.ws-auto-grid-edit-target` element is a small, visible UI element rendered only in edit mode.
- The parent `.ccm-block-edit` element becomes only as tall as this target (40px), which is what the `#ccm-menu-click-proxy` will overlay.
- The `margin-bottom` creates enough space for the grid content (defined by `gridHeight`) to overflow without overlapping subsequent blocks.

The actual content grid is then rendered just below that target:

```css
#ws-auto-grid-${instanceId} {
	margin-top: 5px; /* Adds a small visual gap above the grid */
	~ .ccm-block-cover {
		display: none; /* Prevents hover highlight from covering nested areas */
	}
}
```

- This ensures that the nested `.ws-auto-grid` element — which contains all editable sub-areas — is not hidden by the default `.ccm-block-cover` overlay.
- The final result is that the block behaves normally in the editor (can be moved, deleted, etc.) while still allowing interaction with the individually nested areas.

> **Note:** This workaround depends on current Concrete CMS markup and overlay behavior. Future changes to the edit mode DOM or overlay logic could break this technique.
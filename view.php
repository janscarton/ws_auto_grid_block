<?php
defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Area\Area;
use Concrete\Core\Page\Page;

/** @var Page $c */
$c = $c ?? Page::getCurrentPage();

/** @var int $numberOfCells */
/** @var int $minCellWidth */
/** @var int $gapSize */
/** @var int $bID */ // Block ID is automatically available in the block's scope
/** @var int $autoGridInstanceID */

// Construct the inline style for the grid container
$gridStyle = sprintf(
	'grid-template-columns: repeat(auto-fit, minmax(%dpx, 1fr)); gap: %dpx;',
	(int)$minCellWidth,
	(int)$gapSize
);

if ($c->isEditMode()) : ?>
	<div class="ws-auto-grid-edit-target block-<?php echo $autoGridInstanceID; ?>">Auto Grid</div>
<?php endif; ?>

<div id="ws-auto-grid-<?php echo $autoGridInstanceID; ?>" class="ws-auto-grid" style="<?php echo $gridStyle; ?>">
	<?php
	if ($numberOfCells > 0) {
		for ($i = 1; $i <= $numberOfCells; $i++) {
			echo '<div>'; // Start wrapper div
			$areaHandle = sprintf('Cell %s.%d', $autoGridInstanceID, $i);
			$area = new Area($areaHandle);
			$area->setAreaDisplayName(sprintf('Cell %d', $i)); // Set custom display name for the area
			if ($area) {
				$area->display($c);
			}
			echo '</div>'; // End wrapper div
		}
	}
	?>
</div>

<?php if ($c->isEditMode()) : ?>
<script type="text/javascript">
(function() { // IIFE to ensure script runs on AJAX load and scopes variables
	const instanceId = <?php echo json_encode((string)($autoGridInstanceID ?? '0')); ?>;
	const gridElement = document.getElementById('ws-auto-grid-' + instanceId);

	if (!gridElement) {
		console.warn('WS Auto Grid (Instance ID: ' + instanceId + '): Grid element #ws-auto-grid-' + instanceId + ' not found. Dynamic styles not applied.');
		return;
	}

	// Create a <style> element and append it to the head once.
	const styleElement = document.createElement('style');
	styleElement.type = 'text/css';
	// Assign a unique ID to the style element for potential future reference
	styleElement.id = 'ws-auto-grid-dynamic-styles-' + instanceId;
	document.head.appendChild(styleElement);

	function updateDynamicStyles() {
		const rect = gridElement.getBoundingClientRect();
		const gridHeight = rect.height;
		const styleSheetContent = `
			.ccm-block-edit:has(> .ws-auto-grid-edit-target.block-${instanceId}) {
				margin-bottom: calc(${gridHeight}px + 40px); /* Ensure sufficient space below edit target */
				height: 40px; /* Fixed height for the edit target overlay */
			}
			#ws-auto-grid-${instanceId} {
				margin-top: 5px; /* Small margin above the actual grid content */
				~ .ccm-block-cover {
					display: none;
				}
			}
		`;
		styleElement.textContent = styleSheetContent;
	}

	function attemptInitialStyleUpdateWithRaf() {
		requestAnimationFrame(function() {
			const rect = gridElement.getBoundingClientRect();
			if (rect.height === 0) {
				console.warn('WS Auto Grid (Instance ID: ' + instanceId + '): Grid element #ws-auto-grid-' + instanceId + ' reported zero height after requestAnimationFrame. Initial dynamic styles may be based on zero height. Subsequent observers should correct this if content loads.');
			}
			updateDynamicStyles();
		});
	}

	// Initial update call, using requestAnimationFrame first
	attemptInitialStyleUpdateWithRaf();

	// Observe resize of the grid element
	if (window.ResizeObserver) {
		const resizeObserver = new ResizeObserver(updateDynamicStyles);
		resizeObserver.observe(gridElement);
	}

	// Observe mutations within the grid element that might affect its height
	if (window.MutationObserver) {
		const mutationObserver = new MutationObserver(updateDynamicStyles);
		mutationObserver.observe(gridElement, {
			childList: true,
			subtree: true,
			attributes: true,
			characterData: true
		});
	}
})(); // End of IIFE
</script>
<?php endif; ?>
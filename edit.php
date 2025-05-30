<?php
defined('C5_EXECUTE') or die('Access Denied.');

// Values will be injected into the form fields by the controller (e.g., $numberOfCells, $minCellWidth, $gapSize, $autoGridInstanceID)
// For the purpose of this file creation, we'll use the default values from the mockup.
$numberOfCells = $numberOfCells ?? 3;
$minCellWidth = $minCellWidth ?? 150;
$gapSize = $gapSize ?? 30;
$autoGridInstanceID = $autoGridInstanceID ?? 0; // Default to 0 if not set, controller should provide real one
?>

<?php if (isset($autoGridInstanceID) && $autoGridInstanceID > 0) : ?>
    <input type="hidden" name="autoGridInstanceID" value="<?php echo $autoGridInstanceID; ?>">
<?php endif; ?>

<div class="mb-3">
	<label for="auto_grid_numberOfCells" class="form-label"><span class="range-value"></span> Cells</label>
	<input type="range" class="form-range" name="numberOfCells" id="auto_grid_numberOfCells" min="2" max="12" value="<?php echo $numberOfCells; ?>">
</div>
<div class="mb-3">
	<label for="auto_grid_minCellWidth" class="form-label"><span class="range-value"></span>px Minimum Width</label>
	<input type="range" class="form-range" name="minCellWidth" id="auto_grid_minCellWidth" min="100" max="300" value="<?php echo $minCellWidth; ?>">
</div>
<div>
	<label for="auto_grid_gapSize" class="form-label"><span class="range-value"></span>px Gap</label>
	<input type="range" class="form-range" name="gapSize" id="auto_grid_gapSize" min="0" max="120" value="<?php echo $gapSize; ?>">
</div>


<script>
	document.querySelectorAll('input[type="range"]').forEach(input => {
		let valueDisplay;

		// Option 1: label using for=
		const labelByFor = document.querySelector(`label[for="${input.id}"]`);
		if (labelByFor) {
			valueDisplay = labelByFor.querySelector('.range-value');
		}

		// Option 2: input wrapped inside label
		if (!valueDisplay) {
			const labelParent = input.closest('label');
			if (labelParent) {
				valueDisplay = labelParent.querySelector('.range-value');
			}
		}

		if (valueDisplay) {
			const update = () => {
				valueDisplay.textContent = input.value;
			};
			input.addEventListener('input', update);
			update(); // Set initial value
		}
	});
</script>
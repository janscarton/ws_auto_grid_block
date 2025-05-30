<?php
namespace Application\Block\WsAutoGrid;

use Concrete\Core\Block\BlockController;
use Concrete\Core\Support\Facade\Database;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends BlockController
{
	protected $btTable = 'btWsAutoGrid';
	protected $btInterfaceWidth = 450;
	protected $btInterfaceHeight = 305;
	protected $btCacheBlockOutput = true;
	protected $btCacheBlockOutputOnPost = true;
	protected $btCacheBlockOutputForRegisteredUsers = true;
	protected $btDefaultSet = 'ws_specialty';

	// Fields from db.xml to be automatically set in the controller
	protected $numberOfCells;
	protected $minCellWidth;
	protected $gapSize;
	protected $autoGridInstanceID;

	public function getBlockTypeName()
	{
		return t('Auto Grid');
	}

	public function getBlockTypeDescription()
	{
		return t('A block that creates responsive grid areas based on cell count, minimum width, and gap size.');
	}

	private function getNewAutoGridInstanceID()
	{
		$db = Database::connection();
		// Find the current maximum autoGridInstanceID.
		// The 0 in MAX(IFNULL(autoGridInstanceID, 0)) ensures that if the table is empty or all are NULL, we start from 0, so the first ID will be 1.
		$maxID = $db->fetchColumn('SELECT MAX(IFNULL(autoGridInstanceID, 0)) FROM btWsAutoGrid');
		return ($maxID === null ? 0 : (int)$maxID) + 1;
	}

	public function add()
	{
		// Set default values for the form when adding a new block
		$this->set('numberOfCells', 3); // Default from mockup/db.xml
		$this->set('minCellWidth', 150); // Default from mockup/db.xml
		$this->set('gapSize', 30);	  // Default from mockup/db.xml
		$this->set('autoGridInstanceID', $this->getNewAutoGridInstanceID());
		// The form (edit.php) will use these variables
	}

	public function edit()
	{
		// Values are automatically loaded from the database into controller properties
		// (e.g., $this->numberOfCells, $this->minCellWidth, $this->gapSize, $this->autoGridInstanceID)
		// We just need to pass them to the view (the form)
		$this->set('numberOfCells', $this->numberOfCells);
		$this->set('minCellWidth', $this->minCellWidth);
		$this->set('gapSize', $this->gapSize);
		$this->set('autoGridInstanceID', $this->autoGridInstanceID);
	}

	public function save($args)
	{
		// Sanitize and save the data
		// $args contains the POSTed form data
		$args['numberOfCells'] = isset($args['numberOfCells']) ? intval($args['numberOfCells']) : 3;
		$args['minCellWidth'] = isset($args['minCellWidth']) ? intval($args['minCellWidth']) : 150;
		$args['gapSize'] = isset($args['gapSize']) ? intval($args['gapSize']) : 30;

		// Ensure values are within defined ranges (from form-mockup / block settings)
		$args['numberOfCells'] = max(2, min(12, $args['numberOfCells']));
		$args['minCellWidth'] = max(100, min(300, $args['minCellWidth']));
		$args['gapSize'] = max(0, min(120, $args['gapSize']));

		// Ensure autoGridInstanceID is correctly handled.
		// It's generated in add() for new blocks or loaded from DB for existing blocks, then passed to the form.
		// The form (edit.php) should submit it back in $args['autoGridInstanceID'].
		// The following logic provides fallbacks if $args['autoGridInstanceID'] is unexpectedly empty.

		if (empty($args['autoGridInstanceID'])) {
			if (empty($this->autoGridInstanceID)) {
				// Fallback: New block, but ID from add() was lost from form. Generate a new one.
				$args['autoGridInstanceID'] = $this->getNewAutoGridInstanceID();
			} else {
				// Fallback: Existing block ($this->autoGridInstanceID is set), but ID lost from form. Preserve existing ID.
				$args['autoGridInstanceID'] = $this->autoGridInstanceID;
			}
		}
		// If $args['autoGridInstanceID'] was already set from the form, it will be used by parent::save().

		parent::save($args);
	}

	public function duplicate($newBID)
	{
		parent::duplicate($newBID); // This copies the existing record to the new bID
		$db = Database::connection();
		$newAutoGridInstanceID = $this->getNewAutoGridInstanceID();
		$db->executeQuery('UPDATE btWsAutoGrid SET autoGridInstanceID = ? WHERE bID = ?', [$newAutoGridInstanceID, $newBID]);
		// Update the controller instance's property as well
		$this->autoGridInstanceID = $newAutoGridInstanceID;
	}

	public function view()
	{
		// Pass the saved settings to the view.php template
		$this->set('numberOfCells', $this->numberOfCells);
		$this->set('minCellWidth', $this->minCellWidth);
		$this->set('gapSize', $this->gapSize);
		$this->set('autoGridInstanceID', $this->autoGridInstanceID);
		// view.php will also have access to $bID (block ID) automatically
	}
}

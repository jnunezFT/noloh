<?php
/**
 * ListView class
 *
 * We're sorry, but this class doesn't have a description yet. We're working very hard on our documentation so check back soon!
 * 
 * @package Controls/Extended
 */
class ListView extends Panel
{
	const Ascending = true, Descending = false;
	
	public $ListViewItems;
	private $Columns;
	private $CurrentOffset;
	private $BodyPanelsHolder;
	private $SelectedRows;
	private $Selectable;
	private $SelectCSS;
	private $DataSource;
	private $Limit;
	private $SortedBy;
	private $StoredInMemory;
	private $RowCallback;
	private $ApproxCount;
	private $HeightSpacer;
	private $DataColumns;
	
	protected $ColumnsPanel;
	protected $LVItemsQueue = array();
	protected $InnerPanel;
	protected $Line;
	
	function GetColumnPanel(){return $this->ColumnsPanel;}
	function ListView($left, $top, $width, $height)
	{
		parent::Panel($left, $top, $width, $height)/*, $this)*/;
		$this->ColumnsPanel = new Panel(0, 0, $width, 28, $this);
		$this->ColumnsPanel->CSSBackground_Image = "url(". System::ImagePath() . "Std/HeadBlue.gif)";
		$this->ColumnsPanel->CSSBackground_Repeat = "repeat-x";
		$this->ColumnsPanel->Controls->AddFunctionName = "AddColumn";
		$this->Columns = &$this->ColumnsPanel->Controls;
		$this->BodyPanelsHolder = new Panel(0, $this->ColumnsPanel->Bottom, $width, $height - $this->ColumnsPanel->Height);
		$this->BodyPanelsHolder->Scrolling = System::Auto;
		$this->InnerPanel = new Panel(0, 0, null, 'auto', $this);
		$this->InnerPanel->Layout = Layout::Web;
		$this->BodyPanelsHolder->Scrolling = System::Auto;
		$this->ListViewItems = &$this->InnerPanel->Controls;
		$this->ListViewItems->AddFunctionName = 'AddListViewItem';
		$this->ListViewItems->ClearFunctionName = 'ClearListViewItems';
		$this->ListViewItems->InsertFunctionName = 'InsertListViewItem';
		$this->BodyPanelsHolder->Controls->Add($this->InnerPanel);
		//Shift With Outer Panel
		$this->ColumnsPanel->Shifts[] = Shift::WidthWith($this, Shift::Width);
		//$this->ColumnsPanel->Shifts[] = Shift::With($this, Shift::Width, Shift::Width);
		$this->BodyPanelsHolder->Shifts[] = Shift::WidthWith($this, Shift::Width);
		//$this->BodyPanelsHolder->Shifts[] = Shift::With($this, Shift::Width, Shift::Width);
		
		$this->Line = new Label('', 0, 0, 3, '100%');
		$this->Line->Visible = false;
		$this->Line->BackColor = '#808080';
		$this->Line->ParentId = $this->Id;
		$this->Controls->AddRange($this->ColumnsPanel, /*$this->InnerPanel*/ $this->BodyPanelsHolder);
		$this->ModifyScroll();
	}
	function GetColumns(){return $this->Columns;}
	/**
	 * @ignore
	 */
	function AddColumn($text, $width = System::Auto)
	{
		$count = $this->Columns->Count();
		if(is_string($text) || is_int($text))
			$this->Columns->Add($column = &new ColumnHeader($text, ($count > 0)?$this->Columns[$count-1]->GetRight():0, $width, $this->ColumnsPanel->GetHeight()), true, true);
//			$this->Columns->Add($column = &new ColumnHeader($text, 0, $width, $this->ColumnsPanel->GetHeight()), true, true);
		elseif($text instanceof ColumnHeader)
		{
			$this->Columns->Add($column = &$text, true);
			if($text->GetLeft() == System::Auto)
				$text->SetLeft($tmpCount > 0?$this->Columns[$count-1]->GetRight():0);
//			$text->SetLeft(0);
		}
//		$column->SetLayout(Layout::Relative);
//		$column->CSScssFloat = 'left';
//		if(($right = $column->GetRight()) > $this->GetWidth())
		$right = $column->GetRight();
		$this->InnerPanel->SetWidth($right);
		$this->MakeColumnShift($column);
		$column->SetListView($this->Id);
		$this->ColumnsPanel->BringToFront();
		$column->SizeHandle->ShiftStart = new ClientEvent("_NLVResizeStart('{$this->Line->Id}', '$column->Id', '{$this->InnerPanel->Id}');");
//		$column->SizeHandle->ShiftStart = new ClientEvent("_NLVResizeStart('{$this->Line->Id}', '$column->Id', '{$this->InnerPanel->Id}');return;");
		$column->SizeHandle->ShiftStop = new ClientEvent('_NLVResizeEnd');
//		$this->Line->Shifts[] = Shift::With($column->SizeHandle, Shift::Left);
		$this->Line->Shifts[] = Shift::LeftWith($column->SizeHandle, Shift::Left);
		
		foreach($this->LVItemsQueue as $key => $listViewItem)
			if($this->Update($listViewItem))
				unset($this->LVItemsQueue[$key]);
	}
	protected function MakeColumnShift($column)
	{
		if(($count = $this->Columns->Count) > 1){
			//$column->Shifts[] = Shift::LeftWith($this->Columns[$count - 2]);//, Shift::Mirror, 1, null, -1);
//			$column->Shifts[] = Shift::With($this->Columns[$count - 2], Shift::Left);//, Shift::Mirror, 1, null, -1);
			$column->Shifts[] = Shift::LeftWith($this->Columns[$count - 2], Shift::Width);//, Shift::Mirror, 1, null, -1);
			$column->Shifts[] = Shift::LeftWith($this->Columns[$count - 2]);//, Shift::Mirror, 1, null, -1);
//			$column->Shifts[] = Shift::LeftWith($this->Line);//, Shift::Mirror, 1, null, -1);
		}
		$column->SizeHandle->Shifts[] = Shift::Left($column->SizeHandle);
		$column->SizeHandle->Shifts[] = Shift::Width($column);
	}
	/**
	 * @ignore
	 */
	function AddListViewItem($listViewItem)
	{
		if(!$listViewItem instanceof ListViewItem)
		{
			if(is_array($listViewItem) && $this->RowCallback)
			{
				$previousBound = Event::$BoundData;
				if(isset($GLOBALS['_NLVCols']))
					$previousCols = isset($GLOBALS['_NLVCols'])?$GLOBALS['_NLVCols']:null;
						
				$GLOBALS['_NLVCols'] = $this->DataColumns;
				Event::$BoundData = $listViewItem;
				$listViewItem = $this->RowCallback->Exec();
				Event::$BoundData = $previousBound;
				$GLOBALS['_NLVCols'] = $previousCols;
				
				if(is_array($listViewItem))
				{
					$this->InsertListViewItem($listViewItem[0], $listViewItem[1]);
					return;
				}
			}
			else
				$listViewItem = new ListViewItem($listViewItem);
		}
		$this->ListViewItems->Add($listViewItem, true);
		$this->SetItemProperties($listViewItem);
		/*
		if($tmpSubItemCount > $tmpColCount)
			if(empty($this->LVItemsQueue["{$listViewItem->Id}"])) 
				$this->LVItemsQueue["{$listViewItem->Id}"] = $listViewItem;*/
	}
	/**
	 * @ignore
	 */
	function InsertListViewItem($listViewItem, $idx)
	{
		$this->ListViewItems->Insert($listViewItem, $idx, true);
		$this->SetItemProperties($listViewItem);
	}
	/**
	 * @ignore
	 */
	function Update(ListViewItem $listViewItem=null, $startColumn=null/*, $addToQueue=true*/)
	{
		return true;
		/*//Need to change this function to allow for more optimized adding of subcolumns,
		//Currently it iterates throught all subcolumns, but should only iterate through NEW subcolumns
		$tmpColCount = $this->Columns->Count();
		$tmpIndex = $this->ListViewItems->IndexOf($listViewItem);
		if($tmpIndex != -1)//null))
		{
			$tmpSubItemCount = $listViewItem->SubItems->Count();
			for($i=0; ($i<$tmpSubItemCount && $i < $tmpColCount); ++$i)
			{
				$this->BodyPanels[$i]->Controls[$tmpIndex] = $listViewItem->SubItems[$i];
				$this->BodyPanels[$i]->Controls[$tmpIndex]->SetLeft(0);
				}
			}
			if($tmpSubItemCount <= $tmpColCount)
				return true;
		}
		return false;*/
	}
	//Function will Consolidate adding parts of Update() and AddListViewItem()
	private function SetItemProperties(ListViewItem $listViewItem)
	{
		$subItemCount = $listViewItem->SubItems->Count();
		$colCount = $this->Columns->Count();
		$listViewItem->SetListView($this);
		if($colCount > 0 && $subItemCount > 0)
		{
			for($i=0;$i<$subItemCount && $i < $colCount;++$i)
			{
				if($listViewItem->SubItems->Elements[$i] !== null)
				{
					$listViewItem->SubItems->Elements[$i]->SetLeft($this->Columns->Elements[$i]->GetLeft());
					$listViewItem->SubItems->Elements[$i]->SetWidth($this->Columns->Elements[$i]->GetWidth());
				}
			}
		}
		if($this->Selectable)
		{
			$listViewItem->UpdateEvent('Click');
			NolohInternal::SetProperty('SelCls', $this->SelectCSS, $listViewItem);
		}
	}
	private function ModifyScroll()
	{
		$this->BodyPanelsHolder->Scroll = new ClientEvent("_NLVModScroll('{$this->BodyPanelsHolder->Id}', '{$this->ColumnPanel->Id}', '{$this->InnerPanel->Id}');");
	}
	/**
	 * @ignore
	 */
	public function ClearListViewItems()
	{
		$this->ListViewItems->Clear(true);
		$this->LVItemsQueue = array();
	}
	public function Clear()
	{
		$this->ClearListViewItems(false);
		$this->Columns->Clear();
	}
	function GetDataFetch()
	{
		//$this->SetCursor($this->BodyPanelsHolder->GetCursor());
		return $this->GetEvent('DataFetch');
	}	
	function SetDataFetch($newEvent)
	{
		$this->SetEvent($newEvent, 'DataFetch');
	}
	/**
	 * @ignore
	 */
	function Show()
	{
		AddNolohScriptSrc('ListView.js');
		parent::Show();
	}
	public function GetApproxCount()	{return $this->ApproxCount;}
	public function Bind($dataSource=null, $constraints=null, $limit=50, $offset=0, $rowCallback=null, $storeInMemory = false)
	{
		$data = null;
		if($dataSource != null)
		{
			$this->Clear();
			$this->DataSource = $dataSource;
			$sql = preg_replace('/(.*?);*?\s*?\z/i', '$1', $dataSource->GetSqlStatement());
			$numRows = new DataCommand($dataSource->GetConnection(), 'SELECT count(1) FROM (' . $sql . ') as sub_query ', Data::Num);
			$numRows = $numRows->Execute();
			$numRows = $numRows->Data[0][0];
			if(!$this->HeightSpacer)
			{
				$this->HeightSpacer = new Label('', 0, 0, 1, $numRows * 20);
//				$this->HeightSpacer->SetVisible(false);
				$this->HeightSpacer->ParentId = $this->BodyPanelsHolder->Id;
			}
			else
				$this->HeightSpacer->SetHeight($numRows * 20);
			$this->ApproxCount = $numRows;
			$sql = 'SELECT * FROM (' . $sql . ') as sub_query ';
			$this->DataSource = new DataCommand($dataSource->GetConnection(), $sql, $dataSource->ResultType);
			$this->Limit = $limit;
			$callBack = true;
		}
		else
		{
			$callBack = false;
			$offset = $this->CurrentOffset;
			$limit = $this->Limit;
		}
		if(isset($constraints))
		{
			$this->DataColumns = array();
			$columns = array();
			$count = count($constraints);
			for($i=0; $i < $count; ++$i)
			{
				$properties = array(null, null, System::Auto);
				if(is_array($constraints[$i]))
				{
					$currentProperty = 0;
					//0=>column, 1=>title, 2=>width
					foreach($constraints[$i] as $constraint => $value)
					{
						if(is_string($constraint))
						{
							$constraint = strtolower($constraint);
							if(strtolower($constraint) == 'name')
								$properties[0] = $value;
							elseif(strtolower($constraint) == 'title')
								$properties[1] = $value;
							elseif(strtolower($constraint) == 'width')
								$properties[2] = $value;
						}
						else
							$properties[$currentProperty++] = $value;
					}
				}
				else
					$properties[0] = $properties[1] = $constraints[$i];
				if($properties[1] !== false)
				{
					$this->DataColumns[] = $i;
					$this->AddColumn($properties[1], $properties[2]);
				}
				if($properties[0])
					$columns[] = $properties[0];
			}
		}
		if($this->DataSource instanceof DataCommand)
		{
			if(!$loadIntoMemory)
			{
				$result = preg_replace('/(.*?)\s*(?:(?:OFFSET\s*\d*)|(?:LIMIT\s*\d*)|\s)*?\s*;/i', '$1', $this->DataSource->GetSqlStatement());
				$result .= ' LIMIT ' . $limit . ' OFFSET ' . $offset . ';';
				
				$this->DataSource->SetSqlStatement($result);
				if($callBack)
				{
					if($rowCallback instanceof ServerEvent)
						$this->RowCallback = $rowCallback;
					if($constraints)
						$this->DataSource->Callback(new DataConstraint($columns), $this, 'AddListViewItem');
					else
						$this->DataSource->Callback($this, 'AddListViewItem');
				}
				$data = $this->DataSource->Execute();
				if(count($data->Data) < $limit)
					$this->DataFetch['Bind']->Enabled = false;
				elseif($this->GetDataFetch('Bind')->Blank())
					$this->DataFetch['Bind'] = new ServerEvent($this, 'Bind');
				else
					$this->DataFetch['Bind']->Enabled = true;
			}
			$this->CurrentOffset = $offset + $limit;
		}
		elseif(is_array($dataSource))
		{
		}
		elseif(is_file($dataSource))
		{
			/*if xml
			elseif JSON
			elseif CSV*/
		}
		elseif(false/*URL*/)
		{
			
		}
		if(!isset($constraints) && isset($data->Data[0]) && $callBack && !$rowCallback)
		{
			$columns = array_keys($data->Data[0]);
			$count = count($columns);
			for($i=0; $i<$count; ++$i)
				$this->Columns->Add($columns[$i]);
		}		
	}
	public function Sort($column, $order=true)
	{
		if($column instanceof ColumnHeader)
			$index = $this->Columns->IndexOf($column);
		elseif(is_int($column))
			$index = $column;
		else return;
		$count = $this->Columns->Count;
		for($i=0; $i < $count; ++$i)
		{
			if($this->Columns[$i]->OrderArrow != null)
				$this->Columns[$i]->OrderArrow->SetVisible(false);
		}
		if($this->DataSource != null && !$this->StoredInMemory && $this->DataFetch['Bind']->Enabled)
		{
			$result = preg_replace('/(.*?)\s*(?:(?:OFFSET\s*\d*)|(?:LIMIT\s*\d*)|\s)*?\s*;/i', '$1', $this->DataSource->GetSqlStatement());
			$result = preg_replace('/ ORDER BY (?:[\w"]+(?: ASC| DESC)?(?:, ?)?)+/i', '', $result);
			
			$callBack = $this->DataSource->GetCallback();
			if(isset($callBack['constraint']))
				$sortColumn = '"' . $callBack['constraint']->Columns[$this->DataColumns[$index]] . '"';
			else
				$sortColumn = $index + 1;
			$result .= ' ORDER BY ' . $sortColumn;
			if(!$order)
				$result .= ' DESC';
			$this->DataSource->SetSqlStatement($result);
			$this->ListViewItems->Clear();
			$this->CurrentOffset = 0;
			$this->DataFetch['Bind']->Enabled = true;
			$this->DataFetch['Bind']->Exec();
			//System::Log($result);
			return;
		}		
		$rows = array();
		
		foreach($this->ListViewItems->Elements as $key => $listViewItem)
			$rows[$key] = $listViewItem->SubItems[$index]->GetText();	
		if(!$order)
			asort($rows);
		else
			arsort($rows);
		
		$clientArray = '[';
		foreach($rows as $key => $val)
			$clientArray .= '\'' . $this->ListViewItems->Elements[$key]->Id .'\',';
		
		$clientArray = rtrim($clientArray, ',') . ']';
		QueueClientFunction($this, '_NLVSort', array('"'.$this->InnerPanel->Id.'",'.$clientArray));
	}
	public function Set_NSelectedRows($rows)
	{
		$this->SelectedRows = explode('~d2~', rtrim($rows, '~d2'));
	}
	public function GetSelectedListViewItems()
	{
		$listViewItems = array();
		$count = count($this->SelectedRows);
		for($i=0; $i < $count; ++$i)
			$listViewItems[] = GetComponentById($this->SelectedRows[$i]);
			
		return $listViewItems;
	}
	public function SetSelectable($mode, $cssClass = 'NLVSelect')
	{
		if($mode)
		{
			foreach($this->ListViewItems as $listViewItem)
			{
				$listViewItem->UpdateEvent('Click');
				NolohInternal::SetProperty('SelCls', $cssClass, $listViewItem);
			}
			$this->Selectable = $mode;
			$this->SelectCSS = $cssClass;
		}
	}
}
?>
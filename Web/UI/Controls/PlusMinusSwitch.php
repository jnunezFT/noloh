<?php
/**
 * @package UI
 * @subpackage Controls
 */
class PlusMinusSwitch extends Image
{	
	function PlusMinusSwitch($left=0, $top=0, $width=9, $height=9)
	{
		parent::Image(NOLOHConfig::GetNOLOHPath().'Web/UI/Controls/Images/plus.gif', $left, $top, $width, $height);
		//$this->BackColor = "#FFFFFF";
		//$this->Border = "1px solid #000000";
		//$this->Click = new ClientEvent("var Obj=document.getElementById(" . $this->Id  . "); Obj.checked = !Obj.checked;");
		$this->Click = new ClientEvent("PlusMinusSwitchClick('$this->Id')");
		//$this->Cursor = "default";
	}
	
	function Show()
	{
		parent::Show();
		AddNolohScriptSrc('PlusMinusSwitch.js');
		//require_once($_SERVER['DOCUMENT_ROOT'] . "/NOLOH/Javascripts/PlusMinusSwitchScripts.js");	
		//AddScriptSrc(NOLOHConfig::GetBaseDirectory().NOLOHConfig::GetNOLOHPath()."Javascripts/PlusMinusSwitchScripts.js");
	}
	
	/*
	function Show($IndentLevel=0)
	{
		if($this->Checked == true)
			$this->Text = "-";
		else 
			$this->Text = "+";
		parent::Show($IndentLevel);
		$_SESSION['OnLoadScriptOnce'] .= "document.getElementById(" . $this->Id . 
			").checked = " . $this->Checked . ";\n";
	}
	*/
}

?>
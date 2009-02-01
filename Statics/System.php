<?php
/**
 * System class
 *
 * The System class contains various constants used by various parts of NOLOH, especially constants having to do with the
 * physical properties of controls such as size.
 * 
 * @package Statics
 */
final class System
{
	/**
	 * @ignore
	 */
	private function System(){}
	/**
	 * System::Auto is used to indicate that various properties should figured out their values automatically.
	 * For example:
	 * <pre>
	 * // Creates a label with automatic width and height
	 * $lbl = new Label("This is my string", 0, 0, System::Auto, System::Auto);
	 * // Will Alert the actual width, in pixels, after performing a calculation 
	 * // based on the string and font size
	 * Alert($lbl->Width);
	 * </pre>
	 */
	const Auto = 'Auto';
	/**
	 * System::AutoHtmlTrim is used to indicate that various properties should figured out their values automatically
	 * and any HTML in them should be trimmed out.
	 * For example:
	 * <pre>
	 * // Creates a label with automatic width and height and HTML Trimming
	 * $lbl = new Label("<b>This is my string</b>", 0, 0, System::AutoHtmlTrim, System::AutoHtmlTrim);
	 * // Will Alert the actual width, in pixels, after performing a calculation based on 
	 * // the string and font size, while not considering the bold tags as part of the string.
	 * Alert($lbl->Width);
	 * </pre>
	 */
	const AutoHtmlTrim = 'HtmlTrim';
	/**
	 * System::Full is used to indicate that various properties should expand to accomodate the full control.
	 * For example:
	 * <pre>
	 * // Instantiate a new Panel
	 * $pnl = new Panel();
	 * // Tells the Panel to not cut off its contents, hence, the panel's width and height will be largely ignored.
	 * $pnl->Scrolling = System::Full;
	 * </pre>
	 */
	const Full = 'Full';
	/**
	 * System::Vacuous is used in connection with {@link Control::SetVisible()} to indicate that the control will not
	 * take up space. This is similar to false except that if either static or relative Layout is used, the
	 * control will not occupy space.
	 * <pre>
	 * // Add a new Button
	 * $this->Controls->Add($btn1 = new Button());
	 * // Add another new Button
	 * $this->Controls->Add($btn2 = new Button());
	 * // Position them both statically
	 * $btn1->Layout = $btn2->Layout = 2;
	 * // Make the first button vacuous
	 * $btn1->Visible = System::Vacuous;
	 * // Now $btn2 will be on the left side of the screen, not to the right of an invisible object
	 * </pre>
	 */
	const Vacuous = null;
	/**
	 * System::Unhandled is used in connection with {@link SetStartUpPage} as the fifth, $debugMode, parameter to 
	 * indicate that NOLOH's error handling will be disabled and regular crashing behavior will occur in case of an error.
	 */
	const Unhandled = 'Unhandled';
	/**
	 * System::Horizontal is used in connection with the Scrolling property to indicate the presence of Horizontal scrollbars.
	 */
	const Horizontal = 'horizontal';
	/**
	 * System::Horizontal is used in connection with the Scrolling property to indicate the presence of Horizontal scrollbars.
	 */
	const Vertical = 'vertical';
	/**
	 * @ignore
	 */
	const Kernel = 'Kernel';
	/**
	 * @ignore
	 */
	static function LogFormat($what, $addQuotes=false)
	{
		if(is_object($what))
			return (string)$what . ' ' . get_class($what) . ' object';
		elseif(is_array($what))
		{
			$text = 'array(';
			foreach($what as $key => $val)
				$text .= $key . ' => ' . self::LogFormat($val, true) . ', ';
			return rtrim($text,', ') . ')';
		}
		elseif(!is_string($what) || $addQuotes)
			return ClientEvent::ClientFormat($what);
		return $what;
	}
	/**
	 * System::Log will log any piece of information to a debug window, along with a system timestamp. This function is useful for debugging.
	 * @param mixed $what The information to be logged
	 * @param boolean $toFireBug If set to true and the user has the Firebug extension for Firefox, Log will alternatively log to the console.
	 */
	static function Log($what, $toFireBug=false)
	{
		if($GLOBALS['_NDebugMode'])
			if($toFireBug === true)
			{
				if(UserAgent::GetBrowser() === 'ff')
					AddScript('try{console.log(' . ClientEvent::ClientFormat($text) . ');} catch(e){};');
			}
			elseif($GLOBALS['_NDebugMode'])
			{
				$webPage = WebPage::That();
				$debugWindow = $webPage->DebugWindow;
				if($debugWindow)
				{
					$display = $debugWindow->Controls['Display'];
					$old = true;
				}
				else
				{
					$debugWindow = $webPage->DebugWindow = new WindowPanel('Debug', 500, 0, 400, 300);
					$display = $debugWindow->Controls['Display'] = new MarkupRegion('', 0, 0, null, null);
					//$display->CSSFontFamily = 'consolas, monospace';
					$old = false;
					$debugWindow->Buoyant = true;
				}
				$debugWindow->ParentId = $webPage->Id;
				$debugWindow->Visible = true;
				$stamp = date('h:i:s') . substr(microtime(), 1, 5);
				$display->Text .= ($old?'<BR>':'') . '<SPAN style="font-weight:bold; font-size: 8pt;">' . $stamp . '</SPAN>: ' . self::LogFormat($what);
				if(!isset($GLOBALS['_NDebugScrollAnim']))
				{
					Animate::ScrollTop($debugWindow->BodyPanel, Layout::Bottom);
					$GLOBALS['_NDebugScrollAnim'] = true;
				}
			}
	}
	/**
 	* Returns the full system path to NOLOH
 	* @return string
 	*/
	static function NOLOHPath()		{return $_SESSION['_NPath'];}
	/**
 	* Returns the relative system path to NOLOH
 	* @return string
 	*/
	static function RelativePath()	{return $_SESSION['_NRPath'];}
	static function ImagePath()		{return self::RelativePath() . '/Images/';}
}

?>
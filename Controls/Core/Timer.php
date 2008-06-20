<?php
/**
 * Timer class
 *
 * The Timer Component gives the developer the ability to have events launch after a certain period of time has elapsed.<br>
 * It may either launch the event once, or continue launching it periodically, depending on the value of the $Repeat property.<br>
 * <code>
 * function Init()
 * {
 * 	// Creates a Timer that will execute every time 5 seconds expires
 * 	$timer = new Timer(5000, true);
 * 	// Tells the Timer to execute the AlertIt Event on this object every time the 5 seconds expires
 *  $timer->Elapsed = new ServerEvent($this, "AlertIt");
 * }
 * function AlertIt()
 * {
 * 	Alert("5 seconds has passed");
 * }
 * </code>
 * <b>Note</b>: Timer currently has a number of known issues. For instance, if a Timer is added to a Panel, and then that Panel is removed,
 * the Timer will still tick. It is marked for major development in the near future to dodge these issues.
 * @package Controls/Core
 */
class Timer extends Component
{
	private $Interval;
	//private $Elapsed;
	private $Repeat;
	/**
	 * Constructor. 
	 *
	 * @param integer $interval Specifies the number of miliseconds until event executes
	 * @param boolean $repeats Indicates whether or not the Event will keep executing periodically
	 * @return Timer
	 */
	function Timer($interval, $repeats=false)
	{
		parent::Component();
		$this->Interval = $interval;
		$this->Repeat = $repeats;
	}
	/**
	 * Gets the number of miliseconds until event executes
	 * @return integer
	 */
	function GetInterval()
	{
		return $this->Interval;
	}
	/**
	 * Sets the number of miliseconds until event executes
	 * @param integer $newInterval
	 */
	function SetInterval($newInterval)
	{
		$this->Interval = $newInterval;
		$this->Show();
	}
	/**
	 * Gets the Event associated with the Timer elapsing
	 * @return Event
	 */
	function GetElapsed()
	{
		return $this->GetEvent('Elapsed');
	}
	/**
	 * Sets the Event associated with the Timer elapsing
	 * @param Event $newElapsed
	 */
	function SetElapsed($newElapsed)
	{
		$this->SetEvent($newElapsed, 'Elapsed');
	}
	/**
	 * Gets whether or not the Elapsed Event will execute periodically or just once
	 * @return boolean
	 */
	function GetRepeat()
	{
		return $this->Repeat;
	}
	/**
	 * Sets whether or not the Elapsed Event will execute periodically or just once
	 * @param boolean $newRepeat
	 */
	function SetRepeat($newRepeat)
	{
		if($this->Repeat != $newRepeat)
		{
			$this->Repeat = $newRepeat;
			$this->Show();
		}
	}
	/**
	 * @ignore
	 */
	function UpdateEvent($eventType)
	{
		QueueClientFunction($this, 'NOLOHChangeByObj', array('window.'.$this->Id, '\'onelapsed\'', '\''.$this->GetEvent($eventType)->GetEventString($eventType,$this->Id).'\''));
	}
	/**
	 * Stops the timer from running.
	 */
	function Stop()
	{
		if($this->GetShowStatus != 0)
			AddScript('clear' . ($this->Repeat?'Interval':'Timeout') . '(window.'.$this->Id.'.timer)');
	}
	/**
	 * Resets the timer. That is, regardless of how close the timer was to completing its interval, it will start over.
	 */
	function Reset()
	{
		$this->Show();
	}
	/**
	 * @ignore
	 */
	function Show()
	{
		parent::Show();
		$ref = "window.$this->Id";
		AddScript("$ref=new Object();$ref.timer=set" . ($this->Repeat?'Interval':'Timeout')
			. "('if($ref.onelapsed!=null) $ref.onelapsed.call();'," . $this->Interval . ');');
	}
	/**
	 * @ignore
	 */
	function Hide()
	{
		$this->Stop();
		parent::Hide();
	}
}

?>
function ShiftStart(event, objArray)
{
	var xPos, yPos, obj;
	thisObjArray = objArray;
	thisObjArray.Ghosts = Array();
	thisObjArray.HasMoved = false;
	
	xPos = event.clientX + window.scrollX;
	yPos = event.clientY + window.scrollY;
	 
	thisObjArray.CursorStartX = xPos;
	thisObjArray.CursorStartY = yPos;
	var tmpCount = thisObjArray.length;
	for(var i=0; i<tmpCount; ++i)
	{
		if(thisObjArray[i][2] == 1)
		{
			obj = document.getElementById(thisObjArray[i][0]).cloneNode(true);
			obj.style.position = "absolute";
			obj.style.left = FindX(thisObjArray[i][0]) + "px";
			obj.style.top = FindY(thisObjArray[i][0]) + "px";
			obj.style.opacity = ".5";
			thisObjArray[i][0] = obj.id = thisObjArray[i][0] + "_Ghost";
			document.body.appendChild(obj);
			thisObjArray.Ghosts[thisObjArray.Ghosts.length] = i;
		}
		else
			obj = document.getElementById(thisObjArray[i][0]);
		_NShiftInitObject(thisObjArray[i], obj);
		SetShiftWithInitials(obj);
//		BringToFront(thisObjArray[i][0]);
		BringToFront(obj.id);
	}
	document.addEventListener("mousemove", ShiftObj, true);
    document.addEventListener("mouseup",   ShiftStop, true);
    event.preventDefault();
}
function _NShiftInitObject(info, obj)
{
	info.StartWidth = obj.offsetWidth;
	info.StartHeight = obj.offsetHeight;
	info.StartLeft = obj.offsetLeft;
	info.StartTop = obj.offsetTop;
	if(isNaN(info.StartHeight))
		info.StartHeight = 20;
	if(isNaN(info.StartTop))
		info.StartTop = yPos;
}
function SetShiftWithInitials(obj)
{	
	if(obj.ShiftsWith != null)
	{
		var tmpCount = obj.ShiftsWith.length;
		var subObject;
		for(var j=0; j<tmpCount; ++j)
		{
			subObject = document.getElementById(obj.ShiftsWith[j][0]);
			_NShiftInitObject(obj.ShiftsWith[j], subObject);
			SetShiftWithInitials(subObject);
		}
	}
}
function ShiftObj(event)
{
	var xPos, yPos;
	var deltaX, deltaY;

	xPos = event.clientX + window.scrollX;
    yPos = event.clientY + window.scrollY;
	deltaX = xPos - thisObjArray.CursorStartX;
	deltaY = yPos - thisObjArray.CursorStartY;
	
	thisObjArray.HasMoved = true;
	ShiftObjects(thisObjArray, deltaX, deltaY);
	event.preventDefault();
}
function ShiftObjects(objects, deltaX, deltaY)
{
	var tmpCount = objects.length;
	for(var i=0; i<tmpCount; ++i)
	{
		if(objects[i][1] <= 3)
		{
			if(objects[i][1] != 1)
				ShiftObject(objects[i], "style.height", objects[i].StartHeight, deltaY , objects[i][5], objects[i][6]);
			if(objects[i][1] != 2)	
				ShiftObject(objects[i], "style.width", objects[i].StartWidth, deltaX , objects[i][3], objects[i][4]);
		}
		else
		{
			if(objects[i][1] != 4)
				ShiftObject(objects[i], "style.top", objects[i].StartTop, deltaY , objects[i][5], objects[i][6]);
			if(objects[i][1] != 5)
				ShiftObject(objects[i], "style.left", objects[i].StartLeft, deltaX , objects[i][3], objects[i][4]);
		}
		var tmpObj = document.getElementById(objects[i][0]);
		if(tmpObj.ShiftsWith != null)
			ShiftObjects(tmpObj.ShiftsWith, deltaX, deltaY);
	}
}
function ShiftObject(object, property, start, delta, minBound, maxBound)
{
	var finalCoord = Math.round(start + delta * object[7]);
	ChangeAndSave(object[0], property, (minBound != null && finalCoord <= minBound ? minBound : (maxBound != null && finalCoord >= maxBound ? maxBound : finalCoord))+"px");	
}
function ShiftStop(event)
{
	var tmpCount;
	if(thisObjArray.HasMoved)
	{
		var Catcher, CatcherLeft, CatcherTop, DroppedX, DroppedY, j;
		DroppedX = event.clientX + window.scrollX;
		DroppedY = event.clientY + window.scrollY;
		tmpCount = NOLOHCatchers.length;
		for(var i=0; i<tmpCount; ++i)
			if(IsAvailable(NOLOHCatchers[i]))
			{
				Catcher = document.getElementById(NOLOHCatchers[i]);
				CatcherX = FindX(NOLOHCatchers[i]);
				CatcherY = FindY(NOLOHCatchers[i]);
				if(DroppedX >= CatcherX && DroppedX < CatcherX + (Catcher.style.width==""?80:parseInt(Catcher.style.width,10)) && DroppedY >= CatcherY && DroppedY < CatcherY + (Catcher.style.height==""?20:parseInt(Catcher.style.height,10)))
					for(j=0; j<thisObjArray.length; ++j)
						if(4 <= thisObjArray[j][1] && thisObjArray[j][1] <= 6 && NOLOHCatchers[i]!=thisObjArray[j][0].replace("_Ghost",""))
							NOLOHCaught.push(thisObjArray[j][0].replace("_Ghost",""));
				if(NOLOHCaught.length != 0)
				{
					Catcher.DragCatch.call();
					NOLOHCaught = Array();
				}
			}
	}
	tmpCount = thisObjArray.Ghosts.length;
	for(i=0; i<tmpCount; ++i)
	{
		j = thisObjArray.Ghosts[i];
		document.body.removeChild(document.getElementById(thisObjArray[j][0]));
		thisObjArray[j][0] = thisObjArray[j][0].replace("_Ghost", "");
	}
	if(!thisObjArray.HasMoved)
	{
		var obj;
		tmpCount = thisObjArray.length;
		for(i=0; i<tmpCount; ++i)
		{
			obj = document.getElementById(thisObjArray[i][0]);
			if(obj.onclick != null)
				obj.onclick.call(obj, event);
		}
	}
	thisObjArray = null;
	document.removeEventListener("mousemove", ShiftObj, true);
	document.removeEventListener("mouseup", ShiftStop, true);
}
function AddShiftWith(objectId, info)
{
	var tmpObj = document.getElementById(objectId);
	if(tmpObj.ShiftsWith == null)
		tmpObj.ShiftsWith = new Array(info);
	else	
		tmpObj.ShiftsWith[tmpObj.ShiftsWith.length] = info;
}
function ChangeShiftType(objectId, index, newType)
{
	document.getElementById(objectId).Shifts[index][1] = newType;
}
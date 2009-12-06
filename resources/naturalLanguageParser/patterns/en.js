/**
 * English/American patterns
 */

NaturalLanguageParser.l10n = {
	invalidMonth: 'Invalid month string',
	ambiguousMonth: 'Ambiguous month',
	invalidWeekday: 'Invalid weekday string',
	ambiguousWeekday: 'Ambiguous weekday',
	invalidWeekdayValue: 'Invalid month value [only 1 to 12]',
	invalidMonthValue: 'Invalid weekday value [only 1 to 31]',
	invalidDateString: 'Inserted string could not be parsed'
};

NaturalLanguageParser.prototype._monthNames = [
	'January',
	'February',
	'March',
	'April',
	'May',
	'June',
	'July',
	'August',
	'September',
	'October',
	'November',
	'December'
];

NaturalLanguageParser.prototype._weekdayNames = [
	'Sunday',
	'Monday',
	'Tuesday',
	'Wednesday',
	'Thursday',
	'Friday',
	'Saturday'
];

NaturalLanguageParser.prototype._dateParsePatterns = [
	// Today/Now
	{
		re: /^tod|^now/i,
		handler: function(db, bits) {
			return new Date();
		}
	},
	// Tomorrow
	{
		re: /^tom/i,
		handler: function(db, bits) {
			var d = new Date();
			d.setDate(d.getDate() + 1);
			return d;
		}
	},
	// Yesterday
	{
		re: /^yes/i,
		handler: function(db, bits) {
			var d = new Date();
			d.setDate(d.getDate() - 1);
			return d;
		}
	},
	// 4th
	{
		re: /^(\d{1,2})(?:st|nd|rd|th)?$/i,
		handler: function(db, bits) {
			var d = new Date();
			var yyyy = d.getFullYear();
			var dd = parseInt(bits[1]);
			var mm = d.getMonth();

			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// 4th of Jan (4th Jan)
	{
		re: /^(\d{1,2})(?:st|nd|rd|th)? (?:of\s)?(\w+)$/i,
		handler: function(db, bits) {
			var d = new Date();
			var yyyy = d.getFullYear();
			var dd = parseInt(bits[1]);
			var mm = db.parseMonth(bits[2]);

			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// 4th of Jan, 2009 (4th Jan 2009)
	{
		re: /^(\d{1,2})(?:st|nd|rd|th)? (?:of )?(\w+),? (\d{4})$/i,
		handler: function(db, bits) {
			var yyyy = parseInt(bits[3]);
			var dd = parseInt(bits[1]);
			var mm = db.parseMonth(bits[2]);

			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// Jan 4th
	{
		re: /^(\w+) (\d{1,2})(?:st|nd|rd|th)?$/i,
		handler: function(db, bits) {
			var d = new Date();
			var yyyy = d.getFullYear();
			var dd = parseInt(bits[2]);
			var mm = db.parseMonth(bits[1]);

			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// Jan 4th, 2003 (Jan 4th 2003)
	{
		re: /^(\w+) (\d{1,2})(?:st|nd|rd|th)?,? (\d{4})$/i,
		handler: function(db, bits) {
			var yyyy = parseInt(bits[3]);
			var dd = parseInt(bits[2]);
			var mm = db.parseMonth(bits[1]);

			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// Next Week, Last Week, Next Month, Last Month, Next Year, Last Year
	{
		re: /^((next|last)\s(week|month|year))$/i,
		handler: function(db, bits) {
			var objDate = new Date();

			var dd = objDate.getDate();
			var mm = objDate.getMonth();
			var yyyy = objDate.getFullYear();

			switch (bits[3]) {
			  case 'week':
				var newDay = (bits[2] == 'next') ? (dd + 7) : (dd - 7);
				objDate.setDate(newDay);
				break;
			  case 'month':
				var newMonth = (bits[2] == 'next') ? (mm + 1) : (mm - 1);
				objDate.setMonth(newMonth);
				break;
			  case 'year':
				var newYear = (bits[2] == 'next') ? (yyyy + 1) : (yyyy - 1);
				objDate.setYear(newYear);
				break;
			}

			return objDate;
		}
	},
	// 2 years from now , 3 days ago
	{
		re: /^(\d{1,2}|one|two|three|four|five|six|seven|eight|nine|ten|eleven|twelve|thirteen|fourteen|fifteen|sixteen|seventeen|eighteen|nineteen|twenty) (day|week|month|year)s? (from|ago)(\s(today|now))?$/i,
		handler: function(db, bits) {
			var objDate = new Date();
			var dd = objDate.getDate();
			var mm = objDate.getMonth();
			var yyyy = objDate.getFullYear();

			if(isNaN(bits[1])) {
			  switch (bits[1]) {
				case 'one': bits[1] = 1; break;
				case 'two': bits[1] = 2; break;
				case 'three': bits[1] = 3; break;
				case 'four': bits[1] = 4; break;
				case 'five': bits[1] = 5; break;
				case 'six': bits[1] = 6; break;
				case 'seven': bits[1] = 7; break;
				case 'eight': bits[1] = 8; break;
				case 'nine': bits[1] = 9; break;
				case 'ten': bits[1] = 10; break;
				case 'eleven': bits[1] = 11; break;
				case 'twelve': bits[1] = 12; break;
				case 'thirteen': bits[1] = 13; break;
				case 'fourteen': bits[1] = 14; break;
				case 'fifteen': bits[1] = 15; break;
				case 'sixteen': bits[1] = 16; break;
				case 'seventeen': bits[1] = 17; break;
				case 'eighteen': bits[1] = 18; break;
				case 'nineteen': bits[1] = 19; break;
				case 'twenty': bits[1] = 20; break;
			  }
			}

			var number = parseInt(bits[1], 10);
			var period = bits[2];
			var direction = bits[3];

			var newDay = '';
			switch (period) {
			  case 'day':
				newDay = (direction == 'ago') ? (dd - number) : (dd + number);
				objDate.setDate(newDay);
				break;
			  case 'week':
				newDay = (direction == 'ago') ? (dd - (number*7)) : (dd + (number*7));
				objDate.setDate(newDay);
				break;
			  case 'month':
				var newMonth = (direction == 'ago') ? (mm - number) : (mm + number);
				objDate.setMonth(newMonth);
				break;
			  case 'year':
				var newYear = (direction == 'ago') ? (yyyy - number) : (yyyy + number);
				objDate.setYear(newYear);
				break;
			}

			return objDate;
		}
	},
	// next tuesday
	// this mon, tue, wed, thu, fri, sat, sun
	// mon, tue, wed, thu, fri, sat, sun
	{
		re: /^(next|this)?\s?(\w+)$/i,
		handler: function(db, bits) {
			var d = new Date();
			var day = d.getDay();
			var newDay = db.parseWeekday(bits[2]);
			var addDays = newDay - day;
			if (newDay <= day) {
				addDays += 7;
			}
			d.setDate(d.getDate() + addDays);
			return d;
		}
	},
	// last Tuesday
	{
		re: /^last (\w+)$/i,
		handler: function(db, bits) {
			var d = new Date();
			var wd = d.getDay();
			var nwd = db.parseWeekday(bits[1]);

			// determine the number of days to subtract to get last weekday
			var addDays = (-1 * (wd + 7 - nwd)) % 7;

			// above calculate 0 if weekdays are the same so we have to change this to 7
			if (0 == addDays) {
			  addDays = -7;
			}

			// adjust date and return
			d.setDate(d.getDate() + addDays);
			return d;
		}
	},
	// mm/dd/yyyy (American style)
	{
		re: /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/,
		handler: function(db, bits) {
			var mm = parseInt(bits[1]);
			var dd = parseInt(bits[2]);

			if ((mm - 1) > 12) {
				var real_day = mm;
				var real_month = dd;

				mm = real_month;
				dd = real_day;
			}

			mm -= 1;

			var yyyy = parseInt(bits[3], 10);

			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// mm/dd/yy (American style) short year
	{
		re: /^(\d{1,2})\/(\d{1,2})\/(\d{1,2})$/,
		handler: function(db, bits) {
			var d = new Date();
			var yyyy = d.getFullYear() - (d.getFullYear() % 100) + parseInt(bits[3]);
			var dd = parseInt(bits[2]);
			var mm = parseInt(bits[1]) - 1;

			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// mm/dd (American style) omitted year
	{
		re: /^(\d{1,2})\/(\d{1,2})$/,
		handler: function(db, bits) {
			var d = new Date();
			var yyyy = d.getFullYear();
			var dd = parseInt(bits[2], 10);
			var mm = parseInt(bits[1], 10) - 1;

			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// mm-dd-yyyy
	{
		re: /^(\d{1,2})-(\d{1,2})-(\d{4})$/,
		handler: function(db, bits) {
			var yyyy = parseInt(bits[3], 10);
			var dd = parseInt(bits[2], 10);
			var mm = parseInt(bits[1], 10) - 1;
			
			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// mm.dd.yyyy
	{
		re: /^(\d{1,2})\.(\d{1,2})\.(\d{4})$/,
		handler: function(db, bits) {
			var mm = parseInt(bits[1], 10);
			var dd = parseInt(bits[2], 10);

			mm -= 1;
			var yyyy = parseInt(bits[3], 10);

			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// yyyy-mm-dd (ISO style) + yyyy.mm.dd
	{
		re: /^(\d{4})[-|\.](\d{1,2})[-|\.](\d{1,2})$/,
		handler: function(db, bits) {
			var yyyy = parseInt(bits[1], 10);
			var dd = parseInt(bits[3], 10);
			var mm = parseInt(bits[2], 10) - 1;

			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// yy-mm-dd (ISO style) short year + yy.mm.dd
	{
		re: /^(\d{1,2})[-|\.](\d{1,2})[-|\.](\d{1,2})$/,
		handler: function(db, bits) {
			var d = new Date();

			var yyyy = d.getFullYear() - (d.getFullYear() % 100) + parseInt(bits[1], 10);
			var dd = parseInt(bits[3], 10);
			var mm = parseInt(bits[2], 10) - 1;

			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// mm-dd (ISO style) omitted year
	{
		re: /^(\d{1,2})-(\d{1,2})$/,
		handler: function(db, bits) {
			var d = new Date();
			var yyyy = d.getFullYear();
			var dd = parseInt(bits[2], 10);
			var mm = parseInt(bits[1], 10) - 1;

			return db.getDateObj(yyyy, mm, dd);
		}
	}
];

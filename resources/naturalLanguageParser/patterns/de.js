/**
 * German patterns
 */

NaturalLanguageParser.l10n = {
	invalidMonth: 'Monat existiert nicht',
	ambiguousMonth: 'Monat ist mehrdeutig',
	invalidWeekday: 'Wochentag existiert nicht',
	ambiguousWeekday: 'Wochentag ist mehrdeutig',
	invalidWeekdayValue: 'Wochentag existiert nicht [nur 1 bis 31]',
	invalidMonthValue: 'Monat existiert nicht [nur 1 bis 12]',
	invalidDateString: 'Eingabe konnte nicht analysiert werden'
};

NaturalLanguageParser.prototype._monthNames = [
	'Januar',
	'Februar',
	'März',
	'April',
	'Mai',
	'Juni',
	'Juli',
	'August',
	'September',
	'Oktober',
	'November',
	'Dezember'
];

NaturalLanguageParser.prototype._weekdayNames = [
	'Sonntag',
	'Montag',
	'Dienstag',
	'Mittwoch',
	'Donnerstag',
	'Freitag',
	'Samstag'
];

NaturalLanguageParser.prototype._dateParsePatterns = [
	// Heute/Jetzt
	{
		re: /^heu|^jet/i,
		handler: function(db, bits) {
			return new Date();
		}
	},
	// Morgen
	{
		re: /^mor/i,
		handler: function(db, bits) {
			var d = new Date();
			d.setDate(d.getDate() + 1);
			return d;
		}
	},
	// Gestern
	{
		re: /^ges/i,
		handler: function(db, bits) {
			var d = new Date();
			d.setDate(d.getDate() - 1);
			return d;
		}
	},
	// 4te
	{
		re: /^(\d{1,2})(?:te)?$/i,
		handler: function(db, bits) {
			var d = new Date();
			var yyyy = d.getFullYear();
			var dd = parseInt(bits[1]);
			var mm = d.getMonth();

			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// 4te Jan
	{
		re: /^(\d{1,2})(?:te)? ([\w\u00E4]+)$/i,
		handler: function(db, bits) {
			console.log(bits);
			var d = new Date();
			var yyyy = d.getFullYear();
			var dd = parseInt(bits[1]);
			var mm = db.parseMonth(bits[2]);

			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// 4te Jan, 2009 (4te Jan 2009)
	{
		re: /^(\d{1,2})(?:te)? ([\w\u00E4]+),? (\d{4})$/i,
		handler: function(db, bits) {
			var yyyy = parseInt(bits[3]);
			var dd = parseInt(bits[1]);
			var mm = db.parseMonth(bits[2]);

			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// Jan 4te
	{
		re: /^([\w\u00E4]+) (\d{1,2})(?:te)?$/i,
		handler: function(db, bits) {
			var d = new Date();
			var yyyy = d.getFullYear();
			var dd = parseInt(bits[2]);
			var mm = db.parseMonth(bits[1]);

			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// Jan 4te, 2003 (Jan 4th 2003)
	{
		re: /^([\w\u00E4]+) (\d{1,2})(?:te)?,? (\d{4})$/i,
		handler: function(db, bits) {
			var yyyy = parseInt(bits[3]);
			var dd = parseInt(bits[2]);
			var mm = db.parseMonth(bits[1]);

			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// nächste Woche, letzte Woche, nächsten Monat, letzten Monat, nächstes Jahr, letztes Jahr
	{
		re: /^((n[\u00E4]chste|letzte)[snr\s]+?(woche|monat|jahr))$/i,
		handler: function(db, bits) {
			var objDate = new Date();

			var dd = objDate.getDate();
			var mm = objDate.getMonth();
			var yyyy = objDate.getFullYear();

			switch (bits[3]) {
			  case 'woche':
				var newDay = (bits[2] == 'letzte') ? (dd - 7) : (dd + 7);
				objDate.setDate(newDay);
				break;
			  case 'monat':
				var newMonth = (bits[2] == 'letzte') ? (mm - 1) : (mm + 1);
				objDate.setMonth(newMonth);
				break;
			  case 'jahr':
				var newYear = (bits[2] == 'letzte') ? (yyyy - 1) : (yyyy + 1);
				objDate.setYear(newYear);
				break;
			}

			return objDate;
		}
	},
	// 2 jahre von jetzt, vor 3 tagen
	{
		re: /^(?:(vor) )?(\d{1,2}) (tag|woche|monat|jahr)[en\s]*?(?:von)?(\s(heu|jet))?.+$/i,
		handler: function(db, bits) {
			var objDate = new Date();
			var dd = objDate.getDate();
			var mm = objDate.getMonth();
			var yyyy = objDate.getFullYear();

			var number = parseInt(bits[2], 10);
			var period = bits[3];
			var direction = bits[1];

			var newDay = '';
			switch (period) {
			  case 'tag':
				newDay = (direction == 'vor') ? (dd - number) : (dd + number);
				objDate.setDate(newDay);
				break;
			  case 'woche':
				newDay = (direction == 'vor') ? (dd - (number*7)) : (dd + (number*7));
				objDate.setDate(newDay);
				break;
			  case 'monat':
				var newMonth = (direction == 'vor') ? (mm - number) : (mm + number);
				objDate.setMonth(newMonth);
				break;
			  case 'jahr':
				var newYear = (direction == 'vor') ? (yyyy - number) : (yyyy + number);
				objDate.setYear(newYear);
				break;
			}

			return objDate;
		}
	},
	// nächster Dienstag
	{
		re: /^n[\u00E4]chster (\w+)$/i,
		handler: function(db, bits) {
			var d = new Date();
			var day = d.getDay();
			var newDay = db.parseWeekday(bits[1]);
			var addDays = newDay - day;
			if (newDay <= day) {
				addDays += 7;
			}
			d.setDate(d.getDate() + addDays);
			return d;
		}
	},
	// letzter Dienstag
	{
		re: /^letzter (\w+)$/i,
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
	// dd/mm/yyyy (American style)
	{
		re: /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/,
		handler: function(db, bits) {
			var dd = parseInt(bits[1]);
			var mm = parseInt(bits[2]);

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
	// dd/mm/yy (American style) short year
	{
		re: /^(\d{1,2})\/(\d{1,2})\/(\d{1,2})$/,
		handler: function(db, bits) {
			var d = new Date();
			var yyyy = d.getFullYear() - (d.getFullYear() % 100) + parseInt(bits[3]);
			var dd = parseInt(bits[1]);
			var mm = parseInt(bits[2]) - 1;

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
	// dd-mm-yyyy
	{
		re: /^(\d{1,2})-(\d{1,2})-(\d{4})$/,
		handler: function(db, bits) {
			var yyyy = parseInt(bits[3], 10);
			var dd = parseInt(bits[1], 10);
			var mm = parseInt(bits[2], 10) - 1;
			
			return db.getDateObj(yyyy, mm, dd);
		}
	},
	// dd.mm.yyyy
	{
		re: /^(\d{1,2})\.(\d{1,2})\.(\d{4})$/,
		handler: function(db, bits) {
			var dd = parseInt(bits[1], 10);
			var mm = parseInt(bits[2], 10);
			
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
	// dd-mm (ISO style) omitted year
	{
		re: /^(\d{1,2})-(\d{1,2})$/,
		handler: function(db, bits) {
			var d = new Date();
			var yyyy = d.getFullYear();
			var dd = parseInt(bits[1], 10);
			var mm = parseInt(bits[2], 10) - 1;

			return db.getDateObj(yyyy, mm, dd);
		}
	}
];

/////////////////////////////////////////////////////////////////////////////////////
//
// Natural Language Parser - Intuitive Date Input Selection
//
// Based on:
//      Datetime Toolbocks - Intuitive Date Input Selection
//      http://datetime.toolbocks.com
//      Nathaniel Brown - http://nshb.net
//      Email: nshb(at)inimit.com
//      
// Rewritten by:
//      Stefan Galinski <stefan.galinski(at)gmail.com>
//
// License:
//      Modified GNU Lesser General Public License version 2.1
//
// Dependencies:
//      None
/////////////////////////////////////////////////////////////////////////////////////

NaturalLanguageParser = function (options) {
	// default options of natural language parser and jscalendar
	this.options = {
		inputName: 'NaturalLanguageParser',
		elementId: 'NaturalLanguageParser',
		elementIdSuffixInput: '_hr',
		elementIdSuffixCheckbox: '_cb',
		elementIdSuffixMessage: '_msg',
		elementIdSuffixHtmlContainer: '_msgCnt',
		elementIdSuffixHelp: '_help',
		elementIdSuffixButton: '_trigger',
		elementIdSuffixFlat: '_flat',
		classNameError: 'error',
		classNameSuccess: 'success',
		showHelp: true,
		format: 'iso',

		calendarOptions: {
			align: 'Br'
		}
	};

	// merge default options of jscalendar and the natural language parser with the parameter "options"
	for (var calendarAttributeName in options.calendarOptions || {}) {
		this.options.calendarOptions[calendarAttributeName] =
			options.calendarOptions[calendarAttributeName];
	}
	delete options.calendarOptions;

	for (var attributeName in options || {}) {
		this.options[attributeName] = options[attributeName];
	}

	this._formatString = 'yyyy-mm-dd';
};

// current version
NaturalLanguageParser.prototype.VERSION = '1.0.0';

/**
 * Configures the options and evaluates them
 */
NaturalLanguageParser.prototype._configureOptions = function () {
	// set date format of the jscalendar
	this.options.calendarOptions.ifFormat = this.options.format;

	// prepare human readable date format
	this._formatString = this.options.format.replace(/%Y/, 'yyyy').replace(/%d/,
		'dd').replace(/%m/, 'mm').replace(/%H/, 'HH').replace(/%M/, 'MM').replace(/%y/, 'yy');

	// prepare calendar for flat mode?
	if (this.options.calendarOptions.flat) {
		// remove align
		this.options.calendarOptions.remove('align');

		// if the option is set to true, then calculate the element id for the flat calendar
		if (this.options.calendarOptions.flat) {
			this.options.calendarOptions.flat = this.options.elementId + this.options.elementIdSuffixFlat;
		}
	}

	// build elements
	this.options.calendarOptions.inputField = this.options.elementId + this.options.elementIdSuffixInput;
	this.options.messageField = this.options.elementId + this.options.elementIdSuffixMessage;
	this.options.htmlContainer = this.options.elementId + this.options.elementIdSuffixHtmlContainer;

	if (!this.options.calendarOptions.flat) {
		this.options.calendarOptions.button = this.options.elementId + this.options.elementIdSuffixButton;
	}

	if (this.options.showHelp) {
		this.options.helpField = this.options.elementId + this.options.elementIdSuffixHelp;
	}

	// add an additional pattern as a first item that parses the currently wanted format
	var pattern = this.options.format.replace(/%Y/, '(\\d{4})').replace(/%d/,
		'(\\d{1,2})').replace(/%m/, '(\\d{1,2})').replace(/%y/, '(\\d{1,2})');

	var currentFormat = {
		re: new RegExp(pattern, 'i'),
		handler: function (db, bits) {
			// fetch first occurences of the year, month and day format strings
			var occurences = [];
			occurences.month = db.options.format.indexOf('%m');
			occurences.day = db.options.format.indexOf('%d');
			occurences.shortYear = db.options.format.indexOf('%y');
			occurences.year = db.options.format.indexOf('%Y');

			// sort the stuff
			var keyMap = ['month', 'day', 'shortYear', 'year'];
			keyMap.sort(function (a, b) {
				return (occurences[a] - occurences[b]);
			});

			// init variables
			var yyyy = 0;
			var mm = 0;
			var dd = 0;
			var date = new Date();

			// calculate day
			if (occurences.day != -1) {
				dd = parseInt(bits[keyMap.indexOf('day')], 10);
			}

			if (occurences.month != -1) {
				mm = parseInt(bits[keyMap.indexOf('month')], 10) - 1;
			}

			if (occurences.shortYear != -1) {
				yyyy = date.getFullYear() - (date.getFullYear() % 100) +
					parseInt(bits[keyMap.indexOf('shortYear')], 10);
			}

			if (occurences.year != -1) {
				yyyy = parseInt(bits[keyMap.indexOf('year')], 10);
			}

			return db.getDateObj(yyyy, mm, dd);
		}
	};
	this._dateParsePatterns.unshift(currentFormat);
};

/**
 * Initializes the jscalendar
 */
NaturalLanguageParser.prototype._initializeCalendar = function () {
	Calendar.setup(this.options.calendarOptions);
};

/**
 * Adds the key observer, onchange and onclick events to the elements...
 */
NaturalLanguageParser.prototype._attachEvents = function () {
	var naturalLanguageParserInstance = this;

	// kill the default possible default onchange handler of the input field
	var staticInputFieldOnChangeCallback = this.options.calendarOptions.inputField.onchange;
	this.options.calendarOptions.inputField.onchange = '';

	// register on change event on the input field
	var onChangeEventCallback = function (event, naturalLanguageParserInstance) {
		naturalLanguageParserInstance.convertStringToDate();

		if (staticInputFieldOnChangeCallback != undefined) {
			eval(staticInputFieldOnChangeCallback + ";onchange();");
		}
	};

	if (this.options.calendarOptions.inputField.addEventListener) {
		this.options.calendarOptions.inputField.addEventListener(
			'change',
			function (event) {
				onChangeEventCallback(event, naturalLanguageParserInstance);
			},
			false
		);
	} else if (this.options.calendarOptions.inputField.attachEvent) {
		this.options.calendarOptions.inputField.attachEvent(
			'onchange',
			function (event) {
				onChangeEventCallback(event, naturalLanguageParserInstance);
			}
		);
	}

	// register onkeypress event on the input field
	var onKeyPressEventCallback = function (event, naturalLanguageParserInstance) {
		var keyCode = '';
		if (event.keyCode) {
			keyCode = event.keyCode;
		} else {
			keyCode = (event.which ? event.which : event.charCode);
		}

		if (keyCode == 13 || keyCode == 10) {
			naturalLanguageParserInstance.convertStringToDate();

			if (staticInputFieldOnChangeCallback != undefined) {
				eval(staticInputFieldOnChangeCallback + ";onchange();");
			}
			return false;
		}

		return true;
	};

	if (this.options.calendarOptions.inputField.addEventListener) {
		this.options.calendarOptions.inputField.addEventListener(
			'keypress',
			function (event) {
				onKeyPressEventCallback(event, naturalLanguageParserInstance);
			},
			false
		);
	} else if (this.options.calendarOptions.inputField.attachEvent) {
		this.options.calendarOptions.inputField.attachEvent(
			'onkeypress',
			function (event) {
				onKeyPressEventCallback(event, naturalLanguageParserInstance);
			}
		);
	}

	// add the event for when a user clicks on the help icon
	if (this.options.showHelp) {
		var openHelpPage = function (event, naturalLanguageParserInstance) {
			NaturalLanguageParser.windowOpenCenter(
				naturalLanguageParserInstance.options.calendarOptions.helpPage,
				'NaturalLanguageParserHelp'
			);
		};

		this.options.helpField = document.getElementById(this.options.helpField);
		if (this.options.helpField.addEventListener) {
			this.options.helpField.addEventListener(
				'click',
				function (event) {
					openHelpPage(event, naturalLanguageParserInstance);
				},
				false
			);
		} else if (this.options.helpField.attachEvent) {
			this.options.helpField.attachEvent(
				'onclick',
				function (event) {
					openHelpPage(event, naturalLanguageParserInstance);
				}
			);
		}
	}
};

/**
 * Adds the default format message to the message container
 */
NaturalLanguageParser.prototype.setDefaultFormatMessage = function () {
	document.getElementById(this.options.messageField).innerHTML = this._formatString;
};

/**
 * Takes a string, returns the index of the month matching that string,
 * throws an error if 0 or more than 1 matches
 */
NaturalLanguageParser.prototype.parseMonth = function (month) {
	var matches = NaturalLanguageParser.prototype._monthNames.findAll(function (item) {
		return new RegExp("^" + month, "i").test(item);
	});

	if (matches.length == 0) {
		throw new Error(this._formatString + ' - ' + NaturalLanguageParser.l10n.invalidMonth);
	}

	if (matches.length > 1) {
		throw new Error(this._formatString + ' - ' + NaturalLanguageParser.l10n.ambiguousMonth);
	}

	return NaturalLanguageParser.prototype._monthNames.indexOf(matches[0]);
};

/**
 * Same as parseMonth but for days of the week
 */
NaturalLanguageParser.prototype.parseWeekday = function (weekday) {
	var matches = NaturalLanguageParser.prototype._weekdayNames.findAll(function (item) {
		return new RegExp("^" + weekday, "i").test(item);
	});

	if (matches.length == 0) {
		throw new Error(this._formatString + ' - ' + NaturalLanguageParser.l10n.invalidWeekday);
	}

	if (matches.length > 1) {
		throw new Error(this._formatString + ' - ' + NaturalLanguageParser.l10n.ambiguousWeekday);
	}

	return NaturalLanguageParser.prototype._weekdayNames.indexOf(matches[0]);
};

/**
 * Returns the current date format
 */
NaturalLanguageParser.prototype.getFormat = function () {
	var format = '';
	switch (this.options.format) {
		case 'de':
			format = 'mm.dd.yyyy';
			break;

		case 'us':
			format = 'mm/dd/yyyy';
			break;

		case 'iso':
			format = 'yyyy-mm-dd';
			break;

		default:
			format = this.options.format;
			break;
	}

	return format;
};

/**
 * Performs sanity checks on the year, month and day to make sure the date is sane.
 */
NaturalLanguageParser.prototype.dateInRange = function (yyyy, mm, dd) {
	// if month out of range
	if (mm < 0 || mm > 11) {
		throw new Error(this._formatString + ' - ' + NaturalLanguageParser.l10n.invalidMonthValue);
	}

	// if day out of range
	if (dd < 0 || dd > 31) {
		throw new Error(this._formatString + ' - ' + NaturalLanguageParser.l10n.invalidWeekdayValue);
	}

	return true;
};

/**
 * Takes date parameters and returns a javascript date object...
 */
NaturalLanguageParser.prototype.getDateObj = function (yyyy, mm, dd) {
	if (this.dateInRange(yyyy, mm, dd)) {
		var date = new Date();

		date.setDate(1);
		date.setYear(yyyy);
		date.setMonth(mm);
		date.setDate(dd);

		return date;
	}

	return null;
};

/**
 * Takes a string and run it through the dateParsePatterns.
 * The first one that succeeds will return a Date object.
 */
NaturalLanguageParser.prototype.parseDateString = function (inputValue) {
	for (var i = 0; i < this._dateParsePatterns.length; ++i) {
		var re = this._dateParsePatterns[i].re;
		var handler = this._dateParsePatterns[i].handler;
		var bits = re.exec(inputValue);
		if (bits) {
			return handler(this, bits);
		}
	}

	throw new Error(this._formatString + ' - ' + NaturalLanguageParser.l10n.invalidDateString);
};

/**
 * Puts an extra 0 in front of single digit integers.
 */
NaturalLanguageParser.prototype.zeroPad = function (integer) {
	if (integer < 10) {
		return '0' + integer;
	} else {
		return integer;
	}
};

/**
 *  Conversion of the value inside the input field...
 */
NaturalLanguageParser.prototype.convertStringToDate = function () {
	var inputField = this.options.calendarOptions.inputField;
	var messageField = document.getElementById(this.options.messageField);

	try {
		var message = this._formatString;
		if (inputField.value != '') {
			// split date and extract the possible time
			var time = '';
			var date = null;
			var bits = /^(?:(\d{1,2}:\d{1,2}) )?(.+)$/i.exec(inputField.value);

			if (bits.length) {
				if (bits[1] != undefined) {
					time = bits[1].split(':');
				}

				if (bits[2] != undefined) {
					date = this.parseDateString(bits[2]);
				}
			}

			var day = this.zeroPad(date.getDate());
			var month = this.zeroPad(date.getMonth());
			var year = date.getFullYear();
			var hours = this.zeroPad((time[0] ? time[0] : date.getHours()));
			var minutes = this.zeroPad((time[1] ? time[1] : date.getMinutes()));

			// set date
			date.setFullYear(year);
			date.setMonth(month);
			date.setDate(day);
			date.setHours(hours);
			date.setMinutes(minutes);
			inputField.value = date.print(this.options.calendarOptions.ifFormat);

			message = date.toDateString();
		}

		// set human readable date
		messageField.innerHTML = message;
		messageField.className = this.options.classNameSuccess;
	} catch (error) {
		// set error message
		messageField.innerHTML = error.message;
		messageField.className = this.options.classNameError;
	}
};

/**
 * Opens a centered popup window...
 */
NaturalLanguageParser.windowOpenCenter = function (url, name) {
	var width = 500;
	var height = 550;
	var left = parseInt((screen.availWidth / 2) - (width / 2));
	var top = parseInt((screen.availHeight / 2) - (height / 2));
	var windowFeatures = "width=" + width + ",height=" + height +
		",status,resizable,left=" + left + ",top=" + top + "screenX=" +
		left + ",screenY=" + top;

	return window.open(url, name, windowFeatures);
};

/**
 * This method generates a natural language parser!
 *
 * Note: The parameter options must contain a subobject named "calendarOptions" that contains the
 * jscalendar options. Read the documentation of jscalendar for possible configuration options.
 *
 * Natural language parser options:
 *
 * - inputName: name of the input field (default: nlp)
 * - elementId: main part of each calendar id (default: nlp)
 * - elementIdSuffixInput: suffix id of the input field (default: _hr),
 * - elementIdSuffixCheckbox: suffix id of the checkbox field (default: _cb),
 * - elementIdSuffixMessage: suffix id of the message field (default: _msg),
 * - elementIdSuffixHtmlContainer: suffix id of the message container (default: _msgCnt)
 * - elementIdSuffixHelp: suffix id of the help field (default: _help)
 * - elementIdSuffixButton: suffix id of the trigger button (default: _trigger)
 * - elementIdSuffixFlat: suffix id of the flat calendar container (default: _flat; only in flat calendar mode)
 * - classNameError: class name of the error message (default: error)
 * - classNameSuccess: class name of the success message (default: success)
 * - showHelp: show the help button (default: true)
 * - format: input format (default: iso)
 *
 * The format can be one of the following options:
 *
 * - iso
 * - de
 * - us
 * - dd/mm/yyyy
 * - dd-mm-yyyy
 * - mm/dd/yyyy
 * - mm.dd.yyyy
 * - mm-dd-yyyy
 * - yyyy-mm-dd
 *
 * @param options object options of the natural language parser and the jscalendar
 */
NaturalLanguageParser.setup = function (options) {
	var nlp = new NaturalLanguageParser(options);
	nlp._configureOptions();
	nlp._initializeCalendar();
	nlp.setDefaultFormatMessage();
	nlp._attachEvents();
};
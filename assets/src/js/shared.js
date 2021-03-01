// https://tc39.github.io/ecma262/#sec-array.prototype.find
if (!Array.prototype.find) {
	Object.defineProperty(Array.prototype, 'find', {
		value: function (predicate) {
			// 1. Let O be ? ToObject(this value).
			if (this == null) {
				throw TypeError('"this" is null or not defined');
			}

			var o = Object(this);

			// 2. Let len be ? ToLength(? Get(O, "length")).
			var len = o.length >>> 0;

			// 3. If IsCallable(predicate) is false, throw a TypeError exception.
			if (typeof predicate !== 'function') {
				throw TypeError('predicate must be a function');
			}

			// 4. If thisArg was supplied, let T be thisArg; else let T be undefined.
			var thisArg = arguments[1];

			// 5. Let k be 0.
			var k = 0;

			// 6. Repeat, while k < len
			while (k < len) {
				// a. Let Pk be ! ToString(k).
				// b. Let kValue be ? Get(O, Pk).
				// c. Let testResult be ToBoolean(? Call(predicate, T, « kValue, k, O »)).
				// d. If testResult is true, return kValue.
				var kValue = o[k];
				if (predicate.call(thisArg, kValue, k, o)) {
					return kValue;
				}
				// e. Increase k by 1.
				k++;
			}

			// 7. Return undefined.
			return undefined;
		},
		configurable: true,
		writable: true
	});
}

if (!Array.prototype.filter) {
	Array.prototype.filter = function (func, thisArg) {
		'use strict';
		if (!((typeof func === 'Function' || typeof func === 'function') && this))
			throw new TypeError();

		var len = this.length >>> 0,
			res = new Array(len), // preallocate array
			t = this, c = 0, i = -1;

		var kValue;
		if (thisArg === undefined) {
			while (++i !== len) {
				// checks to see if the key was set
				if (i in this) {
					kValue = t[i]; // in case t is changed in callback
					if (func(t[i], i, t)) {
						res[c++] = kValue;
					}
				}
			}
		}
		else {
			while (++i !== len) {
				// checks to see if the key was set
				if (i in this) {
					kValue = t[i];
					if (func.call(thisArg, t[i], i, t)) {
						res[c++] = kValue;
					}
				}
			}
		}

		res.length = c; // shrink down array to proper size
		return res;
	};
}

if (!Array.prototype.every) {
	Array.prototype.every = function (callbackfn, thisArg) {
		'use strict';
		var T, k;

		if (this == null) {
			throw new TypeError('this is null or not defined');
		}

		// 1. Let O be the result of calling ToObject passing the this
		//    value as the argument.
		var O = Object(this);

		// 2. Let lenValue be the result of calling the Get internal method
		//    of O with the argument "length".
		// 3. Let len be ToUint32(lenValue).
		var len = O.length >>> 0;

		// 4. If IsCallable(callbackfn) is false, throw a TypeError exception.
		if (typeof callbackfn !== 'function' && Object.prototype.toString.call(callbackfn) !== '[object Function]') {
			throw new TypeError();
		}

		// 5. If thisArg was supplied, let T be thisArg; else let T be undefined.
		if (arguments.length > 1) {
			T = thisArg;
		}

		// 6. Let k be 0.
		k = 0;

		// 7. Repeat, while k < len
		while (k < len) {

			var kValue;

			// a. Let Pk be ToString(k).
			//   This is implicit for LHS operands of the in operator
			// b. Let kPresent be the result of calling the HasProperty internal
			//    method of O with argument Pk.
			//   This step can be combined with c
			// c. If kPresent is true, then
			if (k in O) {
				var testResult;
				// i. Let kValue be the result of calling the Get internal method
				//    of O with argument Pk.
				kValue = O[k];

				// ii. Let testResult be the result of calling the Call internal method
				// of callbackfn with T as the this value if T is not undefined
				// else is the result of calling callbackfn
				// and argument list containing kValue, k, and O.
				if (T) testResult = callbackfn.call(T, kValue, k, O);
				else testResult = callbackfn(kValue, k, O)

				// iii. If ToBoolean(testResult) is false, return false.
				if (!testResult) {
					return false;
				}
			}
			k++;
		}
		return true;
	};
}

var woongkirLocation = {
	filterLocationData: function (results, fieldPrefix, fieldSuffix, fieldData) {
		var getLocationDataFilter = [];

		if (fieldData.convertInputToSelect && fieldData.convertInputToSelect.fieldFilters) {
			getLocationDataFilter = fieldData.convertInputToSelect.fieldFilters.map(function (item) {
				return {
					key: item,
					value: $('#' + fieldPrefix + '_' + item).val(),
				};
			});
		}

		var options = results.filter(function (result) {
			if (!getLocationDataFilter || !getLocationDataFilter.length) {
				return true;
			}

			return getLocationDataFilter.every(function (locationFilter) {
				return result[locationFilter.key] === locationFilter.value;
			});
		}).map(function (item) {
			return {
				id: item.value,
				text: item.label || item.value,
				selected: $('#' + fieldPrefix + '_' + fieldSuffix).val() === item.value,
			};
		});

		return options;
	},
	onChangeFieldState: function (event) {
		var fieldPrefix = $(event.target).attr('id').replace('_state', '');
		var $fieldCountry = $('#' + fieldPrefix + '_country');

		if ($fieldCountry && $fieldCountry.length && 'ID' !== $fieldCountry.val()) {
			return;
		}

		$.each(woongkirLocation.getFields(['state'], 'omit'), function (fieldSuffix, fieldData) {
			woongkirLocation.getLocationData(fieldSuffix).then(function (results) {
				var options = woongkirLocation.filterLocationData(results, fieldPrefix, fieldSuffix, fieldData);

				$('#' + fieldPrefix + '_' + fieldSuffix).selectWoo({
					data: options,
					placeholder: fieldData.placeholder || '',
				});
			});
		});
	},
	onChangeFieldCity: function (event) {
		var fieldPrefix = $(event.target).attr('id').replace('_city', '');
		var $fieldCountry = $('#' + fieldPrefix + '_country');

		if ($fieldCountry && $fieldCountry.length && 'ID' !== $fieldCountry.val()) {
			return;
		}

		$.each(woongkirLocation.getFields(['city', 'state'], 'omit'), function (fieldSuffix, fieldData) {
			woongkirLocation.getLocationData(fieldSuffix).then(function (results) {
				var options = woongkirLocation.filterLocationData(results, fieldPrefix, fieldSuffix, fieldData);

				$('#' + fieldPrefix + '_' + fieldSuffix).selectWoo({
					data: options,
					placeholder: fieldData.placeholder || '',
				});
			});
		});
	},
	onChangeFieldAddress2: function () {

	},
	getFields: function (filterBy, filterType) {
		var fields = {
			state: {
				onChange: woongkirLocation.onChangeFieldState,
				settingSuffix: 'province',
			},
			city: {
				onChange: woongkirLocation.onChangeFieldCity,
				placeholder: woongkir_params.text.placeholder.city,
				convertInputToSelect: {
					fieldPrefix: true,
					fieldFilters: ['state'],
				},
			},
			address_2: {
				onChange: woongkirLocation.onChangeFieldAddress2,
				placeholder: woongkir_params.text.placeholder.address_2,
				convertInputToSelect: {
					fieldPrefix: true,
					fieldFilters: ['state', 'city'],
				},
				cloneIfNotExist: {
					fieldPrefix: 'calc_shipping',
					fieldFrom: 'postcode',
				},
			},
		};

		if (filterBy) {
			var filtered = {};

			$.each(fields, function (key, fieldData) {
				if ('omit' === filterType && -1 !== filterBy.indexOf(key)) {
					return;
				}

				if ('includes' === filterType && -1 === filterBy.indexOf(key)) {
					return;
				}

				filtered[key] = fieldData;
			});

			return filtered;
		}

		return fields;
	},
	getLocationDataCountry: function () {
		return woongkirLocation.getLocationData('country');
	},
	getLocationDataState: function () {
		return woongkirLocation.getLocationData('state');
	},
	getLocationDataCity: function () {
		return woongkirLocation.getLocationData('city');
	},
	getLocationDataAddress2: function () {
		return woongkirLocation.getLocationData('address_2');
	},
	getLocationData: function (locationType) {
		var dfd = new $.Deferred();
		var dataKey = woongkir_params.json[locationType].key;
		var dataUrl = woongkir_params.json[locationType].url;

		var items = Lockr.get(dataKey);

		if (null === items || typeof items === 'undefined') {
			$.getJSON(dataUrl, { _: new Date().getTime() }, function (data) {
				data.sort(function (a, b) {
					return (a.value > b.value) ? 1 : ((b.value > a.value) ? -1 : 0);
				});

				Lockr.set(dataKey, data);

				dfd.resolve(data);
			});
		} else {
			dfd.resolve(items);
		}

		return dfd.promise();
	}
};

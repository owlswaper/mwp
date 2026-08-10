bijan = {
	addZero: function(number) {
		if (typeof number === 'number' && number < 10) {
			if (number >= 0) {
				return `0${parseFloat(number)}`;
			} else {
				return `-0${Math.abs(number)}`;
			}
		}
		return number;
	},
	formatTime: function(seconds) {
		let minutes = Math.floor(seconds / 60);
		let secs = Math.floor(seconds % 60);
		return bijan.addZero(minutes) + ':' + bijan.addZero(secs);
	},
	validateEmail: function(email) {
		// Regular expression to validate email addresses
		const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		
		// Test the email against the regex and return the result
		return emailRegex.test(email);
	},
	persianNumbers: function(string) {
		return String(string).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
	},
	convertChars: function(string) {
		string = string.toString();
		let replaces = {
			'۰'	: '0',
			'۱'	: '1',
			'۲'	: '2',
			'۳'	: '3',
			'۴'	: '4',
			'۵'	: '5',
			'۶'	: '6',
			'۷'	: '7',
			'۸'	: '8',
			'۹'	: '9',
			'٪'	: '%',
			'÷'	: '/',
			'×'	: '*',
			'-'	: '-',
			'ـ'	: '_',
			'ي'	: 'ی',
			'ك'	: 'ک',
		}
		return string.replace(/[۰۱۲۳۴۵۶۷۸۹٪÷×ـيك]/g, match => replaces[match]);
	},
	isMobile: function(mobile) {
		return mobile.substr(0, 1) == '0' || mobile.substr(0, 2) == '09';
	},
	validateMobile: function(mobile) {
		return this.isMobile(mobile) && mobile.replaceAll(' ', '').length === 11;
	},
	validateUsername: function(username) {
		if(this.isMobile(username)) {
			return this.validateMobile(username);
		} else {
			if( username.includes('@') ) {
				return bijan.validateEmail(username);
			}
		}
		return true;
	},
	removeArrayItem: function(array, value) {
		const index = array.indexOf(value);
		if (index !== -1) {
			array.splice(index, 1);
		}
		return array;
	},
	deepClone: function(obj) {
		if (obj === null || typeof obj !== 'object') return obj;
		if (obj instanceof Array) {
			return obj.map(bijan.deepClone);
		}
		const cloned = {};
		for (let key in obj) {
			if (obj.hasOwnProperty(key)) {
				cloned[key] = bijan.deepClone(obj[key]);
			}
		}
		return cloned;
	}
}
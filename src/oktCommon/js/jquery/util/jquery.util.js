/**
 * jQuery Util
 * Version 1.0 - 17/01/2012
 * @author Flavien Bucheton
 *
 * Rajoute des fonctions utilitaires à Jquery
 *
 **/

(function($) {

	var toFixedFix = function (n,prec) {
		var k = Math.pow(10,prec);
		return (Math.round(n*k)/k).toString();
	};
	
	
	var parseInterval = function (interval){
		var aInterval = new Array(0,0,0,0,0,0,0);
		var aListe = interval.split("|");
		
		for(var i=0; i < aListe.length; i++){
			var aParam = aListe[i].split(" ");

			switch(aParam[1]){
				case "JOUR":
					aInterval[0] += parseInt(aParam[0], 10);
				break;
				case "SEMAINE":
					aInterval[0] += (parseInt(aParam[0], 10) * 7);
				break;
				case "MOIS":
					aInterval[1] += parseInt(aParam[0], 10) + 1;
				break;
				case "AN":
					aInterval[2] += parseInt(aParam[0], 10);
				break;
				case "HEURE":
					aInterval[3] += parseInt(aParam[0], 10);
				break;
				case "MINUTE":
					aInterval[4] += parseInt(aParam[0], 10);
				break;
				case "SECONDE":
					aInterval[5] += parseInt(aParam[0], 10);
				break;
			}
		}
		
		return aInterval;
	}
	
			
	$.extend({

		//Récupérer un chiffre d'après une chaine
		strToFloat: function(val){
			val = val.replace(/,/gi,".");
			val = val.replace(/([^0-9.])/gi,"");
			val = parseFloat(val);
			return val;
		},

		number_format: function(number, decimals, dec_point, thousands_sep) {
			var n = number, prec = decimals;

			n = !isFinite(+n) ? 0 : +n;
			prec = !isFinite(+prec) ? 0 : Math.abs(prec);
			var sep = (typeof thousands_sep === 'undefined') ? '' : thousands_sep;
			var dec = (typeof dec_point === 'undefined') ? '.' : dec_point;

			var s = (prec > 0) ? toFixedFix(n, prec) : toFixedFix(Math.round(n), prec);

			var abs = toFixedFix(Math.abs(n), prec);
			var _, i;

			if (abs >= 1000) {
				_ = abs.split(/\D/);
				i = _[0].length % 3 || 3;

				_[0] = s.slice(0,i + (n < 0)) +
					  _[0].slice(i).replace(/(\d{3})/g, sep+'$1');
				s = _.join(dec);
			} else {
				s = s.replace('.', dec);
			}

			var decPos = s.indexOf(dec);
			if (prec >= 1 && decPos !== -1 && (s.length-decPos-1) < prec) {
				s += new Array(prec-(s.length-decPos-1)).join(0)+'0';
			}
			else if (prec >= 1 && decPos === -1) {
				s += dec+new Array(prec).join(0)+'0';
			}
			return s;
		},

		array_compare: function(aTab){
			var cpt = 0;			
			for(var i=0; i<aTab.length; i++){
				var j = i+1;
				for(j; j<aTab.length; j++){
					if(aTab[i] == aTab[j] && (aTab[i]!="" || aTab[j]!="")) cpt++;
				}
			}
			return cpt;
		},
		
		ltrim: function(str, chars)
		{
		   chars = chars || "\\s";
		   return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
		},

		rtrim: function(str, chars)
		{
		   chars = chars || "\\s";
		   return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
		},

		trim: function(str, chars)
		{
		   return $.ltrim($.rtrim(str, chars), chars);
		},
        
        str_pad: function (input, pad_length, pad_string, pad_type)
        {
			var half = '', pad_to_go;
			var str_pad_repeater = function (s, len) {
				var collect = '', i;				
				while (collect.length < len) {collect += s;}
				collect = collect.substr(0,len); 
				return collect;
			};
			
			input += '';    
			pad_string = pad_string !== null ? pad_string : ' ';
			
			if (pad_type != 'STR_PAD_LEFT' && pad_type != 'STR_PAD_RIGHT' && pad_type != 'STR_PAD_BOTH') { pad_type = 'STR_PAD_RIGHT'; }
			if ((pad_to_go = pad_length - input.length) > 0) {
				if (pad_type == 'STR_PAD_LEFT') { input = str_pad_repeater(pad_string, pad_to_go) + input; }        
				else if (pad_type == 'STR_PAD_RIGHT') { input = input + str_pad_repeater(pad_string, pad_to_go); }
				else if (pad_type == 'STR_PAD_BOTH') {
					half = str_pad_repeater(pad_string, Math.ceil(pad_to_go/2));
					input = half + input + half;
					input = input.substr(0, pad_length);
				}
			}
			
			return input;
		},
        
        nl2br: function(str) {
             return str.replace(/\n/g, '<br>');
        },
        
        br2nl: function(str){
            return str.replace(/\n/gi, '').replace(/<br>/gi, '\n');
        },

		isMobile: function(){
			var user_agent = navigator.userAgent.toLowerCase();
			if(user_agent.match(/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|vodafone|o2|pocket|mobile|pda|psp|treo)/gi)){
				return true;
			}

			var agent = navigator.userAgent.substr(0,4).toLowerCase();
			var aAgents = new Array('acs-','alav','alca','amoi','audi','aste','avan','benq','bird','blac','blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno','ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-','maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-','newt','noki','opwv','palm','pana','pant','pdxg','phil','play','pluc','port','prox','qtek','qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar','sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-','tosh','treo','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp','wapr','webc','winw','winw','xda','xda-');
			if($.inArray(agent, aAgents)>-1){
				return true;
			}

			return false;
		},

        JSONtoString: function(obj) {
            var t = typeof (obj);
            if (t != "object" || obj === null) {
                // simple data type
                if (t == "string") obj = '"'+obj+'"';
                return String(obj);
            }
            else {
                // recurse array or object
                var n, v, json = [], arr = (obj && obj.constructor == Array);
                for (n in obj) {
                    v = obj[n]; t = typeof(v);
                    if (t == "string") v = '"'+v+'"';
                    else if (t == "object" && v !== null) v = $.JSONtoString(v);
                    json.push((arr ? "" : '"' + n + '":') + String(v));
                }
                return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
            }
        },
		
		dateSub: function (nTimeStamp, interval){
			var aInterval = parseInterval(interval);
			var d = new Date(nTimeStamp);
			
			d.setHours(d.getHours() - aInterval[3]);
			d.setMinutes(d.getMinutes() - aInterval[4]);
			d.setSeconds(d.getSeconds() - aInterval[5]);
			d.setDate(d.getDate() - aInterval[0]);
			d.setMonth(d.getMonth() - aInterval[1]);
			d.setFullYear(d.getFullYear() - aInterval[2]);
			
			return d.getTime();
		},

		dateAdd: function (nTimeStamp, interval){
			var aInterval = parseInterval(interval);
			var d = new Date(nTimeStamp);

			d.setHours(d.getHours() + aInterval[3]);
			d.setMinutes(d.getMinutes() + aInterval[4]);
			d.setSeconds(d.getSeconds() + aInterval[5]);
			d.setDate(d.getDate() + aInterval[0]);
			d.setMonth(d.getMonth() + aInterval[1]);
			d.setFullYear(d.getFullYear() + aInterval[2]);

			return d.getTime();
		}
	});

	$.fn.extend({
		readOnly: function(active, val, bindEvt){
			if(active){
				$(this).bind(bindEvt, null, function(){
					if($(this).val()!=val){
						$(this).val(val);
					}
				});
			}else{
				$(this).unbind(bindEvt);
			}
		}
	});
})(jQuery);
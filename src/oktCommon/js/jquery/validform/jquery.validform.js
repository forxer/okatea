/**
 * jQuery Formulaire
 * Version 1.0 - 06/05/2009
 * @author Flavien Bucheton
 *
 * Permet de gérer la validation d'un formulaire
 *
 **/

(function($) {
	var myself = $(this);
	var regSeparateurDate = new RegExp("[\\.,;: _\\-/\\\\]", "ig");

	//Callback par défaut pour la gestion d'erreur du captcha
	var erreurCapt = function(obj, txt, code){
		alert(txt);
		$('#'+code).val("");
		$('#'+code).focus();
	};

	//Callback par défaut pour la gestion d'erreur
	var erreurForm = function(obj, txt){
		alert(txt);
		obj.focus();
	};

	var Remplace = function(expr,a,b) {
		var i=0;
		while (i!=-1) {
			i=expr.indexOf(a,i);
			if (i>=0) {
				expr=expr.substring(0,i)+b+expr.substring(i+a.length);
				i+=b.length;
			}
		}
		return expr;
	};

	var getUnite = function(format){
		switch(format.charAt(0)){
			case "j":
			case "d":
				return "j";
				break;
			case "m":
				return "m";
				break;
			case "a":
			case "y":
				return "a";
				break;
		}
	};

	var verifDate = function(val, format){
		if(format==null){
			format = "jj/mm/aaaa";
		}
		var sep = regSeparateurDate.exec(format);
		regSeparateurDate.lastIndex = 0

		var regFormat = new RegExp(sep, "ig");
		var tabFormat = format.split(regFormat);
		var part1 = tabFormat[0].charAt(0)=="j" || tabFormat[0].charAt(0)=="d" || tabFormat[0].charAt(0)=="m" ? "\\d{1,2}" : "\\d{2,4}";
		var part2 = tabFormat[1].charAt(0)=="j" || tabFormat[1].charAt(0)=="d" || tabFormat[1].charAt(0)=="m" ? "\\d{1,2}" : "\\d{2,4}";
		var part3 = tabFormat[2].charAt(0)=="j" || tabFormat[2].charAt(0)=="d" || tabFormat[2].charAt(0)=="m" ? "\\d{1,2}" : "\\d{2,4}";

		var regTest = new RegExp("^"+part1+sep+part2+sep+part3+"$", "ig");
		if(!regTest.test(val)) return false;
		else{
			var tabDate = val.split(regFormat);
			for(var i=0; i<3; i++){
				switch(tabFormat[i].charAt(0)){
					case "j":
					case "d":
						var jour = parseInt(tabDate[i],10);
						break;
					case "m":
						var mois = parseInt(tabDate[i],10);
						break;
					case "a":
					case "y":
						var annee = parseInt(tabDate[i],10);
						if(annee.length<4){
							annee += 2000;
						}
						break;
				}
			}

			var nbJourMax=31;
			if (jour>0 && mois>0 && mois<=12)
			{
				if (mois==2)
				{
					if (annee%4==0 && annee%100!=0)
						nbJourMax=29;
					else
						nbJourMax=28;
				}
				else if (mois==4 || mois==6 || mois==9 || mois==11)
				{
					nbJourMax=30;
				}

				if (jour>nbJourMax)	return false;
				else return true;
			}
			else return false;
		}
	};

	var verifHeure = function(val){
        var regTest = new RegExp("^([0-9]){2}:([0-9]){2}$");
        if(!regTest.test(val)) return false;
        else{
            var heure = parseInt(val.split(":")[0],10);
            var minute = parseInt(val.split(":")[1],10);
            if(heure > 23 || minute > 59) return false;
			else return true;
        }
    };
	
	$.fn.extend({
		isDate: function(param){
			return verifDate(this.val(), param);
		},
		
		estPresent: function(texterr, callback) {
			if (this.val() == "") {
				var myObj = this;
				if(jQuery.isFunction(callback)){
					callback.call(this, myObj, texterr);
				}else{
					erreurForm(this, texterr);
				}
				return false;
			}
			return true;
		},


		estValide: function(texterr, type, param, callback) {
			var erreur = false;
			switch(type){
				case "length":
					if(this.val().length < param) erreur = true;
					break;
				case "int":
					temp = parseInt(this.val(),10);
					if(isNaN(temp)) erreur = true;
					break;
				case "intPositif":
					temp = parseInt(this.val(),10);
					if (isNaN(temp) || temp < 0) erreur = true;
					break;
				case "float":
					var reg = RegExp(" ","gi");
					var temp = this.val().replace(reg,"");
					temp = Remplace(temp,",",".");
					temp = parseFloat(temp);
					if (isNaN(temp)) erreur = true;
					break;
				case "floatPositif":
					var reg = RegExp(" ","gi");
					var temp = this.val().replace(reg,"");
					temp = Remplace(temp,",",".");
					temp = parseFloat(temp);
					if (isNaN(temp) || temp < 0) erreur = true;
					break;
				case "email":
					var mailReg = new RegExp("^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]{2,}[.][a-zA-Z]{2,4}$");
					if (!mailReg.test(this.val())) erreur = true;
					break;
				case "email_multiple":
					var mailRegM = new RegExp("^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]{2,}[.][a-zA-Z]{2,4}$");
					var listeMail = this.val().split(",");
					for(var i=0; i<listeMail.length; i++){
						if (!mailRegM.test(listeMail[i])) erreur = true;
					}
					break;                
				case "date":
					if(!verifDate(this.val(), param)) erreur = true;
					break;
				case "time":
					var timeReg = new RegExp("^([0-9]){2,3}:([0-9]){2}$");
					if (!timeReg.test(this.val())) erreur = true;
					break;
				case "heure":
					if(!verifHeure(this.val())) erreur = true;
					break;
                case "telephone":
                    var telephone = this.val().replace(/([^0-9])/gi,"");
					if (telephone.length < 10 || isNaN(telephone)) erreur = true;               
                    break;                    
				case "url":
					var urlReg = new RegExp("^^http://([a-zA-Z0-9-]+\.)+([a-zA-Z0-9-]{2,4})(:[0-9]{0,5})?(/[a-zA-Z0-9._-]*)*$");
					if(!urlReg.test(this.val())) erreur = true;
					break;
				case "domaine":
					var domaineReg = new RegExp("^([a-z0-9]+-?[a-z0-9]*)*[.][a-z]{2,4}$");
					var limit = param.split(";");
					var domain = this.val().split(".")[0];
					if(this.val()!="" && ((!domaineReg.test(this.val())) || (domain.length < limit[0]) || (domain.length > limit[1]))) erreur = true;
					break;
				case "ip":
					var ipReg = new RegExp("[0-9]{1,3}[\.][0-9]{1,3}[\.][0-9]{1,3}[\.][0-9]{1,3}");
					if (!ipReg.test(this.val())) erreur = true;
					break;
				case "siren":
					this.val(this.val().replace(/([^0-9])/gi,""));
					if (this.val().length != 9 || this.val()=="000000000" || isNaN(this.val()))
						erreur = true;
					else {
						var somme = 0;
						var tmp;
						for (var cpt = 0; cpt<this.val().length; cpt++) {
							if ((cpt % 2) == 1) {
								tmp = this.val().charAt(cpt) * 2;
								if (tmp > 9) tmp -= 9;
							}
							else tmp = this.val().charAt(cpt);
							somme += parseInt(tmp,10);
						}

						if ((somme % 10) == 0) erreur = false;
						else erreur = true;
					}
					break;
			}

			if (erreur) {
				var myObj = this;
				if(jQuery.isFunction(param)) callback = param;
				if(jQuery.isFunction(callback)){
					callback.call(this, myObj, texterr);
				}else{
					erreurForm(this, texterr);
				}
				return false;
			}
			return true;
		},

		estSelectionne: function(texterr, valDef, callback) {
			if (this.val() == valDef) {
				var myObj = this;
				if(jQuery.isFunction(callback)){
					callback.call(this, myObj, texterr);
				}else{
					erreurForm(this, texterr);
				}
				return false;
			}
			return true;
		},

		estIdentique: function(texterr, obj, callback){
			if (this.val() != obj.val()) {
				var myObj = this;
				if(jQuery.isFunction(callback)){
					callback.call(this, myObj, texterr);
				}else{
					erreurForm(this, texterr);
				}
				return false;
			}
			return true;
		},

		estCoche: function(texterr, valDef, callback) {
			var verif = valDef == null ? this.is(':checked') : this.val() != valDef;
			if (!verif) {
				var myObj = this;
				if(jQuery.isFunction(callback)){
					callback.call(this, myObj, texterr);
				}else{
					erreurForm(this, texterr);
				}
				return false;
			}
			return true;
		},


		verifCaptcha : function(url, callback, code) {
			var myObj = this;
			if(url==null){
				url = "Scripts/response.php";
			}
			if(code==null){
				code = "code";
			}

			$.post(url, "code="+$('#'+code).val(), function(data){
				var items_res = data.getElementsByTagName("res")
				var items_val = data.getElementsByTagName("valeur")
				switch(items_res.item(0).firstChild.data){
					case "404":
					case "403":
						var texterr = items_val.item(0).firstChild.data;
						if(jQuery.isFunction(url)) callback = url;
						if(jQuery.isFunction(callback)){
							callback.call(this, myObj, texterr, code);
						}else{
							erreurCapt(this, texterr, code);
						}
						return false;
						break;
					case "200":
						myObj.submit();
						break;
				}
			},"xml");
		},

		nlInscrire: function(options){
			var settings = {
				pageInscrire: "Scripts/inscrire.php",
				flash: false,
				email: "newsEmail"
			};

			$.extend(settings, options);

			$.post(settings.pageInscrire, {texte: $("#"+settings.email).val()}, function(docXML){
				var items_res = docXML.getElementsByTagName("res")
				var items_val = docXML.getElementsByTagName("valeur")
				for (i=0;i<items_res.length;i++)
				{
					alert (items_val.item(i).firstChild.data);
					if(items_res.item(i).firstChild.data=="200"){
						if(!settings.flash){ $("#"+settings.email).val(""); }
					}
				}
			},"xml");
		}
	});

})(jQuery);
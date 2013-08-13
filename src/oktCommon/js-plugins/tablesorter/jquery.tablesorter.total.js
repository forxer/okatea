(function($) {
	$.extend({
                tablesorterTotal: new function() {
                        var formatDefaut = {decimals: 0, dec_point: '.', thousands_sep: '', prefix: '', suffix: ''};
                        
                        function toFixedFix (n,prec) {
                                var k = Math.pow(10,prec);
                                return (Math.round(n*k)/k).toString();
                        }
                    
                        function strToFloat(val){
                                val = val.replace(/,/gi,".");
                                val = val.replace(/([^0-9.])/gi,"");
                                val = parseFloat(val);
                                return val;
                        }

                        function number_format(number, decimals, dec_point, thousands_sep) {
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
                        }
                        
                        function buildFooters(table){
                            $tableFooters = $("tfoot th",table).size() > 0 ? $("tfoot th",table) : $("tfoot td",table);                            
                            $tableFooters.each(function(index) {
                                if($.inArray(index, table.config.totalColumns) != -1){
                                    this.column = index + 1;                                    
                                    if(($.metadata) && ($(this).metadata().format)){
                                        this.format = $.extend({}, formatDefaut, $(this).metadata().format);
                                    }else{
                                        if(table.config.format[index]){
                                            this.format = $.extend({}, formatDefaut, table.config.format[index]);
                                        }else{
                                            this.format = formatDefaut;
                                        }
                                    }                                                      
                                    table.config.formatList.push(this);
                                }
                            });
                        }
                        
                        function updateTotal(table){                            
                            if(table.config.totalColumns.length > 0){                            
                                $.each(table.config.formatList, function() {
                                    var total = 0;    
                                    $("tbody tr td:nth-child("+this.column+")",table).each(function(){
                                        total += strToFloat($(this).html());
                                    });
                                    $(this).html(this.format.prefix + number_format(total, this.format.decimals, this.format.dec_point, this.format.thousands_sep) + this.format.suffix);
                                });
                            }
                        }
                        
                        
                        this.defaults = {
                            totalColumns: [],
                            format: {},
                            formatList: []
			};
			
			this.construct = function(settings) {
				
				return this.each(function() {	
					
					config = $.extend(this.config, $.tablesorterTotal.defaults, settings);
					
					var table = this, total = config.container;
				
					$(this).trigger("appendCache");
					
                                        if($.metadata && ($(this).metadata() && $(this).metadata().totalColumns)) {
						config.totalColumns = $(this).metadata().totalColumns;
					}
                                        
                                        buildFooters(table);
                                        updateTotal(table);
                                        
                                        $(table).bind("update",function() {
                                            updateTotal(table);
                                        });
				});
			};
			
		}
	});
	// extend plugin scope
	$.fn.extend({
            tablesorterTotal: $.tablesorterTotal.construct
	});
	
})(jQuery);
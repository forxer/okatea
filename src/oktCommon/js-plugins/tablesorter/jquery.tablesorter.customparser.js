//Parser table sorter
$.tablesorter.addParser({ 
    id: 'tri_datetime', 
    is: function(s) { 
        return false; 
    }, 
    format: function(s) {
        var aDate = s.split(" ");
        var aPeriode = aDate[0].split("/");
        return aPeriode[2] + "-" + aPeriode[1] + "-" + aPeriode[0]+" "+aDate[1]; 
    }, 
    type: 'text' 
});

$.tablesorter.addParser({ 
    id: 'tri_int', 
    is: function(s) { 
        return false; 
    }, 
    format: function(s) {
        return parseInt(s.replace(" ",""),10); 
    }, 
    type: 'numeric' 
});

$.tablesorter.addParser({ 
    id: 'tri_float', 
    is: function(s) { 
        return false; 
    }, 
    format: function(s) {
        return parseFloat(s.replace(/([^0-9.,])/gi,"")); 
    }, 
    type: 'numeric' 
});

$.tablesorter.addParser({ 
    id: 'tri_periode', 
    is: function(s) { 
        return false; 
    }, 
    format: function(s) {        
        s = s.replace("Du ","");
        s = s.replace(" au ","/");
        s = s.replace(/([^0-9/])/gi,"");
        if(s==""){ return "0000-00-00 0000-00-00"; }
        aPeriode = s.split("/");     
        return aPeriode[2] + "-" + aPeriode[1] + "-" + aPeriode[0]+" "+aPeriode[5] + "-" + aPeriode[4] + "-" + aPeriode[3];
        
    }, 
    type: 'text' 
}); 
/********Javascript for FREE TEXT SEARCH ************/
var Quicksearch = Class.create();
var idSearchInput = '';
Quicksearch.prototype = {
	initialize: function(searchUrl,resultNotice,idSearchInput){
		this.idSearchInput = idSearchInput;
        this.searchUrl = searchUrl;		
		this.onSuccess = this.onSuccess.bindAsEventListener(this);        
		this.onFailure = this.onFailure.bindAsEventListener(this);				
		this.currentSearch = ''; 	
		this.resultNotice = resultNotice;
    },
	search: function(){	
		var searchBox = $(this.idSearchInput);
	    
		if(searchBox.value=='')
		{
			return;
		}
		
	    if ((this.currentSearch!="") &&(searchBox.value == this.currentSearch)) {
	        return;
	    }
	    this.currentSearch = searchBox.value;
		
		searchBox.className =  'loading-result input-text';
		var keyword = searchBox.value;
		
		
		url = this.searchUrl+"keyword/" + escape(keyword);
		 
		new Ajax.Request(url, {
			  method: 'get',		 
		      onSuccess: this.onSuccess,
			 
			  onFailure: this.onFailure 
		  });	 
    },
	onFailure: function(transport){
        $(this.idSearchInput).className ="input-text";
    },
	onSuccess: function(transport)
	{
		var showResults = $('showResults');
		showResults.style.display = "block";
		var listResults = $('listResults');
		listResults.style.display = "block";
		var searchBox = $(this.idSearchInput);
		if (transport && transport.responseText) {
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
			
			if (response.html != "") {
				this.currentSearch = searchBox.value;
				listResults.update(response.html);
				var searchResultNotice = this.resultNotice;
				var strNotice = searchResultNotice.replace("{{keyword}}",this.currentSearch);
				this.updateResultLabel(strNotice);
				searchBox.className = 'search-complete input-text';
            }
			else
			{
				listResults.update(response.html);
				this.updateResultLabel('No results for "<span class="keyword">'+this.currentSearch+'</span>"');
				searchBox.className ="search-complete input-text";
			}			
		}		
	},
	updateResultLabel: function(message)
	{
		$("resultLabel").update(message);
	}
}
if (!RedactorPlugins) var RedactorPlugins = {};

RedactorPlugins.mergetags = {
	init: function ()
	{
	    this.buttonAdd('mergeTags', 'Merge Tags', this.mergeTagsButton);
	},
	mergeTagsButton: function(buttonName, buttonDOM, buttonObj, e)
	{
	    $('.MergeTagsView').toggle();
}
};
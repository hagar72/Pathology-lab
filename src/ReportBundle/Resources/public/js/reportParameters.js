var $collectionHolder;

// setup an "add a reportParameter" link
var $addParameterLink = $('<a href="#" class="add_parameter_link">Add a parameter</a>');
var $newLinkLi = $('<li></li>').append($addParameterLink);

jQuery(document).ready(function() {
    // Get the container that holds the collection of parameters
    $collectionHolder = $('fieldset.parameters');

    // add the "add a reportParameter" anchor and li to the parameters div
    $collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 1), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', $collectionHolder.find(':input').length);

    if(window.location.pathname.search('new') > 0) {
        addParameterForm($collectionHolder, $newLinkLi);
    }
    
    $addParameterLink.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // add a new reportParameter form (see next code block)
        addParameterForm($collectionHolder, $newLinkLi);
    });
    
    $collectionHolder.find('div.reportParameterContainer').each(function() {
        addParameterFormDeleteLink($(this));
    });
});

function addParameterForm($collectionHolder, $newLinkLi) {
    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');

    // get the new index
    var index = $collectionHolder.data('index');

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    var newForm = prototype.replace(/__name__/g, index);

    // increase the index with one for the next item
    $collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a reportParameter" link li
    var $newFormLi = $('<li></li>').append(newForm);
    $newLinkLi.before($newFormLi);
    
    // add a delete link to the new form
    addParameterFormDeleteLink($newFormLi);
    
    $('div[id^="report_reportParameters_"]').find('div').addClass('form-group');
    $('div[id^="report_reportParameters_"]').find('input').addClass('form-control');
    
}

function addParameterFormDeleteLink($reportParameterFormLi) {
    var $removeFormA = $('<a href="#">delete this parameter</a>');
    $reportParameterFormLi.append($removeFormA);

    $removeFormA.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // remove the li for the reportParameter form
        $reportParameterFormLi.remove();
    });
}
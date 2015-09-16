document.observe('dom:loaded', function(){

    var form = $('classfinder_form');
    var results = $$('#results_fieldset tbody')[0];

    form.select('a[data-type]').invoke('observe', 'click', function(e){
        e.preventDefault();

        var type = this.readAttribute('data-type');
        var value = form.select('input[name="' + type + '"]')[0].getValue();

        if(!value)
            return false;

        new Ajax.Request(this.readAttribute('href'), {
            parameters: {
                class: value
            },
            onSuccess: function(response){
                results.insert(new Element('tr')
                    .insert(new Element('td', {class: 'label'}).update(type.charAt(0).toUpperCase() + type.slice(1))
                        .insert(new Element('div').update(value).setStyle({fontSize: '10px', lineHeight: '100%'})))
                    .insert(new Element('td', {class: 'value'}).update(response.responseJSON || "Class does not exist")));
            }
        });
    });

});
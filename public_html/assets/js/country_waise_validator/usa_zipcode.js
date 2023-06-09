jQuery.validator.addMethod("zipCodeCheck", function(value, element) {
    //return this.optional(element) || /^((?!(0))[0-9]{6,7})$/i.test(value);
    //return this.optional(element) || /^[A-Z]{1,2}[0-9][A-Z0-9]? ?[0-9][A-Z]{2}$/i.test(value);
    return this.optional(element) || /^\d{5}(-\d{4})?$/.test(value);
}, "Enter valid zipcode");
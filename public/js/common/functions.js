/*
 * Guachi (Lightweight and very simple web development framework)
 * https://gitlab.com/vitronic/Guachi_Framework
 *
 * Copyright (c) 2018 Díaz Devera Víctor (Máster Vitronic)
 * Licensed under the MIT license.
 * 
 * Module functions
 * Contiene funciones comunes
 * 
 * Solo para usar con node_development_mode On
 * */
 
'use strict';

//var $         = require('jquery');

var functions = {
    /*retorna el modulo en uso*/
    get_module:function(){
        //return $("meta[name=module]").attr("content"); /*jquery*/
        return document.querySelector('meta[name="module"]')['content'];/*vanilla*/
    }
}

module.exports = functions;

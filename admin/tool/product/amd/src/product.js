// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Manages shopping cart actions.
 *
 * @module     tool_product/product
 * @copyright  2022 Ranga Reddy <rangareddy@eabyas.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import ModalFactory from 'core/modal_factory';
import ModalForm from 'core_form/modalform';
import ModalEvents from 'core/modal_events';


import $ from 'jquery';
import {get_string as getString} from 'core/str';
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import HomePageJs from "theme_academy/homepage";
import ModalLogin from 'tool_product/login-modal';

const Selectors = {
    actions: {
        paymentsupdate: '[data-action="paymentsupdate"]',
        approvalseats: '[data-action="approvalseats"]',
        sendemailtoorgofficial: '[data-action="sendemailtoorgofficial"]',
		cancelorder: '[data-action="cancelorder"]',
    },
};

const strings = {
	select : 'Select',//getString('select', 'tool_product'),
	selected: 'Selected',//getString('selected', 'tool_product'),
	pleaseselectvariation: 'Please Select Variation' //getString('pleaseselectvariation', 'tool_product'),

}

const get_attributes = ( selector ) => {
	let attributes = $(selector).data();
	
	return attributes;
}

const product_has_variations = ( args ) => {
	return args['hasvariations'];
}
const get_product_id = ( args ) => {
	return product_has_variations(args) ? args['variation'] : args['product'];
}

const set_product = (products) => {
	localStorage.setItem('products', JSON.stringify(products));
}

const get_products = () => {
	let products = localStorage.getItem('products');
	products = JSON.parse(products);
	return products ? products : [];
}

const set_productformdata = (formdata) => {
	localStorage.setItem('productformdata', JSON.stringify(formdata));
}

const get_productformdata = () => {
	let productformdata = localStorage.getItem('productformdata');
	productformdata = JSON.parse(productformdata);
	return productformdata ? productformdata : [];
}
const set_coupons = (coupons) => {
	localStorage.setItem('coupons', JSON.stringify(coupons));
}

const get_coupons = () => {
	let coupons = localStorage.getItem('coupons');

	coupons = JSON.parse(coupons);

	if(coupons){

		coupons = coupons.filter(Boolean);

	}

	return coupons ? coupons : [];
}

const exists_in_coupon = ( couponcode ) => {
	let coupons = get_coupons();

	return coupons ? coupons.findIndex(x => (x.couponcode == couponcode)) : -1;
}


const get_product_information = () => {
	let products = get_products();
	
	let promise = Ajax.call([{
			methodname: 'tool_product_view_cart',
			args: {
				products: products
			}
		}]);
	promise[0].done((response) => {
		render_cart_page(response);
	}).fail( (error) => {

		let homepage = new HomePageJs();
    	homepage.confirmbox(error.error);
	});

	let productscount = get_products();

	$('.basketicon').html('<span class="basketcount">'+productscount.length+'</span>');
}

const add_to_cart = (args, parent) => {

	let incart = exists_in_cart(args);
	let products = get_products();
	let data = {
		variation: args['variation'],
		product: args['product'],
		grouped: args['grouped'],
		hasvariations: product_has_variations(args),
		quantity: args['quantity'],
		category: args['category'],
		language: args['language'],
		hallscheduleid: args['hallscheduleid'],
		profileid: args['profileid'],
		processtype: args['processtype'],
		tandcconfirm: args['tandcconfirm'],

	}
	if(incart > -1 && products[incart]){
	
		products[incart] = data;
	}else{
		products.push(data);
	}
	
	set_product(products);

	let productscount = get_products();

	if(parent){
		render_template('tool_product/cart/go-to-cart', parent, {isloggedin: args['loggedin'], checkout: args['checkout']});
	}
	$('.basketicon').html('<span class="basketcount">'+productscount.length+'</span>');
}

const remove_from_cart = ( args , refresh = true) => {
	let index = exists_in_cart( args );
	if(index > -1){
		let products = get_products();
		if(products[index]){
			products.splice(index, 1);
			set_product(products);
			if(refresh){
				get_product_information();
			}
		}
	}
	if(args['couponcode']) {
		let couponexist = exists_in_coupon( args['couponcode'] );
		if(couponexist > -1){
			let coupons = get_coupons();
			if(coupons[couponexist]){
				coupons.splice(couponexist, 1);
				set_coupons(coupons);	
			}
		}
	}
	let productscount = get_products();
	$('.basketicon').html('<span class="basketcount">'+productscount.length+'</span>');
}

const remove_group_from_cart = ( args ) => {
	let products = get_products();
	for(let i = 0; i < products.length; i++){
		if(products[i] && (products[i]['grouped'] == args['product'])){
			let data = {};
			data['category'] = products[i]['category'];
			data['product'] = products[i]['product'];
			remove_from_cart( data, false );
		}
	}
	get_product_information();
}

const applycoupon_to_cart = ( args ) => {

	let index = exists_in_cart(args);

	if(index > -1){


		let data = {
			product: args['product'],
			couponcode: $('#couponcode-'+args.product).val(),
			category: args['category']
		}
	
		let promise = Ajax.call([{
					methodname: 'tool_product_validate_couponcode',
					args: {
						products: data
					}
				}]);
		promise[0].done((response) => {


			let couponindex = exists_in_coupon(response.couponcode);
			
			 if(response.couponvalid == false){


				$('#couponmsg-'+args.product).html(response.couponmsg);
				 

			}else{


				let couponincart = exists_in_cart(args);


				let productsdata = get_products();


				if(couponincart > -1 && productsdata[couponincart]){

					productsdata[couponincart]['couponcode'] = response.couponcode;

					productsdata[couponincart]['couponid'] = response.couponid;

					productsdata[couponincart]['roles'] = response.roles;

					set_product(productsdata);

					let coupons = get_coupons();

					let couponcodedata = {
						couponcode: response.couponcode,
					}

					if(couponindex > -1  && couponindex == args['product'] && coupons[couponindex]){

						coupons[couponindex] = couponcodedata;

					}else{

						coupons[args['product']]=couponcodedata;

					}
					
					set_coupons(coupons);

				}

				if(response.roles == false){

					render_template('tool_product/cart/index', '#cart_container', response, false);

				}else{

					let productformdata = get_productformdata();

					render_template('tool_product/checkout/index', '#checkout_container',{formdata: JSON.stringify(productformdata)}, false);
				}
			}
	
		}).fail( (error) => {
			let homepage = new HomePageJs();
    		homepage.confirmbox(error.error);
		});
	}
}
const removecoupon_to_cart = ( args ) => {


	let couponincart = exists_in_cart(args);


	let productsdata = get_products();


	if(couponincart > -1 && productsdata[couponincart]){

		productsdata[couponincart]['couponcode'] = 0;

		productsdata[couponincart]['couponid'] = 0;

		set_product(productsdata);

		let couponexist = exists_in_coupon( args['couponcode'] );

		if(couponexist > -1){

			let coupons = get_coupons();

			if(coupons[couponexist]){

				coupons.splice(couponexist, 1);

				set_coupons(coupons);	

			}

		}

	}

	if(productsdata[couponincart]['roles'] == false){

		render_template('tool_product/cart/index', '#cart_container', productsdata, false);

	}else{

		let productformdata = get_productformdata();

		render_template('tool_product/checkout/index', '#checkout_container',{formdata: JSON.stringify(productformdata)}, false);
	}
	
	
}
const empty_cart = () => {

	localStorage.removeItem('products');

	localStorage.removeItem('coupons');

	localStorage.removeItem('productformdata');

	get_product_information();

	$('.basketicon').html('<span class="basketcount">0</span>');
}

const get_checkout_summary = (formdata) => {

	let products = get_products();

	set_productformdata(formdata);

	let productformdata = get_productformdata();

	let promise = Ajax.call([{
			methodname: 'tool_product_checkout_summary',
			args: {
				products: products,
				formdata:productformdata
			}
		}]);
	promise[0].done((response) => {
		render_checkout_page(response);
	}).fail( (error) => {
		let homepage = new HomePageJs();
   		homepage.confirmbox(error.error);
	});
}

const render_checkout_page = (response) => {


	let productformdata = get_productformdata();
	
	render_template('tool_product/checkout/summary', '[data-region="order-summary"]', response, false);
	render_template('tool_product/checkout/payment-methods', '[data-region="payment-methods"]', {summary: JSON.stringify(response.productdata), data: response,formdata: JSON.stringify(productformdata)}, false);
	$('#checkout-loader').empty();
}

const render_paymentsummary_page = (response) => {

	if(response.coupondiscounfailed == true){

		let productformdata = get_productformdata();

		render_template('tool_product/checkout/index', '#checkout_container',{formdata: JSON.stringify(productformdata),coupondiscounfailed:true}, false);

	}else{

		localStorage.removeItem('products');

		localStorage.removeItem('coupons');

		localStorage.removeItem('productformdata');

		render_template('tool_product/paymentsummary', '#checkout_container', response, false);

	}
}


const exists_in_cart = ( args ) => {
	let products = get_products();
	return products ? products.findIndex(x => (x.category == args['category']) && (x.product == args['product'])) : -1;
}

const render_cart_page = ( response ) => {
	let formatted_response = format_cart_response( response );
	response['formatted_response'] = formatted_response;

	render_template('tool_product/cart/empty-cart-button', '[data-region="empty-cart-button"]', response, false);
	render_template('tool_product/cart/content', '[data-region="cart-body"]', response, false);
	$('#cart-loader').empty();
}

const format_cart_response = ( response ) => {
	let result  = [];
	let items = response.items;
	result = [];
	let elements = [];
	for(let i = 0; i < items.length; i++){
		if(items[i]['grouped'] != 0){
			let index = elements.findIndex( x => x === items[i]['grouped']);
			if( index == -1 ){
				let group = {};
				group['isgrouped'] = true;
				group['title'] = get_group_title(response.tracks, items[i]['grouped']);
				group['id'] = items[i]['grouped'];
				group['subitems'] = get_grouped_items(items, items[i]['grouped']);
				result.push(group);
				elements.push(items[i]['grouped']);
			}
		}else{
			items[i]['isgrouped'] = false;
			result.push(items[i]);
		}
	}
	return result;
}


const get_grouped_items = ( response, group) => {
	let data = [];
 	for(let i = 0; i < response.length; i++){ 		
 		if(response[i]['grouped'] == group){
 			data.push(response[i]);
 		}
 	}
	return data;
}

const get_group_title = (tracks, group) => {
	let index = tracks.findIndex(x => x.id == group);
	if(index != -1){
		return (tracks && tracks[index]) ? tracks[index]['name'] : '';
	}
}

const render_template = (template, selector, params, append = false) => {
	if(!append){
		$(selector).empty();
	}
	Templates.renderForPromise(template, params).then(({html, js}) => {
		Templates.appendNodeContents(selector, html, js);
	});	
}

const loader = (selector, isloading, append = false) => {
	if(isloading){
		render_template('tool_product/loader', selector, {}, append);
	}else{
		$("[data-region='page-loader']").remove();
	}
}

const get_variation_buttons = ( selector ) =>{
 	$(selector).each( (key, value) => {
 	    var selectString  = getString('select' ,'tool_product');
		selectString.done(function(html, js){
			$(value).text(html);
		});
 		////$(value).text(strings.select);
 	});
}

const process_postpaid_payments = (root, products) => {

	let promise = Ajax.call([{
			methodname: 'tool_product_postpaid_payments',
			args: {
				products: products
				// paymenttype: 'postpaid'
			}
		}]);
	promise[0].done((response) => {
		render_paymentsummary_page(response);
		// window.location.href = response.returnurl;
	}).fail( (error) => {

		let homepage = new HomePageJs();
		homepage.confirmbox(error.error);

	});
}

const process_prepaid_payments = (root, products) => {
	let promise = Ajax.call([{
			methodname: 'tool_product_prepaid_payments',
			args: {
				products: products
			}
		}]);
	promise[0].done((response) => {
		render_paymentsummary_page(response);
	}).fail( (error) => {
		let homepage = new HomePageJs();
		homepage.confirmbox(error.error);
	});
}

const process_creditcard_payments = (root, products) => {
	let promise = Ajax.call([{
		methodname: 'tool_product_telr_begin_trans',
		args: {
			products: products
		}
	}]);
	render_template('tool_product/loader');
	promise[0].done((response) => {
		if(response.noseats) {
			let productname = getString('noseatsavailable', 'tool_product', response.productname);
			ModalFactory.create({
                title: '',
                type: ModalFactory.types.SAVE_CANCEL,
                body: productname
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('ok', 'tool_product'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    promise[0].done(function() {
						for(let i = 0; i < response.products.length; i++){
							let data = {};
							data['category'] = response.products[i]['category'];
							data['product'] = response.products[i]['productid'];
							remove_from_cart(data, false );
						}
                        window.location.reload(true);
                    }).fail(function() {
                        // do something with the exception
                    });
                }.bind(this));
                modal.show();
            }.bind(this));
		} else {
			window.location.href = response.returnurl;
		}
	}).fail( (error) => {
		let homepage = new HomePageJs();
		homepage.confirmbox(error.message);
	});
}

export const add = ( root ) => {
	$(root).on('click', (e) => {
		e.preventDefault();
		var args = get_attributes($(root));
		args.language = $(root).data('language');
		args.hallscheduleid = $(root).data('hallscheduleid');
		args.profileid = $(root).data('profileid');
		args.processtype = $(root).data('processtype');
		args.loggedin = $(root).data('loggedin');
		args.tandc = $(root).data('tandc');
		

		if( get_product_id(args)){

			if(args.hallscheduleid !== undefined){
				if(args.loggedin==false){
					ModalFactory.create({type: ModalLogin.TYPE}, $(e.currentTarget)); 
				}else{

					const title = getString('tandc', 'local_exams');
					const form = new ModalForm({
					formClass: 'local_exams\\form\\termsconditionsform',
					args: {examid:args.product},
					modalConfig: {title},
					saveButtonText: getString('confirm', 'local_exams'),
				

					});
				
				form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
					
				const data = event.detail;
					if(data.tandc > 0) {

					var tandcconfirm = 1;
					$(event.currentTarget).find('button[data-action="save"]').attr('disabled', false);
				} else {
					var tandcconfirm = 0;
					$(event.currentTarget).find('button[data-action="save"]').attr('disabled', true);
				}
					loader("#halls_section", true, true);
						let promise = Ajax.call([{
							methodname: 'local_exams_validateexamschedule',
							args: {
								product_id: args.variation,
								hallscheduelid: args.hallscheduleid,
								profileid: args.profileid,

							}
						}]);

							promise[0].done((response) => {
							let homepage = new HomePageJs();
					
							loader("#halls_section", false);
							if(response.success == true){
								args.tandcconfirm = tandcconfirm;
								add_to_cart(args, $(root).parent());
								window.location.href = M.cfg.wwwroot+"/admin/tool/product/cart.php";
							}else{
							
								homepage.confirmbox(response.messages);
								$(e.currentTarget).attr('disabled', true);
							}
							
						}).fail( (error) => {
							loader("#halls_section", false);
							let homepage = new HomePageJs();
							homepage.confirmbox(error.messages);
						});



		});
		if(args.tandc == 1){
		form.show();
		}else{
			loader("#halls_section", true, true);
						let promise = Ajax.call([{
							methodname: 'local_exams_validateexamschedule',
							args: {
								product_id: args.variation,
								hallscheduelid: args.hallscheduleid,
								profileid: args.profileid,

							}
						}]);

							promise[0].done((response) => {
							let homepage = new HomePageJs();
					
							loader("#halls_section", false);
							if(response.success == true){
								
								add_to_cart(args, $(root).parent());
								window.location.href = M.cfg.wwwroot+"/admin/tool/product/cart.php";
							}else{
							
								homepage.confirmbox(response.messages);
								$(e.currentTarget).attr('disabled', true);
							}
							
						}).fail( (error) => {
							loader("#halls_section", false);
							let homepage = new HomePageJs();
							homepage.confirmbox(error.messages);
						});



		}

				}
			}else{
				
				add_to_cart(args, $(root).parent());
			}
			
			
		}else{
			let homepage = new HomePageJs();
			homepage.confirmbox(args['errortext']);
		}
	});
}




export const addprogramregister = ( root) => {

	$(root).on('click', (e) => {
		e.preventDefault();
		var args = get_attributes(root);
		args.tptandc = $(root).data('tptandc');
		args.isloggedin = $(root).data('isloggedin');
		args.programid = $(root).data('programid');

		let selector = '[data-action="'+args['parent']+'"]';
		let buttons = get_variation_buttons('[data-type="'+args['type']+'"]');
		
		
		if(args['checkout']){
			if(args.isloggedin==false){
					ModalFactory.create({type: ModalLogin.TYPE}, $(e.currentTarget)); 
				}
				else
				{
					if(args.tptandc==1)
					{
						const title = getString('tptandc', 'local_trainingprogram');
							const form = new ModalForm({
							formClass: 'local_trainingprogram\\form\\termsandconditions',
							args: {programid:args.programid},
							modalConfig: {title},
							saveButtonText: getString('confirm', 'local_trainingprogram'),

							});
							form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
			
						const data = event.detail;
							if(data.tptandc > 0) {
							$(selector).data('variation', args['variation']);
							$(selector).prop('disabled', false);
							var selectedString  = getString('selected' ,'tool_product');
							selectedString.done(function(html, js){
								$(root).text(html);
							});
								
							$("[data-action='"+args['parent']+"']").trigger('click');
							window.location.href = M.cfg.wwwroot+"/admin/tool/product/cart.php";
						}

						});
							if(args.tptandc == 1){
							form.show();
						}

						
						
						else
						{
							window.location.href = M.cfg.wwwroot+"/admin/tool/product/cart.php";	
						}
					}
					else
					{
						$(selector).data('variation', args['variation']);
							$(selector).prop('disabled', false);
							var selectedString  = getString('selected' ,'tool_product');
							selectedString.done(function(html, js){
								$(root).text(html);
							});
						$("[data-action='"+args['parent']+"']").trigger('click');
						window.location.href = M.cfg.wwwroot+"/admin/tool/product/cart.php";
					}
					
				}
			

			
		}
	    
	});
}
export const addregister = ( root,targetparent ) => {

   var args = get_attributes($(root));

   if(get_product_id(args)){

   	    add_to_cart(args, $(root).parent());


       render_template('tool_product/cart/register',targetparent, {checkout:true});


       window.location = M.cfg.wwwroot + "/admin/tool/product/cart.php";


   }else{
        let homepage = new HomePageJs();
        homepage.confirmbox(args['errortext']);
    }
}
export const add_order_seats = ( root ) => {

		var args = get_attributes($(root));


		if(get_product_id(args)){

        
            const title = getString('seatreservation', 'tool_product');
			
            const form = new ModalForm({
                formClass: 'tool_product\\form\\seatreservationform',
                args: {seatsdata: args['seatsdata'],productid: args['product'], autoapproval: args['autoapproval']},
                modalConfig: {title},
            });
		
            form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
				if (args['autoapproval'] == 1) {
					event.preventDefault();
					let promise = Ajax.call([{
						methodname: 'tool_product_postpaid_payments',
						args: {
							products: event.detail.returnparams
						}
					}]);
					promise[0].done((response) => {
						
						var params = {};
						params.orderid = response.paymentid;
						var promise = Ajax.call([{
							methodname: 'tool_product_get_orderinfo',
							args: params
						}]);
						promise[0].done(function(resp) {
							if (resp) {
								let promises = Ajax.call([{
									methodname: 'tool_product_generate_sadadbill',
									args: {
										products: resp.info
									}
								}]);
								render_template('tool_product/loader');
								promises[0].done((response) => {
									window.location.reload();
								}).fail( (error) => {
									let homepage = new HomePageJs();
									homepage.confirmbox(error.message);
								});
							}
						}).fail( (error) => {
							
							homepage.confirmbox(error.message);
						});
					}).fail( (error) => {				
						let homepage = new HomePageJs();
						homepage.confirmbox(error.error);
					});
				} else {
					let promise = Ajax.call([{
						methodname: 'tool_product_postpaid_payments',
						args: {
							products: event.detail.returnparams
						}
					}]);
					promise[0].done((response) => {
						let homepage = new HomePageJs();
						homepage.confirmbox(getString('ordersubmitted', 'tool_product'));
					}).fail( (error) => {				
						let homepage = new HomePageJs();
						homepage.confirmbox(error.error);
					});
				}
            });
            form.show();

		}else{
			let homepage = new HomePageJs();
			homepage.confirmbox(args['errortext']);
		}

}
export const add_learningtrack_order_seats = ( root ) => {


		var args = get_attributes($(root));



		if(get_product_id(args)){

        
            const title = getString('seatreservation', 'tool_product');
            
            const form = new ModalForm({
                formClass: 'tool_product\\form\\learningtrackseatreservationform',
                args: {seatsdata: args['seatsdata'],productid: args['product']},
                modalConfig: {title},
            });
            form.addEventListener(form.events.FORM_SUBMITTED, (event) => {


                Templates.render('tool_product/add_learningtrack_order_seats',{product_attributes:event.detail.returnparams})
                .then(function(html, js) {

                    $('[data-region="booking-order-summary"]').html(html);

                    $('[data-region="booking-order-summary"]').append(event.detail.returnurlbtn);

                   Templates.runTemplateJS(js);    

                    return;
                })
                .always(function() {
                    return;
                })
                .fail();
                    

            });
            form.show();

		}else{
			let homepage = new HomePageJs();
			homepage.confirmbox(args['errortext']);
		}

}
export const checkout = ( formdata ) => {
	get_checkout_summary(formdata);
}

export const cart = () => {
	get_product_information();
}	

export const cart_actions = ( root ) => {
	$(root).on('click', (e) => {
		e.preventDefault();
		let args = get_attributes($(root));
		switch(args['action']){
			case 'remove':
				remove_from_cart(args);
			break;
			case 'remove-group':
				remove_group_from_cart(args);
			break;
			case 'applycoupon':
				applycoupon_to_cart(args);
			break;
			case 'removecoupon':
				removecoupon_to_cart(args);
			break;
			case 'empty':
				empty_cart();
			break;
			default:
			break;
		}
	});
}

export const variations = ( root ) => {
	$(root).on('click', (e) => {
		e.preventDefault();
		var args = get_attributes(root);
		args.tptandc = $(root).data('tptandc');
		args.isloggedin = $(root).data('isloggedin');
		args.programid = $(root).data('programid');
		let selector = '[data-action="'+args['parent']+'"]';
		//alert(selector);
		let buttons = get_variation_buttons('[data-type="'+args['type']+'"]');
		
		if(args['checkout']){
			if(args.isloggedin==false){
					ModalFactory.create({type: ModalLogin.TYPE}, $(e.currentTarget)); 
			}
			else{
				
					if(args.tptandc==1)
					{
							const title = getString('tptandc', 'local_trainingprogram');
							const form = new ModalForm({
							formClass: 'local_trainingprogram\\form\\termsandconditions',
							args: {programid:args.programid},
							modalConfig: {title},
							saveButtonText: getString('confirm', 'local_trainingprogram'),
							});
							form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
							const data = event.detail;
							if(data.tptandc > 0) {
							$(selector).data('variation', args['variation']);
							$(selector).prop('disabled', true);	
							args.tptandc = 0;
							var selectedString  = getString('selected' ,'tool_product');
							selectedString.done(function(html, js){
								$(root).text(html);
							});
							$("[data-action='"+args['parent']+"']").trigger('click');
						}
						});
						
						if(args.tptandc == 1){
							form.show();
						}

					}
					else
					{
						$(selector).data('variation', args['variation']);
							$(selector).prop('disabled', false);	
							var selectedString  = getString('selected' ,'tool_product');
							selectedString.done(function(html, js){
								$(root).text(html);
							});
						$("[data-action='"+args['parent']+"']").trigger('click');
					}	
				}
			}
		});
	}

export const make_payment = (root) => {
	$(root).on('click', (e) => {
		e.preventDefault();
		e.stopImmediatePropagation();
		$(e.target).attr('disabled', true);
		let method = $('[name=payment-method]:checked').val();
		let products = $(root).data('summary');

		if(typeof method === 'undefined'){
			process_creditcard_payments(root, products);
		}

		switch(method){
			case 'postpaid':
				process_postpaid_payments(root, products);
			break;
			case 'prepaid':
				process_prepaid_payments(root, products);
			break;
			case 'telr':
			case 'default':
				process_creditcard_payments(root, products);
			break;

		}
	});
}
export const paymentsinit = () => {
    document.addEventListener('click', function(e) {
 
        let paymentsupdate = e.target.closest(Selectors.actions.paymentsupdate);
        if (paymentsupdate) {
            e.preventDefault();
            const title = getString('paymentsupdatedtls', 'tool_product',paymentsupdate.getAttribute('data-name'));
            const form = new ModalForm({
                formClass: 'tool_product\\form\\paymentsupdateform',
                args: {id: paymentsupdate.getAttribute('data-id')},
                modalConfig: {title},
                returnFocus: paymentsupdate,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();
        }

        let sendemailtoorgofficial = e.target.closest(Selectors.actions.sendemailtoorgofficial);
        if (sendemailtoorgofficial) {
        	
            e.preventDefault();
            const title = getString('sendemailtoorgofficial', 'tool_product',sendemailtoorgofficial.getAttribute('data-orgoffcialname'));
            const form = new ModalForm({
                formClass: 'tool_product\\form\\sendemailform',
                args: {productid:sendemailtoorgofficial.getAttribute('data-productid')},
                modalConfig: {title},
                returnFocus: sendemailtoorgofficial,
            });
            form.addEventListener(form.events.FORM_SUBMITTED, () => window.location.reload());
            form.show();

        }
        
        let approvalseats = e.target.closest(Selectors.actions.approvalseats);
        if (approvalseats) {
            e.preventDefault();
			e.stopPropagation();
			approvalid = approvalseats.getAttribute('data-id');
            ModalFactory.create({
                title: getString('approveorder', 'tool_product'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('approveorderconfirm', 'tool_product')
            }).done(function(modal) {
                this.modal = modal;
				//$(modal.getRoot()).attr('data-backdrop','static').attr('data-keyboard',false);
                modal.setSaveButtonText(getString('approve', 'tool_product'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
					Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                        Templates.appendNodeContents('.modal-content', html, js);
                    });
					$(e.currentTarget).find('button[data-action="save"]').attr('disabled', true);
                    var params = {};
                    params.orderid = approvalid;
                    var promise = Ajax.call([{
                        methodname: 'tool_product_get_orderinfo',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
                        if (resp) {
							if(resp.seatsinfo) {
								let parsed_data = JSON.parse(resp.seatsinfo);
								if(parsed_data.seatnotexist) {
									let seats = getString('userscountismore', 'local_exams', parsed_data.availableseats);
									let homepage = new HomePageJs();
									modal.hide();
									homepage.confirmbox(seats);
								}
							} else {
								let promises = Ajax.call([{
									methodname: 'tool_product_generate_sadadbill',
									args: {
										products: resp.info
									}
								}]);
								render_template('tool_product/loader');
								promises[0].done((response) => {
									window.location.href = response.returnurl;
								}).fail( (error) => {
									let homepage = new HomePageJs();
									homepage.confirmbox(error.message);
								});
							}
                        }
                    }).fail( (error) => {
						let homepage = new HomePageJs();
						homepage.confirmbox(error.message);
					});
                }.bind(this));
                modal.show();
            }.bind(this));
        }
        let cancelorder = e.target.closest(Selectors.actions.cancelorder);
        if (cancelorder) {
            e.preventDefault();
			approvalid = cancelorder.getAttribute('data-id');
            ModalFactory.create({
                title: getString('rejectorder', 'tool_product'),
                type: ModalFactory.types.SAVE_CANCEL,
                body: getString('rejectorderconfirm', 'tool_product')
            }).done(function(modal) {
                this.modal = modal;
                modal.setSaveButtonText(getString('reject', 'tool_product'));
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    Templates.renderForPromise('tool_product/loader', {}).then(({html, js}) => {
                        Templates.appendNodeContents('.modal-content', html, js);
                    });
                    var params = {};
                    params.orderid = approvalid;
                    var promise = Ajax.call([{
                        methodname: 'tool_product_rejectorder',
                        args: params
                    }]);
                    promise[0].done(function(resp) {
						console.log(resp.response);
						if(resp.response == 'success') {
							modal.hide();
							let homepage = new HomePageJs();
							homepage.confirmbox(getString('ordercancelled', 'tool_product'));
							setTimeout(function() {
								window.location = M.cfg.wwwroot + '/admin/tool/product/orderapproval.php';
							},4000);
                        } else {
							modal.hide();
							let homepage = new HomePageJs();
							homepage.confirmbox(resp.response);
						}
                    }).fail( (error) => {
						
					});
                }.bind(this));
                modal.show();
            }.bind(this));
        }
    });
};

export const grouped_products = ( root ) => {
	$(root).on('click', (e) => {
		e.preventDefault();
		var args = get_attributes($(root));	
		if(args['grouped']){
			var radios = $('#user_assign_entities :radio');
    		var values = {};
    		radios.each(function() {
    			values[this.name] = $(this).val();
            });
            Object.entries(values).forEach( function(value, index) {
            	args['product'] = value[1];
            	add_to_cart(args, false);
            });
            let products = get_products();
            render_template('tool_product/cart/go-to-cart', $(root).parent(), {isloggedin: args['loggedin'], checkout: args['checkout']});
        }
	});
};

export const reset_cart = () => {
	empty_cart();
}

export default { add, cart, checkout, variations, cart_actions, make_payment,paymentsinit,add_order_seats,addregister, grouped_products,add_learningtrack_order_seats,addprogramregister, reset_cart};

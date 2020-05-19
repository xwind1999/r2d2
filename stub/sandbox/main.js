var moment = require('helpers/moment.min.js');
state.property = require("./data/property.js").property;
state.product = require("./data/product.js").property;
state.availabilities = state.availabilities || [];
state.cmhubavailabilities = state.cmhubavailabilities || [];
state.prices = state.prices || [];
state.booking = state.booking || [];
state.box = state.box || [];
state.experience = state.experience || [];
state.component = state.component || [];
state.box_experience = state.box_experience || [];
state.experience_component = state.experience_component || [];
state.experience_price = state.experience_price || [];

//set Data in state
setDataInState();
setAvailabilityDataInState();

/*Sandbox.define('/api/room_availabilities', 'OPTIONS', function (req, res) {
    res.set('Access-Control-Allow-Origin', '*');
});
*/
Sandbox.define('/api/room_availabilities', 'GET', function (req, res) {
    // Check the request, make sure it is a compatible type
    res.set('Access-Control-Allow-Origin', '*');
    if (!req.is('application/json')) {
        //return res.send(400, 'Invalid content type, expected application/json');
    }

    // Set the type of response, sets the content type.
    res.type('application/json');

    // Set the status code of the response.
    res.status(200);

    // Send the response body.
    var response = buildResponse();

    res.json(response);

    function buildResponse() {
        var responseArray = [];
        var numberOfDays = calculateDaysBetweenStartDateAndEndDate(req.query.startDate, req.query.endDate);
        var from = moment(req.query.startDate, 'YYYY-MM-DD');
        for (var i = 0; i <= numberOfDays; i++) {
            if (state.availabilities[req.query.roomId][from.clone().add(i, 'days').format('YYYY-MM-DD')]) {
                responseArray.push(state.availabilities[req.query.roomId][from.clone().add(i, 'days').format('YYYY-MM-DD')]);
            }
        }
        return responseArray;
    }
});

Sandbox.define('/broadcast-listener/partner', 'POST', function(req, res){

    state.partner = state.partner || [];
    //validate request from EAI about partner information
    if (req.body.id === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "id : This field cant be null"
        })
    }

    if (req.body.status === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "status : This field cant be null"
        })
    }

    if (req.body.currencyCode === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "currencyCode : This field cant be null"
        })
    }

    if (req.body.isChannelManagerEnabled === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "isChannelManagerEnabled : This field cant be null"
        })
    }

    state.partner.push(req.body)

    return res.json(202, {
        status: "Relationship handled"
    })
});

Sandbox.define('/broadcast-listener/product', 'POST', function(req, res) {

    // validate request from EAI about Product information
    if (req.body.id === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "id : This field cant be null"
        })
    }

    if (req.body.isSellable === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "isSellable : This field cant be null"
        })
    }

    if (req.body.isReservable === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "isReservable : This field cant be null"
        })
    }

    if (req.body.status === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "status : This field cant be null"
        })
    }

    if (req.body.type === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "type : This field cant be null"
        })
    }

    if (req.body.type === "dev" || req.body.type === "mev" || req.body.type === "mlv") {
        state.box.push(req.body)
    } else if (req.body.type === "component") {
        state.component.push(req.body)
    } else
        state.experience.push(req.body)

    return res.json(202, {
        status: "Relationship handled"
    })
});

Sandbox.define('/broadcast-listener/product-relationship','POST',function (req, res) {


    // validate request from EAI about Product-relationship
    if (req.body.parentProduct === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "parentProduct : This field cant be null"
        })
    }

    if (req.body.childProduct === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "childProduct : This field cant be null"
        })
    }

    if (req.body.isEnabled === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "isEnabled : This field cant be null"
        })
    }

    if (req.body.relationshipType === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "relationshipType : This field cant be null"
        })
    }

    if (req.body.sortOrder === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "sortOrder : This field cant be null"
        })
    }

    if (req.body.relationshipType === "box-experience")
        state.box_experience.push(req.body)
    else
        state.experience_component.push(req.body)

    return res.json(202, {
        status: "Relationship handled"
    })
});

Sandbox.define('/broadcast-listener/price-information','POST',function (req, res) {

    // validate request from EAI about Price Information
    if (req.body.product === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "product : This field cant be null"
        })
    }

    if(!req.is('application/json'))
    {
        return res.json(400, {
            details: "Invalid Content type, expected application/json"
        })
    }

    state.experience_price.push(req.body)

    return res.json(202, {
        status: "Price information handled"
    })
});

// Booking transactions
// Booking creation
Sandbox.define('/api/booking','POST',function (req, res) {

    if(req.body === null) {
        return res.json(204, {
            status: "No Content",
            details: "There is no json body in request"
        })
    }

    if(!req.is('application/json'))
    {
        return res.json(400, {
            details: "Invalid Content type, expected application/json"
        })
    }

    if (req.body.bookingId === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "bookingId : This field cant be null"
        })
    }

    if (req.body.box === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "box : This field cant be null"
        })
    }

    if (req.body.experience === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "experience : This field cant be null"
        })
    }

    if (req.body.voucher === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "voucher : This field cant be null"
        })
    }

    if (req.body.startDate === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "startDate : This field cant be null"
        })
    }

    if (req.body.endDate === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "endDate : This field cant be null"
        })
    }

    if (req.validationErrors()) {
        return res.json(400,{
            status: "Bad Request",
            details:"Not a valid JSON"
        });
    }

    state.booking.push(req.body)

    return res.json(201,{
        status: "Booking Created"
    })


})

// Booking Confirmation - Complete/Cancelled
Sandbox.define('/api/booking','PATCH',function (req, res) {

    if(req.body === null) {
        return res.json(204, {
            status: "No Content",
            details: "There is no json body in request"
        })
    }

    if(!req.is('application/json'))
    {
        return res.json(400, {
            details: "Invalid Content type, expected application/json"
        })
    }

    if (req.body.bookingId === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "bookingId : This field cant be null"
        })
    }

    if (req.body.status === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "status : This field cant be null"
        })
    }

    state.booking.push(req.body)
    return res.json(201, {
        status: "Booking Updated"
    })
})

// CMHUB Get Availability
Sandbox.define('/api/availability','GET',function (req, res) {

    if(!req.is('application/json'))
    {
        return res.json(400, {
            details: "Invalid Content type, expected application/json"
        })
    }

    if(req.query.productId === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "ProductID : This field cant be null"
        })
    }

    if(req.query.start === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "start : This value should not be blank"
        })
    }

    if(req.query.end === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "end : This value should not be blank"
        })
    }


    // Set the type of response, sets the content type.
    res.type('application/json');

    res.status(200);

    var response = Response();
    res.json(response)

    function Response() {
        var responseArray = [];
        var numberOfDays = calculateDaysBetweenStartDateAndEndDate(req.query.start, req.query.end);
        var from = moment(req.query.start, 'YYYY-MM-DD');
        for (var i = 0; i <= numberOfDays; i++) {
            if (state.cmhubavailabilities[req.query.productId][from.clone().add(i, 'days').format('YYYY-MM-DD')]) {
                responseArray.push(state.cmhubavailabilities[req.query.productId][from.clone().add(i, 'days').format('YYYY-MM-DD')]);
            }
        }
        return responseArray;
    }

})

/*Sandbox.define('/api/room_prices', 'GET', function (req, res) {
    res.set('Access-Control-Allow-Origin', '*');
    res.status(200);
    var responseArray = [];
    var numberOfDays = calculateDaysBetweenStartDateAndEndDate(req.query.startDate, req.query.endDate);
    var from = moment(req.query.startDate, 'YYYY-MM-DD');
    for (var i = 0; i <= numberOfDays; i++) {
        if (state.prices[req.query.roomId][from.clone().add(i, 'days').format('YYYY-MM-DD')]) {
            responseArray.push(state.prices[req.query.roomId][from.clone().add(i, 'days').format('YYYY-MM-DD')]);
        }
    }
    return res.json(responseArray);
});Sandbox.define('/api/room_prices', 'OPTIONS', function (req, res) {
    res.set('Access-Control-Allow-Origin', '*');
});*/

function calculateDaysBetweenStartDateAndEndDate(start, end) {
    var from = moment(start, 'YYYY-MM-DD');
    var to = moment(end, 'YYYY-MM-DD');
    return to.diff(from, 'days');
}
function setDataInState()
{
// populate state of availability and prices of each room if needed
    state.property.rooms.forEach(function (room) {
        if (!state.availabilities[room.id]) {
            state.availabilities[room.id] = [];
            for (var i = 0; i < 60; i++) {
                var newDate = moment().add(i, 'days').format('YYYY-MM-DD');
                state.availabilities[room.id][newDate] = {
                    date: newDate,
                    partner_golden_id: 'partner'+ i,
                    room_golden_id: 'room' + i,
                    rate_band_golden_id: 'rate' + i,
                    stock: 5,
                };
            }
        }
        if (!state.prices[room.id]) {
            state.prices[room.id] = [];
            for (var i = 0; i < 60; i++) {
                var newDate = moment().add(i, 'days').format('YYYY-MM-DD');
                state.prices[room.id][newDate] = {
                    date: newDate,
                    partner_golden_id: 'partner'+ i,
                    room_golden_id: 'room' + i,
                    rate_band_golden_id: 'rate' + i,
                    price: 67.90 + i,
                    currency: 'EUR'
                }
            }
        }
    });
}

function setAvailabilityDataInState()
{
// populate state of availability and prices of each room if needed
    state.product.products.forEach(function (product) {
        if (!state.cmhubavailabilities[product.id]) {
            state.cmhubavailabilities[product.id] = [];
            for (var i = 0; i < 60; i++) {
                var newDate = moment().add(i, 'days').format('YYYY-MM-DD');
                state.cmhubavailabilities[product.id][newDate] = {
                    date: newDate,
                    quantity: 40
                };
            }
        }
    });
}

var moment = require('helpers/moment.min.js');
state.property = require("./data/property.js").property;
state.availabilities = state.availabilities || [];
state.prices = state.prices || [];
state.booking = state.booking || [];

//set Data in state
 setDataInState();

Sandbox.define('/api/room_availabilities', 'OPTIONS', function (req, res) {
    res.set('Access-Control-Allow-Origin', '*');
});

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

Sandbox.define('/api/broadcast-listeners/partner', 'POST', function(req, res){

    state.booking = state.partner || [];
    //validate request from EAI about partner information
    if (req.body.id === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"id\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.body.status === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"status\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.body.currencyCode === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"currencyCode\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.body.isChannelManagerEnabled === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"isChannelManagerEnabled\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    state.partner.push(req.body)

    return res.json(202, {
        status: "Relationship handled"
    })
});

Sandbox.define('/api/broadcast-listeners/product', 'POST', function(req, res){

    // validate request from EAI about Product information
    if (req.body.id === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"id\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.body.isSellable === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"isSellable\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.body.isReservable === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"isReservable\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.body.status === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"status\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.body.type === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"type\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    return res.json(202, {
        status: "Relationship handled"
    })
});

Sandbox.define('/api/broadcast-listeners/product-relationship','POST',function (req, res) {
    // validate request from EAI about Product-relationship
    if (req.body.parentProduct === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"parentProduct\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.body.childProduct === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"childProduct\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.body.isEnabled === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"isEnabled\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.body.relationshipType === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"relationshipType\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.body.sortOrder === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"sortOrder\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    return res.json(202, {
        status: "Relationship handled"
    })
});

// Booking transactions
// Booking creation
Sandbox.define('/api/booking','POST',function (req, res) {

    if (req.body.bookingId === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"bookingId\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.body.box === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"Box ID\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.body.experience === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"Experience Value\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.body.voucher === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"Voucher\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.body.startDate === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"Start Date\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.body.endDate === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"End Date\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.length === 0) {
        return res.json(204, {
            status: "No Content",
            details: "There is no body in request"
        })
    }

    if(!req.is('application/json'))
    {
        return res.send(400, 'Invalid Content type, expected application/json')
    }

    if (req.validationErrors()) {
        return res.json(400,{
            status: "Bad Request",
            details:"Not a valid JSON"
        });
    }

    state.booking.push(req.body)
    return res.json(201,{
        status: "Created"
    })


})

// Booking Confirmation - Complete/Cancelled
Sandbox.define('/api/booking','PATCH',function (req, res) {

    if (req.body.status === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"status\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.body.voucher === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"voucher\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if (req.length === 0) {
        return res.json(204, {
            status: "No Content",
            details: "There is no body in request"
        })
    }

    if (req.body.bookingId === undefined) {
        return res.json(422, {
            status: "Error: Unprocessable Entity",
            details: "\"bookingId\": [\n" +
                "        \"This value should not be blank.\"\n" +
                "      ]\n" +
                " "
        })
    }

    if(!req.is('application/json'))
    {
        return res.send(400, 'Invalid Content type, expected application/json')
    }

    if (req.validationErrors()) {
        return res.json(400,{
            status: "Bad Request",
            details:"Not a valid JSON"
        });
    }

    state.booking.push(req.body)
    return res.json(201, {
        status: "Created"
    })
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

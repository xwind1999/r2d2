var moment = require('helpers/moment.min.js');
state.property = require("./data/property.js").property;
state.availabilities = state.availabilities || [];
state.prices = state.prices || [];

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

Sandbox.define('/api/broadcast_listeners/partners', 'POST', function(req, res){

    // validate username is present
    if (req.body.golden_id === undefined) {
        return res.json(400, {
            status: "error",
            details: "Missing golden_id"
        })
    }

    if (req.body.currency === undefined) {
        return res.json(400, {
            status: "error",
            details: "Missing currency"
        })
    }

    if (req.body.cease_date === undefined) {
        return res.json(400, {
            status: "error",
            details: "Missing case date"
        })
    }

    return res.json({
        status: "ok"
    })
});

Sandbox.define('/api/broadcast_listeners/products', 'POST', function(req, res){

    // validate username is present
    if (req.body.golden_id === undefined) {
        return res.json(400, {
            status: "error",
            details: "Missing golden_id"
        })
    }

    if (req.body.name === undefined) {
        return res.json(400, {
            status: "error",
            details: "Missing name"
        })
    }

    if (req.body.description === undefined) {
        return res.json(400, {
            status: "error",
            details: "Missing description"
        })
    }

    return res.json({
        status: "ok"
    })
});

Sandbox.define('/api/broadcast_listeners/channel_room_availabilities', 'POST', function(req, res){

    // validate username is present
    if (req.body.partner_golden_id === undefined) {
        return res.json(400, {
            status: "error",
            details: "Missing partner_golden_id"
        })
    }

    if (req.body.stock === undefined) {
        return res.json(400, {
            status: "error",
            details: "Missing stock"
        })
    }

    if (req.body.room_golden_id === undefined) {
        return res.json(400, {
            status: "error",
            details: "Missing room_golden_id"
        })
    }

    return res.json({
        status: "ok"
    })
});

Sandbox.define('/api/room_prices', 'OPTIONS', function (req, res) {
    res.set('Access-Control-Allow-Origin', '*');
});

Sandbox.define('/api/room_prices', 'GET', function (req, res) {
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
});


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

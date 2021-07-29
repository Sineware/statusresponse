const express = require("express");
const fs = require('fs');
const got = require('got');

const app = express();
const port = 3000;


let config = JSON.parse(fs.readFileSync(__dirname + '/config.json').toString());
let state;

async function writeConfig() {
    fs.writeFileSync(__dirname + '/config.json', JSON.stringify(config));
}

app.get("/api/config", (req, res) => {
    res.send(config);
});
app.get("/api/state", (req, res) => {
    res.send(state);
});

app.listen(port, () => {
    console.log(`Sineware StatusResponse API Server listening at http://localhost:${port}`);
});

async function pokeServices() {
    for(let service of state) {
        if(service.type === "webservice") {
            try {
                const response = await got(service.url, {timeout: 10000, throwHttpErrors: false});
                console.log(response);
                service.ping = response.timings.phases;
                service.status = response.statusCode;
                service.up = (response.statusCode === 200);
            } catch(e) {
                console.log(e);
                service.ping = null;
                service.status = 408;
                service.up = false;
            }

        }
    }
}

async function main() {
    console.log("Registering services...");
    console.log("Updating state...");
    state = config.services;
    await pokeServices();
    setInterval(async () => {
        await pokeServices();
    }, 60000);
}
main().then();
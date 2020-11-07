const express = require('express')
const path = require('path');
const multer = require('multer');
const fs = require('fs');
const $ = require( "jquery" );
const upload = multer({ dest: 'uploads/' } );
const low = require('lowdb')
const FileSync = require('lowdb/adapters/FileSync')
const adapter = new FileSync('db.json')
const db = low(adapter)

const fileExists = (file) => {
    return new Promise((resolve, reject) => {
        fs.access(file, fs.constants.F_OK, (err) => {
            err ? reject(false) : resolve(true)
        });
    })
}

function initLowDB(){

    let tmp = ""

    fs.readFile('./templates/template_chart.txt', 'utf8' , (err, data) => {
        tmp = JSON.stringify(data)
    })
    db.defaults('template.chart', tmp).write()
    
    /*
    fs.readFile('./templates/template_tab.txt', 'utf8' , (err, data) => {
        db.defaults('template.tab', data).write()
    })
    */
}

function getAllDate(data) {
    return data.split(',').filter(d => d != '' && d != " " && d != "\r")
}

function createChartFile(name, data){
    fs.writeFile("./charts/" + name, data, function (err) {
        if (err) return console.log(err);
        console.log('File ' + name + " done !");
    });

    db.set(name, data).write()
}

function getSingleActivities(activites) {
    let activitetab = []
    Object.values(activites).forEach(element => {
        activitetab = activitetab.concat(element.matin)
        activitetab = activitetab.concat(element.aprem)
    })

    return [... new Set(activitetab)].sort()

}

function getAllEleve(data, dates) {
    let tab = []
    let current = []
    let currentDate
    data.forEach(element => {
        current = element.split(',')
        if(current.slice(4).join('') !== ("" || "\r")){
            currentDate = {}
            dates.forEach(function(el, index){
                currentDate[el] = {
                    matin : current[4 + index], 
                    aprem : current[4 + index + 1]
                }
            })
            tab.push({
                nom: current[0], 
                prenom : current[1],
                sexe : current[2],
                classe : current[3],
                jours : currentDate
            })
        }
    });

    return tab
}

function getAllActivites(data, dates){
    let activites = {}
    dates.forEach(element => {
        activites[element] = {
            matin : [... new Set(data.map(x => x.jours[element].matin))].filter(x => x), 
            aprem: [... new Set(data.map(x => x.jours[element].aprem))].filter(x => x)
        }
    })

    return activites
    
}

function makeGraphe(idGraphe, titre, series, nameFileChart) {
    let grapheTexte = ""
    fs.readFile('template_chart.txt', 'utf8' , (err, data) => {
        if (err) console.error(err)
        grapheTexte = data.replace(/{%IDGRAPHE%}/g, idGraphe).replace(/{%TITIRE%}/g, titre).replace(/{%SERIES%}/g, JSON.stringify(series))
        createChartFile(nameFileChart, grapheTexte)
    })
}

function getSeriesEleves(data) {
    let elevesPrimaire = [
        {"name":"CP","data":[{"name":"CP","y": data.filter(e => e.classe.match(/cp/i)).length}]},
        {"name":"CE","data":[{"name":"CE","y": data.filter(e => e.classe.match(/ce/i)).length}]},
        {"name":"CM","data":[{"name":"CM","y": data.filter(e => e.classe.match(/cm/i)).length}]}
    ]

    let elevesCollege = [
        {"name":"6ème","data":[{"name":"6ème","y": data.filter(e => e.classe.match(/6ème/i)).length}]},
        {"name":"5ème","data":[{"name":"5ème","y": data.filter(e => e.classe.match(/5ème/i)).length}]},
        {"name":"4ème","data":[{"name":"4ème","y": data.filter(e => e.classe.match(/4/i)).length}]},
        {"name":"3ème","data":[{"name":"3ème","y": data.filter(e => e.classe.match(/3/i)).length}]},
        {"name":"2nde","data":[{"name":"2nde","y": data.filter(e => e.classe.match(/2nd/i)).length}]},
        {"name":"1ère","data":[{"name":"1ère","y": data.filter(e => e.classe.match(/1e/i)).length}]}
    ]

    return [elevesPrimaire, elevesCollege]
}


function getSeriesActities(activites){
    let activitetab = []
    Object.values(activites).forEach(element => {
        activitetab = activitetab.concat(element.matin)
        activitetab = activitetab.concat(element.aprem)
    })

    let singleActivity = [... new Set(activitetab)].sort()

    let series = []
    singleActivity.forEach(el => {
        series.push({
            name : el,
            data : [{
                name: el,
                y: activitetab.filter(v => v == el).length
            }]
        })
    })
    return series
}

function createArrayFiles(name, data){
    fs.writeFile("./arrays/" + name, data, function (err) {
        if (err) return console.log(err);
        console.log('File ' + name + " done !");
    });
}

function getActivitiesTabs(activites, dates, eleves){
    let r = {}

    let activitetab = []
    Object.values(activites).forEach(element => {
        activitetab = activitetab.concat(element.matin)
        activitetab = activitetab.concat(element.aprem)
    })

    let singleActivity = [... new Set(activitetab)].sort()

    db.set('activite', singleActivity).write()

    singleActivity.forEach(function(el) {
        
        r[el] = {dates : [], eleves : []}
        dates.forEach(function(d){
            let x = eleves.filter(e => e.jours[d].matin == el || e.jours[d].aprem == el)
            if(x.length != 0){
                r[el].dates.push(d)
                r[el].eleves.push(x)
            }
        })
    })

    return r
}

function flaten(f){
    let r = []
    f.forEach(e => {
        e.map(e => r.push(e))
    })
    return r
}

function makeTabActivities(tabActivities) {
    
    db.set('htmlRecap', []).write()
    db.set('tableauActivite', tabActivities).write()
    let elf = null
    let arrayTexte = ""
    Object.keys(tabActivities).forEach(function(el){
        arrayTexte = ""
            fs.readFile('template_array.txt', 'utf8' , (err, data) => {
                if (err) console.error(err)
                elf = flaten(tabActivities[el].eleves)
                arrayTexte = data
                    .replace(/{%ACTIVITE%}/g, el)
                    .replace(/{%DATES%}/g,  tabActivities[el].dates.join(" - "))
                    .replace(/{%PF%}/g,  elf.filter(e => e.sexe == "F" && e.classe.match(/c/i)).length)
                    .replace(/{%PM%}/g,  elf.filter(e => e.sexe == "M" && e.classe.match(/c/i)).length)
                    .replace(/{%PT%}/g,  elf.filter(e => e.classe.match(/c/i)).length)
                    .replace(/{%CF%}/g,  elf.filter(e => e.sexe == "F" && e.classe.match(/6|5|4|3|2nd|1ère/i)).length)
                    .replace(/{%CM%}/g,  elf.filter(e => e.sexe == "M" && e.classe.match(/6|5|4|3|2nd|1ère/i)).length)
                    .replace(/{%CT%}/g,  elf.filter(e => e.classe.match(/6|5|4|3|2nd|1ère/i)).length)
                    .replace(/{%FT%}/g,  elf.filter(e => e.sexe == "F" && (e.classe.match(/c/i) || e.classe.match(/6|5|4|3|2nd|1ère/i))).length)
                    .replace(/{%MT%}/g,  elf.filter(e => e.sexe == "M" && (e.classe.match(/c/i) || e.classe.match(/6|5|4|3|2nd|1ère/i))).length)
                    .replace(/{%TT%}/g,  elf.length)

                db.get('htmlRecap').push(arrayTexte).write()

                createArrayFiles(el + "Array", arrayTexte)
            })
    })
}

const app = express()

app.get('/', (req, res) => {
    res
    .sendFile(path.join(__dirname+'/index.html'))
})

app.get('/result', (req, res) => {
    res
    .sendFile(path.join(__dirname+'/result.html'))
})

app.get('/graphe.js', (req, res) => {
    res
    .sendFile(path.join(__dirname+'/graphe.js'))
})

app.get('/script.js', (req, res) => {
    res
    .sendFile(path.join(__dirname+'/script.js'))
})

app.post('/file-upload', upload.single('file'), (req, res) => {
    const data = fs.readFileSync(req.file.path, 'utf8')
    let dates = getAllDate(data.split("\n")[1])
    let eleves = getAllEleve(data.split("\n").slice(3), dates)
    let allActivites = getAllActivites(eleves, dates)
    let serieActivities = getSeriesActities(allActivites)
    let eleveSeries = getSeriesEleves(eleves)
    let tabs = getActivitiesTabs(allActivites, dates, eleves)
    makeGraphe('idChart', 'Toutes les activités', serieActivities, "activitiesCharts")
    makeGraphe('idChartElevePrimiare', 'Tous les primaires', eleveSeries[0], "elevePrimiareChart")
    makeGraphe('idChartEleveCollege', 'Tous les collègiens', eleveSeries[1], "eleveCollegeChart")
    makeTabActivities(tabs)
})

app.get('/makeCharts', (req, res) => {

    let chart1 = db.get('activitiesCharts').value() // fs.readFileSync(path.join(__dirname + '/charts/activitiesCharts'), 'utf8')
    let chart2 = db.get('elevePrimiareChart').value() // fs.readFileSync(path.join(__dirname + '/charts/elevePrimiareChart'), 'utf8')
    let chart3 = db.get('eleveCollegeChart').value() // fs.readFileSync(path.join(__dirname + '/charts/eleveCollegeChart'), 'utf8')

    let arrayHTML = db.get('htmlRecap').value()

    res.send({
        chart1 : chart1,
        chart2 : chart2,
        chart3 : chart3,
        htmlArray : arrayHTML
    })
})


app.use('/assets', express.static('assets'))


const port = 12345;

// app.on('listening', initLowDB());

app.listen(port, () => {
    console.log('Serveur en marche')
})
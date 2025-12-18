"use strict";

class Ciudad {
    #nombre;
    #pais;
    #gentilicio;
    #poblacion;
    #coordenadas;
    
    constructor(nombre, pais, gentilicio){
        this.#nombre = nombre;
        this.#pais = pais;
        this.#gentilicio = gentilicio;
        this.#poblacion = 0;
        this.#coordenadas = null; 
    }

    set_poblacion_coordenadas(poblacion, coordenadas){
        this.#poblacion = poblacion;
        this.#coordenadas = coordenadas;
    }

    getNombreCiudad(){
        return this.#nombre;
    }

    getNombrePais(){
        return this.#pais;
    }

    getLista(){
        let html = "<ul>\n";
        html += `  <li>Gentilicio: ${this.#gentilicio}</li>\n`;
        html += `  <li>Población: ${this.#poblacion} habitantes</li>\n`;
        html += "</ul>";
        return html;
    }

    escribirCoordenadas(){
        const fragment = document.createDocumentFragment();

        if (this.#coordenadas){
            const h4 = document.createElement('h4');
            h4.textContent = `Coordenadas de ${this.#nombre}`; 
            fragment.appendChild(h4);

            const ul = document.createElement('ul');
            
            const liLat = document.createElement('li');
            liLat.textContent = `Latitud: ${this.#coordenadas.latitud}`;
            ul.appendChild(liLat);
            
            const liLong = document.createElement('li');
            liLong.textContent = `Longitud: ${this.#coordenadas.longitud}`;
            ul.appendChild(liLong);
            
            fragment.appendChild(ul);
            
        } else{
            const p = document.createElement('p');
            p.textContent = `Las coordenadas de ${this.#nombre} aún no se han establecido`; 
            fragment.appendChild(p);
        }
        
        return fragment; 
    }

    getMeteorologiaCarrera(){
        let fecha = "2025-06-08"
        let urlApi = `https://archive-api.open-meteo.com/v1/archive?latitude=${this.#coordenadas.latitud}&longitude=${this.#coordenadas.longitud}&start_date=${fecha}&end_date=${fecha}&daily=sunrise,sunset&hourly=temperature_2m,rain,relative_humidity_2m,wind_speed_10m,wind_direction_10m,apparent_temperature&timezone=Europe%2FBerlin`;

        $.ajax({
            dataType: "json",
            url: urlApi,
            method: "GET",
            success: (datos) => {
                let tiempo = this.#procesarJSONCarrera(datos);
                $("main").append(tiempo);
            },
            error: (xhr, status, error) => {
                const p = document.createElement("p");
                p.textContent = "Error: No se ha podido cargar la meteorología de la carrera";
                $("main").append(p);
                console.error("Error en la meteorología de los entrenos:", status, error);
            }
        });
    }

    getMeteorologiaEntrenos(){
        let fechaInicio = "2025-06-05"
        let fechaFin = "2025-06-07"
        let urlApi = `https://archive-api.open-meteo.com/v1/archive?latitude=${this.#coordenadas.latitud}&longitude=${this.#coordenadas.longitud}&start_date=${fechaInicio}&end_date=${fechaFin}&hourly=temperature_2m,rain,relative_humidity_2m,wind_speed_10m&timezone=Europe%2FBerlin`;
        $.ajax({
            dataType: "json",
            url: urlApi,
            method: "GET",
            success: (datos) => {
                console.log(datos);
                let tiempo = this.#procesarJSONEntrenos(datos);
                $("main").append(tiempo);
            },
            error: (xhr, status, error) => {
                const p = document.createElement("p");
                p.textContent = "Error: No se ha podido cargar la meteorología de los entrenos";
                $("main").append(p);
                console.error("Error en la meteorología de los entrenos:", status, error);
            }
        });
    }

    #procesarJSONEntrenos(datos){
        const fragment = document.createDocumentFragment();
        const section = document.createElement("section");
        
        const h3 = document.createElement('h3');
        h3.textContent = `Días de Entrenamientos (${datos.hourly.time[0].substring(0, 10)} a ${datos.hourly.time.at(-1).substring(0, 10)})`;
        section.appendChild(h3);

        const hourly = datos.hourly;
        const units = datos.hourly_units;
        const numHours = hourly.time.length;
        
        // Definimos las métricas que vamos a promediar
        const metricasAAnalizar = [
            { key: 'temperature_2m', label: 'Temperatura Media', unit: units.temperature_2m },
            { key: 'rain', label: 'Lluvia Total', unit: units.rain, isSum: true }, // La lluvia se suma, no se promedia
            { key: 'relative_humidity_2m', label: 'Humedad Media', unit: units.relative_humidity_2m },
            { key: 'wind_speed_10m', label: 'Viento Medio', unit: units.wind_speed_10m }
        ];

        // Array para almacenar los resultados diarios
        const resultadosDiarios = [];
        const horasPorDia = 24;
        
        // Iterar sobre los días (cada 24 horas)
        for (let i = 0; i < numHours; i += horasPorDia) {
            const diaInicio = i;
            const diaFin = i + horasPorDia;

            // Extraer la fecha (solo la parte YYYY-MM-DD)
            const fecha = hourly.time[diaInicio].substring(0, 10);
            const resultadosDia = { fecha };
            
            // Calcular la suma de cada métrica para las 24 horas
            for (const metrica of metricasAAnalizar) {
                let suma = 0;
                
                // Recorrer las 24 horas del día
                for (let j = diaInicio; j < diaFin && j < numHours; j++) {
                    // Asegurarse de que el valor existe antes de sumar
                    if (typeof hourly[metrica.key][j] === 'number') {
                        suma += hourly[metrica.key][j];
                    }
                }
                
                // Aplicar el cálculo: Promedio (si no es suma) o Suma
                if (metrica.isSum) {
                    // Lluvia: Suma de las 24 horas
                    resultadosDia[metrica.key] = suma.toFixed(2);
                } else {
                    // Promedio: Suma / 24
                    const promedio = suma / horasPorDia;
                    resultadosDia[metrica.key] = promedio.toFixed(1); // Un decimal para temperatura/humedad/viento
                }
            }
            resultadosDiarios.push(resultadosDia);
        }

        // Rellenar con los resultados diarios
        resultadosDiarios.forEach(dia => {
            const h3Dia = document.createElement('h4');
            h3Dia.textContent = `Día: ${dia.fecha}`;
            section.appendChild(h3Dia);

            const ulDia = document.createElement('ul');

            metricasAAnalizar.forEach(metrica => {
                const li = document.createElement('li');
                const value = dia[metrica.key];
                const label = metrica.label;
                const unit = metrica.unit;
                
                li.textContent = `${label}: ${value} ${unit}`;
                ulDia.appendChild(li);
            });

            section.appendChild(ulDia);
        });
        fragment.append(section);
        return fragment;
    }

    #procesarJSONCarrera(datos){
        const fragment = document.createDocumentFragment();
        const section = document.createElement("section");

        const h3 = document.createElement('h3');
        h3.textContent = `Meteorología de la Carrera (Día: ${datos.daily.time[0]})`;
        section.appendChild(h3);

        const ulDiario = document.createElement('ul');
        const liSalida = document.createElement('li');
        liSalida.innerHTML = `Salida del Sol: ${datos.daily.sunrise[0].substring(11, 16)} h`;
        ulDiario.appendChild(liSalida);

        const liPuesta = document.createElement('li');
        liPuesta.innerHTML = `Puesta del Sol: ${datos.daily.sunset[0].substring(11, 16)} h`;
        ulDiario.appendChild(liPuesta);
    
        section.appendChild(ulDiario);

        const hourly = datos.hourly;
        const units = datos.hourly_units;

        // Lista principal para las horas
        const ulHoras = document.createElement('ul');
        
        // Asumimos que todos los arrays de datos horarios tienen la misma longitud
        const numHours = hourly.time.length;

        // Sustituir el bucle anterior por este:
        for (let i = 0; i < hourly.time.length; i++) {
            const horaCompleta = hourly.time[i].substring(11, 13); // Extrae solo el número de la hora (ej: "14")
            
            // Filtramos para que solo procese la hora 14 (las 2 de la tarde)
            if (horaCompleta === "14") {
                const horaFormateada = hourly.time[i].substring(11); // "14:00"

                // 1. Crear el TÍTULO H4 para la hora de la carrera
                const h4Hora = document.createElement('h4');
                h4Hora.textContent = `Condiciones en el horario de carrera: ${horaFormateada} h`; 
                section.appendChild(h4Hora);

                // 2. Lista para las métricas de esta hora específica
                const ulMetricas = document.createElement('ul');
                
                const metricas = [
                    { label: 'Temperatura a 2 metros del suelo', value: hourly.temperature_2m[i], unit: units.temperature_2m },
                    { label: 'Sensación Térmica', value: hourly.apparent_temperature[i], unit: units.apparent_temperature },
                    { label: 'Lluvia', value: hourly.rain[i], unit: units.rain },
                    { label: 'Humedad Relativa', value: hourly.relative_humidity_2m[i], unit: units.relative_humidity_2m },
                    { label: 'Velocidad Viento', value: hourly.wind_speed_10m[i], unit: units.wind_speed_10m },
                    { label: 'Dirección Viento', value: hourly.wind_direction_10m[i], unit: '°' }
                ];

                metricas.forEach(metrica => {
                    const liMetrica = document.createElement('li');
                    liMetrica.textContent = `${metrica.label}: ${metrica.value} ${metrica.unit}`;
                    ulMetricas.appendChild(liMetrica);
                });

                section.appendChild(ulMetricas);
                
                // Como ya encontramos la hora de la carrera, podemos dejar de buscar
                break; 
            }
        }
        fragment.append(section);
        return fragment;
    }


}


"use strict";

class Memoria {

    // Atributos privados
    #tablero_bloqueado;
    #primera_carta;
    #segunda_carta;
    #cronometro; // Asumiendo que Cronometro también es una dependencia interna.

    constructor(){
        // Inicialización de atributos privados
        this.#tablero_bloqueado = true;
        this.#primera_carta = null;
        this.#segunda_carta = null;

        // Métodos privados llamados en el constructor
        this.#barajarCartas();

        this.#tablero_bloqueado = false;

        // Inicialización del cronómetro
        this.#cronometro = new Cronometro();
        this.#cronometro.arrancar();
    }

    // Método público
    voltearCarta(carta){
        if (this.#tablero_bloqueado || carta.dataset.estado === 'revelada' || carta.dataset.estado === 'volteada') {
            return;
        }
        carta.dataset.estado = 'volteada';
        
        if (!this.#primera_carta) {
            this.#primera_carta = carta;
            return; 
        } 
        
        this.#segunda_carta = carta;
        this.#comprobarPareja(); // Llamada a método privado
    }

    // Método privado (anteriormente barajarCartas)
    #barajarCartas(){

        const cartasNodeList = document.querySelectorAll('main article');

        // Se mantiene 'this.voltearCarta.bind(this, carta)' porque voltearCarta es público
        cartasNodeList.forEach(carta => {
            carta.addEventListener('click', this.voltearCarta.bind(this, carta));
        });
        
        const cartasArray = Array.from(cartasNodeList);
        const numCartas = cartasArray.length;

        for (let i = numCartas - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [cartasArray[i], cartasArray[j]] = [cartasArray[j], cartasArray[i]];
        }

        const contenedor = cartasNodeList[0].parentNode;
        cartasArray.forEach(carta => {
            contenedor.appendChild(carta);
        });

        // Se mantiene 'this.voltearCarta.bind(this, carta)' porque voltearCarta es público
        cartasNodeList.forEach(carta => {
            carta.addEventListener('click', this.voltearCarta.bind(this, carta));
        });
    }

    // Método privado (anteriormente reiniciarAtributos)
    #reiniciarAtributos(){
        this.#tablero_bloqueado = false;
        this.#primera_carta = null;
        this.#segunda_carta = null;
    }

    // Método privado (anteriormente deshabilitarCartas)
    #deshabilitarCartas(){
        this.#primera_carta.dataset.estado = 'revelada';
        this.#segunda_carta.dataset.estado = 'revelada';

        this.#comprobarJuego(); // Llamada a método privado

        this.#reiniciarAtributos(); // Llamada a método privado
    }

    // Método privado (anteriormente comprobarJuego)
    #comprobarJuego(){
        const cartas = document.querySelectorAll("main article");
        let juegoAcabado = true; 

        cartas.forEach(carta => {
            if (carta.dataset.estado !== "revelada") {
                juegoAcabado = false;
            }
        });

        if (juegoAcabado) {
            this.#cronometro.parar();
        }
    }

    // Método privado (anteriormente cubrirCartas)
    #cubrirCartas(){
        this.#tablero_bloqueado = true;
        
        setTimeout(() => {
            this.#primera_carta.removeAttribute('data-estado');
            this.#segunda_carta.removeAttribute('data-estado');
            this.#reiniciarAtributos(); // Llamada a método privado
            
        }, 1500); // Retardo de 1.5 segundos (1500ms)
    }

    // Método privado (anteriormente comprobarPareja)
    #comprobarPareja() {
        const altPrimera = this.#primera_carta.querySelector('img').getAttribute('alt');
        const altSegunda = this.#segunda_carta.querySelector('img').getAttribute('alt');
        
        const sonIguales = altPrimera === altSegunda;

        // Llamadas a métodos privados
        sonIguales ? this.#deshabilitarCartas() : this.#cubrirCartas();
    }
}
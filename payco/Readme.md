# Davipay Pagos Divididos para PrestaShop v1.6.1.6 o superior
 
## Descripción

Este plugin permite gestionar pagos divididos en PrestaShop, facilitando a los comerciantes la distribución de pagos entre múltiples receptores. Es esencial que los usuarios creen receptores en su cuenta de Davipay y configuren correctamente las llaves de acceso.

## Tabla de Contenido
- [Versiones](#versiones)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Pasos](#pasos)


## Versiones
* [Davipay plugin Pagos_Divididos_PrestaShop v2.0.0.0](https://github.com/epayco/plugin_prestashop_pagos_divididos/releases/tag/v2.0.0.0).
* [Davipay plugin Pagos_Divididos_PrestaShop v1.8.0.1](https://github.com/epayco/plugin_prestashop_pagos_divididos/releases/tag/v1.8.0.1).

## Requisitos

- Tener una cuenta activa en [Davipay](https://davipay.com).
- Tener instalado PrestaShop v1.6.1.6 o superior.
- Acceso a las carpetas donde se encuentra instalado PrestaShop.
- Acceso al panel de administración de PrestaShop.

## Instalación

1. [Descarga el plugin.](https://github.com/epayco/plugin_prestashop_pagos_divididos#versiones).
2. Descomprimir el archivo descargado y luego comprimes la carpeta llamada payco.
3. Ingresa al administrador de PrestaShop.
4. Navega a **Módulos y Servicios**.
5. Carga el plugin en PrestaShop.
6. Busca el plugin Davipay en la lista de módulos.
7. Debes crear cada receptor o comercio registrado en tu cuenta de Davipay, con los cuales se dividirá el pago. 
8. Ingresa al panel de clientes de Davipay [panel de clientes de Davipay](https://dashboard.davipay.com/configuration) para obtener el `P_CUST_ID_CLIENTE`, `P_KEY`,  `PRIVATE_KEY` y  `PUBLIC_KEY`. 
9. Configura el plugin de Davipay ingresando los datos del formulario.


## Pasos

1. **Crear receptores**: Asegúrate de crear los receptores necesarios en tu cuenta de Davipay antes de proceder.
2. **Configurar llaves**: Asegúrate de ingresar correctamente las llaves de Davipay en la configuración del plugin.
3. **Configurar Productos**: Ingresa al producto -> datos -> Añadir característica y completa los datos
- Caracteristica "Davipay receiver"
- Selecciona el recibidor asociado al producto como valor predefinido
4. **Realiza una prueba**: Una vez configurado, realiza una prueba de compra para verificar que los pagos se distribuyan correctamente.

Si tienes alguna pregunta o problema, no dudes en ponerte en contacto con nuestro soporte técnico: [desarrollo@epayco.com](mailto:desarrollo@epayco.com).

## Contacto

Para soporte técnico o consultas, puedes escribir a: desarrollo@epayco.com ó desarrolladores@epayco.com

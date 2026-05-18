---
name: spec-definition
description: Use when the user wants to define a spec from a user story, fill missing functional details, review assumptions, and refine rejected assumptions one by one before creating the final specification.
---

# Spec Definition

Usa esta skill cuando el usuario quiera convertir una historia de usuario en una especificacion funcional clara antes de implementar.

El objetivo es ayudar a definir una spec completa, identificando espacios en blanco, haciendo asunciones funcionales/no tecnicas y refinando con el usuario cualquier asuncion que no acepte.

## Entradas Esperadas

El usuario entregara una historia de usuario, normalmente con una estructura como:

```text
Como <tipo de usuario>, quiero <accion o capacidad>, para <beneficio o resultado>.
```

Tambien puede entregar una descripcion informal. En ese caso, interpreta la intencion y extrae la historia de usuario base.

## Flujo Obligatorio

### Paso 1: Analizar La Historia

Cuando el usuario entregue una historia de usuario:

- Identifica el actor.
- Identifica la accion principal.
- Identifica el resultado esperado.
- Detecta espacios en blanco funcionales.
- Completa los espacios en blanco con asunciones razonables.
- No hagas preguntas todavia en este paso.

### Paso 2: Mostrar Asunciones

Muestra al usuario un listado numerado de todas las asunciones funcionales o no tecnicas que hiciste.

Reglas:

- Solo listar asunciones que afecten comportamiento, reglas de negocio, UX, permisos, estados, criterios de aceptacion o integracion.
- No listar detalles puramente tecnicos como nombres de clases, tablas, frameworks o patrones internos, salvo que afecten al usuario final.
- Cada asuncion debe ser concreta y facil de aceptar o rechazar.
- Pide al usuario que indique los numeros de las asunciones que no le gustaron.

Formato recomendado:

```markdown
**Asunciones Realizadas**

1. <asuncion funcional>
2. <asuncion funcional>
3. <asuncion funcional>

Indica los numeros de las asunciones que no te gustaron para redefinirlas una por una.
```

### Paso 3: Recibir Asunciones Rechazadas

Cuando el usuario responda con los numeros de las asunciones que no le gustaron:

- Guarda internamente la lista en el orden indicado.
- Pregunta una asuncion a la vez.
- No preguntes por varias asunciones en el mismo mensaje.

### Paso 4: Preguntar Una Por Una

Por cada asuncion rechazada, muestra:

- Una barra de progreso.
- La cantidad de preguntas respondidas y faltantes.
- La asuncion original que se va a redefinir.
- Una pregunta clara para que el usuario elija la nueva definicion.
- Cuatro nuevas asunciones alternativas.
- Una quinta opcion llamada `Otra`.

La opcion `Otra` debe permitir que el usuario escriba su propia definicion.

Formato obligatorio:

```markdown
**Progreso:** [##---] 2/5

**Asuncion a redefinir:**
<texto de la asuncion original>

**Pregunta:**
<pregunta clara sobre la definicion deseada>

**Opciones:**

1. <nueva asuncion alternativa>
2. <nueva asuncion alternativa>
3. <nueva asuncion alternativa>
4. <nueva asuncion alternativa>
5. Otra
```

Reglas para la barra de progreso:

- Usa 5 segmentos visuales cuando sea posible.
- Llena segmentos proporcionalmente al avance.
- El contador debe mostrar `respondidas/total`.
- Antes de la primera respuesta, el progreso debe marcar `0/total`.
- Despues de cada respuesta, avanza a la siguiente pregunta con el contador actualizado.

Ejemplos de barra:

```text
[-----] 0/5
[#----] 1/5
[##---] 2/5
[###--] 3/5
[####-] 4/5
[#####] 5/5
```

### Paso 5: Registrar La Nueva Definicion

Cuando el usuario elija una opcion:

- Si responde `1`, `2`, `3` o `4`, reemplaza la asuncion original por esa opcion.
- Si responde `5`, `otra` u otra redaccion libre, usa la respuesta especificada por el usuario como nueva definicion.
- Confirma brevemente la nueva definicion.
- Continua con la siguiente asuncion rechazada, si existe.

No generes la spec final hasta terminar todas las redefiniciones.

### Paso 6: Finalizar Refinamiento

Cuando todas las asunciones rechazadas hayan sido redefinidas, responde:

```markdown
Ya me encuentro listo para crear la especificacion.
```

No crees la especificacion final automaticamente a menos que el usuario lo pida explicitamente.

## Estructura Recomendada De La Spec Final

Cuando el usuario pida crear la especificacion, usa esta estructura:

```markdown
# Spec: <nombre de la funcionalidad>

## Historia De Usuario

Como <actor>, quiero <accion>, para <beneficio>.

## Objetivo

<objetivo funcional de la historia>

## Alcance

- <incluido>
- <incluido>

## Fuera De Alcance

- <excluido>
- <excluido>

## Reglas De Negocio

- <regla>
- <regla>

## Flujo Principal

1. <paso>
2. <paso>
3. <paso>

## Flujos Alternativos

- <flujo alternativo>

## Validaciones

- <validacion>

## Permisos Y Roles

- <permiso>

## Estados

- <estado>

## Criterios De Aceptacion

- Dado <contexto>, cuando <accion>, entonces <resultado>.

## Casos De Error

- <error y resultado esperado>

## Contrato De Datos Esperado

- <entrada/salida relevante si aplica>

## Dependencias

- <dependencia funcional o integracion>
```

## Reglas De Comportamiento

- Mantente en espanol si el usuario escribe en espanol.
- No inventes implementacion tecnica durante el refinamiento inicial.
- Diferencia claramente entre lo definido por el usuario y lo asumido.
- No ocultes asunciones funcionales importantes.
- No hagas preguntas antes de mostrar la primera lista de asunciones.
- Durante la redefinicion, haz una sola pregunta por mensaje.
- Siempre ofrece exactamente 5 opciones durante la redefinicion: 4 propuestas y `Otra`.
- Al finalizar el refinamiento, solo indica que estas listo para crear la especificacion.

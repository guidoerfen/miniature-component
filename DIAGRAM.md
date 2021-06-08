<img align="right" width="100" height="100" src="https://github.com/guidoerfen/miniature-component/blob/master/img/miniature-logo-100px.png">

# An extended Component Diagramm

This is a loose-syntax diagram of what the
[Miniature\Component wiring](https://github.com/guidoerfen/miniature-component#wiring-the-coupling)
is trying to achieve.
One goal is to prevent criss-cross access all over the system (resulting in dense coupling)
and control on the access directions.

The interface notation might appear a bit redundant.
They just were adopted from the average
[component diagram](https://en.wikipedia.org/wiki/Component_diagram)
notation and since these components are globally accessible singletons
they do not mean that components must implement a certain interface.
*(Rather the return type hint should be an interface.
Of course if you want to keep Componts interchangeable the component classes should implement an interface.)*

[The wiring](#wiring-yaml)<!-- @IGNORE PREVIOUS: anchor --> is the way more heavyweight contract here:
Each wiring is a permission of a certain method to access a certain container method.
The method-call arrows illustrate which accesses are granted.
As far as the accesses between components are concerend the grant/method-call arrows
illustrate how the interfaces between them are released.

* `ComponentA->provideA1()` allows only to be called by `ComponentB->consumeA1()`
* While `ComponentB->consumeA1()` allows only to be called by two classes from inside it's own DI-Container. No methods are specified in this example, but self-speaking it could be done.
* But class `B1`is not allowed to call `ComponentB->consumeA1()`
* `ComponentD` isn't granted to access `ComponentA` and `ComponentB` at all.
* The two connections between `ComponentA` and `ComponentB` illustrate how *one* interface can be realized by connectiong multiple methods.

All those public `provide` and `consume` methods in the component instances are all getter methods actually.
You might consider them getter-guards sitting on an castle wall
on demand lowering down service staff on ropes in both directions, to the outside and the inside of the castle.
(Slightly confusingly this is the opposite direction of the granted-method-call arrows.)

Don't misunderstand the method-call and instantiation arrows as data stream directions.
Directions could be either ways in the end result.
Provider methods should not directly trigger functionality but deliver "service facades"
which in return provide access to well-chosen functionality.

![A Component Diagram](img/component-diagram.png)

<a name="wiring-yaml"></a>
The relations as shown above would result in a wiring like this:

```YML
coupling:
    ComponentA:
        provideA1:
            ComponentB:
                consumeA1: true
        provideA2:
            ComponentB:
                consumeA2: true
        provideA3:
            Componentc:
                consumeA3: true
    ComponentB:
        consumeA1:
            B1: true
            B2: true
        consumeA2:
            B2: true
        provideB3:
            ComponentD: 
                consumeB1: true
    ComponentC:
        consumeA3:
            C2: true
    ComponentD:
        consumeB1:
            D1: true
            D2: true
            
```

# Services & Components

A **service** is a descriptor for some advertised functionality (e.g. an `HttpDispatcher` service implies ability to dispatch HTTP requests). A **component** is the actual class fulfilling a service.

A service exposes an _interface_ that it conforms to, and can have _metadata_ that gives more descriptions or drives component configuration. A consumer can search for services either by its interface or metadata (or both).

> ```
> service:
>   interface: org.something.HttpDispatcher
> component:
>   class: org.something.impl.BasicHttpDispatcher
> ```
> Service-component relationship in config file

Multiple services can provide the same interface. In these cases, an optional _rank_ can be supplied that controls priority.

Services can also _reference_ other services. There are 4 ways to do define a reference relationship (_cardinality_):

- 0 or 1 (`ONE_OPTIONAL`)
- 1:1 (`ONE`)
- 0 or more (`MANY_OPTIONAL`)
- 1 or more (`MANY`)

## Service Configuration

### Service

Services have the following properties:

- `interface` (string): represents the interface being exposed. Note that this doesn't have to be an actual interface in the code. It can be any string that defines some sort of behavior
- `rank` (integer): defines priority when there are multiple services registered with the same interface.

### Component

Components have the following properties:

- `class` (string): the concrete class to instantiate
- `activate` (string): if defined, the method to invoke to complete service startup
- `deactivate` (string): if defined, the method to invoke when service is shutting down
- `abstract` (boolean): allows abstract classes to define baseline configuration that implementations can override further
- `immediate` (boolean): if false, service is activated on first access

Reflection is used to invoke activation and deactivation, so it's recommended that they be at least protected.

### Reference

Reference bindings have the following properties:

- `interface` (string): the service interface
- `query` (string): a more complex expression defining other service properties to match beyond interface. can be combined with `interface`
- `cardinality`: either `ONE`, `ONE_OPTIONAL`, `MANY`, `MANY_OPTIONAL`
- `bind`: the method to use when binding the service before activation
- `unbind`: the method to use when unbinding the service before deactivation
- `target`: if `bind` and `unbind` are undefined, the property on the instance to set the service on

Reflection is used to invoke binding and unbinding, so it's recommended that they be at least protected.

### Properties

Properties are simple key-value pairs. They can drive configuration or expose additional information that other services can query.

### Metadata

Metadata describes the properties themselves. These can be used for things like auto-generating UI, validating configuration values, etc.

## Component

Components are plain PHP objects by default. The following is expected:

1. components must have a null constructor
2. dependency injection is done through reflection
3. activation and deactivation methods must be explicitly defined if required and accept the following parameters: `DinoTech\Phelix\Api\ServiceProperties`

### Factory

tood

## Lifecycle

### Statuses

A service has the following lifecycles:

1. `DISABLED`
2. `STARTING`
3. `UNSATISFIED`
4. `SATISFIED`
5. `ERROR`
6. `ACTIVE`
7. `STOPPING`

> ```
> disabled -> starting -> unsatisfied -> satisfied -> active -> stopping -> disabled*
>                                                  -> error
> ```

`DISABLED` means it's not started or not available.

`STARTING` means it's in the startup phase, where configurations are read and references are resolved.

`UNSATISFIED` means there were 1 or more references that were required and not available.

`SATISFIED` means that every required reference was found and that the references were either `SATISFIED` or `ACTIVE`

`ERROR` means service failed to start during component activation.

`ACTIVE` means service successfully started and available.

`STOPPING` means service is shutting down and unbinding references.

### Activation Requirements

The following steps are applied when activating a service:

1. if there are no references
    1. invoke activation
        1. if an exception is thrown, set service to `ERROR`
        2. otherwise, set service to `ACTIVE`
            1. publish service availability
2. otherwise
    1. for each reference
        1. if reference is not minimally satisfied (`< SATISFIED`), mark service as `UNSATISFIED` and subscribe to reference availability
    2. if all references are satisfied, invoke activation (see above)

#### Activation Event

Once a service is activated, any dependents should have their reference status raised to `SATISFIED`. If all references are satisfied, service should be minimally at a `SATISFIED` state. The service manager can decide the most optimal time to attempt activation.

### Deactivation Requirements

The following steps are applied when deactivating a service:

1. unbind each reference
2. invoke deactivation
    1. if an exception is thrown, set service to `ERROR` (?)
    2. otherwise, set service to `DISABLED`
    3. publish service unavailibility

#### Deactivation Event

Once a service is deactivated, a dependent service immediately will go through a restart cycle:

1. deactivate service
2. unbind dependency
3. if dependency can be satisfied with another service, bind to new service
4. if all dependencies are satisfied, invoke activation
5. otherwise, set dependent to `UNSATISFIED`

---

# Internals

## Service Registration

The service registry of each bundle will be in `phelix/service-registry.yml` and structured as follows:

```yml
services:
    -
        service:
            interface: Package\Interface
            rank: 0 # bigger numbers for higher priority
        component:
            class: Package\ImplementingClass
            activate: methodName
            deactivate: methodName
        references:
            -
                interface: TargetServiceInterface
                query: properties.key = value || properties.handler-type = 'some-value'
                cardinality: MANY
        properties:
            key: value
        metadata:
            ...
```

`service` defines the interface being exposed. `component` defines the backing class along with activation/deactivation

In Phelix, a `ServiceTracker` is used to monitor and retrieve services and components. It consists of:

- `ServiceConfig`
- `BundleManifest`
- `Scoreboard` (tracks the number of reference cardinalities that haven't been satisfied)
- component instance

An `Index` is used to hold all services in the framework and provide query access to them. 

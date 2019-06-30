# Phelix

Phelix is a loose adaptation of the service-oriented Java framework Apache Felix (which is OSGi-compliant) that allows for code to be rapidly integrated into a system through dynamic modules.

The framework loads **bundles** which encapsulate a specific set of functionalities. The bundles publish their presence through a set of **services** which are backed by a **component** that fulfills service obligations.

### Example

```
[ bundle:HttpDispatcherCore]
  └ [ service:MainDispatcher ] < - - - - - - ┐
      └ [ component:DispatherImpl ]          ^
    [ service:Controller ]                   |
      └ [ component:ManagementController ] - ┘
                                             ^
[ bundle:YourBundle ]                        |
  └ [ service:Controller ] - - - - - - - - - ┘
      └ [ component:YourController]
```

In the above diagram, we have a `HttpDispatcherCore` bundle that presumably handles HTTP requests. It advertises a `MainDispatcher` service and a `Controller` service that binds to `MainDispatcher`. Then we have `YourBundle`, which advertises a `Controller` service that binds to `MainDispatcher. The dispatcher would now have two controllers to chosoe from.

## Sounds like Symfony...

Symfony does share similar terminology and organization of code, and on the surface operates in a similar way. However, the internals and semantics of Phelix are vastly different:

1. It takes full control of discovering and wiring services (even 1:many/many:1). All declarations and bindings are made within your components.
    1. If you don't care about per-environment configs, there's no extra configurations needed.
2. It doesn't impose any internal structure to your code. As long as bundles have a conformant top-level organization you are free to put things wherever/however.
3. It removes a whole layer of service configuration management, only leaving you to define environment-specific overrides.

## Get Started

See a TODO EXAMPLE BUNDLE in action. Also check out the [bundle](./src/Api/Bundle/readme.md) and [service](./src/Api/Service/readme.md) specs.

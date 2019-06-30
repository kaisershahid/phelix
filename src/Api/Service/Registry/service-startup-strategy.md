Goal: correctly and automatically resolve all service references and startup all satisfied services.

**`initialLoad()`**

1. load all bundle manifests
2. for each manifest
    1. get `serviceConfigs`
    2. for each `serviceConfig` in `serviceConfigs`
        1. `registerService(serviceConfig)`

**`registerService(serviceConfig)`**

1. create `component`
2. merge `serviceConfig.metadata` with runtime configuration
3. create `serviceTracker(component, serviceConfig)`
4. if `serviceConfig.references` is empty
    1. attempt `component.activate()`
    2. if success, mark `serviceTracker.status = active`
    3. otherwise, mark `serviceTracker.status = error`
5. otherwise
    1. put tracker in wait queue
    2. add references to `serviceIndex` for caching
        1. each time new ref is added, create a reference tracker and apply query to all existing services

At this point, we've got X active/error components + Y queued components with references

**`postInitialLoad()`**

1. while reference scoreboard keeps decreasing
    1. for each tracker in wait queue
        1.  for each unsatisfied reference
            1. if reference found
                1. decrease `tracker.referenceScoreboard` by cardinality
                2. decrease `referenceScoreboard` by cardinality
        2. if all references satisfied, mark tracker as `satisfied`

**`finalLoad`**

1. while activation count increases
    1. for each satisfied tracker
        1. attempt `component.activate()`
        2. if success, mark `serviceTracker.status = active`
        3. otherwise, mark `serviceTracker.status = error`

## Optimizations

1. create a dependency tree where the dependent is a node, and each dependency is a leaf
2. when dependent is active, it updates dependency's ref tracking and decreases by 1

in this way, we go from passively checking that dependencies are being resolved to actively marking resolutions. we can also reduce iterations.


```
:predicate :op (:predicate)
:predicate => :field :op :value
:field => '[\w]+'
:op => '(!=|!|=|\|\||&&)'
:value => :number OR :boolean OR :string
:number => '-?(\d+|\d+\.\d+)'
:boolean => '(true|false)'
:string => '"' + '([^"]*)' + '"'
```

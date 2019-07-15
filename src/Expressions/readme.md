# Expressions

The expression library contains both high-level interfaces for general purpose expression parsing as well as concrete classes related either to pure utility or Phelix-specific uses.

## Tokens

The `TokenSetInterface` is a collection of functionally related tokens, such as logical operators, mathematical operators, and string operators, among others. A `TokenMapper` holds a set of `TokenSetInterface` objects that represent all the tokens necessary to represent a grammar.

> For the remainder of the document, a token will refer to a set of characters encapsulated within a `TokenSetInterface` instance.

## Lexer and Parser

A `ParserInterface` instance represents a very high-level parser that accepts either a concrete token from `TokenMapper` or a stream of non-token characters.

The `ExpressionLexer` takes a `TokenMapper` and input string, from which is sends events to a `ParserInterface`.

## Statements and Predicates

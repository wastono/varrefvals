# Varrefvals

I know, it's too LOL wanting PHP coding without $ sign on its variable name. But, writing code with $ sign on every variable is adding typing complexity, for me. I wish I could do coding using simple syntax only. So I decided to design and write simple program that can be used for compiling no $ sign code into normal PHP code.

**Varrefvals**, that's the name that I use for this program. It comes from 3 selected keywords: `var`, `ref`, and `vals`. `var` keyword is used as direct variable declaration. `ref` keyword is used for setting variable accessed by reference. `vals` keyword is used for setting variables accessed by value.

## Coding Specification

1. Use `var` keyword for direct variable declaration.
2. Detect variable declaration from keywords: `global`, `static`, `public`, `private`, `protected`, `for`, `foreach`, `list`, `catch`, `function`
3. Use `ref` keyword to set variable accessed by reference.
4. Use `refs` keyword to set variables accessed by reference.
5. Use `vals` keyword to set variables accessed by value.
6. Use `this` keyword to access own object properties or methods.
7. Set alias variables to write superglobal variables.
8. Use dot operator to access every method or property, static or not.
9. Use colon for associative arrays.

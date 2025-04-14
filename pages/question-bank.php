<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Expanded Question Bank</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; line-height: 1.6; background-color: #f9f9f9; color: #333; }
        h1 { color: #0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; }
        .subject-section { margin-bottom: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); overflow: hidden; }
        .subject-title { cursor: pointer; background-color: #e9ecef; padding: 12px 15px; border-bottom: 1px solid #dee2e6; font-weight: bold; font-size: 1.1em; transition: background-color 0.2s ease; }
        .subject-title:hover { background-color: #d1d9e0; }
        .questions { display: none; padding: 15px; border-top: none; max-height: 500px; overflow-y: auto; /* Add scroll for long lists */ }
        .question { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dashed #eee; }
        .question:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .question strong { display: block; margin-bottom: 8px; color: #0056b3; }
        .question input[type='radio'] { margin-right: 5px; vertical-align: middle; }
        .question label { display: block; margin-bottom: 5px; cursor: pointer; padding-left: 5px; }
        .answer { display: none; color: #28a745; margin-top: 10px; padding: 8px; background-color: #e9f7ec; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; }
        button { margin-top: 10px; padding: 8px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.2s ease; font-size: 0.9em; }
        button:hover { background-color: #0056b3; }
    </style>
    <script>
        function toggleQuestions(id) {
            var x = document.getElementById(id);
            // Optional: Close others when one opens
            // var allSections = document.querySelectorAll('.questions');
            // allSections.forEach(section => {
            //     if (section.id !== id) {
            //         section.style.display = 'none';
            //     }
            // });

            if (x.style.display === "none" || x.style.display === "") {
                x.style.display = "block";
            } else {
                x.style.display = "none";
            }
        }

        function showAnswer(id) {
            var x = document.getElementById(id);
            // Toggle answer visibility
            if (x.style.display === "none" || x.style.display === "") {
                x.style.display = "block";
            } else {
                x.style.display = "none";
            }
        }
    </script>
</head>
<body>

    <h1>Question Bank</h1>

    <!-- Subject Sections -->
    <?php
    // WARNING: THIS ARRAY IS VERY LARGE. MANAGEABILITY IS LOW.
    // Consider using a database or external files (JSON/CSV) for real projects.
    $subjects = [
        "C++" => [
            // Previous questions...
            [ "question" => "What is the correct syntax to output \"Hello World\" in C++?", "options" => ["print(\"Hello World\");", "echo \"Hello World\";", "cout << \"Hello World\";", "System.out.println(\"Hello World\");"], "answer" => "cout << \"Hello World\";" ],
            [ "question" => "Which keyword is used to define a constant in C++?", "options" => ["const", "final", "static", "define"], "answer" => "const" ],
            [ "question" => "What does STL stand for in C++?", "options" => ["Standard Template Library", "Static Type Language", "Standard Type Library", "Simple Template Language"], "answer" => "Standard Template Library" ],
            [ "question" => "How do you allocate memory dynamically in C++?", "options" => ["malloc()", "alloc()", "new", "create()"], "answer" => "new" ],
            [ "question" => "Which operator is used to access members of a structure or class using a pointer?", "options" => [". (dot)", ":: (scope resolution)", "* (dereference)", "-> (arrow)"], "answer" => "-> (arrow)" ],
            // New questions...
            [ "question" => "What is the purpose of the `delete` operator in C++?", "options" => ["To remove a file", "To deallocate memory allocated with `new`", "To delete a variable", "To clear the console screen"], "answer" => "To deallocate memory allocated with `new`" ],
            [ "question" => "Which header file is needed for input/output operations like `cout` and `cin`?", "options" => ["<string>", "<vector>", "<iostream>", "<cmath>"], "answer" => "<iostream>" ],
            [ "question" => "What is function overloading?", "options" => ["Defining multiple functions with the same name but different parameters", "Defining a function inside another function", "Overriding a base class function", "Calling a function recursively"], "answer" => "Defining multiple functions with the same name but different parameters" ],
            [ "question" => "What does `&` mean when used before a variable name in a function parameter list (e.g., `void func(int &x)`)?", "options" => ["Pass by pointer", "Pass by value", "Pass by reference", "Address of operator"], "answer" => "Pass by reference" ],
            [ "question" => "Which access specifier makes members accessible only within the class and its derived classes?", "options" => ["public", "private", "protected", "internal"], "answer" => "protected" ],
            [ "question" => "What is a constructor in C++?", "options" => ["A function to destroy objects", "A special member function automatically called when an object is created", "A function to copy objects", "A standard library function"], "answer" => "A special member function automatically called when an object is created" ],
            [ "question" => "What is the `virtual` keyword used for in C++?", "options" => ["To declare a variable", "To enable dynamic polymorphism (runtime polymorphism)", "To define a static member", "To create a template"], "answer" => "To enable dynamic polymorphism (runtime polymorphism)" ],
            [ "question" => "How do you write a single-line comment in C++?", "options" => ["/* comment */", "# comment", "// comment", "-- comment"], "answer" => "// comment" ],
            [ "question" => "What is the size of `char` data type in C++ (typically)?", "options" => ["Depends on the system", "4 bytes", "2 bytes", "1 byte"], "answer" => "1 byte" ],
             [ "question" => "Which STL container provides dynamic array functionality?", "options" => ["std::map", "std::set", "std::vector", "std::list"], "answer" => "std::vector" ],
             [ "question" => "What is the purpose of the `nullptr` keyword introduced in C++11?", "options" => ["Represents a null pointer value with better type safety than NULL", "Declares a variable", "Defines a constant", "Allocates memory"], "answer" => "Represents a null pointer value with better type safety than NULL" ],
             [ "question" => "How is inheritance implemented in C++?", "options" => ["Using the `implements` keyword", "Using the `:` symbol followed by access specifier and base class name", "Using the `extends` keyword", "Using the `inherits` keyword"], "answer" => "Using the `:` symbol followed by access specifier and base class name" ],
             [ "question" => "What is the scope resolution operator (`::`) used for?", "options" => ["Pointer access", "Conditional operations", "Accessing static members or members of a namespace/class", "Bitwise operations"], "answer" => "Accessing static members or members of a namespace/class" ],

        ],
        "Python" => [
            // Previous questions...
            [ "question" => "Which of the following is the correct extension of the Python file?", "options" => [".python", ".pl", ".py", ".p"], "answer" => ".py" ],
            [ "question" => "What keyword is used to define a function in Python?", "options" => ["fun", "define", "def", "function"], "answer" => "def" ],
            [ "question" => "Which data type is used to store an ordered, immutable sequence of items?", "options" => ["list", "dictionary", "set", "tuple"], "answer" => "tuple" ],
            [ "question" => "How do you start a single-line comment in Python?", "options" => ["//", "/*", "#", "--"], "answer" => "#" ],
            [ "question" => "What is the output of `print(type([]))`?", "options" => ["<class 'list'>", "<class 'array'>", "<class 'tuple'>", "<class 'dict'>"], "answer" => "<class 'list'>" ],
            [ "question" => "Which method removes the last element from a list and returns it?", "options" => ["remove()", "delete()", "pop()", "discard()"], "answer" => "pop()" ],
            // New questions...
            [ "question" => "What is the purpose of the `if __name__ == '__main__':` block?", "options" => ["It defines the main function", "It checks if the script is being run directly or imported", "It imports necessary modules", "It handles exceptions"], "answer" => "It checks if the script is being run directly or imported" ],
            [ "question" => "Which built-in function returns the number of items in a container like a list or tuple?", "options" => ["count()", "size()", "length()", "len()"], "answer" => "len()" ],
            [ "question" => "How do you create a dictionary in Python?", "options" => ["dict = [key:value]", "dict = (key:value)", "dict = {key:value}", "dict = <key:value>"], "answer" => "dict = {key:value}" ],
            [ "question" => "What does the `pass` statement do in Python?", "options" => ["Exits the loop", "Skips the current iteration", "Does nothing; acts as a placeholder", "Raises an error"], "answer" => "Does nothing; acts as a placeholder" ],
            [ "question" => "Which loop is used for iterating over a sequence (like list, tuple, string)?", "options" => ["while", "for", "do-while", "repeat"], "answer" => "for" ],
            [ "question" => "How do you handle potential errors in Python?", "options" => ["Using if-else statements", "Using try-except blocks", "Using asserts", "Using error codes"], "answer" => "Using try-except blocks" ],
            [ "question" => "What is the result of `3 * 'abc'`?", "options" => ["Error", "'abcabcabc'", "9", "'abc3'"], "answer" => "'abcabcabc'" ],
            [ "question" => "Which keyword is used to bring a module's contents into your current script?", "options" => ["include", "require", "use", "import"], "answer" => "import" ],
            [ "question" => "Are Python lists mutable or immutable?", "options" => ["Mutable", "Immutable", "Depends on the contents", "Both"], "answer" => "Mutable" ],
            [ "question" => "How do you get user input from the console in Python 3?", "options" => ["cin()", "read()", "input()", "getline()"], "answer" => "input()" ],
            [ "question" => "What is PEP 8?", "options" => ["A Python Enhancement Proposal defining core language features", "A Python Enhancement Proposal that provides style guidelines for Python code", "A specific version of Python", "Python's error protocol"], "answer" => "A Python Enhancement Proposal that provides style guidelines for Python code" ],
            [ "question" => "Which method is used to add an element to the end of a list?", "options" => ["add()", "insert()", "push()", "append()"], "answer" => "append()" ],

        ],
        "Java" => [
            // Previous questions...
            [ "question" => "Which of the following is not a Java feature?", "options" => ["Dynamic", "Architecture Neutral", "Use of pointers", "Object-oriented"], "answer" => "Use of pointers" ],
            [ "question" => "What is the entry point method for a Java application?", "options" => ["main()", "start()", "public static void main(String[] args)", "run()"], "answer" => "public static void main(String[] args)" ],
            [ "question" => "Which keyword is used to prevent a class from being subclassed?", "options" => ["static", "private", "abstract", "final"], "answer" => "final" ],
            [ "question" => "What does JVM stand for?", "options" => ["Java Virtual Machine", "Java Verified Machine", "Java Visual Machine", "Java Variable Method"], "answer" => "Java Virtual Machine" ],
            [ "question" => "Which collection class stores elements in key-value pairs?", "options" => ["ArrayList", "HashSet", "HashMap", "LinkedList"], "answer" => "HashMap" ],
             // New questions...
            [ "question" => "What is the difference between `==` and `.equals()` for comparing objects in Java?", "options" => ["They are identical", "`==` compares memory addresses, `.equals()` compares content (if overridden)", "`.equals()` compares memory addresses, `==` compares content", "`==` is for primitives, `.equals()` is for objects only"], "answer" => "`==` compares memory addresses, `.equals()` compares content (if overridden)" ],
            [ "question" => "Which keyword is used to inherit a class in Java?", "options" => ["inherits", "super", "extends", "implements"], "answer" => "extends" ],
            [ "question" => "What is an interface in Java?", "options" => ["A concrete class", "A blueprint of a class containing abstract methods and static constants", "A type of exception", "A data structure"], "answer" => "A blueprint of a class containing abstract methods and static constants" ],
            [ "question" => "Which keyword is used for a class to use an interface?", "options" => ["extends", "uses", "implements", "inherits"], "answer" => "implements" ],
            [ "question" => "What is garbage collection in Java?", "options" => ["Manually deleting unused objects", "A process to automatically reclaim memory occupied by objects that are no longer referenced", "A tool for cleaning up code", "A way to handle exceptions"], "answer" => "A process to automatically reclaim memory occupied by objects that are no longer referenced" ],
            [ "question" => "What is the `static` keyword used for in Java?", "options" => ["To make a variable constant", "To indicate a method or variable belongs to the class itself, not to any specific instance", "To allow method overriding", "To specify the return type"], "answer" => "To indicate a method or variable belongs to the class itself, not to any specific instance" ],
            [ "question" => "Which access modifier provides the widest accessibility?", "options" => ["private", "default (package-private)", "protected", "public"], "answer" => "public" ],
            [ "question" => "What is method overloading in Java?", "options" => ["Having multiple methods with the same name but different parameter lists in the same class", "Overriding a superclass method", "Hiding a superclass method", "Calling a method from itself"], "answer" => "Having multiple methods with the same name but different parameter lists in the same class" ],
            [ "question" => "What is method overriding in Java?", "options" => ["Having multiple methods with the same name and parameters", "Providing a specific implementation for a method in a subclass that is already defined in its superclass", "Defining multiple constructors", "Using the `super` keyword"], "answer" => "Providing a specific implementation for a method in a subclass that is already defined in its superclass" ],
            [ "question" => "What does JRE stand for?", "options" => ["Java Runtime Environment", "Java Realtime Execution", "Java Runtime Engine", "Java Resource Emulator"], "answer" => "Java Runtime Environment" ],
            [ "question" => "Which primitive data type is used for true/false values?", "options" => ["int", "char", "boolean", "float"], "answer" => "boolean" ],
            [ "question" => "How do you declare an array of integers named 'scores' in Java?", "options" => ["array int scores;", "int scores[];", "int[] scores;", "Both 'int scores[];' and 'int[] scores;' are correct"], "answer" => "Both 'int scores[];' and 'int[] scores;' are correct" ],

        ],
        "DBMS" => [
            // Previous questions...
            [ "question" => "Which of the following is not a type of database?", "options" => ["Hierarchical", "Network", "Distributed", "Decentralized"], "answer" => "Decentralized" ],
            [ "question" => "What does SQL stand for?", "options" => ["Structured Query Language", "Standard Query Language", "Sequential Query Language", "Simple Query Language"], "answer" => "Structured Query Language" ],
            [ "question" => "Which SQL clause is used to filter records?", "options" => ["FILTER", "SELECT", "GROUP BY", "WHERE"], "answer" => "WHERE" ],
            [ "question" => "What is the purpose of a Primary Key?", "options" => ["To uniquely identify a record in a table", "To link two tables together", "To sort records", "To index frequently queried columns"], "answer" => "To uniquely identify a record in a table" ],
            [ "question" => "Which normalization form deals with eliminating transitive dependencies?", "options" => ["1NF", "2NF", "3NF", "BCNF"], "answer" => "3NF" ],
            [ "question" => "Which SQL command is used to add new data into a database?", "options" => ["ADD", "INSERT INTO", "UPDATE", "CREATE"], "answer" => "INSERT INTO" ],
            // New questions...
            [ "question" => "Which SQL command modifies existing data in a table?", "options" => ["MODIFY", "CHANGE", "UPDATE", "ALTER"], "answer" => "UPDATE" ],
            [ "question" => "Which SQL command removes data from a table?", "options" => ["REMOVE", "ERASE", "DELETE", "DROP"], "answer" => "DELETE" ],
            [ "question" => "What is a Foreign Key?", "options" => ["The main key of a table", "A key used for sorting", "A key in one table that refers to the Primary Key in another table, establishing a link", "An index"], "answer" => "A key in one table that refers to the Primary Key in another table, establishing a link" ],
            [ "question" => "Which `JOIN` clause returns only the rows where the join condition is met in both tables?", "options" => ["LEFT JOIN", "RIGHT JOIN", "FULL OUTER JOIN", "INNER JOIN"], "answer" => "INNER JOIN" ],
            [ "question" => "Which SQL clause is used to sort the result set?", "options" => ["SORT BY", "GROUP BY", "ORDER BY", "ARRANGE BY"], "answer" => "ORDER BY" ],
            [ "question" => "What does ACID stand for in the context of database transactions?", "options" => ["Atomicity, Concurrency, Isolation, Durability", "Atomicity, Consistency, Isolation, Durability", "Association, Concurrency, Integrity, Durability", "Atomicity, Consistency, Integrity, Distribution"], "answer" => "Atomicity, Consistency, Isolation, Durability" ],
            [ "question" => "Which SQL aggregate function returns the number of rows?", "options" => ["SUM()", "AVG()", "COUNT()", "MAX()"], "answer" => "COUNT()" ],
            [ "question" => "What is the purpose of the `GROUP BY` clause?", "options" => ["To filter results", "To sort results", "To group rows that have the same values in specified columns into a summary row", "To join tables"], "answer" => "To group rows that have the same values in specified columns into a summary row" ],
            [ "question" => "Which normalization form requires that there are no partial dependencies?", "options" => ["1NF", "2NF", "3NF", "BCNF"], "answer" => "2NF" ],
            [ "question" => "What SQL command is used to create a new table?", "options" => ["NEW TABLE", "MAKE TABLE", "CREATE TABLE", "DEFINE TABLE"], "answer" => "CREATE TABLE" ],
            [ "question" => "What is an index in a database?", "options" => ["A primary key", "A special lookup table that the database search engine can use to speed up data retrieval operations", "A foreign key", "A view"], "answer" => "A special lookup table that the database search engine can use to speed up data retrieval operations" ],
            [ "question" => "Which SQL command removes a table and all its data entirely?", "options" => ["DELETE TABLE", "REMOVE TABLE", "TRUNCATE TABLE", "DROP TABLE"], "answer" => "DROP TABLE" ],
        ],
        "PHP" => [
            // Previous questions...
            [ "question" => "What does PHP stand for?", "options" => ["Personal Hypertext Processor", "Private Home Page", "PHP: Hypertext Preprocessor", "Personal Home Page"], "answer" => "PHP: Hypertext Preprocessor" ],
            [ "question" => "Which symbol is used to denote a variable in PHP?", "options" => ["$", "&", "%", "@"], "answer" => "$" ],
            [ "question" => "How do you include the contents of another PHP file?", "options" => ["#include 'file.php';", "require 'file.php';", "import 'file.php';", "<include file='file.php'>"], "answer" => "require 'file.php';" ],
            // [ "question" => "Which superglobal variable holds information about HTTP headers, paths, and script locations?", "options" => ["$_GET", "$_POST", "$_REQUEST", "$_SERVER"], "answer" => "$_SERVER" ],
            [ "question" => "What function is used to start a session in PHP?", "options" => ["start_session()", "session_begin()", "session_start()", "init_session()"], "answer" => "session_start()" ],
            // New questions...
            // [ "question" => "How do you concatenate two strings, `$str1` and `$str2`, in PHP?", "options" => ["$str1 + $str2", "$str1 & $str2", "$str1 . $str2", "concat($str1, $str2)"], "answer" => "$str1 . $str2" ],
            [ "question" => "Which PHP function is used to print output to the browser?", "options" => ["print()", "output()", "display()", "echo"], "answer" => "echo" ], // echo is a language construct, print is a function-like construct, both work
            [ "question" => "What is the difference between `include` and `require` in PHP?", "options" => ["No difference", "`require` produces a fatal error if the file is not found, `include` produces a warning", "`include` produces a fatal error, `require` produces a warning", "`require` is faster"], "answer" => "`require` produces a fatal error if the file is not found, `include` produces a warning" ],
            // [ "question" => "How do you access data sent via the GET method in PHP?", "options" => ["$_POST['key']", "$_GET['key']", "$_REQUEST['key']", "$_SERVER['key']"], "answer" => "$_GET['key']" ],
            // [ "question" => "How do you create an array in PHP?", "options" => ["array = new Array()", "$myArray = []", "$myArray = array()", "Both $myArray = [] and $myArray = array()"], "answer" => "Both $myArray = [] and $myArray = array()" ],
            [ "question" => "Which PHP loop iterates over each key/value pair in an array?", "options" => ["for", "while", "do-while", "foreach"], "answer" => "foreach" ],
            [ "question" => "What does PDO stand for in PHP?", "options" => ["PHP Database Object", "PHP Data Objects", "Personal Data Object", "PHP Direct Object"], "answer" => "PHP Data Objects" ],
            [ "question" => "How do you check if a variable is set and is not NULL?", "options" => ["is_null()", "exists()", "defined()", "isset()"], "answer" => "isset()" ],
            [ "question" => "What function destroys all data registered to a session?", "options" => ["session_close()", "session_delete()", "session_unset()", "session_destroy()"], "answer" => "session_destroy()" ],
            [ "question" => "What is Composer used for in PHP development?", "options" => ["A code editor", "A web server", "A dependency manager for PHP", "A debugging tool"], "answer" => "A dependency manager for PHP" ],
            [ "question" => "Which PHP magic constant returns the full path and filename of the current script?", "options" => ["__LINE__", "__FUNCTION__", "__FILE__", "__DIR__"], "answer" => "__FILE__" ],
            //  [ "question" => "How do you define a constant in PHP?", "options" => ["const MY_CONST = 'value';", "define('MY_CONST', 'value');", "$MY_CONST = 'value';", "let MY_CONST = 'value';"], "answer" => "define('MY_CONST', 'value');" ], // `const` can also be used at top level or in classes since PHP 5.3/5.6
        ],
        "Frontend" => [
            // Previous questions...
            [ "question" => "Which HTML tag is used to define an internal style sheet?", "options" => ["<style>", "<css>", "<script>", "<link>"], "answer" => "<style>" ],
            [ "question" => "What does CSS stand for?", "options" => ["Cascading Style Sheets", "Creative Style Sheets", "Computer Style Sheets", "Colorful Style Sheets"], "answer" => "Cascading Style Sheets" ],
            [ "question" => "Which HTML element is used to specify a footer for a document or section?", "options" => ["<bottom>", "<section>", "<footer>", "<foot>"], "answer" => "<footer>" ],
            [ "question" => "How do you select an element with id 'myElement' in CSS?", "options" => [".myElement", "myElement", "#myElement", "*myElement"], "answer" => "#myElement" ],
            [ "question" => "Which JavaScript keyword declares a variable that cannot be reassigned?", "options" => ["var", "let", "const", "static"], "answer" => "const" ],
            [ "question" => "What HTML tag is used to link an external JavaScript file?", "options" => ["<script src='...'>", "<javascript>", "<link rel='script'>", "<js>"], "answer" => "<script src='...'>" ],
            // New questions...
            [ "question" => "Which HTML tag defines the most important heading?", "options" => ["<h6>", "<heading>", "<h1>", "<head>"], "answer" => "<h1>" ],
            [ "question" => "Which CSS property controls the text color?", "options" => ["font-color", "text-color", "color", "background-color"], "answer" => "color" ],
            [ "question" => "How do you select elements with class name 'myClass' in CSS?", "options" => ["#myClass", "myClass", ".myClass", "*myClass"], "answer" => ".myClass" ],
            [ "question" => "What is the correct HTML for creating a hyperlink?", "options" => ["<a url='...'>Link</a>", "<link href='...'>Link</link>", "<a href='...'>Link Text</a>", "<hyperlink>...</hyperlink>"], "answer" => "<a href='...'>Link Text</a>" ],
            [ "question" => "Which CSS property is used to change the background color of an element?", "options" => ["color", "bgcolor", "background-color", "background"], "answer" => "background-color" ],
            [ "question" => "In JavaScript, how do you write 'Hello World' in an alert box?", "options" => ["msgBox('Hello World');", "alertBox('Hello World');", "alert('Hello World');", "msg('Hello World');"], "answer" => "alert('Hello World');" ],
            [ "question" => "What does HTML stand for?", "options" => ["Hyper Text Markup Language", "Hyperlinks and Text Markup Language", "Home Tool Markup Language", "Hyper Tool Markup Language"], "answer" => "Hyper Text Markup Language" ],
            [ "question" => "Which HTML element defines navigation links?", "options" => ["<navigate>", "<nav>", "<navigation>", "<menu>"], "answer" => "<nav>" ],
            [ "question" => "What is the CSS Box Model composed of (from innermost to outermost)?", "options" => ["Margin, Border, Padding, Content", "Content, Padding, Border, Margin", "Content, Margin, Border, Padding", "Padding, Content, Border, Margin"], "answer" => "Content, Padding, Border, Margin" ],
            [ "question" => "How do you declare a JavaScript function named 'myFunction'?", "options" => ["function:myFunction()", "def myFunction():", "function myFunction()", "declare function myFunction()"], "answer" => "function myFunction()" ],
            [ "question" => "Which HTML tag is used to embed an image?", "options" => ["<image src='...'>", "<img src='...' alt='...'>", "<picture src='...'>", "<embed src='...'>"], "answer" => "<img src='...' alt='...'>" ],
            [ "question" => "What does the `===` operator do in JavaScript?", "options" => ["Assignment", "Checks for equal value", "Checks for equal value and equal type", "Checks for unequal value"], "answer" => "Checks for equal value and equal type" ],
        ],
        "DAA" => [
            // Previous questions...
            [ "question" => "Which of the following is not a characteristic of a greedy algorithm?", "options" => ["Local optimization", "Optimal substructure", "Overlapping subproblems", "Greedy choice property"], "answer" => "Overlapping subproblems" ],
            [ "question" => "What is the time complexity of Binary Search in a sorted array?", "options" => ["O(n)", "O(log n)", "O(n log n)", "O(1)"], "answer" => "O(log n)" ],
            [ "question" => "Which algorithm design paradigm is used in Merge Sort?", "options" => ["Greedy", "Dynamic Programming", "Divide and Conquer", "Backtracking"], "answer" => "Divide and Conquer" ],
            [ "question" => "Big O notation primarily describes the:", "options" => ["Average case performance", "Best case performance", "Memory usage", "Upper bound on the growth rate (Worst case)"], "answer" => "Upper bound on the growth rate (Worst case)" ],
            [ "question" => "Which data structure is typically used to implement a priority queue efficiently?", "options" => ["Stack", "Queue", "Heap", "Linked List"], "answer" => "Heap" ],
            // New questions...
            [ "question" => "What is the time complexity of finding an element in a hash table (average case)?", "options" => ["O(n)", "O(log n)", "O(1)", "O(n^2)"], "answer" => "O(1)" ],
            [ "question" => "Which sorting algorithm has a worst-case time complexity of O(n^2) but performs well on nearly sorted data?", "options" => ["Merge Sort", "Quick Sort", "Heap Sort", "Insertion Sort"], "answer" => "Insertion Sort" ],
            [ "question" => "Dijkstra's algorithm is used to find:", "options" => ["Minimum Spanning Tree", "Shortest path in a weighted graph (non-negative weights)", "Maximum flow", "Strongly connected components"], "answer" => "Shortest path in a weighted graph (non-negative weights)" ],
            [ "question" => "Which algorithm paradigm explores all possible solutions by incrementally building candidates and abandoning a candidate ('backtracking') as soon as it determines that it cannot possibly lead to a valid solution?", "options" => ["Greedy", "Divide and Conquer", "Dynamic Programming", "Backtracking"], "answer" => "Backtracking" ],
            [ "question" => "What does 'Optimal Substructure' mean in the context of Dynamic Programming?", "options" => ["The problem can be solved greedily", "An optimal solution to the problem contains within it optimal solutions to subproblems", "The problem involves overlapping subproblems", "The problem has only one solution"], "answer" => "An optimal solution to the problem contains within it optimal solutions to subproblems" ],
            [ "question" => "What is the time complexity of Bubble Sort in the worst case?", "options" => ["O(n)", "O(log n)", "O(n log n)", "O(n^2)"], "answer" => "O(n^2)" ],
            [ "question" => "Which data structure uses a Last-In, First-Out (LIFO) approach?", "options" => ["Queue", "Heap", "Stack", "Linked List"], "answer" => "Stack" ],
            [ "question" => "Which data structure uses a First-In, First-Out (FIFO) approach?", "options" => ["Stack", "Queue", "Heap", "Tree"], "answer" => "Queue" ],
            [ "question" => "What is the main advantage of Merge Sort over Quick Sort?", "options" => ["Faster average time complexity", "Lower space complexity", "Guaranteed O(n log n) worst-case time complexity", "Easier implementation"], "answer" => "Guaranteed O(n log n) worst-case time complexity" ],
            [ "question" => "Knapsack problem (0/1 variation) is typically solved using which paradigm?", "options" => ["Greedy", "Divide and Conquer", "Dynamic Programming", "Brute Force"], "answer" => "Dynamic Programming" ],
            [ "question" => "What is the space complexity of an in-place sorting algorithm?", "options" => ["O(n)", "O(log n)", "O(1)", "O(n^2)"], "answer" => "O(1)" ], // Typically O(1) auxiliary space, some might use O(log n) for recursion stack
            [ "question" => "Which algorithm finds the Minimum Spanning Tree (MST) of a weighted undirected graph?", "options" => ["Dijkstra's Algorithm", "Bellman-Ford Algorithm", "Prim's Algorithm / Kruskal's Algorithm", "Floyd-Warshall Algorithm"], "answer" => "Prim's Algorithm / Kruskal's Algorithm" ],

        ],
        // Add even more subjects here if needed
    ];

    $section_id = 0;
    foreach ($subjects as $subject => $questions) {
        echo "<div class='subject-section'>";
        // Use htmlspecialchars to prevent XSS
        echo "<div class='subject-title' onclick='toggleQuestions(\"section" . htmlspecialchars($section_id) . "\")'>" . htmlspecialchars($subject) . " (" . count($questions) . " Questions)</div>";
        echo "<div class='questions' id='section" . htmlspecialchars($section_id) . "'>";
        $q_id = 0;
        foreach ($questions as $q) {
            $unique_name = "q" . htmlspecialchars($section_id) . "_" . htmlspecialchars($q_id);
            $answer_id = "answer" . htmlspecialchars($section_id) . "_" . htmlspecialchars($q_id);

            echo "<div class='question'>";
            echo "<strong>Q" . ($q_id + 1) . ": " . htmlspecialchars($q['question']) . "</strong>"; // Sanitize output

            // Shuffle options (optional but recommended)
            $shuffled_options = $q['options'];
            shuffle($shuffled_options);

            foreach ($shuffled_options as $option) {
                 // Use labels for better accessibility and click handling
                echo "<label><input type='radio' name='" . $unique_name . "'> " . htmlspecialchars($option) . "</label>";
            }
            // Add button to reveal answer
            echo "<button onclick='showAnswer(\"" . $answer_id . "\")'>Show/Hide Answer</button>";
            // Ensure answer is also sanitized
            echo "<div class='answer' id='" . $answer_id . "'>Answer: " . htmlspecialchars($q['answer']) . "</div>";
            echo "</div>";
            $q_id++;
        }
        echo "</div>"; // Close questions div
        echo "</div>"; // Close subject-section div
        $section_id++;
    }
    ?>

</body>
</html>
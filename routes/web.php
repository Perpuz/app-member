<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/books', function () {
    return view('books.index');
})->name('books.index');

Route::get('/books/{id}', function ($id) {
    return view('books.show', ['id' => $id]);
})->name('books.show');

Route::get('/transactions', function () {
    return view('transactions.index');
})->name('transactions.index');

Route::get('/profile', function () {
    return view('profile.edit');
})->name('profile');

// Custom GraphiQL Interface
Route::get('/graphiql', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>GraphiQL</title>
    <style>
        body { height: 100%; margin: 0; width: 100%; overflow: hidden; }
        #graphiql { height: 100vh; }
    </style>
    <script crossorigin src="https://cdnjs.cloudflare.com/ajax/libs/react/17.0.2/umd/react.production.min.js"></script>
    <script crossorigin src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/17.0.2/umd/react-dom.production.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/graphiql/1.11.5/graphiql.min.css" rel="stylesheet" />
    <script crossorigin src="https://cdnjs.cloudflare.com/ajax/libs/graphiql/1.11.5/graphiql.min.js"></script>
</head>
<body>
    <div id="graphiql">Loading (CDN)...</div>
    <script>
        function graphQLFetcher(graphQLParams) {
            let headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            };
            
            const token = localStorage.getItem('auth_token');
            if (token) {
                headers['Authorization'] = 'Bearer ' + token;
            }

            return fetch(window.location.origin + '/api/graphql', {
                method: 'post',
                headers: headers,
                body: JSON.stringify(graphQLParams),
            }).then(function (response) {
                return response.json();
            }).catch(function (error) {
                console.error(error);
                return error;
            });
        }
        
        window.onload = function() {
            try {
                ReactDOM.render(
                    React.createElement(GraphiQL, { fetcher: graphQLFetcher, defaultQuery: "query {\n  books(limit: 5) {\n    id\n    title\n    category {\n      name\n    }\n  }\n}" }),
                    document.getElementById('graphiql'),
                );
            } catch (e) {
                document.getElementById('graphiql').innerHTML = "Error loading GraphiQL: " + e.message;
            }
        };
    </script>
</body>
</html>
HTML;
    return response($html);
});

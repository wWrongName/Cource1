<head>
    <link href="https://stackpath.bootstrapcdn.com/bootswatch/4.4.1/superhero/bootstrap.min.css" rel="stylesheet" integrity="sha384-R/oa7KS0iDoHwdh4Gyl3/fU7pgvSCt7oyuQ79pkw+e+bMWD9dzJJa+Zqd+XJS0AD" crossorigin="anonymous">  </head>
    <style>
        body {
            margin-left: 1%;
            margin-top: 1%;
        }
    </style>
</head>
<body>
    <h1>Help and description</h1>
    <hr class="my-4">
    <p>That resource presents an Open API interface for interaction with the site: <a href="https://app.swaggerhub.com/apis/wWrongName/Cource_API/1.0.0">The diary Open API</a></p>
    Please, log in via our telegram bot "Schedulebot", it is necessary to reset your password if you'll forget it.
    Just say "the diary" or "sign in" in order to log into our account.
    <button class="btn btn-secondary" style="position: fixed; right: 1%; bottom: 1%" onclick="window.location.href = `accountPage?user=<?php if(isset($_GET['user'])) echo $_GET['user']?>`">back</button>
</body>
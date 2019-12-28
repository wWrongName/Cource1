<head>
    <link href="https://stackpath.bootstrapcdn.com/bootswatch/4.4.1/superhero/bootstrap.min.css" rel="stylesheet" integrity="sha384-R/oa7KS0iDoHwdh4Gyl3/fU7pgvSCt7oyuQ79pkw+e+bMWD9dzJJa+Zqd+XJS0AD" crossorigin="anonymous">  </head>
<style>
    body {
        position: fixed;
        top: 50%;
        left: 50%;
        margin-top: -170px;
        margin-left: -105px;
        background-image: url(authTheme.png);
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-size: cover;
    }
</style>
</head>
<body>
<b>Input new Pass</b><pre></pre>
<script>
    let send = function () {
        let promise = new Promise(() => {
            let req = new XMLHttpRequest();
            req.open("GET", 'commandsAllocator.php/confirmCode/' + document.getElementById("exampleInputcode").value + '/' + document.getElementById("exampleInputPass").value + '/' + '<?php if(isset($_GET['user'])) echo $_GET['user']?>');
            req.send();
            req.onload = function() {
                if (this.status === 200) {
                    window.location.href = 'authPage.html';
                }
                else {
                    alert('Wrong code');
                }
            };
        });
    }
</script>
<div class="form-group">
    <label for="exampleInputcode">Code</label>
    <input type="text" name="email" class="form-control" id="exampleInputcode" placeholder="Code">
</div>
<div class="form-group">
    <label for="exampleInputPass">New password</label>
    <input type="password" name="password" class="form-control" id="exampleInputPass" placeholder="Password">
</div>
<input id="form" type="submit" class="btn btn-secondary" value="Ok" onclick="send()">
</body>
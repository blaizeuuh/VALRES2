<!doctype html>

<html>
    <head>
        <meta charset="utf-8">
        <title>Connectez vous</title>
        <style>
            .group {
            position: relative;
            }

            .form {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            border: 1px solid white;
            padding: 120px 40px;
            padding-top: 60px;
            padding-bottom: 90px;
            padding-right: 40px;
            padding-left: 40px;
            background-color: black;
            border-radius: 20px;
            position: relative;

            width: 300px;
            height: 400px;
            }

            .form p {
            padding-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: .5px;
            color: white;
            }

            .container-1 {
            padding-top: 30px;
            }

            .main-input {
            font-size: 16px;
            padding: 10px 10px 10px 5px;
            display: block;
            width: 185px;
            border: none;
            border-bottom: 1px solid #6c6c6c;
            background: transparent;
            color: #ffffff;
            }

            .main-input:focus {
            outline: none;
            border-bottom-color: #42ff1c;
            }

            .lebal-email {
            color: #999999;
            font-size: 18px;
            font-weight: normal;
            position: absolute;
            pointer-events: none;
            left: 5px;
            top: 10px;
            transition: 0.2s ease all;
            -moz-transition: 0.2s ease all;
            -webkit-transition: 0.2s ease all;
            }

            .main-input:focus ~ .lebal-email,
            .main-input:valid ~ .lebal-email {
            top: -20px;
            font-size: 14px;
            color: #42ff1c;
            }

            .highlight-span {
            position: absolute;
            height: 60%;
            width: 0px;
            top: 25%;
            left: 0;
            pointer-events: none;
            opacity: 0.5;
            }

            .main-input:focus ~ .highlight-span {
            -webkit-animation: input-focus 0.3s ease;
            animation: input-focus 0.3s ease;
            }

            @keyframes input-focus {
            from {
                background: #42ff1c;
            }

            to {
                width: 185px;
            }
            }

            .submit {
            margin-top: 1.2rem;
            padding: 10px 20px;
            border-radius: 10px;
            }
        </style>
    </head>
    <body style="margin: 0; padding: 0; min-height: 100vh; display: flex; justify-content: center; align-items: center; background-color: #f0f0f0;">
        <form class="form">
            <p>Se connecter</p>
            <div class="group">
                <input required="true" class="main-input" type="text">
                <span class="highlight-span"></span>
                <label class="lebal-email">Nom</label>
            </div>
            <div class="container-1">
                <div class="group">
                <input required="true" class="main-input" type="text">
                <span class="highlight-span"></span>
                <label class="lebal-email">Mot de passe</label>
                </div>
            </div>
            <button class="submit">Envoyer</button>
        </form>
    </body>
</html>
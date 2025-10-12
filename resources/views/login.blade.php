<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ config('app.name') }}</title>

	<link rel="icon" href="{{ url('logo.png') }}" type="image/png" />
	<link href="assets/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Orbitron:wght@700&family=Poppins:wght@600&display=swap" rel="stylesheet">

	<style>
		body {
			margin: 0;
			height: 100vh;
			font-family: 'Roboto', sans-serif;
			display: flex;
			justify-content: center;
			align-items: center;
			transition: background-color 0.4s, color 0.4s;
			background-color: #0f172a;
			color: #fff;
			overflow: hidden;
		}

		body.light-mode {
			background-color: #f8fafc;
			color: #000;
		}

		.vanta-bg {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			z-index: 0;
		}

		.login-card {
			position: relative;
			z-index: 1;
			background-color: rgba(255, 255, 255, 0.1);
			backdrop-filter: blur(10px);
			border-radius: 16px;
			padding: 2rem;
			width: 400px;
			box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
			transition: background-color 0.4s;
		}

		body.light-mode .login-card {
			background-color: rgba(255, 255, 255, 0.85);
		}

		.login-card h3 {
			text-align: center;
			margin-bottom: 1.5rem;
			font-weight: 500;
		}

		.form-control {
			border-radius: 10px;
			padding: 10px 15px;
		}

		.btn-primary {
			background-color: #3b82f6;
			border: none;
			border-radius: 10px;
			padding: 10px;
			transition: background 0.3s;
		}

		.btn-primary:hover {
			background-color: #2563eb;
		}

		.toggle-switch {
			position: fixed;
			top: 20px;
			left: 20px;
			width: 100px;
			height: 50px;
			--light: #d8dbe0;
			--dark: #28292c;
			z-index: 10;
		}

		.switch-label {
			position: absolute;
			width: 100%;
			height: 50px;
			background-color: var(--dark);
			border-radius: 25px;
			cursor: pointer;
			border: 3px solid var(--dark);
		}

		.checkbox {
			position: absolute;
			display: none;
		}

		.slider {
			position: absolute;
			width: 100%;
			height: 100%;
			border-radius: 25px;
			transition: 0.3s;
		}

		.checkbox:checked~.slider {
			background-color: var(--light);
		}

		.slider::before {
			content: "";
			position: absolute;
			top: 10px;
			left: 10px;
			width: 25px;
			height: 25px;
			border-radius: 50%;
			box-shadow: inset 12px -4px 0px 0px var(--light);
			background-color: var(--dark);
			transition: 0.3s;
		}

		.checkbox:checked~.slider::before {
			transform: translateX(50px);
			background-color: var(--dark);
			box-shadow: none;
		}

		/* === Welcome Animation === */
		#welcome-screen {
			position: fixed;
			inset: 0;
			background-color: #0f172a;
			display: flex;
			justify-content: center;
			align-items: center;
			flex-direction: column;
			z-index: 9999;
			overflow: hidden;
			animation: fadeOut 1s ease-in-out 6.5s forwards;
			text-align: center;
		}

		#welcome-globe {
			position: absolute;
			inset: 0;
			z-index: 0;
		}

		.welcome-text {
			color: #ffffff;
			text-align: center;
			font-size: 4rem; /* kattalashgan yozuv */
			line-height: 1.4;
			z-index: 2;
			text-shadow: 0 0 25px rgba(59, 130, 246, 0.7);
			font-family: 'Poppins', sans-serif;
			font-weight: 600;
			letter-spacing: 1px;
		}

		.welcome-text .fade {
			opacity: 0;
			animation: fadeIn 1.5s forwards;
		}

		.welcome-text .fade:nth-child(1) {
			animation-delay: 0.3s;
		}

		.welcome-text .fade:nth-child(3) {
			animation-delay: 4.8s;
		}

		#typing-text {
			font-family: 'Orbitron', sans-serif;
			color: #3b82f6;
			font-weight: 700;
			letter-spacing: 3px;
			font-size: 4rem; /* typing yozuv hajmi */
			text-shadow: 0 0 25px rgba(59, 130, 246, 0.8);
		}

		.cursor {
			display: inline-block;
			color: #3b82f6;
			animation: blink 0.8s infinite;
			font-size: 4rem;
		}

		@keyframes blink {
			50% {
				opacity: 0;
			}
		}

		@keyframes fadeIn {
			to {
				opacity: 1;
				transform: translateY(0);
			}

			from {
				opacity: 0;
				transform: translateY(15px);
			}
		}

		@keyframes fadeOut {
			to {
				opacity: 0;
				visibility: hidden;
			}
		}
	</style>
</head>

<body>

	<!-- === Welcome screen with animated globe === -->
	<div id="welcome-screen">
		<div id="welcome-globe"></div>
		<h1 class="welcome-text">
			<span class="fade">Welcome to</span><br>
			<span id="typing-text"></span><span class="cursor">|</span><br>
			<span class="fade">Global Voice</span>
		</h1>
	</div>

	<div class="vanta-bg"></div>

	<div class="toggle-switch">
		<label class="switch-label">
			<input type="checkbox" id="theme-toggle" class="checkbox">
			<span class="slider"></span>
		</label>
	</div>

	<div class="login-card">
		<div class="text-center mb-3">
			<img src="/logo.png" width="70" alt="logo">
		</div>
		<h3>Kirish</h3>

		<form method="POST" action="{{ route('login_post') }}">
			@csrf
			<div class="mb-3">
				<label for="inputLogin" class="form-label">Login</label>
				<input type="text" class="form-control" id="inputLogin" name="login" placeholder="Logini kiriting">
			</div>
			<div class="mb-3">
				<label for="inputPassword" class="form-label">Parol</label>
				<input type="password" class="form-control" id="inputPassword" name="password" placeholder="Parolni kiriting">
			</div>
			<div class="form-check mb-3">
				<input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
				<label class="form-check-label" for="remember">Eslab qolish</label>
			</div>
			<button type="submit" class="btn btn-primary w-100">Kirish</button>
		</form>
	</div>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r121/three.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.globe.min.js"></script>
	<script>
		let vantaEffect;
		function setVanta() {
			if (vantaEffect) vantaEffect.destroy();
			vantaEffect = window.VANTA.GLOBE({
				el: ".vanta-bg",
				mouseControls: true,
				touchControls: true,
				gyroControls: false,
				minHeight: 200.00,
				minWidth: 200.00,
				scale: 1.00,
				scaleMobile: 1.00,
				color: 0x00bcd4,
				color2: 0x2196f3,
				backgroundColor: 0x0f172a
			});
		}
		setVanta();

		// Globe in Welcome screen
		window.VANTA.GLOBE({
			el: "#welcome-globe",
			mouseControls: false,
			touchControls: false,
			minHeight: 200.00,
			minWidth: 200.00,
			scale: 1.00,
			scaleMobile: 1.00,
			color: 0x00bcd4,
			color2: 0x2196f3,
			backgroundColor: 0x0f172a
		});

		const toggle = document.getElementById("theme-toggle");
		toggle.addEventListener("change", () => {
			document.body.classList.toggle("light-mode");
			if (document.body.classList.contains("light-mode")) {
				vantaEffect.setOptions({ backgroundColor: 0xf8fafc, color: 0x2563eb, color2: 0x3b82f6 });
			} else {
				vantaEffect.setOptions({ backgroundColor: 0x0f172a, color: 0x00bcd4, color2: 0x2196f3 });
			}
		});

		document.addEventListener("DOMContentLoaded", () => {
			const text = "Global Voice";
			const typingText = document.getElementById("typing-text");
			const mainContent = document.querySelector(".login-card");
			let i = 0;

			mainContent.style.opacity = "0";

			function type() {
				if (i < text.length) {
					typingText.textContent += text.charAt(i);
					i++;
					setTimeout(type, 120);
				}
			}

			setTimeout(() => {
				document.querySelectorAll('.fade')[0].style.opacity = '1';
				type();
			}, 500);

			setTimeout(() => {
				document.querySelectorAll('.fade')[1].style.opacity = '1';
			}, 4800);

			setTimeout(() => {
				document.getElementById("welcome-screen").style.display = "none";
				mainContent.style.transition = "opacity 1s ease";
				mainContent.style.opacity = "1";
			}, 6500);
		});
	</script>
</body>
</html>

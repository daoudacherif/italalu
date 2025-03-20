
<!DOCTYPE html>
<html lang="en">
<head>
<title>Inventory Management System || Home Page</title>

<!-- Custom Theme files -->
<link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
<link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
<link href="css/fasthover.css" rel="stylesheet" type="text/css" media="all" />
<link href="css/popuo-box.css" rel="stylesheet" type="text/css" media="all" />
<!-- //Custom Theme files -->
<!-- font-awesome icons -->
<link href="css/font-awesome.css" rel="stylesheet"> 
<!-- //font-awesome icons -->
<!-- js -->
<script src="js/jquery.min.js"></script>
<link rel="stylesheet" href="css/jquery.countdown.css" /> <!-- countdown --> 
<!-- //js -->  
<!-- web fonts --> 
<link href='//fonts.googleapis.com/css?family=Glegoo:400,700' rel='stylesheet' type='text/css'>
<link href='//fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,400italic,600,600italic,700,700italic,800,800italic' rel='stylesheet' type='text/css'>
<!-- //web fonts -->  
<!-- start-smooth-scrolling -->
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$(".scroll").click(function(event){		
			event.preventDefault();
			$('html,body').animate({scrollTop:$(this.hash).offset().top},1000);
		});
	});
</script>
<!-- //end-smooth-scrolling --> 
</head> 
<body>
	<!-- for bootstrap working -->
	<script type="text/javascript" src="js/bootstrap-3.1.1.min.js"></script>
	<!-- //for bootstrap working -->
	
		<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Italal - Matériaux en Aluminium</title>
  <style>
    /* Style basique pour l'exemple */
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
    }
    header {
      background-color: #333;
      color: #fff;
      padding: 1rem;
    }
    header h1 {
      margin: 0;
      font-size: 1.8rem;
    }
    nav {
      background-color: #444;
      display: flex;
      justify-content: center;
    }
    nav a {
      color: #fff;
      text-decoration: none;
      padding: 0.8rem 1.2rem;
      display: inline-block;
    }
    nav a:hover {
      background-color: #555;
    }
    .hero {
      background: url("https://via.placeholder.com/1400x400/ccc/000?text=Image+Aluminium") no-repeat center center;
      background-size: cover;
      height: 400px;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: #fff;
    }
    .hero h2 {
      background-color: rgba(0,0,0,0.6);
      padding: 1rem 2rem;
      border-radius: 5px;
      font-size: 2rem;
    }
    main {
      padding: 2rem;
      max-width: 1100px;
      margin: 0 auto;
      background-color: #fff;
      margin-top: -30px; /* effet d'overlap sur la hero */
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      position: relative;
      z-index: 2;
    }
    .section-title {
      font-size: 1.6rem;
      margin-bottom: 1rem;
      color: #333;
    }
    footer {
      background-color: #333;
      color: #aaa;
      text-align: center;
      padding: 1rem;
      margin-top: 2rem;
    }
    .btn {
      background-color: #ff6600;
      color: #fff;
      padding: 0.8rem 1.2rem;
      text-decoration: none;
      border-radius: 4px;
      transition: background-color 0.3s ease;
    }
    .btn:hover {
      background-color: #e65c00;
    }
  </style>
</head>
<body>

<header>
  <h1>Italal - Aluminium & Matériaux de Construction</h1>
</header>
<!-- navigation -->
<div class="navigation">
		<div class="container">
			<nav class="navbar navbar-default">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header nav_2">
					<button type="button" class="navbar-toggle collapsed navbar-toggle1" data-toggle="collapse" data-target="#bs-megadropdown-tabs">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				</div> 
				<div class="collapse navbar-collapse" id="bs-megadropdown-tabs">
					<ul class="nav navbar-nav">
						<li ><a href="index.php" class="act">Home</a></li>	
						
						<li><a href="admin/login.php">Admin</a></li> 
					  
						
					</ul>
				</div>
			</nav>
		</div>
	</div>
	<!-- //navigation -->

<div class="hero">
  <h2>Spécialiste de l'aluminium en République de Guinée</h2>
</div>

<main>
  <section>
    <h2 class="section-title">Bienvenue chez Italal</h2>
    <p>
      Nous sommes une entreprise spécialisée dans l'importation et la vente 
      de matériaux de construction à base d'aluminium en République de Guinée. 
      Grâce à notre expertise, nous proposons une large gamme de produits 
      (profils, tôles, fenêtres, portes, etc.) pour répondre aux besoins 
      de vos projets, qu'ils soient résidentiels, commerciaux ou industriels.
    </p>
    <p>
      Chez <strong>Italal</strong>, nous mettons un point d'honneur à fournir 
      des produits de haute qualité, résistants et adaptés aux conditions 
      climatiques locales. Notre équipe se tient à votre disposition pour 
      vous conseiller et vous accompagner dans vos choix.
    </p>
  </section>

  <section>
    <h2 class="section-title">Nos Produits</h2>
    <p>
      Nous offrons un large éventail de solutions en aluminium :
    </p>
    <ul>
      <li>Profilés en aluminium pour portes et fenêtres</li>
      <li>Tôles et plaques d'aluminium de différentes épaisseurs</li>
      <li>Accessoires et pièces de fixation</li>
      <li>Finitions personnalisées (laquage, anodisation, etc.)</li>
    </ul>
    <p>
      Découvrez l'ensemble de nos produits et trouvez la solution la plus 
      adaptée à votre projet.
    </p>
    <a href="#" class="btn">En savoir plus</a>
  </section>

  <section>
    <h2 class="section-title">Pourquoi choisir Italal ?</h2>
    <ul>
      <li><strong>Qualité supérieure</strong> : matériaux sélectionnés et conformes aux normes.</li>
      <li><strong>Conseils d'experts</strong> : une équipe formée pour vous guider.</li>
      <li><strong>Service après-vente</strong> : un accompagnement réactif et personnalisé.</li>
      <li><strong>Livraison rapide</strong> : logistique efficace en Guinée.</li>
    </ul>
  </section>

  <section>
    <h2 class="section-title">Contactez-nous</h2>
    <p>
      Vous avez des questions ? Besoin d'un devis ? 
      <a href="#" class="btn">Contactez-nous</a>
    </p>
  </section>
</main>

<footer>
  <p>&copy; 2023 Italal - Aluminium & Matériaux de Construction. Tous droits réservés.</p>
</footer>

</body>
</html>


	<!-- cart-js -->
	<script src="js/minicart.js"></script>
	<script>
        w3ls.render();

        w3ls.cart.on('w3sb_checkout', function (evt) {
        	var items, len, i;

        	if (this.subtotal() > 0) {
        		items = this.items();

        		for (i = 0, len = items.length; i < len; i++) { 
        		}
        	}
        });
    </script>  
	<!-- //cart-js -->   
</body>
</html>
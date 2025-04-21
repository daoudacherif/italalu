  <!-- Optionally include additional responsive overrides -->
  <style>
    /* Example: Orientation overlay to prompt users to rotate device */
   
    /* Show overlay when device is in portrait */
    @media screen and (orientation: portrait) {
      #rotate-overlay {
        display: block;
      }
      /* Optionally, hide main app container if you want to force landscape */
      #app-container {
        display: none;
      }
    }
    /* Landscape - hide overlay and show app */
    @media screen and (orientation: landscape) {
      #rotate-overlay {
        display: none;
      }
      #app-container {
        display: block;
      }
    }
    .control-label { 
  font-weight: bold;
  font-size: 20px;
  color:black;
}

    /* The rest of your responsive CSS code (as provided in the previous integration)
       can either be here or in your external responsive.css file included via cs.php */
  </style>
  </head>
<body>
     <!-- Orientation overlay -->
  <div id="rotate-overlay">
    <p>Pour une meilleure expérience, veuillez tourner votre appareil.</p>
  </div>
  
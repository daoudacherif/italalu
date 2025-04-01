<!-- Sidebar -->
<div id="sidebar">
  <ul class="nav nav-list">
    <li class="active">
      <a href="dashboard.php">
        <i class="icon icon-home"></i>
        <span>Tableau de bord</span>
      </a>
    </li>

    <!-- Catégorie Dropdown -->
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="icon icon-th-list"></i>
        <span>Catégorie</span>
        <b class="caret"></b>
      </a>
      <ul class="dropdown-menu">
        <li><a href="add-category.php">Ajouter une catégorie</a></li>
        <li><a href="manage-category.php">Gérer les catégories</a></li>
      </ul>
    </li>

    <!-- Sous-catégorie Dropdown -->
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="icon icon-inbox"></i>
        <span>Sous-catégorie</span>
        <b class="caret"></b>
      </a>
      <ul class="dropdown-menu">
        <li><a href="add-subcategory.php">Ajouter une sous-catégorie</a></li>
        <li><a href="manage-subcategory.php">Gérer les sous-catégories</a></li>
      </ul>
    </li>

    <!-- Marque Dropdown -->
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="icon icon-file"></i>
        <span>Marque</span>
        <b class="caret"></b>
      </a>
      <ul class="dropdown-menu">
        <li><a href="add-brand.php">Ajouter une marque</a></li>
        <li><a href="manage-brand.php">Gérer les marques</a></li>
      </ul>
    </li>

    <!-- Produit Dropdown -->
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="icon icon-info-sign"></i>
        <span>Produit</span>
        <b class="caret"></b>
      </a>
      <ul class="dropdown-menu">
        <li><a href="add-product.php">Ajouter un produit</a></li>
        <li><a href="manage-product.php">Gérer les produits</a></li>
      </ul>
    </li>

    <!-- Other Items -->
    <li>
      <a href="inventory.php">
        <i class="icon icon-info-sign"></i>
        <span>Inventaire</span>
      </a>
    </li>
    <li>
      <a href="cart.php">
        <i class="icon-shopping-cart"></i>
        <span>Comptant</span>
        <span class="label label-important"><?php echo htmlentities($cartcountcount); ?></span>
      </a>
    </li>
    <li>
      <a href="dettecart.php">
        <i class="icon-shopping-cart"></i>
        <span>Terme</span>
        <span class="label label-important"><?php echo htmlentities($cartcountcount); ?></span>
      </a>
    </li>
    <li>
      <a href="search.php">
        <i class="icon-search"></i>
        <span>Rechercher</span>
      </a>
    </li>
    <li>
      <a href="transact.php">
        <i class="icon-search"></i>
        <span>Transactions</span>
      </a>
    </li>
    <li>
      <a href="return.php">
        <i class="icon-search"></i>
        <span>Retour</span>
      </a>
    </li>
    <li>
      <a href="invoice-search.php">
        <i class="icon-search"></i>
        <span>Rechercher une facture</span>
      </a>
    </li>
    <li>
      <a href="arrival.php">
        <i class="icon-group"></i>
        <span>Arrivage</span>
      </a>
    </li>
    <li>
      <a href="supplier.php">
        <i class="icon-group"></i>
        <span>Fournisseur</span>
      </a>
    </li>
    <li>
      <a href="supplier-payments.php">
        <i class="icon-group"></i>
        <span>Payment Fournisseur</span>
      </a>
    </li>
    <li>
      <a href="client-account.php">
        <i class="icon-group"></i>
        <span>Compte Client</span>
      </a>
    </li>
    <li>
      <a href="customer-details.php">
        <i class="icon-group"></i>
        <span>Détails Client</span>
      </a>
    </li>

    <!-- Rapports Dropdown -->
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="icon icon-th-list"></i>
        <span>Rapports</span>
        <b class="caret"></b>
      </a>
      <ul class="dropdown-menu">
        <li><a href="stock-report.php">Rapport de stock</a></li>
        <li><a href="sales-report.php">Rapport des ventes</a></li>
        <li><a href="daily-repport.php">Rapport journalier</a></li>
      </ul>
    </li>
  </ul>
</div>

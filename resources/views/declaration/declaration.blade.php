<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport d'Auto-Déclaration SFE</title>
    <style>
        {!! file_get_contents(resource_path('css/declaration.css')) !!}
        @media print { .page-break { page-break-before: always; } }
    </style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
<div class="max-w-4xl mx-auto bg-white shadow-lg p-8 mt-6">

    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-blue-900">RAPPORT D'AUTO-DÉCLARATION SFE</h1>
        <h2 class="text-xl text-gray-600">e-MECeF - Direction Générale des Impôts du Bénin</h2>
    </div>

    <!-- ANNEXE 1 -->
    <div id="annexe1" class="page-break">
        <h2 class="text-2xl font-bold mb-6 text-blue-900 border-b-2 pb-2">ANNEXE 1 -- Formulaire d'auto-déclaration</h2>
        
        <div class="mb-6">
            <h3 class="text-lg font-semibold bg-blue-100 p-2 mb-2">Informations sur l'entreprise</h3>
            <table class="w-full"><tbody>
                <tr><td class="font-bold p-2 w-1/3">Nom commercial:</td><td class="p-2">{{ $companyInfo['name'] ?? '' }}</td></tr>
                <tr><td class="font-bold p-2">Régime fiscal:</td><td class="p-2">{{ $companyInfo['tax_regime'] ?? '' }}</td></tr>
                <tr><td class="font-bold p-2">IFU:</td><td class="p-2">{{ $companyInfo['ifu'] ?? '' }}</td></tr>
                <tr><td class="font-bold p-2">RCCM:</td><td class="p-2">{{ $companyInfo['rccm'] ?? '' }}</td></tr>
            </tbody></table>
        </div>

        <div class="mb-6">
            <h3 class="text-lg font-semibold bg-blue-100 p-2 mb-2">Informations sur le SFE</h3>
            <table class="w-full"><tbody>
                <tr><td class="font-bold p-2 w-1/3">Nom du logiciel:</td><td class="p-2">{{ $sfeInfo['software_name'] ?? '' }}</td></tr>
                <tr><td class="font-bold p-2">Version:</td><td class="p-2">{{ $sfeInfo['software_version'] ?? '' }}</td></tr>
            </tbody></table>
        </div>

        <div class="mb-6">
            <h3 class="text-lg font-semibold bg-blue-100 p-2 mb-2">Déclaration de Conformité</h3>
            <table class="w-full text-center"><thead><tr class="bg-gray-200"><th class="p-2">Type de Facture</th><th class="p-2">Supporté</th></tr></thead><tbody>
                @foreach($sfeInfo['supported_features']['invoice_types'] as $type => $isSupported)
                <tr><td class="border p-2">{{ $type }}</td><td class="border p-2 font-bold">{{ $isSupported ? 'OUI' : 'NON' }}</td></tr>
                @endforeach
            </tbody></table>
        </div>
    </div>

    <!-- ANNEXE 2 -->
    <div id="annexe2" class="page-break">
        <h2 class="text-2xl font-bold mb-6 text-blue-900 border-b-2 pb-2">ANNEXE 2 -- Détails des Factures de Test</h2>
        
        @foreach ($testResults as $result)
            <div class="mb-8 page-break-inside-avoid">
                <h3 class="text-xl font-bold bg-blue-900 text-white p-3">{{ $result['name'] }}</h3>
                <div class="border-2 border-gray-300 p-4">
                    <p class="mb-4"><strong>Description:</strong> {{ $result['description'] }}</p>
                    <div class="bg-gray-200 border-dashed border-2 border-gray-400 p-4 flex justify-center items-center">
                        <img src="{{ $result['image_path'] }}" alt="Capture d'écran pour {{ $result['name'] }}" class="max-w-full h-auto shadow-lg">
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>
</body>
</html>

<p class="ml-6 mt-4">5. S'il y a un écart, s'il vous plaît prendre des mesures correctives sur SFE et a apporté des modifications jusqu'à ce que tout est correct.</p>
</div>

<!-- ANNEXE 1 -->
<div id="annexe1" class="p-8 border-b-2 page-break">
<h2 class="text-2xl font-bold mb-6 text-blue-900 text-center">ANNEXE 1 -- Formulaire d'auto-déclaration</h2>
<div class="text-center mb-6">
<h3 class="text-xl font-bold text-blue-800">e-MECeF</h3>
</div>

<div class="bg-green-100 border-2 border-green-600 p-4 mb-6">
<h3 class="text-lg font-bold text-center">L'auto-déclaration de SFE</h3>
<p class="text-center font-bold text-red-600">Remplissez tous les champs verts et fournissez des photos de tous les cas de test</p>
</div>

<!-- Informations de l'entreprise -->
<div class="border-2 border-gray-400 mb-6">
<div class="bg-blue-100 p-2">
<p class="font-bold">REEL TPS</p>
</div>
<table class="w-full">
<tr>
<td class="border p-2 w-2/3 font-semibold">Nom commercial</td>
<td class="border p-2 w-1/3 font-semibold">Régime fiscal</td>
</tr>
<tr>
<td class="border p-2 bg-green-100" contenteditable="true">&nbsp;</td>
<td class="border p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border p-2 w-2/3 font-semibold">IFU</td>
<td class="border p-2 w-1/3 font-semibold">RCCM</td>
</tr>
<tr>
<td class="border p-2 bg-green-100" contenteditable="true">&nbsp;</td>
<td class="border p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border p-2 font-semibold" colspan="2">
<div class="grid grid-cols-4 gap-2">
<div>Département</div>
<div>Ville/Commune</div>
<div>Arrondissement</div>
<div>QIP</div>
</div>
</td>
</tr>
<tr>
<td class="border p-2 bg-green-100" colspan="2" contenteditable="true">
<div class="grid grid-cols-4 gap-2">
<div>&nbsp;</div>
<div>&nbsp;</div>
<div>&nbsp;</div>
<div>&nbsp;</div>
</div>
</td>
</tr>
<tr>
<td class="border p-2 w-2/3 font-semibold">Téléphone</td>
<td class="border p-2 w-1/3 font-semibold">Email</td>
</tr>
<tr>
<td class="border p-2 bg-green-100" contenteditable="true">&nbsp;</td>
<td class="border p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
</table>
</div>

<!-- Informations SFE -->
<div class="border-2 border-gray-400 mb-6">
<div class="bg-blue-100 p-2">
<p class="font-bold">Informations sur le système de facturation (SFE):</p>
</div>
<table class="w-full">
<tr>
<td class="border p-2 w-1/4 font-semibold">Pays d'origine</td>
<td class="border p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border p-2 font-semibold">Fabricant</td>
<td class="border p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border p-2 font-semibold">Nom du logiciel</td>
<td class="border p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border p-2 font-semibold">Version du logiciel</td>
<td class="border p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
</table>
</div>

<!-- Système d'exploitation -->
<div class="border-2 border-gray-400 mb-6 p-4">
<p class="font-semibold mb-2">Quel est le système d'exploitation de votre système de facturation:</p>
<div class="grid grid-cols-3 gap-4">
<div class="bg-green-100 p-2">
<label class="flex items-center">
<input type="checkbox" class="mr-2">
<span>Windows</span>
</label>
<input type="text" class="w-full mt-2 p-1 border" placeholder="version">
</div>
<div class="bg-green-100 p-2">
<label class="flex items-center">
<input type="checkbox" class="mr-2">
<span>Linux</span>
</label>
<input type="text" class="w-full mt-2 p-1 border" placeholder="version">
</div>
<div class="bg-green-100 p-2">
<label class="flex items-center">
<input type="checkbox" class="mr-2">
<span>Autre</span>
</label>
<input type="text" class="w-full mt-2 p-1 border" placeholder="version">
</div>
</div>
</div>

<!-- Contact fournisseur -->
<div class="border-2 border-gray-400 mb-6">
<div class="bg-blue-100 p-2">
<p class="font-bold">Contact de votre fournisseur de système de facturation au Bénin</p>
</div>
<table class="w-full">
<tr>
<td class="border p-2 w-1/4 font-semibold">Nom commercial</td>
<td class="border p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border p-2 font-semibold">Ville</td>
<td class="border p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border p-2 font-semibold">Téléphone</td>
<td class="border p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border p-2 font-semibold">Email</td>
<td class="border p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
</table>
</div>

<div class="border-2 border-gray-400 mb-6 p-2">
<p class="font-semibold">Date: <span class="bg-green-100 px-4 py-1 ml-2" contenteditable="true">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></p>
</div>

<!-- Déclaration de conformité -->
<div class="bg-gray-100 p-6 mb-6 border-2 border-gray-400">
<h3 class="text-xl font-bold text-center mb-6">DÉCLARATION DE CONFORMITÉ</h3>

<h4 class="font-bold mb-3">NOTRE SFE GENERE LES TYPES DE FACTURES SUIVANTS:</h4>
<table class="w-full border-collapse mb-6">
<thead>
<tr class="bg-blue-200">
<th class="border border-gray-400 p-2 text-left">Type de Facture</th>
<th class="border border-gray-400 p-2 w-1/3">Prise en charge (OUI ou NON)</th>
</tr>
</thead>
<tbody>
<tr>
<td class="border border-gray-400 p-2">FACTURE DE VENTE</td>
<td class="border border-gray-400 p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border border-gray-400 p-2">FACTURE D'AVOIR</td>
<td class="border border-gray-400 p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border border-gray-400 p-2">FACTURE DE VENTE A L'EXPORTATION</td>
<td class="border border-gray-400 p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border border-gray-400 p-2">FACTURE D'AVOIR A L'EXPORTATION</td>
<td class="border border-gray-400 p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
</tbody>
</table>

<h4 class="font-bold mb-3">NOTRE SFE GERE LES GROUPES D'IMPÔT SUIVANTS:</h4>
<table class="w-full border-collapse mb-6">
<thead>
<tr class="bg-blue-200">
<th class="border border-gray-400 p-2">Groupe de taxation</th>
<th class="border border-gray-400 p-2">Étiquette</th>
<th class="border border-gray-400 p-2">Description</th>
<th class="border border-gray-400 p-2 w-1/5">Prise en charge (OUI ou NON)</th>
</tr>
</thead>
<tbody>
<tr>
<td class="border border-gray-400 p-2">Groupe A</td>
<td class="border border-gray-400 p-2">EXO</td>
<td class="border border-gray-400 p-2">Exonéré</td>
<td class="border border-gray-400 p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border border-gray-400 p-2">Groupe B</td>
<td class="border border-gray-400 p-2">TAX</td>
<td class="border border-gray-400 p-2">Taxable</td>
<td class="border border-gray-400 p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border border-gray-400 p-2">Groupe C</td>
<td class="border border-gray-400 p-2">EXP</td>
<td class="border border-gray-400 p-2">Exportation de produits taxables</td>
<td class="border border-gray-400 p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border border-gray-400 p-2">Groupe D</td>
<td class="border border-gray-400 p-2">MP</td>
<td class="border border-gray-400 p-2">TVA régime d'exception</td>
<td class="border border-gray-400 p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border border-gray-400 p-2">Groupe E</td>
<td class="border border-gray-400 p-2">TPS</td>
<td class="border border-gray-400 p-2">Régime fiscal TPS</td>
<td class="border border-gray-400 p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border border-gray-400 p-2">Groupe F</td>
<td class="border border-gray-400 p-2">RES</td>
<td class="border border-gray-400 p-2">Réservé</td>
<td class="border border-gray-400 p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
</tbody>
</table>

<h4 class="font-bold mb-3">NOTRE SFE GERE:</h4>
<table class="w-full border-collapse">
<thead>
<tr class="bg-blue-200">
<th class="border border-gray-400 p-2 text-left">Description</th>
<th class="border border-gray-400 p-2 w-1/3">Prise en charge (OUI ou NON)</th>
</tr>
</thead>
<tbody>
<tr>
<td class="border border-gray-400 p-2">AIB</td>
<td class="border border-gray-400 p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border border-gray-400 p-2">TAXE SPECIFIQUE</td>
<td class="border border-gray-400 p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
<tr>
<td class="border border-gray-400 p-2">TAXE DE SEJOUR</td>
<td class="border border-gray-400 p-2 bg-green-100" contenteditable="true">&nbsp;</td>
</tr>
</tbody>
</table>
</div>
</div>

<!-- ANNEXE 2 -->
<div id="annexe2" class="p-8 page-break">
<h2 class="text-2xl font-bold mb-6 text-blue-900 text-center">ANNEXE 2 : LISTE DES FACTURES DE TEST PROPOSÉES</h2>

<div class="overflow-x-auto">
<table class="w-full border-collapse text-xs table-compact">
<thead>
<tr class="bg-blue-200">
<th rowspan="2" class="border border-gray-400 p-1">N° TEST</th>
<th rowspan="2" class="border border-gray-400 p-1">Type de Facture</th>
<th rowspan="2" class="border border-gray-400 p-1">Article taxable</th>
<th rowspan="2" class="border border-gray-400 p-1">Article Exonéré</th>
<th rowspan="2" class="border border-gray-400 p-1">Article Régime d'exception</th>
<th rowspan="2" class="border border-gray-400 p-1">Article Exportation</th>
<th rowspan="2" class="border border-gray-400 p-1">Article Régime TPS</th>
<th rowspan="2" class="border border-gray-400 p-1">IFU et Nom Client</th>
<th rowspan="2" class="border border-gray-400 p-1">Taxe Spécifique</th>
<th rowspan="2" class="border border-gray-400 p-1">AIB 1%</th>
<th rowspan="2" class="border border-gray-400 p-1">AIB 5%</th>
<th rowspan="2" class="border border-gray-400 p-1">Taxe de Séjour</th>
<th colspan="5" class="border border-gray-400 p-1">Quantité</th>
<th rowspan="2" class="border border-gray-400 p-1">VISA DGI*</th>
</tr>
<tr class="bg-blue-100">
<th class="border border-gray-400 p-1">Art Tax</th>
<th class="border border-gray-400 p-1">Art Exo</th>
<th class="border border-gray-400 p-1">Art Exc</th>
<th class="border border-gray-400 p-1">Art Exp</th>
<th class="border border-gray-400 p-1">Art TPS</th>
</tr>
</thead>
<tbody>
<tr>
<td class="border border-gray-400 p-1 text-center">1</td>
<td class="border border-gray-400 p-1 text-center font-bold">FV</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center font-bold">1</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">2</td>
<td class="border border-gray-400 p-1 text-center font-bold">FV</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center font-bold">1</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">3</td>
<td class="border border-gray-400 p-1 text-center font-bold">FV</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center font-bold">2</td>
<td class="border border-gray-400 p-1 text-center font-bold">3</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">4</td>
<td class="border border-gray-400 p-1 text-center font-bold">FV</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center font-bold">2,5</td>
<td class="border border-gray-400 p-1 text-center font-bold">3,250</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">5</td>
<td class="border border-gray-400 p-1 text-center font-bold">FV</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">✓</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center font-bold">2</td>
<td class="border border-gray-400 p-1 text-center font-bold">3</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">6</td>
<td class="border border-gray-400 p-1 text-center font-bold">FA</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">✓</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center font-bold">2</td>
<td class="border border-gray-400 p-1 text-center font-bold">3</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">7</td>
<td class="border border-gray-400 p-1 text-center font-bold">FV</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">✓</td>
<td class="border border-gray-400 p-1 text-center">✓</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center font-bold">3</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">8</td>
<td class="border border-gray-400 p-1 text-center font-bold">FV</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">✓</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center font-bold">2</td>
<td class="border border-gray-400 p-1 text-center font-bold">3</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">9</td>
<td class="border border-gray-400 p-1 text-center font-bold">FV</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">✓</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">✓</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center font-bold">2</td>
<td class="border border-gray-400 p-1 text-center font-bold">3</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">10</td>
<td class="border border-gray-400 p-1 text-center font-bold">FV</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">✓</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">✓</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center font-bold">2</td>
<td class="border border-gray-400 p-1 text-center font-bold">3</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">11</td>
<td class="border border-gray-400 p-1 text-center font-bold">FV</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">✓</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">✓</td>
<td class="border border-gray-400 p-1 text-center font-bold">2</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">12</td>
<td class="border border-gray-400 p-1 text-center font-bold">FV</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center font-bold">2</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">14</td>
<td class="border border-gray-400 p-1 text-center font-bold">FV</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center font-bold">2</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">15</td>
<td class="border border-gray-400 p-1 text-center font-bold">FV</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">✓</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center font-bold">2</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">16</td>
<td class="border border-gray-400 p-1 text-center font-bold">EV</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center font-bold">2</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">17</td>
<td class="border border-gray-400 p-1 text-center font-bold">EV</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center font-bold">2</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center font-bold">3</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">18</td>
<td class="border border-gray-400 p-1 text-center font-bold">EA</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center font-bold">2</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">19</td>
<td class="border border-gray-400 p-1 text-center font-bold">EV</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center font-bold">2</td>
<td class="border border-gray-400 p-1"></td>
</tr>
<tr>
<td class="border border-gray-400 p-1 text-center">20</td>
<td class="border border-gray-400 p-1 text-center font-bold">EA</td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1"></td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center">-</td>
<td class="border border-gray-400 p-1 text-center font-bold">2</td>
<td class="border border-gray-400 p-1"></td>
</tr>
</tbody>
</table>
</div>

<div class="mt-6 p-4 bg-yellow-100 border-l-4 border-yellow-500">
<p class="font-bold">(*) À Viser par La DGI Après les tests sur la plateforme de Vérification (Mention: Accepté ou Rejeté)</p>
<div class="mt-2 flex items-center gap-4">
<span class="font-semibold">Résultat:</span>
<label class="flex items-center"><input type="checkbox" class="mr-1"> OUI</label>
<label class="flex items-center"><input type="checkbox" class="mr-1"> NON</label>
</div>
</div>

<!-- Légende des types de factures -->
<div class="mt-6 p-4 bg-blue-50 border-2 border-blue-300">
<h4 class="font-bold mb-2">Légende:</h4>
<ul class="grid grid-cols-2 gap-2 text-sm">
<li><strong>FV</strong> = Facture de Vente</li>
<li><strong>FA</strong> = Facture d'Avoir</li>
<li><strong>EV</strong> = Facture de Vente à l'Exportation</li>
<li><strong>EA</strong> = Facture d'Avoir à l'Exportation</li>
</ul>
</div>
</div>

<!-- Section des Tests détaillés -->
<div class="p-8">
<h2 class="text-2xl font-bold mb-6 text-blue-900 text-center">DÉTAILS DES TESTS</h2>
@foreach ($testResults as $result)
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
    <div class="bg-blue-900 text-white p-3 mb-4">
        <h3 class="text-xl font-bold">{{ $result['name'] }}</h3>
    </div>
    <table class="w-full mb-4">
        <tr>
            <td class="font-bold w-1/3 p-2">Description:</td>
            <td class="p-2">{{ $result['description'] }}</td>
        </tr>
    </table>
    <div class="bg-gray-200 border-2 border-dashed border-gray-400 min-h-[30rem] flex items-center justify-center">
        <img src="{{ $result['image_path'] }}" alt="Capture d'écran pour {{ $result['name'] }}">
    </div>
</div>
@endforeach
</div>

<!-- Test 1 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #1</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de vente</td>
</tr>
<tr>
<td class="font-bold p-2">Article exonéré:</td>
<td class="p-2">Oui, quantité 1</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 2 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #2</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de vente</td>
</tr>
<tr>
<td class="font-bold p-2">Article taxable:</td>
<td class="p-2">Oui, quantité 1</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 3 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #3</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de vente</td>
</tr>
<tr>
<td class="font-bold p-2">Article exonéré:</td>
<td class="p-2">Oui, quantité 2</td>
</tr>
<tr>
<td class="font-bold p-2">Article taxable:</td>
<td class="p-2">Oui, quantité 3</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 4 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #4</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de vente</td>
</tr>
<tr>
<td class="font-bold p-2">Article exonéré:</td>
<td class="p-2">Oui, quantité 2,5</td>
</tr>
<tr>
<td class="font-bold p-2">Article taxable:</td>
<td class="p-2">Oui, quantité 3,250</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 5 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #5</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de vente</td>
</tr>
<tr>
<td class="font-bold p-2">Article exonéré:</td>
<td class="p-2">Oui, quantité 2</td>
</tr>
<tr>
<td class="font-bold p-2">Article taxable:</td>
<td class="p-2">Oui, quantité 3</td>
</tr>
<tr>
<td class="font-bold p-2">IFU et nom du client:</td>
<td class="p-2">Oui</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 6 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #6</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de remboursement</td>
</tr>
<tr>
<td class="font-bold p-2">Article exonéré:</td>
<td class="p-2">Oui, quantité 2</td>
</tr>
<tr>
<td class="font-bold p-2">Article taxable:</td>
<td class="p-2">Oui, quantité 3</td>
</tr>
<tr>
<td class="font-bold p-2">IFU et nom du client:</td>
<td class="p-2">Oui</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 7 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #7</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de vente</td>
</tr>
<tr>
<td class="font-bold p-2">Article taxable:</td>
<td class="p-2">Oui, quantité 3, avec taxe spécifique</td>
</tr>
<tr>
<td class="font-bold p-2">IFU et nom du client:</td>
<td class="p-2">Oui</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 8 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #8</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de vente</td>
</tr>
<tr>
<td class="font-bold p-2">Article exonéré:</td>
<td class="p-2">Oui, quantité 2</td>
</tr>
<tr>
<td class="font-bold p-2">Article taxable:</td>
<td class="p-2">Oui, quantité 3</td>
</tr>
<tr>
<td class="font-bold p-2">AIB:</td>
<td class="p-2">AIB 5%</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 9 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #9</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de vente</td>
</tr>
<tr>
<td class="font-bold p-2">Article exonéré:</td>
<td class="p-2">Oui, quantité 2</td>
</tr>
<tr>
<td class="font-bold p-2">Article taxable:</td>
<td class="p-2">Oui, quantité 3</td>
</tr>
<tr>
<td class="font-bold p-2">IFU et nom du client:</td>
<td class="p-2">Oui</td>
</tr>
<tr>
<td class="font-bold p-2">AIB:</td>
<td class="p-2">AIB 1%</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 10 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #10</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de vente</td>
</tr>
<tr>
<td class="font-bold p-2">Article exonéré:</td>
<td class="p-2">Oui, quantité 2</td>
</tr>
<tr>
<td class="font-bold p-2">Article taxable:</td>
<td class="p-2">Oui, quantité 3, taxe spécifique</td>
</tr>
<tr>
<td class="font-bold p-2">AIB:</td>
<td class="p-2">AIB 5%</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 11 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #11</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de vente</td>
</tr>
<tr>
<td class="font-bold p-2">Article taxable:</td>
<td class="p-2">Oui, quantité 2</td>
</tr>
<tr>
<td class="font-bold p-2">IFU et nom du client:</td>
<td class="p-2">Oui</td>
</tr>
<tr>
<td class="font-bold p-2">TAXE DE SEJOUR:</td>
<td class="p-2">Oui</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 12 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #12</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de vente</td>
</tr>
<tr>
<td class="font-bold p-2">Article régime d'exception:</td>
<td class="p-2">Oui, quantité 2</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 13 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #13</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de vente</td>
</tr>
<tr>
<td class="font-bold p-2">Article régime d'exception:</td>
<td class="p-2">Oui, quantité 2, avec taxe spécifique</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 14 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #14</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de vente</td>
</tr>
<tr>
<td class="font-bold p-2">Article régime fiscal TPS:</td>
<td class="p-2">Oui, quantité 2</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 15 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #15</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de vente</td>
</tr>
<tr>
<td class="font-bold p-2">Article régime fiscal TPS:</td>
<td class="p-2">Oui, quantité 2, avec taxe spécifique</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 16 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #16</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de vente à l'exportation</td>
</tr>
<tr>
<td class="font-bold p-2">Article exportation de produits taxables:</td>
<td class="p-2">Oui, quantité 2</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 17 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #17</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de vente à l'exportation</td>
</tr>
<tr>
<td class="font-bold p-2">Article exonéré:</td>
<td class="p-2">Oui, quantité 2</td>
</tr>
<tr>
<td class="font-bold p-2">Article exportation de produits taxables:</td>
<td class="p-2">Oui, quantité 3</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 18 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #18</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture d'avoir à l'exportation</td>
</tr>
<tr>
<td class="font-bold p-2">Article Exportation de produits taxables:</td>
<td class="p-2">Oui, quantité 2</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 19 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #19</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture de vente à l'exportation</td>
</tr>
<tr>
<td class="font-bold p-2">Article régime fiscal TPS:</td>
<td class="p-2">Oui, quantité 2</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>

<!-- Test 20 -->
<div class="border-4 border-gray-300 p-6 mb-8 page-break">
<div class="bg-blue-900 text-white p-3 mb-4">
<h3 class="text-xl font-bold">Test #20</h3>
</div>
<table class="w-full mb-4">
<tr>
<td class="font-bold w-1/3 p-2">Type:</td>
<td class="p-2">Facture d'avoir à l'exportation</td>
</tr>
<tr>
<td class="font-bold p-2">Article régime fiscal TPS:</td>
<td class="p-2">Oui, quantité 2</td>
</tr>
</table>
<div class="bg-gray-200 border-2 border-dashed border-gray-400 h-96 flex items-center justify-center">
<p class="text-gray-600 text-lg font-semibold">INSÉRER LA PHOTO DE LA FACTURE SUR CETTE PAGE</p>
</div>
</div>
</div>

<!-- Footer -->
<div class="bg-blue-900 text-white p-6 text-center mt-8">
<p class="font-bold">Direction Générale des Impôts - République du Bénin</p>
<p class="mt-2">e-MECeF - Mécanisme Electronique de Certification de Facture</p>
<p class="mt-2 text-sm">Contact: <span class="bg-yellow-200 text-blue-900 px-2 py-1 rounded">emecefbenin@finances.bj</span></p>
</div>
</div>

<script>
// Script pour impression
function printDocument() {
window.print();
}
</script>
</body>
</html> 
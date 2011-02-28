<?php

  //=== C O N F I G U R A T I O N   D A T A ====================================

  // the name can be used in the head title tag and for reporting
  $www_title            = 'Markmann FeWo Datenbank';

  // the stylesheet to use if template has no own stylesheet
  $www_default_css      = XOX_WWW_BASE.'/inc/default.css';

  // templates
  $www_template_mask    = 'de/templates/*.html';
  $www_default_template = 'default';
	// javascript editor
  $html_editor = "
<script type=\"text/javascript\"><!--
   _editor_url = '".XOX_WWW_BASE."/xox/bin/editor/htmlarea/';
   _editor_lang = 'de';
// -->
</script>
<script type=\"text/javascript\" src=\"xox/bin/editor/htmlarea/htmlarea.js\"></script>
<script type=\"text/javascript\"><!--
   var editor = null;
   window.onload = function() {

      editor1 = new HTMLArea('htmlarea');

      var cfg = editor1.config;
      cfg.width = '592px';
      cfg.height = '300px';
      cfg.pageStyle = 'body { background-color: #fffeee; color: black; font-family: Verdana,Arial,helvetica,sans-serif }';

      /*cfg.registerDropdown({
         id: 'nltags',
         options: {
            '{...}' : '',
            'Max.Personen' : 'USEREMAIL',
            'Anrede': 'USERSALUT',
            'Vorname': 'USERFNAME',
            'Nachname' : 'USERLNAME',
            'Name' : 'USERNAME',
            'Firma' : 'USERCOMPANY',
            'Strasse' : 'USERSTREET',
            'Land' : 'USERCOUNTRY',
            'PLZ' : 'USERZIP',
            'Ort' : 'USERCITY',
            'Telefon' : 'USERTEL',
            'Fax' : 'USERFAX',
            'Mobil' : 'USERMOB',
            'Domäne' : 'DOMAINID',
            'UserID' : 'USERID',
            'Version' : 'ISSUEID'
         },
         action: function(editor) {
            var tag = editor._toolbarObjects['nltags'].element.value;
            if ( tag != '' ) editor.insertHTML('{'+tag+'}');
         },
         refresh: function(editor) {
            var select = editor._toolbarObjects['nltags'].element;
            var html = editor.getSelectedHTML();
            if (typeof html != 'undefined' && /\S/.test(html)) {
               var options = select.options;
               var value = html;
               for (var i = options.length; --i >= 0;) {
                  var option = options[i];
                  if (value == option.value) {
                     select.selectedIndex = i;
                     return;
                  }
               }
            }
            select.selectedIndex = 0;
         },
         context: ''
      });*/

      cfg.toolbar = [
      [ /*'nltags', 'separator',
         'inserthorizontalrule', 'createlink', 'insertimage', 'inserttable', 'htmlmode', 'separator',*/
				'fontname', 'fontsize', 'separator',
        'bold', 'italic', 'underline', /*'strikethrough',*/ 'separator',
        'justifyleft', 'justifycenter', 'justifyright', 'justifyfull', 'separator',
				'insertorderedlist', 'insertunorderedlist', 'outdent', 'indent', 'separator',
         /*'subscript', 'superscript', 'separator', */
        'forecolor', 'hilitecolor', 'separator',
         'inserthorizontalrule', 'separator',
         'popupeditor'/*, 'separator',
         'showhelp'*/
      ],
      /*[ 'formatblock','fontname', 'fontsize', 'separator',
        'bold', 'italic', 'underline', 'strikethrough', 'separator',
        'justifyleft', 'justifycenter', 'justifyright', 'justifyfull'
      ]*/];
      editor1.generate();
      if ( document.getElementById('htmlarea2') ) {
         editor2 = new HTMLArea('htmlarea2');
         editor2.config = editor1.config;
         editor2.generate();
      }
      return false;
   };
// -->
</script>
      ";

  // site navigation
  $www_navigation = array(

    new nav_entry('home','Home','Startseite des Admin-Bereiches von FeWo',array(
      new nav_entry('willkommen','','Aktuelle News und Informationen','~xox/bin/mod.startpage.php'),
      new nav_entry('kontakt','','Kontakt','home/kontakt.html'),
      new nav_entry('sitemap','','Sitemap','home/sitemap.html'),
      new nav_entry('hilfe','','Hilfe','home/hilfe.html'),
      new nav_entry('login','','Login','~xox/bin/mod.login.php'),
      new nav_entry('agb','','Allgemeine Nutzungsbedingungen','home/nutzungsbedingungen.html'),
      new nav_entry('datenschutz','','Datenschutzerklärung','home/datenschutz.html'),
      new nav_entry('impressum','','Impressum','home/impressum.html'),
    )),

    new nav_entry('address','Kunden','kundendaten',array(
    	new nav_entry('kunden','Kundenverwaltung','Hier können die Daten der Kunden bearbeitet werden',array(
			new nav_entry('suchen','Suchen','Gezielt nach Kunden suchen','kunden/search.php',O_ADMIN),
			new nav_entry('auswahlsuchen','','','kunden/search_selection.php',O_ADMIN),
			new nav_entry('bearbeiten','Bearbeiten','Kundendaten bearbeiten','kunden/bearbeiten.php',O_ADMIN),
			new nav_entry('Status ändern','Status ändern','Hier können Sie den Kundenstatus ändern','kunden/treaty_change.php',O_ADMIN),
      	new nav_entry('importieren','Importieren','Daten aus externen Quellen einlesen','kunden/import.php',O_ADMIN),
      	new nav_entry('exportieren','Exportieren','Kundendaten in Datei exportieren','kunden/export.php',O_ADMIN),
			new nav_entry('test','','Nur zu Testzwecken','kunden/export_popup.php',O_ADMIN,'','blank'),
			new nav_entry('saveexport','','','~/fewo/export_save.php',O_ADMIN),
			),O_ADMIN),
    	new nav_entry('webseite','Webseiten','Erstellen/Löschen von Kundensites',array(
				new nav_entry('demomaker','Demo erstellen','Erstellt eine Website für den Kunden','kunden/demo_create.php',O_ADMIN),
				new nav_entry('loeschen','Demo entfernen','Löscht eine Website des Kunden','kunden/demo_delete.php',O_ADMIN),
			),O_ADMIN),
    	new nav_entry('buchungen','Buchungen','objektdaten',array(
				new nav_entry('ueberweisungen','Überweisungen','Übersicht über Kontobewegungen','kunden/ueberweisungen.php',O_ADMIN),
			),O_ADMIN),

		),O_ADMIN),

    new nav_entry('address','Verwaltung','Informationen zu Objekten',array(
    	new nav_entry('kontakt','Kontaktdaten','Wer kann wie erreicht werden',array(
				new nav_entry('eigene','Eigene','Wie kann der Vermieter erreicht werden','verwaltung/kontaktdaten_eigene.php',O_CLIENT),
				new nav_entry('schluesselbesitzer','Schlüsselbesitzer','Wie kann der Schlüsselverwalter erreicht werden','verwaltung/kontaktdaten_keykeeper.php',O_KEYKEEPER),
			),O_KEYKEEPER),
    	new nav_entry('verwaltung','Objektverwaltung','Objektinformationen ändern',array(
				new nav_entry('auswahl','Auswahl','Welches Objekt soll bearbeitet werden ?','verwaltung/objektverwaltung_auswahl.php',O_CLIENT),
				new nav_entry('details','Details','Detailinformationen des Objektes bearbeiten','verwaltung/objektverwaltung_details.php',O_CLIENT,'','',$html_editor),
				new nav_entry('vertrag','Vertrag','Vertragliche Details bearbeiten und Tarife verwalten','verwaltung/objektverwaltung_tarife.php',O_CLIENT),
				new nav_entry('kriterien','Kriterien','Welche Kriterien treffen auf das Objekt zu','verwaltung/objektverwaltung_kriterien.php',O_CLIENT),
			),O_CLIENT),
    	new nav_entry('buchungen','Buchungen','objektdaten',array(
				new nav_entry('uebersicht','Buchungsuebersicht','Übersicht über die Buchungssituation','verwaltung/buchungsuebersicht.html',O_ASSISTANT),
				new nav_entry('eigenbelegung','Eigenbelegung','Eigenbelegung des Objektes durch den kunden','verwaltung/eigenbelegung.html',O_ASSISTANT),
			),O_ASSISTANT),
		),O_KEYKEEPER),

    new nav_entry('forms','Website','Homepagegestaltung',array(
			new nav_entry('homepage','Homepage','Webseiten Auswahl',array(
				new nav_entry('auswahl','Auswahl','Webseiten Auswahl','verwaltung/offen.html',O_BROWSE),
				new nav_entry('neu','Beantragen','Neue Webseite beantragen','verwaltung/offen.html',O_BROWSE),
				new nav_entry('loeschen','Löschen','Vorhandene Webseite löschen','verwaltung/offen.html',O_BROWSE),
			),O_BROWSE),
			new nav_entry('layout','Layout','Webseite gestalten',array(
				new nav_entry('vorlage','Designvorlage','Wählen Sie das Grundlayout Ihrer Webseite','verwaltung/offen.html',O_BROWSE),
				new nav_entry('style','Schriften und Farben','Ändern Sie Schriften und Farben','verwaltung/offen.html',O_BROWSE),
			),O_BROWSE),
			new nav_entry('inhalte','Inhalte','Inhalte',array(
				new nav_entry('navigation','Navigation','Gestalten Sie die Navigation Ihrer Webseite','verwaltung/offen.html',O_BROWSE),
				new nav_entry('texte','Textinhalte','Bearbeiten Sie die Textinhalte','verwaltung/offen.html',O_BROWSE),
				new nav_entry('objekte','Objektauswahl','Wählen Sie die Ferienwohnungsangebote für Ihre Website','verwaltung/offen.html',O_BROWSE),
			),O_BROWSE),
			new nav_entry('tools','Tools','Tools zum Bearbeiten Ihrer Webseite',array(
				new nav_entry('bilder','Bilderverwaltung','Laden Sie Bilder für Ihre Webseite hoch','verwaltung/offen.html',O_BROWSE),
				new nav_entry('dokumente','Dokumentverwaltung','Laden Sie Dokumente für Ihre Webseite hoch','verwaltung/offen.html',O_BROWSE),
				new nav_entry('websuche','Suchoptimierung','Optimieren Sie Ihre Webseite für Websuchseiten','verwaltung/offen.html',O_BROWSE),
			),O_BROWSE),
		),O_BROWSE),

    new nav_entry('service','Newsletter','Unser Kundenservice',array(
 	  	new nav_entry('newsletter','Newsletter','Hier können Sie Newsletter anlegen und bearbeiten',array(
				/*new nav_entry('newsletter','','','newsletter/newsletter/newsletter.php'),
				new nav_entry('newsletter','','Newsletter anlegen','newsletter/newsletter/nlneu.php'),
				new nav_entry('newsletter','','Newsletterdetail','newsletter/staticHTML/nldetail.html'),
				new nav_entry('newsletter','','Newsletter bearbeiten','newsletter/newsletter/nlbearbeiten.php'),//'staticHTML/nlbearbeiten.html'),
				new nav_entry('newsletter','','Neuen Version erstellen','newsletter/newsletter/versionneu.php',0,'','editor'),
				new nav_entry('newsletter','','Version bearbeiten','newsletter/newsletter/versionbearbeiten.php',0,'','editor'),
				new nav_entry('newsletter','','Versiondetail','newsletter/newsletter/versiondetail.php'),
				new nav_entry('newsletter','','Neuen Topic erstellen','newsletter/newsletter/topiceintragen.php',0,'','editor'),
				new nav_entry('newsletter','','Neuen Content erstellen','newsletter/newsletter/contenteintragen.php',0,'','editor'),
				  */
			new nav_entry('overview','Übersicht','Newsletterübersicht','newsletter/newsletter/newsletter.php'),
			new nav_entry('new','Newsletter erstellen','Einen neuen Newsletter erstellen','newsletter/newsletter/nlneu.php'),  //4.0.1
			new nav_entry('issues','','Ausgaben Übersicht','newsletter/newsletter/nldetail.php'),                                 //4.0.2
			new nav_entry('edit','','Newsletter bearbeiten','newsletter/newsletter/nlbearbeiten.php'),                          //4.0.3
			new nav_entry('new_issue','','Neue Ausgabe erstellen','newsletter/newsletter/versionneu.php',0,'','',$html_editor),       //4.0.4
			new nav_entry('issue_content','','Beiträge Übersicht','newsletter/newsletter/versiondetail.php'),                             //4.0.5
			new nav_entry('edit_issue','','Ausgabe bearbeiten','newsletter/newsletter/versionbearbeiten.php',0,'','',$html_editor),    //4.0.6
			new nav_entry('category','','Neue Kategorie erstellen','newsletter/newsletter/topiceintragen.php',0,'','',$html_editor), //4.0.7
			new nav_entry('edit_content','','Beitrag','newsletter/newsletter/contenteintragen.php',0,'','',$html_editor),                //4.0.8


    	)),
    	new nav_entry('forms','Formulare','Formulare',array(
    		new nav_entry('formulare','Formulare','Formulare','newsletter/formulare/formulare.php'),
    		new nav_entry('formulare','Formular erstellen','Formulare','newsletter/formulare/eintragen.php',0,'','editor'),
    	)),
    	new nav_entry('address','Adressen','Adressen',array(
    	    new nav_entry('adressen','Adressen&uuml;bersicht','Adressen','newsletter/adressen/adressen.php'),
    	      new nav_entry('adressen','Neue Adresse eintragen','Adressen','newsletter/adressen/eintragen.php'),
			)),
    	new nav_entry('database','Datenbank','Datenbank',array(
    	  new nav_entry('index','','','newsletter/datenbank/index.html'),
    	  new nav_entry('blacklist','Blacklist','Liste der nicht zulässigen E-Mail-Adressen bearbeiten','newsletter/datenbank/blacklist.php'),
    	  new nav_entry('blacklist','','Blacklist','newsletter/datenbank/bleintragen.php'),
    	  new nav_entry('blacklist','','Blacklist','newsletter/datenbank/userblocklist.php'),
    	  new nav_entry('import','Import','Neue Adressen im CSV-Format in die Datenbank importieren','newsletter/datenbank/import.html'),
    	  new nav_entry('export','Export','Adressen im CSV-Format aus der Datenbank exportieren','newsletter/datenbank/export.html'),
    	)),
			new nav_entry('help','','Hilfe',array(
    	  new nav_entry('hilfe0','','Index','dummy.html'),
    	  new nav_entry('hilfe1','Newsletter erstellen','Dummy','newsletter/hilfe/nlerstellen.php'),
    	  new nav_entry('hilfe2','Newsletter verwalten','Dummy','newsletter/hilfe/nlverwalten.php'),
    	  new nav_entry('hilfe3','Statistikfunktionen','Dummy','newsletter/hilfe/nlstatistik.php'),
    	  new nav_entry('hilfe4','Adressen erstellen','Dummy','newsletter/hilfe/adresserstellen.php'),
    	  new nav_entry('hilfe5','Adressen verwalten','Dummy','newsletter/hilfe/adressverwalten.php'),
    	  new nav_entry('hilfe6','Blacklisten','Dummy','newsletter/hilfe/blacklist.php'),
    	  new nav_entry('hilfe7','An-/Abmeldeformulare','Dummy','newsletter/hilfe/formular.php'),
    	)),
			new nav_entry('flow','','',array(
    			new nav_entry('flow0','Löschbestättigung','','newsletter/staticHTML/delete.php'),
    	)),
		),O_BROWSE),

    new nav_entry('database','Setup','Einstellungen',array(
    	new nav_entry('setupidx','','Auswahl','setup/index.html',O_ADMIN),
    	new nav_entry('settings','Einstellungen','Globale Einstellungen vornehmen','setup/settings.php',O_ADMIN),
    	new nav_entry('backup','Datensicherung','Datenbank Sichern','setup/backup.php',O_ADMIN),
    	new nav_entry('test','Test','Test','~fewo/test.inc.classes.php',O_ADMIN),
    ),O_ADMIN),

  );

?>

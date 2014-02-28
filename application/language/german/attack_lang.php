<?php
$lang['attack_plan'] = 'Angriffsplan';
$lang['attack_name'] = 'Angriffsplaner';

$lang['attack_back_to_overview'] = 'Zurück zur Übersicht';

$lang['attack_own_plans'] = 'Meine Angriffspläne';
$lang['attack_create_plan'] = 'Angriffsplan erstellen';

$lang['attack_id'] = 'ID';
$lang['attack_desc'] = 'Beschreibung';
$lang['attack_created'] = 'Erstellt am';

$lang['attack_tab_overview'] = 'Übersicht';
$lang['attack_tab_add'] = 'Aktion hinzufügen';
$lang['attack_tab_wizard'] = 'Automatisch Aktionen hinzufügen';
$lang['attack_tab_map'] = 'Kartenansicht';

$lang['attack_type'] = 'Typ';
$lang['attack_type_attack'] = 'Angriff';
$lang['attack_type_fake'] = 'Fake';
$lang['attack_type_snob'] = 'Eroberung';
$lang['attack_type_def'] = 'Verteidigung';

$lang['attack_units'] = 'Einheiten';
$lang['attack_units_note'] = 'Hinweis: Du musst keine genauen Einheiten eingeben. Es 
    reicht in das Feld der langsamsten Einheit eine 1 zu schreiben!';
$lang['attack_arrival'] = 'Ankunftszeit';
$lang['attack_start_time'] = 'Abschickzeit';
$lang['attack_village_owner'] = 'Besitzer';
$lang['attack_send_in'] = 'Abschicken in';

$lang['attack_unit_speed'] = 'Zeit pro Feld';
$lang['attack_unit_speed_unit'] = 'min/Feld';

$lang['attack_start_vill'] = 'Startdorf';
$lang['attack_target_vill'] = 'Zieldorf';

$lang['attack_note'] = 'Notiz';

$lang['attack_vill_left'] = 'verlassen';

$lang['attack_wizard_line_error'] = 'Die Zeile %s enthält ein ungültiges Dorf-Format!';
$lang['attack_wizard_village_error'] = 'Das Dorf %s konnte nicht gefunden werden.';
$lang['attack_wizard_village_none'] = 'Keine Dörfer eingegeben!';

$lang['attack_wizard_info'] = 'Die "Automatisch Aktionen hinzufügen" Funktion versucht 
    automatisch aus gegebenen Zielen und Startdörfern einen vollständigen Angriffsplan mit 
    Fakes, Offs, Defs und Adelungen zu erstellen. WICHTIG: Bitte wärend dem gesammten Prozess 
    die Seite nicht neu laden!';

$lang['attack_wizard_troop_config'] = 'Truppen-Konfiguration für ';
$lang['attack_wizard_troop_config_O'] = 'Off';
$lang['attack_wizard_troop_config_S'] = 'Adelung';
$lang['attack_wizard_troop_config_F'] = 'Fake';

$lang['attack_wizard_per_target'] = 'Pro %s-Zieldorf';

$lang['attack_wizard_next'] = 'Weiter';

$lang['attack_wizard_step1'] = 'Schritt 1: Zuerst musst du die Zieldörfer festlegen. Gib dafür einfach 
    eine Liste der Zieldörfer in folgendes Textfeld ein. Pro Zeile sollte nur ein Zieldorf stehen. 
    Das Format für eine Zeile <u>muss</u> wie folgt ausehen: <b>xxx|yyy,t</b> - xxx stellt hierbei die 
    X-Koordinate dar, yyy die Y-Koordinate. Das t gibt an, ob ein Dorf nur gefaked, geofft oder geadelt 
    werden soll. Wenn das dorf gefaked werden soll, dann schreib ein F. Soll es geofft werden, schreib 
    ein O. Soll es geadelt werden, schreib ein S. Beispiel: <b>500|501,F</b> würde heißen, dass das Dorf 
    mit den Koordinaten 500|501 gefaked werden soll. ACHTUNG: S (adeln) bedeutet immer: Offen und Adeln';

$lang['attack_wizard_step2'] = 'Schritt 2: Erstelle nun bei Die-Stämme eine Dörfergruppe, die 
    alle Dörfer beinhaltet die an der Aktion teilnehmen sollen. Klicke dann auf Übersichten &gt; Kombiniert und  
    wähle diese Gruppe aus. Nun alles markieren (Tastenkombination: [STRG]+[A]), kopieren (Tastenkombi: [STRG]+[C]) 
    und dann in das unten stehende Textfeld einfügen (Tastenkombi: [STRG]+[V]).';

$lang['attack_wizard_step3'] = 'Schritt 3: Definiere nun die Truppen-Konfiguration für deine Aktionen.';
<?php
/**
 * Built-in German translations of the demo trips and places and of the spa services,
 * keyed by their Arabic title / name. Merged into seed_content_translations().
 */
$oils = '100 % natürliche Öle';

return [
    'trips' => [
        'رحلة جزيرة أورانج باي' => [
            'title' => 'Ausflug zur Insel Orange Bay',
            'short_description' => 'Ein ganzer Tag an den schönsten Stränden der Insel Giftun: weißer Sand, türkisblaues Wasser und Schnorcheln an Korallenriffen.',
            'description' => "Mit einer komfortablen Yacht geht es von der Marina Hurghada nach Orange Bay, einer der bekanntesten Inseln im Roten Meer mit weißem Strand und kristallklarem Wasser.\n\nUnterwegs halten wir an zwei Schnorchelplätzen, um mit erfahrenen Guides Korallen und bunte Fische zu sehen. Danach hast du freie Zeit am Strand zum Entspannen, Schwimmen und Fotografieren. An Bord gibt es ein frisches Mittagessen.",
            'duration' => '8 Stunden (ganzer Tag)', 'location' => 'Insel Giftun', 'pickup_time' => '8:00 Uhr am Hotel', 'group_size' => 'Bis zu 40 Personen',
            'includes' => "Transfer vom und zum Hotel\nYachtfahrt hin und zurück\nSchnorchelausrüstung\nMittagessen und Getränke\nReiseleiter\nEintritt zur Insel",
            'excludes' => "Unterwasserfotos\nZusätzliche Wassersportarten (Parasailing, Bananenboot)",
            'itinerary' => "08:00 - Abholung am Hotel\n09:00 - Abfahrt von der Marina Hurghada\n10:30 - Erster Schnorchelstopp\n12:00 - Ankunft auf der Insel Orange Bay\n14:00 - Mittagessen an Bord\n15:00 - Zweiter Schnorchelstopp\n17:00 - Rückkehr zur Marina",
        ],
        'سفاري سوبر بالموتوسيكلات الرباعية' => [
            'title' => 'Super-Safari mit dem Quad',
            'short_description' => 'Abenteuer in der Östlichen Wüste: Quadfahren, Kamelreiten, Besuch eines Beduinendorfs und Abendessen mit Folkloreshow.',
            'description' => "Wüste pur! Zuerst fahren wir mit dem Quad durch Dünen und Berge, dann besuchen wir ein echtes Beduinendorf, lernen das Leben und die Traditionen der Beduinen kennen und trinken Beduinentee.\n\nDanach folgen ein Kamelritt und der Sonnenuntergang über den Bergen. Zum Abschluss gibt es ein Grill-Abendessen mit Tanoura- und Bauchtanzshow unter dem Sternenhimmel.",
            'duration' => '5 Stunden', 'location' => 'Östliche Wüste', 'pickup_time' => '14:00 Uhr am Hotel', 'group_size' => 'Kleine Gruppen',
            'includes' => "Transfer im klimatisierten Fahrzeug\nEine Stunde Quadfahren\nKamelritt\nBesuch des Beduinendorfs\nGrill-Buffet zum Abendessen\nTanoura- und Folkloreshow",
            'excludes' => "Zusätzliche Softdrinks\nTrinkgeld",
            'itinerary' => "14:00 - Abholung am Hotel\n15:00 - Sicherheitseinweisung und Start der Quadtour\n16:30 - Ankunft im Beduinendorf\n17:15 - Kamelritt und Sonnenuntergang\n18:30 - Abendessen und Folkloreshow\n20:00 - Rückkehr zum Hotel",
        ],
        'رحلة بيت الدلافين' => [
            'title' => 'Ausflug zum Delfinhaus',
            'short_description' => 'Fahrt zum Riff „Delfinhaus“ mit der Chance, in der Nähe von Delfinen in ihrem natürlichen Lebensraum zu schwimmen.',
            'description' => "Ein schöner Bootsausflug für die ganze Familie zum bekannten Riff Abu Ramada, wo Delfine leben. Wir fahren ruhig, beobachten die Delfine und können bei passenden Bedingungen in ihrer Nähe schwimmen.\n\nDazu gehören zwei Schnorchelstopps an beeindruckenden Korallenriffen und ein Mittagessen auf der Yacht.",
            'duration' => '7 Stunden', 'location' => 'Riff Abu Ramada', 'pickup_time' => '8:30 Uhr', 'group_size' => 'Bis zu 35 Personen',
            'includes' => "Transfer vom und zum Hotel\nYachtfahrt\nSchnorchelausrüstung\nMittagessen und Getränke\nMeeres-Guide",
            'excludes' => "Hafen-Umweltgebühren (falls erhoben)",
            'itinerary' => "08:30 - Abholung am Hotel\n09:30 - Abfahrt\n11:00 - Suche nach Delfinen\n12:30 - Schnorcheln am Riff\n13:30 - Mittagessen\n15:30 - Rückfahrt",
        ],
        'غوص للمبتدئين (تجربة الغوص الأولى)' => [
            'title' => 'Schnuppertauchen für Anfänger',
            'short_description' => 'Noch nie getaucht? Probiere es zum ersten Mal mit zertifizierten Tauchlehrern in einem der besten Tauchgebiete der Welt.',
            'description' => "Das ideale Programm für alle, die ohne Vorkenntnisse zum ersten Mal tauchen möchten. Nach einer einfachen Theorieeinweisung und Übungen im flachen Wasser folgen zwei Tauchgänge mit persönlichem Tauchlehrer in sicherer Tiefe, um die Riffe und das Meeresleben zu entdecken.",
            'duration' => '7 Stunden', 'location' => 'Insel Giftun', 'pickup_time' => '8:00 Uhr', 'group_size' => '1 Tauchlehrer pro 2 Taucher',
            'includes' => "Transfer vom und zum Hotel\nKomplette Tauchausrüstung\nZertifizierter Tauchlehrer\nZwei Tauchgänge\nMittagessen und Getränke",
            'excludes' => "Unterwasserfotos und -videos\nInternationales Tauchzertifikat",
            'itinerary' => "08:00 - Abholung am Hotel\n09:00 - Theorie und Vorbereitung der Ausrüstung\n10:30 - Erster Tauchgang\n12:30 - Mittagessen und Pause\n14:00 - Zweiter Tauchgang\n16:00 - Rückfahrt",
        ],
        'رحلة الأقصر ليوم واحد' => [
            'title' => 'Tagesausflug nach Luxor',
            'short_description' => 'Das größte Freilichtmuseum der Welt: Karnak-Tempel, Tal der Könige, Hatschepsut-Tempel und die Memnonkolosse.',
            'description' => "Ein historischer Tagesausflug nach Luxor mit einem Ägyptologen als Reiseleiter. Wir besuchen den Karnak-Tempel am Ostufer und setzen dann zum Westufer über – zum Tal der Könige, zum Tempel der Königin Hatschepsut und zu den Memnonkolossen. Mittagessen in einem Restaurant am Nil.",
            'duration' => '16 Stunden', 'location' => 'Luxor', 'pickup_time' => '4:30 Uhr', 'group_size' => 'Bis zu 20 Personen',
            'includes' => "Klimatisierter Reisebus\nÄgyptologe als Reiseleiter\nEintrittsgelder\nMittagessen im Restaurant\nMineralwasser",
            'excludes' => "Tickets für Sondergräber (Tutanchamun)\nHeißluftballonfahrt\nTrinkgeld",
            'itinerary' => "04:30 - Abfahrt in Hurghada\n09:00 - Karnak-Tempel\n11:30 - Überfahrt zum Westufer\n12:00 - Tal der Könige\n14:00 - Hatschepsut-Tempel und Memnonkolosse\n15:30 - Mittagessen\n16:30 - Rückfahrt nach Hurghada",
        ],
        'الغواصة السياحية (سيمي سب)' => [
            'title' => 'Halb-U-Boot-Tour',
            'short_description' => 'Die Unterwasserwelt des Roten Meeres durch Glasfenster erleben, ohne nass zu werden – ideal für Kinder und Senioren.',
            'description' => "Ein schöner Familienausflug mit einem halb getauchten Glasboot: Durch Panoramafenster siehst du Korallenriffe und tropische Fische. Wer möchte, kann bei einem Stopp schnorcheln.",
            'duration' => '3 Stunden', 'location' => 'Hurghada', 'pickup_time' => 'Mehrere Abfahrten täglich', 'group_size' => 'Für alle Altersgruppen',
            'includes' => "Transfer vom und zum Hotel\nFahrt mit dem Halb-U-Boot\nSchnorchelausrüstung\nEin Getränk",
            'excludes' => "Mahlzeiten",
            'itinerary' => "Abholung am Hotel\nFahrt zum Riff\nBeobachtung der Unterwasserwelt (45 Minuten)\nSchnorchelstopp\nRückfahrt",
        ],
        'جولة الغردقة ليلاً' => [
            'title' => 'Hurghada bei Nacht',
            'short_description' => 'Entdecke Hurghada: die Marina, die El-Mina-Moschee, die koptische Kathedrale und den traditionellen Markt in Dahar.',
            'description' => "Eine abendliche Tour zu den wichtigsten Sehenswürdigkeiten Hurghadas: Wir beginnen an der großen El-Mina-Moschee, besuchen die bekannte koptische Kathedrale, bummeln über den traditionellen Markt in Dahar mit Gewürzen und Souvenirs und schließen mit einem Spaziergang durch die Marina Hurghada zwischen Restaurants und Cafés.",
            'duration' => '4 Stunden', 'location' => 'Stadtzentrum Hurghada', 'pickup_time' => '17:00 Uhr', 'group_size' => 'Kleine Gruppen',
            'includes' => "Transfer im klimatisierten Fahrzeug\nReiseleiter\nEin Getränk in der Marina",
            'excludes' => "Persönliche Einkäufe\nAbendessen",
            'itinerary' => "17:00 - Abholung am Hotel\n17:30 - Große El-Mina-Moschee\n18:15 - Koptische Kathedrale\n19:00 - Markt in Dahar\n20:00 - Marina Hurghada\n21:00 - Rückfahrt",
        ],
        'جزيرة الجنة (باراديس)' => [
            'title' => 'Paradise Island',
            'short_description' => 'Ein entspannter Ausflug zur Paradise Island: ruhiger Strand, kristallklares Wasser und Schnorcheln.',
            'description' => "Genieße einen ruhigen Tag auf der Paradiesinsel mit Sandstränden und klarem Wasser. Dazu gehören ein Schnorchelstopp, freie Zeit auf der Insel und ein Mittagessen auf der Yacht.",
            'duration' => '7 Stunden', 'location' => 'Inseln vor Hurghada', 'pickup_time' => '8:30 Uhr', 'group_size' => 'Bis zu 40 Personen',
            'includes' => "Transfer vom und zum Hotel\nYachtfahrt\nSchnorchelausrüstung\nMittagessen und Getränke\nInselgebühr",
            'excludes' => "Zusätzliche Wassersportarten",
            'itinerary' => "08:30 - Abholung am Hotel\n09:30 - Abfahrt\n11:00 - Schnorcheln\n12:00 - Insel und freie Zeit\n14:00 - Mittagessen\n16:00 - Rückfahrt",
        ],

        // spa services
        'برنامج الحمام التركي' => [
            'title' => 'Türkisches Bad – Programm',
            'short_description' => 'Zweieinhalb Stunden Entspannung: Sauna, Dampfbad, traditionelles türkisches Bad mit Peeling und Schaummassage, Whirlpool und eine 50-minütige Ganzkörpermassage.',
            'description' => "Ein komplettes Programm, das mit Sauna und Dampfbad beginnt. Danach folgt das traditionelle türkische Bad: Du entspannst auf dem heißen Marmor, der Feuchtigkeit aus Haut und Gelenken zieht. Anschließend übergießt dich der Bademeister mit warmem Wasser und peelt die Haut mit dem türkischen Peeling, um abgestorbene Hautschüppchen zu entfernen.\n\nEs folgen eine Schaummassage (Seife) am ganzen Körper auf dem heißen Marmor, dann der Whirlpool, und zum Abschluss eine 50-minütige Ganzkörpermassage.",
            'duration' => '2,5 Stunden',
            'includes' => "Sauna\nDampfbad\nTürkisches Bad auf dem heißen Marmor\nTürkisches Peeling\nSchaummassage (Seife)\nWhirlpool\n50 Minuten Ganzkörpermassage",
            'itinerary' => "Sauna\nDampfbad\nEntspannen auf dem heißen Marmor, der Feuchtigkeit aus Haut und Gelenken zieht\nWarmes Wasser, dann türkisches Peeling gegen abgestorbene Hautschüppchen\nSchaummassage (Seife) am ganzen Körper auf dem heißen Marmor\nWhirlpool\n50 Minuten Ganzkörpermassage",
        ],
        'مساج الظهر' => [
            'title' => 'Rückenmassage', 'duration' => '30 Minuten', 'includes' => $oils,
            'short_description' => 'Eine gezielte Massage für Rücken und Schultern, die Muskelverspannungen und Müdigkeit löst.',
            'description' => 'Eine kurze Anwendung für Rücken, Nacken und Schultern – ideal, um Verspannungen nach einem langen Tag oder einem Ausflug zu lösen. Mit 100 % natürlichen Ölen.',
        ],
        'مساج الزيوت العطرية (أروما)' => [
            'title' => 'Aroma-Massage', 'duration' => '60 Minuten', 'includes' => $oils,
            'short_description' => 'Eine ruhige Ganzkörpermassage mit natürlichen Aromaölen zum Entspannen und Stressabbau.',
            'description' => 'Eine sanfte, beruhigende Ganzkörpermassage mit 100 % natürlichen Aromaölen, die tiefe Entspannung schenkt, Stress abbaut und die Stimmung hebt.',
        ],
        'المساج الرياضي والعلاجي' => [
            'title' => 'Sport- und medizinische Massage', 'duration' => '60 Minuten', 'includes' => $oils,
            'short_description' => 'Eine Massage mit kräftigerem Druck für müde, verspannte Muskeln – ideal nach Sport oder Anstrengung.',
            'description' => 'Eine Massage mit kräftigerem Druck für müde, verspannte Muskeln und Verspannungspunkte. Ideal für Sportler und nach körperlicher Anstrengung oder Wassersport. Mit 100 % natürlichen Ölen.',
        ],
        'مساج الأحجار الساخنة' => [
            'title' => 'Hot-Stone-Massage', 'duration' => '60 Minuten', 'includes' => $oils,
            'short_description' => 'Warme Steine auf dem Körper während der Massage wärmen die Muskeln und entspannen sie tief.',
            'description' => 'Glatte, warme Steine werden auf wichtige Punkte des Körpers gelegt und in die Massage einbezogen. Sie wärmen die Muskeln und helfen ihnen, sich tief zu entspannen. Mit 100 % natürlichen Ölen.',
        ],
        'مساج الأعشاب' => [
            'title' => 'Kräuter-Massage', 'duration' => '60 Minuten', 'includes' => $oils,
            'short_description' => 'Eine Massage mit natürlichen Kräutern und Ölen zum Entspannen und Auftanken.',
            'description' => 'Eine Ganzkörpermassage mit natürlichen Kräutern und 100 % natürlichen Ölen, nach der du dich entspannt, erfrischt und voller Energie fühlst.',
        ],
        'مساج العلاج بالخشب (وود ثيرابي)' => [
            'title' => 'Holztherapie-Massage', 'duration' => '60 Minuten', 'includes' => $oils,
            'short_description' => 'Eine Massagetechnik mit speziellen Holzwerkzeugen, die den Körper strafft und belebt.',
            'description' => 'Eine Massagetechnik mit speziell geformten Holzwerkzeugen, die die Durchblutung der behandelten Zonen anregt und hilft, den Körper zu straffen und zu formen. Mit 100 % natürlichen Ölen.',
        ],
        'مساج السيلوليت (أنتي سيليوليت)' => [
            'title' => 'Anti-Cellulite-Massage', 'duration' => '60 Minuten', 'includes' => $oils,
            'short_description' => 'Eine Massage für Cellulite-Zonen, die das Hautbild verbessert und strafft.',
            'description' => 'Eine Massage, die sich auf Zonen mit Cellulite konzentriert. Spezielle Griffe helfen, die Haut glatter, ebenmäßiger und straffer wirken zu lassen. Mit 100 % natürlichen Ölen.',
        ],
    ],

    'places' => [
        'جزيرة الجفتون' => ['name' => 'Insel Giftun', 'description' => 'Ein Naturschutzgebiet mit einigen der schönsten Korallenriffe und weißen Strände des Roten Meeres.'],
        'أورانج باي' => ['name' => 'Orange Bay', 'description' => 'Ein traumhafter Strand mit türkisblauem Wasser und feinem Sand – eines der beliebtesten Fotomotive in Hurghada.'],
        'مارينا الغردقة' => ['name' => 'Marina Hurghada', 'description' => 'Eine moderne Uferpromenade mit Yachten, Restaurants und Cafés – der schönste Ort für einen Abendspaziergang.'],
        'الشعاب المرجانية' => ['name' => 'Korallenriffe', 'description' => 'Eine farbenfrohe Unterwasserwelt, die Hurghada zu einem der besten Orte der Welt zum Tauchen und Schnorcheln macht.'],
        'الصحراء الشرقية' => ['name' => 'Östliche Wüste', 'description' => 'Berge und goldene Dünen hinter der Stadt – perfekt für Safaris und den Sonnenuntergang.'],
        'مسجد الميناء الكبير' => ['name' => 'Große El-Mina-Moschee', 'description' => 'Ein architektonisches Meisterwerk am Meer im Stadtteil Dahar und eines der Wahrzeichen der Stadt.'],
        'الجونة' => ['name' => 'El Gouna', 'description' => 'Die Lagunenstadt mit ihren Kanälen nördlich von Hurghada und ihrer unverwechselbaren Architektur.'],
        'شاطئ البحر الأحمر' => ['name' => 'Küste des Roten Meeres', 'description' => 'Sonne das ganze Jahr und lange Strände machen Hurghada zu jeder Jahreszeit zum idealen Reiseziel.'],
    ],
];

<?php
function My_Scan_dir($argv)// scanDir function, avec ou sans option
{
    global $array;
    global $name_sprite;
    global $name_style;
    $name_sprite = "sprite.png"; // Nom du .png par défaut
    $name_style = "style.css"; // Nom du .css par défaut
    array_shift($argv); // Retire "php CSS_GENERATOR" du tableau $argv
    if (in_array("-man", $argv) or in_array("--help", $argv)) { // Gestion de -man ou --help
        echo shell_exec('cat man'); // Cat le man en globalité
        return;
    }
    if (in_array("-i", $argv) or in_array("--output-image", $argv)) {  // Gestion de -i & -output-image
        if (in_array("-i", $argv)) {
            $array_option_verif = array_search("-i", $argv); // Recherche l'arguement après -i dans $argv
        } else {
            $array_option_verif = array_search("--output-image", $argv); // Recherche l'arguement après -output-image dans $argv
        }
        $array_sprite_name = $argv[$array_option_verif +1]; // Crée l'option et le nom de l'image
        $name_sprite = $array_sprite_name; 
        unset($argv[$array_option_verif +1]); // Supprime l'option et le nom de l'image
        unset($argv[$array_option_verif]);
    }
    if (in_array("-s", $argv) or in_array("--output-style", $argv)) { // Gestion de -s & --output-style
        if (in_array("-s", $argv)) {
            $array_option_verif = array_search("-s", $argv); // Recherche l'arguement après -s dans $argv
        } else {
            $array_option_verif = array_search("--output-style", $argv); // Recherche l'arguement après --output-style dans $argv
        }
        $array_style_name = $argv[$array_option_verif +1]; // Crée l'option et le nom du style
        $name_style = $array_style_name;
        unset($argv[$array_option_verif +1]); //Supprime l'option et le nom du style
        unset($argv[$array_option_verif]);
    }
    if (in_array("-r", $argv) or in_array("--recursive", $argv)) { // Gestion de -r & --recursive
        if (in_array("-r", $argv)) { //Si $argv = -r OR --recursive on le delete
            $array_option_verif = array_search("-r", $argv);
        } else {
            $array_option_verif = array_search("--recursive", $argv);
        }
        $array_no_option = end($argv);
        unset($argv[$array_option_verif]);
        $dir_path = []; // Transforme $argv -> string pour la récursive
        array_push($dir_path, $array_no_option);
        $dir_path_string = implode(' ', $dir_path);
        $array = [];
        My_recursive($dir_path_string); // Lancement du scan recursif 
    } elseif (is_dir(end($argv))) { // Gestion si $argv = dir
        $dh = opendir(end($argv));
        $array = [];
        while (($file = readdir($dh)) !== false) { // Scan itératif 
            if (substr($file, -3) == "png") {
                array_push($array, end($argv)."/".$file); // Push $files dans mon array
            }
        }
        closedir($dh);
    } else {  // Gestion si $argv = files
        $array = [];
        foreach ($argv as $files) { 
            array_push($array, $files); // Push $files dans mon $array
        }
    }
    echo "En cas de problèmes utiliser -man ou --help pour accéder au manuel \n";
    echo "-------------------------------------------------------------------\n";
    echo "Ajout des images : \n"; // En cas de réussite, echo les images ajouter au sprite et indique la fait du script
    foreach ($array as $key => $value) { 
        echo "--> $value\n"; // Indique les images ajouter au rendu final une par une
        usleep(500000); // Ajout d'un délai pour avoir le temps de lire.
    }
    echo "-------------------------------\n";
    echo "Le rendu final est prêt !\n";
    usleep(500000); // Ajout d'un délai pour avoir le temps de lire.
    echo "Ouverture du .png en cours.....\n";
    usleep(1000000); // Ajout d'un délai pour avoir le temps de lire.
    Generate_sprite($array);
}
My_Scan_dir($argv);
function My_recursive($dir_path_string) // Récursive
{
    //fonction récusrive
    global $array;
    if ($dh = opendir($dir_path_string)) {
        while (($file = readdir($dh)) !== false) {
            if ($file !== "." and $file !== ".." and $file !== ".git") { // Passe outre les $files "." / ".." / ".git"
                if (is_dir($dir_path_string.$file)) { // Si $files = dossier --> relance la fonction (recursif)
                    My_recursive($dir_path_string.$file."/");
                } elseif (substr($file, -4) == ".png") { // Si les 3 dernier string de $files = png 
                    array_push($array, $dir_path_string.$file); // Push $file dans $array 
                }
            }
        }
        closedir($dh);
    }
}
function Generate_sprite($array) 
{
    // Génère l'image sprite png
    global $name_sprite;
    global $name_style;
    $img_height_max = [];
    $img_width_max = [];
    $all_img_width = [];
    foreach ($array as $picture) { // Gère la taille
        $source = imagecreatefrompng($picture);
        $img_width = imagesx($source);
        $img_height = imagesy($source);
        array_push($img_height_max, $img_height);
        array_push($img_width_max, $img_width);
        array_push($all_img_width, $img_width);
    }
    $sprite_width = 0;// Incrémentation
    foreach ($array as $key => $picture) {
        $sprite_width += $all_img_width[$key];
    }
        $destination = imagecreatetruecolor($sprite_width, max($img_height_max));
    foreach ($array as $key => $picture) {
        $source = imagecreatefrompng($picture);
        static $pos_x = 0;
        imagecopy($destination, $source, $pos_x, 0, 0, 0, $sprite_width, max($img_height_max));
        $pos_x += $all_img_width[$key];
    }
    imagepng($destination, $name_sprite); // Crée le .png
    $fichier = fopen($name_style, "c"); // Crée le fichier CSS
    fwrite($fichier, "html{\n\tposition: relative;\n}.image{\n\tbackground: url('".$name_sprite."') no-repeat;\n\twidth:200vw;\n\theight: ".max($img_height_max)."px;\n\tleft: 0;\n\ttop: 0;\n}\n");
        $position_array=[];
        $arr = [];
        $all_img_width_for_position_x = array_merge($arr, $all_img_width);
        array_unshift($all_img_width, 0);
    foreach ($array as $key => $file) {
        $key1 = $key +1;
        static $position_x = 0;
        $position_x -= $all_img_width[$key];
        fwrite($fichier, ".image-".$key1."{\n\tposition: absolute;\n\tbackground: url('".$name_sprite."') no-repeat;\n\twidth:".$all_img_width_for_position_x[$key]."px;\n\theight: ".max($img_height_max)."px;\n\tleft: 0;\n\ttop: 0;\n\tbackground-position: ".$position_x."px;\n}\n");
    }
        fclose($fichier);
    shell_exec("open $name_sprite");
}

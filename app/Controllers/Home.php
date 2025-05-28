<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        // Renderiza la vista del encabezado
        $header = view('header/header.php');
        
        // Renderiza la vista de la barra de navegación
        $navbar = view('navbar/navbar.php');

        //Renderiza la vista de la barra de navegación
        $footer = view('footer/footer.php');

        //renderiza la vista de los productos
       

     
        
        // Combina las vistas en una sola variable
        $output = $navbar . $header . $footer;
        
        // Retorna la combinación de las vistas
        return $output;
    }

   
    public function login()
    {
        // Renderiza la vista del encabezado
        $header = view('header/header.php');
        
        // Renderiza la vista de la barra de navegación
        $navbar = view('navbar/navbar.php');

        //Renderiza la vista de la barra de navegación
        $footer = view('footer/footer.php');

        //renderiza la vista del formulario de login
        $login = view('login/login.php');
        
        // Combina las vistas en una sola variable
        $output = $navbar . $login .  $footer;
        
        // Retorna la combinación de las vistas
        return $output;
    }
   

   

}

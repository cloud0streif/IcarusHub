<?php

// these are imported libraries
// if you need additional stuff from http://silex.sensiolabs.org/
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;


// this is the controller, it allows you to define "routes"
// "routes" are the parts of the url after your domain name
// ex. ondra.com/route
// $app->match() is the function used to define a route
// the first argument is the route in string form
// the second argument is the "callback function", it's the code that gets executed
$app->match('/', function() use ($app) {
    $input= 9;

    $output = 3;
    if (($input % 2) == 0){
        $output = 1;
    } else {
        $output = 2;
    }

    return $app['twig']->render('index.html.twig', array(
        "output" => $output
    ));
})->bind('homepage');

$app->match('/login', function(Request $request) use ($app) {
    $form = $app['form.factory']->createBuilder('form')
        ->add('username', 'text', array('label' => 'Username', 'data' => $app['session']->get('_security.last_username')))
        ->add('password', 'password', array('label' => 'Password'))
        ->getForm()
    ;

    return $app['twig']->render('login.html.twig', array(
        'form'  => $form->createView(),
        'error' => $app['security.last_error']($request),
    ));
})->bind('login');

$app->match('/doctrine', function() use ($app) {
    return $app['twig']->render(
        'doctrine.html.twig',
        array(
            'posts' => $app['db']->fetchAll('SELECT * FROM post')
        )
    );
})->bind('doctrine');


// form will allow us to POST information to our server
$app->match('/form', function(Request $request) use ($app) {

    $builder = $app['form.factory']->createBuilder('form');
//    $choices = array('choice a', 'choice b', 'choice c');

    $form = $builder
//        ->add('text2', 'text', array('attr' => array('class' => 'span1', 'placeholder' => '.span1')))
//        ->add('text3', 'text', array('attr' => array('class' => 'span2', 'placeholder' => '.span2')))
//        ->add('text4', 'text', array('attr' => array('class' => 'span3', 'placeholder' => '.span3')))
//        ->add('text5', 'text', array('attr' => array('class' => 'span4', 'placeholder' => '.span4')))
//        ->add('text6', 'text', array('attr' => array('class' => 'span5', 'placeholder' => '.span5')))
//        ->add('text8', 'text', array('disabled' => true, 'attr' => array('placeholder' => 'disabled field')))
//        ->add('textarea', 'textarea')
//        ->add('email', 'email')
        ->add('Longit', 'integer')
        ->add('Time', 'integer')
        ->add('Dir', 'text')
        ->add('Day', 'integer')
        ->add('Elev_ang', 'integer')
//        ->add('money', 'money')
//        ->add('number', 'number')
//        ->add('password', 'password')
//        ->add('percent', 'percent')
//        ->add('search', 'search')
//        ->add('url', 'url')
//        ->add('choice1', 'choice',  array(
//            'choices'  => $choices,
//            'multiple' => true,
//            'expanded' => true
//        ))
//        ->add('choice2', 'choice',  array(
//            'choices'  => $choices,
//            'multiple' => false,
//            'expanded' => true
//        ))
//        ->add('choice3', 'choice',  array(
//            'choices'  => $choices,
//            'multiple' => true,
//            'expanded' => false
//        ))
//        ->add('choice4', 'choice',  array(
//            'choices'  => $choices,
//            'multiple' => false,
//            'expanded' => false
//        ))
//        ->add('country', 'country')
//        ->add('language', 'language')
//        ->add('locale', 'locale')
//        ->add('timezone', 'timezone')
//        ->add('date', 'date')
//        ->add('datetime', 'datetime')
//        ->add('time', 'time')
//        ->add('birthday', 'birthday')
//        ->add('checkbox', 'checkbox')
//        ->add('file', 'file')
//        ->add('radio', 'radio')
        ->add('submit', 'submit')
        ->getForm()
    ;

    // A POST is the standard http form submission method
    // this checks whether when you hit /form, you're getting a regular form
    // or you're processing the form
    // the request is a POST, run the code inside the if, to process the form
    if ($request->isMethod('POST')) {
        // get the form object
        $submission = $request->get("form");

        $Longit = $submission["Longit"];
        $Dir =$submission["Dir"];
        $Time = $submission["Time"];
        $Day = $submission["Day"];
        $Elev_ang = deg2rad($submission["Elev_ang"]);
        $Ps = 1000; //W/m^2

        if ($Dir="W")
            $Longit = $Longit * -1;

        $t_zone = round($Longit / 15);

        $d_deg = (360/365.2422)*$Day;
        $d_rad = deg2rad((360/365.2422)*$Day);

        $EOT = -0.017188 - 0.42811 * Cos($d_rad) + 7.35141 * Sin($d_rad) + 3.34946 * Cos(2 * $d_rad) + 9.3617 * Sin(2 * $d_rad);

        $t_offset = $EOT -  4 * $Longit + 60 * $t_zone;

        $t_true = $Time + $t_offset / 60;

        $alpha = deg2rad(360 / 24 * ($t_true - 12));

        $declination = deg2rad(23.44 * Sin(deg2rad(360 * (($Day - 80) / 365.25))));

        $cos_zenith = Sin($declination) * Sin(deg2rad($Longit )) + Cos($declination) * Cos(deg2rad($Longit)) * Cos($alpha);

        $zenith = Acos($cos_zenith);

        $tan_azimuth = Sin($alpha) / (Sin(deg2rad($Longit)) * Cos($alpha) - Cos(deg2rad($Longit)) * Tan($declination));

        $sol_azimuth = "";
        If ($alpha > 0 And $tan_azimuth > 0)
            $sol_azimuth = 180 + rad2deg(Atan($tan_azimuth));

        If ($alpha > 0 And $tan_azimuth < 0)
            $sol_azimuth = 360 + rad2deg(Atan($tan_azimuth));

        If ($alpha < 0 And $tan_azimuth > 0)
            $sol_azimuth = rad2deg(Atan($tan_azimuth));

        If ($alpha < 0 And $tan_azimuth < 0)
            $sol_azimuth = 180 + rad2deg(Atan($tan_azimuth));

        $azimuth = array(range(1,360,1));
        $P = array();
            For($i = 1; $i=360; $i++){

                $P[] = $Ps * (Cos($Elev_ang) * Cos($zenith) + Sin($Elev_ang) * Sin($zenith) * Cos(deg2rad($sol_azimuth - $azimuth[$i])));
            }
        $Opt_Az = array_keys($P,max($P));


        
        // rewrite your function using the $submission['whatever'] variables here
        // use http://php.net/manual/en/book.math.php
        $twiceLong = $submission["Longit"] * 2;

        // Write your equation here and set the ouput
        //the . conatemates two things together
       // $output = "your equation output here for example, 2x the longiture=".$twiceLong;

        // on form submission, we'll return the display template
        return $app['twig']->render('display.html.twig', array(
            'Longitude' => $submission["Longit"],
            'Dir' => $Dir,
            'Time' => $Time,
            'EOT' => $EOT,
            'T_offset' => $t_offset,
            'True_Time' => $t_true,
            'Power' => $P,
            'Time_Zone' => $t_zone,
            'Azimuth' => $azimuth,
            'Power' => $P,
            'Opt_Az' => $Opt_Az,

        ));

//        check the isValid function in the silex documentation and implement it for your fomr
//        if ($form->submit($request)->isValid()) {
//
//        }
    }

    // the return of a controller function will always be what needs to be displayed
    // in this case it's form.html.twig
    // the render function takes two arguments:
    // a string of the file name that contains the template
    // an array of variables to pass to the template
    return $app['twig']->render('form.html.twig', array(
        'form' => $form->createView(),
        // pass additional variables to the template like this
        'name' => 'Alex'
    ));
})->method("GET|POST")->bind('form');

$app->match('/', function() use ($app) {
    return $app['twig']->render(
        'doctrine.html.twig',
        array(
            'posts' => $app['db']->fetchAll('SELECT * FROM post')
        )
    );
})->bind('doctrine');

$app->match('/logout', function() use ($app) {
    $app['session']->clear();

    return $app->redirect($app['url_generator']->generate('homepage'));
})->bind('logout');

$app->get('/page-with-cache', function() use ($app) {
    $response = new Response($app['twig']->render('page-with-cache.html.twig', array('date' => date('Y-M-d h:i:s'))));
    $response->setTtl(10);

    return $response;
})->bind('page_with_cache');

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
    }

    return new Response($message, $code);
});

return $app;

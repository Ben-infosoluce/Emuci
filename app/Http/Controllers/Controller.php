<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
    public function showRtiDashboard()
    {
        return inertia('Rti/Layout');
    }
    public function showMainDashboard()
    {
        return inertia('Rti/Dashbord');
    }
}

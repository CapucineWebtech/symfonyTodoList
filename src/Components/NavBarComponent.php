<?php
namespace App\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('navBarComponent')]
class NavBarComponent
{
    public string $userName;
}
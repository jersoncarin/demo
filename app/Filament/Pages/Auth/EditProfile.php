<?php

namespace App\Filament\Pages\Auth;

use App\Forms\Components\LeafletInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                LeafletInput::make('location')
                    ->setMapHeight(300) // Here you can specify a map height in pixels, by default the height is equal to 200
                    ->setZoomControl(false) // Here you can enable/disable zoom control on the map (default: true)
                    ->setScrollWheelZoom(true) // Here you can enable/disable zoom on wheel scroll (default: true)
                    ->setZoomLevel(13) // Here you can change the default zoom level (when the map is loaded for the first time), default value is 10
                    ->required()
            ]);
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: aitspeko
 * Date: 31/07/2017
 * Time: 11:48
 */

namespace App;

use Illuminate\Support\Facades\Auth;

/**
 * Class Country
 *
 * @package App
 *
 * @property string title
 * @property string iso_2
 * @property string iso_3
 * @property int    iso_numeric
 * @property int    calling_code
 * @property int    risk_rating
 * @property string risk_rating_description
 * @property int    updated_by
 * @property string insurance_code
 */
class Country extends BaseModel
{


        protected $table = 'countries';

        /**
         * Update the rating of the country.
         *
         * @param $rating
         */

}
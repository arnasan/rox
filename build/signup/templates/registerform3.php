<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.

*/

/*
 * REGISTER FORM TEMPLATE
 */
$map_conf = PVars::getObj('map');
?>
<input type="hidden" id="osm-tiles-provider-base-url"
       value="<?php echo($map_conf->osm_tiles_provider_base_url); ?>"/>
<input type="hidden" id="osm-tiles-provider-api-key"
       value="<?php echo($map_conf->osm_tiles_provider_api_key); ?>"/>



    <div class="card card-block">
        <div class="row">
            <div class="card-header">
                <h4 class="card-title"><?php echo $words->get('Location'); ?><small class="pull-right">Step 3/4</small></h4>
                <progress class="progress progress-striped progress-success" value="75" max="100">
                    <div class="progress">
                        <span class="progress-bar" style="width: 75%;">75%</span>
                    </div>
                </progress>
                <h4><small>Please fill out all fields</small></h4>
            </div>
        </div>

        <form method="post" action="<?php echo $baseuri.'signup/4' ?>" class="form" name="geo-form-js" id="geo-form-js">
            <?= $callback_tag ?>
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group">
                        <label for="location"
                               class="form-control-label sr-only"><?= $words->getSilent(
                                'label_setlocation'
                            ) ?></label><?php echo $words->flushBuffer(); ?>
                        <input type="hidden" name="location-geoname-id" id="location-geoname-id"
                               value="<?= isset($vars['location-geoname-id']) ? $vars['location-geoname-id'] : '' ?>"/>
                        <input type="hidden" name="location-latitude" id="location-latitude"
                               value="<?= isset($vars['location-latitude']) ? $vars['location-latitude'] : '' ?>"/>
                        <input type="hidden" name="location-longitude" id="location-longitude"
                               value="<?= isset($vars['location-longitude']) ? $vars['location-longitude'] : '' ?>"/>
                        <input type="text" name="location"
                               id="location" class="form-control location-picker"
                               placeholder="<?= $words->get(
                                   'label_setlocation'
                               ) ?>"
                            <?php
                            echo isset($vars['location']) ? 'value="'
                                .htmlentities(
                                    $vars['location'],
                                    ENT_COMPAT,
                                    'utf-8'
                                ).'" ' : '';
                            ?>
                        >
                        <small class="text-muted text-justify"><?= $words->get(
                                'subline_location'
                            ) ?></small>
                    </div>
                </div>
            </div>
        <div class="row">
            <div class="col-xs-12">
                <div id="map" class="m-b-1" style="width: 100%; height: 440px; border: 1px solid #aaa;"></div>
            </div>
        </div><!-- subcolumns -->

            <div class="form-group row">
                <div class="col-xs-12">
                <input type="submit" value="<?php echo $words->getSilent('NextStep'); ?>" class="form-control btn btn-primary" ><?php echo $words->flushBuffer(); ?>
                </div>
            </div>
    </form>
</div>
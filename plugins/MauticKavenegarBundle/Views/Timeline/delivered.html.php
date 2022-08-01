<?php

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://www.mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

$message = $event['extra']['message'];
$sms_message = $event['extra']['sms']->getMessage();
$stat_id = $event['extra']['stat']->getId();
$stat_refid = $event['extra']['stat']->getRefid();

?>

<strong><?php echo $message; ?></strong>

<dl class="dl-horizontal">
    <dt><?php echo $view['translator']->trans('SMS'); ?>:</dt>
    <dd class="ellipsis"><?php echo $sms_message ?></dd>
    <dt><?php echo $view['translator']->trans('Stat Id'); ?>:</dt>
    <dd class="ellipsis"><?php echo $stat_id ?></dd>
    <dt><?php echo $view['translator']->trans('Kavenegar messageid'); ?>:</dt>
    <dd class="ellipsis"><?php echo $stat_refid ?></dd>
</dl>


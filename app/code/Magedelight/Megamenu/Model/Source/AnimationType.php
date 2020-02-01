<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Megamenu
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Megamenu\Model\Source;

class AnimationType implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => __('Attention Seekers'), 'value' => [
                [
                    'label' => 'bounce',
                    'value' => 'bounce'
                ],
                [
                    'label' => 'flash',
                    'value' => 'flash'
                ],
                [
                    'label' => 'pulse',
                    'value' => 'pulse'
                ],
                [
                    'label' => 'rubberBand',
                    'value' => 'rubberBand'
                ],
                [
                    'label' => 'shake',
                    'value' => 'shake'
                ],
                [
                    'label' => 'swing',
                    'value' => 'swing'
                ],
                [
                    'label' => 'tada',
                    'value' => 'tada'
                ],
                [
                    'label' => 'wobble',
                    'value' => 'wobble'
                ],
                [
                    'label' => 'jello',
                    'value' => 'jello'
                ],
            ],
        ];
        $options[] = ['label' => __('Bouncing Entrances'), 'value' => [
                [
                    'label' => 'bounceIn',
                    'value' => 'bounceIn'
                ],
                [
                    'label' => 'bounceInDown',
                    'value' => 'bounceInDown'
                ],
                [
                    'label' => 'bounceInLeft',
                    'value' => 'bounceInLeft'
                ],
                [
                    'label' => 'bounceInRight',
                    'value' => 'bounceInRight'
                ],
                [
                    'label' => 'bounceInUp',
                    'value' => 'bounceInUp'
                ],
            ],
        ];
        $options[] = ['label' => __('Bouncing Exits'), 'value' => [
                [
                    'label' => 'bounceOut',
                    'value' => 'bounceOut'
                ],
                [
                    'label' => 'bounceOutDown',
                    'value' => 'bounceOutDown'
                ],
                [
                    'label' => 'bounceOutLeft',
                    'value' => 'bounceOutLeft'
                ],
                [
                    'label' => 'bounceOutRight',
                    'value' => 'bounceOutRight'
                ],
                [
                    'label' => 'bounceOutUp',
                    'value' => 'bounceOutUp'
                ],
            ],
        ];
        $options[] = ['label' => __('Fading Entrances'), 'value' => [
                [
                    'label' => 'fadeIn',
                    'value' => 'fadeIn'
                ],
                [
                    'label' => 'fadeInDown',
                    'value' => 'fadeInDown'
                ],
                [
                    'label' => 'fadeInDownBig',
                    'value' => 'fadeInDownBig'
                ],
                [
                    'label' => 'fadeInLeft',
                    'value' => 'fadeInLeft'
                ],
                [
                    'label' => 'fadeInLeftBig',
                    'value' => 'fadeInLeftBig'
                ],
                [
                    'label' => 'fadeInRight',
                    'value' => 'fadeInRight'
                ],
                [
                    'label' => 'fadeInRightBig',
                    'value' => 'fadeInRightBig'
                ],
                [
                    'label' => 'fadeInUp',
                    'value' => 'fadeInUp'
                ],
                [
                    'label' => 'fadeInUpBig',
                    'value' => 'fadeInUpBig'
                ],
            ],
        ];
        $options[] = ['label' => __('Fading Exits'), 'value' => [
                [
                    'label' => 'fadeOut',
                    'value' => 'fadeOut'
                ],
                [
                    'label' => 'fadeOutDown',
                    'value' => 'fadeOutDown'
                ],
                [
                    'label' => 'fadeOutDownBig',
                    'value' => 'fadeOutDownBig'
                ],
                [
                    'label' => 'fadeOutLeft',
                    'value' => 'fadeOutLeft'
                ],
                [
                    'label' => 'fadeOutLeftBig',
                    'value' => 'fadeOutLeftBig'
                ],
                [
                    'label' => 'fadeOutRight',
                    'value' => 'fadeOutRight'
                ],
                [
                    'label' => 'fadeOutRightBig',
                    'value' => 'fadeOutRightBig'
                ],
                [
                    'label' => 'fadeOutUp',
                    'value' => 'fadeOutUp'
                ],
                [
                    'label' => 'fadeOutUpBig',
                    'value' => 'fadeOutUpBig'
                ],
            ],
        ];
        $options[] = ['label' => __('Flippers'), 'value' => [
                [
                    'label' => 'flip',
                    'value' => 'flip'
                ],
                [
                    'label' => 'flipInX',
                    'value' => 'flipInX'
                ],
                [
                    'label' => 'flipInY',
                    'value' => 'flipInY'
                ],
                [
                    'label' => 'flipOutX',
                    'value' => 'flipOutX'
                ],
                [
                    'label' => 'flipOutY',
                    'value' => 'flipOutY'
                ]
            ],
        ];
        $options[] = ['label' => __('Lightspeed'), 'value' => [
                [
                    'label' => 'lightSpeedIn',
                    'value' => 'lightSpeedIn'
                ],
                [
                    'label' => 'lightSpeedOut',
                    'value' => 'lightSpeedOut'
                ]
            ],
        ];
        $options[] = ['label' => __('Rotating Entrances'), 'value' => [
                [
                    'label' => 'rotateIn',
                    'value' => 'rotateIn'
                ],
                [
                    'label' => 'rotateInDownLeft',
                    'value' => 'rotateInDownLeft'
                ],
                [
                    'label' => 'rotateInDownRight',
                    'value' => 'rotateInDownRight'
                ],
                [
                    'label' => 'rotateInUpLeft',
                    'value' => 'rotateInUpLeft'
                ],
                [
                    'label' => 'rotateInUpRight',
                    'value' => 'rotateInUpRight'
                ]
            ],
        ];
        $options[] = ['label' => __('Rotating Exits'), 'value' => [
                [
                    'label' => 'rotateOut',
                    'value' => 'rotateOut'
                ],
                [
                    'label' => 'rotateOutDownLeft',
                    'value' => 'rotateOutDownLeft'
                ],
                [
                    'label' => 'rotateOutDownRight',
                    'value' => 'rotateOutDownRight'
                ],
                [
                    'label' => 'rotateOutUpLeft',
                    'value' => 'rotateOutUpLeft'
                ],
                [
                    'label' => 'rotateOutUpRight',
                    'value' => 'rotateOutUpRight'
                ]
            ],
        ];
        $options[] = ['label' => __('Sliding Entrances'), 'value' => [
                [
                    'label' => 'slideInUp',
                    'value' => 'slideInUp'
                ],
                [
                    'label' => 'slideInDown',
                    'value' => 'slideInDown'
                ],
                [
                    'label' => 'slideInLeft',
                    'value' => 'slideInLeft'
                ],
                [
                    'label' => 'slideInRight',
                    'value' => 'slideInRight'
                ]
            ],
        ];
        $options[] = ['label' => __('Sliding Exits'), 'value' => [
                [
                    'label' => 'slideOutUp',
                    'value' => 'slideOutUp'
                ],
                [
                    'label' => 'slideOutDown',
                    'value' => 'slideOutDown'
                ],
                [
                    'label' => 'slideOutLeft',
                    'value' => 'slideOutLeft'
                ],
                [
                    'label' => 'slideOutRight',
                    'value' => 'slideOutRight'
                ]
            ]
        ];
        $options[] = ['label' => __('Zoom Entrances'), 'value' => [
                [
                    'label' => 'zoomIn',
                    'value' => 'zoomIn'
                ],
                [
                    'label' => 'zoomInDown',
                    'value' => 'zoomInDown'
                ],
                [
                    'label' => 'zoomInLeft',
                    'value' => 'zoomInLeft'
                ],
                [
                    'label' => 'zoomInRight',
                    'value' => 'zoomInRight'
                ],
                [
                    'label' => 'zoomInUp',
                    'value' => 'zoomInUp'
                ]
            ]
        ];
        $options[] = ['label' => __('Zoom Exits'), 'value' => [
                [
                    'label' => 'zoomOut',
                    'value' => 'zoomOut'
                ],
                [
                    'label' => 'zoomOutDown',
                    'value' => 'zoomOutDown'
                ],
                [
                    'label' => 'zoomOutLeft',
                    'value' => 'zoomOutLeft'
                ],
                [
                    'label' => 'zoomOutRight',
                    'value' => 'zoomOutRight'
                ],
                [
                    'label' => 'zoomOutUp',
                    'value' => 'zoomOutUp'
                ]
            ]
        ];
        $options[] = ['label' => __('Specials'), 'value' => [
                [
                    'label' => 'hinge',
                    'value' => 'hinge'
                ],
                [
                    'label' => 'rollIn',
                    'value' => 'rollIn'
                ],
                [
                    'label' => 'rollOut',
                    'value' => 'rollOut'
                ]
            ]
        ];
        return $options;
    }
}

<?php

return [
    'paycaseMember' => [
        // class of your domain object
        'class' => App\User::class,

        // name of the graph (default is "default")
        'graph' => 'paycaseMember',

        // property of your object holding the actual state (default is "state")
        'property_path' => 'last_state',

        // list of all possible states
        'states' => [
            'new',
            'pending',
            'verification',
            'photo_verification',
            're-verify',
            'reviewing',
            'approved',
            'rejected',
        ],

        // list of all possible transitions
        'transitions' => [
            'grant' => [
                'from' => ['new'],
                'to' => 'pending',
            ],
            'personal_information_submitted' => [
                'from' =>  ['pending'],
                'to' => 'verification',
            ],
            'personal_information_updated' => [
                'from' =>  ['approved', 'rejected', 'reviewing'],
                'to' => 're-verify',
            ],
            'photo_id_updated' => [
                'from' =>  ['approved', 'rejected'],
                'to' => 're-verify',
            ],
            'personal_information_review' => [
                'from' =>  ['verification'],
                'to' => 'reviewing',
            ],
            'photo_id_submitted' => [
                'from' => ['verification', 're-verify'],
                'to' => 'photo_verification',
            ],
            'reviewing' => [
                'from' => ['personal_information_review'],
                'to' => 'approved',
            ],
            'approved' => [
                'from' => ['photo_verification', 'verification', 're-verify', 'reviewing'],
                'to' =>  'approved',
            ],
            'rejected' => [
                'from' => ['verification', 'photo_verification', 're-verify'],
                'to' =>  'rejected',
            ],
        ],
        // Using Listeners instead callbacks
        // 'callbacks' => [
        //     // will be called when testing a transition
        //     'guard' => [
        //         // 'guard_on_submitting' => [
        //         //     // call the callback on a specific transition
        //         //     'on' => 'submit_changes',
        //         //     // will call the method of this class
        //         //     'do' => ['MyClass', 'handle'],
        //         //     // arguments for the callback
        //         //     'args' => ['object'],
        //         // ],
        //     ],

        //     // will be called before applying a transition
        //     'before' => [],

        //     // will be called after applying a transition
        //     'after' => [],
        // ],
    ],
];

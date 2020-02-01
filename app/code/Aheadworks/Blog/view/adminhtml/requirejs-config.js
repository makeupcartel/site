/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

var config = {
    map: {
        '*': {
            blogPostMetaCharCount:  'Aheadworks_Blog/js/post/char-count',
            wordpressImport:  'Aheadworks_Blog/js/system/config/wordpress-import',
            blogCategoryTree:  'Aheadworks_Blog/js/category-tree'
        }
    },
    shim: {
        'jquerytokenize':           ['jquery'],
        'jquerytree':               ['jquery']
    },
    paths: {
        'jquerytokenize':           'Aheadworks_Blog/js/lib/jquery.tokenize',
        'jquerytree':               'Aheadworks_Blog/js/lib/jstree'
    }
};

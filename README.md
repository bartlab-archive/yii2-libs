yii2-libs
============

Library for Yii PHP framework 2.0

Features:
---------
* Traits for ActiveRecord that add new functionality

Installation
------------
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require "maybeworks/yii2-libs" "*"
```

or add

```json
"maybeworks/yii2-libs" : "*"
```

to the require section of your application's `composer.json` file.

Usage
-----

SearchTrait usage
-----
```
use maybeworks\libs\SearchTrait;

class User extends ActiveRecord implements IdentityInterface {
    use SearchTrait;

    // [optional] default page size
    public $pageSize = 20;

    // ... other code ...

    public function init(){
        // add validators
        $this->searchInit();
    }

    // filter by LIKE %..%
    public function filterLikeAttributes() {
        return [
     	    'id',
     		'username',
     		'email',
     		'first_name',
     		'last_name',
     		'comment',
     	];
    }

    // filter by column = "value"
    public function filterAttributes() {
        return [
     	    'id',
     		'status',
     		'created_at',
     		'updated_at',
     		'last_visit',
     	];
    }
}

```

```
$list = User::forSearch(['email'=>'gmail.com']);

/* 
* or by form post
* 
* $item = new User();
* $item->load(Yii::$app->request->post());
* $list = $item->search();
* 
* or by direct value set
* $item = new User();
* $item->email = 'gmail.com';
* $list = $item->search();
*/

foreach ($list->getModels() as $user){
    echo $user->email;
}

```


AdditionsTrait usage
-----
```
use maybeworks\libs\AdditionsTrait;


// get new record
$user = User::getItem();

// get record by ID
$user = User::getItem(10);

// get record copy
$user = User::getItem(10);
$new = $user->copy;

?>

```


> [![MaybeWorks](http://maybe.works/logo/logo_mw.png)](http://maybe.works)  
<i>Nothing is impossible, limit exists only in the minds of...</i>  
[maybe.works](http://maybe.works)
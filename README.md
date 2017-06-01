# Goliath post terms order

This is a WordPress plugin that sorts taxonomy terms per post.

This plugin only adds an admin interface to sort terms. Otherwise it uses core functions because they already exist !

If you want to learn more about term ordering per post 

* [http://simonwheatley.co.uk/2012/07/ordering-terms-in-wordpress-taxonomies/#more-2320](http://simonwheatley.co.uk/2012/07/ordering-terms-in-wordpress-taxonomies/#more-2320)
* [Core trac ticket 9547](https://core.trac.wordpress.org/ticket/9547)

## how to use it

Create a custom taxonomy ( or filter a existing taxonomy ) and add the following argument :
```php
'sort' => true
```

When you want to get all you terms in the right order use this core function :
```php
$terms = wp_get_object_terms( $post_id, $taxonomy, array( 'orderby' => 'term_order' ) );
```


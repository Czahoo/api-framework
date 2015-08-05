# Framework for API
After installing module using composer, you need to follow steps below:

1. Add content of src/.htaccess to your .htaccess file or just copy it to your root directory if u don't have one
2. Create basic folder for your API (by default it should be named 'Api')
3. Create application folder inside your Api (by default it should be named 'App')
4. Create Routing.php file where you put your routing config with basic structure as defined below:
5. Implement 'show' method in your basic controller which will be called by default if no other method is passed
6. To call default method in your basic controller type yoursitename.com/api/ (for external api) or yoursitename.com/internal_api/ (for internal api)
7. To call method 'test' in your custom controller for internal api type yoursitename.com/api/custom_route/test
```
$FRAMEWORK_ROUTING = array(
    Framework::API_TYPE_EXTERNAL => array(
        Framework::DEFAULT_CONTROLLER_ROUTE_NAME => 'Api\App\Basic\Controller\BasicControllerName',
        'custom_route' => 'Api\App\Path\To\Your\Controller',
    ),
    Framework::API_TYPE_INTERNAL => array(
        Framework::DEFAULT_CONTROLLER_ROUTE_NAME => 'Api\App\Basic\Controller\BasicControllerName',
    ),
);
```

# Ignite Backend

http://ignite.eliblaney.com

This is the backend API service for the Ignite app. It is mainly coded in PHP and includes an administrative dashboard. This API is queried whenever any interactions need to be made with the Ignite database and to retrieve information. To use this API, you will need to configure the credentials in `constants.php`, and create a secret token that is identical to the one found in the client application, which will be used to make any request to the API.

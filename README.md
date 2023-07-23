<p align="center">
FOR RUN THE PROJECT
</p>

<p align="center">
   #2 USE THIS COMAND FIRST php artisan migrate:fresh && php artisan passport:install && php artisan db:seed && php artisan serve
</p>

<p align="center">
   After run project you can manage the site SWAGGER DOCUMENTATION FOR YOURE CONVENIENCE http://127.0.0.1:8000/api/documentation#/ <br>
   If you want to use all api inside swagger.io, you must take into account that the iamge field does not exist, you must try to send via postman
</p>

<p align="center">
    Add this lines in youre .env <br><br>
    L5_SWAGGER_CONST_HOST=http://project.test/api/v1 <br>
    L5_SWAGGER_GENERATE_ALWAYS=true <br>
    DB_CONNECTION=mysql <br>
    DB_HOST=127.0.0.1 <br>
    DB_PORT=3306 <br>
    DB_DATABASE=Test_work <br>
    DB_USERNAME=root <br>
    DB_PASSWORD= <br>

</p>

<p align="center">
  INFORMATION FOR USE PROJECT
</p>

<p align="center">
    After doing this line #2 you already have an added admin ['email'=>'admin@gmail.com','password'=>'1234567890'] <br>
    for first lets create a Big Store - http://127.0.0.1:8000/api/documentation#/Big%20Store%20Section/257b50e2ff9d12d14fd2d6a79e06a83f <br>
    Lets create a Category - http://127.0.0.1:8000/api/documentation#/Category%20Section/c93ace596ff38f36f0b8a2f731f7495e <br>
    Lets create a Sub Category - http://127.0.0.1:8000/api/documentation#/Sub%20Category%20Section/1d9b90c17470af22232a478403c3e6ca <br>
    Lets create a Product - http://127.0.0.1:8000/api/documentation#/Sub%20Category%20Section/1d9b90c17470af22232a478403c3e6ca <br>
    Lest add to Cart product - http://127.0.0.1:8000/api/documentation#/Cart%20Section/5109a3eebfcb7725835c2243d3c3910f <br>
    Let's add the quantity of the product - http://127.0.0.1:8000/api/documentation#/Cart%20Section/58a228bce11ba84a2c89a9aa6767c3e7 <br>
</p>



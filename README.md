## Installation

-  Install Docker on your computer

-  Clone the project from the git repository to your local computer and open it in any code editor of your choice (VSCode,      PHPStorm, ATOM, ...) 

-  Create a .env file in the project root directory and copy the contents of the .env.example into this file

-  Open your terminal and navigate to the project root directory

-  run the following

    cd news_env

    docker-compose build app
    
    docker-compose up -d

- Open your Docker desktop application, find the news image and open it in terminal

- run :

    composer install
    
    php artisan migrate:fresh --seed
    
    php artisan passport:install --force
    
    php artisan storage:link

    php artisan config:cache
    
## Testing 

access http://localhost:8000 on your web browser 

<!-- You are all set ! -->

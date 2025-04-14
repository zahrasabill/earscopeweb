pipeline {
    agent any
    environment {
        IMAGE_TAG = "${env.BUILD_ID}"
        GIT_BRANCH = "main"
        DOCKER_IMAGE_NAME_BACKEND = "earscopeweb-backend"
        DOCKER_IMAGE_NAME_FRONTEND = "earscopeweb-frontend"
        NGINX_CONF_PATH = "/etc/nginx/conf/earscope-web.conf"

        // implement blue green deployment
        NEW_CONTAINER_SUFFIX = "-new"
        NEW_COMPOSE_FILE = "docker-compose-new.yml"
        HEALTH_CHECK_TIMEOUT = 60 // seconds

    }
    stages {
        stage('Checkout Code') {
            steps {
                script {
                    sh '''
                    echo "Cleaning workspace..."
                    rm -rf earscopeweb || true
                    '''
                    withCredentials([usernamePassword(credentialsId: 'github-auth-to-jenkins', usernameVariable: 'GIT_USER', passwordVariable: 'GIT_TOKEN')]) {
                        sh '''
                        echo "Cloning repository with authentication..."
                        git clone https://${GIT_USER}:${GIT_TOKEN}@github.com/zahrasabill/earscopeweb.git -b ${GIT_BRANCH}
                        '''
                    }
                }
            }
        }
        stage('Prepare .env File') {
            steps {
                script {
                    withCredentials([file(credentialsId: 'earscopeweb-backend-env', variable: 'ENV_FILE')]) {
                        sh '''
                        echo "Copying .env file..."
                        cp \${ENV_FILE} earscopeweb/backend/.env
                        '''
                    }
                }
                script {
                    withCredentials([file(credentialsId: 'earscopeweb-frontend-env', variable: 'ENV_FILE')]) {
                        sh '''
                        echo "Copying .env file..."
                        cp \${ENV_FILE} earscopeweb/frontend/.env
                        '''
                    }
                }
            }
        }
        stage('Update Image Tags in docker-compose-new.yml') {
            steps {
                script {
                    sh '''
                    echo "Updating docker-compose.yml with new image tag..."
                    
                    cd earscopeweb

                    # Copy docker-compose.yml to a new file for blue-green deployment
                    cp docker-compose.yml ${NEW_COMPOSE_FILE}
                    
                    sed -i "s|image: earscopeweb-backend:latest|image: ${DOCKER_IMAGE_NAME_BACKEND}:${IMAGE_TAG}|" ${NEW_COMPOSE_FILE}
                    sed -i "s|image: earscopeweb-frontend:latest|image: ${DOCKER_IMAGE_NAME_FRONTEND}:${IMAGE_TAG}|" ${NEW_COMPOSE_FILE}
                    
                    echo "Final docker-compose.yml content:"
                    cat ${NEW_COMPOSE_FILE}
                    '''
                }
            }
        }
        stage('Build Docker Images') {
            steps {
                script {
                    sh '''
                    echo "Building Docker images..."
                    cd earscopeweb
                    docker compose -f ${NEW_COMPOSE_FILE} build --no-cache --pull
                    '''
                }
            }
        }
        stage('Deploy New Container') {
            steps {
                script {
                    sh '''
                    echo "Deploying new container..."

                    cd earscopeweb
                    
                    docker compose -f ${NEW_COMPOSE_FILE} up -d --force-recreate 
                    
                    echo "Waiting for new container to be healthy..."
                    sleep ${HEALTH_CHECK_TIMEOUT}

                    echo "Checking health status of new containers..."
                    BACKEND_HEALTH=\$(docker inspect --format='{{json .State.Health.Status}}' ${DOCKER_IMAGE_NAME_BACKEND}${NEW_CONTAINER_SUFFIX} | tr -d '"')
                    FRONTEND_HEALTH=\$(docker inspect --format='{{json .State.Health.Status}}' ${DOCKER_IMAGE_NAME_FRONTEND}${NEW_CONTAINER_SUFFIX} | tr -d '"')
                    
                    if [[ "\$BACKEND_HEALTH" != "healthy" || "\$FRONTEND_HEALTH" != "healthy" ]]; then
                        echo "New containers are not healthy!, rolling back to old container..."
                        docker compose -f ${NEW_COMPOSE_FILE} down || true
                        docker compose -f docker-compose.yml up -d --force-recreate
                        error "Rollback completed due to unhealthy containers!"
                    fi

                    echo "New containers are healthy!. Stopping old containers..."
                    docker compose -f docker-compose.yml down || true
                    '''
                }
            }
        }
        stage('Copy Nginx Config & Restart Nginx') {
            steps {
                script {
                        sh """
                        echo "Copying Nginx configuration..."
                        sudo cp earscopeweb/earscopeweb-frontend.conf /etc/nginx/conf/
                        sudo cp earscopeweb/earscopeweb-backend.conf /etc/nginx/conf/

                        echo "Restarting Nginx..."
                        docker restart nginx

                        echo "Nginx configuration updated successfully!"
                        """
                }
            }
        }
    }
    post {
        success {
            echo "Pipeline build completed successfully!"
        }
        failure {
            echo "Pipeline build failed!"
        }
    }
}

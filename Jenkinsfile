pipeline {
    agent any
    environment {
        IMAGE_TAG = "${env.BUILD_ID}"
        GIT_BRANCH = "main"
        DOCKER_IMAGE_NAME_BACKEND = "earscopeweb-backend"
        DOCKER_IMAGE_NAME_FRONTEND = "earscopeweb-frontend"
        NGINX_CONF_PATH = "/etc/nginx/conf/"

        // implement blue green deployment
        NEW_CONTAINER_SUFFIX = "-new"
        NEW_COMPOSE_FILE = "docker-compose-new.yml"
        HEALTH_CHECK_TIMEOUT = 120 // Meningkatkan timeout
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
                        cp ${ENV_FILE} earscopeweb/backend/.env
                        '''
                    }
                }
                script {
                    withCredentials([file(credentialsId: 'earscopeweb-frontend-env', variable: 'ENV_FILE')]) {
                        sh '''
                        echo "Copying .env file..."
                        cp ${ENV_FILE} earscopeweb/frontend/.env
                        '''
                    }
                }
            }
        }
        stage('Prepare New Docker Compose File') {
            steps {
                script {
                    sh '''
                    echo "Creating new docker-compose file for testing..."

                    cd earscopeweb

                    # copas docker compose file to new file
                    cp docker-compose.yml ${NEW_COMPOSE_FILE}

                    # Update image tags
                    sed -i "s|image: earscopeweb-backend:latest|image: ${DOCKER_IMAGE_NAME_BACKEND}:${IMAGE_TAG}|" ${NEW_COMPOSE_FILE}
                    sed -i "s|image: earscopeweb-frontend:latest|image: ${DOCKER_IMAGE_NAME_FRONTEND}:${IMAGE_TAG}|" ${NEW_COMPOSE_FILE}
                    
                    # Update container names
                    sed -i "s|container_name: earscopeweb-backend|container_name: earscopeweb-backend${NEW_CONTAINER_SUFFIX}|" ${NEW_COMPOSE_FILE}
                    sed -i "s|container_name: earscopeweb-frontend|container_name: earscopeweb-frontend${NEW_CONTAINER_SUFFIX}|" ${NEW_COMPOSE_FILE}
                    
                    # Change port container
                    sed -i 's/8010:8010/8020:8010/' ${NEW_COMPOSE_FILE}
                    sed -i 's/8011:80/8021:80/' ${NEW_COMPOSE_FILE}

                    echo "Final new docker compose file..."
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
        stage('Deploy Docker Images to Container for Testing') {
            steps {
                script {
                    sh '''
                    echo "Deploying new containers for testing..."
                    cd earscopeweb
                    docker compose -f ${NEW_COMPOSE_FILE} up -d

                    echo "Checking running containers..."
                    docker ps -a | grep earscopeweb
                    '''
                }
            }
        }
        stage('Health Check') {
            steps {
                script{
                    def backendHealth = false
                    def frontendHealth = false

                    sh '''
                    echo "Performing health check on new containers..."
                    cd earscopeweb

                    # Wait longer for container to initialize
                    echo "Waiting 30 seconds for containers to initialize..."
                    sleep 30
                    '''

                    // Health check for backend with better error handling
                    timeout(time: "${HEALTH_CHECK_TIMEOUT}", unit: 'SECONDS') {
                        retry(5) {
                            try {
                                sh '''
                                echo "Checking backend container status..."
                                if [ "$(docker inspect -f '{{.State.Running}}' earscopeweb-backend${NEW_CONTAINER_SUFFIX})" = "true" ]; then
                                    echo "Backend container is running. Performing HTTP check..."
                                    # Log curl output for debugging
                                    curl -v -f http://localhost:8020 || (echo "Backend endpoint check failed" && exit 1)
                                    echo "Backend health check passed!"
                                else
                                    echo "Backend container is not running!"
                                    docker logs earscopeweb-backend${NEW_CONTAINER_SUFFIX}
                                    exit 1
                                fi
                                '''
                                backendHealth = true
                            } catch (Exception e) {
                                echo "Backend health check failed! Retrying in 10 seconds..."
                                sh 'docker logs earscopeweb-backend${NEW_CONTAINER_SUFFIX}'
                                sleep 10 // Longer wait before retrying
                            }
                        }
                    }

                    // Health check for frontend with better error handling
                    timeout(time: "${HEALTH_CHECK_TIMEOUT}", unit: 'SECONDS') {
                        retry(5) {
                            try {
                                sh '''
                                echo "Checking frontend container status..."
                                if [ "$(docker inspect -f '{{.State.Running}}' earscopeweb-frontend${NEW_CONTAINER_SUFFIX})" = "true" ]; then
                                    echo "Frontend container is running. Performing HTTP check..."
                                    # Add -s to silence progress, but keep errors
                                    curl -s -f http://localhost:8021 || (echo "Frontend endpoint check failed" && exit 1)
                                    echo "Frontend health check passed!"
                                else
                                    echo "Frontend container is not running!"
                                    docker logs earscopeweb-frontend${NEW_CONTAINER_SUFFIX}
                                    exit 1
                                fi
                                '''
                                frontendHealth = true
                            } catch (Exception e) {
                                echo "Frontend health check failed! Retrying in 10 seconds..."
                                sh 'docker logs earscopeweb-frontend${NEW_CONTAINER_SUFFIX}'
                                sleep 10 // Longer wait before retrying
                            }
                        }
                    }

                    if (backendHealth && frontendHealth) {
                        echo "Both backend and frontend health checks passed!"
                    } else {
                        error "Health check failed for one or both containers!"
                    }
                }
            }
        }
        stage('Switch to New Containers') {
            steps {
                script {
                    sh '''
                    echo "Health checks passed! Switching to new containers..."
                    cd earscopeweb

                    # Backup old compose file
                    if [ -f docker-compose.yml ]; then
                        cp docker-compose.yml docker-compose.bak
                    fi
                    
                    # Perbarui docker-compose.yml dengan image tag baru
                    sed -i "s|image: earscopeweb-backend:latest|image: ${DOCKER_IMAGE_NAME_BACKEND}:${IMAGE_TAG}|" docker-compose.yml
                    sed -i "s|image: earscopeweb-frontend:latest|image: ${DOCKER_IMAGE_NAME_FRONTEND}:${IMAGE_TAG}|" docker-compose.yml
                    
                    # Simpan tag image saat ini untuk rollback (jika perlu)
                    echo "${IMAGE_TAG}" > .current_tag
                    
                    # Hentikan container pengujian
                    docker compose -f ${NEW_COMPOSE_FILE} down
                    
                    # Hentikan container produksi lama
                    docker compose down
                    
                    # Jalankan container produksi baru
                    docker compose up -d
                    
                    sleep 15

                    echo "Checking if new containers are running..."
                    docker ps | grep earscopeweb || (echo "Error: New containers are not running!" && docker compose logs && exit 1)

                    echo "Successfully switched to new containers!"
                    '''
                }
            }
        }
        stage('Copy Nginx Config & Restart Nginx') {
            steps {
                script {
                    // Perbaikan pada perintah sudo - periksa konfigurasi file sebelum menyalinnya
                    sh '''
                    echo "Checking if Nginx configuration files exist..."
                    if [ -f earscopeweb/earscopeweb-frontend.conf ] && [ -f earscopeweb/earscopeweb-backend.conf ]; then
                        echo "Configuration files found. Checking permissions..."
                        
                        # Periksa folder tujuan
                        if [ -d "${NGINX_CONF_PATH}" ]; then
                            echo "Nginx conf directory exists. Copying configuration files..."
                            
                            # Gunakan sudo dengan opsi -n (non-interactive)
                            sudo -n cp earscopeweb/earscopeweb-frontend.conf ${NGINX_CONF_PATH} || echo "Warning: Failed to copy frontend config, might need sudo permissions"
                            sudo -n cp earscopeweb/earscopeweb-backend.conf ${NGINX_CONF_PATH} || echo "Warning: Failed to copy backend config, might need sudo permissions"
                            
                            echo "Restarting Nginx..."
                            if docker ps | grep -q nginx; then
                                docker restart nginx || echo "Warning: Failed to restart nginx container"
                            else
                                echo "Nginx container not found, skipping restart"
                            fi
                        else
                            echo "Warning: Nginx conf directory not found: ${NGINX_CONF_PATH}"
                            echo "Skipping Nginx configuration update"
                        fi
                    else
                        echo "Warning: Nginx configuration files not found"
                        echo "Skipping Nginx configuration update"
                    fi
                    
                    echo "Nginx configuration stage completed"
                    '''
                }
            }
        }
    }
    post {
        failure {
            script {
                echo "Pipeline failed! Rolling back to previous version..."
                sh '''
                cd earscopeweb || true
                
                # Hentikan container pengujian jika masih berjalan
                docker compose -f ${NEW_COMPOSE_FILE} down || true
                
                # Periksa apakah container produksi sedang berjalan
                if ! docker ps -q --filter "name=earscopeweb-backend$" | grep -q . && ! docker ps -q --filter "name=earscopeweb-frontend$" | grep -q .; then
                    echo "No running production containers found. Restoring from backup..."
                    
                    # Kembalikan compose file dari backup jika ada
                    if [ -f docker-compose.bak ]; then
                        cp docker-compose.bak docker-compose.yml
                    fi
                    
                    # Start container lama
                    docker compose up -d
                fi
                
                echo "Rollback completed! Current running containers:"
                docker ps -a | grep earscopeweb
                '''
            }
        }
        success {
            echo "Pipeline build completed successfully!"
        }
        always {
            echo "Cleaning up any leftover test containers and temporary files..."
            sh '''
            cd earscopeweb || true
            docker compose -f ${NEW_COMPOSE_FILE} down || true
            rm -f ${NEW_COMPOSE_FILE} || true
            '''
        }
    }
}
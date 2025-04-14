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
                    sh """
                    echo "Cleaning workspace..."
                    rm -rf earscopeweb || true
                    """
                    withCredentials([usernamePassword(credentialsId: 'github-auth-to-jenkins', usernameVariable: 'GIT_USER', passwordVariable: 'GIT_TOKEN')]) {
                        sh """
                        echo "Cloning repository with authentication..."
                        git clone https://${GIT_USER}:${GIT_TOKEN}@github.com/zahrasabill/earscopeweb.git -b ${GIT_BRANCH}
                        """
                    }
                }
            }
        }
        stage('Prepare .env File') {
            steps {
                script {
                    withCredentials([file(credentialsId: 'earscopeweb-backend-env', variable: 'ENV_FILE')]) {
                        sh """
                        echo "Copying .env file..."
                        cp \${ENV_FILE} earscopeweb/backend/.env
                        """
                    }
                }
                script {
                    withCredentials([file(credentialsId: 'earscopeweb-frontend-env', variable: 'ENV_FILE')]) {
                        sh """
                        echo "Copying .env file..."
                        cp \${ENV_FILE} earscopeweb/frontend/.env
                        """
                    }
                }
            }
        }
        stage('Prepare New Docker Compose File') {
            steps {
                script {
                    sh """
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

                    # Change path volumes
                    sed -i 's|/var/www/log/earscopeweb-backend/storage/logs|/var/www/log/earscopeweb-backend-new/storage/logs|' ${NEW_COMPOSE_FILE}
                    sed -i 's|/var/www/earscopeweb-backend/storage/app/private|/var/www/earscopeweb-backend-new/storage/app/private|' ${NEW_COMPOSE_FILE}
                    
                    # Pastikan direktori log dan storage untuk container testing tersedia
                    mkdir -p /var/www/log/earscopeweb-backend-new/storage/logs
                    mkdir -p /var/www/earscopeweb-backend-new/storage/app/private

                    echo "Final new docker compose file..."
                    cat ${NEW_COMPOSE_FILE}
                    """
                }
            }
        }
        stage('Build Docker Images') {
            steps {
                script {
                    sh """
                    echo "Building Docker images..."
                    cd earscopeweb
                    docker compose -f ${NEW_COMPOSE_FILE} build --no-cache --pull
                    """
                }
            }
        }
        stage('Deploy Docker Images to Container for Testing') {
            steps {
                script {
                    sh """
                    echo "Deploying new containers for testing..."
                    cd earscopeweb
                    docker compose -f ${NEW_COMPOSE_FILE} up -d

                    echo "Checking running containers..."
                    docker ps -a | grep earscopeweb
                    """
                }
            }
        }
        stage('Health Check') {
            steps {
                script{
                    def backendHealth = false
                    def frontendHealth = false

                    sh """
                    echo "Performing health check on new containers..."
                    cd earscopeweb

                    # Wait container to initialize
                    sleep 10
                    """

                    // Health check for backend
                    timeout(time: "${HEALTH_CHECK_TIMEOUT}", unit: 'SECONDS') {
                        retry(5) {
                            try {
                                sh """
                                # Check if backend container is running
                                if [ "\$(docker inspect -f '{{.State.Running}}' earscopeweb-backend${NEW_CONTAINER_SUFFIX})" = "true" ]; then
                                    # Endpoint check
                                    curl -f http://localhost:8020 || exit 1
                                    echo "Backend health check passed!"
                                else
                                    echo "Backend container is not running!"
                                    exit 1
                                fi
                                """
                                backendHealth = true
                            } catch (Exception e) {
                                echo "Backend health check failed! Retrying..."
                                sleep 5 // Wait before retrying
                                error "Backend health check failed"
                            }
                        }
                    }

                    // Health check for frontend
                    timeout(time: "${HEALTH_CHECK_TIMEOUT}", unit: 'SECONDS') {
                        retry(5) {
                            try {
                                sh """
                                # Check if frontend container is running
                                if [ "\$(docker inspect -f '{{.State.Running}}' earscopeweb-frontend${NEW_CONTAINER_SUFFIX})" = "true" ]; then
                                    # Endpoint check
                                    curl -f http://localhost:8021 || exit 1
                                    echo "Frontend health check passed!"
                                else
                                    echo "Frontend container is not running!"
                                    exit 1
                                fi
                                """
                                frontendHealth = true
                            } catch (Exception e) {
                                echo "Frontend health check failed! Retrying..."
                                sleep 5 // Wait before retrying
                                error "Frontend health check failed"
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
                    sh """
                    echo "Health checks pased! switch to new containers..."
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
                    
                    echo "Successfully switched to new containers!"
                    docker ps -a | grep earscopeweb
                    """
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
        failure {
            script {
                echo "Pipeline failed! Rolling back to previous version..."
                sh """
                cd earscopeweb || true
                
                # Hentikan container pengujian jika masih berjalan
                docker compose -f ${NEW_COMPOSE_FILE} down || true
                
                # Periksa apakah container produksi sedang berjalan
                if ! docker ps -q --filter "name=earscopeweb-backend\$" | grep -q . && ! docker ps -q --filter "name=earscopeweb-frontend$" | grep -q .; then
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
                """
            }
        }
        success {
            echo "Pipeline build completed successfully!"
        }
        always {
            echo "Cleaning up any leftover test containers and temporary files..."
            sh """
            cd earscopeweb || true
            docker compose -f \${NEW_COMPOSE_FILE} down || true
            rm -f \${NEW_COMPOSE_FILE} || true
            """
        }
    }
}

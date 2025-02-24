pipeline {
    agent any
    environment {
        IMAGE_TAG = "${env.BUILD_ID}"
        GIT_BRANCH = "main"
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
                    withCredentials([file(credentialsId: 'earscopeweb-env', variable: 'ENV_FILE')]) {
                        sh """
                        echo "Copying .env file..."
                        cp \${ENV_FILE} earscopeweb/backend/.env
                        """
                    }
                }
            }
        }
        stage('Update Image Tags in docker-compose.yml') {
            steps {
                script {
                    sh """
                    echo "Updating docker-compose.yml with new image tag..."
                    
                    cd earscopeweb
                    
                    sed -i "s|image: earscopeweb-backend:.*|image: earscopeweb-backend:${IMAGE_TAG}|" docker-compose.yml
                    sed -i "s|image: earscopeweb-frontend:.*|image: earscopeweb-frontend:${IMAGE_TAG}|" docker-compose.yml
                    
                    echo "Final docker-compose.yml content:"
                    cat docker-compose.yml
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
                    docker compose build --no-cache
                    """
                }
            }
        }
        stage('Deploy Docker Images to Container') {
            steps {
                script {
                    sh """
                    echo "Building Docker images..."
                    cd earscopeweb
                    echo "Deploying containers..."
                    docker compose up -d --force-recreate

                    echo "Checking running containers..."
                    docker ps -a

                    echo "Checking backend working directory..."
                    docker exec earscopeweb-backend pwd
                    docker exec earscopeweb-backend ls -al /app

                    echo "Checking frontend working directory..."
                    docker exec earscopeweb-frontend pwd
                    docker exec earscopeweb-frontend ls -al /app
                    """
                }
            }
        }
        // stage('Cleanup Docker Images') {
        //     steps {
        //         script {
        //             sh """
        //             echo "Cleaning up built Docker images..."
        //             docker rmi -f earscopeweb-backend:${IMAGE_TAG} || true
        //             docker rmi -f earscopeweb-frontend:${IMAGE_TAG} || true
        //             """
        //         }
        //     }
        // }
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

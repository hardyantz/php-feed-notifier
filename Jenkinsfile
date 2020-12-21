pipeline {
  agent any
  environment {
    CI = 'true'
  }
  stages {
    stage('Build') {
      tools {
        jdk "jdk11" // the name you have given the JDK installation in Global Tool Configuration
      }
      environment {
        scannerHome = tool 'SonarQube_Scanner_4.5' // the name you have given the Sonar Scanner (in Global Tool Configuration)
      }
      steps {
        echo 'hello world'
      }
    }
    stage('Test') {
      tools {
        jdk "jdk11" // the name you have given the JDK installation in Global Tool Configuration
      }
      environment {
        scannerHome = tool 'SonarQube_Scanner_4.5' // the name you have given the Sonar Scanner (in Global Tool Configuration)
      }
      steps {
        echo 'hello tests'
      }
    }
  }
}
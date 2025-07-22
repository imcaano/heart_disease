import mysql.connector
from mysql.connector import Error
import os
from dotenv import load_dotenv

def create_database():
    try:
        # Load environment variables
        load_dotenv()
        
        # Get database credentials from DATABASE_URL
        db_url = os.getenv('DATABASE_URL')
        if not db_url:
            raise ValueError("DATABASE_URL not found in .env file")
            
        # Parse database URL
        # Format: mysql://username:password@localhost/database_name
        db_parts = db_url.replace('mysql://', '').split('/')
        db_credentials = db_parts[0].split(':')
        db_name = db_parts[1]
        
        # Connect to MySQL server
        connection = mysql.connector.connect(
            host='localhost',
            user=db_credentials[0],
            password=db_credentials[1]
        )
        
        if connection.is_connected():
            cursor = connection.cursor()
            
            # Read and execute SQL script
            with open('database.sql', 'r') as file:
                sql_commands = file.read()
                for command in sql_commands.split(';'):
                    if command.strip():
                        cursor.execute(command)
            
            print("Database and tables created successfully!")
            
    except Error as e:
        print(f"Error: {e}")
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()
            print("MySQL connection closed.")

if __name__ == "__main__":
    create_database() 
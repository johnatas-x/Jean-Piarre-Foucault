from flask import Flask, render_template
from dotenv import load_dotenv
import os
import mysql.connector

load_dotenv()


class DBManager:
    def __init__(self):
        self.connection_params = {
            "database": os.getenv("DB_NAME"),
            "user": os.getenv("DB_USER"),
            "password": os.getenv("DB_PASSWORD"),
            "host": os.getenv("DB_HOST"),
            "auth_plugin": "mysql_native_password",
        }

    def get_versions(self) -> list[str]:
        connection = None
        try:
            connection = mysql.connector.connect(**self.connection_params)
            cursor = connection.cursor()
            cursor.execute("SELECT version FROM lotto_versions")

            return [version[0] for version in cursor.fetchall()]
        except mysql.connector.Error as err:
            return []
        finally:
            if cursor:
                cursor.close()
            if connection:
                connection.close()


app = Flask(__name__)
db_manager = DBManager()


@app.route("/algo")
def algo():
    versions = db_manager.get_versions()
    return render_template("algo.html", versions=versions)


if __name__ == "__main__":
    app.run()
